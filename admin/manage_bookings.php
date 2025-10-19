<?php
include_once '../api/apimethods.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$api = new ApiMethods();

// Handle update payment status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    global $conn;
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $message = 'Booking updated';
}

// Handle delete booking
if (isset($_GET['delete_booking'])) {
    $id = intval($_GET['delete_booking']);
    global $conn;
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if (!isset($_GET['fragment'])) {
            header('Location: manage_bookings.php');
            exit();
        }
    } else {
        $error = 'Error deleting booking: ' . $conn->error;
    }
}

// Fetch bookings
global $conn;
$result = $conn->query("SELECT b.*, u.name as user_name, e.title as event_title FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN events e ON b.event_id = e.id ORDER BY b.booking_date DESC");
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>

<div id="fragment-manage-bookings" class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Manage Bookings</h2>

    <?php if (!empty($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">ID</th>
                <th class="py-2">User</th>
                <th class="py-2">Event</th>
                <th class="py-2">Quantity</th>
                <th class="py-2">Amount</th>
                <th class="py-2">Status</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr class="border-t">
                    <td class="py-2"><?php echo $b['id']; ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($b['user_name']); ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($b['event_title']); ?></td>
                    <td class="py-2"><?php echo $b['quantity']; ?></td>
                    <td class="py-2"><?php echo number_format($b['total_amount'],2); ?></td>
                    <td class="py-2"><?php echo $b['payment_status']; ?></td>
                    <td class="py-2">
                        <form method="post" data-ajax="true" style="display:inline-block">
                            <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php if($b['payment_status']==='pending') echo 'selected'; ?>>pending</option>
                                <option value="completed" <?php if($b['payment_status']==='completed') echo 'selected'; ?>>completed</option>
                                <option value="failed" <?php if($b['payment_status']==='failed') echo 'selected'; ?>>failed</option>
                            </select>
                            <button name="update_status" class="ml-2 bg-blue-600 text-white px-2 py-1 rounded">Update</button>
                        </form>
                        <a href="manage_bookings.php?delete_booking=<?php echo $b['id']; ?>&fragment=1" class="text-red-600 ml-2 fragment-link" data-confirm="Delete booking #<?php echo $b['id']; ?>?">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php
// end fragment
?>