<?php
// login.php (Production-Ready Template)

// --- PHP Login Logic Placeholder ---
// This is where you would include your configuration, start sessions,
// and process the POST request from the login form.

$login_message = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // In a real application, you would:
    // 1. Sanitize and validate input (email/password).
    // 2. Query the database to find the user.
    // 3. Verify the hashed password (e.g., using password_verify).
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Handled securely via hashing, but simple validation remains

    // --- MOCK LOGIN SUCCESS/FAILURE ---
    if ($email === 'test@urljar.com' && $password === 'Password123!') {
        // In reality, start session and redirect
        // $_SESSION['user_id'] = $user_id;
        $login_success = true;
        $login_message = 'Login successful! Redirecting...';
        // header('Location: /dashboard.php'); exit; 
    } else {
        $login_success = false;
        $login_message = 'Invalid email or password. Please try again.';
    }
}
// ------------------------------------

// Use a distinct neon color for the login page hero button/accent
$main_accent = 'neon-blue'; 
$main_glow = 'neon-glow-blue'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="urlJAR: Log in to the next-gen, visually-stunning bookmark manager. Access your vibrant 'Jars' and organized digital life.">
    <meta name="keywords" content="bookmark manager, URL organizer, web app, productivity tool, neon aesthetic, gen z design, creative organization, link saving, login">
    <title>urlJAR | Log In to Your Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&display=swap" rel="stylesheet">

 <style>
    
    /* 1. Root Variables & Base Style (COPIED FROM INDEX.PHP) */
    :root {
        --neon-pink: #FF0099; /* Primary Accent (Magenta) */
        --neon-blue: #00CCFF; /* Secondary Accent (Cyan) */
        --neon-green: #00FF77; /* Tertiary Accent (Lime) */
        --neon-purple: #9D00FF;
        --neon-orange: #FF8800;
        --neon-teal: #00FFFF;
        --neon-violet: #CC00FF;
        
        /* NEW CUSTOM COLORS */
        --icon-red: #DD0303;
        --icon-indigo: #450693;

        --dark-background: #08080A;
        --text-primary: #f0f0f0;
        --dark-card: #0F0F11;
        --text-secondary: #b0b0b0;
    }

 
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-background);
            color: var(--text-primary);
            overflow-x: hidden; /* Prevents horizontal scroll */
        



    /* Criss-cross pattern */
    background-image:
      repeating-linear-gradient(
        45deg,
        #222 0px,
        #222 2px,
        #111 2px,
        #111 4px
      ),
      repeating-linear-gradient(
        -45deg,
        #222 0px,
        #222 2px,
        #111 2px,
        #111 4px
      );
    background-size: 20px 20px;
  }
    /* 2. Custom Fonts & Utilities (COPIED FROM INDEX.PHP) */
    .bubble-font { font-family: 'Poppins', sans-serif; }
    .heading-font { font-family: 'Bungee Outline', cursive; }
    
    /* Utility Colors from variables (Standard) */
    .bg-neon-pink { background-color: var(--neon-pink); }
    .text-neon-pink { color: var(--neon-pink); }
    .border-neon-pink { border-color: var(--neon-pink); }

    .bg-neon-blue { background-color: var(--neon-blue); }
    .text-neon-blue { color: var(--neon-blue); }
    .border-neon-blue { border-color: var(--neon-blue); }

    .bg-neon-green { background-color: var(--neon-green); }
    .text-neon-green { color: var(--neon-green); }
    .border-neon-green { border-color: var(--neon-green); }

    .text-neon-purple { color: var(--neon-purple); }
    .border-neon-purple { border-color: var(--neon-purple); }

    .bg-neon-orange { background-color: var(--neon-orange); }
    .text-neon-orange { color: var(--neon-orange); }
    .border-neon-orange { border-color: var(--neon-orange); }

    .text-neon-teal { color: var(--neon-teal); }
    .border-neon-teal { border-color: var(--neon-teal); }

    .text-neon-violet { color: var(--neon-violet); }
    .border-neon-violet { border-color: var(--neon-violet); }
    
    /* NEW: Utility Classes for custom icon colors */
    .text-icon-red { color: var(--icon-red); }
    .text-icon-indigo { color: var(--icon-indigo); }
    /* ADDED UTILITY CLASSES FOR ICON COLORS */

/* Custom Red */
.bg-icon-red { background-color: var(--icon-red); }
.text-icon-red { color: var(--icon-red); }
.border-icon-red { border-color: var(--icon-red); }
.neon-glow-red { text-shadow: 0 0 1px var(--icon-red); }

/* Custom Indigo (for the other card) */
.bg-icon-indigo { background-color: var(--icon-indigo); }
.text-icon-indigo { color: var(--icon-indigo); }
.border-icon-indigo { border-color: var(--icon-indigo); }
.neon-glow-indigo { text-shadow: 0 0 1px var(--icon-indigo); }



    /* 3. NEON Glow Styles (COPIED FROM INDEX.PHP) */
    .neon-glow-pink { text-shadow: 0 0 1px var(--neon-pink); }
    .neon-glow-blue { text-shadow: 0 0 1px var(--neon-blue); }
    .neon-glow-green { text-shadow: 0 0 1px var(--neon-green); }
    .neon-glow-orange { text-shadow: 0 0 1px var(--neon-orange); }
    .neon-glow-teal { text-shadow: 0 0 1px var(--neon-teal); }
    .neon-glow-violet { text-shadow: 0 0 1px var(--neon-violet); }
    
    .neon-border {
        border: 2px solid;
        box-shadow: 0 0 4px currentColor, inset 0 0 2px currentColor;
    }
    
    /* 7. Background Pattern (COPIED FROM INDEX.PHP) */
    .zigzag {
        background: 
            linear-gradient(135deg, var(--dark-background) 25%, transparent 25%) -50px 0,
            linear-gradient(225deg, var(--dark-background) 25%, transparent 25%) -50px 0,
            linear-gradient(315deg, var(--dark-background) 25%, transparent 25%),
            linear-gradient(45deg, var(--dark-background) 25%, transparent 25%);
        background-size: 100px 100px;
        background-color: #0a0a0c;
        opacity: 0.3;
    }

     /* 4. FAQ Accordion CSS (Pure JS Slide Effect) - Not needed here, but kept in case of reuse */
    .faq-answer {
        transition: max-height 0.4s ease-in-out, margin-top 0.4s ease-in-out; 
        overflow: hidden;
    }
    .faq-answer.collapsed {
        max-height: 0 !important; /* Critical for transition start */
        margin-top: 0 !important;
    }
    
    /* Icon Rotation */
    .faq-item .fas {
        transition: transform 0.3s ease;
    }

    /* 5. Mobile Menu Overlay - (COPIED FROM INDEX.PHP) */
    .mobile-menu {
        transition: transform 0.4s ease-in-out;
        transform: translateX(100%);
        background: var(--dark-background);
    }
    .mobile-menu.active {
        transform: translateX(0);
    }
    
    /* Input shake animation */
    .animate-shake {
        animation: shake 0.5s;
    }
    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-6px); }
      50% { transform: translateX(6px); }
      75% { transform: translateX(-4px); }
      100% { transform: translateX(0); }
    }

    /* Ensure text-neon-green is styled for the success box text */
    .text-success-green { color: var(--neon-green); }
    
    /* 4. ANIMATIONS & Micro-Interactions (COPIED FROM INDEX.PHP) */
    
    /* Hero Button Pulse Animation (Changed to use main-accent for Login) */
    .pulse-once {
        animation: pulse 1.5s ease-out 1;
    }
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 204, 255, 0.4); } /* Neon Blue Pulse */
        70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(0, 204, 255, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 204, 255, 0); }
    }

    /* Universal Hover Lift Effect (Applied to buttons and cards) */
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s;
        will-change: transform, box-shadow;
    }
    .hover-lift:hover {
        transform: translateY(-5px) scale(1.01);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
    }
    
    /* Neon Button Base */
    .neon-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease-in-out;
        border: 2px solid;
        font-family: 'Bebas Neue', sans-serif;
        letter-spacing: 1px;
    }

    .neon-btn:hover {
        box-shadow: 0 0 15px currentColor, 0 0 25px currentColor;
    }

    /* Fade-In-on-Scroll Utility (Staggered) */
    .stagger-in {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    .stagger-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* 5. Unique 'Pinterest' / Card Layout Styling (Refined for cleaner hover) */
    .neon-card {
        background: rgba(15, 15, 17, 0.85);
        border-radius: 16px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
    }
    
    /* Input Focus Glow (Changed to use main-accent: neon-blue) */
    .form-input {
        background: #00000040; /* Slightly darker input background */
        border: 1px solid var(--text-secondary);
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    .form-input:focus {
        border-color: var(--neon-blue);
        box-shadow: 0 0 0 3px rgba(0, 204, 255, 0.3);
    }

    /* Footer Icon Glows */
    .fab:hover {
        text-shadow: 0 0 5px currentColor;
    }
    
    /* Ensure text-neon-green is styled for the success box text */
    .text-success-green { color: var(--neon-green); }
    .neon-glow-green { text-shadow: 0 0 4px var(--neon-green); }
    .neon-glow-red { text-shadow: 0 0 4px var(--icon-red); }

    /* Small utility CSS (shake animation) */
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-6px); }
        50% { transform: translateX(6px); }
        75% { transform: translateX(-4px); }
        100% { transform: translateX(0); }
    }

    /* Ensure modal centers nicely on very large screens (TVs) */
    #login-modal .max-w-lg { max-width: 42rem; }


</style>
</head>
<body class="bg-dark-background">
    <div id="mobile-menu" class="mobile-menu fixed top-0 right-0 w-full h-full z-[100] p-6 lg:hidden shadow-2xl">
        <div class="flex justify-between items-center mb-10">
            <span class="text-3xl font-bold heading-font text-neon-blue neon-glow-blue">url<span class="text-neon-pink neon-glow-pink">JAR</span></span>
            <button id="close-menu-btn" class="text-neon-pink text-3xl hover:text-white transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <ul class="flex flex-col space-y-6 text-2xl">
            <li><a href="index.php#features" class="block text-gray-300 hover:text-neon-green transition-colors font-semibold py-2 bubble-font">Features</a></li>
            <li><a href="index.php#how-it-works" class="block text-gray-300 hover:text-neon-blue transition-colors font-semibold py-2 bubble-font">Workflow</a></li>
            <li><a href="index.php#why-us" class="block text-gray-300 hover:text-neon-purple transition-colors font-semibold py-2 bubble-font">Why urlJAR?</a></li>
            <li><a href="index.php#faq" class="block text-gray-300 hover:text-neon-orange transition-colors font-semibold py-2 bubble-font">FAQ</a></li>
            <li><a href="index.php#signup" class="block text-neon-pink hover:text-white transition-colors font-semibold py-2 bubble-font">Sign Up</a></li>
        </ul>
        <a href="#login" class="neon-btn bg-neon-blue text-dark-background px-6 py-3 rounded-lg font-extrabold text-xl border-neon-blue w-full block text-center mt-12 hover-lift">
            Log In Now
        </a>
    </div>

    <header>
        <nav class="fixed w-full z-50 bg-dark-background/90 backdrop-blur-md border-b border-gray-900 shadow-xl">
            <div class="container mx-auto px-4 md:px-8 py-4 flex justify-between items-center">
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-neon-pink rounded-xl flex items-center justify-center neon-border border-neon-pink">
                        <i class="fas fa-bookmark text-dark-background text-lg"></i>
                    </div>
                    <span class="text-3xl font-bold heading-font text-neon-blue neon-glow-blue">url<span class="text-neon-pink neon-glow-pink">JAR</span></span>
                </a>
                
                <ul class="hidden lg:flex space-x-10 text-lg">
                    <li><a href="index.php#features" class="text-gray-300 hover:text-neon-green transition-colors font-medium hover:neon-glow-green">Features</a></li>
                    <li><a href="index.php#how-it-works" class="text-gray-300 hover:text-neon-blue transition-colors font-medium hover:neon-glow-blue">Workflow</a></li>
                    <li><a href="index.php#why-us" class="text-gray-300 hover:text-neon-purple transition-colors font-medium hover:neon-glow-purple">Why urlJAR?</a></li>
                    <li><a href="index.php#faq" class="text-gray-300 hover:text-neon-orange transition-colors font-medium hover:neon-glow-orange">FAQ</a></li>
                </ul>
                
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="index.php#signup" class="text-gray-300 hover:text-neon-pink transition-colors font-semibold hover:neon-glow-pink">Sign Up</a>
                    <a href="#login" class="neon-btn bg-neon-blue text-dark-background px-6 py-2 rounded-lg font-extrabold text-lg border-neon-blue hover-lift">
                        Log In
                    </a>
                </div>
                
                <button id="mobile-menu-btn" class="lg:hidden text-neon-pink text-2xl p-2 rounded-lg hover:bg-white/10 transition-colors focus:outline-none neon-glow-pink">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>


    <section id="login" class="pt-20 pb-20 sm:pt-24 sm:pb-24 md:pt-28 md:pb-28 lg:pt-32 lg:pb-32 xl:pt-36 xl:pb-36 py-12 md:py-20 lg:py-28 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-neon-blue/30 to-neon-green/30"></div>
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl mx-auto text-center stagger-in">
                 <div class="container mx-auto px-6 flex flex-col items-center relative z-10 text-center">
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold mb-4 leading-tight bubble-font stagger-in">
                <span class="text-neon-blue neon-glow-blue">WELCOME</span> <span class="text-neon-pink neon-glow-pink">BACK!</span> 
            </h1>
            <p class="text-lg md:text-xl mb-12 text-gray-300 max-w-xl stagger-in" style="transition-delay: 0.1s;">
                Enter your credentials to continue organizing your links with urlJAR's neon magic.
            </p>
       
        </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 text-white bubble-font">
                    SECURE LOGIN
                </h2>
                <p class="text-sm sm:text-base md:text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                    Access your account in seconds. Let's get back to jarring!
                </p>
            </div>

            <div class="max-w-md mx-auto neon-card p-5 sm:p-6 md:p-8 border-neon-blue hover-lift rounded-2xl stagger-in" style="transition-delay: 0.2s;">
                <h3 class="text-xl sm:text-2xl font-bold mb-4 text-neon-blue neon-glow-blue bubble-font text-center">
                    LOG IN TO urlJAR
                </h3>

                <form id="login-form" method="POST" action="api/process_login.php" class="space-y-4" novalidate>
                    <input type="hidden" name="login" value="1">
                    
                    <div>
                        <label for="login-email" class="sr-only">Email address</label>
                        <input type="email" id="login-email" name="email" placeholder="Email Address"
                            class="form-input w-full px-4 py-3 bg-black/50 border border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-neon-blue placeholder-gray-400 text-white"
                            required autocomplete="email" inputmode="email">
                    </div>

                    <div class="relative">
                        <label for="login-password" class="sr-only">Password</label>
                        <input type="password" id="login-password" name="password"
                            placeholder="Password"
                            class="form-input w-full px-4 py-3 pr-12 bg-black/50 border border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-neon-blue placeholder-gray-400 text-white"
                            required autocomplete="current-password">
                        
                        <button type="button" id="toggle-password" aria-label="Toggle password visibility"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 focus:outline-none text-gray-400 hover:text-neon-blue transition-colors">
                            <i id="eye-open-icon" class="fa-regular fa-eye text-lg transition-opacity"></i>
                            <i id="eye-closed-icon" class="fa-regular fa-eye-slash text-lg opacity-0 absolute top-1/2 -translate-y-1/2 transition-opacity"></i>
                        </button>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-neon-blue border-gray-600 rounded focus:ring-neon-blue bg-gray-700">
                            <label for="remember-me" class="ml-2 block text-gray-400">Remember me</label>
                        </div>
                        <a href="#" class="text-neon-pink hover:text-white transition-colors text-sm">Forgot Password?</a>
                    </div>
                    
                    <div id="message-container" class="text-sm p-3 rounded-xl <?php echo $login_message ? '' : 'hidden'; ?>" role="alert" aria-live="polite">
                        <?php if ($login_message): ?>
                            <div class="
                                <?php echo $login_success ? 'bg-neon-green/30 border-neon-green/70 text-neon-green neon-glow-green' : 'bg-red-900/30 border-red-500/40 text-neon-pink neon-glow-red'; ?> 
                                p-3 rounded-xl text-left
                            ">
                                <?php echo htmlspecialchars($login_message); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" id="login-button" class="w-full neon-btn bg-neon-pink text-dark-background font-extrabold py-3 px-5 rounded-xl hover-lift border-neon-blue">
                        LOG IN 🗝️
                    </button>
                </form>

                <p class="text-gray-400 text-xs sm:text-sm mt-4 text-center">
                    Don't have an account?
                    <a href="index.php#signup" class="text-neon-pink hover:underline font-semibold">Sign Up Now</a>.
                </p>
            </div>
        </div>
    </section>

    <div id="redirect-modal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/80 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="bg-dark-card p-8 rounded-xl shadow-2xl max-w-sm w-full text-center neon-border border-neon-green hover-lift">
        <div class="w-16 h-16 bg-neon-green rounded-full flex items-center justify-center mx-auto mb-4 neon-glow-green">
            <i class="fas fa-check text-black text-2xl"></i>
        </div>
        <h3 id="modal-title" class="text-2xl font-bold mb-2 text-neon-green neon-glow-green bubble-font">Login Successful!</h3>
        <p class="text-gray-300 mb-6" id="modal-text">Redirecting to your urlJAR Dashboard...</p>
        <div class="w-full h-2 bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-neon-green animate-pulse w-full"></div>
        </div>
    </div>
</div>
    
    <footer class="bg-black py-12 border-t border-gray-800">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-8 border-b border-gray-900 pb-8 mb-8">
                <div class="col-span-2 lg:col-span-2 mb-4 md:mb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-neon-pink rounded-xl flex items-center justify-center neon-border border-neon-pink">
                            <i class="fas fa-bookmark text-dark-background text-xl"></i>
                        </div>
                        <span class="text-3xl font-bold heading-font text-neon-blue neon-glow-blue">url<span class="text-neon-pink neon-glow-pink">JAR</span></span>
                    </div>
                    <p class="text-gray-400 max-w-xs">The ultimate way to save, organize, and access your bookmarks across all devices.</p>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4 text-neon-green bubble-font">PRODUCT</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="index.php#features" class="hover:text-neon-green transition-colors">Features</a></li>
                        <li><a href="index.php#how-it-works" class="hover:text-neon-green transition-colors">Workflow</a></li>
                        <li><a href="index.php#try-now" class="hover:text-neon-green transition-colors">Demo</a></li>
                        <li><a href="index.php#faq" class="hover:text-neon-green transition-colors">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4 text-neon-blue bubble-font">COMPANY</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-neon-blue transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-neon-blue transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-neon-blue transition-colors">Careers</a></li>
                        <li><a href="#" class="hover:text-neon-blue transition-colors">Press</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4 text-neon-pink bubble-font">LEGAL</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-neon-pink transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-neon-pink transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-neon-pink transition-colors">Security</a></li>
                        <li><a href="#" class="hover:text-neon-pink transition-colors">Sitemap</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">© 2024 urlJAR. All rights reserved. Made with ✨ for digital natives.</p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-neon-pink transition-colors text-xl"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="text-gray-400 hover:text-neon-blue transition-colors text-xl"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-neon-green transition-colors text-xl"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-neon-purple transition-colors text-xl"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </div>
    </footer>

  <script>
document.addEventListener('DOMContentLoaded', () => {
    // =========================================================
    // 1. CORE DOM ELEMENTS & CONFIG
    // =========================================================
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    const togglePasswordButton = document.getElementById('toggle-password');
    const eyeOpenIcon = document.getElementById('eye-open-icon');
    const eyeClosedIcon = document.getElementById('eye-closed-icon');
    const loginButton = document.getElementById('login-button');
    const messageContainer = document.getElementById('message-container');
    const redirectModal = document.getElementById('redirect-modal');
    const modalText = document.getElementById('modal-text');
    const originalButtonText = 'LOG IN 🗝️';
    
    // Menu logic remains the same
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const closeMenuBtn = document.getElementById('close-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const staggerInElements = document.querySelectorAll('.stagger-in');


    // =========================================================
    // 2. UTILITY & FEEDBACK FUNCTIONS
    // =========================================================

    // Utility function to display success/error messages in the form
    const displayMessage = (message, isSuccess = false) => {
        messageContainer.innerHTML = '';
        messageContainer.classList.remove('hidden', 'animate-shake');
        
        const messageDiv = document.createElement('div');
        messageDiv.textContent = message;
        
        // Apply success (green) or error (pink/red) styles
        const successClasses = 'bg-neon-green/30 border-neon-green/70 text-neon-green neon-glow-green';
        const errorClasses = 'bg-red-900/30 border-red-500/40 text-neon-pink neon-glow-red';
        
        messageDiv.className = `${isSuccess ? successClasses : errorClasses} p-3 rounded-xl text-left`;
        messageContainer.appendChild(messageDiv);
        
        messageContainer.classList.add('animate-shake');
        setTimeout(() => messageContainer.classList.remove('animate-shake'), 500);
    };

    const clearMessage = () => {
        messageContainer.classList.add('hidden');
        messageContainer.innerHTML = '';
    };

    const setButtonLoading = () => {
        loginButton.disabled = true;
        loginButton.innerHTML = `
            <span class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg> Logging In
            </span>`;
    };

    const resetButton = () => {
        loginButton.disabled = false;
        loginButton.textContent = originalButtonText;
    };
    
    // --- CLIENT-SIDE VALIDATION LOGIC ---
    const validateForm = () => {
        clearMessage();
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            displayMessage("Please enter a valid email address.", false);
            emailInput.focus();
            return false;
        }
        
        if (password.length === 0) {
            displayMessage("Password cannot be empty.", false);
            passwordInput.focus();
            return false;
        }

        return true;
    };

    // --- PASSWORD TOGGLE ---
    if (togglePasswordButton) {
        togglePasswordButton.addEventListener('click', (e) => {
            e.preventDefault();
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';

            eyeOpenIcon.classList.toggle('opacity-0', !isPassword);
            eyeClosedIcon.classList.toggle('opacity-0', isPassword);
            passwordInput.focus();
        });
    }

    // --- CORE AJAX LOGIN HANDLER ---
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); // Crucial: Stop traditional form submit

        if (!validateForm()) {
            return;
        }
        
        setButtonLoading();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api/process_login.php', { // Target the login handler
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // 1. Show success message in the form
                displayMessage(result.message || "Login successful!", true);
                
                // 2. Show the modal
                modalText.textContent = `Welcome, ${data.email.split('@')[0]}! Redirecting to your urlJAR Dashboard...`;
                redirectModal.classList.remove('hidden');
                redirectModal.classList.add('flex'); // Show the modal
                
                // 3. Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = result.redirect || 'dashboard.php'; 
                }, 2000); 

            } else {
                // Login failed (due to 401/400 status from PHP)
                displayMessage(result.message || "Login failed. Check your credentials.", false);
                resetButton();
            }

        } catch (error) {
            console.error('Login Error:', error);
            displayMessage("A network or server error occurred. Please try again.", false);
            resetButton();
        } 
    });


    // =========================================================
    // 3. MOBILE MENU LOGIC
    // =========================================================
    // (Existing Mobile Menu Logic - COPIED)
    if (mobileMenuBtn && mobileMenu && closeMenuBtn) {
        const toggleMenu = (open) => {
            if (open) {
                mobileMenu.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        };

        mobileMenuBtn.addEventListener('click', () => toggleMenu(true));
        closeMenuBtn.addEventListener('click', () => toggleMenu(false));

        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => toggleMenu(false));
        });
    }

    // =========================================================
    // 4. INTERSECTION OBSERVER (Fade-in-on-Scroll)
    // =========================================================
    // (Existing Intersection Observer Logic - COPIED)
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const delay = element.getAttribute('data-delay') || 0;
                
                setTimeout(() => {
                    element.classList.add('visible');
                    observer.unobserve(element);
                }, parseFloat(delay) * 1000); 
            }
        });
    }, {
        rootMargin: '0px',
        threshold: 0.1 
    });

    staggerInElements.forEach(el => {
        observer.observe(el);

        if (el.getBoundingClientRect().top < window.innerHeight) {
            const delay = el.getAttribute('data-delay') || 0;
            setTimeout(() => {
                el.classList.add('visible');
            }, parseFloat(delay) * 1000); 
        }
    });
});
</script>
</body>
</html>