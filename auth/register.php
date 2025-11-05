<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php?registered=1");
        exit;
    } else {
        $error = "Error creating account. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - FlixCinema</title>
  <link rel="stylesheet" href="../css/auth-dark.css">
</head>
<body>

<div class="auth-container">
  <h1>Create Account 🎥</h1>

  <?php if (!empty($error)): ?>
    <p style="color:#ff4d4d; text-align:center;"><?php echo $error; ?></p>
  <?php endif; ?>

  <form method="POST">
    <div class="input-group">
      <label>Full Name</label>
      <input type="text" name="name" required>
    </div>

    <div class="input-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>

    <div class="input-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <button class="btn" type="submit">Register</button>

    <div class="bottom-text">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </form>
</div>

</body>
</html>
