<?php
// ========================================================
// admin_dashboard.php — urlJAR Administrator Dashboard (Enhanced & Fixed)
// ========================================================

session_start();
// Set timezone to prevent PHP warnings
date_default_timezone_set('UTC'); // Or your server's local timezone
require_once '../includes/config.php'; // Correct path to PDO config

// --------------------------------------------------------
// 1. SECURITY CHECK & MESSAGE DISPLAY
// --------------------------------------------------------
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// Check for and display session messages (from delete_user.php)
$session_message = $_SESSION['admin_message'] ?? '';
$session_message_type = $_SESSION['admin_message_type'] ?? '';

// Clear session messages after display
unset($_SESSION['admin_message']);
unset($_SESSION['admin_message_type']);


// --------------------------------------------------------
// 2. DATA FETCH & CHART PREP (PDO)
// --------------------------------------------------------
$stats = [
    'total_users' => 0,
    'total_links' => 0,
    'total_jars' => 0,
    'avg_links_per_user' => 0,
    'all_users' => [],
    'all_links' => [],
    'all_jars' => [],
];
$db_error = false;

// Chart Data Arrays
$chart_links_by_month = ['labels' => [], 'data' => []];
$chart_links_per_user = ['labels' => [], 'data' => []];


try {
    // A. Fetch Primary Statistics & Recent Users (Combined)
    $sql_stats = "
        SELECT 
            (SELECT COUNT(id) FROM users) AS total_users,
            (SELECT COUNT(link_id) FROM links) AS total_links,
            (SELECT COUNT(jar_id) FROM jars) AS total_jars,
            (SELECT COUNT(link_id) / NULLIF(COUNT(DISTINCT user_id), 0) FROM links) AS avg_links
    ";
    $stmt = $pdo->query($sql_stats);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $stats['total_users'] = (int)$row['total_users'];
        $stats['total_links'] = (int)$row['total_links'];
        $stats['total_jars'] = (int)$row['total_jars'];
        $stats['avg_links_per_user'] = round($row['avg_links'] ?? 0, 1);
    }
    
    // B. Fetch All Users (for table and chart prep)
    $sql_all_users = "SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($sql_all_users);
    $stats['all_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // C. Fetch All Jars (for table)
    $sql_all_jars = "
        SELECT j.jar_id, j.name, j.link_count, j.is_starred, u.full_name AS user_name, j.created_at
        FROM jars j
        JOIN users u ON j.user_id = u.id
        ORDER BY j.created_at DESC
    ";
    $stmt = $pdo->query($sql_all_jars);
    $stats['all_jars'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // D. Fetch All Links (for table)
    $sql_all_links = "
        SELECT l.link_id, l.url, l.name AS link_name, l.tags, l.is_read, l.created_at, u.full_name AS user_name, j.name AS jar_name
        FROM links l
        JOIN users u ON l.user_id = u.id
        LEFT JOIN jars j ON l.jar_id = j.jar_id
        ORDER BY l.created_at DESC
        LIMIT 20 
    ";
    $stmt = $pdo->query($sql_all_links);
    $stats['all_links'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // E. Data for Links Added Over Time Chart
    $sql_monthly_links = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS month_year,
            COUNT(link_id) AS links_added
        FROM links
        GROUP BY month_year
        ORDER BY month_year ASC
    ";
    $stmt = $pdo->query($sql_monthly_links);
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($monthly_data as $row) {
        $formatted_month = date('M Y', strtotime($row['month_year'] . '-01'));
        $chart_links_by_month['labels'][] = $formatted_month;
        $chart_links_by_month['data'][] = (int)$row['links_added'];
    }
    
    // F. Data for Links Per User Chart (Top 10 most active users)
    $sql_user_links_count = "
        SELECT u.full_name, COUNT(l.link_id) AS link_count
        FROM users u
        JOIN links l ON u.id = l.user_id
        GROUP BY u.id, u.full_name 
        ORDER BY link_count DESC
        LIMIT 10
    ";
    $stmt = $pdo->query($sql_user_links_count);
    $user_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($user_data as $row) {
        $chart_links_per_user['labels'][] = htmlspecialchars($row['full_name']);
        $chart_links_per_user['data'][] = (int)$row['link_count'];
    }

} catch (PDOException $e) {
    error_log("Admin Dashboard DB Error: " . $e->getMessage());
    $db_error = true;
}

// --------------------------------------------------------
// 3. UI HELPER
// --------------------------------------------------------
function getStatusBadge($is_read) {
    if ($is_read == 1) {
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-neon-green/30 text-neon-green">Read</span>';
    }
    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-neon-pink/30 text-neon-pink">New</span>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>urlJAR | Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <script>
        // Tailwind Config (Consistent theme)
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
                        'dark-card': '#0F0F11',
                        'icon-red': '#DD0303',
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }
        .neon-card:hover { transform: translateY(-3px); }
        .neon-border-glow {
            border: 2px solid currentColor;
            box-shadow: 0 0 8px currentColor, inset 0 0 4px currentColor;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        .data-table {
            min-width: 1000px;
        }
        /* Message Styling */
        .message-success { background: #00FF7730; color: #00FF77; border-color: #00FF7750; }
        .message-error { background: #FF009930; color: #FF0099; border-color: #FF009950; }
        
        /* Delete Modal Styles */
        #deleteConfirmationModal {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }
        #deleteConfirmationModal.open {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body class="dark min-h-screen font-bubble p-8 lg:p-12">
    
    <header class="mb-10 lg:mb-12">
        <div class="flex justify-between items-center border-b pb-4 border-neon-blue/30">
            <h1 class="text-4xl md:text-5xl font-extrabold font-heading text-neon-pink">
                <i class="fas fa-screwdriver-wrench text-neon-blue mr-2"></i> Admin Panel
            </h1>
            <a href="admin_logout.php" class="text-gray-400 hover:text-neon-pink transition-colors px-4 py-2 rounded-lg bg-dark-card/50">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
        <p class="text-xl text-gray-400 font-light mt-2">
            Welcome back, <span class="text-neon-green font-semibold"><?php echo htmlspecialchars($admin_name); ?></span>. System overview at a glance.
        </p>
    </header>

    <?php if ($session_message): 
        $msg_class = $session_message_type === 'success' ? 'message-success' : 'message-error';
        $icon = $session_message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    ?>
        <div id="status-message" class="p-4 mb-6 text-sm rounded-lg shadow-md border <?php echo $msg_class; ?>">
            <i class="fas <?php echo $icon; ?> mr-2"></i> 
            <?php echo htmlspecialchars($session_message); ?>
        </div>
    <?php elseif ($db_error): ?>
        <div class="p-4 mb-6 text-sm text-white bg-red-700 rounded-lg shadow-md border border-red-900">
            <i class="fas fa-times-circle mr-2"></i> Critical Error: Cannot connect to or query the database. Check logs.
        </div>
    <?php endif; ?>

    <h2 class="text-3xl font-extrabold mb-6 border-b border-gray-700 pb-2 text-neon-blue">
        Key Metrics
    </h2>
    <div class="stats-grid mb-12">
        
        <article class="neon-card p-6 border-neon-green neon-border-glow text-neon-green">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-extrabold font-bebas"><?php echo number_format($stats['total_users']); ?></h3>
                    <p class="text-gray-400 text-sm">Total Users</p>
                </div>
                <i class="fas fa-users text-4xl opacity-80"></i>
            </div>
        </article>

        <article class="neon-card p-6 border-neon-pink neon-border-glow text-neon-pink">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-extrabold font-bebas"><?php echo number_format($stats['total_jars']); ?></h3>
                    <p class="text-gray-400 text-sm">Total Jars</p>
                </div>
                <i class="fas fa-layer-group text-4xl opacity-80"></i>
            </div>
        </article>
        
        <article class="neon-card p-6 border-neon-blue neon-border-glow text-neon-blue">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-extrabold font-bebas"><?php echo number_format($stats['total_links']); ?></h3>
                    <p class="text-gray-400 text-sm">Total Links Saved</p>
                </div>
                <i class="fas fa-link text-4xl opacity-80"></i>
            </div>
        </article>

        <article class="neon-card p-6 border-neon-orange neon-border-glow text-neon-orange">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-extrabold font-bebas"><?php echo $stats['avg_links_per_user']; ?></h3>
                    <p class="text-gray-400 text-sm">Avg. Links Per User</p>
                </div>
                <i class="fas fa-chart-bar text-4xl opacity-80"></i>
            </div>
        </article>
    </div>

    <h2 class="text-3xl font-extrabold mb-6 border-b border-gray-700 pb-2 text-neon-green">
        System Charts
    </h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        
        <!-- Links Added Over Time (Line Chart) -->
        <div class="neon-card p-6 border-2 border-neon-green/30">
            <h3 class="text-xl font-bold font-bubble text-neon-green mb-4">Links Added Per Month</h3>
            <div class="relative h-80">
                <canvas id="monthlyLinksChart"></canvas>
            </div>
        </div>
        
        <!-- Top 10 Active Users (Bar Chart) -->
        <div class="neon-card p-6 border-2 border-neon-blue/30">
            <h3 class="text-xl font-bold font-bubble text-neon-blue mb-4">Top 10 Links Per User</h3>
            <div class="relative h-80">
                <canvas id="linksPerUserChart"></canvas>
            </div>
        </div>

    </div>
    
    <!-- ========================================================= -->
    <!-- USER MANAGEMENT SECTION -->
    <!-- ========================================================= -->
    <h2 class="text-3xl font-extrabold mb-6 mt-12 border-b border-gray-700 pb-2 text-neon-pink">
        <i class="fas fa-users mr-2"></i> All Users
    </h2>
    <div class="mb-4 flex justify-end">
        <a href="add_admin_user.php" class="py-2 px-4 rounded-lg bg-neon-pink text-dark-card font-semibold hover:bg-pink-400 transition-colors inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Add New User
        </a>
    </div>
    <div class="neon-card p-6 table-container mb-12">
        <table class="w-full text-sm text-left text-gray-400 data-table">
            <thead class="text-xs text-neon-pink uppercase bg-dark-card/70">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Full Name</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">Joined</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php if ($stats['all_users']): ?>
                    <?php foreach ($stats['all_users'] as $user): ?>
                        <tr id="user-row-<?php echo $user['id']; ?>" class="border-b border-gray-700 hover:bg-dark-card/50 transition-colors">
                            <th scope="row" class="px-6 py-4 font-medium text-white"><?php echo $user['id']; ?></th>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td class="px-6 py-4 text-neon-blue"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-6 py-4"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td class="px-6 py-4 space-x-2 whitespace-nowrap">
                                <!-- EDIT Button: Redirects to edit_user.php -->
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-neon-green hover:text-green-400" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- DELETE Button: Now uses a button to trigger the modal -->
                                <button data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                    class="text-neon-pink hover:text-red-500 delete-modal-btn" title="Delete User">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                     <tr id="no-users-row" class="border-b border-gray-700"><td colspan="5" class="px-6 py-4 text-center text-gray-500">No users registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- ========================================================= -->
    <!-- JAR MANAGEMENT SECTION -->
    <!-- ========================================================= -->
    <h2 class="text-3xl font-extrabold mb-6 mt-12 border-b border-gray-700 pb-2 text-neon-orange">
        <i class="fas fa-layer-group mr-2"></i> All Jars (<?php echo $stats['total_jars']; ?> Total)
    </h2>
    <div class="neon-card p-6 table-container mb-12">
        <table class="w-full text-sm text-left text-gray-400 data-table">
            <thead class="text-xs text-neon-orange uppercase bg-dark-card/70">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Owner</th>
                    <th scope="col" class="px-6 py-3">Links</th>
                    <th scope="col" class="px-6 py-3">Starred</th>
                    <th scope="col" class="px-6 py-3">Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($stats['all_jars']): ?>
                    <?php foreach ($stats['all_jars'] as $jar): ?>
                        <tr class="border-b border-gray-700 hover:bg-dark-card/50 transition-colors">
                            <th scope="row" class="px-6 py-4 font-medium text-white"><?php echo $jar['jar_id']; ?></th>
                            <td class="px-6 py-4 text-neon-pink"><?php echo htmlspecialchars($jar['name']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($jar['user_name']); ?></td>
                            <td class="px-6 py-4 font-bold"><?php echo number_format($jar['link_count']); ?></td>
                            <td class="px-6 py-4">
                                <?php if ($jar['is_starred']): ?>
                                    <i class="fas fa-star text-yellow-400" title="Starred"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-gray-600" title="Not Starred"></i>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4"><?php echo date('Y-m-d', strtotime($jar['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                     <tr class="border-b border-gray-700"><td colspan="6" class="px-6 py-4 text-center text-gray-500">No jars have been created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ========================================================= -->
    <!-- LINK MANAGEMENT SECTION -->
    <!-- ========================================================= -->
    <h2 class="text-3xl font-extrabold mb-6 mt-12 border-b border-gray-700 pb-2 text-neon-purple">
        <i class="fas fa-link mr-2"></i> Recent Links (<?php echo $stats['total_links']; ?> Total)
    </h2>
    <div class="neon-card p-6 table-container mb-12">
        <table class="w-full text-sm text-left text-gray-400 data-table">
            <thead class="text-xs text-neon-purple uppercase bg-dark-card/70">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">URL</th>
                    <th scope="col" class="px-6 py-3">User</th>
                    <th scope="col" class="px-6 py-3">Jar</th>
                    <th scope="col" class="px-6 py-3">Tags</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($stats['all_links']): ?>
                    <?php foreach ($stats['all_links'] as $link): ?>
                        <tr class="border-b border-gray-700 hover:bg-dark-card/50 transition-colors">
                            <th scope="row" class="px-6 py-4 font-medium text-white"><?php echo $link['link_id']; ?></th>
                            <td class="px-6 py-4 text-neon-green"><?php echo htmlspecialchars($link['link_name']); ?></td>
                            <td class="px-6 py-4 truncate max-w-xs">
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="text-neon-blue hover:underline">
                                    <?php echo htmlspecialchars($link['url']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($link['user_name']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($link['jar_name'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-xs"><?php echo htmlspecialchars($link['tags'] ?? '-'); ?></td>
                            <td class="px-6 py-4"><?php echo getStatusBadge($link['is_read']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                     <tr class="border-b border-gray-700"><td colspan="7" class="px-6 py-4 text-center text-gray-500">No links have been saved yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ========================================================= -->
    <!-- DELETE CONFIRMATION MODAL -->
    <!-- ========================================================= -->
    <div id="deleteConfirmationModal" class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4" onclick="if (event.target.id === 'deleteConfirmationModal') closeModal()">
        <div class="relative w-full max-w-sm">
            
            <div class="neon-card p-6 border-4 border-icon-red/50 shadow-2xl">
                <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-6xl text-icon-red mb-4 neon-border-glow p-3 rounded-full border-icon-red"></i>
                    <h3 class="text-2xl font-bold font-bubble text-neon-pink mb-2">
                        Permanent Deletion
                    </h3>
                    <p class="text-gray-300 text-base mb-6">
                        You are about to delete user <span id="userNameSpan" class="font-extrabold text-neon-green"></span> (ID: <span id="userIdSpan" class="font-extrabold text-neon-green"></span>).
                    </p>
                    
                    <p class="text-sm text-icon-red font-semibold mb-6 p-2 border border-icon-red rounded-lg bg-icon-red/10">
                        This action will **PERMANENTLY** delete the user and **ALL** their associated links and jars. This cannot be undone.
                    </p>

                    <form method="POST" action="delete_user.php" id="deleteForm" class="flex space-x-4">
                        <input type="hidden" name="user_id" id="modalUserIdInput" value="">
                        
                        <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl font-bold transition-all duration-300 
                            bg-gray-600 text-white hover:bg-gray-700">
                            Cancel
                        </button>
                        
                        <button type="submit" class="flex-1 py-3 rounded-xl font-bold transition-all duration-300 
                            bg-icon-red text-white border-2 border-icon-red hover:bg-red-700 
                            shadow-lg shadow-icon-red/50">
                            Yes, Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>


    <script>
        // --- Custom Delete Confirmation Logic ---
        const deleteModal = document.getElementById('deleteConfirmationModal');
        const userIdSpan = document.getElementById('userIdSpan');
        const userNameSpan = document.getElementById('userNameSpan');
        const modalUserIdInput = document.getElementById('modalUserIdInput');

        function openModal(userId, userName) {
            userIdSpan.textContent = userId;
            userNameSpan.textContent = userName;
            modalUserIdInput.value = userId;
            deleteModal.classList.add('open');
        }

        function closeModal() {
            deleteModal.classList.remove('open');
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            // Attach click listener to all delete buttons
            const deleteButtons = document.querySelectorAll('.delete-modal-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default action (which was nothing or old confirm)
                    const userId = this.getAttribute('data-id');
                    const userName = this.getAttribute('data-name');
                    openModal(userId, userName);
                });
            });
            
            // --- Chart Initialization Functions (Unchanged) ---
            const linksByMonthData = {
                labels: <?php echo json_encode($chart_links_by_month['labels'] ?? []); ?>,
                data: <?php echo json_encode($chart_links_by_month['data'] ?? []); ?>
            };
            const linksPerUserData = {
                labels: <?php echo json_encode($chart_links_per_user['labels'] ?? []); ?>,
                data: <?php echo json_encode($chart_links_per_user['data'] ?? []); ?>
            };

            function createLineChart() {
                const ctx = document.getElementById('monthlyLinksChart');
                if (!ctx) return;
                new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: linksByMonthData.labels,
                        datasets: [{
                            label: 'Links Added',
                            data: linksByMonthData.data,
                            backgroundColor: 'rgba(0, 255, 119, 0.4)', // Neon Green
                            borderColor: '#00FF77',
                            borderWidth: 2,
                            pointBackgroundColor: '#00FF77',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#e6e6e6' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            x: { ticks: { color: '#e6e6e6' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            function createBarChart() {
                const ctx = document.getElementById('linksPerUserChart');
                if (!ctx) return;
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: linksPerUserData.labels,
                        datasets: [{
                            label: 'Total Links',
                            data: linksPerUserData.data,
                            backgroundColor: [
                                '#FF0099', '#00CCFF', '#00FF77', '#9D00FF', '#FF8800',
                                '#FF0099', '#00CCFF', '#00FF77', '#9D00FF', '#FF8800'
                            ],
                            borderColor: '#0F0F11',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y', // Makes it a horizontal bar chart
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { beginAtZero: true, ticks: { color: '#e6e6e6' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            y: { ticks: { color: '#e6e6e6' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // Initialize charts when the DOM is ready
            createLineChart();
            createBarChart();
        });
    </script>
</body>
</html>
