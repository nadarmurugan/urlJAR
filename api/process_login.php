<?php
// ========================================================
// process_login.php — Secure Login Handler for urlJAR
// Supports JSON (AJAX) + normal POST, with session & logging
// ========================================================

session_start();
// NOTE: Assuming config.php contains the $pdo connection object
// Adjust the path to config.php if needed.
require_once '../includes/config.php'; // Path to DB config assumed

// Default response type: JSON for AJAX calls
header('Content-Type: application/json; charset=utf-8');

// Variable to check if the request is an AJAX call
$is_ajax = ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)
           || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

try {
    // -----------------------
    // 1️⃣ Get Input
    // -----------------------
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    // -----------------------
    // 2️⃣ Validate Input
    // -----------------------
    if (empty($email) || empty($password)) {
        throw new Exception("⚠️ Please enter both email and password.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Use a generic error message here to prevent enumeration attacks
        throw new Exception("❌ Invalid credentials. Please try again."); 
    }

    // -----------------------
    // 3️⃣ Fetch User
    // -----------------------
    $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found, use a generic error message
        throw new Exception("❌ Invalid credentials. Please try again.");
    }

    // -----------------------
    // 4️⃣ Verify Password
    // -----------------------
    if (!password_verify($password, $user['password_hash'])) {
        // Password mismatch, use a generic error message
        throw new Exception("❌ Invalid credentials. Please try again.");
    }

    // -----------------------
    // 5️⃣ Session Management & Response
    // -----------------------
    
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8');
    $_SESSION['logged_in'] = true;

    $redirect_url = 'dashboard.php';
    
    if ($is_ajax) {
        // Response for the JS fetch/AJAX request (Success 200 OK)
        // This response contains the user data and redirect link needed for the modal.
        echo json_encode([
            'success' => true,
            'message' => "Welcome back, " . $user['full_name'] . "!",
            'redirect' => $redirect_url
        ]);
        exit;
    } else {
        // Non-AJAX/Form submission fallback. Redirect immediately.
        header("Location: " . $redirect_url);
        exit;
    }

} catch (Exception $e) {
    // -----------------------
    // 6️⃣ Error Handling & Logging
    // -----------------------
    // Log the error for internal review
    error_log("Login Error (" . date('Y-m-d H:i:s') . "): " . $e->getMessage() . " | Email: " . ($email ?? 'N/A') . "\n", 3, "../logs/error.log");

    if ($is_ajax) {
        // Response for the JS fetch/AJAX request
        http_response_code(401); // 401 Unauthorized/Invalid credentials
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    } else {
        // Non-AJAX/Form submission fallback. Set error message and redirect back to login page.
        $_SESSION['error'] = $e->getMessage();
        // Redirect back to the login page
        header("Location: ../login.php"); 
        exit;
    }
}
?>