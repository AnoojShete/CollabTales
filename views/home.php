<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$pageTitle = "Discover Stories";
include '../includes/header.php';

// Get popular stories
$popularStories = $pdo->query("
    SELECT s.*, u.username, COUNT(v.id) as views, COUNT(DISTINCT l.id) as likes
    FROM stories s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN views v ON s.id = v.story_id
    LEFT JOIN chapters c ON s.id = c.story_id
    LEFT JOIN likes l ON c.id = l.chapter_id
    GROUP BY s.id
    ORDER BY views DESC, likes DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get new stories
$newStories = $pdo->query("
    SELECT s.*, u.username
    FROM stories s
    JOIN users u ON s.user_id = u.id
    ORDER BY s.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get recently updated stories
$updatedStories = $pdo->query("
    SELECT s.*, u.username, MAX(c.updated_at) as last_updated
    FROM stories s
    JOIN users u ON s.user_id = u.id
    JOIN chapters c ON s.id = c.story_id
    GROUP BY s.id
    ORDER BY last_updated DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="stories-section">
    <h2>Popular Stories</h2>
    <div class="stories-grid">
        <?php foreach ($popularStories as $story): ?>
            <div class="story-card">
                <img src="/uploads/cover_images/<?php echo htmlspecialchars($story['cover_image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <h3><a href="/views/story.php?id=<?php echo $story['id']; ?>"><?php echo htmlspecialchars($story['title']); ?></a></h3>
                <p>by <?php echo htmlspecialchars($story['username']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="stories-section">
    <h2>New Stories</h2>
    <div class="stories-grid">
        <?php foreach ($newStories as $story): ?>
            <div class="story-card">
                <img src="/uploads/cover_images/<?php echo htmlspecialchars($story['cover_image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <h3><a href="/views/story.php?id=<?php echo $story['id']; ?>"><?php echo htmlspecialchars($story['title']); ?></a></h3>
                <p>by <?php echo htmlspecialchars($story['username']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="stories-section">
    <h2>Recently Updated</h2>
    <div class="stories-grid">
        <?php foreach ($updatedStories as $story): ?>
            <div class="story-card">
                <img src="/uploads/cover_images/<?php echo htmlspecialchars($story['cover_image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <h3><a href="/views/story.php?id=<?php echo $story['id']; ?>"><?php echo htmlspecialchars($story['title']); ?></a></h3>
                <p>by <?php echo htmlspecialchars($story['username']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>