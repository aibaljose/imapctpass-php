<?php
include_once '../api/apimethods.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$api = new ApiMethods();

// Handle add event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_event') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price'] ?? 0.00;

    global $conn;
    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image_url, location, price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssd", $title, $description, $event_date, $image_url, $location, $price);
    if ($stmt->execute()) {
        $message = 'Event added successfully';
    } else {
        $error = 'Error adding event: ' . $conn->error;
    }
}

// Handle delete event
if (isset($_GET['delete_event'])) {
    $id = intval($_GET['delete_event']);
    global $conn;
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // If this was requested as fragment, just continue to render updated fragment
        if (isset($_GET['fragment'])) {
            // continue
        } else {
            header('Location: manage_events.php');
            exit();
        }
    } else {
        $error = 'Error deleting event: ' . $conn->error;
    }
}

$events = $api->getEvents();
?>

<div id="fragment-manage-events" class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Manage Events</h2>

    <?php if (!empty($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="mb-6">
        <form method="post" data-ajax="true" class="grid grid-cols-1 gap-3">
            <input type="hidden" name="action" value="add_event">
            <input name="title" placeholder="Title" class="border p-2" required>
            <textarea name="description" placeholder="Description" class="border p-2"></textarea>
            <input name="event_date" type="datetime-local" class="border p-2" required>
            <input name="image_url" placeholder="Image URL" class="border p-2">
            <input name="location" placeholder="Location" class="border p-2">
            <input name="price" type="number" step="0.01" placeholder="Price" class="border p-2" value="0.00">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Add Event</button>
        </form>
    </div>

    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">ID</th>
                <th class="py-2">Title</th>
                <th class="py-2">Date</th>
                <th class="py-2">Location</th>
                <th class="py-2">Price</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $e): ?>
                <tr class="border-t">
                    <td class="py-2"><?php echo $e['id']; ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($e['title']); ?></td>
                    <td class="py-2"><?php echo $e['event_date']; ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($e['location']); ?></td>
                    <td class="py-2"><?php echo number_format($e['price'],2); ?></td>
                    <td class="py-2">
                        <a href="manage_events.php?delete_event=<?php echo $e['id']; ?>&fragment=1" class="text-red-600 fragment-link" data-confirm="Delete event '<?php echo htmlspecialchars(addslashes($e['title'])); ?>'?"><?php echo 'Delete'; ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// End fragment
?>