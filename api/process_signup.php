<?php
// ========================================================
// process_signup.php — Robust Signup Handler for urlJAR
// Supports JSON (AJAX) + normal POST, with session & logging
// ========================================================

session_start();
// NOTE: Assuming config.php contains the $pdo connection object
// Adjust the path to config.php if needed.
require_once '../includes/config.php';

// Default response type: JSON for AJAX calls
header('Content-Type: application/json; charset=utf-8');

// Variable to check if the request is an AJAX call (used for response type)
// Use strpos for compatibility with PHP versions < 8.0
$is_ajax = ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false)
           || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

try {
    // -----------------------
    // 1️⃣ Get Input
    // -----------------------
    // Read JSON data first (for modern fetch/AJAX), fallback to $_POST
    $raw = file_get_contents('php://input');
    // json_decode returns null on failure, so the null coalescing operator (??) falls back to $_POST.
    $data = json_decode($raw, true) ?? $_POST;

    $name     = trim($data['name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    // -----------------------
    // 2️⃣ Validate Input
    // -----------------------
    if (empty($name) || empty($email) || empty($password)) {
        throw new Exception("⚠️ All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("📧 Invalid email address format.");
    }

    // Server-side Password Strength Check: 8+ chars, uppercase, lowercase, number, and special char.
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $password)) {
        throw new Exception("🔐 Password must be 8+ chars with uppercase, lowercase, number, and special character.");
    }
    
    // Optional: Basic name length check (for robustness against extremely short names)
    if (strlen($name) < 2) {
        throw new Exception("👤 Please enter your full name (at least 2 characters).");
    }


    // -----------------------
    // 3️⃣ Check Existing Email
    // -----------------------
    // Use user_id as it's typically the primary key, though 'id' works too.
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception("🚫 This email is already registered. Please try logging in.");
    }

    // -----------------------
    // 4️⃣ Hash Password & Insert
    // -----------------------
    // BCRYPT is the recommended algorithm
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $pdo->prepare("
        INSERT INTO users (full_name, email, password_hash, created_at)
        VALUES (:name, :email, :hash, NOW())
    ");
    
    // Sanitize name before inserting into DB for security (XSS prevention)
    $sanitized_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    $insert->execute([
        'name' => $sanitized_name,
        'email' => $email,
        'hash' => $hash
    ]);

    // -----------------------
    // 5️⃣ Respond Success
    // -----------------------
    // Set a session message for the subsequent page (e.g., login.php)
    $_SESSION['signup_message'] = "🎉 Account created successfully! Please log in.";

    if ($is_ajax) {
        // Response for the JS fetch/AJAX request (Success 200 OK)
        echo json_encode([
            'success' => true,
            'message' => 'Account created. Redirecting to login.',
            'redirect' => '../login.php'
        ]);
        exit;
    } else {
        // Non-AJAX/Form submission fallback. Redirect to login.
        header("Location: ../login.php?signup=success");
        exit;
    }

} catch (Exception $e) {
    // -----------------------
    // 6️⃣ Error Handling & Logging
    // -----------------------
    // Log the error for internal review (path assumed to be relative to process_signup.php)
    error_log("Signup Error (" . date('Y-m-d H:i:s') . "): " . $e->getMessage() . "\n", 3, "../logs/error.log");

    if ($is_ajax) {
        // Response for the JS fetch/AJAX request
        // Use HTTP status 400 for bad request/validation errors
        http_response_code(400); 
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    } else {
        // Non-AJAX/Form submission fallback. Redirect back to the index page.
        $_SESSION['error'] = $e->getMessage();
        // Redirect back to the index page, using a fragment to scroll to the signup section
        header("Location: ../index.php#signup"); 
        exit;
    }
}
?>