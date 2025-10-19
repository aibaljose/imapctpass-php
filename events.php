<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Set cache control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Past date
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Events - ImpactPass</title>
    <?php include 'session-checker.php'; ?>
    <!-- Add Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <?php include 'nav.php'; ?>

    <?php
    // Database connection
    include_once 'api/apimethods.php';
    global $conn;
    
    // Add demo events if needed (this should run first)
    $api = new ApiMethods();
    $api->addDemoEvents();
    
    // Get events from apimethods
    $events = $api->getEvents();

    // --- Search & Filters (from GET params) ---
    $q = trim($_GET['q'] ?? '');
    $priceFilter = $_GET['price'] ?? 'all'; // all | free | paid
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $locationFilter = trim($_GET['location'] ?? '');

    // Apply filters to the $events array
    $events = array_filter($events, function($ev) use ($q, $priceFilter, $dateFrom, $dateTo, $locationFilter) {
        // Search query (title or description)
        if ($q !== '') {
            $hay = mb_strtolower($ev['title'] . ' ' . ($ev['description'] ?? ''));
            if (mb_strpos($hay, mb_strtolower($q)) === false) return false;
        }

        // Price filter
        $price = isset($ev['price']) ? floatval($ev['price']) : 0.0;
        if ($priceFilter === 'free' && $price > 0) return false;
        if ($priceFilter === 'paid' && $price <= 0) return false;

        // Location filter (simple substring match)
        if ($locationFilter !== '') {
            if (!isset($ev['location']) || mb_stripos($ev['location'], $locationFilter) === false) return false;
        }

        // Date range filter (compare dates if provided)
        if ($dateFrom !== '') {
            $evDate = strtotime($ev['event_date']);
            $from = strtotime($dateFrom . ' 00:00:00');
            if ($evDate < $from) return false;
        }
        if ($dateTo !== '') {
            $evDate = strtotime($ev['event_date']);
            $to = strtotime($dateTo . ' 23:59:59');
            if ($evDate > $to) return false;
        }

        return true;
    });

    // Reindex array
    $events = array_values($events);
    ?>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-10">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 mt-8">Upcoming Events</h2>
        <!-- Modern Search & Filters -->
        <form method="GET" id="searchForm" class="mb-6">
            <div class="bg-white shadow rounded-lg p-4">
                <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                    <div class="flex items-center flex-1 bg-gray-50 rounded-lg px-3 py-2 border border-gray-200">
                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1016.65 16.65z"></path></svg>
                        <input type="search" name="q" id="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search events, speakers, or descriptions" class="bg-transparent focus:outline-none w-full text-gray-700" />
                    </div>

                    <div class="mt-3 md:mt-0 flex items-center gap-2">
                        <!-- Price pills -->
                        <input type="hidden" name="price" id="priceInput" value="<?php echo htmlspecialchars($priceFilter); ?>">
                        <div class="flex items-center space-x-2">
                            <button type="button" data-price="all" class="price-pill px-3 py-1 rounded-full text-sm <?php echo $priceFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'; ?>">All</button>
                            <button type="button" data-price="free" class="price-pill px-3 py-1 rounded-full text-sm <?php echo $priceFilter === 'free' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'; ?>">Free</button>
                            <button type="button" data-price="paid" class="price-pill px-3 py-1 rounded-full text-sm <?php echo $priceFilter === 'paid' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'; ?>">Paid</button>
                        </div>

                        <button type="button" id="toggleAdvanced" class="ml-3 inline-flex items-center px-3 py-1.5 border border-gray-200 rounded text-sm text-gray-700 hover:bg-gray-50">
                            Advanced
                            <svg id="advIcon" class="ml-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button type="submit" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Search</button>
                        <a href="events.php" class="ml-2 inline-flex items-center px-3 py-1.5 bg-gray-100 rounded text-sm text-gray-700">Reset</a>
                    </div>
                </div>

                <!-- Advanced filters panel -->
                <div id="advancedPanel" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 hidden">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Date from</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Date to</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" class="w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($locationFilter); ?>" placeholder="City or venue" class="w-full border rounded px-3 py-2" />
                    </div>
                </div>
            </div>
        </form>

        <?php if(empty($events)): ?>
            <div class="bg-gray-100 p-8 rounded-lg text-center">
                <p class="text-gray-600">No events found.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($events as $event): ?>
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($event['description']); ?></p>
                        <p class="text-gray-500 text-sm">Date: <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                        <?php if(isset($event['location'])): ?>
                            <p class="text-gray-500 text-sm">Location: <?php echo htmlspecialchars($event['location']); ?></p>
                        <?php endif; ?>
                        <div class="flex justify-between items-center mt-4">
                            <p class="font-bold text-lg">
                                <?php 
                                $displayPrice = 0;
                                if(isset($event['price'])) {
                                    $displayPrice = $event['price'];
                                }
                                
                                if($displayPrice > 0): ?>
                                    ₹<?php echo number_format($displayPrice, 2); ?>
                                <?php else: ?>
                                    <span class="text-green-600">Free</span>
                                <?php endif; ?>
                            </p>
                            <a href="book_event.php?id=<?php echo $event['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-4 rounded-lg">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

   </section>

    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
        <hr class="my-8">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Admin Utilities</h3>
        <div class="flex flex-wrap gap-4">
            <a href="check_columns.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg">
                Check Database Structure
            </a>
            <a href="fix_payment_ids.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg">
                Fix Payment IDs
            </a>
            <a href="check_bookings_table.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg">
                View Bookings Table
            </a>
        </div>
    </section>
    <?php endif; ?>

    <script>
        (function(){
            const pills = document.querySelectorAll('.price-pill');
            const priceInput = document.getElementById('priceInput');
            pills.forEach(p => p.addEventListener('click', function(){
                pills.forEach(x => x.classList.remove('bg-blue-600','text-white'));
                pills.forEach(x => x.classList.add('bg-gray-100','text-gray-700'));
                this.classList.remove('bg-gray-100','text-gray-700');
                this.classList.add('bg-blue-600','text-white');
                priceInput.value = this.getAttribute('data-price');
            }));

            const toggle = document.getElementById('toggleAdvanced');
            const panel = document.getElementById('advancedPanel');
            const advIcon = document.getElementById('advIcon');
            if (toggle) {
                toggle.addEventListener('click', function(){
                    panel.classList.toggle('hidden');
                    advIcon.classList.toggle('transform');
                    advIcon.classList.toggle('rotate-180');
                });
            }
            // Show advanced panel if any advanced filter present
            const dateFrom = '<?php echo addslashes($dateFrom); ?>';
            const dateTo = '<?php echo addslashes($dateTo); ?>';
            const locationFilter = '<?php echo addslashes($locationFilter); ?>';
            if (dateFrom || dateTo || locationFilter) {
                panel.classList.remove('hidden');
            }
        })();
    </script>
</body>
</html>