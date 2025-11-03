<?php
// ========================================================
// logout.php — Confirmation Page for urlJAR Logout
// ========================================================

session_start();

// --------------------------------------------------------
// 1. SESSION CHECK & DATA PREP
// --------------------------------------------------------

// If the user isn't logged in, redirect them to the login page immediately.
// Corrected block:
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); // Correct path
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'User';
$initials = '??';
if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
    $initials = substr(strtoupper(preg_replace('/[^A-Za-z0-9\s]/', '', $user_name)), 0, 2); 
    if (str_word_count($user_name) >= 2) {
        $parts = explode(' ', $user_name);
        $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    }
    $initials = substr($initials, 0, 2); 
}

// --------------------------------------------------------
// 2. HTML CONFIRMATION VIEW
// --------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Confirm Logout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <script>
        // Tailwind Config (Ensure theme consistency)
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
        /* Base and Theme Styles for Confirmation Page */
        body {
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        /* Consistent dark theme background */
        body.dark {
            background-color: #0a0a0c;
            color: #e6e6e6;
            background-image: repeating-linear-gradient(45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px), repeating-linear-gradient(-45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px);
            background-size: 20px 20px;
            background-attachment: fixed;
        }
        /* Consistent card styling */
        .neon-card {
            border-radius: 16px;
            backdrop-filter: blur(8px);
            background: rgba(15, 15, 17, 0.85); /* Dark Card background */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        .neon-border-glow {
            border: 2px solid currentColor;
            box-shadow: 0 0 8px currentColor, inset 0 0 4px currentColor;
        }
    </style>
</head>
<body class="dark flex items-center justify-center min-h-screen">
    
    <div class="w-full max-w-md p-8 text-center neon-card border-2 border-neon-pink/50 neon-border-glow" style="border-color: rgba(255, 0, 153, 0.5);">
        
        <div class="mx-auto w-16 h-16 mb-4 rounded-full text-white bg-icon-red flex items-center justify-center neon-border-glow border-icon-red">
            <i class="fas fa-sign-out-alt text-3xl" aria-hidden="true"></i>
        </div>
        
        <h1 class="text-3xl font-extrabold font-bubble text-neon-pink mb-2">
            Confirm Logout
        </h1>
        <p class="text-lg text-gray-300 mb-8">
            Hello, <?php echo htmlspecialchars($user_name); ?>! Are you sure you want to end your session now?
        </p>
        
        <div class="flex flex-col space-y-4">
            
            <a href="api/process_logout.php" class="block w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                bg-icon-red text-white border-2 border-icon-red hover:bg-red-800 
                shadow-lg shadow-icon-red/50 hover:shadow-icon-red/70">
                <i class="fas fa-check-circle mr-2"></i> Yes, Log Me Out
            </a>

            <a href="dashboard.php" class="block w-full py-3 px-6 rounded-xl font-bold text-lg transition-all duration-300 
                bg-neon-green text-black border-2 border-neon-green hover:bg-green-400 
                shadow-lg shadow-neon-green/50 hover:shadow-neon-green/70">
                <i class="fas fa-times-circle mr-2"></i> No, Go Back
            </a>
            
        </div>
    </div>

</body>
</html>