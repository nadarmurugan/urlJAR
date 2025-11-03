<?php
// ========================================================
// add_admin_user.php — Dedicated Page for Adding a New User
// Location: /admin/
// ========================================================

session_start();
// Set timezone to prevent PHP warnings
date_default_timezone_set('UTC'); 
// FIXED PATH: Should be '../' to go up one level (out of 'admin')
require_once '../includes/config.php'; 

// --------------------------------------------------------
// 1. SECURITY CHECK
// --------------------------------------------------------
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$message = '';
$message_type = ''; // 'success' or 'error'

// --------------------------------------------------------
// 2. FORM PROCESSING (PDO)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // --- Validation ---
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("⚠️ All fields are required.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("📧 Invalid email address format.");
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $password)) {
            throw new Exception("🔐 Password must be 8+ chars with uppercase, lowercase, number, and special character.");
        }
        if (strlen($name) < 2) {
            throw new Exception("👤 Please enter the full name (at least 2 characters).");
        }

        // --- Check Existing Email ---
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("🚫 This email is already registered. Cannot create user.");
        }

        // --- Hash Password & Insert ---
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $insert = $pdo->prepare("
            INSERT INTO users (full_name, email, password_hash, created_at)
            VALUES (:name, :email, :hash, NOW())
        ");
        
        $sanitized_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $insert->execute([
            'name' => $sanitized_name,
            'email' => $email,
            'hash' => $hash
        ]);

        $message = "User **{$sanitized_name}** added successfully! Redirecting...";
        $message_type = 'success';
        
        // Redirect back to dashboard after a delay
        header("Refresh: 2; URL=admin_dashboard.php");
        
    } catch (Exception $e) {
        error_log("Admin Add User Error: " . $e->getMessage());
        $message = $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Add User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'neon-pink': '#FF0099',
                        'neon-blue': '#00CCFF',
                        'neon-green': '#00FF77',
                        'dark-card': '#0F0F11',
                    },
                    fontFamily: {
                        'bubble': ['Poppins', 'sans-serif'],
                        'heading': ['Bungee Outline', 'cursive'],
                    },
                }
            }
        }
    </script>
    <style>
        body.dark {
            background-color: #0a0a0c;
            color: #e6e6e6;
            background-image: repeating-linear-gradient(45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px), repeating-linear-gradient(-45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px);
            background-size: 20px 20px;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
        }
        .neon-card {
            border-radius: 16px;
            backdrop-filter: blur(8px);
            background: rgba(15, 15, 17, 0.85); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .neon-input {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 204, 255, 0.2);
            transition: all 0.3s;
        }
        .neon-input:focus {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #00CCFF;
            box-shadow: 0 0 10px #00CCFF;
        }
        .message-success { background: #00FF7730; color: #00FF77; border-color: #00FF7750; }
        .message-error { background: #FF009930; color: #FF0099; border-color: #FF009950; }
    </style>
</head>
<body class="dark flex items-center justify-center min-h-screen">
    
    <div class="w-full max-w-md p-8 text-center neon-card border-2 border-neon-green/50">
        
        <a href="admin_dashboard.php" class="absolute top-4 left-4 text-gray-400 hover:text-neon-blue transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>

        <div class="flex items-center justify-center space-x-2 mb-6 mt-8">
            <i class="fas fa-user-plus text-3xl text-neon-green"></i>
            <span class="text-4xl font-extrabold font-heading text-neon-blue">url<span class="text-neon-pink">JAR</span></span>
        </div>
        
        <h1 class="text-2xl font-extrabold text-neon-green mb-6">Create New User</h1>
        
        <?php if ($message): ?>
            <div class="p-3 mb-4 text-sm rounded-lg shadow-md border <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i> 
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_admin_user.php" class="space-y-4">
            
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-300 text-left">Full Name</label>
                <input type="text" id="name" name="name" class="w-full p-3 rounded-lg neon-input text-white" 
                       placeholder="Full Name" required autocomplete="name" inputmode="text">
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-300 text-left">Email Address</label>
                <input type="email" id="email" name="email" class="w-full p-3 rounded-lg neon-input text-white" 
                       placeholder="user@example.com" required autocomplete="email" inputmode="email">
            </div>
            
            <div class="relative">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-300 text-left">Password</label>
                <input type="password" id="password" name="password" class="w-full p-3 rounded-lg neon-input text-white pr-10" 
                       placeholder="Password (Min 8 Chars)" required minlength="8" autocomplete="new-password">
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 pt-5 text-neon-blue/80 hover:text-neon-blue transition-colors">
                    <i id="toggleIcon" class="fas fa-eye-slash"></i>
                </button>
            </div>
            
            <button type="submit" class="w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                bg-neon-pink text-dark-card border-2 border-neon-pink hover:bg-pink-400 
                shadow-lg shadow-neon-pink/50 hover:shadow-neon-pink/70 mt-6">
                <i class="fas fa-check-circle mr-2"></i> Create User
            </button>
            
        </form>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            
            passwordInput.setAttribute('type', type);
            
            // Toggle the eye icon
            toggleIcon.classList.toggle('fa-eye-slash');
            toggleIcon.classList.toggle('fa-eye');
        });
    </script>

</body>
</html>
