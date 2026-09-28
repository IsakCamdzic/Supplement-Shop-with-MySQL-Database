# Supplement-Shop-with-MySQL-Database
Complete e-commerce platform for sports supplements with multi-role dashboard (Admin, Stockkeeper, Delivery, Customer). Built with PHP and MySQL.


# 💪 SUPP.SCIENCE - E-commerce Platform for Sports Supplements

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

| Table | Description |
|-------|-------------|
| `kupac` | Customer information (name, email, address) |
| `kategorija` | Product categories (Protein, Creatine, etc.) |
| `proizvod` | Products with price, stock, manufacturer |
| `korpa` + `stavke_korpe` | Shopping cart before checkout |
| `narudzba` + `stavke_narudzbe` | Orders and order items |
| `racun` | Invoices for each order |
| `zaposlenik` | Employees (Admin, Stockkeeper, Delivery) |

### SQL Features

- **Triggers** - Automated stock management when orders are placed
- **Views** - Top 10 products for analytics
- **Stored Procedures** - Batch status updates
- **Foreign Keys** - Referential integrity
- **Check Constraints** - Data validation (price > 0, stock >= 0)

---

## User Roles & Permissions

| Role | Permissions | Dashboard |
|------|-------------|-----------|
| **Admin** | Full system access | `admin/dashboard.php` |
| **Stockkeeper** | Manage products and inventory | `skladistar/dashboard.php` |
| **Delivery** | View and update delivery status | `dostavljac/dashboard.php` |
| **Customer** | Browse, cart, orders | `kupac/dashboard.php` |

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@shop.com` | `password` |
| Stockkeeper | `skladistar@shop.com` | `password` |
| Delivery | `dostavljac@shop.com` | `password` |
| Customer | `isak.camdzic@gmail.com` | `password` |

---

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

