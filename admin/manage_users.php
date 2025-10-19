<?php
include_once '../api/apimethods.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    $stmt->execute();
    $message = 'User updated';
}

// Handle delete user
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    // Prevent deleting self
    if ($id == $_SESSION['user_id']) {
        $error = 'Cannot delete yourself';
    } else {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if (!isset($_GET['fragment'])) {
                header('Location: manage_users.php');
                exit();
            }
        } else {
            $error = 'Error deleting user: ' . $conn->error;
        }
    }
}

// Fetch users
global $conn;
$result = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<div id="fragment-manage-users" class="max-w-4xl mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Manage Users</h2>

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
                <th class="py-2">Name</th>
                <th class="py-2">Email</th>
                <th class="py-2">Role</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-t">
                    <td class="py-2"><?php echo $u['id']; ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($u['name']); ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($u['email']); ?></td>
                    <td class="py-2"><?php echo $u['role']; ?></td>
                    <td class="py-2">
                        <form method="post" data-ajax="true" style="display:inline-block">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <select name="role">
                                <option value="user" <?php if($u['role']==='user') echo 'selected'; ?>>user</option>
                                <option value="admin" <?php if($u['role']==='admin') echo 'selected'; ?>>admin</option>
                            </select>
                            <button name="update_role" class="ml-2 bg-blue-600 text-white px-2 py-1 rounded">Update</button>
                        </form>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="manage_users.php?delete_user=<?php echo $u['id']; ?>&fragment=1" class="text-red-600 ml-2 fragment-link" data-confirm="Delete user '<?php echo htmlspecialchars(addslashes($u['name'])); ?>'?">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php
// end fragment
?>