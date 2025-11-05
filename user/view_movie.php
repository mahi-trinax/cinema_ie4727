<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['email'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_email = $_SESSION['email'];

if (!isset($_GET['movie_id'])) {
    header("Location: home.php");
    exit;
}

$movie_id = intval($_GET['movie_id']);

// Fetch movie details
$stmt = $conn->prepare("SELECT title, description, rating, image FROM movies WHERE movie_id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) {
    die("<p style='text-align:center;color:red;'>Invalid movie selected.</p>");
}

// Fetch timings
$tstmt = $conn->prepare("SELECT date, time_block FROM movie_timings WHERE movie_id = ? ORDER BY date ASC");
$tstmt->bind_param("i", $movie_id);
$tstmt->execute();
$timings = $tstmt->get_result();
$tstmt->close();

// Fetch feedbacks for the movie
$fstmt = $conn->prepare("
    SELECT f.name, f.rating, f.message, f.submitted_at
    FROM feedback f
    WHERE f.movie_id = ?
    ORDER BY f.submitted_at DESC
");
$fstmt->bind_param("i", $movie_id);
$fstmt->execute();
$feedbacks = $fstmt->get_result();
$fstmt->close();

// Calculate average rating
$avg_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM feedback WHERE movie_id = ?");
$avg_stmt->bind_param("i", $movie_id);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result()->fetch_assoc();
$avg_stmt->close();

$average_rating = $avg_result['avg_rating'] ? round($avg_result['avg_rating'], 1) : 'N/A';
$total_reviews = $avg_result['total'] ?? 0;

// Handle image
$image_path = "../uploads/" . htmlspecialchars($movie['image']);
if (!file_exists($image_path) || empty($movie['image'])) $image_path = "../uploads/placeholder.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($movie['title']) ?> | FlixCinema</title>
<link rel="stylesheet" href="../css/dark-theme.css">
<style>
body {
  background:#0e0e10;
  color:#fff;
  font-family:"Poppins",sans-serif;
  margin:0;
}

/* ---------- Container Layout ---------- */
.container {
  max-width:1000px;
  margin:60px auto;
  padding:20px;
  background:#1a1a1d;
  border-radius:12px;
  display:flex;
  gap:30px;
  flex-wrap:wrap;
  box-shadow:0 0 20px rgba(255,177,0,0.08);
}
.poster {
  flex:1 1 35%;
}
.poster img {
  width:100%;
  border-radius:10px;
  box-shadow:0 0 10px rgba(255,177,0,0.2);
}
.details {
  flex:1 1 60%;
}
h1 {
  color:#ffb100;
  margin-top:0;
}
p {
  color:#ccc;
  line-height:1.6;
}
a.btn {
  display:inline-block;
  background:#ffb100;
  color:#000;
  padding:10px 20px;
  border-radius:8px;
  text-decoration:none;
  margin-top:25px;
  font-weight:600;
  transition:0.2s;
}
a.btn:hover { background:#ffd75a; }

/* ---------- Showtimes Section ---------- */
.showtime-heading {
  color: #ffb100;
  margin-top: 25px;
  font-size: 1.4rem;
  margin-bottom: 15px;
  letter-spacing: 0.5px;
}

.showtime-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.showtime-card {
  background: linear-gradient(145deg, #1f1f22, #141416);
  border: 1px solid #2a2a2d;
  border-radius: 10px;
  padding: 15px 20px;
  color: #fff;
  transition: all 0.3s ease;
  box-shadow: 0 0 10px rgba(255,177,0,0.08);
  text-align: center;
}
.showtime-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 0 18px rgba(255,177,0,0.25);
}
.showtime-date,
.showtime-time {
  font-size: 1rem;
  font-weight: 500;
  margin: 6px 0;
  color: #ffb100;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.showtime-date span,
.showtime-time span {
  font-size: 1.1rem;
}
@media (max-width: 768px) {
  .showtime-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
  .showtime-card {
    padding: 12px 10px;
  }
}

/* ---------- Feedback Section ---------- */
.feedback-section {
  max-width:1000px;
  margin:50px auto;
  background:#1a1a1d;
  border-radius:12px;
  padding:30px;
  box-shadow:0 0 15px rgba(255,177,0,0.08);
}
.feedback-header {
  text-align:center;
  margin-bottom:25px;
}
.feedback-header h2 {
  color:#ffb100;
  font-size:1.8rem;
  margin-bottom:10px;
}
.feedback-header p {
  color:#aaa;
  font-size:0.95rem;
}
.rating-summary {
  text-align:center;
  margin-bottom:25px;
}
.rating-summary strong {
  color:#ffb100;
  font-size:1.3rem;
}
.feedback-list {
  display:grid;
  gap:15px;
}
.feedback-card {
  background:#2a2a2a;
  padding:15px;
  border-radius:10px;
  transition:0.2s;
}
.feedback-card:hover {
  background:#333;
}
.feedback-card .name { font-weight:600; color:#fff; }
.feedback-card .stars { color:#ffb100; margin:5px 0; }
.feedback-card .message { color:#ccc; line-height:1.4; }
.feedback-card .date { font-size:0.8rem; color:#888; text-align:right; margin-top:6px; }
.no-feedback { text-align:center; color:#888; margin:20px 0; }

/* ---------- Responsive ---------- */
@media (max-width:768px) {
  .container { flex-direction:column; align-items:center; }
  .poster, .details { flex:1 1 100%; }
  .feedback-section { padding:20px; margin:30px 10px; }
}
</style>
</head>
<body>

<?php include "../components/header.php" ?>
          
<!-- ---------- Movie Details Section ---------- -->
<div class="container">
  <div class="poster">
    <img src="<?= $image_path ?>" alt="Poster">
  </div>

  <div class="details">
    <h1>🎬 <?= htmlspecialchars($movie['title']) ?></h1>
    <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
    <p><strong>Rating:</strong> <?= htmlspecialchars($movie['rating']) ?></p>

    <h3 class="showtime-heading">🎞️ Available Showtimes</h3>
    <div class="showtime-grid">
      <?php while ($t = $timings->fetch_assoc()): ?>
        <div class="showtime-card">
          <div class="showtime-date">
            <span>📅</span>
            <?= date("M d, Y", strtotime($t['date'])) ?>
          </div>
          <div class="showtime-time">
            <span>🕒</span>
            <?= htmlspecialchars($t['time_block']) ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <a href="book_movie.php?movie_id=<?= $movie_id ?>" class="btn">🎟️ Book This Movie</a>
  </div>
</div>

<!-- ---------- Viewer Feedback Section ---------- -->
<section class="feedback-section">
  <div class="feedback-header">
    <h2>🎥 Viewer Feedback</h2>
    <p>See what others thought about <strong><?= htmlspecialchars($movie['title']) ?></strong>.</p>
  </div>

  <div class="rating-summary">
    <strong><?= $average_rating ?></strong> / 5 ⭐ (<?= $total_reviews ?> reviews)
  </div>

  <?php if ($feedbacks->num_rows > 0): ?>
    <div class="feedback-list">
      <?php while ($f = $feedbacks->fetch_assoc()): ?>
        <div class="feedback-card">
          <div class="name"><?= htmlspecialchars($f['name']) ?></div>
          <div class="stars"><?= str_repeat("⭐", intval($f['rating'])) ?></div>
          <div class="message"><?= nl2br(htmlspecialchars($f['message'])) ?></div>
          <div class="date"><?= date("M d, Y", strtotime($f['submitted_at'])) ?></div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="no-feedback">No feedback yet for this movie.</p>
  <?php endif; ?>

  <div style="text-align:center;margin-top:30px;">
    <a href="feedback.php?movie_id=<?= $movie_id ?>" class="btn">✍️ Leave Your Feedback</a>
  </div>
</section>

<?php include "../components/footer.php" ?>
</body>
</html>
