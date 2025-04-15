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
if (empty($data['title']) || empty($data['description'])) {
    echo json_encode(['error' => 'Title and description are required']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Insert story
    $stmt = $pdo->prepare("
        INSERT INTO stories (user_id, title, description, category)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $data['title'],
        $data['description'],
        $data['category'] ?? null
    ]);
    
    $storyId = $pdo->lastInsertId();
    
    // Handle cover image upload if present
    if (!empty($data['cover_image'])) {
        $uploadDir = '../uploads/cover_images/';
        $fileName = 'story_' . $storyId . '_' . time() . '.jpg';
        $filePath = $uploadDir . $fileName;
        
        // Save the base64 image
        if (file_put_contents($filePath, base64_decode($data['cover_image']))) {
            $stmt = $pdo->prepare("UPDATE stories SET cover_image = ? WHERE id = ?");
            $stmt->execute([$fileName, $storyId]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'story_id' => $storyId
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>