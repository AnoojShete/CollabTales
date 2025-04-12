<?php
include 'db.php';

$content = mysqli_real_escape_string($conn, $_POST['content']);
$story_id = (int)$_POST['story_id'];

mysqli_query($conn, "UPDATE stories SET content='$content' WHERE id=$story_id");
echo "Saved";
?>
