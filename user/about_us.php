<?php include "../components/header.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - FlixCinema</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            gold: '#ffb100',
            dark: '#0e0e10',
            card: '#1a1a1d'
          },
          fontFamily: {
            poppins: ['Poppins', 'sans-serif']
          }
        }
      }
    }
  </script>
</head>
<body class="bg-dark text-white font-poppins">

  <!-- HERO SECTION -->
  <section class="relative h-[65vh] flex items-center justify-center bg-cover bg-center">
    <div class="absolute inset-0 bg-black bg-opacity-70"></div>
    <div class="relative text-center px-6">
      <h1 class="text-4xl md:text-6xl font-bold text-gold mb-4">About FlixCinema</h1>
      <p class="max-w-2xl mx-auto text-gray-300 text-lg leading-relaxed">
        Your go to platform for booking the latest blockbusters, bringing you a seamless and cinematic movie experience.
      </p>
    </div>
  </section>

  <!-- OUR STORY -->
  <section class="max-w-7xl mx-auto px-6 md:px-10 py-20">
    <h2 class="text-3xl font-semibold text-gold border-b-2 border-gold inline-block pb-2 mb-10">Our Story</h2>
    <div class="grid md:grid-cols-2 gap-12 items-center">
      <div class="space-y-5 text-gray-300 leading-relaxed">
        <p>
          FlixCinema was built to make movie booking effortless, intuitive, and visually immersive.
          Designed for both cinema enthusiasts and casual movie goers, it combines the excitement of
          theatre going with the convenience of modern web technology.
        </p>
        <p>
          Every time an admin adds a new movie or timing, it automatically appears on the user’s homepage.
          This dynamic system ensures real time updates without manual editing, enhancing both efficiency and scalability.
        </p>
        <p>
          Our platform also supports feedback submissions, personalized bookings, and secure transactions, all styled with a sleek dark interface inspired by the ambience of a movie theatre.
        </p>
      </div>
    </div>
  </section>

  <!-- WHY CHOOSE US -->
  <section class="bg-card py-20 px-6 md:px-10">
    <div class="max-w-7xl mx-auto text-center">
      <h2 class="text-3xl font-semibold text-gold border-b-2 border-gold inline-block pb-2 mb-12">Why Choose Us?</h2>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-dark p-8 rounded-xl shadow-lg shadow-gold/10 hover:shadow-gold/30 transition">
          <h3 class="text-xl font-semibold text-gold mb-3">🎟️ Real-Time Booking</h3>
          <p class="text-gray-300 text-sm">
            Movies, timings, and seats update instantly from the database, no refresh needed.
          </p>
        </div>
        <div class="bg-dark p-8 rounded-xl shadow-lg shadow-gold/10 hover:shadow-gold/30 transition">
          <h3 class="text-xl font-semibold text-gold mb-3">🍿 Immersive Design</h3>
          <p class="text-gray-300 text-sm">
            A cinematic dark theme with golden highlights for a premium visual experience.
          </p>
        </div>
        <div class="bg-dark p-8 rounded-xl shadow-lg shadow-gold/10 hover:shadow-gold/30 transition">
          <h3 class="text-xl font-semibold text-gold mb-3">💬 User Interaction</h3>
          <p class="text-gray-300 text-sm">
            Integrated feedback and rating system to help us continuously improve your experience.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- TEAM SECTION -->
  <section class="max-w-7xl mx-auto py-20 px-6 md:px-10 text-center">
    <h2 class="text-3xl font-semibold text-gold border-b-2 border-gold inline-block pb-2 mb-10">Meet the Team</h2>
    <p class="text-gray-300 max-w-3xl mx-auto mb-14 leading-relaxed">
      FlixCinema is powered by a passionate group of developers and designers dedicated to delivering
      smooth, modern, and intuitive digital cinema experiences.
    </p>

    <div class="grid md:grid-cols-3 gap-10 justify-center">
      <div class="bg-card rounded-xl p-8 shadow-lg shadow-gold/10 hover:shadow-gold/30 transition">
        <img src="../assets/mahi.jpeg" alt="Mahidhar" class="w-28 h-28 rounded-full mx-auto mb-4 border-2 border-gold object-cover">
        <h3 class="text-gold font-semibold text-lg mb-1">Mahidhar Reddy</h3>
        <p class="text-gray-400 text-sm">Lead Developer</p>
      </div>

      <div class="bg-card rounded-xl p-8 shadow-lg shadow-gold/10 hover:shadow-gold/30 transition">
        <img src="../assets/hoexun.jpeg" alt="Hoe Xun" class="w-28 h-28 rounded-full mx-auto mb-4 border-2 border-gold object-cover">
        <h3 class="text-gold font-semibold text-lg mb-1">Hoe Xun</h3>
        <p class="text-gray-400 text-sm">Lead Developer</p>
      </div>

      <div class="bg-card rounded-xl p-8 shadow-lg shadow-gold/10 hover:shadow-gold/30 transition">
        <img src="../assets/prof.png" alt="Prof Hu Xiao" class="w-28 h-28 rounded-full mx-auto mb-4 border-2 border-gold object-cover">
        <h3 class="text-gold font-semibold text-lg mb-1">Prof Hu Xiao</h3>
        <p class="text-gray-400 text-sm">Boss</p>
      </div>
    </div>
  </section>

  <?php include "../components/footer.php"; ?>
</body>
</html>
