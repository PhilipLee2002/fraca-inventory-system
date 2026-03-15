/**
 * Simple in-memory cache with 5-minute TTL for reference data.
 */
const TTL = 5 * 60 * 1000; // 5 minutes
const store = {};

export function cacheGet(key) {
    const entry = store[key];
    if (!entry) return null;
    if (Date.now() - entry.ts > TTL) {
        delete store[key];
        return null;
    }
    return entry.data;
}

export function cacheSet(key, data) {
    store[key] = { data, ts: Date.now() };
}

export function cacheClear(key) {
    if (key) delete store[key];
    else Object.keys(store).forEach(k => delete store[k]);
}

/**
 * Fetch with cache: returns cached value or calls fetcher() and caches result.
 */
export async function cachedFetch(key, fetcher) {
    const cached = cacheGet(key);
    if (cached !== null) return cached;
    const data = await fetcher();
    cacheSet(key, data);
    return data;
}
