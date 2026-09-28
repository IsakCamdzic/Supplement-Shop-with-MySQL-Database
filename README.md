# Supplement-Shop-with-MySQL-Database
Complete e-commerce platform for sports supplements with multi-role dashboard (Admin, Stockkeeper, Delivery, Customer). Built with PHP and MySQL. The focus of the project was the database itself.


# SUPP.SCIENCE - E-commerce Platform for Sports Supplements

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CSS-06B6D4?logo=tailwindcss&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?logo=javascript&logoColor=black)

> **Complete e-commerce platform for sports supplements with multi-role dashboards, automated stock management, and real-time order tracking.**

---

## About The Project

SUPP.SCIENCE is a fully functional e-commerce system designed for online stores selling fitness supplements, protein powders, creatine, pre-workouts, and vitamins. The system supports four different user roles with customized dashboards and permissions.

### Key Features

| Feature | Description |
|---------|-------------|
| **Admin Dashboard** | Complete system control, order management, user overview |
| **Stockkeeper Panel** | Inventory tracking, low stock alerts, add/edit products |
| **Delivery Panel** | Active deliveries, mark as delivered, route overview |
| **Customer Panel** | Product browsing, shopping cart, order history |
| **Automated Stock Management** | Trigger updates inventory when orders are placed |
| **Automatic Invoice Generation** | Invoice created with every order |
| **Analytics** | Top 10 products view for bestsellers |
| **Secure Authentication** | Role-based access control (RBAC) |

---

## Built With

| Technology | Purpose |
|------------|---------|
| **PHP 8.x** | Backend logic, session management, database interaction |
| **MySQL 8.x** | Database with triggers, views, and stored procedures |
| **HTML5/CSS3** | Structure and styling |
| **Tailwind CSS** | Modern dark-mode UI framework |
| **JavaScript** | Interactivity, live search, password toggle |

---

## Database Schema

### Database Tables

| Table                    | Description                                                                                                           |
| ------------------------ | --------------------------------------------------------------------------------------------------------------------- |
| `drzava`                 | Country information (id, unique country name)                                                                         |
| `grad`                   | Town information with a reference to the country id (id, town name, unique constraint (town name and country id))     |
| `adresa`                 | Address information (street, number, town, postal code)                                                               |
| `kupac`                  | Customer information (name, surname, unique email, active status, creation and update timestamps)                     |
| `zaposlenik_uloga`       | Employee roles (Admin, Stockkeeper, Delivery)                                                                         |
| `zaposlenik`             | Employees with a reference to their role (name, surname, unique email, active status, creation and update timestamps) |
| `kupac_adresa`           | Many-to-many relationship between customers and addresses, with an indicator for the default address                  |
| `kategorija`             | Product categories with support for hierarchical parent-child categories                                              |
| `proizvodjac`            | Product manufacturers (id, manufacturer name)                                                                         |
| `proizvod`               | Product information with manufacturer, category, price, description and active status                                 |
| `status_narudzbe`        | Order statuses (Pending, Confirmed, Delivered, Cancelled, Rejected)                                                   |
| `narudzba`               | Orders with customer address, order date, status and assigned employee                                                |
| `zaliha_transakcija`     | Inventory transactions for purchases, stock replenishment and stock corrections, optionally linked to an order        |
| `stavke_narudzbe`        | Items belonging to orders, including product, quantity and price at the time of ordering                              |
| `korpa` + `stavke_korpe` | Shopping cart and its items before checkout                                                                           |
| `status_placanja`        | Payment statuses (Pending, Confirmed, Rejected)                                                                       |
| `racun`                  | Invoices associated with orders, including issue date, payment status and total amount                                |


### SQL Features

- **Triggers** - Automated stock management when orders are placed
- **Views** - Top 10 products for analytics
- **Stored Procedures** - Batch status updates
- **Foreign Keys** - Referential integrity
- **Check Constraints** - Data validation (price > 0, stock >= 0)

The database underwent a complete redesign:
- **Separate customer entity** – Added a dedicated kupac table with customer information, active status, email, and timestamps.
- **Multiple customer addresses** – Added kupac_adresa, allowing customers to have multiple addresses and designate a default address.
- **Separate manufacturers** – Added proizvodjac so manufacturers are stored independently and can be reused across multiple products.
- **Hierarchical categories** – Added roditelj_id to kategorija, allowing categories and subcategories to be organized in a hierarchy.
- **Inventory transaction history** – Added zaliha_transakcija to track stock changes caused by purchases, restocking, and inventory corrections.
- **Order status management** – Added status_narudzbe to manage different order states such as pending, confirmed, delivered, cancelled, and rejected.
- **Payment status management** – Added status_placanja to separately track the payment status of invoices.
- **Employee roles** – Added zaposlenik_uloga to manage employee roles such as Admin, Stockkeeper, and Delivery.
- **Optional employee assignment** – Orders can initially exist without an assigned employee, allowing employees to be assigned later.
- **Historical order prices** – stavke_narudzbe stores the product price at the time of purchase, preserving historical pricing even if the product's current price changes.
- **Timestamps and active status** – Customers, employees, and products include created_at, updated_at, and aktivan fields for better data management.
- **Sales analytics** – Added the top10_proizvoda view to identify the top-selling products based on quantity sold and total revenue.
- **Improved data normalization** – Repeated information such as manufacturers, employee roles, order statuses, and payment statuses is separated into dedicated tables, reducing data duplication and improving consistency.

### Entity-relationship diagram of the database used for the project
<img width="3380" height="1890" alt="Blank diagram" src="https://github.com/user-attachments/assets/8b65da2d-fc48-400f-b2de-d995f5702028" />


---

## User Roles & Permissions

| Role | Permissions | Dashboard |
|------|-------------|-----------|
| **Admin** | Full system access | `admin/dashboard.php` |
| **Stockkeeper** | Manage products and inventory | `skladistar/dashboard.php` |
| **Delivery** | View and update delivery status | `dostavljac/dashboard.php` |
| **Customer** | Browse, cart, orders | `kupac/dashboard.php` |


## Installation

### Prerequisites

- XAMPP / WAMP / LAMP with PHP 8.x
- MySQL 8.x
- Git (optional)

### Step-by-Step Guide

#### 1. Clone the repository

```bash
git clone https://github.com/IsakCamdzic/Supplement-Shop-with-MySQL-Database.git

-- 1. Create tables
source C:/xampp/htdocs/SupplementShop/tabele.sql

-- 2. Insert sample data
source C:/xampp/htdocs/SupplementShop/insert.sql

-- 3. Create trigger (automated stock management)
source C:/xampp/htdocs/SupplementShop/trigger.sql

-- 4. Create view (top 10 products)
source C:/xampp/htdocs/SupplementShop/top10_proizvoda.sql

# Edit database.php with your MySQL credentials

