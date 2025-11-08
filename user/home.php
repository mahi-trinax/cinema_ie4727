<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['email'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_email = $_SESSION['email'] ?? 'Guest';

// Get search query if any
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$movies_html = '';
$movies = [];

// If search query exists
if (!empty($search_query)) {
  $stmt = $conn->prepare("
    SELECT movie_id, title, description, rating, image 
    FROM movies 
    WHERE title LIKE CONCAT('%', ?, '%') 
       OR description LIKE CONCAT('%', ?, '%')
    ORDER BY created_at DESC
  ");
  $stmt->bind_param("ss", $search_query, $search_query);
} else {
  // Default query - 4 most recent movies
  $stmt = $conn->prepare("
    SELECT movie_id, title, description, rating, image 
    FROM movies 
    ORDER BY created_at DESC 
    LIMIT 4
  ");
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $movie_id = (int)$row['movie_id'];
    $title = htmlspecialchars($row['title']);
    $desc = nl2br(htmlspecialchars($row['description']));
    $rating = htmlspecialchars($row['rating']);
    $image = htmlspecialchars($row['image']);
    $image_path = "../uploads/" . $image;
    if (!file_exists($image_path) || empty($image)) $image_path = "../uploads/placeholder.jpg";

    $movies_html .= "
    <div class='movie-card fade-in'>
      <img src='$image_path' alt='$title'>
      <div class='movie-info'>
        <h3>$title</h3>
        <p class='rating'>⭐ $rating</p>
        <p>$desc</p>
        <div class='movie-actions'>
          <a href='book_movie.php?movie_id=$movie_id' title='Book Now'><span>🎟️</span></a>
          <a href='view_movie.php?movie_id=$movie_id' title='View Movie'><span>👁️</span></a>
          <a href='feedback.php?movie_id=$movie_id' title='Give Feedback'><span>💬</span></a>
        </div>
      </div>
    </div>";
    $movies[] = [
      'id' => $movie_id,
      'title' => $title,
      'desc' => strip_tags($desc),
      'rating' => $rating,
      'image' => $image_path
    ];
  }
}
$stmt->close();

// Safe hero movie selection
if (!empty($movies)) {
  $hero_movie = $movies[array_rand($movies)];
} else {
  $hero_movie = [
    'title' => 'Welcome to FlixCinema',
    'desc' => 'Experience the latest blockbusters and timeless classics.',
    'image' => '../uploads/placeholder.jpg',
    'id' => 0
  ];
}

// Fetch all movies for carousel (unique by movie_id)
$tstmt = $conn->query("
    SELECT DISTINCT m.movie_id, m.title, m.image, m.description,  m.created_at
    FROM movies m
    INNER JOIN movie_timings mt ON mt.movie_id = m.movie_id
    ORDER BY m.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FlixCinema - Home</title>
<link rel="stylesheet" href="../css/dark-theme.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"Poppins",sans-serif;background-color:#0e0e10;color:#fff;overflow-x:hidden;}

/* HEADER */
header{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;padding:20px 60px;background:rgba(20,20,20,0.8);backdrop-filter:blur(8px);position:sticky;top:0;z-index:100;}
.logo{font-size:1.5rem;font-weight:700;color:#ffb100;}
nav a{color:#ccc;text-decoration:none;margin-left:20px;transition:color .3s;}
nav a:hover{color:#fff;}
.user-info{color:#ffb100;font-weight:500;margin-left:10px;}
.search-container{flex:1 1 100%;display:flex;justify-content:center;margin-top:10px;}
.search-container form{width:60%;max-width:420px;display:flex;}
#movieSearch{flex:1;padding:10px 14px;border-radius:20px 0 0 20px;border:none;outline:none;background:#2a2a2d;color:#fff;}
.search-btn{padding:10px 16px;background:#ffb100;border:none;border-radius:0 20px 20px 0;color:#000;font-weight:600;cursor:pointer;}
.search-btn:hover{background:#ffd75a;}

/* HERO */
.hero{position:relative;height:70vh;display:flex;align-items:flex-end;justify-content:flex-start;background-size:cover;background-position:center;animation:heroFade 1.5s ease-in-out;}
.hero::before{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(14,14,16,1)20%,rgba(14,14,16,0.2)80%);}
.hero-content{position:relative;z-index:2;padding:60px;max-width:600px;}
.hero-content h1{font-size:2.8rem;color:#ffb100;margin-bottom:10px;}
.hero-content p{color:#ccc;margin-bottom:25px;}
.hero .btn{background:#ffb100;color:#000;padding:12px 25px;border-radius:8px;font-weight:600;text-decoration:none;transition:.3s;}
.hero .btn:hover{background:#ffd75a;transform:translateY(-3px);}

/* MOVIES GRID */
.section{padding:60px;}
.section h2{margin-bottom:25px;font-size:1.8rem;color:#ffb100;position:relative;}
.section h2::after{content:"";position:absolute;left:0;bottom:-6px;width:60px;height:3px;background:#ffb100;}
.movies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
  justify-items: center;
}

.movie-card {
  background: #1a1a1d;
  border-radius: 12px;
  overflow: hidden;
  transition: 0.3s;
  box-shadow: 0 0 12px rgba(255, 177, 0, 0.05);
  width: 250px; 
}

.movie-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 0 18px rgba(255, 177, 0, 0.2);
}

.movie-card img {
  width: 100%;
  height: 260px;
  object-fit: cover;
}

.movie-info{padding:15px;}
.movie-info h3{margin-bottom:5px;color:#fff;}
.movie-info p{color:#aaa;font-size:.9rem;}
.rating{color:#ffb100;}
.movie-actions{display:flex;justify-content:space-around;align-items:center;margin-top:10px;}
.movie-actions a{color:#ffb100;font-size:1.3rem;transition:.2s;}
.movie-actions a:hover{color:#ffd75a;transform:scale(1.2);}

/* CAROUSEL */
.carousel-container{position:relative;overflow:hidden;padding:40px 0;}
.carousel-track{display:flex;transition:transform 0.6s ease;}
.carousel-card{min-width:25%;padding:10px;}
.carousel-item{background:#1a1a1d;border-radius:14px;overflow:hidden;transition:.3s;}
.carousel-item:hover{transform:translateY(-6px);box-shadow:0 0 18px rgba(255,177,0,0.2);}
.carousel-item img{width:100%;height:200px;object-fit:cover;}
.carousel-item-content{padding:15px;}
.carousel-item-content h3{color:#ffb100;margin-bottom:5px;}
.carousel-item-content p{font-size:.9rem;color:#ccc;}
.carousel-item-content a{display:inline-block;margin-top:8px;padding:8px 14px;background:#ffb100;color:#000;font-weight:600;border-radius:6px;text-decoration:none;transition:.3s;}
.carousel-item-content a:hover{background:#ffd75a;}
.carousel-btn{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,177,0,0.8);border:none;padding:10px;border-radius:50%;cursor:pointer;z-index:10;}
.carousel-btn:hover{background:#ffd75a;}
.prev{left:15px;} .next{right:15px;}

/* ANIMATIONS */
@keyframes heroFade{from{opacity:0;transform:scale(1.05);}to{opacity:1;transform:scale(1);}}

/* RESPONSIVE */
@media(max-width:768px){
  .carousel-card{min-width:80%;}
  .section{padding:40px 25px;}
  .hero{height:55vh;}
  .hero-content{padding:30px;}
}
</style>
</head>
<body>

<header>
  <div class="logo">🎥 FlixCinema</div>
  <nav>
    <a href="home.php">Home</a>
    <a href="my_bookings.php">My Bookings</a>
    <a href="about_us.php">About Us</a>
    <a href="../auth/logout.php">Logout</a>
  </nav>
  <div class="user-info">Hello, <?= htmlspecialchars($user_email) ?></div>
  <div class="search-container">
    <form method="GET" action="home.php">
      <input type="text" id="movieSearch" name="search" placeholder="Search movies..." value="<?= htmlspecialchars($search_query) ?>">
      <button type="submit" class="search-btn">Search</button>
    </form>
  </div>
</header>

<?php if (!empty($search_query)): ?>
  <!-- SEARCH RESULTS -->
  <section class="section">
    <h2>Search Results for "<?= htmlspecialchars($search_query) ?>"</h2>
    <div class="movies-grid">
      <?= $movies_html ?: "<p style='color:#888;'>No movies found.</p>" ?>
    </div>
  </section>
<?php else: ?>
  <!-- NORMAL HOME PAGE -->
  <section class="hero" style="background-image:url('<?= $hero_movie['image'] ?>');">
    <div class="hero-content">
      <h1><?= htmlspecialchars($hero_movie['title']) ?></h1>
      <p><?= htmlspecialchars(substr($hero_movie['desc'],0,180)) ?>...</p>
      <?php if ($hero_movie['id']): ?>
        <a href="book_movie.php?movie_id=<?= $hero_movie['id'] ?>" class="btn">🎟️ Book Now</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="section">
    <h2>Now Showing</h2>
    <div class="movies-grid">
      <?= $movies_html ?>
    </div>
  </section>

  <section class="section">
    <h2>🎞️ Movie Timetable</h2>
    <div class="carousel-container">
      <button class="carousel-btn prev">◀</button>
      <div class="carousel-track">
        <?php
        if ($tstmt && $tstmt->num_rows > 0) {
          while ($row = $tstmt->fetch_assoc()) {
            $movie_id = (int)$row['movie_id'];
            $title = htmlspecialchars($row['title']);
            $desc = htmlspecialchars(substr($row['description'], 0, 120));
            $poster = htmlspecialchars($row['image']);
            $poster_path = "../uploads/" . $poster;
            if (!file_exists($poster_path) || empty($poster)) $poster_path = "../uploads/placeholder.jpg";
            echo "
            <div class='carousel-card'>
              <div class='carousel-item'>
                <img src='$poster_path' alt='$title'>
                <div class='carousel-item-content'>
                  <h3>$title</h3>
                  <p>$desc...</p>
                  <a href='view_movie.php?movie_id=$movie_id'>View Movie</a>
                </div>
              </div>
            </div>";
          }
        } else {
          echo '<p style=\"color:#888;\">No movies available.</p>';
        }
        ?>
      </div>
      <button class="carousel-btn next">▶</button>
    </div>
  </section>
<?php endif; ?>

<script>
const track=document.querySelector('.carousel-track');
if(track){
  const nextBtn=document.querySelector('.next');
  const prevBtn=document.querySelector('.prev');
  let index=0;
  nextBtn.addEventListener('click',()=>{
    const cards=document.querySelectorAll('.carousel-card').length;
    const maxIndex=Math.ceil(cards/4)-1;
    if(index<maxIndex) index++;
    track.style.transform=`translateX(-${index*100}%)`;
  });
  prevBtn.addEventListener('click',()=>{
    if(index>0) index--;
    track.style.transform=`translateX(-${index*100}%)`;
  });
}
</script>

<?php include "../components/footer.php"; ?>
</body>
</html>
