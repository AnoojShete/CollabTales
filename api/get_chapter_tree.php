<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['story_id'])) {
    echo json_encode(['error' => 'Story ID is required']);
    exit();
}

$storyId = $_GET['story_id'];

function buildChapterTree($pdo, $storyId, $parentId = null) {
    $query = "
        SELECT c.id, c.title, u.username
        FROM chapters c
        JOIN users u ON c.user_id = u.id
        WHERE c.story_id = ? AND c.parent_chapter_id " . 
        ($parentId === null ? "IS NULL" : "= ?") . "
        ORDER BY c.order_num
    ";
    
    $stmt = $pdo->prepare($query);
    $params = [$storyId];
    if ($parentId !== null) {
        $params[] = $parentId;
    }
    $stmt->execute($params);
    
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tree = [];
    foreach ($chapters as $chapter) {
        $node = [
            'id' => $chapter['id'],
            'name' => $chapter['title'],
            'author' => $chapter['username'],
            'children' => buildChapterTree($pdo, $storyId, $chapter['id'])
        ];
        $tree[] = $node;
    }
    
    return $tree;
}

try {
    $tree = buildChapterTree($pdo, $storyId);
    
    if (empty($tree)) {
        echo json_encode(['error' => 'No chapters found for this story']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'tree' => $tree[0] // Return the root node
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>