<?php
// ========================================================
// jars.php — View All User Jars/Collections (PDO Migration)
// ========================================================

session_start();
// The config.php file is included here, making the PDO object ($pdo) available.
require_once 'includes/config.php'; 

// --------------------------------------------------------
// 1. SECURITY & SESSION CHECK
// --------------------------------------------------------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_name']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];
$greeting = "All Jars for " . htmlspecialchars($user_name);

// Get initials for avatar
$parts = explode(' ', $user_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1) {
    $initials .= strtoupper(substr(end($parts), 0, 1));
} elseif (strlen($initials) < 2 && strlen($user_name) > 1) {
     $initials = strtoupper(substr($user_name, 0, 2));
}


// --------------------------------------------------------
// 3. FETCH ALL USER'S JARS
// --------------------------------------------------------
$jars = [];
$total_jars = 0;

// Fetch ALL jars, ordered by whether they are starred, then by creation date.
// Using PDO named placeholder :user_id
$sql = "SELECT jar_id, name, description, is_starred, link_count, created_at FROM jars WHERE user_id = :user_id ORDER BY is_starred DESC, created_at DESC";

// FIXED: Using $pdo->prepare and PDO binding/fetching
try {
    $stmt = $pdo->prepare($sql);
    
    // Bind the user_id parameter
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Fetch all results directly using PDO
        $jars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_jars = count($jars);
    }
} catch (PDOException $e) {
    // Log the error instead of crashing the application
    error_log("Jars Fetch Query Error: " . $e->getMessage());
    // Optionally set a friendly message for the user if the data fails to load
    // $error_message = "Could not load your jars. Please try again.";
}

// REMOVED $mysqli->close(); - PDO handles connection closure automatically.

// --- Start HTML Template Here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | All Jars</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <script>
        // --- TAILWIND CONFIG (COPIED FROM DASHBOARD.PHP) ---
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'neon-pink': '#FF0099',
                        'neon-blue': '#00CCFF',
                        'neon-green': '#00FF77',
                        'neon-purple': '#9D00FF',
                        'neon-orange': '#FF8800',
                        'neon-teal': '#00FFFF',
                        'icon-red': '#DD0303',
                        'dark-background': '#08080A',
                        'light-background': '#F8FAFC',
                        'dark-card': '#0F0F11',
                        'light-card': '#FFFFFF',
                        'vibrant-indigo': '#450693', 
                        'vibrant-cyan': '#6F00FF', 
                        'vibrant-lime': '#FFC400', 
                        'vibrant-rose': '#FF3F7F', 
                        'vibrant-amber': '#FFC400',
                        'star-gold': '#FFD700',
                        'star-gold-dark': '#FFC400',
                        'card-green': '#ecf39e',
                        'card-dark-green': '#31572c',
                        'card-light-green': '#90a955',
                    },
                    fontFamily: {
                        'bubble': ['Poppins', 'sans-serif'],
                        'heading': ['Bungee Outline', 'cursive'],
                        'bebas': ['Bebas Neue', 'sans-serif'],
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'gradient-shift': 'gradientShift 3s ease infinite', 
                        'star-pulse': 'starPulse 2s ease-in-out infinite',
                        'bounce-in': 'bounceIn 0.5s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        pulse: { 
                            '0%': { 'box-shadow': '0 0 0 0 rgba(0, 255, 119, 0.7)' },
                            '70%': { 'box-shadow': '0 0 0 10px rgba(0, 255, 119, 0)' },
                            '100%': { 'box-shadow': '0 0 0 0 rgba(0, 255, 119, 0)' },
                        },
                        gradientShift: { 
                             '0%': { 'background-position': '0% 50%' },
                             '50%': { 'background-position': '100% 50%' },
                             '100%': { 'background-position': '0% 50%' },
                        },
                        starPulse: {
                            '0%, 100%': { 
                                transform: 'scale(1)',
                                'text-shadow': '0 0 5px currentColor'
                            },
                            '50%': { 
                                transform: 'scale(1.1)',
                                'text-shadow': '0 0 15px currentColor, 0 0 25px currentColor'
                            },
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.3)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)', opacity: '0.8' },
                            '70%': { transform: 'scale(0.9)', opacity: '0.9' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
/* --- BASE STYLES & THEME (COPIED FROM DASHBOARD.PHP) --- */
body {
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

body.light {
  background-color: #6db76eff; 
  color: #5a063fff; 
  
  background-image: 
    repeating-linear-gradient(
      45deg, #ffffff 0px, #ffffff 2px, #dfe3e8 2px, #dfe3e8 4px
    ),
    repeating-linear-gradient(
      -45deg, #ffffff 0px, #ffffff 2px, #dfe3e8 2px, #dfe3e8 4px
    );
    
  background-blend-mode: overlay;
  background-size: 20px 20px;
  background-attachment: fixed;
  transition: background 0.5s ease, color 0.5s ease;
}


body.dark {
    background-color: #0a0a0c;
    color: #e6e6e6;
    background-image:
        repeating-linear-gradient(45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px),
        repeating-linear-gradient(-45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px);
    background-size: 20px 20px;
    background-attachment: fixed;
}

/* --- CUSTOM FONT AND GLOW --- */
.custom-greeting-font {
    font-family: 'Comic Sans MS', 'Comic Sans', cursive; 
    letter-spacing: 2px;
    text-transform: uppercase;
    font-weight: 700;
}

.neon-border-glow {
    border: 2px solid currentColor;
    box-shadow: 0 0 8px currentColor, inset 0 0 4px currentColor;
}
body.light .neon-border-glow {
    border: 1px solid currentColor;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
}
.hover-lift {
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s;
    will-change: transform, box-shadow;
}
.hover-lift:hover {
    transform: translateY(-5px) scale(1.01);
}
body.light .hover-lift:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}
.neon-btn {
    transition: all 0.3s ease-in-out;
    border: 2px solid;
    font-family: 'Bebas Neue', sans-serif;
    letter-spacing: 1px;
}
.neon-btn:hover {
    box-shadow: 0 0 15px currentColor, 0 0 25px currentColor;
}
.pulse-cta {
    animation: pulse 2s infinite;
}
.neon-card {
    border-radius: 16px;
    border-color: #000;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}
body.dark .neon-card {
    background: rgba(15, 15, 17, 0.85);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
}
body.light .neon-card {
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

/* --- Sidebar & Layout Overrides --- */
#sidebar { width: 250px; transition: transform 0.3s ease-in-out; top: 0; height: 100vh; z-index: 50; }
body.dark #sidebar { background: #08080A; border-right: 1px solid #1a1a1e; }
body.light #sidebar { background: #F8FAFC; border-right: 1px solid #e2e8f0; }
@media (max-width: 1023px) {
    #sidebar { transform: translateX(-100%); }
    #sidebar.active { transform: translateX(0); }
    body.sidebar-open { overflow: hidden; }
}
.content-area {
    padding-left: 265px; 
    padding-right: 33px;
    padding-top: 80px;
    transition: padding-left 0.3s ease, padding-right 0.3s ease;
}
@media (max-width: 1023px) {
    .content-area { padding-left: 0; padding-right: 0; padding-top: 5.5rem; }
}
.sidebar-backdrop {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 49; opacity: 0; visibility: hidden;
    transition: opacity 0.3s ease-in-out, visibility 0.3s;
}
body.sidebar-open .sidebar-backdrop { opacity: 1; visibility: visible; }
body.dark .sidebar-backdrop { background-color: rgba(0, 0, 0, 0.75); }
body.light .sidebar-backdrop { background-color: rgba(0, 0, 0, 0.5); }
.fixed-header {
    background: transparent; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
    border-color: rgba(255, 255, 255, 0.05);
}
body.light .fixed-header {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(0, 0, 0, 0.1);
}
body.dark .text-secondary { color: #b0b0b0; }
body.light .text-secondary { color: #64748b; }
body.dark .sidebar-link.active { background-color: rgba(0, 255, 119, 0.1); color: #00FF77; }
body.light .sidebar-link.active { background-color: rgba(0, 255, 119, 0.15); color: #00a55a; }

/* --- CUSTOM JAR CARD STYLES (MATCHING LINK VIEW) --- */

     /* 🌑 BASE CARD STYLES */
        .jar-card {
                        color: #060606ff;

            background: #0f0f11;
            border-radius: 20px;
            width: 100%;
            padding: 2rem 0.8rem;
            border: none;
            min-height: 240px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.25rem;
            transition: all 0.4s ease;
            box-shadow:
                8px 8px 16px #0a0a0c,
                -8px -8px 16px #141416;
            position: relative;
            overflow: hidden;
        }

           /* Neon glow effect on hover */
        .jar-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #021d98ff, #ff2de7ff);
            z-index: -1;
            border-radius: 22px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .jar-card:hover::before {
            opacity: 1;
            animation: gradientShift 3s ease infinite;
        }

        /* ☀️ LIGHT THEME */
        body.light .jar-card {
            background: #dee2e7ff;
            box-shadow:
                8px 8px 16px #131517ff,
                -8px -8px 16px #d9d4d4ff;
        }

        /* ✨ HOVER EFFECT */
        .jar-card:hover {
            transform: translateY(-5px);
            box-shadow:
                10px 10px 20px #4b4b542b,
                -10px -10px 20px #18181a,
                inset 2px 2px 4px rgba(255, 255, 255, 0.1),
                inset -2px -2px 4px rgba(0, 0, 0, 0.3);
        }

        body.light .jar-card:hover {
            box-shadow:
                10px 10px 20px #0d0e0fff,
                -10px -10px 20px #ffffff,
                inset 2px 2px 4px rgba(255, 255, 255, 0.6),
                inset -2px -2px 4px rgba(0, 0, 0, 0.1);
        }

        /* 🧭 HEADER SECTION */
        .jar-card-header {
            display: flex;
                        color: #060606ff;

            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }

        /* 🏷️ TITLE */
        .jar-card-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: #00ccff;
            margin: 0;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        body.light .jar-card-title {
            color: #4b0082;
        }

        .jar-card:hover .jar-card-title {
            color: #0e0f0eff;
            transform: scale(1.02);
        }

        body.light .jar-card:hover .jar-card-title {
            color: #0b0c0cff;
        }

        /* 📝 DESCRIPTION */
        .jar-description-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #fefefeff;
            flex-grow: 1;
            transition: color 0.3s ease;
        }
    
          

        body.light .jar-description-text {
            color: #060606ff;
        }

        /* 🔘 BUTTON / FOOTER AREA */
        .jar-card-footer {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

/* 🌐 RESPONSIVENESS */
@media (max-width: 1024px) {
  .jar-card {
    padding: 1.75rem;
    min-height: 220px;
  }
  .jar-card-title {
    font-size: 1.4rem;
  }
}

@media (max-width: 768px) {
  .jar-card {
    padding: 1.25rem;
    min-height: 200px;
  }
  .jar-card-title {
    font-size: 1.25rem;
  }
}

@media (max-width: 480px) {
  .jar-card {
    padding: 1rem;
    border-radius: 16px;
  }
  .jar-card-title {
    font-size: 1.1rem;
  }
  .jar-description-text {
    font-size: 0.9rem;
  }
}

/* Star button styles adapted to the new theme */
.star-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  transition: all 0.3s ease;
  padding: 5px;
  border-radius: 50%;
}

.star-btn:hover {
  transform: scale(1.2);
}

.star-btn.starred {
  color: #FFD700;
  animation: starPulse 2s ease-in-out infinite;
}

.star-btn:not(.starred) {
  color: #b0b0b0; /* Use a neutral color when unstarred */
}
body.light .star-btn:not(.starred) {
  color: #64748b;
}
/* 🌟 FOOTER THEME OVERRIDES 🌟 */
footer { transition: background-color 0.3s ease, border-color 0.3s ease; border-color: #1a1a1e; }
body.light footer {
    background-color: #F8FAFC !important; 
    border-top-color: #e2e8f0 !important; 
    color: #1a1a1a; 
}
body.light footer .text-gray-400,
body.light footer .text-gray-500 { color: #64748b; }
body.light footer .neon-border { border: 1px solid currentColor; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
body.light footer a.hover\:text-neon-pink:hover,
body.light footer a.hover\:text-neon-blue:hover,
body.light footer a.hover\:text-neon-green:hover,
body.light footer a.hover\:text-neon-purple:hover { filter: brightness(0.85); }
.fab:hover { text-shadow: 0 0 5px currentColor; }
    </style>
</head>
<body class="dark min-h-screen">
    
<header id="main-header" class="fixed w-full top-0 right-0 z-40 py-3 px-4 transition-all duration-300 fixed-header border-b">
    <div class="flex justify-between items-center h-12">
        
        <button id="mobile-sidebar-toggle" aria-label="Toggle Navigation Menu" class="lg:hidden text-neon-green text-2xl p-2 rounded-lg hover:bg-white/10 dark:hover:bg-black/10 transition-colors focus:outline-none mr-4">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="flex-grow max-w-2xl relative lg:mr-8">
            <input type="search" placeholder="Search All Jars..." aria-label="Search all content"
                    class="w-full px-5 py-3 rounded-full text-sm border search-input focus:outline-none focus:ring-2 focus:ring-neon-blue transition-all bg-transparent shadow-inner">
            <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-neon-blue hover:text-neon-pink transition-colors pointer-events-none"></i>
        </div>
        
        <div class="hidden lg:flex items-center space-x-4 ml-auto">
            
            <div class="flex items-center space-x-2">
                <i class="fas fa-sun text-yellow-500 text-lg"></i>
                <div id="theme-toggle-desktop" class="theme-toggle w-[60px] h-[30px] rounded-[15px] relative cursor-pointer bg-gray-700">
                    <div class="theme-toggle-handle w-[24px] h-[24px] rounded-full absolute top-[3px] transition-all bg-yellow-400"></div>
                </div>
                <i class="fas fa-moon text-indigo-400 text-lg"></i>
            </div>
            
            <button id="new-jar-cta-header" class="neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta" title="Create a New Collection" onclick="alert('Open Create Jar Modal')">
                <i class="fas fa-plus mr-2"></i> NEW JAR
            </button>
        
            
            <a href="#" title="User Profile" class="block w-10 h-10 rounded-full text-neon-purple neon-border-glow flex items-center justify-center cursor-pointer hover-lift border-neon-purple">
                <span class="font-bold text-sm font-bebas"><?php echo $initials; ?></span>
            </a>
        </div>
    </div>
</header>

<nav id="sidebar" class="fixed top-0 left-0 h-full w-[250px] z-50 pt-4 pb-4 flex flex-col transition-transform duration-300 lg:translate-x-0 overflow-y-auto">
    
    <a href="dashboard.php" class="flex items-center space-x-2 px-6 pt-1 pb-6 mb-4 border-b border-gray-700 dark:border-gray-900" title="Go to Dashboard">
        <div class="w-8 h-8 bg-neon-pink rounded-xl flex items-center justify-center neon-border-glow border-neon-pink">
            <i class="fas fa-bookmark text-white text-lg" aria-hidden="true"></i>
        </div>
        <span class="text-3xl font-extrabold font-heading text-neon-blue">url<span class="text-neon-pink">JAR</span></span>
    </a>
    
    <ul class="flex flex-col space-y-2 text-base flex-grow px-4">
        
        <li class="lg:hidden pt-2 pb-4 border-b border-gray-700 dark:border-gray-900 mb-2">
            
            <div class="flex justify-between items-center mb-4">
                <a href="#" title="User Profile" class="w-10 h-10 rounded-full text-neon-purple neon-border-glow flex items-center justify-center cursor-pointer hover-lift border-neon-purple">
                    <span class="font-bold text-sm font-bebas"><?php echo $initials; ?></span>
                </a>
                <button id="new-jar-cta-mobile" class="w-full neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta mb-4" title="Create a New Collection" onclick="alert('Open Create Jar Modal')">
                    <i class="fas fa-plus mr-2"></i> NEW JAR
                </button>
            </div>
            
            <div class="flex items-center justify-between text-sm text-secondary font-medium">
                <span>Toggle Theme</span>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-sun text-yellow-500 text-lg"></i>
                    <div id="theme-toggle-mobile" class="theme-toggle w-[60px] h-[30px] rounded-[15px] relative cursor-pointer bg-gray-700">
                        <div class="theme-toggle-handle w-[24px] h-[24px] rounded-full absolute top-[3px] transition-all bg-yellow-400"></div>
                    </div>
                    <i class="fas fa-moon text-indigo-400 text-lg"></i>
                </div>
            </div>
        </li>

       <li><a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link font-medium transition-colors hover-lift"><i class="fas fa-home text-xl" aria-hidden="true"></i><span class="font-bubble">Dashboard</span></a></li>
       <li><a href="jars.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link active font-extrabold transition-colors hover-lift" aria-current="page"><i class="fas fa-link text-xl" aria-hidden="true"></i><span class="font-bubble">All Jars</span></a></li>
       <li><a href="analytics.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link font-medium transition-colors hover-lift"><i class="fas fa-chart-bar text-xl" aria-hidden="true"></i><span class="font-bubble">Analytics</span></a></li>
        <li><a href="logout.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link text-icon-red font-medium transition-colors hover-lift"><i class="fas fa-sign-out-alt text-xl" aria-hidden="true"></i><span class="font-bubble">Logout</span></a></li>
    </ul>
</nav>
    
<div id="sidebar-backdrop" class="sidebar-backdrop lg:hidden"></div>

<main class="content-area min-h-screen py-8 lg:py-10 transition-all duration-300">
    
    <header class="mb-12 lg:mb-16 pt-16 lg:pt-20 px-4 lg:px-12 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-2 font-bubble">
            <span class="dynamic-text-fx dynamic-gradient dynamic-gradient-animate neon-glow custom-greeting-font">
                ALL YOUR JARS
            </span>
        </h1>
        <p class="text-xl text-secondary font-light">
            You currently have <?php echo $total_jars; ?> organized collections.
        </p>
    </header>
   
    
    <h2 class="text-2xl font-bold mb-6 font-bubble text-gray-700 dark:text-gray-300 border-b border-gray-8300 dark:border-gray-800 pb-2 px-4 lg:px-12">Collections (<?php echo $total_jars; ?>)</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-4 lg:px-12">
        <?php if ($total_jars > 0): ?>
            <?php foreach ($jars as $jar): ?>
                <?php
                $jar_name = htmlspecialchars($jar['name'] ?? 'Untitled Jar');
                $jar_description = htmlspecialchars($jar['description'] ?? '');
                $jar_created_at = date('M j, Y', strtotime($jar['created_at'] ?? 'now'));
                $jar_starred = (int)($jar['is_starred'] ?? 0); 
                $jar_id = $jar['jar_id'] ?? 0;
                ?>
                
                <div class="jar-card hover-lift" data-jar-id="<?php echo $jar_id; ?>">
                    <div class="jar-card-header">
                        <h3 class="jar-card-title"><?php echo $jar_name; ?></h3>
                        <button class="star-btn <?php echo $jar_starred ? 'starred' : ''; ?>" 
                                data-jar-id="<?php echo $jar_id; ?>" 
                                data-is-starred="<?php echo $jar_starred; ?>"
                                onclick="alert('Star/Unstar functionality is coming soon!')"
                        >
                            <i class="<?php echo $jar_starred ? 'fas' : 'far'; ?> fa-star"></i>
                        </button>
                    </div>

                    <?php if (!empty($jar_description)): ?>
                        <p class="jar-description-text">
                            <?php echo $jar_description; ?>
                        </p>
                    <?php else: ?>
                        <p class="jar-description-text italic">
                            No description provided.
                        </p>
                    <?php endif; ?>
                    
                    <p class="text-xs text-secondary mb-3">
                        <i class="far fa-clock mr-1"></i> Created: <?php echo $jar_created_at; ?>
                    </p>

                    <div class="link-action-bar">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-sm text-neon-green dark:text-neon-teal">
                                <i class="fas fa-link mr-1"></i> <?php echo $jar['link_count'] ?? 0; ?> Links
                            </span>
                            
                            <div class="flex items-center space-x-4">
                                <button class="text-sm text-red-400 hover:text-icon-red transition-colors"
                                        onclick="alert('Delete functionality not yet implemented here.')">
                                    <i class="fas fa-trash-alt text-xs mr-1"></i> Delete
                                </button>
                                
                                <a class="text-sm text-neon-blue hover:text-neon-pink transition-colors font-bold"
                                        href="jar_view.php?jar_id=<?php echo $jar_id; ?>&name=<?php echo urlencode($jar_name); ?>">
                                    View Links <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-10 border border-dashed border-gray-600 rounded-lg bg-gray-900">
                <i class="far fa-lightbulb text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-400">You haven't created any jars yet. Go back to the dashboard to create your first one!</p>
            </div>
        <?php endif; ?>
    </div>
</main>


<footer class="bg-black content-area py-8 lg:py-10 border-t border-gray-800">
    <div class="mb-8 lg:mb-10 pt-6 lg:pt-10 px-4 lg:px-12">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-6 border-b border-gray-900 pb-6 mb-6">
            
            <div class="col-span-2 mb-4 md:mb-0">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-neon-pink rounded-lg flex items-center justify-center neon-border-glow border-neon-pink">
                        <i class="fas fa-bookmark text-dark-background text-lg"></i>
                    </div>
                    <span class="text-2xl font-bold font-heading text-neon-blue neon-glow">
                        url<span class="text-neon-pink">JAR</span>
                    </span>
                </div>
                <p class="text-gray-400 text-sm max-w-sm">
                    Save, organize, and access your bookmarks across all devices.
                </p>
            </div>

            <div>
                <h4 class="font-bold mb-3 text-neon-green font-bubble">PRODUCT</h4>
                <ul class="space-y-1.5 text-gray-400 text-sm">
                    <li><a href="#features" class="hover:text-neon-green transition-colors">Features</a></li>
                    <li><a href="#how-it-works" class="hover:text-neon-green transition-colors">Workflow</a></li>
                    <li><a href="#try-now" class="hover:text-neon-green transition-colors">Demo</a></li>
                    <li><a href="#faq" class="hover:text-neon-green transition-colors">FAQ</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold mb-3 text-neon-blue font-bubble">COMPANY</h4>
                <ul class="space-y-1.5 text-gray-400 text-sm">
                    <li><a href="#" class="hover:text-neon-blue transition-colors">About</a></li>
                    <li><a href="#" class="hover:text-neon-blue transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-neon-blue transition-colors">Careers</a></li>
                    <li><a href="#" class="hover:text-neon-blue transition-colors">Press</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold mb-3 text-neon-pink font-bubble">LEGAL</h4>
                <ul class="space-y-1.5 text-gray-400 text-sm">
                    <li><a href="#" class="hover:text-neon-pink transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-neon-pink transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-neon-pink transition-colors">Security</a></li>
                    <li><a href="#" class="hover:text-neon-pink transition-colors">Sitemap</a></li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-left">
            <p class="text-gray-500 text-xs mb-3 md:mb-0">
                © 2024 <span class="text-neon-blue font-semibold">url</span><span class="text-neon-pink font-semibold">JAR</span>. 
                Made with ✨ for digital natives.
            </p>
            <div class="flex space-x-5 text-lg">
                <a href="#" class="text-gray-400 hover:text-neon-pink transition-colors"><i class="fab fa-tiktok"></i></a>
                <a href="#" class="text-gray-400 hover:text-neon-blue transition-colors"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-400 hover:text-neon-green transition-colors"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-gray-400 hover:text-neon-purple transition-colors"><i class="fab fa-discord"></i></a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Module 1: Core Elements
    const ELEMENTS = {
        body: document.body,
        sidebar: document.getElementById('sidebar'),
        toggleButton: document.getElementById('mobile-sidebar-toggle'),
        backdrop: document.getElementById('sidebar-backdrop'),
        header: document.getElementById('main-header'),
        
        // Theme Toggles
        themeToggleDesktop: document.getElementById('theme-toggle-desktop'), 
        themeToggleMobile: document.getElementById('theme-toggle-mobile'),
    };

    // Module 2: Theme Management 
    const ThemeManager = {
        isDarkMode: () => ELEMENTS.body.classList.contains('dark'),

        applyThemeStyles: (theme) => {
            ELEMENTS.body.classList.remove('light', 'dark');
            ELEMENTS.body.classList.add(theme);
            localStorage.setItem('theme', theme);
            
            document.querySelectorAll('.theme-toggle-handle').forEach(handle => {
                handle.style.left = theme === 'dark' ? '33px' : '3px';
                handle.style.backgroundColor = theme === 'dark' ? '#fbbf24' : '#f59e0b';
            });
        },

        handleThemeToggle: (event) => {
            event.stopPropagation();
            const newTheme = ThemeManager.isDarkMode() ? 'light' : 'dark';
            ThemeManager.applyThemeStyles(newTheme);
        },

        initialize: () => {
            const savedTheme = localStorage.getItem('theme');
            let initialTheme = savedTheme;
            
            if (!initialTheme) {
                initialTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            }

            ThemeManager.applyThemeStyles(initialTheme);
            
            if (ELEMENTS.themeToggleDesktop) ELEMENTS.themeToggleDesktop.addEventListener('click', ThemeManager.handleThemeToggle);
            if (ELEMENTS.themeToggleMobile) ELEMENTS.themeToggleMobile.addEventListener('click', ThemeManager.handleThemeToggle);
        }
    };

    // Module 3: Sidebar Management 
    const SidebarManager = {
        close: () => {
            if(ELEMENTS.sidebar) ELEMENTS.sidebar.classList.remove('active');
            if(ELEMENTS.body) ELEMENTS.body.classList.remove('sidebar-open');
        },

        open: () => {
            if(ELEMENTS.sidebar) ELEMENTS.sidebar.classList.add('active');
            if(ELEMENTS.body) ELEMENTS.body.classList.add('sidebar-open');
        },

        initialize: () => {
            if (ELEMENTS.toggleButton && ELEMENTS.sidebar && ELEMENTS.backdrop) {
                ELEMENTS.toggleButton.addEventListener('click', () => {
                    if (ELEMENTS.sidebar.classList.contains('active')) {
                        SidebarManager.close();
                    } else {
                        SidebarManager.open();
                    }
                });

                ELEMENTS.backdrop.addEventListener('click', SidebarManager.close);
                
                ELEMENTS.sidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 1024) SidebarManager.close();
                    });
                });
            }
        }
    };

    // Module 4: Layout Management 
    const LayoutManager = {
        SIDEBAR_WIDTH_DESKTOP: 250,

        updateHeaderWidth: () => {
            if (!ELEMENTS.header) return;
            if (window.innerWidth >= 1024) {
                ELEMENTS.header.style.width = `calc(100% - ${LayoutManager.SIDEBAR_WIDTH_DESKTOP}px)`;
                ELEMENTS.header.style.right = '0';
            } else {
                ELEMENTS.header.style.width = `100%`;
                ELEMENTS.header.style.right = '';
            }
        },

        initialize: () => {
            window.addEventListener('resize', LayoutManager.updateHeaderWidth);
            LayoutManager.updateHeaderWidth();
            
            // Search Input Focus Styling
            document.querySelectorAll('.search-input').forEach(input => {
                input.addEventListener('focus', () => {
                    input.classList.add('ring-2', 'ring-neon-blue');
                });
                input.addEventListener('blur', () => {
                    input.classList.remove('ring-2', 'ring-neon-blue');
                });
            });
        }
    };

    // Initialize all modules when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        ThemeManager.initialize();
        SidebarManager.initialize();
        LayoutManager.initialize();
    });
</script>
</body>
</html>