# ecomerce-furniture-demo 

# 🪑 Furniture Website

A modern and responsive furniture e-commerce website built using **PHP, MySQL, HTML, CSS, and JavaScript**. This project includes both a customer-facing storefront and an admin panel for managing products, categories, and customer inquiries.

---

## 🚀 Features

### 👤 User Side

* Browse furniture products
* Product categories section
* Product detail page
* Add products to cart
* Quote request system
* Responsive UI design
* Search and sorting functionality

### 🛠️ Admin Panel

* Admin login system
* Add/Edit/Delete products
* Manage product categories
* View customer inquiries
* Dashboard overview

---

## 🧰 Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Server:** XAMPP / Apache

---

## 📂 Project Structure

```bash
Furniture-website/
│
├── admin/                # Admin panel files
├── assets/               # CSS, JS, images
├── includes/             # Database & reusable components
├── uploads/              # Uploaded product/category images
├── cart.php              # Shopping cart page
├── category.php          # Category page
├── index.php             # Home page
├── product-detail.php    # Product details page
├── products.php          # All products page
├── quote.php             # Quote request form
└── quote-submit.php      # Quote form handler
```

---

## ⚙️ Installation Guide

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/your-username/furniture-website.git
```

### 2️⃣ Move Project to XAMPP htdocs

```bash
C:/xampp/htdocs/furniture-website
```

### 3️⃣ Start Apache & MySQL

Open **XAMPP Control Panel** and start:

* Apache
* MySQL

### 4️⃣ Create Database

* Open phpMyAdmin
* Create a new database
* Import the SQL file (if included)

### 5️⃣ Configure Database Connection

Open:

```bash
includes/db.php
```

Update your database credentials:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "your_database_name";
```

---

## 📸 Screenshots

Add your project screenshots here.

Example:

```md
![Home Page](screenshots/home.png)
```

---

## 🔒 Admin Login

You can access the admin panel from:

```bash
http://localhost/furniture-website/admin
```

---

## 📌 Future Improvements

* User authentication system
* Online payment gateway
* Wishlist feature
* Order tracking system
* Better UI animations

---

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository
2. Create a new branch
3. Commit your changes
4. Push to your branch
5. Open a Pull Request

---

## 📄 License

This project is for educational purposes.

---

## 👨‍💻 Author

Developed by **Satyam Chatterjee**
