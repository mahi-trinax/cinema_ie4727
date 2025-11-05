<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $rating = trim($_POST['rating']);
    $description = trim($_POST['description']);
    $image = '';

    // File upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../uploads/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $image = $fileName;
            } else {
                $message = "Failed to upload image.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, or WEBP allowed.";
        }
    }

    if ($title && $rating && $description && $image) {
        $stmt = $conn->prepare("INSERT INTO movies (title, rating, description, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $rating, $description, $image);
        if ($stmt->execute()) {
            $movie_id = $stmt->insert_id;

            // Insert movie timings
            if (isset($_POST['date'], $_POST['start_hour'], $_POST['am_pm'], $_POST['duration'])) {
                $dates = $_POST['date'];
                $start_hours = $_POST['start_hour'];
                $am_pms = $_POST['am_pm'];
                $durations = $_POST['duration'];

                for ($i = 0; $i < count($dates); $i++) {
                    $date = trim($dates[$i]);
                    $hour = intval($start_hours[$i]);
                    $ampm = $am_pms[$i];
                    $duration = intval($durations[$i]);

                    if ($date && $hour && $ampm && $duration) {
                        // Convert start hour to 24h format for calculation
                        $startHour24 = ($ampm === 'PM' && $hour != 12) ? $hour + 12 : (($ampm === 'AM' && $hour == 12) ? 0 : $hour);
                        $endHour24 = $startHour24 + $duration;
                        if ($endHour24 >= 24) $endHour24 -= 24; // wrap around midnight

                        // Convert back to formatted AM/PM time
                        $endAMPM = $endHour24 >= 12 ? 'PM' : 'AM';
                        $endHour12 = $endHour24 % 12;
                        if ($endHour12 == 0) $endHour12 = 12;

                        $time_block = sprintf("%d:00 %s - %d:00 %s", $hour, $ampm, $endHour12, $endAMPM);

                        $tstmt = $conn->prepare("INSERT INTO movie_timings (movie_id, date, time_block) VALUES (?, ?, ?)");
                        $tstmt->bind_param("iss", $movie_id, $date, $time_block);
                        $tstmt->execute();
                        $tstmt->close();
                    }
                }
            }

            $message = "✅ Movie and timings added successfully!";
        } else {
            $message = "Error adding movie: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill all fields and upload an image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Movie | Admin Panel</title>
<link rel="stylesheet" href="../css/admin-dark.css">
<style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background-color: #121212;
  color: #f0f0f0;
}
.container { display: flex; height: 100vh; }
.sidebar {
  width: 220px; background-color: #1e1e1e;
  display: flex; flex-direction: column;
  padding-top: 40px; box-shadow: 2px 0 8px rgba(0,0,0,0.3);
}
.sidebar h2 { text-align: center; color: #ffb703; margin-bottom: 40px; }
.sidebar a {
  padding: 15px 20px; color: #f0f0f0; text-decoration: none;
  display: flex; align-items: center; transition: background 0.2s;
}
.sidebar a:hover { background-color: #292929; }
.sidebar a.active { background-color: #333; font-weight: 600; }
.main-content { flex: 1; padding: 40px; overflow-y: auto; }
.topbar { display: flex; justify-content: space-between; align-items: center; }
.topbar h1 { font-size: 28px; color: #ffb703; }
.topbar .back-btn {
  background-color: #ffb703; color: #121212; border: none;
  padding: 10px 18px; border-radius: 6px; cursor: pointer;
  font-weight: 600; transition: background 0.3s;
}
.topbar .back-btn:hover { background-color: #ffa500; }
.form-card {
  background: #1f1f1f; padding: 30px; border-radius: 12px;
  max-width: 700px; margin-top: 40px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.4);
}
.form-card label {
  display: block; margin-bottom: 6px; color: #ccc; font-size: 14px;
}
.form-card input, .form-card textarea, .form-card select {
  width: 100%; padding: 10px; border-radius: 6px;
  border: 1px solid rgba(255,177,0,0.3);
  background: rgba(255,255,255,0.05); color: #fff;
  margin-bottom: 15px; font-size: 15px;
}
.form-card textarea { resize: vertical; height: 120px; }
.form-card .btn {
  background: #ffb703; color: #121212; border: none;
  padding: 10px 20px; border-radius: 6px;
  cursor: pointer; font-weight: 600; transition: background 0.3s;
}
.form-card .btn:hover { background: #ffa500; }
.message {
  margin-top: 20px; padding: 10px; border-radius: 6px; text-align: center;
}
.message.success { background: rgba(0,255,0,0.1); color: #7CFC00; }
.message.error { background: rgba(255,0,0,0.1); color: #ff4d4d; }

.timing-group {
  background: rgba(255,255,255,0.03);
  padding: 15px; border-radius: 8px; margin-bottom: 15px;
}
.timing-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
.add-timing-btn {
  background: #333; color: #ffb703; border: 1px dashed #ffb703;
  padding: 8px 12px; border-radius: 6px; cursor: pointer;
  transition: background 0.3s;
}
.add-timing-btn:hover { background: #444; }
</style>
</head>
<body>
<div class="container">
  <div class="sidebar">
    <h2>🎬 Admin</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="manage_bookings.php">🎟️ Bookings</a>
  </div>

  <div class="main-content">
    <div class="topbar">
      <h1>Add Movie</h1>
      <button class="back-btn" onclick="window.location='dashboard.php'">← Back</button>
    </div>

    <div class="form-card">
      <?php if ($message): ?>
        <div class="message <?= str_contains($message, '✅') ? 'success' : 'error' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST" enctype="multipart/form-data">
        <label>Movie Title</label>
        <input type="text" name="title" placeholder="Enter movie title" required>

        <label>Rating</label>
        <select name="rating" required>
          <option value="">Select rating</option>
          <option value="G">G</option>
          <option value="PG">PG</option>
          <option value="PG-13">PG-13</option>
          <option value="R">R</option>
          <option value="NC-17">NC-17</option>
        </select>

        <label>Description</label>
        <textarea name="description" placeholder="Enter short description" required></textarea>

        <label>Poster Image</label>
        <input type="file" name="image" accept="image/*" required>

        <h3 style="margin-top:25px; color:#ffb703;">🎥 Movie Timings</h3>
        <div id="timing-container">
          <div class="timing-group">
            <label>Date</label>
            <input type="date" name="date[]" required>

            <div class="timing-row">
              <select name="start_hour[]" required>
                <option value="">Start Hour</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                  <option value="<?= $i ?>"><?= $i ?>:00</option>
                <?php endfor; ?>
              </select>

              <select name="am_pm[]" required>
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>

              <select name="duration[]" required>
                <option value="">Duration</option>
                <option value="1">1 hour</option>
                <option value="2">2 hours</option>
                <option value="3">3 hours</option>
                <option value="4">4 hours</option>
                <option value="5">5 hours</option>
              </select>
            </div>
          </div>
        </div>
        <button type="button" class="add-timing-btn" onclick="addTiming()">+ Add Another Timing</button>

        <br><br>
        <button type="submit" class="btn">Add Movie</button>
      </form>
    </div>
  </div>
</div>

<script>
function addTiming() {
  const container = document.getElementById('timing-container');
  const div = document.createElement('div');
  div.classList.add('timing-group');
  div.innerHTML = `
    <label>Date</label>
    <input type="date" name="date[]" required>

    <div class="timing-row">
      <select name="start_hour[]" required>
        <option value="">Start Hour</option>
        ${[...Array(12)].map((_, i) => `<option value="${i+1}">${i+1}:00</option>`).join('')}
      </select>

      <select name="am_pm[]" required>
        <option value="AM">AM</option>
        <option value="PM">PM</option>
      </select>

      <select name="duration[]" required>
        <option value="">Duration</option>
        <option value="1">1 hour</option>
        <option value="2">2 hours</option>
        <option value="3">3 hours</option>
        <option value="4">4 hours</option>
        <option value="5">5 hours</option>
      </select>
    </div>
  `;
  container.appendChild(div);
}
</script>
</body>
</html>
