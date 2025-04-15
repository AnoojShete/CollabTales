<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit();
}

$storyId = $_GET['id'];

// Record view
if (isLoggedIn()) {
    $stmt = $pdo->prepare("INSERT INTO views (story_id, user_id) VALUES (?, ?)");
    $stmt->execute([$storyId, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO views (story_id) VALUES (?)");
    $stmt->execute([$storyId]);
}

// Get story details
$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.profile_pic, 
    COUNT(DISTINCT v.id) as views,
    COUNT(DISTINCT b.id) as bookmarks,
    COUNT(DISTINCT ch.id) as chapters
    FROM stories s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN views v ON s.id = v.story_id
    LEFT JOIN bookmarks b ON s.id = b.story_id
    LEFT JOIN chapters ch ON s.id = ch.story_id
    WHERE s.id = ?
    GROUP BY s.id
");
$stmt->execute([$storyId]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$story) {
    header("Location: /");
    exit();
}

// Get root chapters (those with no parent)
$chapters = $pdo->prepare("
    SELECT c.*, u.username, COUNT(l.id) as likes
    FROM chapters c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN likes l ON c.id = l.chapter_id
    WHERE c.story_id = ? AND c.parent_chapter_id IS NULL
    GROUP BY c.id
    ORDER BY c.order_num, c.created_at
");
$chapters->execute([$storyId]);
$rootChapters = $chapters->fetchAll(PDO::FETCH_ASSOC);

// Check if bookmarked
$isBookmarked = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE story_id = ? AND user_id = ?");
    $stmt->execute([$storyId, $_SESSION['user_id']]);
    $isBookmarked = $stmt->fetch() !== false;
}

$pageTitle = $story['title'];
include '../includes/header.php';
?>

<div class="story-header">
    <img src="/uploads/cover_images/<?php echo htmlspecialchars($story['cover_image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>" class="story-cover">
    <div class="story-meta">
        <h1><?php echo htmlspecialchars($story['title']); ?></h1>
        <p class="author">by <a href="/views/profile.php?id=<?php echo $story['user_id']; ?>"><?php echo htmlspecialchars($story['username']); ?></a></p>
        <p class="description"><?php echo htmlspecialchars($story['description']); ?></p>
        <div class="stats">
            <span><i class="fas fa-eye"></i> <?php echo $story['views']; ?></span>
            <span><i class="fas fa-bookmark"></i> <?php echo $story['bookmarks']; ?></span>
            <span><i class="fas fa-file-alt"></i> <?php echo $story['chapters']; ?></span>
        </div>
        <div class="actions">
            <?php if (isLoggedIn()): ?>
                <button id="bookmarkBtn" class="<?php echo $isBookmarked ? 'bookmarked' : ''; ?>">
                    <?php echo $isBookmarked ? 'Bookmarked' : 'Bookmark'; ?>
                </button>
                <a href="/views/create_chapter.php?story_id=<?php echo $storyId; ?>" class="btn">Continue Story</a>
            <?php endif; ?>
            <?php if (isLoggedIn() && $_SESSION['user_id'] == $story['user_id']): ?>
                <a href="/views/edit_story.php?id=<?php echo $storyId; ?>" class="btn">Edit Story</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="story-content">
    <div class="chapter-list">
        <h2>Chapters</h2>
        <ul>
            <?php foreach ($rootChapters as $chapter): ?>
                <li>
                    <a href="/views/chapter.php?id=<?php echo $chapter['id']; ?>">
                        <?php echo htmlspecialchars($chapter['title']); ?>
                    </a>
                    <span class="chapter-meta">
                        by <?php echo htmlspecialchars($chapter['username']); ?>
                        <i class="fas fa-heart"></i> <?php echo $chapter['likes']; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="chapter-tree" id="chapterTree">
        <h2>Story Tree</h2>
        <svg width="100%" height="400"></svg>
    </div>
</div>

<script>
$(document).ready(function() {
    // Bookmark functionality
    $('#bookmarkBtn').click(function() {
        $.ajax({
            url: '/api/toggle_bookmark.php',
            method: 'POST',
            data: { story_id: <?php echo $storyId; ?> },
            success: function(response) {
                if (response.status === 'bookmarked') {
                    $('#bookmarkBtn').addClass('bookmarked').text('Bookmarked');
                } else {
                    $('#bookmarkBtn').removeClass('bookmarked').text('Bookmark');
                }
            }
        });
    });
    
    // Draw chapter tree with D3.js
    drawChapterTree(<?php echo $storyId; ?>);
});

function drawChapterTree(storyId) {
    $.get('/api/get_chapter_tree.php', { story_id: storyId }, function(data) {
        const treeData = data.tree;
        const margin = {top: 20, right: 90, bottom: 30, left: 90};
        const width = $('.chapter-tree').width() - margin.left - margin.right;
        const height = 400 - margin.top - margin.bottom;
        
        // Clear previous SVG
        d3.select("#chapterTree svg").html("");
        
        const svg = d3.select("#chapterTree svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);
        
        // Create the tree layout
        const treeLayout = d3.tree().size([width, height]);
        
        // Convert the data to a hierarchy
        const root = d3.hierarchy(treeData);
        
        // Assign the data to the tree layout
        const treeDataLayout = treeLayout(root);
        
        // Add links between nodes
        svg.selectAll(".link")
            .data(treeDataLayout.links())
            .enter()
            .append("path")
            .attr("class", "link")
            .attr("d", d3.linkVertical()
                .x(d => d.x)
                .y(d => d.y));
        
        // Add each node
        const node = svg.selectAll(".node")
            .data(treeDataLayout.descendants())
            .enter()
            .append("g")
            .attr("class", "node")
            .attr("transform", d => `translate(${d.x},${d.y})`);
        
        // Add circles for the nodes
        node.append("circle")
            .attr("r", 10)
            .style("fill", "#69b3a2");
        
        // Add labels for the nodes
        node.append("text")
            .attr("dy", ".35em")
            .attr("y", d => d.children ? -20 : 20)
            .style("text-anchor", "middle")
            .text(d => d.data.name);
    });
}
</script>

<?php include '../includes/footer.php'; ?>