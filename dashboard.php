<?php
// ========================================================
// dashboard.php — Unified User Dashboard for urlJAR (PRODUCTION READY)
// ========================================================

session_start();
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
$greeting = "Welcome back, " . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); 

// Get initials for avatar
$parts = explode(' ', $user_name);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1) {
    $last_initial = strtoupper(substr(end($parts), 0, 1));
    if ($last_initial !== substr($initials, 0, 1) || count($parts) > 2) {
        $initials .= $last_initial;
    }
} elseif (strlen($initials) < 2 && strlen($user_name) > 1) {
     $initials = strtoupper(substr($user_name, 0, 2));
}
$initials = substr($initials, 0, 2);

// --------------------------------------------------------
// 2. DATABASE CONFIGURATION & CONNECTION
// --------------------------------------------------------
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '', 
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

// Initialize status message variables
$status_message = '';
$status_type = ''; // success, error, or warning

// --------------------------------------------------------
// 3. HANDLE NEW JAR CREATION
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jar_name'])) {
    $jar_name = trim($_POST['jar_name']);
    $jar_description = isset($_POST['description']) ? trim($_POST['description']) : NULL;
    $is_starred = isset($_POST['is_starred']) ? 1 : 0; 
    
    $jar_description = (empty($jar_description) && $jar_description !== '0') ? NULL : $jar_description;

    if (!empty($jar_name)) {
        $sql = "INSERT INTO jars (user_id, name, description, is_starred) VALUES (?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("issi", $param_user_id, $param_name, $param_description, $param_is_starred);
            
            $param_user_id = $user_id; 
            $param_name = $jar_name;
            $param_description = $jar_description;
            $param_is_starred = $is_starred;

            if ($stmt->execute()) {
                header("Location: dashboard.php?status=JarCreated");
                exit;
            } else {
                error_log("JAR Insertion Error: " . $stmt->error);
                $status_message = "Error creating jar.";
                $status_type = 'error';
            }
            $stmt->close();
        } else {
            error_log("SQL Prepare Error (Jar Creation): " . $mysqli->error);
            $status_message = "A database error occurred.";
            $status_type = 'error';
        }
    }
}

// --------------------------------------------------------
// 4. HANDLE AJAX ENDPOINTS (FAVORITE & DELETE)
// --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $jar_id_param = isset($_POST['jar_id']) ? (int)$_POST['jar_id'] : 0;
    
    if ($jar_id_param === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid jar ID.']);
        $mysqli->close();
        exit;
    }

    if ($action === 'toggle_favorite') {
        $is_starred_toggle = isset($_POST['is_starred']) ? (int)$_POST['is_starred'] : 0;
        
        $sql = "UPDATE jars SET is_starred = ? WHERE jar_id = ? AND user_id = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("iii", $is_starred_toggle, $param_jar_id, $param_user_id);
            $param_jar_id = $jar_id_param;
            $param_user_id = $user_id;

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'is_starred' => $is_starred_toggle, 'message' => 'Favorite status updated.']);
            } else {
                error_log("Favorite Toggle Error: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Database update failed.']);
            }
            $stmt->close();
        } else {
            error_log("Favorite Toggle Prepare Error: " . $mysqli->error);
            echo json_encode(['success' => false, 'message' => 'Database error during preparation.']);
        }
    } 
    
    elseif ($action === 'delete_jar') {
        // Start a transaction for safe deletion (deleting associated links first)
        $mysqli->begin_transaction();
        $jar_name_for_log = '';

        try {
            // 1. Get jar name for confirmation message
            $sql_name = "SELECT name FROM jars WHERE jar_id = ? AND user_id = ?";
            if ($stmt_name = $mysqli->prepare($sql_name)) {
                $stmt_name->bind_param("ii", $jar_id_param, $user_id);
                $stmt_name->execute();
                $result_name = $stmt_name->get_result();
                if ($row = $result_name->fetch_assoc()) {
                    $jar_name_for_log = htmlspecialchars($row['name']);
                }
                $stmt_name->close();
            }

            // 2. Delete all links associated with this jar
            $sql_links = "DELETE FROM links WHERE jar_id = ? AND user_id = ?";
            if ($stmt_links = $mysqli->prepare($sql_links)) {
                $stmt_links->bind_param("ii", $jar_id_param, $user_id);
                $stmt_links->execute();
                $stmt_links->close();
            }

            // 3. Delete the jar itself
            $sql_jar = "DELETE FROM jars WHERE jar_id = ? AND user_id = ?";
            if ($stmt_jar = $mysqli->prepare($sql_jar)) {
                $stmt_jar->bind_param("ii", $jar_id_param, $user_id);
                $stmt_jar->execute();
                $deleted_rows = $stmt_jar->affected_rows;
                $stmt_jar->close();
            }

            if ($deleted_rows > 0) {
                $mysqli->commit();
                echo json_encode(['success' => true, 'message' => "Jar '{$jar_name_for_log}' and its links deleted successfully."]);
            } else {
                $mysqli->rollback();
                echo json_encode(['success' => false, 'message' => 'Jar not found or unauthorized.']);
            }

        } catch (Exception $e) {
            $mysqli->rollback();
            error_log("Jar Deletion Transaction Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'A critical database error occurred during deletion.']);
        }
    }
    
    $mysqli->close();
    exit;
}

// --------------------------------------------------------
// 5. FETCH USER'S JARS
// --------------------------------------------------------
$jars = [];
$total_jars = 0;
$favorites = 0; 

$sql = "SELECT jar_id, name, description, is_starred, link_count, created_at FROM jars WHERE user_id = ? ORDER BY created_at DESC";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $param_user_id);
    $param_user_id = $user_id;

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['link_count'] = (int)$row['link_count']; 
            $jars[] = $row;
            if ($row['is_starred'] == 1) {
                $favorites++;
            }
        }
        $total_jars = count($jars);
    } else {
        error_log("JAR Fetch Error: " . $stmt->error);
    }
    $stmt->close();
} else {
    error_log("SQL Prepare Error (Jar Fetch): " . $mysqli->error);
}

// --------------------------------------------------------
// 6. FETCH TOTAL SAVED LINKS
// --------------------------------------------------------
$saved_links = 0;

$sql_links = "SELECT COALESCE(SUM(link_count), 0) AS total_links FROM jars WHERE user_id = ?";

if ($stmt_links = $mysqli->prepare($sql_links)) {
    $stmt_links->bind_param("i", $user_id);

    if ($stmt_links->execute()) {
        $result_links = $stmt_links->get_result();
        if ($row_links = $result_links->fetch_assoc()) {
            $saved_links = (int)$row_links['total_links']; 
        }
    } else {
        error_log("Total Links Fetch Error: " . $stmt_links->error);
    }
    $stmt_links->close();
} else {
     error_log("SQL Prepare Error (Total Links): " . $mysqli->error);
}

// --------------------------------------------------------
// 7. HANDLE STATUS MESSAGES
// --------------------------------------------------------
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'JarCreated') {
        $status_message = "New Jar created successfully!";
        $status_type = 'success';
    } 
}

// --------------------------------------------------------
// 8. CONNECTION CLOSURE & TEMPLATE RENDER
// --------------------------------------------------------
$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Unified Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <script>
        // Tailwind Config 
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
/* Base styles */
body {
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

/* --- THEME STYLES --- */
body.light {
  background-color: #6db76eff; 
  color: #5a063fff; 
  
  background-image: 
    repeating-linear-gradient(
      45deg,
      #ffffff 0px,
      #ffffff 2px,
      #dfe3e8 2px,
      #dfe3e8 4px
    ),
    repeating-linear-gradient(
      -45deg,
      #ffffff 0px,
      #ffffff 2px,
      #dfe3e8 2px,
      #dfe3e8 4px
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

/* D2.php/Pinterest Grid */
.pinterest-grid {
    column-count: 1; column-gap: 1.5rem;
}

@media (min-width: 768px) { .pinterest-grid { column-count: 2; } }
@media (min-width: 1024px) { .pinterest-grid { column-count: 3; } }
@media (min-width: 1280px) { .pinterest-grid { column-count: 4; } }

.grid-item { break-inside: avoid; margin-bottom: 1.5rem; }

/* Theming/Sidebar Links */
body.dark .text-secondary { color: #b0b0b0; }
body.light .text-secondary { color: #64748b; }

body.dark .sidebar-link.active { 
    background-color: rgba(0, 255, 119, 0.1); 
    color: #00FF77; 
}

body.light .sidebar-link.active { 
    background-color: rgba(0, 255, 119, 0.15); 
    color: #00a55a; 
}

/* --- Sidebar & Layout Overrides --- */
#sidebar { 
    width: 250px; 
    transition: transform 0.3s ease-in-out; 
    top: 0; 
    height: 100vh; 
    z-index: 50; 
}

body.dark #sidebar { 
    background: #08080A; 
    border-right: 1px solid #1a1a1e; 
}

body.light #sidebar { 
    background: #F8FAFC; 
    border-right: 1px solid #e2e8f0; 
}

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
    .content-area { 
        padding-left: 0; 
        padding-right: 0; 
        padding-top: 5.5rem; 
    }
}

.sidebar-backdrop {
    position: fixed; 
    top: 0; 
    left: 0; 
    right: 0; 
    bottom: 0; 
    z-index: 49; 
    opacity: 0; 
    visibility: hidden;
    transition: opacity 0.3s ease-in-out, visibility 0.3s;
}

body.sidebar-open .sidebar-backdrop { 
    opacity: 1; 
    visibility: visible; 
}

body.dark .sidebar-backdrop { 
    background-color: rgba(0, 0, 0, 0.75); 
}

body.light .sidebar-backdrop { 
    background-color: rgba(0, 0, 0, 0.5); 
}

.fixed-header {
    background: transparent; 
    backdrop-filter: blur(10px); 
    -webkit-backdrop-filter: blur(10px);
    border-color: rgba(255, 255, 255, 0.05);
}

body.light .fixed-header {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(0, 0, 0, 0.1);
}

/* 🌟 FOOTER THEME OVERRIDES 🌟 */
footer { 
    transition: background-color 0.3s ease, border-color 0.3s ease; 
    border-color: #1a1a1e; 
}

body.light footer {
    background-color: #F8FAFC !important; 
    border-top-color: #e2e8f0 !important; 
    color: #1a1a1a; 
}

body.light footer .text-gray-400,
body.light footer .text-gray-500 { 
    color: #64748b; 
}

body.light footer .neon-border { 
    border: 1px solid currentColor; 
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
}

body.light footer a.hover\:text-neon-pink:hover,
body.light footer a.hover\:text-neon-blue:hover,
body.light footer a.hover\:text-neon-green:hover,
body.light footer a.hover\:text-neon-purple:hover { 
    filter: brightness(0.85); 
}

.fab:hover { 
    text-shadow: 0 0 5px currentColor; 
}

/* MODAL STYLES */
.modal-overlay {
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 60; 
}

.modal-content {
    max-width: 400px;
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
            background: linear-gradient(45deg, #00bfffff, #ff2de7ff);
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

.link-action-bar {
    border-top: 1px solid #1a1a1e;
    padding-top: 1rem;
    margin-top: auto;
}

body.light .link-action-bar {
    border-top: 1px solid #e2e8f0;
}

/* Star button styles */
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
  color: #b0b0b0; 
}

body.light .star-btn:not(.starred) {
  color: #64748b;
}

/* Favorite modal styles */
.favorite-modal-content {
  max-width: 350px;
  background: linear-gradient(135deg, #1a1a2e, #16213e);
  border: 3px solid #FFD700;
  box-shadow: 0 0 25px rgba(255, 215, 0, 0.7);
}

body.light .favorite-modal-content {
  background: #FFFFFF;
  border-color: #FF8800;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.favorite-modal-title {
  background: linear-gradient(90deg, #FFD700, #FFA500);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  font-weight: 800;
}

.favorite-modal-buttons {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  margin-top: 20px;
}

.favorite-modal-btn {
  flex: 1;
  padding: 10px 15px;
  border-radius: 8px;
  font-weight: 700;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.favorite-modal-btn.yes {
  background: linear-gradient(135deg, #00FF77, #00CCFF);
  color: #000;
}

.favorite-modal-btn.no {
  background: linear-gradient(135deg, #FF3F7F, #FF8800);
  color: #fff;
}

.favorite-modal-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

/* Delete Confirmation Modal Styles */
#deleteConfirmModal .modal-content {
    max-width: 350px;
    border-color: #DD0303; 
    box-shadow: 0 0 20px rgba(221, 3, 3, 0.7);
}

body.light #deleteConfirmModal .modal-content {
    border-color: #DD0303; 
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .jar-card {
    max-width: 400px;
    height: auto;
    min-height: 180px;
  }
  
  .favorite-modal-content {
    max-width: 90%;
  }
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

/* No Results Message */
.no-results {
    transition: opacity 0.5s ease;
}

/* Quick access buttons */
.quick-access-btn {
    min-height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transition: all 0.3s ease;
}

.quick-access-btn:hover {
    transform: translateY(-5px);
}

/* Stats cards */
.stats-card {
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

    </style>
</head>
<body class="dark min-h-screen">
    
<header id="main-header" class="fixed w-full top-0 right-0 z-40 py-3 px-4 transition-all duration-300 fixed-header border-b">
    <div class="flex justify-between items-center h-12">
        
        <button id="mobile-sidebar-toggle" aria-label="Toggle Navigation Menu" class="lg:hidden text-neon-green text-2xl p-2 rounded-lg hover:bg-white/10 dark:hover:bg-black/10 transition-colors focus:outline-none mr-4">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="flex-grow max-w-2xl relative lg:mr-8">
            <input type="search" id="jar-search-input" placeholder="Search Jars, Links, or Tags..." aria-label="Search all content"
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
            
            <button id="new-jar-cta-header" class="neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta" title="Create a New Collection">
                <i class="fas fa-plus mr-2"></i> NEW JAR
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
            
            <button id="new-jar-cta-mobile" class="w-full neon-btn bg-neon-green text-black font-extrabold py-2 px-5 rounded-xl text-sm border-neon-green hover-lift transition-all duration-500 pulse-cta mb-4" title="Create a New Collection">
                <i class="fas fa-plus mr-2"></i> NEW JAR
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
                <?php echo htmlspecialchars($greeting); ?>!
            </span>
        </h1>
        <p class="text-xl text-secondary font-light">
ORGANIZE YOUR BOOKMARKS IN STYLE        </p>
    </header>

    <?php if (!empty($status_message)): ?>
        <div id="status-message" class="message-box p-4 rounded-lg mb-6 mx-4 lg:mx-12 show 
            <?php echo $status_type === 'success' ? 'bg-neon-green/20 border border-neon-green text-neon-green' : 
                          ($status_type === 'warning' ? 'bg-neon-orange/20 border border-neon-orange text-neon-orange' : 
                               'bg-icon-red/20 border border-icon-red text-icon-red'); ?>" 
            role="alert">
            <i id="status-icon" class="fas <?php echo $status_type === 'success' ? 'fa-check-circle' : ($status_type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle'); ?> mr-2"></i> 
            <span id="status-message-text"><?php echo htmlspecialchars($status_message); ?></span>
        </div>
    <?php endif; ?>
   
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16 px-4 lg:px-12">
        
        <article class="neon-card p-6 border-neon-blue neon-border-glow text-neon-blue hover-lift border-2 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble" id="total-jars-count"><?php echo $total_jars; ?></h3>
                    <p class="text-secondary text-sm">Active Jars</p>
                </div>
                <i class="fas fa-layer-group text-3xl opacity-80"></i>
            </div>
        </article>

        <article class="neon-card p-6 border-neon-pink neon-border-glow text-neon-pink hover-lift border-2 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble" id="saved-links-count"><?php echo $saved_links; ?></h3>
                    <p class="text-secondary text-sm">Saved Links</p>
                </div>
                <i class="fas fa-link text-3xl opacity-80"></i>
            </div>
        </article>
        
        <article class="neon-card p-6 border-neon-orange neon-border-glow text-neon-orange hover-lift border-2 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-extrabold font-bubble" id="favorites-count"><?php echo $favorites; ?></h3>
                    <p class="text-secondary text-sm">Favorites</p>
                </div>
                <i class="fas fa-star text-3xl opacity-80"></i>
            </div>
        </article>

        <button id="cta-card-button" class="neon-card p-6 border-neon-green neon-border-glow bg-neon-green/10 flex flex-col justify-center transition-colors cursor-pointer hover-lift text-neon-green border-2 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold font-bubble">CREATE NEW JAR</h3>
                    <p class="text-sm text-secondary">Start a new collection now</p>
                </div>
                <i class="fas fa-plus-circle text-3xl opacity-80"></i>
            </div>
        </button>
    </div>
    
    <h2 class="text-2xl font-bold mb-6 font-bubble text-gray-700 dark:text-gray-300 border-b border-gray-300 dark:border-gray-800 pb-2 px-4 lg:px-12">Quick Access</h2>
    
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-4 mb-16 px-4 lg:px-12 mx-auto justify-center">
     
        <button class="neon-card p-4 flex flex-col items-center justify-center text-center cursor-pointer hover-lift border-neon-pink text-neon-pink quick-access-btn" onclick="alert('Share Activated')" aria-label="Quick Access Share">
            <div class="w-10 h-10 bg-neon-pink/20 rounded-lg flex items-center justify-center mb-2">
                <i class="fas fa-share-alt text-xl"></i>
            </div>
            <span class="font-medium text-sm">Share</span>
        </button>
        <button class="neon-card p-4 flex flex-col items-center justify-center text-center cursor-pointer hover-lift border-neon-green text-neon-green quick-access-btn" onclick="alert('QR Code Generated')" aria-label="Quick Access QR Code">
            <div class="w-10 h-10 bg-neon-green/20 rounded-lg flex items-center justify-center mb-2">
                <i class="fas fa-qrcode text-xl"></i>
            </div>
            <span class="font-medium text-sm">QR Code</span>
        </button>
        <button class="neon-card p-4 flex flex-col items-center justify-center text-center cursor-pointer hover-lift border-neon-orange text-neon-orange quick-access-btn" onclick="alert('Opening Tag Manager')" aria-label="Quick Access Tags">
            <div class="w-10 h-10 bg-neon-orange/20 rounded-lg flex items-center justify-center mb-2">
                <i class="fas fa-tags text-xl"></i>
            </div>
            <span class="font-medium text-sm">Tags</span>
        </button>
        <button class="neon-card p-4 flex flex-col items-center justify-center text-center cursor-pointer hover-lift border-neon-purple text-neon-purple quick-access-btn" onclick="alert('Opening Analytics')" aria-label="Quick Access Analytics">
            <div class="w-10 h-10 bg-neon-purple/20 rounded-lg flex items-center justify-center mb-2">
                <i class="fas fa-chart-bar text-xl"></i>
            </div>
            <span class="font-medium text-sm">Analytics</span>
        </button>
        <button class="neon-card p-4 flex flex-col items-center justify-center text-center cursor-pointer hover-lift border-neon-teal text-neon-teal quick-access-btn" onclick="alert('Opening Settings')" aria-label="Quick Access Settings">
            <div class="w-10 h-10 bg-neon-teal/20 rounded-lg flex items-center justify-center mb-2">
                <i class="fas fa-cog text-xl"></i>
            </div>
            <span class="font-medium text-sm">Settings</span>
        </button>
    </div>

<h3 class="text-2xl font-extrabold mb-4 border-b border-gray-700 pb-2 text-black-300 px-4 lg:px-8">Your Jars</h3>

        <div id="jars-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-4 lg:px-8">
            <?php if ($total_jars > 0): ?>
                <?php foreach ($jars as $jar): ?>
                    <?php
                    $jar_name = htmlspecialchars($jar['name'] ?? 'Untitled Jar');
                    $raw_jar_name = $jar['name'] ?? 'Untitled Jar'; // Un-escaped for data attribute
                    $jar_description = htmlspecialchars($jar['description'] ?? '');
                    $raw_jar_description = $jar['description'] ?? ''; // Un-escaped for data attribute
                    $jar_created_at = date('M j, Y', strtotime($jar['created_at'] ?? 'now'));
                    $jar_starred = (int)($jar['is_starred'] ?? 0); 
                    $jar_id = $jar['jar_id'] ?? 0;
                    ?>
                    
                    <article class="jar-card hover-lift fade-in" 
                        data-jar-id="<?php echo $jar_id; ?>"
                        data-search-name="<?php echo htmlspecialchars(strtolower($raw_jar_name)); ?>"
                        data-search-description="<?php echo htmlspecialchars(strtolower($raw_jar_description)); ?>"
                    >
                        <div class="jar-card-header">
                            <h3 class="jar-card-title"><?php echo $jar_name; ?></h3>
                            <button class="star-btn <?php echo $jar_starred ? 'starred' : ''; ?>" 
                                    data-jar-id="<?php echo $jar_id; ?>" 
                                    data-is-starred="<?php echo $jar_starred; ?>"
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
                                    <button class="delete-jar-btn text-sm text-red-400 hover:text-icon-red transition-colors"
                                            data-jar-id="<?php echo $jar_id; ?>"
                                            data-jar-name="<?php echo $jar_name; ?>">
                                        <i class="fas fa-trash-alt text-xs mr-1"></i> Delete
                                    </button>
                                    
                                    <a class="text-sm text-neon-blue hover:text-neon-pink transition-colors font-bold"
                                            href="jar_view.php?jar_id=<?php echo $jar_id; ?>&name=<?php echo urlencode($jar_name); ?>">
                                        View Links <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="no-jars-message" class="col-span-full text-center py-10 border border-dashed border-gray-600 rounded-lg bg-gray-900">
                    <i class="far fa-lightbulb text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-400">You haven't created any jars yet. Click "Create Jar" to get started!</p>
                </div>
            <?php endif; ?>
            
            <div id="no-search-results" class="col-span-full text-center py-10 border border-dashed border-gray-600 rounded-lg bg-gray-900 hidden no-results">
                <i class="fas fa-frown-open text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-400">No jars found matching your search term.</p>
            </div>
        </div>

<div class="text-center mt-8 px-4 lg:px-12">
        <a href="jars.php" class="text-lg font-bold text-neon-blue hover:text-neon-pink transition-colors underline">
            View all <?php echo $total_jars; ?> jars <i class="fas fa-angle-double-right ml-1"></i>
        </a>
    </div>
</main>

<div id="createJarModal" class="fixed inset-0 hidden items-center justify-center modal-overlay">
    <div class="modal-content p-8 rounded-xl relative">
        <h3 class="text-2xl font-bubble text-neon-green mb-6">Create New Jar</h3>
        <button id="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>

        <form action="dashboard.php" method="POST">
            <div class="mb-6">
                <label for="jar_name" class="block text-gray-300 font-semibold mb-2">Jar Name</label>
                <input
                    type="text"
                    id="jar_name"
                    name="jar_name"
                    required
                    maxlength="255"
                    placeholder="e.g., Development Resources"
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100"
                >
            </div>

            <div class="mb-6">
                <label for="jar_description" class="block text-gray-300 font-semibold mb-2">Description (Optional)</label>
                <textarea
                    id="jar_description"
                    name="description"
                    rows="3"
                    maxlength="500"
                    placeholder="Briefly describe what this jar contains..."
                    class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-neon-green focus:ring focus:ring-neon-green focus:ring-opacity-50 text-gray-100 resize-none"
                ></textarea>
            </div>
            
            <div class="mb-8 flex items-center">
                <input 
                    type="checkbox" 
                    id="is_starred" 
                    name="is_starred" 
                    value="1" 
                    class="h-5 w-5 rounded border-gray-700 bg-gray-900 text-neon-orange focus:ring-neon-orange"
                >
                <label for="is_starred" class="ml-3 text-gray-300 font-semibold flex items-center">
                    <i class="fas fa-star mr-2 text-neon-orange"></i> Star this Jar (Quick Access)
                </label>
            </div>

            <button type="submit" class="neon-btn bg-neon-green text-black font-extrabold w-full py-3 rounded-xl text-sm border-neon-green hover-lift transition-all duration-300">
                <i class="fas fa-save mr-2"></i> Save Jar
            </button>
        </form>
    </div>
</div>

<div id="favoriteModal" class="fixed inset-0 hidden items-center justify-center modal-overlay">
    <div class="favorite-modal-content p-8 rounded-xl relative bounce-in">
        <button id="closeFavoriteModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        
        <div class="text-center">
            <i class="fas fa-star text-5xl mb-4 text-yellow-400"></i>
            <h3 id="favoriteModalTitle" class="favorite-modal-title text-2xl font-bubble mb-4">Add to Favorites?</h3>
            <p id="favoriteModalMessage" class="text-gray-300 mb-6">Would you like to add this jar to your favorites for quick access?</p>
            
            <div class="favorite-modal-buttons">
                <button id="confirmFavorite" class="favorite-modal-btn yes">Yes</button>
                <button id="cancelFavorite" class="favorite-modal-btn no">No</button>
            </div>
        </div>
    </div>
</div>

<div id="deleteJarModal" class="fixed inset-0 hidden items-center justify-center modal-overlay">
    <div class="modal-content p-8 rounded-xl relative max-w-sm" id="deleteConfirmModal">
        <h3 class="text-2xl font-bubble text-icon-red mb-4">Confirm Jar Deletion</h3>
        <p class="text-gray-300 mb-6" id="deleteJarModalMessage">
            Are you sure you want to permanently delete the jar:<?php echo $user_name ?> All associated links will also be deleted. This action cannot be undone.
        </p>
        
        <div class="flex justify-between space-x-4">
            <button id="confirmDeleteJar" class="neon-btn bg-icon-red text-white font-extrabold w-1/2 py-3 rounded-xl text-sm border-icon-red hover-lift transition-all duration-300">
                Yes, Delete
            </button>
            <button id="cancelDeleteJar" class="neon-btn bg-gray-600 text-white font-extrabold w-1/2 py-3 rounded-xl text-sm border-gray-600 hover:bg-gray-700 hover:border-gray-700 hover-lift transition-all duration-300">
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
        
        // Modal Elements (Create Jar)
        modal: document.getElementById('createJarModal'),
        openDesktop: document.getElementById('new-jar-cta-header'), 
        openMobile: document.getElementById('new-jar-cta-mobile'), 
        openCard: document.getElementById('cta-card-button'),
        closeBtn: document.getElementById('closeModal'),
        
        // Favorite Modal Elements
        favoriteModal: document.getElementById('favoriteModal'),
        closeFavoriteModal: document.getElementById('closeFavoriteModal'),
        confirmFavorite: document.getElementById('confirmFavorite'),
        cancelFavorite: document.getElementById('cancelFavorite'),
        favoriteModalTitle: document.getElementById('favoriteModalTitle'),
        favoriteModalMessage: document.getElementById('favoriteModalMessage'),

        // Delete Jar Modal Elements
        deleteJarModal: document.getElementById('deleteJarModal'),
        deleteJarModalMessage: document.getElementById('deleteJarModalMessage'),
        confirmDeleteJar: document.getElementById('confirmDeleteJar'),
        cancelDeleteJar: document.getElementById('cancelDeleteJar'),

        // Stats elements
        favoritesCountElement: document.getElementById('favorites-count'),
        totalJarsCountElement: document.getElementById('total-jars-count'),
        savedLinksCountElement: document.getElementById('saved-links-count'),

        // Status Message
        statusMessage: document.getElementById('status-message'),
        statusMessageText: document.getElementById('status-message-text'),
        statusIcon: document.getElementById('status-icon'),

        // Search elements
        jarSearchInput: document.getElementById('jar-search-input'),
        jarsGrid: document.getElementById('jars-grid'),
        noSearchResults: document.getElementById('no-search-results'),
        noJarsMessage: document.getElementById('no-jars-message'),
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

    // Module 4: Modal Management (Create Jar)
    const ModalManager = {
        openModal: () => {
            ELEMENTS.modal.classList.remove('hidden');
            ELEMENTS.modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            document.getElementById('jar_name').focus();
        },

        closeModal: () => {
            ELEMENTS.modal.classList.remove('flex');
            ELEMENTS.modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        },

        initialize: () => {
            if (ELEMENTS.openDesktop) ELEMENTS.openDesktop.addEventListener('click', ModalManager.openModal);
            if (ELEMENTS.openMobile) ELEMENTS.openMobile.addEventListener('click', ModalManager.openModal);
            if (ELEMENTS.openCard) ELEMENTS.openCard.addEventListener('click', ModalManager.openModal);
            if (ELEMENTS.closeBtn) ELEMENTS.closeBtn.addEventListener('click', ModalManager.closeModal);

            if (ELEMENTS.modal) {
                ELEMENTS.modal.addEventListener('click', (e) => {
                    if (e.target === ELEMENTS.modal) ModalManager.closeModal();
                });
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

    // Module 6: Jar Action Management (Favorite and Delete)
    const JarActionManager = {
        currentJarId: null,
        currentJarName: null,
        currentIsStarred: false,

        // --- Helper for in-page messages ---
        showToast: (message, type = 'success') => {
            const classes = {
                success: 'bg-neon-green/20 border border-neon-green text-neon-green',
                error: 'bg-icon-red/20 border border-icon-red text-icon-red',
            };
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
            };
            
            if (!ELEMENTS.statusMessage || !ELEMENTS.statusMessageText || !ELEMENTS.statusIcon) {
                console.error("Status message elements not found.");
                return;
            }

            // Reset and set new styles/content
            ELEMENTS.statusMessage.className = 'message-box p-4 rounded-lg mb-6 mx-4 lg:mx-12'; // Base classes
            ELEMENTS.statusMessage.classList.add(classes[type]);
            ELEMENTS.statusMessageText.textContent = message;
            ELEMENTS.statusIcon.className = `fas ${icons[type]} mr-2`;

            // Show the message
            ELEMENTS.statusMessage.classList.remove('hidden');
            ELEMENTS.statusMessage.classList.add('show');

            // Hide after 5 seconds
            setTimeout(() => {
                ELEMENTS.statusMessage.classList.remove('show');
                // Wait for the transition to finish before hiding from layout
                setTimeout(() => ELEMENTS.statusMessage.classList.add('hidden'), 500); 
            }, 5000);
        },

        // --- Favorite Logic ---
        openFavoriteModal: (jarId, isStarred) => {
            JarActionManager.currentJarId = jarId;
            JarActionManager.currentIsStarred = isStarred;
            
            if (isStarred) {
                ELEMENTS.favoriteModalTitle.textContent = 'Remove from Favorites?';
                ELEMENTS.favoriteModalMessage.textContent = 'Are you sure you want to remove this jar from your favorites?';
            } else {
                ELEMENTS.favoriteModalTitle.textContent = 'Add to Favorites?';
                ELEMENTS.favoriteModalMessage.textContent = 'Would you like to add this jar to your favorites for quick access?';
            }
            
            ELEMENTS.favoriteModal.classList.remove('hidden');
            ELEMENTS.favoriteModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        },

        closeFavoriteModal: () => {
            ELEMENTS.favoriteModal.classList.remove('flex');
            ELEMENTS.favoriteModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        },

        toggleFavorite: async () => {
            if (!JarActionManager.currentJarId) return;
            
            const newIsStarred = JarActionManager.currentIsStarred ? 0 : 1;
            JarActionManager.closeFavoriteModal();

            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'toggle_favorite',
                        jar_id: JarActionManager.currentJarId,
                        is_starred: newIsStarred
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const starButton = document.querySelector(`.star-btn[data-jar-id="${JarActionManager.currentJarId}"]`);
                    const starIcon = starButton.querySelector('i');
                    let currentCount = parseInt(ELEMENTS.favoritesCountElement.textContent);

                    if (newIsStarred === 1) {
                        starButton.classList.add('starred');
                        starIcon.classList.remove('far'); starIcon.classList.add('fas');
                        starButton.setAttribute('data-is-starred', '1');
                        ELEMENTS.favoritesCountElement.textContent = currentCount + 1;
                    } else {
                        starButton.classList.remove('starred');
                        starIcon.classList.remove('fas'); starIcon.classList.add('far');
                        starButton.setAttribute('data-is-starred', '0');
                        ELEMENTS.favoritesCountElement.textContent = currentCount - 1;
                    }
                } else {
                    JarActionManager.showToast('Error: Could not save favorite status. Check console for details.', 'error');
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                JarActionManager.showToast('A network error occurred. Please try again.', 'error');
            }
        },

        // --- Delete Logic ---
        openDeleteModal: (jarId, jarName) => {
            JarActionManager.currentJarId = jarId;
            JarActionManager.currentJarName = jarName;

            const linkCountElement = document.querySelector(`.jar-card[data-jar-id="${jarId}"] .link-action-bar span`);
            const linksCountText = linkCountElement ? linkCountElement.textContent.trim() : '0 Links';

            ELEMENTS.deleteJarModalMessage.innerHTML = `Are you sure you want to permanently delete the jar: <strong>${jarName}</strong>? All ${linksCountText} will also be deleted. This action cannot be undone.`;
            
            ELEMENTS.deleteJarModal.classList.remove('hidden');
            ELEMENTS.deleteJarModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        },

        closeDeleteModal: () => {
            ELEMENTS.deleteJarModal.classList.remove('flex');
            ELEMENTS.deleteJarModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            JarActionManager.currentJarId = null;
            JarActionManager.currentJarName = null;
        },
        
        executeDelete: async () => {
            if (!JarActionManager.currentJarId) return;

            const jarIdToDelete = JarActionManager.currentJarId;
            const jarNameForLog = JarActionManager.currentJarName;
            JarActionManager.closeDeleteModal(); // Close modal immediately

            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'delete_jar',
                        jar_id: jarIdToDelete
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // 1. Remove the jar card from UI & get stats for update
                    const deletedCard = document.querySelector(`.jar-card[data-jar-id="${jarIdToDelete}"]`);
                    if (deletedCard) {
                        const linksCountText = deletedCard.querySelector('.link-action-bar span').textContent.match(/(\d+)/);
                        const linksDeleted = linksCountText ? parseInt(linksCountText[1]) : 0;
                        const wasStarred = deletedCard.querySelector('.star-btn').getAttribute('data-is-starred') === '1';

                        // 2. Update stats cards
                        ELEMENTS.totalJarsCountElement.textContent = parseInt(ELEMENTS.totalJarsCountElement.textContent) - 1;
                        ELEMENTS.savedLinksCountElement.textContent = parseInt(ELEMENTS.savedLinksCountElement.textContent) - linksDeleted;
                        if (wasStarred) {
                            ELEMENTS.favoritesCountElement.textContent = parseInt(ELEMENTS.favoritesCountElement.textContent) - 1;
                        }

                        // 3. Smoothly remove the card
                        deletedCard.style.opacity = '0';
                        deletedCard.style.transform = 'scale(0.9)';
                        setTimeout(() => deletedCard.remove(), 300); 
                    }
                    
                    // 4. Display success message
                    JarActionManager.showToast(`Jar '${jarNameForLog}' deleted successfully!`, 'success');
                    
                } else {
                    // Display error message
                    JarActionManager.showToast(`Deletion Failed: ${data.message}`, 'error');
                }
            } catch (error) {
                console.error('AJAX Delete Error:', error);
                // Display network error message
                JarActionManager.showToast('A network error occurred during deletion. Please try again.', 'error');
            }
        }
    };
    
    // Module 7: Search Filtering
    const SearchManager = {
        filterJars: () => {
            if (!ELEMENTS.jarSearchInput || !ELEMENTS.jarsGrid) return;

            const searchTerm = ELEMENTS.jarSearchInput.value.toLowerCase().trim();
            const jarCards = ELEMENTS.jarsGrid.querySelectorAll('.jar-card');
            let resultsFound = 0;

            jarCards.forEach(card => {
                const name = card.getAttribute('data-search-name') || '';
                const description = card.getAttribute('data-search-description') || '';

                // Check if the search term is found in the name OR description
                if (name.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                    resultsFound++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Handle no results message
            if (resultsFound === 0 && searchTerm.length > 0) {
                ELEMENTS.noSearchResults.classList.remove('hidden');
                if (ELEMENTS.noJarsMessage) ELEMENTS.noJarsMessage.classList.add('hidden'); // Hide the 'no jars created' message
            } else {
                ELEMENTS.noSearchResults.classList.add('hidden');
                 // Only show the default 'no jars created' message if no jars exist at all AND search term is empty
                if (ELEMENTS.noJarsMessage && ELEMENTS.totalJarsCountElement.textContent === '0' && searchTerm.length === 0) {
                     ELEMENTS.noJarsMessage.classList.remove('hidden');
                }
            }
        },

        initialize: () => {
            if (ELEMENTS.jarSearchInput) {
                ELEMENTS.jarSearchInput.addEventListener('input', SearchManager.filterJars);
            }
        }
    };

    // Initialize all modules when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        ThemeManager.initialize();
        SidebarManager.initialize();
        ModalManager.initialize();
        LayoutManager.initialize();
        SearchManager.initialize(); // Initialize Search

        // Initialize JarActionManager listeners
        if (ELEMENTS.confirmDeleteJar) ELEMENTS.confirmDeleteJar.addEventListener('click', JarActionManager.executeDelete);
        if (ELEMENTS.cancelDeleteJar) ELEMENTS.cancelDeleteJar.addEventListener('click', JarActionManager.closeDeleteModal);

        // Star Button Listeners
        document.querySelectorAll('.star-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const jarId = button.getAttribute('data-jar-id');
                const isStarred = button.getAttribute('data-is-starred') === '1';
                JarActionManager.openFavoriteModal(jarId, isStarred); 
            });
        });

        // Delete Button Listeners 
        document.querySelectorAll('.delete-jar-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const jarId = button.getAttribute('data-jar-id');
                const jarName = button.getAttribute('data-jar-name');
                JarActionManager.openDeleteModal(jarId, jarName);
            });
        });

        // Favorite Modal CTAs
        if (ELEMENTS.closeFavoriteModal) ELEMENTS.closeFavoriteModal.addEventListener('click', JarActionManager.closeFavoriteModal);
        if (ELEMENTS.confirmFavorite) ELEMENTS.confirmFavorite.addEventListener('click', JarActionManager.toggleFavorite);
        if (ELEMENTS.cancelFavorite) ELEMENTS.cancelFavorite.addEventListener('click', JarActionManager.closeFavoriteModal);
        
        // Global ESC key listener for modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (ELEMENTS.modal.classList.contains('flex')) ModalManager.closeModal();
                if (ELEMENTS.favoriteModal.classList.contains('flex')) JarActionManager.closeFavoriteModal();
                if (ELEMENTS.deleteJarModal.classList.contains('flex')) JarActionManager.closeDeleteModal();
            }
        });
        
        // Status message auto-hide for initial PHP message
        if(ELEMENTS.statusMessage && ELEMENTS.statusMessage.classList.contains('show')) {
            setTimeout(() => {
                ELEMENTS.statusMessage.classList.remove('show');
                setTimeout(() => ELEMENTS.statusMessage.classList.add('hidden'), 500); 
            }, 5000);
        }
    });
</script>
</body>
</html>