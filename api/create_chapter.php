<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($data['story_id']) || empty($data['title']) || empty($data['content'])) {
    echo json_encode(['error' => 'Story ID, title, and content are required']);
    exit();
}

try {
    // Check if story exists
    $stmt = $pdo->prepare("SELECT id FROM stories WHERE id = ?");
    $stmt->execute([$data['story_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Story not found']);
        exit();
    }
    
    // Get max order_num for this story and parent chapter
    $orderNum = 0;
    $stmt = $pdo->prepare("
        SELECT MAX(order_num) as max_order 
        FROM chapters 
        WHERE story_id = ? AND parent_chapter_id " . 
        (isset($data['parent_chapter_id']) ? "= ?" : "IS NULL")
    );
    
    $params = [$data['story_id']];
    if (isset($data['parent_chapter_id'])) {
        $params[] = $data['parent_chapter_id'];
    }
    
    $stmt->execute($params);
    $result = $stmt->fetch();
    $orderNum = $result['max_order'] !== null ? $result['max_order'] + 1 : 0;
    
    // Insert chapter
    $stmt = $pdo->prepare("
        INSERT INTO chapters (story_id, user_id, title, content, order_num, parent_chapter_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['story_id'],
        $_SESSION['user_id'],
        $data['title'],
        $data['content'],
        $orderNum,
        $data['parent_chapter_id'] ?? null
    ]);
    
    $chapterId = $pdo->lastInsertId();
    
    // Update story's updated_at timestamp
    $stmt = $pdo->prepare("UPDATE stories SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$data['story_id']]);
    
    echo json_encode([
        'success' => true,
        'chapter_id' => $chapterId
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>