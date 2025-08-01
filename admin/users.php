<?php
require_once('check_admin.php');

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id_to_update = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Prevent admin from removing their own admin role
    if ($user_id_to_update == $_COOKIE['userID'] && $new_role !== 'admin') {
        $message = "You cannot remove your own admin role.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id_to_update);
        if ($stmt->execute()) {
            $message = "User role updated successfully.";
        } else {
            $message = "Failed to update user role.";
        }
    }
}

// Fetch all users
$users = $conn->query("SELECT id, username, email, role FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

include('header.php');
?>

<h1 class="h2">User Management</h1>
<p>Here you can manage user roles.</p>

<?php if (isset($message)): ?>
<div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <form action="users.php" method="POST" class="form-inline">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <select name="role" class="form-control form-control-sm mr-2">
                            <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>User</option>
                            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
include('footer.php');
?>
