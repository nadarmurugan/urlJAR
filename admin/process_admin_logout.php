<?php
// ========================================================
// process_admin_logout.php — Destroys Admin Session
// Location: /admin/
// ========================================================

session_start();

// Unset specific admin session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);

// Destroy the session (or rely on the browser closing, but explicit is better)
// To completely clear all session data, you might use:
// session_unset();
// session_destroy();

// Redirect to the application root index page (up one level)
header('Location: ../index.php');
exit;
?>
