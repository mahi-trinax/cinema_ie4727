DROP DATABASE IF EXISTS movie_booking;
CREATE DATABASE movie_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_booking;

-- ---------- USERS ----------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user'
);

INSERT INTO users (name, email, password, role) VALUES
('admin', 'admin@cinema.com', '$2y$10$MNpBSHfcpm5Hk4nZ6YDX3uw35z93u7174XdmuIPSBR1bu.3p/1Sd2', 'admin');

-- ---------- MOVIES ----------
CREATE TABLE movies (
  movie_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  rating VARCHAR(10) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------- MOVIE TIMINGS ----------
CREATE TABLE movie_timings (
  timing_id INT AUTO_INCREMENT PRIMARY KEY,
  movie_id INT NOT NULL,
  date DATE NOT NULL,
  time_block VARCHAR(50) NOT NULL,
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
);

-- ---------- BOOKINGS ----------
CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  movie_id INT NOT NULL,
  timing_id INT NOT NULL,
  num_tickets INT NOT NULL,
  status ENUM('confirmed','cancelled') DEFAULT 'confirmed',
  booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
  FOREIGN KEY (timing_id) REFERENCES movie_timings(timing_id) ON DELETE CASCADE
);

-- ---------- SEATS ----------
CREATE TABLE seats (
  seat_id INT AUTO_INCREMENT PRIMARY KEY,
  row_label CHAR(1) NOT NULL,
  seat_number INT NOT NULL,
  UNIQUE KEY uq_seat (row_label, seat_number)
);

-- Generate simple 8x10 seat grid (Rows A–H, Seats 1–10)
INSERT INTO seats (row_label, seat_number)
SELECT r.lbl, n.num
FROM (SELECT 'A' AS lbl UNION ALL SELECT 'B' UNION ALL SELECT 'C' UNION ALL SELECT 'D'
      UNION ALL SELECT 'E' UNION ALL SELECT 'F' UNION ALL SELECT 'G' UNION ALL SELECT 'H') AS r
JOIN (SELECT 1 AS num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
      UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) AS n;

-- ---------- BOOKING SEATS ----------
CREATE TABLE booking_seats (
  booking_seat_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  timing_id INT NOT NULL,
  seat_id INT NOT NULL,
  UNIQUE KEY uq_timing_seat (timing_id, seat_id),
  FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
  FOREIGN KEY (timing_id) REFERENCES movie_timings(timing_id) ON DELETE CASCADE,
  FOREIGN KEY (seat_id) REFERENCES seats(seat_id) ON DELETE RESTRICT
);

-- ---------- FEEDBACK ----------
CREATE TABLE feedback (
  feedback_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  movie_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  rating INT CHECK (rating BETWEEN 1 AND 5),
  message TEXT NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
