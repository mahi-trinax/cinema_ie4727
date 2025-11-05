<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['email'] = $user['email'];
      $_SESSION['role'] = $user['role'];


      if ($user['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
      } else if ($user['role'] == 'user') {
        header("Location: ../user/home.php");
      }

      exit;
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "No user found with that email.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login - FlixCinema</title>
  <link rel="stylesheet" href="../css/auth-dark.css">
</head>

<body>

  <div class="auth-container">
    <h1>Welcome Back 🎬</h1>

    <?php if (!empty($error)): ?>
      <p style="color:#ff4d4d; text-align:center;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button class="btn" type="submit">Login</button>

      <div class="bottom-text">
        Don’t have an account? <a href="register.php">Register</a>
      </div>
    </form>
  </div>

</body>

</html>