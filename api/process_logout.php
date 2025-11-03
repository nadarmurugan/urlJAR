<?php
// ========================================================
// process_logout.php — Handles the actual session destruction
// ========================================================

session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
// FIXED: Use '../login.php' to move up one directory (out of 'api') to reach 'login.php' in the root.
header('Location: ../login.php');
exit;
?>