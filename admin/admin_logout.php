<?php
// ========================================================
// admin_logout.php — Confirmation Page for Admin Logout
// Location: /admin/
// ========================================================

session_start();

// --------------------------------------------------------
// 1. SECURITY & DATA PREP
// --------------------------------------------------------
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, just send them to the login page
    header('Location: admin_login.php'); 
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// --------------------------------------------------------
// 2. HTML CONFIRMATION VIEW
// --------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Confirm Admin Logout</title>
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
                        'icon-red': '#DD0303',
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(0, 204, 255, 0.5);
        }
    </style>
</head>
<body class="dark flex items-center justify-center min-h-screen">
    
    <div class="w-full max-w-md p-8 text-center neon-card">
        
        <div class="mx-auto w-16 h-16 mb-4 rounded-full text-white bg-icon-red flex items-center justify-center border-2 border-icon-red">
            <i class="fas fa-sign-out-alt text-3xl" aria-hidden="true"></i>
        </div>
        
        <h1 class="text-3xl font-extrabold font-bubble text-neon-pink mb-2">
            Admin Session
        </h1>
        <p class="text-lg text-gray-300 mb-8">
            Hello, **<?php echo htmlspecialchars($admin_name); ?>**. Are you sure you want to log out of the Administrator Panel?
        </p>
        
        <div class="flex flex-col space-y-4">
            
            <!-- Yes, Logout Button -->
            <!-- This points to the actual session destruction endpoint -->
            <a href="process_admin_logout.php" class="block w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                bg-icon-red text-white border-2 border-icon-red hover:bg-red-800 
                shadow-lg shadow-icon-red/50">
                <i class="fas fa-check-circle mr-2"></i> Yes, Log Me Out
            </a>

            <!-- No, Go Back Button -->
            <a href="admin_dashboard.php" class="block w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                bg-neon-green text-dark-card border-2 border-neon-green hover:bg-green-400 
                shadow-lg shadow-neon-green/50">
                <i class="fas fa-times-circle mr-2"></i> No, Go Back to Dashboard
            </a>
            
        </div>
    </div>

</body>
</html>
