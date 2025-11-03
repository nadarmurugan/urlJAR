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


## ✨ Project Overview

**urlJAR** is a next-generation **bookmark manager** that transforms traditional text-based bookmark folders into **beautiful, card-based collections called “Jars.”**

Built for speed, clarity, and aesthetics, urlJAR replaces cluttered browser lists with a sleek interface designed around usability and data safety. The system is powered by secure PHP + MySQL architecture and includes a dedicated **Admin Panel** for oversight and management.

---

## 🌟 Key Features

### 🧑‍💻 User Environment

* 🎨 **Visual Organization (Jars)** – Group links into customizable “Jars” with titles, colors, and emojis.
* 🔗 **Full Link Management (CRUD)** – Add, edit, or delete links with attributes like URL, title, tags, notes, and emoji icons.
* 🔒 **Secure Accounts** – Safe login/logout with server-side validation and `user_id`-based access control.
* 📈 **Personal Analytics** – A dynamic analytics dashboard built with Chart.js showing user activity and link-saving trends.
* 🌙 **Neon Theme + Dark/Light Mode** – Toggle between stunning neon-themed light and dark modes for a better visual experience.

---

### 👑 Admin Environment

A powerful, restricted-access **Admin Panel** provides full control over the platform:

* 🛡️ **Transactional Delete User** – Ensures full data integrity using PDO transactions; deleting a user automatically removes all their Jars and links.
* 👥 **User Lifecycle Management** – Add, edit, or delete any user account securely.
* 📊 **System Metrics Dashboard** – Visual overview of total users, Jars, and links; includes real-time usage charts using Chart.js.

---

## 💻 Technical Stack

| Category                   | Technology          | Description                                                              |
| :------------------------- | :------------------ | :----------------------------------------------------------------------- |
| **Backend/Core**           | PHP (v7+)           | Handles logic, routing, and authentication.                              |
| **Database**               | MySQL / MariaDB     | Stores user, Jar, and link data with relational integrity.               |
| **Database Access**        | PDO + MySQLi        | Secure communication with prepared statements to prevent SQL injection.  |
| **Password Security**      | BCRYPT Hashing      | Safely stores encrypted passwords.                                       |
| **Frontend**               | HTML + Tailwind CSS | Responsive UI built with Tailwind for fast styling and a neon aesthetic. |
| **Charts & Visualization** | Chart.js (v4.4.1)   | Interactive analytics and dashboard graphs.                              |

---

## ⚙️ Setup & Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/nadarmurugan/urlJAR.git
   cd urlJAR
   ```

2. **Set Up Database**

   * Import the provided `urljar.sql` file into your MySQL/MariaDB server.
   * Update your `config.php` with the correct database credentials.

3. **Run on Localhost**

   * Place the project in your `htdocs` (for XAMPP) or webroot folder.
   * Start Apache and MySQL from your XAMPP control panel.
   * Visit `http://localhost/urlJAR/` in your browser.

4. **(Optional) Admin Panel Access**

   * Default admin login uses demo credentials (see below).
   * For production, replace with hashed database-stored credentials.

---

## ⚠️ Security Notes

* 🚨 **Admin Login:** The `admin_login.php` currently uses **demo credentials** (plaintext).

  * Replace with secure, hashed credentials in production.
  * Protect the `/admin/` folder with `.htaccess` or other authentication methods.

* 🔐 **Database Engine:**

  * Uses **InnoDB** to support transactions and maintain referential integrity.
  * `FOREIGN KEY` constraints with `ON DELETE CASCADE` ensure automatic cleanup of dependent data.

---

## 🧠 Data Integrity

urlJAR prioritizes **data safety** and **consistency**:

* Deleting a user triggers a **PDO transaction** that removes all associated Jars and links atomically.
* Foreign key relationships ensure consistent cleanup and prevent orphan data.

---

## 🌐 Deployment

Compatible with **LAMP/LEMP** stacks (Linux, Apache/Nginx, MySQL/MariaDB, PHP).

Recommended:

* PHP 7.4 or higher
* MySQL 5.7+ / MariaDB 10+
* Apache/Nginx server

---

## 💡 Future Enhancements

* 🔍 Search & Tag Filtering for Jars
* 🧠 AI-powered Link Categorization
* 🪄 Drag-and-Drop Link Organization
* 📤 Cloud Backup Integration

---

## 🤝 Contributing

Contributions, feature requests, and feedback are always welcome!

To contribute:

1. Fork the repository
2. Create a new branch
3. Commit your changes
4. Submit a pull request 🚀

---

## 🧾 License

This project is licensed under the **MIT License** — you are free to use, modify, and distribute with attribution.

---

## 💬 Connect

👨‍💻 **Author:** [Jeymurugan Nadar](https://github.com/nadarmurugan)
📍 Mumbai, India
📧 Email: murugannadar077@gmail.com

---

Would you like me to make this README include **badges** (for languages, license, stars, last commit, etc.) and a **preview image section** at the top (for GitHub aesthetics)?



