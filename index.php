<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="urlJAR: The next-gen, visually-stunning bookmark manager. Organize your digital life with vibrant 'Jars,' collaboration, and lightning-fast search. Built for creators and digital natives.">
    <meta name="keywords" content="bookmark manager, URL organizer, web app, productivity tool, neon aesthetic, gen z design, creative organization, digital life, link saving, pinterest layout">
    <title>urlJAR | Your Bookmarks, but Make it Neon ✨</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Bungee+Outline&family=Bebas+Neue&display=swap" rel="stylesheet">

 <style>
    
    /* 1. Root Variables & Base Style (EXPANDED with custom colors) */
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
    /* 2. Custom Fonts & Utilities (EXPANDED with custom colors) */
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



    /* 3. NEON Glow Styles (FIXED INTENSITY) */
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
    
    /* 7. Background Pattern (FIXED SEMICOLON) */
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

     /* 4. FAQ Accordion CSS (Pure JS Slide Effect) */
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

    /* 5. Mobile Menu Overlay */
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
    .neon-glow-green { text-shadow: 0 0 4px var(--neon-green); }

    

    /* 4. ANIMATIONS & Micro-Interactions */
    
    /* Floating Jar Animation */
    .jar-animation { animation: float 6s ease-in-out infinite; }
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); }
        50% { transform: translateY(-15px) rotate(2deg) scale(1.02); }
    }

    /* Hero Button Pulse Animation */
    .pulse-once {
        animation: pulse 1.5s ease-out 1;
    }
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 255, 119, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(0, 255, 119, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 255, 119, 0); }
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
    
    /* 6. Background Grid Effect */
    .cyber-grid {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: linear-gradient(0deg, transparent 95%, #1a1a1e 95%), linear-gradient(90deg, transparent 95%, #1a1a1e 95%);
        background-size: 50px 50px;
        opacity: 0.1;
        pointer-events: none;
        z-index: 0;
    }

    /* 7. Mobile Menu Overlay */
    .mobile-menu {
        transition: transform 0.4s ease-in-out;
        transform: translateX(100%);
        background: var(--dark-background);
    }
    .mobile-menu.active {
        transform: translateX(0);
    }
    
    /* Input Focus Glow */
    .form-input {
        background: #00000040; /* Slightly darker input background */
        border: 1px solid var(--text-secondary);
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    .form-input:focus {
        box-shadow: 0 0 0 3px rgba(0, 255, 119, 0.3);
    }

    /* Footer Icon Glows */
    .fab:hover {
        text-shadow: 0 0 5px currentColor;
    }

    /* Small utility CSS (shake animation) */
    @keyframes shake {
  0% { transform: translateX(0); }
  25% { transform: translateX(-6px); }
  50% { transform: translateX(6px); }
  75% { transform: translateX(-4px); }
  100% { transform: translateX(0); }
}

/* Ensure modal centers nicely on very large screens (TVs) */
#signup-modal .max-w-lg { max-width: 42rem; }


</style>
</head>
<body class="bg-dark-background">
    <div id="mobile-menu" class="mobile-menu fixed top-0 right-0 w-full h-full z-[100] p-6 lg:hidden shadow-2xl">
        <div class="flex justify-between items-center mb-10">
            <span class="text-3xl font-bold heading-font text-neon-blue neon-glow-blue">url<span class="text-neon-pink neon-glow-pink">JAR</span></span>
            <button id="close-menu-btn" class="text-neon-pink text-3xl hover:text-white transition-colors"><i class="fas fa-times"></i></button>
        </div>
        <ul class="flex flex-col space-y-6 text-2xl">
            <li><a href="#features" class="block text-gray-300 hover:text-neon-green transition-colors font-semibold py-2 bubble-font">Features</a></li>
            <li><a href="#how-it-works" class="block text-gray-300 hover:text-neon-blue transition-colors font-semibold py-2 bubble-font">Workflow</a></li>
            <li><a href="#why-us" class="block text-gray-300 hover:text-neon-purple transition-colors font-semibold py-2 bubble-font">Why urlJAR?</a></li>
            <li><a href="#faq" class="block text-gray-300 hover:text-neon-orange transition-colors font-semibold py-2 bubble-font">FAQ</a></li>
            <li><a href="#signup" class="block text-neon-pink hover:text-white transition-colors font-semibold py-2 bubble-font">Sign Up / Sign In</a></li>
        </ul>
        <a href="#signup" class="neon-btn bg-neon-green text-black px-6 py-3 rounded-lg font-extrabold text-xl border-neon-green w-full block text-center mt-12 hover-lift">
            Start Free Now
        </a>
    </div>

    <header>
        <nav class="fixed w-full z-50 bg-dark-background/90 backdrop-blur-md border-b border-gray-900 shadow-xl">
            <div class="container mx-auto px-4 md:px-8 py-4 flex justify-between items-center">
                <a href="#hero" class="flex items-center space-x-2">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-neon-pink rounded-xl flex items-center justify-center neon-border border-neon-pink">
                        <i class="fas fa-bookmark text-dark-background text-lg"></i>
                    </div>
                    <span class="text-3xl font-bold heading-font text-neon-blue neon-glow-blue">url<span class="text-neon-pink neon-glow-pink">JAR</span></span>
                </a>
                
                <ul class="hidden lg:flex space-x-10 text-lg">
                    <li><a href="#features" class="text-gray-300 hover:text-neon-green transition-colors font-medium hover:neon-glow-green">Features</a></li>
                    <li><a href="#how-it-works" class="text-gray-300 hover:text-neon-blue transition-colors font-medium hover:neon-glow-blue">Workflow</a></li>
                    <li><a href="#why-us" class="text-gray-300 hover:text-neon-purple transition-colors font-medium hover:neon-glow-purple">Why urlJAR?</a></li>
                    <li><a href="#faq" class="text-gray-300 hover:text-neon-orange transition-colors font-medium hover:neon-glow-orange">FAQ</a></li>
                </ul>
                
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="login.php" class="text-gray-300 hover:text-neon-blue transition-colors font-semibold hover:neon-glow-blue">Login now</a>
                    <a href="#signup" class="neon-btn bg-neon-pink text-dark-background px-6 py-2 rounded-lg font-extrabold text-lg border-neon-pink hover-lift">
                        Get started
                    </a>
                </div>
                
                <button id="mobile-menu-btn" class="lg:hidden text-neon-pink text-2xl p-2 rounded-lg hover:bg-white/10 transition-colors focus:outline-none neon-glow-pink">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <section id="hero" class="pt-32 pb-20 md:pt-48 md:pb-28 relative overflow-hidden">
        <div class="absolute inset-0 zigzag"></div>
        <div class="container mx-auto px-6 flex flex-col md:flex-row items-center relative z-10">
            <div class="md:w-1/2 mb-12 md:mb-0 stagger-in" style="transition-delay: 0.1s;">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight bubble-font">
                    <span class="text-neon-pink neon-glow-pink">ORGANIZE</span> <span class="text-neon-blue neon-glow-blue">YOUR</span> 
                    <span class="block text-neon-green neon-glow-green">BOOKMARKS IN STYLE</span>
                </h1>
                <p class="text-lg md:text-xl mb-8 text-gray-300 max-w-xl">
                    Save, organize, and access your URLs with the most visually stunning bookmark manager built for the modern creator. No more cluttered browser bars!
                </p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="#signup" class="neon-btn bg-neon-green text-black font-extrabold py-4 px-8 rounded-xl text-center border-neon-green pulse-once hover-lift">
                        START FREE NOW ⚡️
                    </a>
                    <a href="#features" class="neon-btn border-2 border-neon-blue text-neon-blue font-extrabold py-4 px-8 rounded-xl text-center hover-lift">
                        LEARN MORE
                    </a>
                </div>
                <div class="mt-8 flex items-center space-x-4 text-gray-400">
                    <div class="flex items-center">
                        <i class="fas fa-star text-neon-orange"></i>
                        <i class="fas fa-star text-neon-orange"></i>
                        <i class="fas fa-star text-neon-orange"></i>
                        <i class="fas fa-star text-neon-orange"></i>
                        <i class="fas fa-star-half-alt text-neon-orange"></i>
                    </div>
                    <span class="text-sm md:text-base">Rated 4.8/5 by thousands of digital natives</span>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center stagger-in" style="transition-delay: 0.3s;">
                <div class="relative scale-90 md:scale-100">
                    <div class="jar-animation">
                        <div class="w-64 md:w-80 h-64 md:h-80 bg-black/70 rounded-2xl flex flex-col items-center justify-center p-6 neon-border border-neon-purple relative overflow-hidden">
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon-purple/20 rounded-full blur-xl"></div>
                            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-neon-pink/20 rounded-full blur-xl"></div>
                            
                            <div class="w-16 md:w-20 h-16 md:h-20 bg-neon-green rounded-2xl flex items-center justify-center mb-4 neon-border border-neon-green rotate-3 hover-lift">
                                <i class="fas fa-jar text-dark-background text-2xl md:text-3xl"></i>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold mb-2 text-neon-green neon-glow-green bubble-font">DESIGN RESOURCES</h3>
                            <p class="text-center text-gray-300 mb-4 text-sm md:text-base">12 links • Shared with 3 people</p>
                            <div class="w-full bg-gradient-to-r from-neon-pink to-neon-orange h-1 rounded-full mb-4"></div>
                            <div class="flex flex-wrap justify-center gap-2">
                                <span class="bg-neon-blue/30 text-neon-blue text-xs px-3 py-1 rounded-full border border-neon-blue bubble-font">UI KITS</span>
                                <span class="bg-neon-pink/30 text-neon-pink text-xs px-3 py-1 rounded-full border border-neon-pink bubble-font">ICONS</span>
                                <span class="bg-neon-green/30 text-neon-green text-xs px-3 py-1 rounded-full border border-neon-green bubble-font">FONTS</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-neon-orange rounded-xl transform rotate-12 flex items-center justify-center shadow-lg neon-border border-neon-orange hover-lift">
                        <i class="fas fa-qrcode text-dark-background text-xl md:text-2xl"></i>
                    </div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-neon-blue rounded-xl transform -rotate-6 flex items-center justify-center shadow-lg neon-border border-neon-blue hover-lift">
                        <i class="fas fa-mobile-alt text-white text-lg md:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

<section id="features" class="py-20 relative">
    <div class="absolute inset-0 zigzag"></div>
    <div class="absolute top-10 right-10 w-64 h-64 bg-neon-blue/5 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-16 stagger-in" style="transition-delay: 0.1s;">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 neon-glow-blue bubble-font">Amazing Features for Better Bookmarking</h2>
            <p class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">From simple organization to advanced sharing, urlJAR has everything you need to manage your digital resources efficiently.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="neon-card p-6 border-neon-green stagger-in" style="transition-delay: 0.2s;">
                <div class="w-16 h-16 bg-neon-green rounded-xl flex items-center justify-center mb-4 neon-border border-neon-green hover-lift">
                    <i class="fas fa-jar text-black text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2 neon-glow-green bubble-font">Organize in Jars</h3>
                <p class="text-gray-300 mb-4">Group related bookmarks into visual containers with custom colors and icons for easy identification.</p>
                <a href="#features" class="text-neon-green font-bold flex items-center group">Learn more <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i></a>
            </div>
            
            <div class="neon-card p-6 border-neon-blue stagger-in" style="transition-delay: 0.3s;">
                <div class="w-16 h-16 bg-neon-blue rounded-xl flex items-center justify-center mb-4 neon-border border-neon-blue hover-lift">
                    <i class="fas fa-bolt text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2 neon-glow-blue bubble-font">Lightning Fast Search</h3>
                <p class="text-gray-300 mb-4">Find any bookmark instantly with our powerful search that scans titles, descriptions, and tags.</p>
                <a href="#features" class="text-neon-blue font-bold flex items-center group">Learn more <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i></a>
            </div>
            
            <div class="neon-card p-6 border-neon-pink stagger-in" style="transition-delay: 0.4s;">
                <div class="w-16 h-16 bg-neon-pink rounded-xl flex items-center justify-center mb-4 neon-border border-neon-pink hover-lift">
                    <i class="fas fa-wifi text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2 neon-glow-pink bubble-font">Offline Access</h3>
                <p class="text-gray-300 mb-4">Access your bookmarks even without an internet connection. Changes sync automatically when you're back online.</p>
                <a href="#features" class="text-neon-pink font-bold flex items-center group">Learn more <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i></a>
            </div>
            
            <div class="neon-card p-6 border-neon-orange stagger-in" style="transition-delay: 0.5s;">
                <div class="w-16 h-16 bg-neon-orange rounded-xl flex items-center justify-center mb-4 neon-border border-neon-orange hover-lift">
                    <i class="fas fa-share-alt text-black text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2 neon-glow-orange bubble-font">Easy Sharing</h3>
                <p class="text-gray-300 mb-4">Share collections via QR codes, short links, or directly with other urlJAR users for collaboration.</p>
                <a href="#features" class="text-neon-orange font-bold flex items-center group">Learn more <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i></a>
            </div>
            
    <div class="neon-card p-6 border-icon-red stagger-in" style="transition-delay: 0.7s;">
    <div class="w-16 h-16 bg-icon-red rounded-xl flex items-center justify-center mb-4 neon-border border-icon-red hover-lift">
        <i class="fas fa-chart-bar text-dark-background text-2xl"></i>
    </div>
    <h3 class="text-2xl font-bold mb-2 neon-glow-red bubble-font">Visual Analytics</h3>
    <p class="text-gray-300 mb-4">Gain insights into your bookmarking habits with beautiful charts showing your most used tags and jars.</p>
    <a href="#features" class="text-icon-red font-bold flex items-center group">Learn more <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i></a>
</div>
    <div class="neon-card p-6 border-icon-indigo stagger-in" style="transition-delay: 0.7s;">
    <div class="w-16 h-16 bg-icon-indigo rounded-xl flex items-center justify-center mb-4 neon-border border-icon-indigo hover-lift">
        <i class="fas fa-file-import text-white text-2xl"></i>
    </div>
    <h3 class="text-2xl font-bold mb-2 neon-glow-indigo bubble-font">Import & Export</h3>
    <p class="text-gray-300 mb-4">Easily migrate your existing bookmarks from browsers or other services with our import tools.</p>
    <a href="#features" class="text-icon-indigo font-bold flex items-center group">Learn more <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i></a>
</div>
        </div>
    </div>
</section>

    <section id="why-us" class="py-20 relative">
        <div class="absolute inset-0 zigzag"></div>
        <div class="absolute top-1/3 left-1/4 w-80 h-80 bg-neon-purple/5 rounded-full blur-3xl"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 stagger-in" style="transition-delay: 0.1s;">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 neon-glow-orange bubble-font">Why Choose urlJAR?</h2>
                <p class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">We're not just another bookmark manager. Here's what makes us different.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div class="flex items-start space-x-4 stagger-in" style="transition-delay: 0.2s;">
                        <div class="w-12 h-12 bg-neon-pink rounded-lg flex items-center justify-center neon-border border-neon-pink flex-shrink-0">
                            <i class="fas fa-palette text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 neon-glow-pink bubble-font">Visually Stunning</h3>
                            <p class="text-gray-300">Our neon aesthetic and intuitive design make organizing bookmarks a delightful experience.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 stagger-in" style="transition-delay: 0.3s;">
                        <div class="w-12 h-12 bg-neon-blue rounded-lg flex items-center justify-center neon-border border-neon-blue flex-shrink-0">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 neon-glow-blue bubble-font">Built for Collaboration</h3>
                            <p class="text-gray-300">Share jars with team members or friends and work together on collections of resources.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 stagger-in" style="transition-delay: 0.4s;">
                        <div class="w-12 h-12 bg-neon-green rounded-lg flex items-center justify-center neon-border border-neon-green flex-shrink-0">
                            <i class="fas fa-mobile-alt text-black text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 neon-glow-green bubble-font">Truly Cross-Platform</h3>
                            <p class="text-gray-300">Access your bookmarks on any device with our responsive design and dedicated mobile apps.</p>
                        </div>
                    </div>
                </div>
                
                <div class="relative neon-card p-6 border-neon-purple hover-lift stagger-in" style="transition-delay: 0.5s;">
                    <div class="absolute inset-0 cyber-grid"></div>
                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <div class="w-16 h-16 bg-neon-purple rounded-xl flex items-center justify-center neon-border border-neon-purple mr-4">
                                <i class="fas fa-rocket text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold neon-glow-purple bubble-font">The Future of Bookmarking</h3>
                                <p class="text-gray-300">Join the revolution</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-300">Visual Organization</span>
                                <div class="flex text-neon-orange">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-gray-300">Collaboration Features</span>
                                <div class="flex text-neon-orange">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-gray-300">Cross-Platform Sync</span>
                                <div class="flex text-neon-orange">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-gray-300">User Experience</span>
                                <div class="flex text-neon-orange">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <p class="text-center text-gray-300 italic">"urlJAR transformed how I organize my research. It's beautiful and incredibly functional."</p>
                            <p class="text-center text-neon-blue mt-2">- Sarah, UX Designer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section id="try-now" class="py-20 relative">
        <div class="absolute inset-0 zigzag"></div>
        <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-neon-blue/5 rounded-full blur-3xl"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 stagger-in" style="transition-delay: 0.1s;">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 neon-glow-orange bubble-font">Try urlJAR Now!</h2>
                <p class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">Experience the power of organized bookmarking with our interactive demo - no signup required.</p>
            </div>
            
            <div class="max-w-4xl mx-auto bg-gradient-to-r from-neon-blue/50 to-neon-purple/50 rounded-2xl p-8 md:p-12 relative overflow-hidden neon-border border-neon-blue hover-lift stagger-in" style="transition-delay: 0.2s;">
                <div class="absolute inset-0 bg-black/40"></div>
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row items-center">
                        <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                            <h3 class="text-2xl md:text-3xl font-bold mb-4 bubble-font text-white neon-glow-blue">Create Your First Jar</h3>
                            <p class="mb-6 text-gray-200">Try our interactive demo to see how easy it is to organize your bookmarks with urlJAR.</p>
                            <div class="flex space-x-4">
                                <div class="w-12 h-12 bg-neon-green rounded-xl flex items-center justify-center neon-border border-neon-green hover-lift cursor-pointer">
                                    <i class="fas fa-plus text-black"></i>
                                </div>
                                <div class="w-12 h-12 bg-neon-pink rounded-xl flex items-center justify-center neon-border border-neon-pink hover-lift cursor-pointer">
                                    <i class="fas fa-tag text-white"></i>
                                </div>
                                <div class="w-12 h-12 bg-neon-orange rounded-xl flex items-center justify-center neon-border border-neon-orange hover-lift cursor-pointer">
                                    <i class="fas fa-share text-black"></i>
                                </div>
                            </div>
                        </div>
                        <div class="md:w-1/2 bg-black/50 rounded-xl p-6 neon-border border-neon-green">
                            <div class="mb-4">
                                <label class="block text-neon-green mb-2 bubble-font neon-glow-green">Jar Name</label>
                                <input type="text" class="form-input w-full px-4 py-2 border-neon-green rounded-lg text-white" placeholder="My Awesome Jar">
                            </div>
                            <div class="mb-4">
                                <label class="block text-neon-blue mb-2 bubble-font neon-glow-blue">Color Theme</label>
                                <div class="flex space-x-2">
                                    <div class="w-8 h-8 bg-neon-pink rounded-full cursor-pointer hover-lift border-2 border-transparent hover:border-white"></div>
                                    <div class="w-8 h-8 bg-neon-blue rounded-full cursor-pointer hover-lift border-2 border-transparent hover:border-white"></div>
                                    <div class="w-8 h-8 bg-neon-green rounded-full cursor-pointer hover-lift border-2 border-transparent hover:border-white"></div>
                                    <div class="w-8 h-8 bg-icon-red rounded-full cursor-pointer hover-lift border-2 border-transparent hover:border-white"></div>
                                    <div class="w-8 h-8 bg-neon-orange rounded-full cursor-pointer hover-lift border-2 border-transparent hover:border-white"></div>
                                </div>
                            </div>
                            <button class="w-full neon-btn bg-neon-green text-black font-bold py-3 rounded-lg hover-lift border-neon-green" id="demo-btn">Create Demo Jar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



         <!-- FAQ SECTION -->
        <!-- FAQ SECTION -->
    <section id="faq" class="py-20 md:py-32 relative">
        <div class="container mx-auto px-4 md:px-8 relative z-10 max-w-7xl">
            <header class="text-center mb-16 stagger-in" data-delay="0.1">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-neon-blue bubble-font neon-glow-blue">Frequently Asked Questions</h2>
                <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto">Find answers to common questions about urlJAR.</p>
            </header>
            
            <div class="max-w-4xl mx-auto space-y-4">
                
                <!-- FAQ ITEM 1: Free Tier -->
                <div class="neon-card p-4 md:p-6 border-2 border-neon-blue cursor-pointer faq-item hover-lift stagger-in" data-delay="0.2" tabindex="0" role="button" aria-expanded="false" aria-controls="faq-answer-1">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg md:text-xl font-bold text-neon-blue bubble-font">Is urlJAR completely free?</h3>
                        <i class="fas fa-chevron-down text-neon-blue transition-transform"></i>
                    </div>
                    <p id="faq-answer-1" class="text-gray-300 faq-answer collapsed mt-4">Yes! urlJAR offers a generous free tier that includes all core features like unlimited link storage, multiple Jars, and full access to our powerful tagging system. We plan to introduce optional Pro features (like advanced analytics and team collaboration) in the future, but the ability to organize your digital life will always remain free.</p>
                </div>
                
                <!-- FAQ ITEM 2: Importing Bookmarks -->
                <div class="neon-card p-4 md:p-6 border-2 border-neon-green cursor-pointer faq-item hover-lift stagger-in" data-delay="0.3" tabindex="0" role="button" aria-expanded="false" aria-controls="faq-answer-2">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg md:text-xl font-bold text-neon-green bubble-font">Can I import my existing bookmarks?</h3>
                        <i class="fas fa-chevron-down text-neon-green transition-transform"></i>
                    </div>
                    <p id="faq-answer-2" class="text-gray-300 faq-answer collapsed mt-4">Absolutely! We know migrating is a pain. urlJAR provides easy import tools for standard HTML export files from all major browsers (Chrome, Firefox, Safari) and popular bookmarking services. You can be jarring your entire collection in minutes!</p>
                </div>
                
                <!-- FAQ ITEM 3: Data Security (FIXED ID) -->
                <div class="neon-card p-4 md:p-6 border-2 border-neon-pink cursor-pointer faq-item hover-lift stagger-in" data-delay="0.4" tabindex="0" role="button" aria-expanded="false" aria-controls="faq-answer-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg md:text-xl font-bold text-neon-pink bubble-font">Is my data secure with urlJAR?</h3>
                        <i class="fas fa-chevron-down text-neon-pink transition-transform"></i>
                    </div>
                    <p id="faq-answer-3" class="text-gray-300 faq-answer collapsed mt-4">Security is non-negotiable. All your links and personal data are protected with industry-leading encryption. We use SSL/TLS for data in transit and AES-256 encryption for data at rest. We adhere to modern data protection standards.</p>
                </div>

                <!-- FAQ ITEM 4: Multi-Device Sync (FIXED ID) -->
                <div class="neon-card p-4 md:p-6 border-2 border-neon-purple cursor-pointer faq-item hover-lift stagger-in" data-delay="0.5" tabindex="0" role="button" aria-expanded="false" aria-controls="faq-answer-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg md:text-xl font-bold text-neon-purple bubble-font">Can I use urlJAR on multiple devices?</h3>
                        <i class="fas fa-chevron-down text-neon-purple transition-transform"></i>
                    </div>
                    <p id="faq-answer-4" class="text-gray-300 faq-answer collapsed mt-4">Absolutely! urlJAR is built for seamless, real-time syncing across all your platforms. Whether you're using our mobile web app, desktop browser, or the full web interface, your Jars are always up-to-date and accessible.</p>
                </div>
            </div>
        </div>
    </section>












<!-- SIGNUP SECTION (Responsive, Tailwind-ready) -->
<section id="signup" class="py-12 md:py-20 lg:py-28 relative overflow-hidden">
  <div class="absolute inset-0 bg-gradient-to-br from-neon-purple/30 to-neon-pink/30"></div>
  <div class="absolute inset-0 bg-black/60"></div>

  <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="max-w-3xl mx-auto text-center">
      <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 text-white bubble-font">
        DITCH THE FOLDERS. GET JARRING.
      </h2>
      <p class="text-sm sm:text-base md:text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
        Join thousands of digital creatives who are finally organizing their links in a way that sparks joy.
      </p>
    </div>

    <!-- Signup Card -->
    <div class="max-w-md mx-auto neon-card p-5 sm:p-6 md:p-8 border-neon-green hover-lift rounded-2xl">
      <h3 class="text-xl sm:text-2xl font-bold mb-4 text-neon-green neon-glow-green bubble-font text-center">
        CREATE YOUR FREE ACCOUNT
      </h3>

      <!-- SERVER: process_signup.php handles server-side validation, sessions & redirects -->
      <form id="signup-form" method="POST" action="api/process_signup.php" class="space-y-4" novalidate>
        <div>
          <label for="signup-name" class="sr-only">Full name</label>
          <input type="text" id="signup-name" name="name" placeholder="Full Name"
            class="form-input w-full px-4 py-3 bg-black/50 border border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-neon-green placeholder-gray-400 text-white"
            required autocomplete="name" inputmode="text">
        </div>

        <div>
          <label for="signup-email" class="sr-only">Email address</label>
          <input type="email" id="signup-email" name="email" placeholder="Email Address"
            class="form-input w-full px-4 py-3 bg-black/50 border border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-neon-blue placeholder-gray-400 text-white"
            required autocomplete="email" inputmode="email">
        </div>

        <div class="relative">
          <label for="signup-password" class="sr-only">Password</label>
          <input type="password" id="signup-password" name="password"
            placeholder="Password (Min 8 Chars, Mixed Case, Number, Special Char)"
            class="form-input w-full px-4 py-3 pr-12 bg-black/50 border border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-neon-pink placeholder-gray-400 text-white"
            required minlength="8" autocomplete="new-password">
       <button type="button" id="toggle-password" aria-label="Toggle password visibility"
    class="absolute inset-y-0 right-0 flex items-center pr-3 focus:outline-none text-gray-400 hover:text-neon-pink transition-colors">
  
  <!-- Eye open (visible) -->
  <i id="eye-open-icon" class="fa-regular fa-eye text-lg transition-opacity"></i>
  
  <!-- Eye closed (hidden) -->
  <i id="eye-closed-icon" class="fa-regular fa-eye-slash text-lg opacity-0 absolute top-1/2 -translate-y-1/2 transition-opacity"></i>
</button>

        </div>

        <!-- Inline error area (Green for success, Pink for error) -->
                    <div id="error-message" class="text-sm p-3 rounded-xl hidden text-left" role="alert" aria-live="polite"></div>

                    <button type="submit" id="signup-button" class="w-full neon-btn bg-neon-pink text-dark-background font-extrabold py-3 px-5 rounded-xl hover-lift border-neon-pink">
                        SIGN UP NOW 🚀
                    </button>
                </form>

      <p class="text-gray-400 text-xs sm:text-sm mt-4">By signing up, you agree to our
        <a href="#" class="text-neon-blue hover:underline">Terms of Service</a> &amp;
        <a href="#" class="text-neon-pink hover:underline">Privacy Policy</a>.
      </p>

         <p class="text-gray-400 text-xs sm:text-sm mt-4 text-center">
                    Already have an account?
                    <a href="login.php" class="text-neon-pink hover:underline font-semibold">Login</a>.
                </p>
    </div>
  </div>
</section>























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
                        <li><a href="#features" class="hover:text-neon-green transition-colors">Features</a></li>
                        <li><a href="#how-it-works" class="hover:text-neon-green transition-colors">Workflow</a></li>
                        <li><a href="#try-now" class="hover:text-neon-green transition-colors">Demo</a></li>
                        <li><a href="#faq" class="hover:text-neon-green transition-colors">FAQ</a></li>
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
/**
 * JARRING MASTER SCRIPT (Final, Optimized, Pure JS)
 * Includes: Form Validation, AJAX Mock, Mobile Menu, Scroll Observer, and Pure JS Accordion.
 */

document.addEventListener('DOMContentLoaded', () => {
    // =========================================================
    // 1. CORE DOM ELEMENTS & CONFIG
    // =========================================================
    const form = document.getElementById('signup-form');
    const nameInput = document.getElementById('signup-name');
    const emailInput = document.getElementById('signup-email');
    const passwordInput = document.getElementById('signup-password');
    const togglePasswordButton = document.getElementById('toggle-password');
    const eyeOpenIcon = document.getElementById('eye-open-icon');
    const eyeClosedIcon = document.getElementById('eye-closed-icon');
    const errorMessageDiv = document.getElementById('error-message');
    const signupButton = document.getElementById('signup-button');
    const originalButtonText = 'SIGN UP NOW 🚀';
    
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const closeMenuBtn = document.getElementById('close-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    const faqItems = document.querySelectorAll('.faq-item'); 
    const staggerInElements = document.querySelectorAll('.stagger-in');


    // =========================================================
    // 2. UTILITY & FEEDBACK FUNCTIONS (Corrected for Green Success)
    // =========================================================

    /**
     * Shows a message in the designated area with color coding.
     * @param {string} message - The message to display.
     * @param {boolean} isSuccess - If true, displays a success (green) style.
     */
    const displayMessage = (message, isSuccess = false) => {
        errorMessageDiv.textContent = message;
        
        // 1. Reset classes
        errorMessageDiv.classList.remove(
            'hidden', 'animate-shake', 
            'bg-red-900/30', 'border-red-500/40', 'text-neon-pink', 'neon-glow-pink',
            'bg-neon-green/30', 'border-neon-green/70', 'text-neon-green', 'neon-glow-green'
        );
        
        // 2. Apply success or error styles
        if (isSuccess) {
            errorMessageDiv.classList.add(
                'bg-neon-green/30', 
                'border-neon-green/70', 
                'text-neon-green', 
                'neon-glow-green'
            );
        } else {
            errorMessageDiv.classList.add(
                'bg-red-900/30', 
                'border-red-500/40', 
                'text-neon-pink', 
                'neon-glow-pink'
            );
        }
        
        // 3. Show message and trigger animation
        errorMessageDiv.classList.remove('hidden');
        errorMessageDiv.classList.add('animate-shake');
        setTimeout(() => errorMessageDiv.classList.remove('animate-shake'), 500);
    };

    const clearError = () => {
        errorMessageDiv.classList.add('hidden');
        errorMessageDiv.className = 'text-sm p-3 rounded-xl hidden text-left';
    };

    const setButtonLoading = () => {
        signupButton.disabled = true;
        signupButton.innerHTML = `
            <span class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg> Signup
            </span>`;
    };

    const resetButton = () => {
        signupButton.disabled = false;
        signupButton.textContent = originalButtonText;
    };

   // --------------------------
    // Handle form submission
    // --------------------------
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        clearMessage();
        setButtonLoading();

        const payload = {
            name: form.name.value,
            email: form.email.value,
            password: form.password.value
        };

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayMessage(data.message || 'Signup successful!', true);
                setTimeout(() => location.reload(), 1500); // Reload page on success
            } else {
                displayMessage(data.message || 'Signup failed. Try again.', false);
                resetButton();
            }
        })
        .catch(err => {
            displayMessage('Network error. Check your connection.', false);
            console.error(err);
            resetButton();
        });
    });

    const showSuccessModal = (redirectUrl) => {
        displayMessage("🎉 Success! Your Jarring account has been created. Redirecting to login...", true);
        
        setTimeout(() => {
            window.location.href = redirectUrl || '../login.php'; 
        }, 1500); 
    };

    const showFailureModal = (message) => {
        displayMessage(message, false);
        resetButton();
    };

    // --- 2. CLIENT-SIDE VALIDATION LOGIC ---

    const validateForm = () => {
        clearError();
        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Validation logic remains the same (Name, Email, Password Regex)
        // ... [omitted for brevity, but the logic from previous steps is here]
        
        if (name.length < 2) {
            displayError("Please enter your full name (at least 2 characters).");
            nameInput.focus();
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            displayError("Please enter a valid email address.");
            emailInput.focus();
            return false;
        }
        const passwordRegex = new RegExp(
            "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,})"
        );
        if (!passwordRegex.test(password)) {
            displayError("Password must be at least 8 characters long and include: an uppercase letter, a lowercase letter, a number, and a special character (!@#$%^&*).");
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


    // --- 3. AJAX FORM SUBMISSION HANDLER ---
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!validateForm()) {
            return;
        }

        setButtonLoading();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ 
                    message: `Server HTTP Error: ${response.status} ${response.statusText}` 
                }));
                throw new Error(errorData.message || `Signup failed with status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                showSuccessModal();
            } else {
                showFailureModal(result.message || "An unknown error occurred during signup.");
            }

        } catch (error) {
            console.error('Signup Error:', error);
            showFailureModal(error.message || "A network error occurred. Please check your connection.");
        } 
    });


    // =========================================================
    // 4. MOBILE MENU LOGIC
    // =========================================================
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
    // 5. FAQ ACCORDION LOGIC (Pure JS - CSS Transitions)
    // =========================================================

    faqItems.forEach(item => {
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.fa-chevron-down'); 

        // Initial setup
        if (answer) {
             answer.classList.add('collapsed');
             answer.style.maxHeight = '0';
        }

        item.addEventListener('click', () => {
            if (!answer) return;

            const isCollapsed = answer.classList.contains('collapsed');

            // 1. Close all other items (accordion behavior)
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    const otherIcon = otherItem.querySelector('.fa-chevron-down');

                    if (otherAnswer && !otherAnswer.classList.contains('collapsed')) {
                        otherAnswer.style.maxHeight = '0';
                        otherAnswer.classList.add('collapsed');
                        if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                        otherItem.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            // 2. Toggle current item
            if (isCollapsed) {
                // OPEN
                answer.classList.remove('collapsed');
                answer.style.maxHeight = answer.scrollHeight + 'px'; 
                if (icon) icon.style.transform = 'rotate(180deg)';
                item.setAttribute('aria-expanded', 'true');
            } else {
                // CLOSE
                answer.style.maxHeight = '0';
                answer.classList.add('collapsed');
                if (icon) icon.style.transform = 'rotate(0deg)';
                item.setAttribute('aria-expanded', 'false');
            }
        });
    });


    // =========================================================
    // 6. INTERSECTION OBSERVER (Fade-in-on-Scroll)
    // =========================================================
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

        // Initial check for elements already in view on page load
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