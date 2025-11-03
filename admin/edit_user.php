<?php
// ========================================================
// edit_user.php — Dedicated Page for Editing User Details
// Location: /admin/
// ========================================================

session_start();
date_default_timezone_set('UTC');
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

// Get User ID from URL
$user_id = (int)($_GET['id'] ?? 0);

if ($user_id === 0) {
    // If no ID is provided, redirect to dashboard
    header('Location: admin_dashboard.php');
    exit;
}

// --------------------------------------------------------
// 2. FETCH EXISTING USER DATA (GET)
// --------------------------------------------------------
$user = null;
try {
    $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "User not found.";
        $message_type = 'error';
    }
} catch (PDOException $e) {
    error_log("Edit User Fetch Error: " . $e->getMessage());
    $message = "Database error while retrieving user data.";
    $message_type = 'error';
}


// --------------------------------------------------------
// 3. PROCESS UPDATE (POST)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $name     = trim($_POST['name'] ?? $user['full_name']);
    $email    = trim($_POST['email'] ?? $user['email']);
    $password = $_POST['password'] ?? '';
    
    // Default SQL and binding parameters
    $update_fields = ['full_name' => $name, 'email' => $email];
    $sql = "UPDATE users SET full_name = :full_name, email = :email ";
    $params = [':full_name' => $name, ':email' => $email, ':id' => $user_id];

    try {
        // --- Basic Validation ---
        if (empty($name) || empty($email)) {
             throw new Exception("Full name and email are required.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        // --- Password Update Check ---
        if (!empty($password)) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/', $password)) {
                throw new Exception("Password must be 8+ chars with uppercase, lowercase, number, and special character.");
            }
            // Add password hashing to the update query
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql .= ", password_hash = :hash ";
            $params[':hash'] = $hash;
        }
        
        // --- Check Email Uniqueness (Only if the email was changed) ---
        if ($email !== $user['email']) {
             $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
             $stmt_check->execute([':email' => $email, ':id' => $user_id]);
             if ($stmt_check->fetch()) {
                 throw new Exception("🚫 This email is already used by another user.");
             }
        }
        
        // --- Execute Update ---
        $sql .= " WHERE id = :id";
        $stmt_update = $pdo->prepare($sql);
        $stmt_update->execute($params);

        $message = "User **{$user_id}** updated successfully!";
        $message_type = 'success';
        
        // Update the $user array so the form shows the new data immediately
        $user['full_name'] = $name;
        $user['email'] = $email;
        
        // Redirect back to dashboard after a delay
        header("Refresh: 2; URL=admin_dashboard.php");

    } catch (Exception $e) {
        error_log("Edit User Update Error: " . $e->getMessage());
        $message = $e->getMessage();
        $message_type = 'error';
    } catch (PDOException $e) {
        error_log("Edit User Database Error: " . $e->getMessage());
        $message = "A database error occurred during the update.";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Edit User <?php echo $user_id; ?></title>
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
            <i class="fas fa-user-edit text-3xl text-neon-green"></i>
            <span class="text-4xl font-extrabold font-heading text-neon-blue">url<span class="text-neon-pink">JAR</span></span>
        </div>
        
        <h1 class="text-2xl font-extrabold text-neon-green mb-6">
            Editing User ID: <?php echo $user_id; ?>
        </h1>
        
        <?php if ($message): ?>
            <div class="p-3 mb-4 text-sm rounded-lg shadow-md border <?php echo $message_type === 'success' ? 'message-success' : 'message-error'; ?>">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i> 
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form method="POST" action="edit_user.php?id=<?php echo $user_id; ?>" class="space-y-4">
                
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-300 text-left">Full Name</label>
                    <input type="text" id="name" name="name" class="w-full p-3 rounded-lg neon-input text-white" 
                           placeholder="Full Name" required autocomplete="off" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>

                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-300 text-left">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full p-3 rounded-lg neon-input text-white" 
                           placeholder="user@example.com" required autocomplete="off" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                
                <div class="relative">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-300 text-left">New Password (Leave blank to keep existing)</label>
                    <input type="password" id="password" name="password" class="w-full p-3 rounded-lg neon-input text-white pr-10" 
                           placeholder="New Password (Optional)" minlength="8" autocomplete="new-password">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 pt-5 text-neon-blue/80 hover:text-neon-blue transition-colors">
                        <i id="toggleIcon" class="fas fa-eye-slash"></i>
                    </button>
                </div>
                
                <button type="submit" class="w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                    bg-neon-green text-dark-card border-2 border-neon-green hover:bg-green-400 
                    shadow-lg shadow-neon-green/50 hover:shadow-neon-green/70 mt-6">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
                
            </form>
        <?php elseif (!$message): ?>
            <p class="text-gray-400 mt-4">Could not load user data.</p>
        <?php endif; ?>
    </div>

    <script>
        // Password toggle logic (reused from admin_login)
        document.getElementById('togglePassword')?.addEventListener('click', function () {
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
