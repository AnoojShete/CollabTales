<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin($_SESSION['user_id'], $pdo)) {
    header("Location: /");
    exit();
}

function isAdmin($userId, $pdo) {
    // In a real app, you'd have an admin flag in the users table
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && $user['is_admin'];
}

$pageTitle = "Admin Panel";
include '../includes/header.php';

// Get reported content
$reportedStories = $pdo->query("
    SELECT s.*, COUNT(r.id) as report_count
    FROM stories s
    JOIN reports r ON s.id = r.story_id
    GROUP BY s.id
    ORDER BY report_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

$reportedChapters = $pdo->query("
    SELECT c.*, COUNT(r.id) as report_count
    FROM chapters c
    JOIN reports r ON c.id = r.chapter_id
    GROUP BY c.id
    ORDER BY report_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

$reportedUsers = $pdo->query("
    SELECT u.*, COUNT(r.id) as report_count
    FROM users u
    JOIN reports r ON u.id = r.reported_user_id
    GROUP BY u.id
    ORDER BY report_count DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-panel">
    <h1>Admin Panel</h1>
    
    <div class="admin-section">
        <h2>Reported Stories</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Reports</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportedStories as $story): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($story['title']); ?></td>
                        <td><?php echo htmlspecialchars($story['username']); ?></td>
                        <td><?php echo $story['report_count']; ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="reviewContent('story', <?php echo $story['id']; ?>)">Review</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteContent('story', <?php echo $story['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="admin-section">
        <h2>Reported Chapters</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Reports</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportedChapters as $chapter): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($chapter['title']); ?></td>
                        <td><?php echo htmlspecialchars($chapter['username']); ?></td>
                        <td><?php echo $chapter['report_count']; ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="reviewContent('chapter', <?php echo $chapter['id']; ?>)">Review</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteContent('chapter', <?php echo $chapter['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="admin-section">
        <h2>Reported Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Reports</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportedUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['report_count']; ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="reviewUser(<?php echo $user['id']; ?>)">Review</button>
                            <button class="btn btn-sm btn-danger" onclick="banUser(<?php echo $user['id']; ?>)">Ban</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteContent(type, id) {
    if (confirm(`Are you sure you want to delete this ${type}?`)) {
        $.ajax({
            url: `/api/admin/delete_${type}.php`,
            method: 'POST',
            data: { id: id },
            success: function() {
                location.reload();
            }
        });
    }
}

function banUser(userId) {
    if (confirm('Are you sure you want to ban this user?')) {
        $.ajax({
            url: '/api/admin/ban_user.php',
            method: 'POST',
            data: { user_id: userId },
            success: function() {
                location.reload();
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>