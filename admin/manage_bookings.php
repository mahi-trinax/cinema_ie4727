<?php
session_start();
include('../config/config.php');

// Check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

// Delete booking
if (isset($_GET['delete_booking'])) {
    $id = intval($_GET['delete_booking']);
    $conn->query("DELETE FROM bookings WHERE booking_id = $id");
    $message = "Booking deleted successfully.";
}

// Update booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $num_tickets = intval($_POST['num_tickets']);
    $timing_id = intval($_POST['timing_id']);

    if ($booking_id && $num_tickets > 0 && $timing_id) {
        $stmt = $conn->prepare("UPDATE bookings SET num_tickets=?, timing_id=? WHERE booking_id=?");
        $stmt->bind_param("iii", $num_tickets, $timing_id, $booking_id);
        if ($stmt->execute()) {
            $message = "Booking updated successfully.";
        } else {
            $message = "Error updating booking.";
        }
        $stmt->close();
    }
}

// Fetch bookings
$bookings = $conn->query("
    SELECT 
        b.booking_id, u.name AS user_name, m.title AS movie_title,
        mt.timing_id, mt.date, mt.time_block, 
        b.num_tickets, b.booking_date, m.movie_id
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN movies m ON b.movie_id = m.movie_id
    JOIN movie_timings mt ON b.timing_id = mt.timing_id
    ORDER BY b.booking_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Bookings - FlixCinema</title>
<link rel="stylesheet" href="../css/admin-dark.css">
<style>
body {
    background: #0e0e10;
    color: #fff;
    font-family: "Poppins", sans-serif;
    margin: 0;
}
.container {
    max-width: 1100px;
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
.update-box select, .update-box input {
    width: 100%;
    padding: 8px;
    border: none;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #333;
    color: #fff;
}
.update-box button {
    background: #ffb100;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    color: #000;
    cursor: pointer;
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
}
.cancel-btn:hover {
    background: #999;
}

.back-btn {
  background-color: #ffb703; color: #121212; border: none;
  padding: 10px 18px; border-radius: 6px; cursor: pointer;
  font-weight: 600; transition: background 0.3s;
}
</style>
</head>
<body>

<div class="container">
     <button class="back-btn" onclick="window.location='dashboard.php'">← Back</button>
    <h1>🎟️ Manage Bookings</h1>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>User</th>
            <th>Movie</th>
            <th>Date</th>
            <th>Time</th>
            <th>Tickets</th>
            <th>Booked On</th>
            <th>Action</th>
        </tr>
        <?php while ($b = $bookings->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($b['user_name']) ?></td>
            <td><?= htmlspecialchars($b['movie_title']) ?></td>
            <td><?= htmlspecialchars($b['date']) ?></td>
            <td><?= htmlspecialchars($b['time_block']) ?></td>
            <td><?= htmlspecialchars($b['num_tickets']) ?></td>
            <td><?= htmlspecialchars($b['booking_date']) ?></td>
            <td>
                <button class="btn btn-edit" onclick="toggleBox(<?= $b['booking_id'] ?>)">✏️ Edit</button>
                <a href="?delete_booking=<?= $b['booking_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this booking?')">🗑️</a>
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <div id="box-<?= $b['booking_id'] ?>" class="update-box">
                    <form method="POST">
                        <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                        <label>Change Timing</label>
                        <select name="timing_id" required>
                            <option value="">Select Timing</option>
                            <?php
                            $timings = $conn->prepare("SELECT timing_id, date, time_block FROM movie_timings WHERE movie_id = ?");
                            $timings->bind_param("i", $b['movie_id']);
                            $timings->execute();
                            $res = $timings->get_result();
                            while ($t = $res->fetch_assoc()):
                            ?>
                            <option value="<?= $t['timing_id'] ?>" <?= $t['timing_id'] == $b['timing_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['date']) ?> — <?= htmlspecialchars($t['time_block']) ?>
                            </option>
                            <?php endwhile; $timings->close(); ?>
                        </select>

                        <label>Update Ticket Count</label>
                        <input type="number" name="num_tickets" value="<?= $b['num_tickets'] ?>" min="1" max="10" required>

                        <button type="submit" name="update_booking">💾 Save</button>
                        <button type="button" class="cancel-btn" onclick="toggleBox(<?= $b['booking_id'] ?>)">Cancel</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
function toggleBox(id) {
    const box = document.getElementById("box-" + id);
    box.style.display = (box.style.display === "block") ? "none" : "block";
}
</script>

</body>
</html>
