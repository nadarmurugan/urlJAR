<?php
// ========================================================
// analytics.php — urlJAR Statistical Analysis Dashboard (Enhanced Charts)
// ========================================================

session_start();

// --------------------------------------------------------
// INCLUDE PDO DATABASE CONNECTION
// This line now makes the $pdo object available.
// --------------------------------------------------------
require_once 'includes/config.php'; 


// --------------------------------------------------------
// 1. SECURITY & SESSION CHECK
// --------------------------------------------------------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_name']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_id = (int)$_SESSION['user_id']; 
$initials = substr(strtoupper(preg_replace('/[^A-Za-z0-9\s]/', '', $user_name)), 0, 2); 
if (str_word_count($user_name) >= 2) {
    $parts = explode(' ', $user_name);
    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}
$initials = substr($initials, 0, 2); // Ensure max 2 initials


// --------------------------------------------------------
// 3. ANALYTICS QUERIES & DATA PREPARATION
// --------------------------------------------------------

$analytics_data = [
    'total_links' => 0,
    'total_jars' => 0,
    'first_link_date' => 'N/A',
    'last_link_date' => 'N/A',
    'top_jars' => [],
    'links_by_month' => [],
];

// A. Get Total Counts and First/Last Link Dates
// CONVERTED TO USE NAMED PLACEHOLDERS (:user_id_...) for PDO
$sql_summary = "
    SELECT 
        COUNT(DISTINCT j.jar_id) AS total_jars,
        COALESCE(SUM(j.link_count), 0) AS total_links,
        (SELECT MIN(created_at) FROM links WHERE user_id = :user_id_1) AS first_link,
        (SELECT MAX(created_at) FROM links WHERE user_id = :user_id_2) AS last_link
    FROM jars j
    WHERE j.user_id = :user_id_3
";

// REPLACED $mysqli->prepare WITH $pdo->prepare AND UPDATED EXECUTION
try {
    $stmt = $pdo->prepare($sql_summary);
    $stmt->bindParam(':user_id_1', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id_2', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id_3', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Use PDO fetch
        $analytics_data['total_jars'] = (int)$row['total_jars'];
        $analytics_data['total_links'] = (int)$row['total_links'];
        if ($row['first_link']) {
            $analytics_data['first_link_date'] = date('M j, Y', strtotime($row['first_link']));
        }
        if ($row['last_link']) {
            $analytics_data['last_link_date'] = date('M j, Y', strtotime($row['last_link']));
        }
    }
} catch (PDOException $e) {
    error_log("Analytics Summary Query Error: " . $e->getMessage());
    // Graceful error handling for the user
}


// B. Top 5 Most Populated Jars (by link count)
// CONVERTED TO USE NAMED PLACEHOLDERS for PDO
$sql_top_jars = "
    SELECT name, link_count 
    FROM jars 
    WHERE user_id = :user_id 
    ORDER BY link_count DESC, name ASC 
    LIMIT 5
";

$total_links_in_top_5 = 0;
// REPLACED $mysqli->prepare WITH $pdo->prepare AND UPDATED EXECUTION
try {
    $stmt = $pdo->prepare($sql_top_jars);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Use PDO fetch
        $count = (int)$row['link_count'];
        $analytics_data['top_jars'][] = [
            'name' => htmlspecialchars($row['name']),
            'count' => $count
        ];
        $total_links_in_top_5 += $count;
    }
} catch (PDOException $e) {
    error_log("Analytics Top Jars Query Error: " . $e->getMessage());
    // Graceful error handling for the user
}


// Prepare data for Top Jar Pie Chart
$top_jar_chart_labels = [];
$top_jar_chart_data = [];
$top_jar_colors = ['#FF0099', '#00CCFF', '#00FF77', '#9D00FF', '#FF8800'];
$top_jar_chart_colors = [];
$other_links_count = $analytics_data['total_links'] - $total_links_in_top_5;

foreach ($analytics_data['top_jars'] as $index => $jar) {
    $top_jar_chart_labels[] = $jar['name'];
    $top_jar_chart_data[] = $jar['count'];
    $top_jar_chart_colors[] = $top_jar_colors[$index % count($top_jar_colors)];
}

if ($other_links_count > 0 || empty($analytics_data['top_jars'])) {
    $top_jar_chart_labels[] = 'Other Jars';
    $top_jar_chart_data[] = $other_links_count > 0 ? $other_links_count : $analytics_data['total_links'];
    $top_jar_chart_colors[] = '#475569'; // Slate for 'Other'
}


// C. Links Added Over Time (Grouped by Month)
// CONVERTED TO USE NAMED PLACEHOLDERS for PDO
$sql_links_by_month = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month_year,
        COUNT(link_id) AS links_added
    FROM links
    WHERE user_id = :user_id
    GROUP BY month_year
    ORDER BY month_year ASC
";

// REPLACED $mysqli->prepare WITH $pdo->prepare AND UPDATED EXECUTION
try {
    $stmt = $pdo->prepare($sql_links_by_month);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Use PDO fetch
        $analytics_data['links_by_month'][ $row['month_year'] ] = (int)$row['links_added'];
    }
} catch (PDOException $e) {
    error_log("Analytics Links By Month Query Error: " . $e->getMessage());
    // Graceful error handling for the user
}


// Prepare data for Links By Month Line Chart
$line_chart_labels = [];
$line_chart_data = [];
foreach ($analytics_data['links_by_month'] as $month_year => $count) {
    // Format YYYY-MM to MMM YYYY for display
    $formatted_month = date('M Y', strtotime($month_year . '-01'));
    $line_chart_labels[] = $formatted_month;
    $line_chart_data[] = $count;
}


// --------------------------------------------------------
// 4. CONNECTION CLOSURE & TEMPLATE RENDER
// --------------------------------------------------------
// REMOVED $mysqli->close(); - PDO connections automatically close when the script ends.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Analytics & Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <script>
        // Tailwind Config (Same as dashboard.php for consistency)
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
                        'star-gold': '#FFD700',
                    },
                    fontFamily: {
                        'bubble': ['Poppins', 'sans-serif'],
                        'heading': ['Bungee Outline', 'cursive'],
                        'bebas': ['Bebas Neue', 'sans-serif'],
                        'inter': ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                }
            }
        }
    </script>
    
    <style>
/* Base styles */
body {
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

/* --- THEME STYLES (Simplified for brevity, inherited from dashboard) --- */
body.light {
  background-color: #6db76eff; 
  color: #5a063fff; 
  background-image: repeating-linear-gradient(45deg,#ffffff 0px,#ffffff 2px,#dfe3e8 2px,#dfe3e8 4px), repeating-linear-gradient(-45deg,#ffffff 0px,#ffffff 2px,#dfe3e8 2px,#dfe3e8 4px);
  background-blend-mode: overlay; 
  background-size: 20px 20px; 
  background-attachment: fixed; 
}
body.dark {
    background-color: #0a0a0c;
    color: #e6e6e6;
    background-image: repeating-linear-gradient(45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px), repeating-linear-gradient(-45deg, #1f1f1f 0px, #1f1f1f 2px, #0e0e0e 2px, #0e0e0e 4px);
    background-size: 20px 20px;
    background-attachment: fixed;
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
.neon-card {
    border-radius: 16px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}
body.dark .neon-card { background: rgba(15, 15, 17, 0.85); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5); }
body.light .neon-card { background: rgba(255, 255, 255, 0.9); box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }

/* Theming/Sidebar Links */
body.dark .text-secondary { color: #b0b0b0; }
body.light .text-secondary { color: #64748b; }
body.dark .sidebar-link.active { background-color: rgba(0, 255, 119, 0.1); color: #00FF77; }
body.light .sidebar-link.active { background-color: rgba(0, 255, 119, 0.15); color: #00a55a; }

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

/* Chart Container styling */
.chart-container {
    padding: 2rem;
    position: relative;
    max-height: 500px;
}
body.dark .chart-container {
    background: rgba(15, 15, 17, 0.95);
    border: 1px solid rgba(0, 255, 119, 0.1);
}
body.light .chart-container {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0, 165, 90, 0.1);
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
             <h2 class="text-xl font-bold font-bubble text-neon-green">Data Insights</h2>
        </div>
        
        <div class="hidden lg:flex items-center space-x-4 ml-auto">
            
             <div class="flex items-center space-x-2">
                <i class="fas fa-sun text-yellow-500 text-lg"></i>
                <div id="theme-toggle-desktop" class="theme-toggle w-[60px] h-[30px] rounded-[15px] relative cursor-pointer bg-gray-700">
                    <div class="theme-toggle-handle w-[24px] h-[24px] rounded-full absolute top-[3px] transition-all bg-yellow-400"></div>
                </div>
                <i class="fas fa-moon text-indigo-400 text-lg"></i>
            </div>
            
            <a href="dashboard.php" class="text-gray-300 hover:text-neon-teal transition-colors text-lg" aria-label="Go back to Dashboard">
                <i class="fas fa-arrow-left mr-2"></i> Dashboard
             </a>
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
            
            <div class="flex items-center justify-between mb-4">
                <a href="#" title="User Profile" class="w-10 h-10 rounded-full text-neon-purple neon-border-glow flex items-center justify-center cursor-pointer hover-lift border-neon-purple">
                    <span class="font-bold text-sm font-bebas"><?php echo $initials; ?></span>
                </a>
                <button id="new-jar-cta-mobile" class="neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta" title="Create a New Collection" onclick="alert('Open Create Jar Modal')">
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
        <li><a href="jars.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link font-medium transition-colors hover-lift"><i class="fas fa-link text-xl" aria-hidden="true"></i><span class="font-bubble">All Jars</span></a></li>
        <li><a href="analytics.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link active font-extrabold transition-colors hover-lift" aria-current="page"><i class="fas fa-chart-bar text-xl" aria-hidden="true"></i><span class="font-bubble">Analytics</span></a></li>
        <li><a href="logout.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link text-icon-red font-medium transition-colors hover-lift"><i class="fas fa-sign-out-alt text-xl" aria-hidden="true"></i><span class="font-bubble">Logout</span></a></li>
    </ul>
</nav>
    
<div id="sidebar-backdrop" class="sidebar-backdrop lg:hidden"></div>

<main class="content-area min-h-screen py-8 lg:py-10 transition-all duration-300">
    
    <header class="mb-10 lg:mb-12 pt-16 lg:pt-20 px-4 lg:px-12">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-2 font-bubble">
            <i class="fas fa-chart-line text-neon-green mr-2"></i> Your <span class="text-neon-blue">urlJAR</span> Analytics
        </h1>
        <p class="text-xl text-gray-400 font-light">
            In-depth statistics about your link saving habits.
        </p>
    </header>

    <h2 class="text-3xl font-extrabold mb-6 text-black-600 border-b border-gray-700 pb-2 px-4 lg:px-12">
        At a Glance
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12 px-4 lg:px-12">
        
        <article class="neon-card p-6 border-neon-blue neon-border-glow text-neon-blue hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble"><?php echo number_format($analytics_data['total_links']); ?></h3>
                    <p class="text-gray-400 text-sm">Total Saved Links</p>
                </div>
                <i class="fas fa-link text-3xl opacity-80"></i>
            </div>
        </article>

        <article class="neon-card p-6 border-neon-pink neon-border-glow text-neon-pink hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble"><?php echo number_format($analytics_data['total_jars']); ?></h3>
                    <p class="text-gray-400 text-sm">Active Jars</p>
                </div>
                <i class="fas fa-layer-group text-3xl opacity-80"></i>
            </div>
        </article>
        
        <article class="neon-card p-6 border-neon-green neon-border-glow text-neon-green hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-extrabold font-bubble leading-tight"><?php echo $analytics_data['first_link_date']; ?></h3>
                    <p class="text-gray-400 text-sm">First Link Saved</p>
                </div>
                <i class="fas fa-calendar-alt text-3xl opacity-80"></i>
            </div>
        </article>

        <article class="neon-card p-6 border-neon-orange neon-border-glow text-neon-orange hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-extrabold font-bubble leading-tight"><?php echo $analytics_data['last_link_date']; ?></h3>
                    <p class="text-gray-400 text-sm">Most Recent Link</p>
                </div>
                <i class="fas fa-clock text-3xl opacity-80"></i>
            </div>
        </article>
    </div>
    
    <h2 class="text-3xl font-extrabold mb-6 text-black-800 border-b border-gray-700 pb-2 px-4 lg:px-12">
        Usage Trends
    </h2>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12 px-4 lg:px-12">
        
        <div class="lg:col-span-2 neon-card rounded-xl chart-container border-2 border-neon-green/30">
            <h3 class="text-xl font-bold font-bubble text-neon-green mb-4">Links Added Per Month</h3>
            <?php if (!empty($line_chart_data) && array_sum($line_chart_data) > 0): ?>
                <div class="relative h-96">
                    <canvas id="linksByMonthChart"></canvas>
                </div>
            <?php else: ?>
                <div class="py-12 text-center text-gray-400">
                    <i class="fas fa-chart-area text-5xl mb-3"></i>
                    <p>Start saving links to see your monthly trends here!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="lg:col-span-1 neon-card p-6 rounded-xl border-2 border-neon-blue/30 flex flex-col">
            <h3 class="text-xl font-bold font-bubble text-neon-blue mb-4">Top Jar Distribution</h3>
            <?php if ($analytics_data['total_links'] > 0): ?>
                <div class="relative flex-grow flex items-center justify-center min-h-[300px] h-full">
                    <canvas id="topJarsPieChart"></canvas>
                </div>
                <div class="text-center mt-4 text-sm text-gray-400">
                    Showing distribution of all **<?php echo number_format($analytics_data['total_links']); ?>** links.
                </div>
            <?php else: ?>
                <div class="py-12 text-center text-gray-400 flex-grow flex flex-col justify-center">
                    <i class="fas fa-chart-pie text-5xl mb-3"></i>
                    <p>Your jars are empty! Start saving links to see your distribution.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <h2 class="text-3xl font-extrabold mb-6 text-black-800 border-b border-gray-700 pb-2 px-4 lg:px-12">
        Top 5 Jars (List)
    </h2>
    <div class="px-4 lg:px-12 mb-12">
        <?php if (!empty($analytics_data['top_jars'])): ?>
            <div class="neon-card p-6 rounded-xl border-2 border-neon-purple/30">
                <ul class="space-y-4">
                    <?php 
                    $color_classes_list = ['text-neon-pink', 'text-neon-orange', 'text-neon-purple', 'text-neon-teal', 'text-neon-green'];
                    foreach ($analytics_data['top_jars'] as $index => $jar): ?>
                        <li class="flex justify-between items-center p-3 rounded-lg bg-gray-900/10 dark:bg-dark-card/50 shadow-md">
                            <span class="font-semibold <?php echo $color_classes_list[$index % count($color_classes_list)]; ?> flex items-center">
                                <i class="fas fa-folder-open mr-2"></i> <?php echo $jar['name']; ?>
                            </span>
                            <span class="text-lg font-extrabold text-white bg-gray-700/50 px-3 py-1 rounded-full shadow-inner">
                                <?php echo number_format($jar['count']); ?> links
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="py-6 text-center text-gray-400 neon-card p-6 rounded-xl">
                <i class="fas fa-trophy text-5xl mb-3"></i>
                <p>No jars yet! Start creating to fill this list.</p>
            </div>
        <?php endif; ?>
    </div>
    
</main>


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
        
        // Chart containers
        linksByMonthChart: document.getElementById('linksByMonthChart'),
        topJarsPieChart: document.getElementById('topJarsPieChart'),
    };
    
    // Global variable to hold chart instances
    window.chartInstances = {};

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
            
            // Re-render charts on theme change to update colors
            const isDark = theme === 'dark';
            Object.values(window.chartInstances).forEach(chart => {
                // Check if the chart has a scales property (i.e., it's not a Pie/Doughnut chart)
                if (chart && chart.options.scales && chart.options.scales.x) { 
                    // This block only runs for charts with axes (like the Line Chart)
                    chart.options.scales.x.title.color = isDark ? '#00CCFF' : '#450693'; 
                    chart.options.scales.x.grid.color = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
                    chart.options.scales.x.ticks.color = isDark ? '#e6e6e6' : '#1a1a1a';
                    chart.options.scales.y.title.color = isDark ? '#FF0099' : '#DD0303';
                    chart.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
                    chart.options.scales.y.ticks.color = isDark ? '#e6e6e6' : '#1a1a1a';
                }
                
                // This property update runs on all charts
                chart.options.color = isDark ? '#e6e6e6' : '#1a1a1a';
                
                // Special update for the Pie chart border color
                if (chart.config.type === 'doughnut') {
                    chart.data.datasets[0].borderColor = isDark ? '#08080A' : '#F8FAFC';
                }
                
                chart.update();
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

    // Module 4: Layout Management (Including Header Width)
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
        }
    };


    // --- Chart.js Initialization ---
    const chartData = {
        lineLabels: <?php echo json_encode($line_chart_labels); ?>,
        lineData: <?php echo json_encode($line_chart_data); ?>,
        pieLabels: <?php echo json_encode($top_jar_chart_labels); ?>,
        pieData: <?php echo json_encode($top_jar_chart_data); ?>,
        pieColors: <?php echo json_encode($top_jar_chart_colors); ?>
    };

    function initializeCharts() {
        if (ELEMENTS.linksByMonthChart && chartData.lineData.length > 0 && chartData.lineData.reduce((a, b) => a + b, 0) > 0) {
            window.chartInstances.linksByMonth = createLineChart(ELEMENTS.linksByMonthChart);
        }
        
        if (ELEMENTS.topJarsPieChart && chartData.pieData.length > 0) {
            window.chartInstances.topJarsPie = createPieChart(ELEMENTS.topJarsPieChart);
        }
        // Apply initial theme colors to the newly created charts
        ThemeManager.applyThemeStyles(ThemeManager.isDarkMode() ? 'dark' : 'light');
    }
    
    function createLineChart(ctx) {
        const isDarkMode = ThemeManager.isDarkMode();
        
        const config = {
            type: 'line',
            data: {
                labels: chartData.lineLabels,
                datasets: [{
                    label: 'Links Added',
                    data: chartData.lineData,
                    backgroundColor: isDarkMode ? 'rgba(0, 255, 119, 0.4)' : 'rgba(0, 165, 90, 0.4)',
                    borderColor: isDarkMode ? '#00FF77' : '#00a55a', 
                    borderWidth: 2,
                    pointBackgroundColor: isDarkMode ? '#00CCFF' : '#450693',
                    pointBorderColor: isDarkMode ? '#0F0F11' : '#FFFFFF',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Month', color: isDarkMode ? '#00CCFF' : '#450693' },
                        grid: { color: isDarkMode ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: isDarkMode ? '#e6e6e6' : '#1a1a1a' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Total Links', color: isDarkMode ? '#FF0099' : '#DD0303' },
                        grid: { color: isDarkMode ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            color: isDarkMode ? '#e6e6e6' : '#1a1a1a',
                            stepSize: 1,
                            callback: function(value) { if (value % 1 === 0) return value; }
                        }
                    }
                },
                color: isDarkMode ? '#e6e6e6' : '#1a1a1a'
            }
        };
        return new Chart(ctx, config);
    }
    
    function createPieChart(ctx) {
        const isDarkMode = ThemeManager.isDarkMode();

        const config = {
            type: 'doughnut',
            data: {
                labels: chartData.pieLabels,
                datasets: [{
                    data: chartData.pieData,
                    backgroundColor: chartData.pieColors,
                    borderColor: isDarkMode ? '#08080A' : '#F8FAFC', // Separator color
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: isDarkMode ? '#e6e6e6' : '#1a1a1a',
                            font: { size: 14, family: 'Poppins' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat().format(context.parsed) + ' links';
                                }
                                return label;
                            }
                        }
                    }
                },
                color: isDarkMode ? '#e6e6e6' : '#1a1a1a',
                layout: { padding: 10 }
            }
        };
        return new Chart(ctx, config);
    }


    // Initialize all modules when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        ThemeManager.initialize();
        SidebarManager.initialize();
        LayoutManager.initialize();
        initializeCharts();
    });

</script>
</body>
</html>