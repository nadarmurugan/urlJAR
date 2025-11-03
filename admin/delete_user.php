<?php
// ========================================================
// delete_user.php — Handles the deletion of a user account
// Location: /admin/
// ========================================================

session_start();
require_once '../includes/config.php'; 

// --------------------------------------------------------
// 1. SECURITY CHECK & INPUT VALIDATION
// --------------------------------------------------------
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Ensure the request is POST and user_id is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$user_id = (int)$_POST['user_id'];

if ($user_id <= 0) {
    // Optionally set error message in session
    $_SESSION['admin_message'] = "Invalid user ID provided for deletion.";
    header('Location: admin_dashboard.php');
    exit;
}

// --------------------------------------------------------
// 2. DELETE ACTIONS (Transaction Recommended for Safety)
// --------------------------------------------------------
try {
    $pdo->beginTransaction();

    // WARNING: Deleting a user must also delete all associated data (links, jars, etc.)
    
    // 1. Delete associated links
    $stmt_links = $pdo->prepare("DELETE FROM links WHERE user_id = :user_id");
    $stmt_links->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_links->execute();

    // 2. Delete associated jars
    $stmt_jars = $pdo->prepare("DELETE FROM jars WHERE user_id = :user_id");
    $stmt_jars->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_jars->execute();

    // 3. Delete the user
    $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_user->execute();

    $pdo->commit();

    $_SESSION['admin_message'] = "✅ User ID {$user_id} and all associated data successfully deleted.";
    $_SESSION['admin_message_type'] = 'success';

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("User Deletion Error: " . $e->getMessage());
    $_SESSION['admin_message'] = "❌ Database error during user deletion: {$e->getMessage()}";
    $_SESSION['admin_message_type'] = 'error';
}

header('Location: admin_dashboard.php');
exit;
?>
