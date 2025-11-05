<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['email'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['movie_id'])) {
    header("Location: home.php");
    exit;
}

$movie_id = intval($_GET['movie_id']);
$user_email = $_SESSION['email'];
$message = '';

// --- Fetch user info ---
$stmt = $conn->prepare("SELECT id AS user_id, name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Fetch movie details ---
$stmt = $conn->prepare("SELECT movie_id, title, description, rating, image FROM movies WHERE movie_id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) {
    die("<p style='color:red;text-align:center;'>Invalid movie selected.</p>");
}

$image_path = "../uploads/" . htmlspecialchars($movie['image']);
if (!file_exists($image_path) || empty($movie['image'])) {
    $image_path = "../uploads/placeholder.jpg";
}

// --- Check if user has booked this movie ---
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND movie_id = ?");
$stmt->bind_param("ii", $user['user_id'], $movie_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

$has_booking = $booking ? true : false;

// --- Handle feedback submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $has_booking) {
    $rating = intval($_POST['rating']);
    $msg = trim($_POST['message']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if ($rating >= 1 && $rating <= 5 && $msg && $email) {
        // $stmt = $conn->prepare("
        //     INSERT INTO feedback (user_id, movie_id, name, email, rating, message)
        //     VALUES (?, ?, ?, ?, ?, ?)
        // ");
        // $stmt->bind_param("iissis", $user['user_id'], $movie_id, $user['name'], $user['email'], $rating, $msg);

 

$stmt = $conn->prepare(" 
  INSERT INTO feedback (user_id, movie_id, name, email, phone, rating, message) 
  VALUES (?, ?, ?, ?, ?, ?, ?) 
");
$stmt->bind_param("iisssis", $user['user_id'], $movie_id, $user['name'], $user['email'], $phone, $rating, $msg);

        if ($stmt->execute()) {
            $message = "✅ Feedback submitted successfully!";
        } else {
            $message = "❌ Error submitting feedback: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ Please provide a valid rating (1–5) and a message.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$has_booking) {
    $message = "⚠️ You must book this movie before submitting feedback.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback | FlixCinema</title>
<link rel="stylesheet" href="../css/dark-theme.css">
<style>
body { background:#0e0e10; color:#fff; font-family:"Poppins",sans-serif; margin:0; }
.container { max-width:800px; margin:60px auto; padding:20px; background:#1a1a1d; border-radius:12px; }
h1 { color:#ffb100; text-align:center; }
.movie-details { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
input[type="text"], input[type="email"] {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  background: rgba(255,255,255,0.05);
  color: #fff;
  border: none;
  margin-bottom: 10px;
}
.movie-details img { width:150px; border-radius:10px; }
.movie-details .info { flex:1; }
label { display:block; margin-top:10px; font-weight:600; }
textarea, select {
  width:100%; padding:10px; border-radius:6px;
  background:rgba(255,255,255,0.05); color:#fff; border:none;
}
.btn { background:#ffb100; color:#000; padding:10px 20px; border:none; border-radius:8px; margin-top:20px; cursor:pointer; }
.btn:hover { background:#ffd75a; }
.message { text-align:center; margin-bottom:20px; color:#ffb100; }
</style>
</head>
<body>
    <?php include "../components/header.php" ?>
<div class="container">
  <h1>💬 Share Your Feedback</h1>

  <?php if ($message): ?>
    <div class="message"><?= $message ?></div>
  <?php endif; ?>


  <div class="movie-details">
    <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
    <div class="info">
      <h2><?= htmlspecialchars($movie['title']) ?></h2>
      <p><strong>⭐ Rating:</strong> <?= htmlspecialchars($movie['rating']) ?></p>
      <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
    </div>
  </div>

  <?php if ($has_booking): ?>
  <form method="POST" id="feedbackForm">
  <label>Email</label>
  <input type="text" name="email" id="email" placeholder="Enter your email"  required>

  <label>Phone Number</label>
  <input type="text" name="phone" id="phone" placeholder="Enter your phone number" required>

  <label>Rating (1–5)</label>
  <select name="rating" id="rating" required>
    <option value="">Select rating</option>
    <?php for ($i=1; $i<=5; $i++) echo "<option value='$i'>$i</option>"; ?>
  </select>

  <label>Your Message</label>
  <textarea name="message" id="message" rows="4" placeholder="Write your feedback here..." required></textarea>

  <button type="submit" class="btn">Submit Feedback</button>
</form>

  <?php else: ?>
    <p style="text-align:center;color:#aaa;margin-top:20px;">
      You must <a href="book_movie.php?movie_id=<?= $movie_id ?>" style="color:#ffb100;">book this movie</a> before submitting feedback.
    </p>
  <?php endif; ?>
</div>
<?php include "../components/footer.php" ?>
<script>
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
  const phone = document.getElementById('phone').value.trim();
  const email = document.getElementById('email').value.trim();
  const rating = document.getElementById('rating').value;
  const message = document.getElementById('message').value.trim();

  const phonePattern = /^[0-9]{8}$/;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // Validate phone number
  if (!phonePattern.test(phone)) {
    alert("⚠️ Please enter a valid phone number (8 digits).");
    e.preventDefault();
    return false;
  }

  // Validate email
  if (!emailPattern.test(email)) {
    alert("⚠️ Please enter a valid email address.");
    e.preventDefault();
    return false;
  }

  // Validate rating
  if (rating < 1 || rating > 5) {
    alert("⚠️ Rating must be between 1 and 5.");
    e.preventDefault();
    return false;
  }

  // Validate message
  if (message.length < 5) {
    alert("⚠️ Please enter a longer feedback message.");
    e.preventDefault();
    return false;
  }

  return true;
});

</script>
</body>
</html>
