<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'api/apimethods.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$api = new ApiMethods();
$result = $api->getUserBookings();
$bookings = $result['status'] === 'success' ? $result['bookings'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - ImpactPass</title>
    <!-- Add Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">My Bookings</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById('successMessage').style.display = 'none';
                }, 5000);
            </script>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="bg-gray-100 rounded-lg p-8 text-center">
                <p class="text-gray-600 mb-4">You don't have any bookings yet.</p>
                <a href="events.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                    Browse Events
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booked On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td class="py-4 px-4"><?php echo htmlspecialchars($booking['title']); ?></td>
                                <td class="py-4 px-4"><?php echo date('M j, Y', strtotime($booking['event_date'])); ?></td>
                                <td class="py-4 px-4"><?php echo htmlspecialchars($booking['location']); ?></td>
                                <td class="py-4 px-4"><?php echo $booking['quantity']; ?></td>
                                <td class="py-4 px-4">₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td class="py-4 px-4">
                                    <?php if ($booking['payment_status'] === 'completed'): ?>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Confirmed</span>
                                    <?php elseif ($booking['payment_status'] === 'pending'): ?>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Pending</span>
                                    <?php else: ?>
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4"><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>