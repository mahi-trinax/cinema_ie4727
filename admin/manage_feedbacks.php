<?php
session_start();
include('../config/config.php');

// Check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

// Fetch feedbacks
$feedbacks = $conn->query("
    SELECT 
        f.feedback_id, 
        f.user_id,
        f.name, 
        f.email, 
        f.phone, 
        f.rating, 
        f.message, 
        f.submitted_at,
        m.title AS movie_title,
        u.name AS user_name
    FROM feedback f
    LEFT JOIN movies m ON f.movie_id = m.movie_id
    LEFT JOIN users u ON f.user_id = u.id
    ORDER BY f.submitted_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Feedbacks - FlixCinema</title>
<link rel="stylesheet" href="../css/admin-dark.css">
<style>
body {
    background: #0e0e10;
    color: #fff;
    font-family: "Poppins", sans-serif;
    margin: 0;
}
.container {
    max-width: 1300px;
    margin: 60px auto;
    padding: 20px;
    background: #1a1a1d;
    border-radius: 12px;
}
h1 {
    color: #ffb100;
    text-align: center;
}
.message {
    text-align: center;
    margin-bottom: 15px;
    color: #ffb100;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #333;
    text-align: left;
}
th {
    background: #2a2a2a;
    color: #ffb100;
}
tr:hover {
    background: #2a2a2a;
}
.btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
    margin: 2px;
}
.btn-edit {
    background: #ffb100;
    color: #000;
}
.btn-edit:hover {
    background: #ffd75a;
}
.btn-delete {
    background: #e74c3c;
    color: #fff;
}
.btn-delete:hover {
    background: #ff6655;
}

.update-box {
    display: none;
    margin: 15px 0;
    background: #2a2a2a;
    padding: 15px;
    border-radius: 10px;
}
.update-box label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}
.update-box input, .update-box textarea, .update-box select {
    width: 100%;
    padding: 8px;
    border: none;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #333;
    color: #fff;
    box-sizing: border-box;
}
.update-box textarea {
    min-height: 100px;
    resize: vertical;
}
.update-box button {
    background: #ffb100;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    color: #000;
    cursor: pointer;
    font-weight: 600;
}
.update-box button:hover {
    background: #ffd75a;
}
.cancel-btn {
    background: #777;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    margin-left: 10px;
    font-weight: 600;
}
.cancel-btn:hover {
    background: #999;
}

.back-btn {
  background-color: #ffb703; 
  color: #121212; 
  border: none;
  padding: 10px 18px; 
  border-radius: 6px; 
  cursor: pointer;
  font-weight: 600; 
  transition: background 0.3s;
  margin-bottom: 20px;
}
.back-btn:hover {
  background-color: #ffd75a;
}

.rating-stars {
    color: #ffb100;
    font-weight: bold;
}

.message-cell {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.message-cell:hover {
    white-space: normal;
    overflow: visible;
    background: #2a2a2a;
    position: relative;
    z-index: 1;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 10px;
}
.form-group {
    flex: 1;
}
</style>
</head>
<body>

<div class="container">
    <button class="back-btn" onclick="window.location='dashboard.php'">← Back to Dashboard</button>
    <h1>💬 Manage Feedbacks</h1>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Name</th>
            <th>Movie</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Rating</th>
            <th>Message</th>
            <th>Submitted</th>
        </tr>
        <?php while ($f = $feedbacks->fetch_assoc()): ?>
        <tr>
            <td><?= !empty($f['user_name']) ? htmlspecialchars($f['user_name']) : 'Guest' ?></td>
            <td><?= htmlspecialchars($f['movie_title']) ?></td>
            <td><?= htmlspecialchars($f['email']) ?></td>
            <td><?= htmlspecialchars($f['phone']) ?></td>
            <td class="rating-stars"><?= str_repeat('★', $f['rating']) . str_repeat('☆', 5 - $f['rating']) ?></td>
            <td class="message-cell" title="<?= htmlspecialchars($f['message']) ?>"><?= htmlspecialchars($f['message']) ?></td>
            <td><?= htmlspecialchars($f['submitted_at']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>