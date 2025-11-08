<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_email = $_SESSION['name'] ?? 'Guest';
?>
<header>
  <div class="logo">🎥 FlixCinema</div>

  <nav>
    <a href="home.php">Home</a>
    <a href="my_bookings.php">My Bookings</a>
    <a href="about_us.php">About Us</a>
    <a href="../auth/logout.php">Logout</a>
  </nav>

  <div class="user-info">Hello, <?= htmlspecialchars($user_email) ?></div>

  <!-- Search -->
  <div class="search-container">
    <input type="text" id="movieSearch" placeholder="Search movies..." aria-label="Search movies">
  </div>
</header>

<style>
/* Header + Search styles (scoped to header only) */
header {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 60px;
  background:rgba(20,20,20,0.8);
  backdrop-filter:blur(8px);
  position:sticky; top:0; z-index:100;
  gap:16px; flex-wrap:wrap;
}
.logo { font-size:1.5rem; font-weight:700; color:#ffb100; }
nav a {
  color:#ccc; text-decoration:none; margin-left:20px; transition:color 0.3s;
}
nav a:hover { color:#fff; }
.user-info { color:#ffb100; font-weight:500; }

.search-container {
  flex:1 1 100%;
  display:flex; justify-content:center; margin-top:0px;
}
#movieSearch {
  width:60%; max-width:420px;
  padding:10px 14px;
  border-radius:20px; border:none; outline:none;
  background:#2a2a2d; color:#fff; font-size:0.95rem;
  transition: box-shadow 0.25s ease;
}
#movieSearch:focus { box-shadow:0 0 8px #ffb100; }

@media (max-width:768px) {
  header { padding:15px 20px; align-items:flex-start; }
  .search-container { justify-content:flex-start; width:100%; }
  #movieSearch { width:100%; max-width:none; }
}
</style>
