<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['email'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_email = $_SESSION['email'];

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();
$user_id = $user_result['id'] ?? null;
$stmt->close();

if (!$user_id) {
    echo "User not found.";
    exit;
}

// Handle booking cancel
if (isset($_GET['cancel_booking'])) {
    $booking_id = intval($_GET['cancel_booking']);
    $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $booking_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: my_bookings.php?msg=Booking cancelled successfully");
    exit;
}

// Fetch bookings
$sql = "
SELECT b.booking_id, m.title, m.image, mt.date, mt.time_block, b.num_tickets, b.booking_date
FROM bookings b
JOIN movies m ON b.movie_id = m.movie_id
JOIN movie_timings mt ON b.timing_id = mt.timing_id
WHERE b.user_id = ?
ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings - FlixCinema</title>
<link rel="stylesheet" href="../css/dark-theme.css">
<style>
body {
  background-color: #0e0e10;
  font-family: "Poppins", sans-serif;
  color: #fff;
  margin: 0;
}

header {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 60px;
  background:rgba(20,20,20,0.8);
  backdrop-filter:blur(8px);
  position:sticky; top:0; z-index:100;
}

.logo { font-size:1.5rem; font-weight:700; color:#ffb100; }
nav a {
  color:#ccc; text-decoration:none; margin-left:20px; transition:color 0.3s;
}
nav a:hover { color:#fff; }

.container {
  max-width: 900px;
  margin: 50px auto;
  padding: 20px;
  background: #1a1a1d;
  border-radius: 12px;
}

h1 {
  color: #ffb100;
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #333;
}

th {
  color: #ffb100;
  background-color: #222;
}

tr:hover {
  background-color: #2a2a2d;
}

img.poster {
  width: 70px;
  height: 90px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #444;
}

.cancel-btn {
  background: #ff4444;
  color: #fff;
  padding: 8px 12px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.9rem;
  transition: background 0.3s ease;
}
.cancel-btn:hover {
  background: #ff6b6b;
}

.message {
  background: #2a2a2d;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 15px;
  color: #ccc;
  border-left: 4px solid #ffb100;
}

footer {
  background: #111;
  padding: 40px 60px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 30px;
  color: #aaa;
}
footer h3 { color: #ffb100; margin-bottom: 10px; }
footer ul { list-style: none; padding: 0; }
footer ul li { margin: 8px 0; }
footer ul li a { color: #aaa; text-decoration: none; transition: color 0.3s; }
footer ul li a:hover { color: #fff; }
.footer-bottom {
  text-align: center;
  padding: 20px;
  background: #0c0c0c;
  color: #666;
  font-size: 0.9rem;
  border-top: 1px solid #222;
}
</style>
</head>
<body>

<?php include "../components/header.php" ?>

<div class="container">
  <h1>🎟️ My Bookings</h1>

  <?php if (isset($_GET['msg'])): ?>
    <div class="message"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <?php if (count($bookings) > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Poster</th>
        <th>Movie</th>
        <th>Date</th>
        <th>Time</th>
        <th>Tickets</th>
        <th>Booked On</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookings as $b): 
        $poster = "../uploads/" . htmlspecialchars($b['image']);
        if (!file_exists($poster) || empty($b['image'])) $poster = "../uploads/placeholder.jpg";
      ?>
      <tr>
        <td><img src="<?= $poster ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="poster"></td>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= htmlspecialchars($b['date']) ?></td>
        <td><?= htmlspecialchars($b['time_block']) ?></td>
        <td><?= htmlspecialchars($b['num_tickets']) ?></td>
        <td><?= htmlspecialchars($b['booking_date']) ?></td>
        <td><a href="?cancel_booking=<?= $b['booking_id'] ?>" class="cancel-btn" onclick="return confirm('Cancel this booking?')">Cancel</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p style="color:#ccc;">You don’t have any bookings yet.</p>
  <?php endif; ?>
</div>

<?php include "../components/footer.php" ?>