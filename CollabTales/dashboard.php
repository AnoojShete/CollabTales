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
  <title>Your Dashboard</title>
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
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Here are your stories:</p>

    <div class="story-list">
      <?php
        $user_id = $_SESSION['user_id'];
        $result = mysqli_query($conn, "SELECT * FROM stories WHERE user_id = $user_id");
        while ($row = mysqli_fetch_assoc($result)) {
          echo "<div class='story-card'>";
          echo "<h3><a href='story_editor.php?id={$row['id']}'>" . htmlspecialchars($row['title']) . "</a></h3>";
          echo "<p>" . htmlspecialchars(substr($row['content'], 0, 100)) . "...</p>";
          echo "</div>";
        }
      ?>
    </div>

    <a href="create_story.php" class="btn">Create New Story</a>
  </main>

  <footer class="footer">
    <p>&copy; 2025 StoryCollab. All rights reserved.</p>
  </footer>
</body>
</html>
