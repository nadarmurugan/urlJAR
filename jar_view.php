<?php
// ========================================================
// jar_view.php — Individual Jar View for urlJAR (FINAL REFINED)
// ========================================================

session_start();

// --------------------------------------------------------
// 1. SECURITY & SESSION CHECK
// --------------------------------------------------------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_name']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_name = $_SESSION['user_name'];
// Ensure user_id is an integer for security
$user_id = (int)$_SESSION['user_id'];
$jar_id = $_GET['jar_id'] ?? null; 

// Sanitize URL inputs
$jar_id = is_numeric($jar_id) ? (int)$jar_id : null; 
$jar_name_url = $_GET['name'] ?? null; 

// Get initials for avatar (Refined logic for up to 2 characters)
$parts = explode(' ', $user_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1) {
    $initials .= strtoupper(substr(end($parts), 0, 1));
} elseif (strlen($initials) < 2 && strlen($user_name) > 1) {
     $initials = strtoupper(substr($user_name, 0, 2));
}
$initials = substr($initials, 0, 2);


// --------------------------------------------------------
// 2. DATABASE CONFIGURATION & CONNECTION (SECURITY FIX)
// --------------------------------------------------------
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '', // WARNING: Empty password is a security risk in a real application!
    'dbname' => 'urljar'
];

$mysqli = @new mysqli(
    $db_config['host'], 
    $db_config['username'], 
    $db_config['password'], 
    $db_config['dbname']
);

if ($mysqli->connect_error) {
    error_log("Database Connection Error: " . $mysqli->connect_error);
    http_response_code(500);
    die("ERROR: Database connection failed. Please try again later.");
}

// --------------------------------------------------------
// 3. FETCH JAR DETAILS (Crucial for context and security)
// --------------------------------------------------------
$jar_details = null;

if ($jar_id !== null) {
    // Lookup by jar_id: ensures the jar belongs to the user
    $sql = "SELECT jar_id, name, description, link_count FROM jars WHERE user_id = ? AND jar_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ii", $user_id, $jar_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $jar_details = $result->fetch_assoc();
            }
        } else {
             error_log("Jar Details Fetch Error: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Security Check: Redirect if Jar not found or doesn't belong to user
if (!$jar_details) {
    header('Location: dashboard.php');
    exit;
}

// Set Jar Context Variables
$jar_id = (int)$jar_details['jar_id']; // Use the ID confirmed from the DB
$jar_title = htmlspecialchars($jar_details['name'], ENT_QUOTES, 'UTF-8');
$jar_description = htmlspecialchars($jar_details['description'] ?? 'No description.', ENT_QUOTES, 'UTF-8');
// Total links in THIS jar (from link_count column)
$total_links_in_jar = (int)$jar_details['link_count']; 


// --------------------------------------------------------
// 4. CLEANUP: REMOVE UNNECESSARY JAR CREATION/FETCH LOGIC
// --------------------------------------------------------
// The following sections are REMOVED as they belong in dashboard.php:
// - HANDLE NEW JAR CREATION (Section 3 in original)
// - FETCH USER'S JARS (Section 4 in original)
// - The dashboard stats variables: $jars, $total_jars, $favorites.

// --------------------------------------------------------
// 5. HANDLE NEW LINK CREATION
// --------------------------------------------------------
$status_message = '';
$status_type = ''; // success, error, or warning

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    // Sanitize and validate inputs
    $link_url = filter_var(trim($_POST['url']), FILTER_SANITIZE_URL);
    $link_name = trim($_POST['name']);
    $link_tags = trim($_POST['tags']);
    $link_notes = trim($_POST['notes']);
    $link_emoji = trim($_POST['emoji']);

    if (empty($link_url) || empty($link_name)) {
        $status_message = "Link URL and Name are required.";
        $status_type = 'error';
    } elseif (!filter_var($link_url, FILTER_VALIDATE_URL)) {
        $status_message = "The provided URL is not valid.";
        $status_type = 'error';
    } else {
        $sql = "INSERT INTO links (jar_id, user_id, url, name, tags, notes, emoji) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Binding: i i s s s s s (int, int, string, string, string, string, string)
            $stmt->bind_param("iisssss", $param_jar_id, $param_user_id, $param_url, $param_name, $param_tags, $param_notes, $param_emoji);

            $param_jar_id = $jar_id;
            $param_user_id = $user_id;
            $param_url = $link_url;
            $param_name = $link_name;
            $param_tags = $link_tags;
            $param_notes = $link_notes;
            $param_emoji = $link_emoji; // Emoji is typically safe but good to pass through the binding

            if ($stmt->execute()) {
                // UPDATE: Use prepared statement for the link_count update as well for consistency
                $sql_update_count = "UPDATE jars SET link_count = link_count + 1 WHERE jar_id = ?";
                if ($stmt_update = $mysqli->prepare($sql_update_count)) {
                    $stmt_update->bind_param("i", $jar_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                } else {
                    error_log("Link Count Update Prepare Error: " . $mysqli->error);
                }

                header("Location: jar_view.php?jar_id={$jar_id}&status=LinkAdded");
                exit;
            } else {
                error_log("Link Insertion Error: " . $stmt->error);
                $status_message = "Database Error: Could not execute insertion query.";
                $status_type = 'error';
            }
            $stmt->close();
        } else {
            error_log("Link Insert Prepare Error: " . $mysqli->error);
        }
    }
}

// --------------------------------------------------------
// 6. HANDLE LINK DELETION
// --------------------------------------------------------
if (isset($_GET['delete_link_id']) && is_numeric($_GET['delete_link_id'])) {
    $link_id_to_delete = (int)$_GET['delete_link_id'];
    
    // Check ownership before deleting
    $sql = "DELETE FROM links WHERE link_id = ? AND user_id = ? AND jar_id = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("iii", $link_id_to_delete, $user_id, $jar_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Decrement link count in 'jars' table (using prepared statement)
                $sql_update_count = "UPDATE jars SET link_count = link_count - 1 WHERE jar_id = ?";
                if ($stmt_update = $mysqli->prepare($sql_update_count)) {
                    $stmt_update->bind_param("i", $jar_id);
                    $stmt_update->execute();
                    $stmt_update->close();
                } else {
                    error_log("Link Count Update Prepare Error (Delete): " . $mysqli->error);
                }
                
                header("Location: jar_view.php?jar_id={$jar_id}&status=LinkDeleted");
                exit;
            } else {
                $status_message = "Error: Link not found or unauthorized.";
                $status_type = 'error';
            }
        } else {
            error_log("Link Deletion Error: " . $stmt->error);
            $status_message = "Database Error during deletion.";
            $status_type = 'error';
        }
        $stmt->close();
    } else {
        error_log("Link Delete Prepare Error: " . $mysqli->error);
    }
}

// --------------------------------------------------------
// 7. HANDLE LINK EDIT/UPDATE
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_link']) && is_numeric($_POST['link_id'])) {
    
    $link_id_to_update = (int)$_POST['link_id'];
    $link_url = filter_var(trim($_POST['url']), FILTER_SANITIZE_URL);
    $link_name = trim($_POST['name']);
    $link_tags = trim($_POST['tags']);
    $link_notes = trim($_POST['notes']);
    $link_emoji = trim($_POST['emoji']);

    if (empty($link_url) || empty($link_name)) {
        $status_message = "Link URL and Name are required for update.";
        $status_type = 'error';
    } elseif (!filter_var($link_url, FILTER_VALIDATE_URL)) {
        $status_message = "The provided URL is not valid for update.";
        $status_type = 'error';
    } else {
        $sql = "UPDATE links SET url = ?, name = ?, tags = ?, notes = ?, emoji = ? WHERE link_id = ? AND user_id = ? AND jar_id = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            // 's s s s s i i i' binding types
            $stmt->bind_param("sssssiii", $param_url, $param_name, $param_tags, $param_notes, $param_emoji, $param_link_id, $param_user_id, $param_jar_id);

            $param_url = $link_url;
            $param_name = $link_name;
            $param_tags = $link_tags;
            $param_notes = $link_notes;
            $param_emoji = $link_emoji;
            $param_link_id = $link_id_to_update;
            $param_user_id = $user_id;
            $param_jar_id = $jar_id;

            if ($stmt->execute()) {
                 if ($stmt->affected_rows > 0) {
                    header("Location: jar_view.php?jar_id={$jar_id}&status=LinkUpdated");
                    exit;
                 } else {
                    $status_message = "No changes applied, or link was not found/owned.";
                    $status_type = 'warning';
                 }
            } else {
                error_log("Link Update Error: " . $stmt->error);
                $status_message = "Database Error: Could not execute update query.";
                $status_type = 'error';
            }
            $stmt->close();
        } else {
             error_log("Link Update Prepare Error: " . $mysqli->error);
        }
    }
}


// --------------------------------------------------------
// 8. FETCH JAR'S LINKS (FIXED: The actual links needed for the view)
// --------------------------------------------------------
$links = []; // Initialize array for links in the current jar
$total_links = $total_links_in_jar; // Use the count retrieved from jar_details
$saved_links_dashboard_total = 0; // Total links across ALL jars (for the dashboard stats card)


// --- A. Fetch Links in THIS Jar ---
$sql = "SELECT link_id, url, name, tags, notes, emoji, created_at FROM links WHERE user_id = ? AND jar_id = ? ORDER BY created_at DESC";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $jar_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        // NOTE: $total_links_in_jar should match count($links), but we use the column value for consistency.
    } else {
        error_log("Jar Links Fetch Error: " . $stmt->error);
    }
    $stmt->close();
}


// --- B. Fetch Total Links & Jar Count (for Dashboard Stats Cards) ---
// This is done to correctly populate the dashboard-style cards on this page.
$total_jars = 0;
$favorites = 0;

// Fetch ALL jars for dashboard stats
$sql_jars = "SELECT jar_id, is_starred, link_count FROM jars WHERE user_id = ?";
if ($stmt_jars = $mysqli->prepare($sql_jars)) {
    $stmt_jars->bind_param("i", $user_id);
    if ($stmt_jars->execute()) {
        $result_jars = $stmt_jars->get_result();
        while ($row = $result_jars->fetch_assoc()) {
            $saved_links_dashboard_total += (int)$row['link_count'];
            if ((int)$row['is_starred'] === 1) {
                $favorites++;
            }
            $total_jars++;
        }
    }
    $stmt_jars->close();
}


// --------------------------------------------------------
// 9. HANDLE STATUS MESSAGES
// --------------------------------------------------------
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'LinkAdded') {
        $status_message = "Link saved successfully!";
        $status_type = 'success';
    } elseif ($_GET['status'] === 'LinkUpdated') {
        $status_message = "Link updated successfully!";
        $status_type = 'success';
    } elseif ($_GET['status'] === 'LinkDeleted') {
        $status_message = "Link successfully deleted.";
        $status_type = 'warning'; // Using warning color for deletion confirmation
    }
}


$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Jar: <?php echo $jar_title; ?></title>
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
  background-color: #6db76eff; /* Calm greenish-blue base */
  color: #5a063fff; /* Deep magenta text for contrast */
  
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

/* MODAL STYLES (Applies to both Add and Edit Link Modals) */
.modal-overlay {
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 60; 
}
.modal-content {
    max-width: 600px; 
    background-color: var(--dark-card, #0F0F11);
    border: 3px solid var(--neon-blue, #00CCFF);
    box-shadow: 0 0 20px var(--neon-blue, #00CCFF);
    transition: all 0.3s ease;
}
body.light .modal-content {
    background-color: var(--light-card, #FFFFFF);
    border: 3px solid #00a55a; 
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}
.modal-input {
    background-color: #1F2937; 
    border: 1px solid #374151; 
    color: #F3F4F6; 
}
body.light .modal-input {
    background-color: #F3F4F6;
    border: 1px solid #D1D5DB; 
    color: #1F2937;
}

/* --- LINK CARD STYLES --- */
.link-card {
    background: #0F0F11;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid #1a1a1e;
    transition: all 0.3s ease;
}
body.light .link-card {
    background: #FFFFFF;
    border: 1px solid #e2e8f0;
}
.link-card:hover {
    box-shadow: 0 0 15px rgba(0, 255, 119, 0.2);
    transform: translateY(-3px);
    border-color: #00FF77;
}
body.light .link-card:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    border-color: #00a55a;
}
.link-title {
    font-weight: 700;
    color: #00CCFF;
    transition: color 0.3s;
}
body.light .link-title {
    color: #450693;
}
.link-card:hover .link-title {
    color: #00FF77;
}
body.light .link-card:hover .link-title {
    color: #00a55a;
}
.link-url {
    font-size: 0.875rem;
    color: #b0b0b0;
    transition: color 0.3s;
}
body.light .link-url {
    color: #64748b;
}

.link-tag {
    display: inline-block;
    background-color: rgba(255, 0, 153, 0.15);
    color: #FF0099;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-right: 8px;
    margin-top: 8px;
}
body.light .link-tag {
    background-color: rgba(255, 0, 153, 0.1);
    color: #c026d3;
}

/* Status Message Styles */
.message-box {
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.5s ease;
}
.message-box.show {
    opacity: 1;
    transform: translateY(0);
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
            <input type="search" placeholder="Search Links in <?php echo $jar_title; ?>..." aria-label="Search all content"
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
            
            <button id="new-link-cta-header" class="neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta" title="Add a New Link">
                <i class="fas fa-plus mr-2"></i> NEW LINK
            </button>
        
            
            <a href="#" class="text-gray-300 hover:text-neon-teal transition-colors text-xl relative p-2 rounded-full hover:bg-white/10 dark:hover:bg-black/10" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-neon-pink border-2 border-dark-background"></span>
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
            
            <div class="flex justify-between items-center mb-4">
                <a href="#" title="User Profile" class="w-10 h-10 rounded-full text-neon-purple neon-border-glow flex items-center justify-center cursor-pointer hover-lift border-neon-purple">
                    <span class="font-bold text-sm font-bebas"><?php echo $initials; ?></span>
                </a>
                <a href="#" class="text-gray-300 hover:text-neon-teal transition-colors text-xl relative p-2 rounded-full" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-neon-pink border-2 border-dark-background"></span>
                </a>
            </div>
            
            <button id="new-link-cta-mobile" class="w-full neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta mb-4" title="Add a New Link">
                <i class="fas fa-plus mr-2"></i> NEW LINK
            </button>
            
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

     <li><a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link active font-extrabold transition-colors hover-lift" aria-current="page"><i class="fas fa-home text-xl" aria-hidden="true"></i><span class="font-bubble">Dashboard</span></a></li>
        <li><a href="jars.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link font-medium transition-colors hover-lift"><i class="fas fa-link text-xl" aria-hidden="true"></i><span class="font-bubble">All Jars</span></a></li>
        <li><a href="analytics.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link font-medium transition-colors hover-lift"><i class="fas fa-chart-bar text-xl" aria-hidden="true"></i><span class="font-bubble">Analytics</span></a></li>
        <li><a href="logout.php" class="flex items-center space-x-3 p-3 rounded-xl sidebar-link text-icon-red font-medium transition-colors hover-lift"><i class="fas fa-sign-out-alt text-xl" aria-hidden="true"></i><span class="font-bubble">Logout</span></a></li>
    </ul>
</nav>
    
<div id="sidebar-backdrop" class="sidebar-backdrop lg:hidden"></div>

<main class="content-area min-h-screen py-8 lg:py-10 transition-all duration-300">
    
<header class="mb-12 lg:mb-16 pt-16 lg:pt-20 px-4 lg:px-12 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-2 font-bubble">
            <span class="dynamic-text-fx dynamic-gradient dynamic-gradient-animate neon-glow custom-greeting-font">
                <?php echo $jar_title; ?>
            </span>
        </h1>
        <p class="text-xl text-secondary font-light mb-4">
            <?php echo $jar_description; ?>
        </p>
        <div class="flex justify-center items-center space-x-4">
            <button class="neon-btn bg-neon-teal text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-teal hover-lift transition-all duration-300" onclick="alert('Viewing Jar Settings')">
                <i class="fas fa-sliders-h mr-2"></i> Jar Settings
            </button>
            <button id="add-link-cta-main" class="neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-300">
                <i class="fas fa-plus-circle mr-2"></i> Add New Link
            </button>
        </div>
    </header>
    
    <?php if (!empty($status_message)): ?>
        <div id="status-message" class="message-box p-4 rounded-lg mb-6 mx-4 lg:mx-12 show 
            <?php echo $status_type === 'success' ? 'bg-neon-green/20 border border-neon-green text-neon-green' : 
                          ($status_type === 'warning' ? 'bg-neon-orange/20 border border-neon-orange text-neon-orange' : 
                               'bg-icon-red/20 border border-icon-red text-icon-red'); ?>" 
            role="alert">
            <i class="fas <?php echo $status_type === 'success' ? 'fa-check-circle' : ($status_type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle'); ?> mr-2"></i> 
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>
    
     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16 px-4 lg:px-12">
        
        <article class="neon-card p-6 border-neon-blue neon-border-glow text-neon-blue hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble"><?php echo $total_jars; ?></h3>
                    <p class="text-secondary text-sm">Active Jars</p>
                </div>
                <i class="fas fa-layer-group text-3xl opacity-80"></i>
            </div>
        </article>

        <article class="neon-card p-6 border-neon-pink neon-border-glow text-neon-pink hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble"><?php echo $saved_links_dashboard_total; ?></h3>
                    <p class="text-secondary text-sm">Saved Links</p>
                </div>
                <i class="fas fa-link text-3xl opacity-80"></i>
            </div>
        </article>
        
        <article class="neon-card p-6 border-neon-orange neon-border-glow text-neon-orange hover-lift border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble"><?php echo $favorites; ?></h3>
                    <p class="text-secondary text-sm">Favorites</p>
                </div>
                <i class="fas fa-star text-3xl opacity-80"></i>
            </div>
        </article>

        <button id="cta-card-button" class="neon-card p-6 border-neon-green neon-border-glow bg-neon-green/10 flex flex-col justify-center transition-colors cursor-pointer hover-lift text-neon-green border-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold font-bubble">CREATE NEW JAR</h3>
                    <p class="text-sm text-secondary">Start a new collection now</p>
                </div>
                <i class="fas fa-plus-circle text-3xl opacity-80"></i>
            </div>
        </button>
    </div>
    
    
    <h2 class="text-2xl font-bold mb-6 font-bubble text-gray-700 dark:text-gray-300 border-b border-gray-8300 dark:border-gray-800 pb-2 px-4 lg:px-12">All Links (<?php echo $total_links_in_jar; ?>)</h2>
    
    
    <?php if ($total_links_in_jar > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 px-4 lg:px-12">
            <?php foreach ($links as $link): ?>
                <?php 
                    $link_id = $link['link_id'];
                    // Use htmlspecialchars() here for output to prevent XSS
                    $link_name = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');
                    $link_url = htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8');
                    $link_emoji = htmlspecialchars($link['emoji'], ENT_QUOTES, 'UTF-8');
                    $link_notes = htmlspecialchars($link['notes'], ENT_QUOTES, 'UTF-8');
                    $link_tags = htmlspecialchars($link['tags'], ENT_QUOTES, 'UTF-8'); // Full tag string for modal pre-fill
                    $link_tags_array = array_filter(array_map('trim', explode(',', $link['tags']))); // Use the raw tag string for splitting
                ?>
                <div class="link-card hover-lift" 
                     data-link-id="<?php echo $link_id; ?>"
                     data-link-name="<?php echo $link_name; ?>"
                     data-link-url="<?php echo $link_url; ?>"
                     data-link-emoji="<?php echo $link_emoji; ?>"
                     data-link-notes="<?php echo $link_notes; ?>"
                     data-link-tags="<?php echo $link_tags; ?>"
                >
                    <div class="flex items-start justify-between mb-3">
                        <a href="<?php echo $link_url; ?>" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-3 flex-grow">
                            <span class="text-3xl"><?php echo $link_emoji ?: '🔗'; ?></span>
                            <div>
                                <h3 class="text-xl link-title leading-snug"><?php echo $link_name; ?></h3>
                                <p class="link-url truncate max-w-full hover:text-neon-green"><?php echo $link_url; ?></p>
                            </div>
                        </a>
                        <button class="edit-link-btn text-neon-pink hover:text-neon-blue text-lg transition-colors ml-4" title="Edit Link" 
                                data-link-id="<?php echo $link_id; ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>
                    
                    <?php if (!empty($link_notes)): ?>
                        <p class="text-sm text-secondary mb-3 border-l-2 border-neon-blue pl-3 italic">
                            <?php echo $link_notes; ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-2 flex flex-wrap">
                        <?php foreach ($link_tags_array as $tag): ?>
                            <span class="link-tag">#<?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="flex justify-between items-center text-xs mt-4 pt-3 border-t border-gray-800 dark:border-gray-900">
                        <span class="text-secondary"><i class="far fa-clock mr-1"></i> Added: <?php echo date('M j, Y', strtotime($link['created_at'])); ?></span>
                        <button class="delete-link-btn text-icon-red hover:text-red-500 transition-colors" 
                                data-link-id="<?php echo $link_id; ?>"
                                data-link-name="<?php echo $link_name; ?>"
                                onclick="LinkManager.confirmDelete(this.dataset.linkId, this.dataset.linkName)">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="col-span-full text-center py-10 border border-dashed border-gray-600 rounded-lg bg-gray-900 mx-4 lg:mx-12">
            <i class="far fa-lightbulb text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-400">This jar is empty. Click "Add New Link" to get started!</p>
        </div>
    <?php endif; ?>
</main>

<div id="addLinkModal" class="fixed inset-0 hidden items-center justify-center modal-overlay">
    <div class="modal-content p-8 rounded-xl relative">
        <h3 class="text-2xl font-bubble text-neon-green mb-6">Add New Link to <span class="text-neon-blue"><?php echo $jar_title; ?></span></h3>
        <button id="closeAddLinkModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>

        <form action="jar_view.php?jar_id=<?php echo $jar_id; ?>" method="POST">
            <input type="hidden" name="add_link" value="1">
            
            <div class="mb-4">
                <label for="add_link_url" class="block text-gray-300 font-semibold mb-2">URL <span class="text-icon-red">*</span></label>
                <input
                    type="url"
                    id="add_link_url"
                    name="url"
                    required
                    placeholder="https://example.com/great-resource"
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 modal-input"
                >
            </div>
            
            <div class="mb-4">
                <label for="add_link_name" class="block text-gray-300 font-semibold mb-2">Link Name <span class="text-icon-red">*</span></label>
                <input
                    type="text"
                    id="add_link_name"
                    name="name"
                    required
                    maxlength="255"
                    placeholder="E.g., CSS Grid Layout Guide"
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 modal-input"
                >
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="add_link_tags" class="block text-gray-300 font-semibold mb-2">Tags (Comma-Separated)</label>
                    <input
                        type="text"
                        id="add_link_tags"
                        name="tags"
                        maxlength="255"
                        placeholder="e.g., dev, frontend, css"
                        class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-pink focus:ring focus:ring-neon-pink focus:ring-opacity-50 text-gray-100 modal-input"
                    >
                </div>
                <div>
                    <label for="add_link_emoji" class="block text-gray-300 font-semibold mb-2">Emoji</label>
                    <input
                        type="text"
                        id="add_link_emoji"
                        name="emoji"
                        maxlength="5"
                        placeholder="💡, 📚, ⭐"
                        class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-orange focus:ring focus:ring-neon-orange focus:ring-opacity-50 text-gray-100 modal-input"
                    >
                </div>
            </div>

            <div class="mb-6">
                <label for="add_link_notes" class="block text-gray-300 font-semibold mb-2">Notes (Optional)</label>
                <textarea
                    id="add_link_notes"
                    name="notes"
                    rows="3"
                    maxlength="500"
                    placeholder="Quick summary or personal thoughts on this link..."
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 resize-none modal-input"
                ></textarea>
            </div>

            <button type="submit" class="neon-btn bg-neon-green text-black font-extrabold w-full py-3 rounded-xl text-sm border-neon-green hover-lift transition-all duration-300">
                <i class="fas fa-bookmark mr-2"></i> Add Link to Jar
            </button>
        </form>
    </div>
</div>

<div id="editLinkModal" class="fixed inset-0 hidden items-center justify-center modal-overlay">
    <div class="modal-content p-8 rounded-xl relative">
        <h3 class="text-2xl font-bubble text-neon-blue mb-6">Edit Link in <span class="text-neon-green"><?php echo $jar_title; ?></span></h3>
        <button id="closeEditLinkModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>

        <form id="editLinkForm" action="jar_view.php?jar_id=<?php echo $jar_id; ?>" method="POST">
            <input type="hidden" name="update_link" value="1">
            <input type="hidden" name="link_id" id="edit_link_id">
            
            <div class="mb-4">
                <label for="edit_link_url" class="block text-gray-300 font-semibold mb-2">URL <span class="text-icon-red">*</span></label>
                <input
                    type="url"
                    id="edit_link_url"
                    name="url"
                    required
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 modal-input"
                >
            </div>
            
            <div class="mb-4">
                <label for="edit_link_name" class="block text-gray-300 font-semibold mb-2">Link Name <span class="text-icon-red">*</span></label>
                <input
                    type="text"
                    id="edit_link_name"
                    name="name"
                    required
                    maxlength="255"
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 modal-input"
                >
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="edit_link_tags" class="block text-gray-300 font-semibold mb-2">Tags (Comma-Separated)</label>
                    <input
                        type="text"
                        id="edit_link_tags"
                        name="tags"
                        maxlength="255"
                        class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-pink focus:ring focus:ring-neon-pink focus:ring-opacity-50 text-gray-100 modal-input"
                    >
                </div>
                <div>
                    <label for="edit_link_emoji" class="block text-gray-300 font-semibold mb-2">Emoji</label>
                    <input
                        type="text"
                        id="edit_link_emoji"
                        name="emoji"
                        maxlength="5"
                        class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-orange focus:ring focus:ring-neon-orange focus:ring-opacity-50 text-gray-100 modal-input"
                    >
                </div>
            </div>

            <div class="mb-6">
                <label for="edit_link_notes" class="block text-gray-300 font-semibold mb-2">Notes (Optional)</label>
                <textarea
                    id="edit_link_notes"
                    name="notes"
                    rows="3"
                    maxlength="500"
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 resize-none modal-input"
                ></textarea>
            </div>

            <button type="submit" class="neon-btn bg-neon-blue text-black font-extrabold w-full py-3 rounded-xl text-sm border-neon-blue hover-lift transition-all duration-300">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </form>
    </div>
</div>

<div id="deleteConfirmModal" class="fixed inset-0 hidden items-center justify-center modal-overlay">
    <div class="modal-content p-8 rounded-xl relative max-w-sm">
        <h3 class="text-2xl font-bubble text-icon-red mb-4">Confirm Deletion</h3>
        <p class="text-gray-300 mb-6" id="deleteModalMessage">
            Are you sure you want to permanently delete this link? This action cannot be undone.
        </p>
        
        <div class="flex justify-between space-x-4">
            <button id="confirmDeleteLink" class="neon-btn bg-icon-red text-white font-extrabold w-1/2 py-3 rounded-xl text-sm border-icon-red hover-lift transition-all duration-300">
                Yes, Delete
            </button>
            <button id="cancelDeleteLink" class="neon-btn bg-gray-600 text-white font-extrabold w-1/2 py-3 rounded-xl text-sm border-gray-600 hover:bg-gray-700 hover:border-gray-700 hover-lift transition-all duration-300">
                Cancel
            </button>
        </div>
    </div>
</div>


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
        
        // Modal Elements (Add Link Modal)
        addModal: document.getElementById('addLinkModal'),
        openDesktop: document.getElementById('new-link-cta-header'), 
        openMobile: document.getElementById('new-link-cta-mobile'), 
        openCard: document.getElementById('add-link-cta-main'),
        closeAddBtn: document.getElementById('closeAddLinkModal'),

        // Modal Elements (Edit Link Modal) (NEW)
        editModal: document.getElementById('editLinkModal'),
        closeEditBtn: document.getElementById('closeEditLinkModal'),
        editForm: document.getElementById('editLinkForm'),

        // Modal Elements (Delete Confirmation) (NEW)
        deleteConfirmModal: document.getElementById('deleteConfirmModal'),
        confirmDeleteBtn: document.getElementById('confirmDeleteLink'),
        cancelDeleteBtn: document.getElementById('cancelDeleteLink'),
        deleteModalMessage: document.getElementById('deleteModalMessage'),
        
        // Status Message
        statusMessage: document.getElementById('status-message')
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

    // Module 4: Modal and Link Management (Consolidated)
    const LinkManager = {
        
        currentDeleteId: null,

        // --- Add Modal ---
        openAddModal: () => {
            ELEMENTS.addModal.classList.remove('hidden');
            ELEMENTS.addModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            const urlInput = document.getElementById('add_link_url');
            if(urlInput) urlInput.focus();
        },

        closeAddModal: () => {
            ELEMENTS.addModal.classList.remove('flex');
            ELEMENTS.addModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        },

        // --- Edit Modal (NEW) ---
        openEditModal: (linkData) => {
            document.getElementById('edit_link_id').value = linkData.id;
            document.getElementById('edit_link_url').value = linkData.url;
            document.getElementById('edit_link_name').value = linkData.name;
            document.getElementById('edit_link_tags').value = linkData.tags;
            document.getElementById('edit_link_notes').value = linkData.notes;
            document.getElementById('edit_link_emoji').value = linkData.emoji;

            ELEMENTS.editModal.classList.remove('hidden');
            ELEMENTS.editModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            document.getElementById('edit_link_name').focus();
        },
        
        closeEditModal: () => {
            ELEMENTS.editModal.classList.remove('flex');
            ELEMENTS.editModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        },

        // --- Delete Confirmation Modal (NEW) ---
        confirmDelete: (linkId, linkName) => {
            LinkManager.currentDeleteId = linkId;
            ELEMENTS.deleteModalMessage.innerHTML = `Are you sure you want to permanently delete the link: <strong>${linkName}</strong>? This action cannot be undone.`;
            
            ELEMENTS.deleteConfirmModal.classList.remove('hidden');
            ELEMENTS.deleteConfirmModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        },
        
        cancelDelete: () => {
            LinkManager.currentDeleteId = null;
            ELEMENTS.deleteConfirmModal.classList.remove('flex');
            ELEMENTS.deleteConfirmModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        },
        
        executeDelete: () => {
            if (LinkManager.currentDeleteId) {
                // Redirect to delete action URL
                const jarId = new URLSearchParams(window.location.search).get('jar_id');
                window.location.href = `jar_view.php?jar_id=${jarId}&delete_link_id=${LinkManager.currentDeleteId}`;
            }
        },

        initialize: () => {
            // Add Modal CTAs
            if (ELEMENTS.openDesktop) ELEMENTS.openDesktop.addEventListener('click', LinkManager.openAddModal);
            if (ELEMENTS.openMobile) ELEMENTS.openMobile.addEventListener('click', LinkManager.openAddModal);
            if (ELEMENTS.openCard) ELEMENTS.openCard.addEventListener('click', LinkManager.openAddModal);
            if (ELEMENTS.closeAddBtn) ELEMENTS.closeAddBtn.addEventListener('click', LinkManager.closeAddModal);

            // Edit Modal CTAs
            if (ELEMENTS.closeEditBtn) ELEMENTS.closeEditBtn.addEventListener('click', LinkManager.closeEditModal);

            document.querySelectorAll('.edit-link-btn').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const card = button.closest('.link-card');
                    const linkData = {
                        id: card.dataset.linkId,
                        name: card.dataset.linkName,
                        url: card.dataset.linkUrl,
                        emoji: card.dataset.linkEmoji,
                        notes: card.dataset.linkNotes,
                        tags: card.dataset.linkTags
                    };
                    LinkManager.openEditModal(linkData);
                });
            });

            // Delete Confirmation CTAs
            if (ELEMENTS.confirmDeleteBtn) ELEMENTS.confirmDeleteBtn.addEventListener('click', LinkManager.executeDelete);
            if (ELEMENTS.cancelDeleteBtn) ELEMENTS.cancelDeleteBtn.addEventListener('click', LinkManager.cancelDelete);
            
            // Close Modals on Overlay Click
            [ELEMENTS.addModal, ELEMENTS.editModal, ELEMENTS.deleteConfirmModal].forEach(modal => {
                if (modal) {
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) {
                            if (modal.id === 'addLinkModal') LinkManager.closeAddModal();
                            if (modal.id === 'editLinkModal') LinkManager.closeEditModal();
                            if (modal.id === 'deleteConfirmModal') LinkManager.cancelDelete();
                        }
                    });
                }
            });

            // Close Modals on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (ELEMENTS.addModal && ELEMENTS.addModal.classList.contains('flex')) LinkManager.closeAddModal();
                    if (ELEMENTS.editModal && ELEMENTS.editModal.classList.contains('flex')) LinkManager.closeEditModal();
                    if (ELEMENTS.deleteConfirmModal && ELEMENTS.deleteConfirmModal.classList.contains('flex')) LinkManager.cancelDelete();
                }
            });
            
            // Hide status message after 5 seconds if visible
            if(ELEMENTS.statusMessage && ELEMENTS.statusMessage.classList.contains('show')) {
                setTimeout(() => {
                    ELEMENTS.statusMessage.classList.remove('show');
                    ELEMENTS.statusMessage.classList.add('hidden');
                }, 5000);
            }
        }
    };

    // Module 5: Layout Management 
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
        LinkManager.initialize();
        LayoutManager.initialize();
    });
</script>
</body>
</html>