<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch summary data
$totalMovies = $conn->query("SELECT COUNT(*) AS count FROM movies")->fetch_assoc()['count'];
$totalBookings = $conn->query("SELECT COUNT(*) AS count FROM bookings")->fetch_assoc()['count'];

// Fetch movies
$movies = $conn->query("SELECT * FROM movies ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/admin-dark.css">
<style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #121212;
  color: #f0f0f0;
}

.container {
  display: flex;
  height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 220px;
  background-color: #1e1e1e;
  display: flex;
  flex-direction: column;
  padding-top: 40px;
  box-shadow: 2px 0 8px rgba(0,0,0,0.3);
}
.sidebar h2 {
  text-align: center;
  color: #ffb703;
  margin-bottom: 40px;
}
.sidebar a {
  padding: 15px 20px;
  color: #f0f0f0;
  text-decoration: none;
  display: flex;
  align-items: center;
  transition: background 0.2s;
}
.sidebar a:hover {
  background-color: #292929;
}
.sidebar a.active {
  background-color: #333;
  font-weight: 600;
}

/* Main */
.main-content {
  flex: 1;
  padding: 30px 40px;
  overflow-y: auto;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.topbar h1 {
  font-size: 28px;
  color: #ffb703;
}
.topbar .add-btn {
  background-color: #ffb703;
  color: #121212;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s;
}
.topbar .add-btn:hover {
  background-color: #ffa500;
}

/* Summary cards */
.summary {
  display: flex;
  gap: 30px;
  margin: 40px 0;
}
.summary-card {
  background: #1f1f1f;
  flex: 1;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.summary-card h2 {
  font-size: 14px;
  color: #aaa;
  margin-bottom: 10px;
}
.summary-card p {
  font-size: 24px;
  color: #fff;
  margin: 0;
}

/* Movie table */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  padding: 12px;
  border-bottom: 1px solid #333;
  text-align: left;
}
th {
  color: #aaa;
  font-size: 13px;
  text-transform: uppercase;
}
td img {
  width: 60px;
  height: 80px;
  object-fit: cover;
  border-radius: 6px;
}
td .edit-btn {
  background: none;
  color: #ffb703;
  border: 1px solid #ffb703;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}
td .edit-btn:hover {
  background: #ffb703;
  color: #121212;
}
</style>
</head>
<body>

<div class="container">
  <div class="sidebar">
    <h2>🎬 Admin</h2>
    <a href="dashboard.php" class="active">📊 Dashboard</a>
    <a href="manage_bookings.php">🎟️ Bookings</a>
    <a href="manage_feedbacks.php">💬 Feedbacks</a>
    <a href="../auth/logout.php"> ➜🚪Logout</a>
    
  </div>

  <div class="main-content">
    <div class="topbar">
      <h1>Dashboard</h1>
      <button class="add-btn" onclick="window.location='add_movie.php'">+ Add Movie</button>
    </div>

    <div class="summary">
      <div class="summary-card">
        <h2>TOTAL MOVIES AIRING NOW</h2>
        <p><?= $totalMovies ?></p>
      </div>
      <div class="summary-card">
        <h2>TOTAL BOOKINGS</h2>
        <p><?= $totalBookings ?></p>
      </div>
    </div>

    <table>
      <tr>
        <th>Poster</th>
        <th>Title</th>
        <th>Rating</th>
        <th>Actions</th>
      </tr>
      <?php while ($movie = $movies->fetch_assoc()): ?>
      <tr>
        <td><img src="../uploads/<?= htmlspecialchars($movie['image']) ?>" alt="Poster"></td>
        <td><?= htmlspecialchars($movie['title']) ?></td>
        <td><?= htmlspecialchars($movie['rating']) ?></td>
        <td>
          <button class="edit-btn" onclick="window.location='edit_movie.php?id=<?= $movie['movie_id'] ?>'">Edit</button>
          <button class="edit-btn" onclick="window.location='delete_movie.php?id=<?= $movie['movie_id'] ?>'">Delete</button>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>

</body>
</html>
