<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Explore Stories</title>
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
    <h2>Explore Stories</h2>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM stories");
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<div class='story-card'>";
      echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
      echo "<p>" . htmlspecialchars(substr($row['content'], 0, 120)) . "...</p>";
      echo "</div>";
    }
    ?>
  </main>

  <footer class="footer">
    <p>&copy; 2025 StoryCollab. All rights reserved.</p>
  </footer>
</body>
</html>
