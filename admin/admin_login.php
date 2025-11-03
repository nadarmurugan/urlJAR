<?php
// ========================================================
// admin_login.php — urlJAR Administrator Login Interface
// WARNING: Using plain text password comparison (UNSECURE!)
// ========================================================

session_start();
// Include the PDO database connection (Kept for consistency if other admin pages need DB)
require_once '../includes/config.php'; 

// --- DEFINED ADMIN CREDENTIALS ---
// WARNING: This uses plain text comparison and is NOT secure.
define('ADMIN_USERNAME', 'admin'); // Changed from email to username
define('ADMIN_PASSWORD', 'admin123'); // Plain text password
// ---------------------------------

// Check if the admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php'); // Redirects here upon successful login
    exit;
}

$error_message = '';
$admin_username_input = ''; // Initialize input variable

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIXED: Fetching 'username' instead of 'email'
    $admin_username_input = $_POST['username'] ?? '';
    $admin_password_input = $_POST['password'] ?? '';

    // Basic validation
    if (empty($admin_username_input) || empty($admin_password_input)) {
        $error_message = "Username and password cannot be empty.";
    } else {
        // FIXED: Direct comparison against hardcoded username and password
        if ($admin_username_input === ADMIN_USERNAME && $admin_password_input === ADMIN_PASSWORD) {
            
            // Authentication successful
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = 1; 
            $_SESSION['admin_name'] = 'System Admin';
            
            // Redirect to admin dashboard
            header('Location: admin_dashboard.php'); 
            exit;
        } else {
            $error_message = "Invalid administrator credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Admin Login</title>
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
        }
        .neon-card {
            border-radius: 16px;
            backdrop-filter: blur(8px);
            background: rgba(15, 15, 17, 0.85); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .neon-border-glow {
            border: 2px solid currentColor;
            box-shadow: 0 0 8px currentColor, inset 0 0 4px currentColor;
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
    </style>
</head>
<body class="dark flex items-center justify-center min-h-screen font-bubble">
    
    <div class="w-full max-w-md p-8 text-center neon-card border-2 border-neon-blue neon-border-glow">
        
        <div class="flex items-center justify-center space-x-2 mb-6">
            <i class="fas fa-lock text-3xl text-neon-pink"></i>
            <span class="text-4xl font-extrabold font-heading text-neon-blue">url<span class="text-neon-pink">JAR</span></span>
        </div>
        
        <h1 class="text-2xl font-extrabold text-neon-green mb-6">Administrator Login</h1>
        
        <?php if ($error_message): ?>
            <div class="p-3 mb-4 text-sm text-white bg-neon-pink/70 rounded-lg shadow-md border border-neon-pink">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            
            <div class="mb-5 text-left">
                <label for="username" class="block mb-2 text-sm font-medium text-gray-300">Username</label>
                <input type="text" id="username" name="username" class="w-full p-3 rounded-lg neon-input text-white" 
                       placeholder="admin" required value="<?php echo htmlspecialchars($admin_username_input ?? 'admin'); ?>">
            </div>
            
            <div class="mb-6 text-left">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full p-3 rounded-lg neon-input text-white pr-10" 
                           placeholder="********" required>
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-neon-blue/80 hover:text-neon-blue transition-colors">
                        <i id="toggleIcon" class="fas fa-eye-slash"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                bg-neon-blue text-dark-card border-2 border-neon-blue hover:bg-blue-300 
                shadow-lg shadow-neon-blue/50 hover:shadow-neon-blue/70">
                <i class="fas fa-sign-in-alt mr-2"></i> Log In
            </button>
            
        </form>

        <p class="mt-6 text-sm text-gray-500">
            <a href="../login.php" class="text-neon-green hover:underline">Return to User Login</a>
        </p>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            
            passwordInput.setAttribute('type', type);
            
            // Toggle the eye icon
            if (type === 'password') {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    </script>

</body>
</html>