# Implementation Plan: Phase 5 Frontend RBAC Dashboard

## Overview

This implementation plan converts the Phase 5 frontend design into actionable coding tasks. The implementation uses HTML5, CSS3/Bootstrap 5, and vanilla JavaScript with Axios for API communication. Each task builds incrementally on previous work, with checkpoints to ensure quality and allow for user feedback.

The implementation follows a modular architecture with feature-specific JavaScript modules, shared utilities, and a centralized API client. All tasks reference specific requirements for traceability.

## Tasks

- [x] 0. Environment Setup and Foundation
  - [x] 0.1 Verify backend API endpoints and middleware
    - Confirm CheckRole middleware is registered in app/Http/Kernel.php
    - Test all API endpoints documented in API_DOCUMENTATION.md
    - Verify CSRF token generation is working
    - _Requirements: 1.1, 1.2, 1.3_
  
  - [x] 0.2 Configure Vite and asset compilation
    - Ensure Bootstrap 5 is installed via npm
    - Configure Vite to compile resources/css/app.css and resources/js/app.js
    - Add Axios to package.json dependencies
    - Test asset compilation with `npm run dev`
    - _Requirements: 1.1, 1.7_
  
  - [x] 0.3 Create master layout template
    - Create resources/views/layouts/app.blade.php with navbar, content area, and footer
    - Add CSRF token meta tag in head section
    - Include Vite directives for CSS and JS
    - Pass user and permissions data to JavaScript via window object
    - _Requirements: 1.3, 3.5_

  - [x] 0.4 Create layout partials
    - Create resources/views/partials/navbar.blade.php with role-based menu items using @can directives
    - Create resources/views/partials/footer.blade.php with blue background, white text, sticky positioning, and copyright notice
    - Create resources/views/partials/toast-container.blade.php for Bootstrap toast notifications
    - Create resources/views/partials/admin-verify-modal.blade.php as reusable component with email and password fields
    - Admin verification modal will be used in all delete flows for Manager users
    - Log all admin verification attempts (backend handles logging)
    - _Requirements: 2.4, 2.5, 2.6, 3.4, 14.2, 14.3, 14.4_
  
  - [x] 0.5 Add custom CSS styling
    - Create resources/css/app.css with custom styles
    - Add primary button styling (red background #dc3545, white text)
    - Add secondary button styling (black background #343a40, white text)
    - Add footer styling (blue background #007bff, white text, sticky positioning)
    - Add responsive utilities and mobile touch target sizing
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 19.7_
  
  - [x] 0.6 Create core JavaScript utilities
    - Create resources/js/utils/toast.js with showToast(message, type) function
    - Create resources/js/utils/permissions.js with hasPermission() and hasRole() functions
    - Create resources/js/utils/validation.js with form validation helpers
    - Create resources/js/utils/modal.js with showAdminVerifyModal() function
    - Import all utilities in resources/js/app.js
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 14.1, 14.2_
  
  - [x] 0.7 Configure Axios API client
    - Create resources/js/api/client.js with Axios instance configuration
    - Add CSRF token to default headers
    - Implement request interceptor for auth token and loading states
    - Implement response interceptor for error handling (401, 403, 404, 422, 500)
    - Add automatic redirect to login on 401 Unauthorized
    - _Requirements: 1.2, 18.1, 18.2, 18.3, 18.4, 18.5, 18.6_

- [x] 1. Dashboard - Basic Stats and Layout
  - [x] 1.1 Create dashboard module and basic structure
    - Create resources/js/modules/dashboard.js with DashboardModule class
    - Create resources/views/dashboard/index.blade.php extending app layout
    - Add placeholder cards for statistics in 4-column Bootstrap grid
    - Import dashboard module in resources/js/app.js with conditional initialization
    - _Requirements: 5.1, 5.2, 6.1, 6.2, 7.1, 7.2_
  
  - [x] 1.2 Implement basic statistics fetching
    - Add fetchStats() method to DashboardModule to call GET /api/dashboard/stats
    - Display loading skeleton while fetching data
    - Populate four basic statistics cards: Total Products, Today's Sales, Low Stock Items, Total Users
    - Handle API errors with toast notifications
    - _Requirements: 5.1, 5.2, 7.2, 18.1_
  
  - [x] 1.3 Add auto-refresh for dashboard stats
    - Implement 30-second auto-refresh interval for statistics
    - Add visual indicator when data is refreshing
    - Clear interval on page unload
    - _Requirements: 5.1, 20.8_

- [x] 2. Dashboard - Manager Enhancements (Stats & Quick Actions)
  - [x] 2.1 Add extended metrics for Manager and Admin roles
    - Use @can directives to conditionally show extended metrics sections
    - Add Monthly Performance cards: Monthly Sales, Monthly Purchases, Profit Margin, Sales vs Target
    - Add Inventory Health cards: Out of Stock, Overstock, Stock Turnover, Inventory Valuation
    - Add Operational Metrics cards: Pending Sales, Pending Purchases, Active Alerts, Recent Adjustments
    - _Requirements: 5.3, 5.4, 5.5, 6.3, 6.4, 6.5_
  
  - [x] 2.2 Build Quick Actions grid
    - Create 4-column x 3-row grid of Quick Action buttons in dashboard
    - Add 12 buttons: Add Product, Adjust Stock, Add Category, View Alerts, New Sale, New Purchase, Record Payment, Process Return, Add Customer, Add Supplier, Add User, View Reports
    - Style buttons with red/black color scheme using custom CSS classes
    - Use @can directives to show/hide buttons based on permissions
    - Link each button to corresponding page or modal
    - _Requirements: 5.6, 5.7, 5.12, 6.4, 6.6, 7.4_

- [x] 3. Dashboard - Widgets
  - [x] 3.1 Add Activity Timeline widget
    - Fetch recent activities from GET /api/recent-activity
    - Display last 10 events with timestamp, user, action, and entity
    - Format as a scrollable timeline with Bootstrap list group
    - Conditionally show for Manager/Admin only using @can
    - _Requirements: 5.8, 6.5_
  
  - [x] 3.2 Add Top Performers widget
    - Fetch top performers data from dashboard stats API
    - Display top 5 users with sales count and total amount
    - Format as Bootstrap table or card list
    - Conditionally show for Manager/Admin only
    - _Requirements: 5.9, 6.5_
  
  - [x] 3.3 Add Financial Summary widget
    - Display revenue, expenses, and profit for current month
    - Use data from dashboard stats API
    - Format with color-coded indicators (green for profit, red for loss)
    - Conditionally show for Manager/Admin only
    - _Requirements: 5.10, 6.5_
  
  - [x] 3.4 Add Pending Actions widget
    - Display items requiring attention (low stock, pending approvals)
    - Make items clickable to navigate to detail pages
    - Use Bootstrap badges for visual distinction
    - Conditionally show for Manager/Admin only
    - _Requirements: 5.11, 6.5_
  
  - [x] 3.5 Add Alerts & Notifications widget for Manager
    - Fetch alerts from GET /api/alerts
    - Display low stock warnings and system alerts
    - Use Bootstrap alert components with appropriate colors
    - Show only for Manager role using @can
    - _Requirements: 6.6_

- [x] 4. Products - List & AJAX Fetch
  - [x] 4.1 Create products page and module
    - Create resources/views/products/index.blade.php extending app layout
    - Create resources/js/modules/products.js with ProductsModule class
    - Add Bootstrap table with columns: SKU, Name, Category, Quantity, Unit Price, Actions
    - Add table-responsive wrapper for mobile support
    - Import products module in app.js with conditional initialization
    - _Requirements: 8.1, 8.2, 19.4_

  - [x] 4.2 Implement products list fetching and rendering
    - Add loadProducts() method to fetch from GET /api/products
    - Display loading spinner in table body while fetching
    - Populate table dynamically with product data using innerHTML
    - Color-code quantity column (red if at or below reorder level)
    - Handle empty state with "No products found" message
    - Handle API errors with toast notifications
    - _Requirements: 8.1, 8.12, 18.1_
  
  - [x] 4.3 Add search and filter functionality
    - Add search input that filters by name or SKU
    - Add category dropdown filter populated from GET /api/categories
    - Add supplier dropdown filter populated from GET /api/suppliers
    - Implement debounced search (300ms delay) to prevent excessive API calls
    - Refresh product list when filters change
    - _Requirements: 8.3, 8.4, 20.3_

- [x] 5. Products - Add Product Modal
  - [x] 5.1 Create Add Product modal HTML
    - Add modal HTML in products/index.blade.php (hidden by default)
    - Add form fields: Name, SKU, Description, Category dropdown, Supplier dropdown, Unit Price, Quantity, Reorder Level
    - Add invalid-feedback divs below each input for validation errors
    - Add "Add Product" button visible only to Admin/Manager using @can
    - _Requirements: 8.5, 8.6, 8.13_
  
  - [x] 5.2 Implement Add Product modal functionality
    - Add showProductModal() method to open modal and reset form
    - Populate category and supplier dropdowns from API
    - Add saveProduct() method to POST to /api/products
    - On success, refresh product list and show success toast
    - On validation error (422), display inline errors using displayValidationErrors()
    - Disable submit button and show spinner during submission
    - _Requirements: 8.5, 8.6, 8.7, 8.13, 18.5, 18.7_

- [x] 6. Products - Edit Product Modal
  - [x] 6.1 Add Edit button and functionality
    - Add "Edit" button (pencil icon) in each table row visible to Admin/Manager using @can
    - On click, fetch product data from GET /api/products/{id}
    - Pre-fill modal form with product data
    - Reuse the same modal as Add Product
    - _Requirements: 8.8, 8.9_
  
  - [x] 6.2 Implement Edit Product submission
    - Submit to PUT /api/products/{id} when editing
    - On success, refresh list and show toast
    - Handle validation errors inline
    - Clear form when opening for add vs edit
    - _Requirements: 8.9, 8.12, 8.13_

- [x] 7. Products - Delete with Admin Verification
  - [x] 7.1 Implement delete functionality for Admin
    - Add "Delete" button (trash icon) in each row visible to Admin/Manager
    - For Admin: show confirmation modal, then send DELETE request to /api/products/{id}
    - On success, remove row from table and show success toast
    - Handle errors with error toast
    - _Requirements: 8.10, 14.10_
  
  - [x] 7.2 Implement delete with admin verification for Manager
    - For Manager: on delete click, open Admin Verification Modal
    - Submit credentials to POST /api/verify-admin
    - If verification succeeds, proceed with DELETE request
    - If verification fails, show error toast and keep modal open
    - Only attempt delete after successful verification
    - _Requirements: 8.11, 14.1, 14.5, 14.6, 14.7, 14.9, 14.10_

- [x] 8. Checkpoint - Products CRUD Complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Purchases - List & Create Modal
  - [x] 9.1 Create purchases page and module
    - Create resources/views/purchases/index.blade.php extending app layout
    - Create resources/js/modules/purchases.js with PurchasesModule class
    - Add Bootstrap table with columns: Purchase Number, Supplier, Date, Total Amount, Status, Actions
    - Import purchases module in app.js
    - _Requirements: 9.1, 9.2_
  
  - [x] 9.2 Implement purchases list fetching
    - Fetch purchases from GET /api/purchases
    - Display in table with formatted currency and color-coded status badges
    - Add loading spinner and empty state handling
    - Handle API errors with toast notifications
    - _Requirements: 9.1, 9.12_
  
  - [x] 9.3 Create Purchase modal with dynamic line items
    - Add modal HTML with header section: Supplier dropdown, Purchase Date, Status dropdown
    - Add dynamic product rows section with table: Product, Quantity, Unit Price, Subtotal
    - Add "Add Product" button to insert new rows
    - Add remove button (×) for each row
    - Add total amount display in table footer
    - Show "New Purchase" button only to users with create permission using @can
    - _Requirements: 9.3, 9.4, 9.5, 9.6, 9.13_
  
  - [x] 9.4 Implement dynamic line items functionality
    - Populate supplier dropdown from GET /api/suppliers
    - Populate product dropdowns in each row from GET /api/products
    - Add addProductRow() method to dynamically insert new product rows
    - Implement automatic subtotal calculation: Quantity × Unit Price
    - Implement automatic total calculation: Sum of all subtotals
    - Recalculate on any quantity or price change
    - Allow removing rows with remove button
    - _Requirements: 9.5, 9.6, 9.7_

  - [x] 9.5 Implement Purchase submission
    - Submit to POST /api/purchases with header and items array
    - Format data: { supplier_id, purchase_date, status, items: [{product_id, quantity, unit_price}] }
    - On success, close modal, refresh list, show success toast
    - On validation error, display inline errors
    - Disable submit button during submission
    - _Requirements: 9.8, 9.13, 18.7_
  
  - [x] 9.6 Add View Purchase modal
    - Add "View" button in each row
    - Fetch purchase details from GET /api/purchases/{id}
    - Display purchase header and line items in read-only format
    - Show calculated totals
    - No edit capabilities in view mode
    - _Requirements: 9.9_
  
  - [x] 9.7 Implement delete for purchases
    - For Admin: confirmation modal then DELETE /api/purchases/{id}
    - For Manager: admin verification modal then DELETE
    - Refresh list on success
    - _Requirements: 9.10, 9.11, 14.1_

- [x] 10. Sales - List & Create Modal
  - [x] 10.1 Create sales page and module
    - Create resources/views/sales/index.blade.php extending app layout
    - Create resources/js/modules/sales.js with SalesModule class
    - Add Bootstrap table with columns: Invoice Number, Customer, Date, Total Amount, Status, Actions
    - Import sales module in app.js
    - _Requirements: 10.1, 10.2_
  
  - [x] 10.2 Implement sales list fetching
    - Fetch sales from GET /api/sales
    - Display in table with formatted currency and status badges
    - Add loading spinner and empty state handling
    - _Requirements: 10.1, 10.12_
  
  - [x] 10.3 Create Sale modal with dynamic line items
    - Add modal HTML with header: Customer dropdown, Sale Date, Status dropdown
    - Add dynamic product rows: Product, Quantity, Unit Price, Subtotal
    - Add "Add Product" button and remove buttons
    - Add total amount display
    - Show "New Sale" button with permission check
    - _Requirements: 10.3, 10.4, 10.5, 10.6, 10.13_
  
  - [x] 10.4 Implement dynamic line items for sales
    - Populate customer dropdown from GET /api/customers
    - Populate product dropdowns from GET /api/products (filter products with stock > 0)
    - On quantity change, validate against available stock and show warning if insufficient
    - Auto-fill price when product is selected
    - Implement automatic calculations (subtotal and total)
    - Allow adding/removing rows
    - _Requirements: 10.5, 10.6, 10.7_
  
  - [x] 10.5 Implement Sale submission
    - Submit to POST /api/sales with header and items array
    - Handle success and validation errors
    - Refresh list on success
    - _Requirements: 10.8, 10.13_

  - [x] 10.6 Add View Sale modal and delete functionality
    - Add "View" button to display sale details in read-only format
    - Fetch sale details from GET /api/sales/{id}
    - Implement delete with admin verification for Manager
    - Implement direct delete for Admin
    - _Requirements: 10.9, 10.10, 10.11_

- [x] 11. Checkpoint - Transactions Complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 12. Stock Adjustments Interface
  - [x] 12.1 Create stock adjustments page and module
    - Create resources/views/stock-adjustments/index.blade.php
    - Create resources/js/modules/stock-adjustments.js
    - Add adjustment form at top: Product dropdown, Quantity Change (integer input), Reason (textarea)
    - Restrict access to Admin/Manager only using @can directives
    - Add recent adjustments table below form
    - _Requirements: 11.1, 11.5, 11.6_
  
  - [x] 12.2 Implement stock adjustment submission
    - Populate product dropdown from GET /api/products
    - Submit to POST /api/stock-adjustments
    - On success, show toast, clear form, refresh adjustments table
    - On error, show error toast with message
    - Handle validation errors inline
    - _Requirements: 11.2, 11.3, 11.4, 11.8_
  
  - [x] 12.3 Display recent adjustments
    - Fetch from GET /api/stock-adjustments
    - Display columns: Date, Product, Type, Quantity Change, Reason, User
    - Color-code quantity changes: green for increases, red for decreases
    - Display last 20 adjustments
    - No edit or delete capabilities (audit trail)
    - _Requirements: 11.6, 11.7_

- [x] 13. Reports Interface
  - [x] 13.1 Create reports page and module
    - Create resources/views/reports/index.blade.php
    - Create resources/js/modules/reports.js with ReportsModule class
    - Add Bootstrap tabs for report types: Inventory Valuation, Sales by Period, Profit/Loss, Stock Movement
    - Add filter controls section (dynamic based on report type)
    - Add "Generate Report" and "Export to CSV" buttons
    - Add results table section
    - _Requirements: 12.1, 12.2, 12.5_
  
  - [x] 13.2 Implement Inventory Valuation report
    - Add filters: Category dropdown, Supplier dropdown
    - Fetch from GET /api/reports/inventory-valuation with filter parameters
    - Display columns: Product, Category, Quantity, Unit Cost, Total Value
    - Show loading spinner during fetch
    - Handle empty state
    - _Requirements: 12.3, 12.4, 12.7, 12.11, 12.12_

  - [x] 13.3 Implement Sales by Period report
    - Add filters: Date Range (start/end date pickers), Customer dropdown, Category dropdown
    - Fetch from GET /api/reports/sales with parameters
    - Display columns: Date, Product, Quantity Sold, Revenue, Profit
    - _Requirements: 12.3, 12.4, 12.8_
  
  - [x] 13.4 Implement Profit/Loss report
    - Add filters: Date Range, Group By (day/week/month) dropdown
    - Fetch from GET /api/reports/profit-loss
    - Display columns: Period, Revenue, Cost of Goods Sold, Gross Profit, Expenses, Net Profit
    - _Requirements: 12.3, 12.4, 12.9_
  
  - [x] 13.5 Implement Stock Movement report
    - Add filters: Date Range, Product dropdown, Category dropdown
    - Fetch from GET /api/reports/stock-movement
    - Display columns: Product, Opening Stock, Purchases, Sales, Adjustments, Closing Stock
    - _Requirements: 12.3, 12.4, 12.10_
  
  - [x] 13.6 Implement CSV export functionality
    - Create exportToCSV() utility function in utils/export.js
    - Convert table data to CSV format with proper escaping
    - Trigger browser download with filename including date
    - Add "Export" button above results table (visible to Admin/Manager only)
    - Note: Backend should provide export endpoint if PDF export is needed
    - _Requirements: 12.5, 12.6_

- [x] 14. User Management Interface
  - [x] 14.1 Create users page and module
    - Create resources/views/users/index.blade.php
    - Create resources/js/modules/users.js with UsersModule class
    - Add Bootstrap table with columns: Name, Email, Role, Status, Actions
    - Import users module in app.js
    - _Requirements: 13.1, 13.2_
  
  - [x] 14.2 Implement users list fetching
    - Fetch users from GET /api/users (Admin only, Manager can view but may have limited access)
    - Display in table with role and status badges
    - Add loading spinner and empty state
    - _Requirements: 13.1, 13.11_
  
  - [x] 14.3 Create Add/Edit User modal
    - Add modal with fields: Name, Email, Password, Password Confirmation, Role dropdown, Status dropdown
    - Password fields optional for edit mode
    - Show "Add User" button to Admin/Manager using @can
    - _Requirements: 13.3, 13.4, 13.6, 13.7, 13.12_
  
  - [x] 14.4 Implement user creation and editing
    - Submit to POST /api/users for new users
    - Submit to PUT /api/users/{id} for editing (password optional)
    - Handle validation errors inline
    - Refresh list on success
    - _Requirements: 13.5, 13.8, 13.12_

  - [x] 14.5 Implement user deletion
    - For Admin: confirmation modal then DELETE /api/users/{id}
    - For Manager: hide delete buttons using @can
    - Refresh list on successful deletion
    - _Requirements: 13.9, 13.10, 13.11_

- [x] 15. Categories Management Interface
  - [x] 15.1 Create categories page and module
    - Create resources/views/categories/index.blade.php
    - Create resources/js/modules/categories.js
    - Add table with columns: Name, Description, Product Count, Actions
    - _Requirements: 15.1, 15.2_
  
  - [x] 15.2 Implement categories CRUD
    - Fetch from GET /api/categories
    - Add modal with fields: Name, Description
    - Submit to POST /api/categories for create
    - Submit to PUT /api/categories/{id} for edit
    - Implement delete with admin verification for Manager
    - _Requirements: 15.3, 15.4, 15.5, 15.6, 15.7, 15.8, 15.9, 15.10_

- [x] 16. Suppliers Management Interface
  - [x] 16.1 Create suppliers page and module
    - Create resources/views/suppliers/index.blade.php
    - Create resources/js/modules/suppliers.js
    - Add table with columns: Name, Contact Person, Email, Phone, Actions
    - _Requirements: 16.1, 16.2_
  
  - [x] 16.2 Implement suppliers CRUD
    - Fetch from GET /api/suppliers
    - Add modal with fields: Name, Contact Person, Email, Phone, Address
    - Submit to POST /api/suppliers for create
    - Submit to PUT /api/suppliers/{id} for edit
    - Implement delete with admin verification for Manager
    - _Requirements: 16.3, 16.4, 16.5, 16.6, 16.7, 16.8, 16.9, 16.10_

- [x] 17. Customers Management Interface
  - [x] 17.1 Create customers page and module
    - Create resources/views/customers/index.blade.php
    - Create resources/js/modules/customers.js
    - Add table with columns: Name, Email, Phone, Total Purchases, Actions
    - _Requirements: 17.1, 17.2_
  
  - [x] 17.2 Implement customers CRUD
    - Fetch from GET /api/customers
    - Add modal with fields: Name, Email, Phone, Address
    - Submit to POST /api/customers for create
    - Submit to PUT /api/customers/{id} for edit
    - Implement delete with admin verification for Manager
    - _Requirements: 17.3, 17.4, 17.5, 17.6, 17.7, 17.8, 17.9, 17.10_

- [x] 18. Checkpoint - All CRUD Interfaces Complete
  - Ensure all tests pass, ask the user if questions arise.

- [x] 19. Responsive Design and Mobile Optimization
  - [x] 19.1 Implement responsive navigation
    - Ensure navbar collapses to hamburger menu on screens < 992px
    - Test dropdown menus on mobile devices
    - Verify touch targets are at least 44x44 pixels
    - _Requirements: 19.1, 19.7_
  
  - [x] 19.2 Optimize dashboard for mobile
    - Stack statistics cards vertically on screens < 768px
    - Display Quick Actions grid with 2 buttons per row on mobile
    - Test widget layouts on mobile devices
    - _Requirements: 19.2, 19.3_
  
  - [x] 19.3 Optimize tables for mobile
    - Enable horizontal scrolling for tables on screens < 768px
    - Ensure table-responsive wrapper is applied to all tables
    - Test table usability on mobile devices
    - _Requirements: 19.4_
  
  - [x] 19.4 Optimize modals and forms for mobile
    - Set modals to occupy 95% screen width on mobile
    - Use appropriate input types (email, tel, number, date) for mobile keyboards
    - Test form submission on mobile devices
    - _Requirements: 19.5, 19.8_
  
  - [x] 19.5 Test responsive design across breakpoints
    - Test on screen widths: 320px, 768px, 992px, 1200px, 2560px
    - Verify Bootstrap responsive classes (col-sm, col-md, col-lg) are working
    - Ensure all layouts remain readable at all breakpoints
    - _Requirements: 2.8, 19.6_

- [x] 20. Performance Optimization
  - [x] 20.1 Implement lazy loading for JavaScript modules
    - Use dynamic imports in app.js to load modules only when needed
    - Load dashboard module only on dashboard page
    - Load feature modules only on their respective pages
    - _Requirements: 20.1, 20.6_
  
  - [x] 20.2 Implement API response caching
    - Create cache utility in utils/cache.js with 5-minute TTL
    - Cache reference data: categories, suppliers, customers
    - Check cache before making API requests
    - Clear cache on data mutations
    - _Requirements: 20.2_
  
  - [x] 20.3 Implement pagination for large datasets
    - Add pagination controls to all tables with > 50 records
    - Fetch paginated data from API with page parameter
    - Display page numbers and navigation buttons
    - _Requirements: 20.4_
  
  - [x] 20.4 Optimize build configuration
    - Configure Vite for code splitting by feature module
    - Enable minification and compression for production builds
    - Test production build performance
    - _Requirements: 20.5, 20.6_

  - [x] 20.5 Optimize initial page load
    - Ensure initial page content displays within 2 seconds
    - Limit Activity Timeline to 10 events maximum
    - Implement virtual scrolling for dropdowns with > 100 options
    - Add appropriate cache-control headers to API requests
    - _Requirements: 20.7, 20.8, 20.9, 20.10_

- [ ] 21. Error Handling and User Feedback Enhancement
  - [ ] 21.1 Verify global error handling
    - Test network error handling (show "Network error" toast)
    - Test 401 handling (redirect to login)
    - Test 403 handling (show "No permission" toast)
    - Test 404 handling (show "Not found" toast)
    - Test 500 handling (show "Server error" toast)
    - _Requirements: 18.1, 18.2, 18.3, 18.4, 18.6_
  
  - [ ] 21.2 Verify loading states
    - Test form submission loading (disabled button with spinner)
    - Test data fetch loading (spinner in content area)
    - Verify loading states are cleared on error
    - _Requirements: 18.7, 18.8_
  
  - [ ] 21.3 Verify error logging and retry capability
    - Ensure JavaScript errors are logged to console
    - Test that application state is maintained after errors
    - Verify users can retry failed operations
    - _Requirements: 18.9, 18.10_

- [ ] 22. Admin Verification Audit Trail
  - [ ] 22.1 Verify admin verification logging
    - Test that all admin verification attempts are logged
    - Verify log includes: timestamp, manager user ID, admin email, IP address, success/failure
    - Test both successful and failed verification attempts
    - _Requirements: 14.8_

- [ ] 23. Final Integration and Testing
  - [ ] 23.1 Test role-based access control
    - Login as Admin and verify all features are accessible
    - Login as Manager and verify delete requires admin verification
    - Login as Staff and verify only view and transaction features are accessible
    - Test that UI elements match backend authorization rules
    - _Requirements: 3.1, 3.2, 3.3, 3.7_
  
  - [ ] 23.2 Test all CRUD operations end-to-end
    - Test create, read, update, delete for: Products, Purchases, Sales, Categories, Suppliers, Customers, Users
    - Verify toast notifications appear for all operations
    - Verify data refreshes after mutations
    - Test validation error display
    - _Requirements: 8.7, 8.9, 8.10, 9.8, 10.8, 11.2, 13.5, 13.8, 15.5, 15.7, 16.5, 16.7, 17.5, 17.7_
  
  - [ ] 23.3 Test dashboard functionality
    - Verify all statistics display correctly for each role
    - Test Quick Actions navigation
    - Test widget data loading and display
    - Verify auto-refresh works correctly
    - _Requirements: 5.1-5.12, 6.1-6.6, 7.1-7.4_

  - [ ] 23.4 Test reports generation and export
    - Generate each report type with various filter combinations
    - Test CSV export functionality
    - Verify report data accuracy
    - Test empty state handling
    - _Requirements: 12.3, 12.4, 12.6, 12.7, 12.8, 12.9, 12.10, 12.12_
  
  - [ ] 23.5 Cross-browser compatibility testing
    - Test on Chrome, Firefox, Safari, Edge
    - Verify all features work consistently across browsers
    - Test on both desktop and mobile browsers
    - _Requirements: 2.8, 19.1-19.8_
  
  - [ ] 23.6 Performance testing
    - Test page load times on standard broadband connection
    - Verify lazy loading is working
    - Test with large datasets (> 100 records)
    - Verify caching is reducing API calls
    - _Requirements: 20.1-20.10_

- [x] 24. Final Checkpoint and Documentation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- All tasks reference specific requirements from the requirements document for traceability
- Each task builds incrementally on previous work to ensure continuous integration
- Checkpoints are placed at logical breaks to allow for testing and user feedback
- The implementation uses vanilla JavaScript with ES6+ features for maintainability
- Bootstrap 5 provides responsive design and component library
- Axios handles all API communication with centralized error handling
- Role-based access control is enforced at both UI (Blade @can) and API layers
- Admin verification for Manager delete operations provides audit trail and security
- Performance optimizations include lazy loading, caching, debouncing, and pagination
- Responsive design ensures usability on devices from 320px to 2560px screen width

## Dependencies

- Tasks 4-7 depend on Task 1 (toast helper) and Task 0 (CSS setup)
- Tasks 8-11 depend on the patterns established in Tasks 4-7 (modals, AJAX, error handling)
- The admin verification modal (Task 0.4) should be built early to be used in Tasks 7, 9, 10, 14, 15, 16, 17
- Always use @can directives in Blade to conditionally render UI elements
- For dynamic JS actions, pass permissions via data attributes or window.permissions object
- For API endpoints that don't exist yet (like /api/verify-admin), confirm with backend or create a simple verification endpoint that checks admin credentials
