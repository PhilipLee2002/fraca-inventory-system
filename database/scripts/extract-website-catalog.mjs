/**
 * Flatten FRACA-SERVCOM-WEBSITE/gallery-data.js into a JSON catalog
 * the inventory seeders can import. Furniture + bags only (hardware skipped).
 * Selling prices come from the gallery; cost_price and current_stock stay 0.
 * Unpriced items (e.g. some bags) get needs_price + a description flag.
 *
 * Re-run after gallery updates:
 *
 *   node database/scripts/extract-website-catalog.mjs
 *
 * Then seed CategoriesTableSeeder and ProductsTableSeeder. Do not push IMS
 * stock back to the public website.
 */

import fs from "fs";
import path from "path";
import vm from "vm";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, "../..");
const galleryPath = path.join(root, "FRACA-SERVCOM-WEBSITE", "gallery-data.js");
const outPath = path.join(root, "database", "data", "website-catalog.json");

const CATEGORIES = {
  beds: {
    slug: "beds",
    name: "Beds",
    group: "Home Furniture",
    sku_prefix: "BED",
    description: "Executive and home beds from the Fraca catalog.",
  },
  coffeeTables: {
    slug: "coffee-tables",
    name: "Coffee Tables",
    group: "Home Furniture",
    sku_prefix: "CFT",
    description: "Coffee and occasional tables.",
  },
  diningSets: {
    slug: "dining-sets",
    name: "Dining Sets",
    group: "Home Furniture",
    sku_prefix: "DIN",
    description: "Dining tables and chair sets.",
  },
  dressingMirrors: {
    slug: "dressing-mirrors",
    name: "Dressing Mirrors",
    group: "Home Furniture",
    sku_prefix: "DRM",
    description: "Dressing tables, vanities and standing mirrors.",
  },
  sofaSets: {
    slug: "sofa-sets",
    name: "Sofa Sets",
    group: "Home Furniture",
    sku_prefix: "SOF",
    description: "Sofa sets and lounge seating.",
  },
  entertainmentUnits: {
    slug: "entertainment-units",
    name: "Entertainment Units",
    group: "Home Furniture",
    sku_prefix: "ENT",
    description: "TV cabinets and entertainment units.",
  },
  executiveOfficeDesks: {
    slug: "executive-office-desks",
    name: "Executive Office Desks",
    group: "Desks & Workstations",
    sku_prefix: "EOD",
    description: "Executive office desks.",
  },
  pedestalDesks: {
    slug: "pedestal-desks",
    name: "Pedestal Desks",
    group: "Desks & Workstations",
    sku_prefix: "PED",
    description: "Pedestal and L-shaped office desks.",
  },
  receptionDesks: {
    slug: "reception-desks",
    name: "Reception Desks",
    group: "Desks & Workstations",
    sku_prefix: "REC",
    description: "Reception and front-desk units.",
  },
  conferenceTables: {
    slug: "conference-tables",
    name: "Conference Tables",
    group: "Desks & Workstations",
    sku_prefix: "CNT",
    description: "Boardroom and conference tables.",
  },
  workStations: {
    slug: "workstations",
    name: "Workstations",
    group: "Desks & Workstations",
    sku_prefix: "WKS",
    description: "Cluster and shared workstations.",
  },
  studentSets: {
    slug: "student-desks",
    name: "Student Desks",
    group: "Desks & Workstations",
    sku_prefix: "STU",
    description: "School and student desk sets.",
  },
  filingCabinets: {
    slug: "filing-cabinets",
    name: "Filing Cabinets",
    group: "Storage",
    sku_prefix: "FIL",
    description: "Filing cabinets, lockers and storage cupboards.",
  },
  wardrobes: {
    slug: "wardrobes",
    name: "Wardrobes",
    group: "Storage",
    sku_prefix: "WRD",
    description: "Wardrobes and tallboys.",
  },
  libraryShelves: {
    slug: "library-shelves",
    name: "Library & Supermarket Shelves",
    group: "Storage",
    sku_prefix: "LIB",
    description: "Library, supermarket and display shelving.",
  },
  storageSafes: {
    slug: "storage-safes",
    name: "Storage Safes",
    group: "Storage",
    sku_prefix: "SAF",
    description: "Safes and secure storage.",
  },
  shoeRacks: {
    slug: "shoe-racks",
    name: "Shoe Racks",
    group: "Storage",
    sku_prefix: "SHO",
    description: "Shoe racks and organisers.",
  },
  coatHangers: {
    slug: "coat-hangers",
    name: "Coat Hangers",
    group: "Storage",
    sku_prefix: "COA",
    description: "Coat and hat stands.",
  },
  officeChairs: {
    slug: "office-chairs",
    name: "Office Chairs",
    group: "Seating",
    sku_prefix: "OFC",
    description: "Task and office chairs.",
  },
  conferenceChairs: {
    slug: "conference-chairs",
    name: "Conference Chairs",
    group: "Seating",
    sku_prefix: "CFC",
    description: "Conference and banquet chairs.",
  },
  visitorsBoardroomChairs: {
    slug: "visitors-boardroom-chairs",
    name: "Visitors & Boardroom Chairs",
    group: "Seating",
    sku_prefix: "VIS",
    description: "Visitor and boardroom seating.",
  },
  linkChairs: {
    slug: "link-chairs",
    name: "Link Chairs",
    group: "Seating",
    sku_prefix: "LNK",
    description: "Linkable waiting and lounge chairs.",
  },
  catalinaChairs: {
    slug: "catalina-chairs",
    name: "Catalina Chairs",
    group: "Seating",
    sku_prefix: "CAT",
    description: "Catalina chair range.",
  },
  rockingChairs: {
    slug: "rocking-chairs",
    name: "Rocking Chairs",
    group: "Seating",
    sku_prefix: "RCK",
    description: "Rocking chairs.",
  },
  restaurantSeats: {
    slug: "restaurant-seats",
    name: "Restaurant Seats",
    group: "Seating",
    sku_prefix: "RST",
    description: "Restaurant chairs and bar stools.",
  },
  benches: {
    slug: "benches",
    name: "Benches",
    group: "Institutional & Specialty",
    sku_prefix: "BNC",
    description: "Benches for waiting and institutional use.",
  },
  "church-furniture": {
    slug: "church-furniture",
    name: "Church Furniture",
    group: "Institutional & Specialty",
    sku_prefix: "CHF",
    description: "Church chairs, pews and assembly seating.",
  },
  pulpits: {
    slug: "pulpits",
    name: "Pulpits",
    group: "Institutional & Specialty",
    sku_prefix: "PUL",
    description: "Pulpits and lecterns.",
  },
  "executive-chairs": {
    slug: "executive-chairs",
    name: "Executive Chairs",
    group: "Seating",
    sku_prefix: "EXC",
    description: "Senior, orthopedic, mid-back and low-back executive chairs.",
  },
  bags: {
    slug: "bags",
    name: "Bags",
    group: "Bags",
    sku_prefix: "BAG",
    description: "FOS handbags, totes, travel and document bags.",
  },
};

const NESTED_MAP = {
  churchFurniture: {
    chairs: "church-furniture",
    pews: "church-furniture",
    pulpits: "pulpits",
  },
  executiveChairs: {
    senior: "executive-chairs",
    orthopedic: "executive-chairs",
    lowBack: "executive-chairs",
    midBack: "executive-chairs",
  },
  bags: Object.fromEntries(
    [
      "ladiesHandbagsLb001",
      "ladiesHandbagsLb002",
      "classicTotes",
      "toteBucketBags",
      "toteSquareBags",
      "monkeyBags",
      "slingBags",
      "manBags",
      "travellingBags",
      "backpacks",
      "laptopBags",
      "documentationBags",
      "crossBodyBags",
      "boxBags",
      "waistBags",
    ].map((key) => [key, "bags"])
  ),
};

function slugify(value) {
  return String(value || "")
    .toLowerCase()
    .normalize("NFKD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 80);
}

function parseSellingPrice(price) {
  if (!price) return 0;
  const amounts = [...String(price).matchAll(/KSh\s*([\d,]+)/gi)].map((match) =>
    parseInt(match[1].replace(/,/g, ""), 10)
  );
  return amounts.length ? Math.min(...amounts) : 0;
}

function loadGallery() {
  const source = fs.readFileSync(galleryPath, "utf8");
  const sandbox = { window: {} };
  vm.runInNewContext(source, sandbox);
  if (!sandbox.window.FracaGalleryData) {
    throw new Error("FracaGalleryData was not found in gallery-data.js");
  }
  return sandbox.window.FracaGalleryData;
}

function collectItems(node, categoryKey, bucket) {
  if (Array.isArray(node)) {
    for (const item of node) {
      if (item && item.title && item.src) {
        bucket.push({ categoryKey, item });
      }
    }
    return;
  }

  if (node && typeof node === "object") {
    const nested = NESTED_MAP[categoryKey];
    for (const [childKey, child] of Object.entries(node)) {
      const mapped = nested?.[childKey];
      if (!mapped) {
        throw new Error(`Unmapped nested gallery key: ${categoryKey}.${childKey}`);
      }
      collectItems(child, mapped, bucket);
    }
  }
}

function uniqueName(title, categorySlug, usedNames) {
  let name = title.trim();
  if (!usedNames.has(name.toLowerCase())) {
    usedNames.add(name.toLowerCase());
    return name;
  }
  const categoryName = Object.values(CATEGORIES).find((c) => c.slug === categorySlug)?.name;
  name = `${title.trim()} (${categoryName})`;
  let suffix = 2;
  let candidate = name;
  while (usedNames.has(candidate.toLowerCase())) {
    candidate = `${name} ${suffix}`;
    suffix += 1;
  }
  usedNames.add(candidate.toLowerCase());
  return candidate;
}

function uniqueSlug(base, usedSlugs) {
  let slug = base || "item";
  let suffix = 2;
  while (usedSlugs.has(slug)) {
    slug = `${base}-${suffix}`;
    suffix += 1;
  }
  usedSlugs.add(slug);
  return slug;
}

function main() {
  const gallery = loadGallery();
  const collected = [];

  for (const [key, value] of Object.entries(gallery)) {
    if (key === "hardware") {
      continue;
    }
    if (NESTED_MAP[key]) {
      collectItems(value, key, collected);
      continue;
    }
    if (!CATEGORIES[key]) {
      throw new Error(`Unmapped top-level gallery key: ${key}`);
    }
    collectItems(value, key, collected);
  }

  const counters = {};
  const usedNames = new Set();
  const usedSlugs = new Set();
  const seenTitles = new Set();
  const products = [];
  const usedCategorySlugs = new Set();

  for (const { categoryKey, item } of collected) {
    const category = CATEGORIES[categoryKey];
    if (!category) {
      throw new Error(`Unknown category key: ${categoryKey}`);
    }
    usedCategorySlugs.add(category.slug);

    const dedupeKey = `${category.slug}::${item.title.trim().toLowerCase()}`;
    if (seenTitles.has(dedupeKey)) {
      continue;
    }
    seenTitles.add(dedupeKey);

    counters[category.sku_prefix] = (counters[category.sku_prefix] || 0) + 1;
    const sku = `FRC-${category.sku_prefix}-${String(counters[category.sku_prefix]).padStart(3, "0")}`;
    const sellingPrice = parseSellingPrice(item.price);
    const unpriced = sellingPrice === 0;
    let description = (item.desc || "").trim();
    if (item.price && !description.includes(item.price)) {
      description = description
        ? `${description} Price: ${item.price}.`
        : `Price: ${item.price}.`;
    }
    if (unpriced) {
      description = description
        ? `${description} Unpriced — confirm with the showroom before selling.`
        : "Unpriced — confirm with the showroom before selling.";
    }

    products.push({
      name: uniqueName(item.title, category.slug, usedNames),
      sku,
      website_slug: uniqueSlug(slugify(item.title), usedSlugs),
      description: description || null,
      category_slug: category.slug,
      image: item.src,
      is_in_house: true,
      cost_price: 0,
      selling_price: sellingPrice,
      current_stock: 0,
      reorder_level: 0,
      needs_price: unpriced,
    });
  }

  const categories = Object.values(CATEGORIES)
    .filter((category) => usedCategorySlugs.has(category.slug))
    .sort((a, b) => a.group.localeCompare(b.group) || a.name.localeCompare(b.name))
    .map(({ sku_prefix, ...rest }) => rest);

  const catalog = {
    generated_at: new Date().toISOString(),
    source: "FRACA-SERVCOM-WEBSITE/gallery-data.js",
    category_count: categories.length,
    product_count: products.length,
    categories,
    products,
  };

  fs.mkdirSync(path.dirname(outPath), { recursive: true });
  fs.writeFileSync(outPath, JSON.stringify(catalog, null, 2) + "\n", "utf8");
  console.log(`Wrote ${products.length} products across ${categories.length} categories to ${path.relative(root, outPath)}`);
}

main();
