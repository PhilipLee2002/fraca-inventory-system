# User Manual
## FRACA SERVCOM Inventory Management System

**Document Version:** 1.0
**Date:** March 2026
**Audience:** All system users (Admin, Manager, Staff)

---

## 1. Introduction

The FRACA SERVCOM Inventory Management System is a web-based application for managing your business inventory, sales, purchases, suppliers, customers, and financial reports. It runs in any modern web browser and requires no software installation.

### 1.1 User Roles

| Role | What They Can Do |
|------|-----------------|
| Admin | Full access — manage users, all CRUD operations, hard deletes |
| Manager | All operations except hard deletes (requires admin verification) |
| Staff | View products/categories/suppliers/customers; create sales and purchases |

### 1.2 Accessing the System

Open your browser and navigate to the system URL (e.g., `http://localhost:8000` in development or your production domain). You will be redirected to the login page if not already signed in.

---

## 2. Logging In

**URL:** `/login`

1. Enter your email address and password
2. Click **Log In**
3. You will be redirected to the Dashboard

> **[SCREENSHOT: Login page — show the login form with email/password fields and the Log In button]**

**Default credentials (development/demo):**

| Email | Password | Role |
|-------|----------|------|
| admin@inventory.com | password123 | Admin |
| manager@inventory.com | password123 | Manager |
| staff@inventory.com | password123 | Staff |

**Logging Out:**
Click your name in the top-right navigation bar, then click **Logout**.

---

## 3. Navigation

The top navigation bar is always visible. The links shown depend on your role.

| Nav Item | Roles With Access |
|----------|------------------|
| Dashboard | All |
| Products | All |
| Categories | Admin, Manager |
| Suppliers | Admin, Manager |
| Customers | Admin, Manager |
| Sales | All |
| Purchases | All |
| Stock Adjustments | Admin, Manager |
| Reports | All |
| Users | Admin only |

> **[SCREENSHOT: Navigation bar — show the full navbar with all menu items visible (use Admin account)]**

---

## 4. Dashboard

**URL:** `/`

The dashboard gives you a real-time overview of the business. It refreshes automatically every 30 seconds.

### 4.1 Summary Statistics Cards

The top row shows key metrics:

- **Total Products** — number of products in the system
- **Today's Sales** — total sales revenue for today (KSh)
- **Low Stock Items** — products at or below their reorder level
- **Total Users** — registered system users

> **[SCREENSHOT: Dashboard top stats cards — show the 4 metric cards with values]**

### 4.2 Extended Metrics (Admin / Manager)

Below the main cards:

- Monthly Sales and Monthly Purchases (KSh)
- Profit Margin (%)
- Inventory Valuation (KSh)
- Out of Stock count, Pending Sales, Pending Purchases, Active Alerts

> **[SCREENSHOT: Dashboard extended metrics row]**

### 4.3 Widgets

The lower section contains four widgets:

- **Recent Activity** — a timeline of the latest sales, purchases, and stock changes
- **Top Performers** — top-selling products this month with medal rankings
- **Financial Summary** — monthly revenue, expenses, and net profit
- **Pending Actions** — quick links to items needing attention (low stock, pending orders, alerts)

> **[SCREENSHOT: Dashboard widgets section — show all 4 widgets side by side]**

### 4.4 Alerts

The Alerts panel shows active low-stock and system notifications. Click the checkmark button on any alert to dismiss it.

> **[SCREENSHOT: Dashboard alerts panel — show at least one active alert with the dismiss button]**

### 4.5 Quick Action Buttons

Buttons at the top of the dashboard provide one-click navigation to key pages (Products, New Sale, New Purchase, Reports).

> **[SCREENSHOT: Dashboard quick action buttons row]**

---

## 5. Products

**URL:** `/products`

The Products page lists all inventory items with search, filter, and pagination.

### 5.1 Viewing Products

The table shows: SKU, Name, Category, Stock Quantity, Selling Price, and Actions.

- Products with stock at or below the reorder level show the quantity in **red** with a warning icon
- Use the search box to filter by name or SKU
- Use the Category and Supplier dropdowns to filter by those fields
- Click **Clear Filters** to reset all filters

> **[SCREENSHOT: Products page — show the full table with at least one low-stock item highlighted in red]**

### 5.2 Adding a Product (Admin / Manager)

1. Click **Add Product** (top right)
2. Fill in the form:
   - **Name** (required)
   - **SKU** (required, must be unique)
   - **Barcode** (optional)
   - **Category** and **Supplier** (dropdowns)
   - **Cost Price** and **Selling Price** (KSh)
   - **Current Stock** and **Reorder Level**
   - **Description** (optional)
3. Click **Save**

> **[SCREENSHOT: Add Product modal — show the form with fields filled in]**

### 5.3 Editing a Product (Admin / Manager)

Click the pencil icon in the Actions column. The same form opens pre-filled with the product's current data. Make changes and click **Save**.

### 5.4 Deleting a Product

- **Admin:** Click the trash icon → confirm in the dialog
- **Manager:** Click the trash icon → enter admin email and password in the verification modal

> **[SCREENSHOT: Admin Verification Modal — show the modal asking for admin credentials]**

---

## 6. Categories

**URL:** `/categories`

Categories group your products. Each category shows its name, description, and the number of products assigned to it.

### 6.1 Adding a Category

1. Click **Add Category**
2. Enter a **Name** (required, must be unique) and optional **Description**
3. Click **Save**

### 6.2 Editing / Deleting a Category

- Click the pencil icon to edit
- Click the trash icon to delete (a category with products assigned cannot be deleted)

> **[SCREENSHOT: Categories page — show the table with product counts and action buttons]**

---

## 7. Suppliers

**URL:** `/suppliers`

Suppliers are the vendors you purchase stock from.

### 7.1 Supplier Fields

- Name, Contact Person, Email, Phone, Address

### 7.2 Managing Suppliers

- Click **Add Supplier** to create a new record
- Click the pencil icon to edit
- Click the trash icon to delete (Admin only; Manager requires verification)

> **[SCREENSHOT: Suppliers page — show the table with at least 2–3 supplier records]**

---

## 8. Customers

**URL:** `/customers`

Customers are the people or businesses you sell to. Sales can be linked to a customer or recorded as "Walk-in."

### 8.1 Customer Fields

- First Name, Last Name, Email, Phone, Address

### 8.2 Managing Customers

- Click **Add Customer** to create a new record
- Click the pencil icon to edit
- Click the trash icon to delete

> **[SCREENSHOT: Customers page — show the table with customer records and action buttons]**

---

## 9. Sales

**URL:** `/sales`

The Sales page lists all sales transactions. Each sale has an invoice number, customer, date, total amount, and status.

### 9.1 Viewing Sales

Use the search box to find a sale by invoice number or customer name. Filter by status (Pending, Completed, Cancelled) or date range.

> **[SCREENSHOT: Sales page — show the table with multiple sales, different statuses, and filter controls]**

### 9.2 Viewing a Sale's Details

Click the invoice number (blue link) in the table to open a read-only detail view showing all line items and the total.

> **[SCREENSHOT: Sale detail modal — show the line items table and total]**

### 9.3 Creating a New Sale

1. Click **New Sale**
2. Select a **Customer** (or leave as Walk-in)
3. Set the **Sale Date**, **Payment Method**, and **Status**
4. Add line items:
   - Select a **Product** from the dropdown (price auto-fills)
   - Set the **Quantity**
   - Adjust the **Unit Price** if needed
   - Click **+ Add Item** to add more rows
   - Click the red × button to remove a row
5. The **Total** updates automatically as you add items
6. Add **Notes** if needed
7. Click **Save**

> **[SCREENSHOT: New Sale modal — show the form with 2–3 line items and the running total]**

### 9.4 Editing a Sale (Admin / Manager)

Click the pencil icon to reopen the sale form for editing.

### 9.5 Deleting a Sale

- **Admin:** Click trash → confirm
- **Manager:** Click trash → enter admin credentials

---

## 10. Purchases

**URL:** `/purchases`

The Purchases page tracks all stock purchases from suppliers.

### 10.1 Viewing Purchases

Filter by status (Pending, Received, Cancelled) or date range. Click a PO number to view line items.

> **[SCREENSHOT: Purchases page — show the table with PO numbers, supplier names, and status badges]**

### 10.2 Creating a New Purchase

1. Click **New Purchase**
2. Select a **Supplier**
3. Set the **Purchase Date**, **Payment Method**, and **Status**
4. Add line items (Product, Quantity, Unit Cost)
5. The total calculates automatically
6. Click **Save**

When a purchase is saved with status **Received**, the stock levels for all included products are automatically incremented.

> **[SCREENSHOT: New Purchase modal — show the form with supplier selected and line items]**

### 10.3 Editing / Deleting a Purchase

Same workflow as Sales — pencil to edit, trash to delete (with admin verification for Manager role).

---

## 11. Stock Adjustments

**URL:** `/stock-adjustments`

Stock adjustments allow you to manually correct stock levels — for example, after a physical stock count.

### 11.1 Making a Stock Adjustment

1. Click **New Adjustment**
2. Select the **Product**
3. Choose the **Adjustment Type**: Add, Remove, or Set
4. Enter the **Quantity**
5. Enter a **Reason** (required for audit trail)
6. Click **Save**

Every adjustment is logged in the stock history with the user who made it, the previous stock, and the new stock.

> **[SCREENSHOT: Stock Adjustments page — show the adjustment history table and the New Adjustment modal]**

---

## 12. Reports

**URL:** `/reports`

The Reports page provides six report types accessible via tabs.

### 12.1 Available Reports

| Report | Description |
|--------|-------------|
| Inventory Valuation | Current stock value at cost price and potential revenue |
| Sales Report | Sales transactions with revenue summary |
| Purchases Report | Purchase transactions with total spend |
| Profit & Loss | Revenue vs expenses breakdown by period |
| Stock Levels | Current stock status with low/out-of-stock indicators |
| Stock Movement | History of all stock changes (in/out) |

### 12.2 Generating a Report

1. Click the tab for the report you want
2. Set any available filters (date range, category, supplier, customer, product)
3. Click **Generate Report**
4. Results appear in the table below with summary cards at the top

> **[SCREENSHOT: Reports page — show the Inventory Valuation report with summary cards and the data table]**

### 12.3 Exporting to CSV

After generating a report, click **Export to CSV** to download the data as a spreadsheet file.

> **[SCREENSHOT: Reports page — show the Export to CSV button after a report has been generated]**

---

## 13. Users (Admin Only)

**URL:** `/users`

The Users page is only accessible to Admins. It lists all system users with their roles.

### 13.1 Adding a User

1. Click **Add User**
2. Fill in: Name, Email, Password, Role (Admin / Manager / Staff)
3. Click **Save**

### 13.2 Editing a User

Click the pencil icon to update a user's name, email, or role.

### 13.3 Deleting a User

Click the trash icon and confirm. This action is irreversible.

> **[SCREENSHOT: Users page — show the user table with role badges and action buttons]**

---

## 14. Profile Settings

**URL:** `/profile`

Click your name in the navigation bar and select **Profile** to:

- Update your name and email address
- Change your password
- Delete your account (irreversible)

> **[SCREENSHOT: Profile page — show the profile edit form]**

---

## 15. Common Workflows

### 15.1 Receiving New Stock

1. Go to **Purchases** → **New Purchase**
2. Select the supplier and set status to **Received**
3. Add all purchased products with quantities and unit costs
4. Save — stock levels update automatically

### 15.2 Processing a Sale

1. Go to **Sales** → **New Sale**
2. Select the customer (or leave as Walk-in)
3. Add products and quantities
4. Set status to **Completed** and payment method
5. Save — stock levels decrease automatically

### 15.3 Investigating Low Stock

1. Check the **Dashboard** — the Low Stock card shows the count
2. Click the Low Stock link in the Pending Actions widget to go to Products
3. Products with red quantities are at or below reorder level
4. Create a new Purchase to restock

### 15.4 Checking Business Performance

1. Go to **Reports** → **Profit & Loss** tab
2. Set a date range (e.g., current month)
3. Click **Generate Report**
4. Review revenue, expenses, and net profit summary cards

---

## 16. Notifications and Toasts

The system shows brief notification messages (toasts) in the bottom-right corner of the screen after every action:

- **Green** — success (record saved, deleted, etc.)
- **Red** — error (validation failed, server error, etc.)
- **Yellow** — warning

These disappear automatically after a few seconds.

> **[SCREENSHOT: Toast notification — show a green success toast in the bottom-right corner]**

---

## 17. Troubleshooting

| Issue | Solution |
|-------|---------|
| Page shows "Failed to load" | Check your internet connection; refresh the page |
| Buttons or modals not appearing | Ensure JavaScript is enabled in your browser |
| "You do not have permission" message | Contact your Admin to update your role permissions |
| Cannot delete a category | Remove or reassign all products in that category first |
| Sale total shows 0 | Select a product from the dropdown before entering quantity |
| Login fails | Check caps lock; contact Admin to reset your password |
