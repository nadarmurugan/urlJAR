<?php
// ================================
// DATABASE CONFIGURATION (urlJAR)
// ================================

// Database credentials
$host = "localhost";        // Change if using hosting server
$dbname = "urljar";         // Your database name
$username = "root";         // Default for localhost (XAMPP)
$password = "";             // Leave blank if no password

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Optional success message (for testing only — remove in production)
    // echo "✅ Database connected successfully!";

} catch (PDOException $e) {
    // If connection fails
    die("❌ Database Connection Failed: " . $e->getMessage());
}
?>


