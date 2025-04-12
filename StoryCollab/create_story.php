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
  <title>Create Story</title>
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
    <h2>Create a New Story</h2>
    <form action="save_story.php" method="POST" class="form-box">
      <input type="text" name="title" placeholder="Story Title" required />
      <textarea name="content" placeholder="Start your story..." rows="10" required></textarea>
      <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
      <button type="submit" class="btn">Save Story</button>
    </form>
  </main>

  <footer class="footer">
    <p>&copy; 2025 StoryCollab. All rights reserved.</p>
  </footer>
</body>
</html>
