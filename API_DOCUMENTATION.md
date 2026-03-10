# Inventory Management System - API Documentation

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
All endpoints (except login) require Bearer token authentication using Laravel Sanctum.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## Authentication Endpoints

### Login
**POST** `/login`

**Request Body:**
```json
{
  "email": "admin@inventory.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@inventory.com"
    },
    "token": "1|abc123..."
  }
}
```

### Logout
**POST** `/logout`

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Products

### List Products
**GET** `/products`

**Query Parameters:**
- `search` - Search by name, SKU, or barcode
- `category_id` - Filter by category
- `supplier_id` - Filter by supplier
- `low_stock` - Filter low stock items (boolean)
- `sort_by` - Sort field (default: created_at)
- `sort_order` - asc/desc (default: desc)
- `per_page` - Items per page (default: 20)

**Response (200):**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Product Name",
        "sku": "SKU-001",
        "barcode": "123456789",
        "current_stock": 100,
        "reorder_level": 20,
        "unit_price": 50.00,
        "selling_price": 75.00
      }
    ],
    "total": 50,
    "per_page": 20
  }
}
```

### Get Low Stock Products
**GET** `/products/low-stock`

Returns products where `current_stock <= reorder_level`.

### Create Product
**POST** `/products`

**Request Body:**
```json
{
  "name": "New Product",
  "sku": "SKU-002",
  "barcode": "987654321",
  "category_id": 1,
  "supplier_id": 1,
  "unit_price": 50.00,
  "selling_price": 75.00,
  "current_stock": 100,
  "reorder_level": 20,
  "description": "Product description"
}
```

### Get Product
**GET** `/products/{id}`

### Update Product
**PUT** `/products/{id}`

### Delete Product
**DELETE** `/products/{id}`

---

## Suppliers

### List Suppliers
**GET** `/suppliers`

**Query Parameters:**
- `search` - Search by name, email, or phone
- `is_active` - Filter by active status (boolean)
- `sort_by` - Sort field (default: created_at)
- `sort_order` - asc/desc (default: desc)
- `per_page` - Items per page (default: 20)

### Create Supplier
**POST** `/suppliers`

**Request Body:**
```json
{
  "name": "Supplier Name",
  "email": "supplier@example.com",
  "phone": "+1234567890",
  "address": "123 Main St",
  "city": "City",
  "country": "Country",
  "tax_number": "TAX123",
  "is_active": true,
  "notes": "Optional notes"
}
```

### Get Supplier
**GET** `/suppliers/{id}`

Includes recent purchases and products.

### Update Supplier
**PUT** `/suppliers/{id}`

### Delete Supplier
**DELETE** `/suppliers/{id}`

Note: Cannot delete suppliers with existing transactions or products.

---

## Customers

### List Customers
**GET** `/customers`

**Query Parameters:**
- `search` - Search by name, email, or phone
- `is_active` - Filter by active status (boolean)
- `sort_by` - Sort field (default: created_at)
- `sort_order` - asc/desc (default: desc)
- `per_page` - Items per page (default: 20)

### Create Customer
**POST** `/customers`

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "address": "123 Main St",
  "city": "City",
  "country": "Country",
  "is_active": true,
  "notes": "Optional notes"
}
```

### Get Customer
**GET** `/customers/{id}`

Includes recent sales history.

### Update Customer
**PUT** `/customers/{id}`

### Delete Customer
**DELETE** `/customers/{id}`

Note: Cannot delete customers with existing sales.

---

## Purchases

### List Purchases
**GET** `/purchases`

**Query Parameters:**
- `supplier_id` - Filter by supplier
- `status` - Filter by status (pending/completed/cancelled)
- `date_from` - Filter from date (Y-m-d)
- `date_to` - Filter to date (Y-m-d)
- `sort_by` - Sort field (default: created_at)
- `sort_order` - asc/desc (default: desc)
- `per_page` - Items per page (default: 20)

### Create Purchase
**POST** `/purchases`

**Request Body:**
```json
{
  "supplier_id": 1,
  "purchase_date": "2026-03-10",
  "status": "completed",
  "payment_method": "bank_transfer",
  "notes": "Optional notes",
  "items": [
    {
      "product_id": 1,
      "quantity": 50,
      "unit_price": 45.00
    },
    {
      "product_id": 2,
      "quantity": 30,
      "unit_price": 25.00
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Purchase created successfully",
  "data": {
    "id": 1,
    "purchase_number": "PO-2026-001",
    "supplier_id": 1,
    "total_amount": 3000.00,
    "status": "completed",
    "items": [...]
  }
}
```

### Get Purchase
**GET** `/purchases/{id}`

Includes purchase items with product details.

---

## Sales

### List Sales
**GET** `/sales`

**Query Parameters:**
- `customer_id` - Filter by customer
- `status` - Filter by status (pending/completed/cancelled)
- `date_from` - Filter from date (Y-m-d)
- `date_to` - Filter to date (Y-m-d)
- `sort_by` - Sort field (default: created_at)
- `sort_order` - asc/desc (default: desc)
- `per_page` - Items per page (default: 20)

### Create Sale
**POST** `/sales`

**Request Body:**
```json
{
  "customer_id": 1,
  "sale_date": "2026-03-10",
  "status": "completed",
  "payment_method": "cash",
  "notes": "Optional notes",
  "items": [
    {
      "product_id": 1,
      "quantity": 5,
      "unit_price": 75.00
    },
    {
      "product_id": 2,
      "quantity": 3,
      "unit_price": 50.00
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Sale created successfully",
  "data": {
    "id": 1,
    "invoice_number": "INV-2026-001",
    "customer_id": 1,
    "total_amount": 525.00,
    "status": "completed",
    "items": [...]
  }
}
```

### Get Sale
**GET** `/sales/{id}`

Includes sale items with product details.

---

## Stock Adjustments

### List Stock Adjustments
**GET** `/stock-adjustments`

**Query Parameters:**
- `product_id` - Filter by product
- `type` - Filter by type (adjustment/damage/return)
- `date_from` - Filter from date (Y-m-d)
- `date_to` - Filter to date (Y-m-d)
- `per_page` - Items per page (default: 20)

### Create Stock Adjustment
**POST** `/stock-adjustments`

**Request Body:**
```json
{
  "product_id": 1,
  "quantity_change": -5,
  "type": "damage",
  "reason": "Damaged during handling",
  "notes": "Optional additional notes"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Stock adjustment recorded successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "quantity_change": -5,
    "type": "damage",
    "previous_stock": 100,
    "new_stock": 95
  }
}
```

---

## Reports

### Dashboard Summary
**GET** `/reports/dashboard`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_products": 150,
    "low_stock_products": 12,
    "total_sales_today": 5000.00,
    "total_purchases_today": 3000.00,
    "recent_sales": [...],
    "recent_purchases": [...],
    "low_stock_alerts": [...]
  }
}
```

### Sales Report
**GET** `/reports/sales`

**Query Parameters:**
- `date_from` - Start date (Y-m-d)
- `date_to` - End date (Y-m-d)
- `customer_id` - Filter by customer
- `group_by` - Group by (day/week/month)

### Purchases Report
**GET** `/reports/purchases`

**Query Parameters:**
- `date_from` - Start date (Y-m-d)
- `date_to` - End date (Y-m-d)
- `supplier_id` - Filter by supplier
- `group_by` - Group by (day/week/month)

### Stock Levels Report
**GET** `/reports/stock-levels`

Returns current stock levels for all products with low stock alerts.

### Inventory Valuation Report
**GET** `/reports/inventory-valuation`

Returns total inventory value based on unit prices and current stock.

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "quantity": ["The quantity must be greater than 0."]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Error message details"
}
```

---

## Test Accounts

```
Admin:
Email: admin@inventory.com
Password: password123

Manager:
Email: manager@inventory.com
Password: password123

Staff:
Email: staff@inventory.com
Password: password123
```

---

## Notes

1. All timestamps are in UTC
2. Pagination uses Laravel's standard format
3. All monetary values are in decimal format (2 decimal places)
4. Stock adjustments automatically update product stock levels
5. Purchase and sale creation automatically updates stock levels
6. Soft deletes are not implemented - use `is_active` flag instead
