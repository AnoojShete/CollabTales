<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Your Profile</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="header">
    <div class="container">
      <h1>📚 StoryCollab</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="create_story.php">Create Story</a>
        <a href="stories.php">Explore Stories</a>
        <a href="profile.php">Profile</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <h2>Your Profile</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <a href="logout.php" class="btn">Logout</a>
  </main>

  <footer class="footer">
    <p>&copy; 2025 StoryCollab. All rights reserved.</p>
  </footer>
</body>
</html>
