<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) die('Story not found');

$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM stories WHERE id = $id");
$story = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Story</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <header class="header">
    <div class="container">
      <h1>📚 StoryCollab</h1>
    </div>
  </header>

  <main class="container">
    <h2>Edit Story: <?php echo htmlspecialchars($story['title']); ?></h2>
    <textarea id="storyContent" rows="15"><?php echo htmlspecialchars($story['content']); ?></textarea>
    <input type="hidden" id="storyId" value="<?php echo $id; ?>">
    <div id="status" class="save-status">Start typing to auto-save...</div>
  </main>

  <script>
    $(document).ready(function () {
      $('#storyContent').on('input', function () {
        const content = $(this).val();
        const storyId = $('#storyId').val();
        $.post('ajax_save.php', { content, story_id: storyId }, function (response) {
          $('#status').text('Saved at ' + new Date().toLocaleTimeString());
        });
      });
    });
  </script>
</body>
</html>
