<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Debug: log session state for troubleshooting
$navLog = fopen(__DIR__ . "/payment_log.txt", "a");
fwrite($navLog, date('Y-m-d H:i:s') . " - NAV LOAD - SESSION: " . print_r($_SESSION, true) . "\n");
fclose($navLog);

// Process logout
if(isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // If it's desired to kill the session cookie, do this
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Set cache control headers to prevent caching
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Past date
    
    // Redirect to index page with a timestamp parameter to prevent caching
    header('Location: index.php?nocache=' . time());
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Add Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <?php if(isset($_SESSION['success_message'])): ?>
    <div id="successMessage" class="fixed top-20 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('successMessage').style.display = 'none';
        }, 5000);
    </script>
    <?php endif; ?>

    <nav class="bg-white shadow-sm fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                <div class="text-xl font-bold text-blue-600">ImpactPass</div>

                <div class="hidden md:flex space-x-6 justify-center items-center">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600">Home</a>
                    <a href="events.php" class="text-gray-700 hover:text-blue-600">Events</a>
                    <a href="about.php" class="text-gray-700 hover:text-blue-600">About</a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600">Contact</a>
                    
                    <!-- Show login or logout button based on session -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="relative">
                        <button id="profileDropdownBtn" class="flex items-center text-gray-700 hover:text-blue-600">
                            <span class="mr-1"><?php echo $_SESSION['user_name']; ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="my_bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Bookings</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div onclick="window.location.href='login.php'" class="btn1 bg-indigo-600 p-2 pl-4 pr-4 rounded-lg text-white cursor-pointer">
                        Login
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button onclick="toggleMenu()">
                        <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobileMenu" class="hidden md:hidden px-4 pb-4 space-y-2 bg-white shadow">
            <a href="index.php" class="text-gray-700 hover:text-blue-600 block">Home</a>
            <a href="events.php" class="text-gray-700 hover:text-blue-600 block">Events</a>
            <a href="about.php" class="text-gray-700 hover:text-blue-600 block">About</a>
            <a href="contact.php" class="text-gray-700 hover:text-blue-600 block">Contact</a>
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="text-gray-700 hover:text-blue-600 block">Profile</a>
            <a href="my_bookings.php" class="text-gray-700 hover:text-blue-600 block">My Bookings</a>
            <a href="logout.php" class="text-gray-700 hover:text-blue-600 block">Logout</a>
            <?php else: ?>
            <div onclick="window.location.href='login.php'" class="btn1 bg-indigo-600 p-2 pl-4 pr-4 rounded-lg text-white cursor-pointer">
                Login
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        function toggleMenu() {
            const menu = document.getElementById('mobileMenu');
            const icon = document.getElementById('menuIcon');
            menu.classList.toggle('hidden');

            // Toggle icon between menu and X
            if (menu.classList.contains('hidden')) {
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />`;
            } else {
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />`;
            }
        }

        // Profile dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Handle profile dropdown toggle
            const profileBtn = document.getElementById('profileDropdownBtn');
            if (profileBtn) {
                profileBtn.addEventListener('click', function() {
                    document.getElementById('profileDropdown').classList.toggle('hidden');
                });
            }

            // Check if page was loaded after a logout
            if (window.location.search.includes('logout_success=true')) {
                // Force browser to refresh the page properly and clear any cached session state
                window.location.replace(window.location.pathname);
            }
            
            // Session consistency checks are performed by session-checker.php (periodic AJAX)
        });

        // Prevent back-button cache issues after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was loaded from cache (back/forward navigation)
                // Force a hard reload to check session status
                window.location.reload(true);
            }
        });
    </script>
</body>

</html>