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

/* --- Fetch user id --- */
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_stmt->bind_result($user_id);
$user_stmt->fetch();
$user_stmt->close();

/* --- Fetch movie details --- */
$stmt = $conn->prepare("SELECT title, description, rating, image FROM movies WHERE movie_id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) { echo "Movie not found."; exit; }

/* --- Fetch timings --- */
$tstmt = $conn->prepare("SELECT timing_id, date, time_block FROM movie_timings WHERE movie_id = ? ORDER BY date ASC");
$tstmt->bind_param("i", $movie_id);
$tstmt->execute();
$timings = $tstmt->get_result();
$tstmt->close();

/* --- AJAX for seat list --- */
if (isset($_GET['action']) && $_GET['action'] === 'seats') {
  header('Content-Type: application/json');
  $timing_id = intval($_GET['timing_id'] ?? 0);

  if (!$timing_id) {
    echo json_encode(['error' => 'Missing timing_id']);
    exit;
  }

  // Fetch all seats
  $seats_res = $conn->query("SELECT seat_id, row_label, seat_number FROM seats ORDER BY row_label, seat_number");
  $seats = [];
  if ($seats_res) {
    while ($row = $seats_res->fetch_assoc()) {
      $seats[] = $row;
    }
  }

  // Fetch already booked seats
  $booked = [];
  $booked_sql = "SELECT seat_id 
                 FROM booking_seats bs 
                 JOIN bookings b ON b.booking_id = bs.booking_id 
                 WHERE bs.timing_id = $timing_id AND b.status='confirmed'";
  $booked_res = $conn->query($booked_sql);
  if ($booked_res) {
    while ($row = $booked_res->fetch_assoc()) {
      $booked[] = intval($row['seat_id']);
    }
  }

  echo json_encode(['seats' => $seats, 'booked' => $booked]);
  exit;
}

/* --- Handle booking submission --- */
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $timing_id = intval($_POST['timing_id'] ?? 0);
  $seat_ids = $_POST['seat_ids'] ?? [];
  $seat_ids = array_map('intval', $seat_ids);
  $seat_ids = array_unique($seat_ids);

  if ($timing_id && count($seat_ids) > 0) {
    $conn->begin_transaction();
    try {
      $num_tickets = count($seat_ids);
      $bstmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, timing_id, num_tickets, status) VALUES (?, ?, ?, ?, 'confirmed')");
      $bstmt->bind_param("iiii", $user_id, $movie_id, $timing_id, $num_tickets);
      $bstmt->execute();
      $booking_id = $conn->insert_id;
      $bstmt->close();

      $insert = $conn->prepare("INSERT INTO booking_seats (booking_id, timing_id, seat_id) VALUES (?, ?, ?)");
      foreach ($seat_ids as $sid) {
        $insert->bind_param("iii", $booking_id, $timing_id, $sid);
        $insert->execute();
      }
      $insert->close();
      $conn->commit();
      $message = "✅ Booking successful!";
    } catch (Exception $e) {
      $conn->rollback();
      $message = "❌ Error: seat may already be taken.";
    }
  } else {
    $message = "⚠️ Please select a showtime and at least one seat.";
  }
}

/* --- Image path --- */
$image_path = "../uploads/" . htmlspecialchars($movie['image']);
if (!file_exists($image_path) || empty($movie['image'])) $image_path = "../uploads/placeholder.jpg";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Movie - <?= htmlspecialchars($movie['title']) ?></title>
<link rel="stylesheet" href="../css/dark-theme.css">
<style>
body { background:#0e0e10; color:#fff; font-family:"Poppins",sans-serif; margin:0; }
.container { max-width:1000px; margin:60px auto; padding:20px; background:#1a1a1d; border-radius:12px; display:flex; gap:30px; flex-wrap:wrap; }
.poster { flex:1 1 35%; }
.poster img { width:100%; border-radius:10px; }
.details { flex:1 1 60%; }
h1 { color:#ffb100; margin-top:0; }
p { color:#ddd; }
.btn { background:#ffb100; color:#000; border:none; padding:10px 20px; border-radius:8px; margin-top:20px; cursor:pointer; }
.btn:hover { background:#ffd75a; }
.message { text-align:center; margin:15px 0; font-weight:500; }

/* Showtime selection */
.showtime-wrapper {
  margin-top: 15px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.showtime-label {
  font-weight: 600;
  color: #ffb100;
  font-size: 1.1rem;
}

.showtime-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 5px;
}

.showtime-option {
  background: #2a2a2d;
  color: #fff;
  padding: 10px 16px;
  border-radius: 8px;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.25s ease;
  font-size: 0.95rem;
}

.showtime-option:hover {
  background: #3b3b3d;
  border-color: #ffb100;
}

.showtime-option.active {
  background: #ffb100;
  color: #000;
  font-weight: 600;
  border-color: #ffb100;
}

/* Hide default select */
select[name="timing_id"] {
  display: none;
}

/* seat map */
#seat-area { display:none; margin-top:20px; }
.legend { display:flex; gap:12px; margin-bottom:10px; color:#bbb; font-size:14px; flex-wrap:wrap; }
.legend span { display:flex; align-items:center; gap:6px; }
.legend-box { width:18px; height:18px; border-radius:4px; display:inline-block; }

.seat-grid { display:grid; grid-template-columns: repeat(11, 1fr); gap:8px; justify-items:center; margin-top:10px; }
.row-label { color:#aaa; text-align:center; }
.seat { width:30px; height:30px; background:#2a2a2a; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:12px; cursor:pointer; }
.seat.booked { background:#812f2f; border:1px solid #a44a4a; color:#ccc; cursor:not-allowed; opacity:0.8; }
.seat.selected { background:#2d812f; color:#fff; }
.screen { text-align:center; margin-top:10px; color:#ccc; font-size:14px; }
@media(max-width:768px){ .container{flex-direction:column;} }
</style>
</head>
<body>
<?php include "../components/header.php"; ?>
<div class="container">
  <div class="poster"><img src="<?= $image_path ?>" alt="Poster"></div>
  <div class="details">
    <h1>🎬 <?= htmlspecialchars($movie['title']) ?></h1>
    <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
    <p>Rating: <strong><?= htmlspecialchars($movie['rating']) ?></strong></p>

    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>

    <form method="POST" id="booking-form">
      <!-- Updated Showtime UI -->
      <div class="showtime-wrapper">
        <label class="showtime-label">Select Showtime</label>
        <select name="timing_id" id="timing" required>
          <option value="">Select showtime</option>
          <?php while($t=$timings->fetch_assoc()): ?>
            <option value="<?= $t['timing_id'] ?>"><?= $t['date'] ?> — <?= $t['time_block'] ?></option>
          <?php endwhile; ?>
        </select>
        <div class="showtime-container" id="showtime-buttons"></div>
      </div>

      <div id="seat-area">
        <div class="legend">
          <span><span class="legend-box" style="background:#2a2a2a;"></span>Available</span>
          <span><span class="legend-box" style="background:#2d812f;"></span>Selected</span>
          <span><span class="legend-box" style="background:#812f2f;"></span>Booked</span>
        </div>
        <div class="screen">SCREEN</div>
        <div class="seat-grid" id="seat-grid"></div>
      </div>

      <div id="hidden-seats"></div>
      <button class="btn" type="submit">Confirm Booking</button>
    </form>
  </div>
</div>
<?php include "../components/footer.php"; ?>

<script>
// Showtime buttons
const showtimeSelect=document.getElementById("timing");
const showtimeButtons=document.getElementById("showtime-buttons");

function renderShowtimeButtons(){
  const options=[...showtimeSelect.options].filter(o=>o.value);
  showtimeButtons.innerHTML="";
  options.forEach(opt=>{
    const btn=document.createElement("div");
    btn.className="showtime-option";
    btn.textContent=opt.textContent;
    btn.dataset.value=opt.value;
    btn.addEventListener("click",()=>{
      document.querySelectorAll(".showtime-option").forEach(b=>b.classList.remove("active"));
      btn.classList.add("active");
      showtimeSelect.value=opt.value;
      showtimeSelect.dispatchEvent(new Event("change"));
    });
    showtimeButtons.appendChild(btn);
  });
}
renderShowtimeButtons();

// Seat loading logic
const timingSel=document.getElementById("timing");
const seatArea=document.getElementById("seat-area");
const seatGrid=document.getElementById("seat-grid");
const hiddenSeats=document.getElementById("hidden-seats");
let selected=new Set();

timingSel.addEventListener("change",async()=>{
  const tid=timingSel.value;
  if(!tid){ seatArea.style.display="none"; return; }
  const movieId=<?= $movie_id ?>;
  const resp=await fetch(`book_movie.php?movie_id=${movieId}&action=seats&timing_id=${tid}`);
  const data=await resp.json();
  const seats=data.seats||[], booked=data.booked||[];
  seatGrid.innerHTML=""; selected.clear(); hiddenSeats.innerHTML="";
  const rows=[...new Set(seats.map(s=>s.row_label))];

  rows.forEach(r=>{
    const label=document.createElement("div");
    label.className="row-label"; label.textContent=r;
    seatGrid.appendChild(label);
    seats.filter(s=>s.row_label===r).forEach(s=>{
      const div=document.createElement("div");
      div.className="seat"; div.textContent=s.seat_number; div.dataset.id=s.seat_id;

      if (booked.includes(Number(s.seat_id))) {
        div.classList.add("booked");
        div.title = "Seat already booked";
      } else {
        div.addEventListener("click", () => {
          const id = s.seat_id;
          if (selected.has(id)) {
            selected.delete(id);
            div.classList.remove("selected");
            document.querySelector(`#hidden-seats input[value='${id}']`)?.remove();
          } else {
            selected.add(id);
            div.classList.add("selected");
            const inp = document.createElement("input");
            inp.type = "hidden";
            inp.name = "seat_ids[]";
            inp.value = id;
            hiddenSeats.appendChild(inp);
          }
        });
      }
      seatGrid.appendChild(div);
    });
  });
  seatArea.style.display="block";
});
</script>
</body>
</html>
