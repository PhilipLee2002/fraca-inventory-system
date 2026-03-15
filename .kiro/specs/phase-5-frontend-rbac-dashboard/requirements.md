# Requirements Document

## Introduction

This document specifies the requirements for Phase 5 of the FRACA SERVCOM Inventory System: Complete Frontend Integration with Role-Based Access Control (RBAC) and Manager Dashboard Focus. The system builds upon an existing Laravel backend API with complete authentication, authorization, and data management capabilities. This phase delivers a fully functional, responsive web interface using HTML5, CSS3/Bootstrap 5, and vanilla JavaScript with Axios for AJAX operations.

The frontend will provide role-specific dashboards and CRUD interfaces for inventory management, with three user roles: Admin (full control), Manager (view/create/edit, requires admin verification for delete), and Staff (view only, process transactions).

## Glossary

- **Frontend_Application**: The client-side web interface built with HTML5, CSS3/Bootstrap 5, and vanilla JavaScript
- **Backend_API**: The existing Laravel REST API providing data and business logic endpoints
- **Admin_User**: A user with the "Admin" role having full system access including delete operations
- **Manager_User**: A user with the "Manager" role having view/create/edit access, requiring admin verification for delete operations
- **Staff_User**: A user with the "Staff" role having view-only access and transaction processing capabilities
- **Dashboard**: The main landing page displaying statistics, metrics, and quick actions
- **CRUD_Interface**: Create, Read, Update, Delete interface for managing system entities
- **Admin_Verification_Modal**: A modal dialog requiring admin credentials to authorize sensitive operations
- **Toast_Notification**: A temporary Bootstrap toast message displaying operation results
- **Product_Entity**: An inventory item with attributes like name, SKU, quantity, price
- **Purchase_Entity**: A record of products purchased from suppliers
- **Sale_Entity**: A record of products sold to customers
- **Stock_Adjustment_Entity**: A manual inventory quantity change with reason
- **User_Entity**: A system user with assigned role and permissions
- **Category_Entity**: A product classification grouping
- **Supplier_Entity**: A vendor providing products for purchase
- **Customer_Entity**: A buyer purchasing products
- **Axios_Client**: The HTTP client library for making AJAX requests to Backend_API
- **Bootstrap_Modal**: A Bootstrap 5 modal dialog component for forms and confirmations
- **CSRF_Token**: Cross-Site Request Forgery token for securing API requests
- **Role_Based_UI**: User interface elements that render conditionally based on user role
- **Quick_Action_Button**: A dashboard button providing one-click access to common operations
- **Statistics_Card**: A dashboard widget displaying a key performance metric
- **Activity_Timeline**: A chronological list of recent system events
- **Inventory_Health_Metric**: A calculated indicator of inventory status (stock levels, turnover, valuation)
- **Team_Performance_Widget**: A dashboard component showing staff activity and sales metrics
- **Report_Interface**: A tabular view with filters for generating business reports
- **Audit_Trail**: A log of security-sensitive operations including admin verification attempts


## Requirements

### Requirement 1: Frontend Application Foundation

**User Story:** As a developer, I want a properly configured frontend build system, so that I can develop and deploy the application efficiently.

#### Acceptance Criteria

1. THE Frontend_Application SHALL load Bootstrap 5 CSS and JavaScript from npm packages via Vite
2. THE Frontend_Application SHALL configure Axios_Client with CSRF_Token in the default request headers
3. THE Frontend_Application SHALL include a master layout template (app.blade.php) with navigation bar and footer
4. THE Frontend_Application SHALL include a guest layout template (guest.blade.php) for unauthenticated pages
5. THE Frontend_Application SHALL organize JavaScript modules in resources/js/ directory with one module per feature
6. THE Frontend_Application SHALL organize custom CSS in resources/css/app.css
7. THE Frontend_Application SHALL compile assets using Vite build tool
8. FOR ALL JavaScript modules, importing and initializing SHALL produce no console errors

### Requirement 2: Visual Design and Branding

**User Story:** As a user, I want a consistent and professional visual design, so that the application is easy to use and represents the FRACA SERVCOM brand.

#### Acceptance Criteria

1. THE Frontend_Application SHALL use white (#ffffff) as the primary background color
2. THE Frontend_Application SHALL style primary action buttons with red background (#dc3545) and white text
3. THE Frontend_Application SHALL style secondary action buttons with black background (#343a40) and white text
4. THE Frontend_Application SHALL display a footer with blue background (#007bff) and white text
5. THE Frontend_Application SHALL position the footer sticky at the bottom of the viewport
6. THE Frontend_Application SHALL display "© 2025 FRACA SERVCOM. All rights reserved." in the footer
7. THE Frontend_Application SHALL use Bootstrap 5 responsive grid system for all layouts
8. WHEN a user resizes the browser window, THE Frontend_Application SHALL maintain readable layout on screen widths from 320px to 2560px

### Requirement 3: Role-Based Access Control UI

**User Story:** As a system administrator, I want the interface to show only authorized actions for each user role, so that users cannot attempt unauthorized operations.

#### Acceptance Criteria

1. WHEN an Admin_User views any page, THE Frontend_Application SHALL display all available actions including delete operations
2. WHEN a Manager_User views any page, THE Frontend_Application SHALL display view, create, and edit actions but hide direct delete buttons
3. WHEN a Staff_User views any page, THE Frontend_Application SHALL display only view actions and transaction processing buttons
4. THE Frontend_Application SHALL use Blade @can directives to conditionally render UI elements based on user permissions
5. THE Frontend_Application SHALL pass user role and permissions to JavaScript via window.permissions object or data attributes
6. WHEN JavaScript code checks permissions, THE Frontend_Application SHALL prevent unauthorized API calls even if UI elements are manipulated
7. FOR ALL role-restricted UI elements, rendering SHALL match the Backend_API authorization rules

### Requirement 4: Toast Notification System

**User Story:** As a user, I want to see clear feedback messages for my actions, so that I know whether operations succeeded or failed.

#### Acceptance Criteria

1. THE Frontend_Application SHALL implement a showToast(message, type) JavaScript function
2. WHEN showToast is called with type "success", THE Frontend_Application SHALL display a green Bootstrap toast with the message
3. WHEN showToast is called with type "error", THE Frontend_Application SHALL display a red Bootstrap toast with the message
4. WHEN showToast is called with type "info", THE Frontend_Application SHALL display a blue Bootstrap toast with the message
5. WHEN showToast is called with type "warning", THE Frontend_Application SHALL display a yellow Bootstrap toast with the message
6. THE Frontend_Application SHALL position toasts in the top-right corner of the viewport
7. THE Frontend_Application SHALL automatically dismiss toasts after 5 seconds
8. THE Frontend_Application SHALL allow users to manually dismiss toasts by clicking a close button
9. WHEN multiple toasts are displayed, THE Frontend_Application SHALL stack them vertically without overlap

### Requirement 5: Admin Dashboard for Admin Users

**User Story:** As an Admin_User, I want a comprehensive dashboard with all system metrics and quick actions, so that I can monitor and manage the entire system efficiently.

#### Acceptance Criteria

1. WHEN an Admin_User accesses the dashboard, THE Frontend_Application SHALL fetch statistics from GET /api/dashboard/stats
2. THE Dashboard SHALL display four Statistics_Cards: Total Products, Today's Sales, Low Stock Items, Total Users
3. THE Dashboard SHALL display Monthly Performance metrics: Monthly Sales Total, Monthly Purchase Total, Profit Margin, Sales vs Target
4. THE Dashboard SHALL display Inventory_Health_Metrics: Out of Stock count, Overstock count, Stock Turnover rate, Inventory Valuation
5. THE Dashboard SHALL display Operational Metrics: Pending Sales count, Pending Purchases count, Active Alerts count, Recent Adjustments count
6. THE Dashboard SHALL display a Quick Actions grid with 12 buttons in 3 rows and 4 columns
7. THE Quick_Action_Button grid SHALL include: Add Product, Adjust Stock, Add Category, View Alerts, New Sale, New Purchase, Record Payment, Process Return, Add Customer, Add Supplier, Add User, View Reports
8. THE Dashboard SHALL display an Activity_Timeline widget showing the 10 most recent system events
9. THE Dashboard SHALL display a Top Performers widget showing users ranked by sales volume
10. THE Dashboard SHALL display a Financial Summary widget with revenue, expenses, and profit for the current month
11. THE Dashboard SHALL display a Pending Actions widget listing items requiring attention
12. WHEN an Admin_User clicks a Quick_Action_Button, THE Frontend_Application SHALL navigate to the corresponding feature or open the appropriate modal

### Requirement 6: Manager Dashboard for Manager Users

**User Story:** As a Manager_User, I want a dashboard focused on team performance and operational metrics, so that I can effectively manage staff and inventory operations.

#### Acceptance Criteria

1. WHEN a Manager_User accesses the dashboard, THE Frontend_Application SHALL fetch statistics from GET /api/dashboard/stats
2. THE Dashboard SHALL display all Statistics_Cards and metrics specified in Requirement 5 criteria 2-5
3. THE Dashboard SHALL display Team_Performance_Widget showing: Active Staff count, Sales by User breakdown, Recent Activity by staff member
4. THE Dashboard SHALL display the same Quick Actions grid as Admin_User with 12 buttons
5. THE Dashboard SHALL display the same Activity_Timeline, Top Performers, Financial Summary, and Pending Actions widgets as Admin_User
6. THE Dashboard SHALL display an Alerts & Notifications widget showing low stock warnings and system alerts
7. WHEN a Manager_User clicks a Quick_Action_Button, THE Frontend_Application SHALL navigate to the corresponding feature or open the appropriate modal

### Requirement 7: Staff Dashboard for Staff Users

**User Story:** As a Staff_User, I want a simplified dashboard with essential information and transaction processing, so that I can focus on my daily tasks without distraction.

#### Acceptance Criteria

1. WHEN a Staff_User accesses the dashboard, THE Frontend_Application SHALL fetch statistics from GET /api/dashboard/stats
2. THE Dashboard SHALL display three Statistics_Cards: Total Products, Today's Sales, Low Stock Items
3. THE Dashboard SHALL display a Recent Transactions widget showing the 10 most recent sales and purchases
4. THE Dashboard SHALL display Quick_Action_Buttons for: New Sale, New Purchase, View Products
5. THE Dashboard SHALL NOT display User Management, Reports, or administrative widgets
6. WHEN a Staff_User clicks a Quick_Action_Button, THE Frontend_Application SHALL navigate to the corresponding feature or open the appropriate modal

### Requirement 8: Products CRUD Interface

**User Story:** As a user, I want to manage product inventory, so that I can maintain accurate product information and stock levels.

#### Acceptance Criteria

1. WHEN a user accesses the products page, THE Frontend_Application SHALL fetch products from GET /api/products and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: SKU, Name, Category, Quantity, Unit Price, Actions
3. THE CRUD_Interface SHALL include a search input that filters products by name or SKU as the user types
4. THE CRUD_Interface SHALL include a category dropdown filter that shows only products in the selected category
5. WHEN an Admin_User or Manager_User clicks "Add Product", THE Frontend_Application SHALL open a Bootstrap_Modal with a form
6. THE Product form SHALL include fields: Name, SKU, Description, Category (dropdown), Supplier (dropdown), Unit Price, Quantity, Reorder Level
7. WHEN a user submits the Add Product form, THE Frontend_Application SHALL POST to /api/products and display a Toast_Notification with the result
8. WHEN an Admin_User or Manager_User clicks "Edit" on a product, THE Frontend_Application SHALL open a Bootstrap_Modal pre-filled with product data
9. WHEN a user submits the Edit Product form, THE Frontend_Application SHALL PUT to /api/products/{id} and display a Toast_Notification with the result
10. WHEN an Admin_User clicks "Delete" on a product, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/products/{id}
11. WHEN a Manager_User clicks "Delete" on a product, THE Frontend_Application SHALL open the Admin_Verification_Modal
12. WHEN the product list is updated, THE Frontend_Application SHALL refresh the table without page reload
13. FOR ALL product form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 9: Purchases CRUD Interface

**User Story:** As a user, I want to record and manage purchase orders, so that I can track inventory acquisitions from suppliers.

#### Acceptance Criteria

1. WHEN a user accesses the purchases page, THE Frontend_Application SHALL fetch purchases from GET /api/purchases and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: Purchase Number, Supplier, Date, Total Amount, Status, Actions
3. WHEN a user with create permission clicks "New Purchase", THE Frontend_Application SHALL open a Bootstrap_Modal with a purchase form
4. THE Purchase form SHALL include: Supplier (dropdown), Purchase Date (date picker), Status (dropdown: pending/completed/cancelled)
5. THE Purchase form SHALL include a dynamic product rows section with "Add Product" button
6. WHEN a user clicks "Add Product" in the purchase form, THE Frontend_Application SHALL add a new row with: Product (dropdown), Quantity (number), Unit Price (number), Subtotal (calculated)
7. THE Purchase form SHALL calculate and display Total Amount as the sum of all product subtotals
8. WHEN a user submits the purchase form, THE Frontend_Application SHALL POST to /api/purchases with purchase header and items array
9. WHEN a user clicks "View" on a purchase, THE Frontend_Application SHALL open a Bootstrap_Modal displaying purchase details and line items in read-only format
10. WHEN an Admin_User clicks "Delete" on a purchase, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/purchases/{id}
11. WHEN a Manager_User clicks "Delete" on a purchase, THE Frontend_Application SHALL open the Admin_Verification_Modal
12. WHEN the purchase list is updated, THE Frontend_Application SHALL refresh the table without page reload
13. FOR ALL purchase form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 10: Sales CRUD Interface

**User Story:** As a user, I want to record and manage sales transactions, so that I can track inventory sold to customers.

#### Acceptance Criteria

1. WHEN a user accesses the sales page, THE Frontend_Application SHALL fetch sales from GET /api/sales and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: Sale Number, Customer, Date, Total Amount, Status, Actions
3. WHEN a user with create permission clicks "New Sale", THE Frontend_Application SHALL open a Bootstrap_Modal with a sale form
4. THE Sale form SHALL include: Customer (dropdown), Sale Date (date picker), Status (dropdown: pending/completed/cancelled)
5. THE Sale form SHALL include a dynamic product rows section with "Add Product" button
6. WHEN a user clicks "Add Product" in the sale form, THE Frontend_Application SHALL add a new row with: Product (dropdown), Quantity (number), Unit Price (number), Subtotal (calculated)
7. THE Sale form SHALL calculate and display Total Amount as the sum of all product subtotals
8. WHEN a user submits the sale form, THE Frontend_Application SHALL POST to /api/sales with sale header and items array
9. WHEN a user clicks "View" on a sale, THE Frontend_Application SHALL open a Bootstrap_Modal displaying sale details and line items in read-only format
10. WHEN an Admin_User clicks "Delete" on a sale, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/sales/{id}
11. WHEN a Manager_User clicks "Delete" on a sale, THE Frontend_Application SHALL open the Admin_Verification_Modal
12. WHEN the sale list is updated, THE Frontend_Application SHALL refresh the table without page reload
13. FOR ALL sale form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 11: Stock Adjustments Interface

**User Story:** As an Admin_User or Manager_User, I want to manually adjust inventory quantities, so that I can correct discrepancies and record stock changes.

#### Acceptance Criteria

1. WHEN an Admin_User or Manager_User accesses the stock adjustments page, THE Frontend_Application SHALL display a form with: Product (dropdown), Adjustment Type (dropdown: increase/decrease), Quantity (number), Reason (textarea)
2. WHEN a user submits the stock adjustment form, THE Frontend_Application SHALL POST to /api/stock-adjustments
3. WHEN the stock adjustment is successful, THE Frontend_Application SHALL display a success Toast_Notification and clear the form
4. WHEN the stock adjustment fails, THE Frontend_Application SHALL display an error Toast_Notification with the error message
5. THE Frontend_Application SHALL display a table of recent stock adjustments below the form
6. THE Stock_Adjustment_Entity table SHALL display columns: Date, Product, Type, Quantity, Reason, User
7. THE Frontend_Application SHALL fetch recent adjustments from GET /api/stock-adjustments
8. FOR ALL stock adjustment submissions, validation errors SHALL display inline below the relevant form field

### Requirement 12: Reports Interface

**User Story:** As an Admin_User or Manager_User, I want to generate business reports, so that I can analyze inventory performance and make informed decisions.

#### Acceptance Criteria

1. WHEN an Admin_User or Manager_User accesses the reports page, THE Frontend_Application SHALL display report type tabs: Inventory Valuation, Sales by Period, Profit/Loss, Stock Movement
2. WHEN a user selects a report type, THE Frontend_Application SHALL display relevant filter controls: Date Range (start/end date pickers), Category (dropdown), Supplier (dropdown), Customer (dropdown)
3. WHEN a user clicks "Generate Report", THE Frontend_Application SHALL fetch data from the appropriate GET /api/reports/* endpoint with filter parameters
4. THE Report_Interface SHALL display results in a Bootstrap table with appropriate columns for the report type
5. THE Report_Interface SHALL include an "Export to CSV" button above the table
6. WHEN a user clicks "Export to CSV", THE Frontend_Application SHALL convert the table data to CSV format and trigger a browser download
7. THE Inventory Valuation report SHALL display columns: Product, Category, Quantity, Unit Cost, Total Value
8. THE Sales by Period report SHALL display columns: Date, Product, Quantity Sold, Revenue, Profit
9. THE Profit/Loss report SHALL display columns: Period, Revenue, Cost of Goods Sold, Gross Profit, Expenses, Net Profit
10. THE Stock Movement report SHALL display columns: Product, Opening Stock, Purchases, Sales, Adjustments, Closing Stock
11. WHEN report data is loading, THE Frontend_Application SHALL display a loading spinner
12. WHEN a report has no data, THE Frontend_Application SHALL display "No data available for the selected criteria"

### Requirement 13: User Management Interface

**User Story:** As an Admin_User, I want to manage system users and their roles, so that I can control access to the system.

#### Acceptance Criteria

1. WHEN an Admin_User accesses the users page, THE Frontend_Application SHALL fetch users from GET /api/users and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: Name, Email, Role, Status, Actions
3. WHEN an Admin_User or Manager_User clicks "Add User", THE Frontend_Application SHALL open a Bootstrap_Modal with a user form
4. THE User form SHALL include fields: Name, Email, Password, Password Confirmation, Role (dropdown: Admin/Manager/Staff), Status (dropdown: active/inactive)
5. WHEN a user submits the Add User form, THE Frontend_Application SHALL POST to /api/users and display a Toast_Notification with the result
6. WHEN an Admin_User or Manager_User clicks "Edit" on a user, THE Frontend_Application SHALL open a Bootstrap_Modal pre-filled with user data
7. THE Edit User form SHALL include all fields from Add User form except password fields are optional
8. WHEN a user submits the Edit User form, THE Frontend_Application SHALL PUT to /api/users/{id} and display a Toast_Notification with the result
9. WHEN an Admin_User clicks "Delete" on a user, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/users/{id}
10. WHEN a Manager_User views the users page, THE Frontend_Application SHALL hide delete buttons
11. WHEN the user list is updated, THE Frontend_Application SHALL refresh the table without page reload
12. FOR ALL user form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 14: Admin Verification for Manager Delete Operations

**User Story:** As a system administrator, I want managers to require admin credentials for delete operations, so that sensitive deletions are properly authorized and audited.

#### Acceptance Criteria

1. WHEN a Manager_User attempts to delete any entity, THE Frontend_Application SHALL open the Admin_Verification_Modal
2. THE Admin_Verification_Modal SHALL display title "Admin Verification Required"
3. THE Admin_Verification_Modal SHALL display message "This action requires administrator credentials. Please enter an admin email and password to continue."
4. THE Admin_Verification_Modal SHALL include fields: Admin Email (email input), Admin Password (password input)
5. WHEN a Manager_User submits admin credentials, THE Frontend_Application SHALL POST to /api/verify-admin with email and password
6. WHEN admin verification succeeds, THE Frontend_Application SHALL proceed with the delete operation and close the modal
7. WHEN admin verification fails, THE Frontend_Application SHALL display an error Toast_Notification "Invalid admin credentials" and keep the modal open
8. THE Backend_API SHALL log all admin verification attempts to the Audit_Trail with: timestamp, manager user ID, admin email attempted, IP address, success/failure status
9. THE Admin_Verification_Modal SHALL include a "Cancel" button that closes the modal without performing the delete operation
10. WHEN admin verification succeeds and delete operation completes, THE Frontend_Application SHALL display a success Toast_Notification

### Requirement 15: Categories Management Interface

**User Story:** As an Admin_User or Manager_User, I want to manage product categories, so that I can organize products logically.

#### Acceptance Criteria

1. WHEN an Admin_User or Manager_User accesses the categories page, THE Frontend_Application SHALL fetch categories from GET /api/categories and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: Name, Description, Product Count, Actions
3. WHEN a user clicks "Add Category", THE Frontend_Application SHALL open a Bootstrap_Modal with a category form
4. THE Category form SHALL include fields: Name, Description (textarea)
5. WHEN a user submits the Add Category form, THE Frontend_Application SHALL POST to /api/categories and display a Toast_Notification with the result
6. WHEN a user clicks "Edit" on a category, THE Frontend_Application SHALL open a Bootstrap_Modal pre-filled with category data
7. WHEN a user submits the Edit Category form, THE Frontend_Application SHALL PUT to /api/categories/{id} and display a Toast_Notification with the result
8. WHEN an Admin_User clicks "Delete" on a category, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/categories/{id}
9. WHEN a Manager_User clicks "Delete" on a category, THE Frontend_Application SHALL open the Admin_Verification_Modal
10. FOR ALL category form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 16: Suppliers Management Interface

**User Story:** As an Admin_User or Manager_User, I want to manage supplier information, so that I can track vendors and their contact details.

#### Acceptance Criteria

1. WHEN an Admin_User or Manager_User accesses the suppliers page, THE Frontend_Application SHALL fetch suppliers from GET /api/suppliers and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: Name, Contact Person, Email, Phone, Actions
3. WHEN a user clicks "Add Supplier", THE Frontend_Application SHALL open a Bootstrap_Modal with a supplier form
4. THE Supplier form SHALL include fields: Name, Contact Person, Email, Phone, Address (textarea)
5. WHEN a user submits the Add Supplier form, THE Frontend_Application SHALL POST to /api/suppliers and display a Toast_Notification with the result
6. WHEN a user clicks "Edit" on a supplier, THE Frontend_Application SHALL open a Bootstrap_Modal pre-filled with supplier data
7. WHEN a user submits the Edit Supplier form, THE Frontend_Application SHALL PUT to /api/suppliers/{id} and display a Toast_Notification with the result
8. WHEN an Admin_User clicks "Delete" on a supplier, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/suppliers/{id}
9. WHEN a Manager_User clicks "Delete" on a supplier, THE Frontend_Application SHALL open the Admin_Verification_Modal
10. FOR ALL supplier form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 17: Customers Management Interface

**User Story:** As an Admin_User or Manager_User, I want to manage customer information, so that I can track buyers and their contact details.

#### Acceptance Criteria

1. WHEN an Admin_User or Manager_User accesses the customers page, THE Frontend_Application SHALL fetch customers from GET /api/customers and display them in a Bootstrap table
2. THE CRUD_Interface SHALL display columns: Name, Email, Phone, Total Purchases, Actions
3. WHEN a user clicks "Add Customer", THE Frontend_Application SHALL open a Bootstrap_Modal with a customer form
4. THE Customer form SHALL include fields: Name, Email, Phone, Address (textarea)
5. WHEN a user submits the Add Customer form, THE Frontend_Application SHALL POST to /api/customers and display a Toast_Notification with the result
6. WHEN a user clicks "Edit" on a customer, THE Frontend_Application SHALL open a Bootstrap_Modal pre-filled with customer data
7. WHEN a user submits the Edit Customer form, THE Frontend_Application SHALL PUT to /api/customers/{id} and display a Toast_Notification with the result
8. WHEN an Admin_User clicks "Delete" on a customer, THE Frontend_Application SHALL show a confirmation modal then DELETE /api/customers/{id}
9. WHEN a Manager_User clicks "Delete" on a customer, THE Frontend_Application SHALL open the Admin_Verification_Modal
10. FOR ALL customer form submissions, validation errors SHALL display inline below the relevant form field

### Requirement 18: Error Handling and User Feedback

**User Story:** As a user, I want clear error messages when operations fail, so that I understand what went wrong and how to fix it.

#### Acceptance Criteria

1. WHEN any API request fails with a network error, THE Frontend_Application SHALL display an error Toast_Notification "Network error. Please check your connection."
2. WHEN any API request returns a 401 Unauthorized response, THE Frontend_Application SHALL redirect to the login page
3. WHEN any API request returns a 403 Forbidden response, THE Frontend_Application SHALL display an error Toast_Notification "You do not have permission to perform this action."
4. WHEN any API request returns a 404 Not Found response, THE Frontend_Application SHALL display an error Toast_Notification "The requested resource was not found."
5. WHEN any API request returns a 422 Validation Error response, THE Frontend_Application SHALL display validation errors inline below the relevant form fields
6. WHEN any API request returns a 500 Server Error response, THE Frontend_Application SHALL display an error Toast_Notification "Server error. Please try again later."
7. WHEN a form submission is in progress, THE Frontend_Application SHALL disable the submit button and display a loading spinner
8. WHEN a data fetch operation is in progress, THE Frontend_Application SHALL display a loading spinner in the content area
9. THE Frontend_Application SHALL log all JavaScript errors to the browser console for debugging
10. FOR ALL error scenarios, the Frontend_Application SHALL maintain application state and allow users to retry operations

### Requirement 19: Responsive Design and Mobile Support

**User Story:** As a user, I want the application to work well on mobile devices, so that I can manage inventory from anywhere.

#### Acceptance Criteria

1. WHEN a user accesses the application on a screen width less than 768px, THE Frontend_Application SHALL display a mobile-optimized navigation menu
2. WHEN a user accesses the application on a screen width less than 768px, THE Dashboard SHALL stack Statistics_Cards vertically
3. WHEN a user accesses the application on a screen width less than 768px, THE Quick Actions grid SHALL display 2 buttons per row instead of 4
4. WHEN a user accesses data tables on a screen width less than 768px, THE Frontend_Application SHALL enable horizontal scrolling for the table
5. WHEN a user opens a Bootstrap_Modal on a screen width less than 768px, THE modal SHALL occupy 95% of the screen width
6. THE Frontend_Application SHALL use responsive Bootstrap classes (col-sm, col-md, col-lg) for all grid layouts
7. THE Frontend_Application SHALL ensure all buttons and interactive elements have a minimum touch target size of 44x44 pixels on mobile devices
8. WHEN a user accesses forms on mobile devices, THE Frontend_Application SHALL use appropriate input types (email, tel, number, date) to trigger correct mobile keyboards

### Requirement 20: Performance and Optimization

**User Story:** As a user, I want the application to load and respond quickly, so that I can work efficiently without delays.

#### Acceptance Criteria

1. THE Frontend_Application SHALL lazy-load JavaScript modules only when the corresponding feature is accessed
2. THE Frontend_Application SHALL cache API responses for reference data (categories, suppliers, customers) for 5 minutes
3. WHEN a user performs a search or filter operation, THE Frontend_Application SHALL debounce the input with a 300ms delay before making API requests
4. THE Frontend_Application SHALL paginate data tables showing more than 50 records
5. THE Frontend_Application SHALL compress and minify JavaScript and CSS assets in production builds
6. THE Frontend_Application SHALL use Vite code splitting to generate separate bundles for each feature module
7. WHEN the application loads, THE Frontend_Application SHALL display the initial page content within 2 seconds on a standard broadband connection
8. THE Frontend_Application SHALL limit the Activity_Timeline widget to display a maximum of 10 recent events
9. THE Frontend_Application SHALL implement virtual scrolling for dropdowns with more than 100 options
10. FOR ALL API requests, the Axios_Client SHALL include appropriate cache-control headers

