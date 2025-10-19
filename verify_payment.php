<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'api/apimethods.php';

// Check if the request is a valid payment verification
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id'])) {
    header('Location: events.php');
    exit;
}

// Check if either payment_id or razorpay_payment_id is present
if (!isset($_POST['razorpay_payment_id']) && !isset($_POST['payment_id'])) {
    $_SESSION['error_message'] = "Payment ID is missing.";
    header('Location: events.php');
    exit;
}

// Set payment ID from either parameter
$_POST['razorpay_payment_id'] = $_POST['razorpay_payment_id'] ?? ($_POST['payment_id'] ?? null);

// Log the payment data for debugging
$logFile = fopen("payment_log.txt", "a");
fwrite($logFile, date('Y-m-d H:i:s') . " - Payment received: " . 
    "Payment ID: " . $_POST['razorpay_payment_id'] . 
    ", Order ID: " . $_POST['razorpay_order_id'] . 
    ", Booking ID: " . $_POST['booking_id'] . "\n");
fclose($logFile);

// Process the payment verification
$api = new ApiMethods();
$result = $api->verifyPayment();

if ($result['status'] === 'success') {
    $_SESSION['success_message'] = $result['message'];
    header('Location: my_bookings.php');
} else {
    $_SESSION['error_message'] = $result['message'];
    header('Location: book_event.php?id=' . $_GET['event_id']);
}
exit;
?>