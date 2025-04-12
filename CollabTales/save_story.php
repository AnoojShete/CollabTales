<?php
include 'db.php';

$title = mysqli_real_escape_string($conn, $_POST['title']);
$content = mysqli_real_escape_string($conn, $_POST['content']);
$user_id = (int)$_POST['user_id'];

$sql = "INSERT INTO stories (title, content, user_id) VALUES ('$title', '$content', $user_id)";
mysqli_query($conn, $sql);

header('Location: dashboard.php');
exit;
?>
