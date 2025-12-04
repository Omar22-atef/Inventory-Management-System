# 📦 Inventra – Inventory Management System

**Inventra** is a web-based **Inventory Management System** designed for small businesses to efficiently manage products, suppliers, stock levels, sales trends, and automatic low-stock reordering — all controlled by **one Admin user**.

The system is simple, secure, and focused on giving full control to a single administrator without allowing supplier or staff access to the platform.

---

## 📖 Overview

Inventra helps small businesses:

- Track real-time stock levels  
- Manage suppliers and product sourcing  
- Monitor incoming and outgoing inventory  
- Receive **automatic low-stock notifications**  
- Send **reorder requests to suppliers by email**  
- Generate detailed sales and inventory reports  

The entire system is managed by **one Admin user only**.  
Suppliers are external and **do not have accounts or access** to the system.

---

## ✨ Features

### 1. Admin Authentication
- Secure login for **a single Admin user**.
- No staff accounts or multi-user roles.
- Full system access is restricted to the Admin only.

### 2. Admin Dashboard
- Centralized control panel for the entire system.
- Displays:
  - Total products
  - Current stock levels
  - Low-stock alerts
  - Recent inventory activity
- Quick access to all system modules.

### 3. Stock Management
- Add, update, and delete products.
- Track:
  - Stock-in (purchases, restocking)
  - Stock-out (sales, damaged goods, adjustments)
- Full history log for every product movement.

### 4. Supplier Management
- Add and manage suppliers with:
  - Name
  - Email
  - Phone
  - Address
  - Payment terms
- Assign each product to a specific supplier.
- **Suppliers do NOT have system accounts and cannot log in.**

### 5. Automatic Low-Stock Reordering
- Define a **low-stock threshold** for each product.
- When stock reaches the threshold:
  - A **notification appears in the Admin dashboard**.
  - A **reorder email is automatically sent to the assigned supplier**.
  - An **email notification is sent to the Admin** (optional).
- Entire reorder process is controlled and reviewed by the Admin.

### 6. Sales & Inventory Reports
- Generate detailed reports for:
  - Sales performance
  - Inventory movement (stock-in vs stock-out)
  - Low-stock and out-of-stock items
- Filter reports by:
  - Date range
  - Product
  - Supplier

### 7. Search & Filter
- Search products by:
  - Name
  - Category
  - SKU
- Filter by stock status:
  - In Stock
  - Low Stock
  - Out of Stock
- View complete product and supplier details.

---

## 👤 System Role

### ✅ Admin (Only User)
- The **only user allowed to log in**.
- Has full control over:
  - Products
  - Stock transactions
  - Suppliers
  - Low-stock thresholds
  - Reorder emails
  - Reports and analytics
- Responsible for all system operations.

### ❌ Suppliers
- **Do NOT have accounts.**
- **Cannot log in to the system.**
- Only receive **email-based reorder requests** from Inventra.

---

## 🧰 Tech Stack


- **Backend:** Laravel (PHP)
- **Frontend:** Blade Templates + Bootstrap / Tailwind CSS
- **Database:** MySQL
- **Authentication:** Laravel Built-in Auth
- **Notifications & Emails:** Laravel Mail & Notifications
- **Reports:** Laravel Eloquent + Chart libraries

---

## 🚀 Installation

### ✅ Prerequisites

- PHP 8.1+
- Composer
- MySQL
- Node.js & npm
- Git

---
