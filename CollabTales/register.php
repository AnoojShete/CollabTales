<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
  if (mysqli_num_rows($check) > 0) {
    $error = "Username already taken.";
  } else {
    mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
    $_SESSION['username'] = $username;
    $_SESSION['user_id'] = mysqli_insert_id($conn);
    header("Location: dashboard.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container">
    <h2>Create an Account</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" class="form-box">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
  </main>
</body>
</html>
