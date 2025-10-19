<?php
// Initialize session if not already started
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Set cache control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Past date

require_once 'api/apimethods.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Check if event ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: events.php');
    exit;
}

$event_id = intval($_GET['id']);
$api = new ApiMethods();
global $conn;

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: events.php');
    exit;
}

$event = $result->fetch_assoc();

// Process booking
$error = '';
$booking_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['event_id'] = $event_id;
    $booking_result = $api->bookEvent();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Book Event - <?php echo htmlspecialchars($event['title']); ?></title>
    <?php include 'session-checker.php'; ?>

    <!-- Add Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <?php if (isset($event['price']) && $event['price'] > 0): ?>
    <!-- Add Razorpay script for paid events -->
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
    <?php endif; ?>
    
    <!-- Add manifest for service worker support -->
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="max-w-4xl mx-auto px-4 py-32">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/2">
                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-64 object-cover">
                </div>
                <div class="p-6 md:w-1/2">
                    <h1 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($event['title']); ?></h1>
                    
                    <div class="mb-6">
                        <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($event['description']); ?></p>
                        <p class="text-gray-700"><strong>Date:</strong> <?php echo date('F j, Y, g:i A', strtotime($event['event_date'])); ?></p>
                        <p class="text-gray-700"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                        <p class="text-xl font-bold mt-2">
                            <?php if (isset($event['price']) && $event['price'] > 0): ?>
                                Price: ₹<?php echo number_format($event['price'], 2); ?>
                            <?php else: ?>
                                <span class="text-green-600">Free Event</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if (isset($booking_result) && $booking_result['status'] === 'success' && isset($booking_result['razorpay_data'])): ?>
                        <!-- Payment button for successful booking with pending payment -->
                        <div id="payment-details" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <p class="text-green-800 mb-2">Booking created successfully! Please complete the payment.</p>
                        </div>
                        <button id="pay-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg">
                            Pay Now ₹<?php echo number_format($booking_result['total_amount'], 2); ?>
                        </button>
                        
                        <script>
                            // Initialize Razorpay when the script is fully loaded
                            document.addEventListener('DOMContentLoaded', function() {
                                if (typeof Razorpay !== 'undefined') {
                                    initRazorpay();
                                } else {
                                    // If Razorpay isn't loaded yet, wait for it
                                    document.querySelector('script[src*="checkout.razorpay.com"]').onload = initRazorpay;
                                }
                                
                                function initRazorpay() {
                                    // Configure Razorpay
                                    var options = {
                                        "key": "<?php echo htmlspecialchars($booking_result['razorpay_data']['key_id']); ?>", // Use the key from the server
                                        "amount": "<?php echo $booking_result['razorpay_data']['amount']; ?>",
                                        "currency": "<?php echo $booking_result['razorpay_data']['currency']; ?>",
                                        "name": "ImpactPass",
                                        "description": "<?php echo htmlspecialchars($booking_result['event_title']); ?>",
                                        "order_id": "<?php echo $booking_result['razorpay_data']['id']; ?>",
                                        "handler": function (response) {
                                            // Set both payment ID fields for compatibility
                                            var paymentId = response.razorpay_payment_id;
                                            document.getElementById("razorpay_payment_id").value = paymentId;
                                            
                                            // Set both order ID and signature
                                            document.getElementById("razorpay_order_id").value = response.razorpay_order_id;
                                            document.getElementById("razorpay_signature").value = response.razorpay_signature;
                                            
                                            // Submit the form
                                            document.getElementById("payment-form").submit();
                                        },
                                        "prefill": {
                                            "name": "<?php echo htmlspecialchars($_SESSION['user_name']); ?>",
                                            "email": "<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
                                        },
                                        "theme": {
                                            "color": "#2563eb"
                                        },
                                        "modal": {
                                            "ondismiss": function() {
                                                console.log("Payment modal closed");
                                            }
                                        }
                                    };
                                    
                                    // Create Razorpay instance
                                    window.rzp = new Razorpay(options);
                                    
                                    // Attach click handler
                                    document.getElementById('pay-button').onclick = function(e) {
                                        e.preventDefault();
                                        rzp.open();
                                    }
                                }
                            });
                        </script>
                        
                        <!-- Hidden form to process payment verification -->
                        <form id="payment-form" action="verify_payment.php" method="POST" class="hidden">
                            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                            <input type="hidden" name="payment_id" id="razorpay_payment_id"> <!-- Add both field names for compatibility -->
                            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_result['booking_id']; ?>">
                        </form>
                    
                    <?php elseif (isset($booking_result) && $booking_result['status'] === 'success'): ?>
                        <!-- Successful free booking message -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-green-800"><?php echo $booking_result['message']; ?></p>
                            <a href="my_bookings.php" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                                View My Bookings
                            </a>
                        </div>
                    
                    <?php else: ?>
                        <!-- Booking form -->
                        <?php if (isset($booking_result) && $booking_result['status'] === 'error'): ?>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <p class="text-red-800"><?php echo $booking_result['message']; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="space-y-4">
                            <div>
                                <label class="block text-gray-700 mb-2" for="quantity">Number of Tickets</label>
                                <select name="quantity" id="quantity" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                    <?php for ($i = 1; $i <= 2; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <p class="mt-2 text-sm text-gray-500">Maximum of 2 tickets per person</p>
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg">
                                    <?php echo (isset($event['price']) && $event['price'] > 0) ? 'Proceed to Payment' : 'Register for Free'; ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>