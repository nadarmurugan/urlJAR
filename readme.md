
---

<h1 align="center"> 🫙 urlJAR – Organize Your Bookmarks in Style 🌐 </h1>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-Core_PHP-blueviolet?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-Database-blue?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Visualization-Chart.js-ff6384?style=for-the-badge&logo=chart.js" alt="Chart.js">
  <img src="https://img.shields.io/badge/Security-PDO_&_BCRYPT-green?style=for-the-badge&logo=lock" alt="Security">
</p>

<p align="center">
  <img src="https://img.shields.io/github/stars/nadarmurugan/urlJAR?style=social" alt="GitHub stars">
  <img src="https://img.shields.io/github/forks/nadarmurugan/urlJAR?style=social" alt="GitHub forks">
  <img src="https://img.shields.io/github/last-commit/nadarmurugan/urlJAR?style=flat&color=brightgreen" alt="Last Commit">
  <img src="https://img.shields.io/github/license/nadarmurugan/urlJAR?color=blue&style=flat" alt="License">
</p>

<p align="center">✨ A sleek, modern, and secure way to save, organize, and visualize your bookmarks — all in one stylish interface. ✨</p>

---

## ✨ Project Overview

**urlJAR** is a next-generation **bookmark manager** that reimagines the way you store and organize links.
Instead of cluttered browser folders, it introduces **beautiful, card-based collections** called **“Jars.”**

Built for speed, simplicity, and security, urlJAR delivers a clean user experience powered by a **PHP + MySQL** backend and a vibrant **Tailwind CSS** frontend — complete with analytics, theming, and an admin dashboard.

---

## 🌟 Key Features

### 🧑‍💻 User Features

* 🎨 **Visual Jars** – Organize your bookmarks into customizable jars with colors, emojis, and titles.
* 🔗 **Full Link Management (CRUD)** – Add, edit, or delete links with metadata such as tags, titles, and notes.
* 🔒 **Account Security** – Secure login/logout with server-side validation and user-based access control.
* 📈 **Personal Analytics** – Interactive dashboard powered by Chart.js to visualize link-saving activity.
* 🌙 **Dynamic Theme** – Neon-themed dark/light modes for a better visual experience.

---

### 👑 Admin Features

The **Admin Panel** provides full control and insight into the platform:

* 🛡️ **Transactional User Deletion** – Ensures full data integrity via PDO transactions.
* 👥 **User Management** – Add, update, or delete users securely.
* 📊 **System Metrics Dashboard** – Real-time overview of total users, jars, and links, visualized with Chart.js.

---

## 💻 Technical Stack

| Category              | Technology          | Description                                                |
| :-------------------- | :------------------ | :--------------------------------------------------------- |
| **Backend/Core**      | PHP (v7+)           | Handles application logic, routing, and authentication.    |
| **Database**          | MySQL / MariaDB     | Stores user, jar, and link data with relational integrity. |
| **Database Access**   | PDO + MySQLi        | Secure database interaction with prepared statements.      |
| **Password Security** | BCRYPT              | Encrypts and stores passwords safely.                      |
| **Frontend**          | HTML + Tailwind CSS | Modern, responsive, and fast UI design.                    |
| **Visualization**     | Chart.js (v4.4.1)   | Displays analytics and statistics interactively.           |

---

## ⚙️ Setup & Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/nadarmurugan/urlJAR.git
   cd urlJAR
   ```

2. **Set Up Database**

   * Import the provided `urljar.sql` file into your MySQL/MariaDB server.
   * Update `includes/config.php` with your database credentials.

3. **Run Locally**

   * Place the project in your `htdocs` (XAMPP) or webroot folder.
   * Start Apache and MySQL.
   * Open `http://localhost/urlJAR/` in your browser.

4. **Access the Admin Panel (Optional)**

   * Default demo credentials are provided.
   * For production, replace them with secure, hashed credentials.

---

## ⚠️ Security Notes

* 🚨 **Admin Login:** Uses demo credentials for testing.
  Replace with hashed credentials in production.
  Protect `/admin/` using `.htaccess` or server-level authentication.

* 🔐 **Database Integrity:**

  * Uses **InnoDB** with **foreign key constraints** for safe cascading deletions.
  * Ensures atomic operations via **PDO transactions**.

---

## 🧠 Data Integrity

urlJAR ensures consistency and prevents orphan data:

* Deleting a user triggers a PDO transaction to remove all their jars and links.
* Relational constraints maintain database cleanliness and reliability.

---

## 🌐 Deployment

**Compatible with:** LAMP/LEMP Stacks (Linux, Apache/Nginx, MySQL/MariaDB, PHP)

**Recommended:**

* PHP ≥ 7.4
* MySQL ≥ 5.7 / MariaDB ≥ 10
* Apache or Nginx

---

## 🚀 Deployment on InfinityFree

urlJAR can be easily hosted on [InfinityFree](https://infinityfree.net) — a free PHP + MySQL hosting platform.

### 🏗️ Steps

1️⃣ **Create a Hosting Account**

* Sign up on InfinityFree and create a new hosting instance.
* You’ll receive a subdomain (e.g., `yourproject.epizy.com`).

2️⃣ **Database Setup**

* Go to **cPanel → MySQL Databases**.
* Create a new database and note your credentials.

**Example:**

| Parameter | Value                   |
| --------- | ----------------------- |
| Database  | if0_40320375_urljar     |
| User      | if0_40320375            |
| Host      | sql309.infinityfree.com |

3️⃣ **Upload Files**

* Upload all project files to `/htdocs/`.
* Place admin files in `/admin/` for structure.

4️⃣ **Configure Database**
Update `includes/config.php`:

```php
<?php
$host = "sql309.infinityfree.com";
$user = "if0_40320375";
$pass = "your_database_password";
$dbname = "if0_40320375_urljar";
?>
```

5️⃣ **Import the Database**

* Open **phpMyAdmin** in InfinityFree.
* Import `urljar.sql` to create tables (`users`, `jars`, `links`).

6️⃣ **Access the Site**

* 🌐 **Main Site:** `https://yourproject.epizy.com/`
* 🔑 **Admin Panel:** `https://yourproject.epizy.com/admin/`

---

### 🔒 Production Security Tips

* Protect `/admin/` with `.htaccess`.
* Replace any hardcoded credentials with hashed, database-stored ones.
* Keep regular backups and enforce strict file permissions.

---

## ✅ Current Hosting Setup

| Parameter   | Details                 |
| ----------- | ----------------------- |
| Platform    | InfinityFree            |
| Database    | if0_40320375_urljar     |
| Host        | sql309.infinityfree.com |
| Admin Panel | `/admin/` subdirectory  |

---

## 💡 Future Enhancements

* 🔍 **Smart Search & Tag Filtering**
* 🧠 **AI-Powered Link Categorization**
* 🪄 **Drag-and-Drop Jar Organization**
* ☁️ **Cloud Backup & Sync Integration**

---

## 🤝 Contributing

Contributions, ideas, and feature requests are welcome!

**Steps:**

1. Fork this repository
2. Create a feature branch
3. Commit your changes
4. Open a pull request 🚀

---

## 🧾 License

Licensed under the **MIT License** — free to use, modify, and distribute with attribution.

---

## 💬 Connect

👨‍💻 **Author:** [Jeymurugan Nadar](https://github.com/nadarmurugan)
📍 Mumbai, India
📧 **Email:** [murugannadar077@gmail.com](mailto:murugannadar077@gmail.com)

---

