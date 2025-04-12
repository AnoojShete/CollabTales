<?php
$conn = mysqli_connect('localhost', 'root', '', 'story_collab');
if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}
?>
