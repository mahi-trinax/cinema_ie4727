<?php $year = date('Y'); ?>

<style>
footer {
  background: radial-gradient(circle at top, #1a1a1d 0%, #0b0b0d 100%);
  padding: 35px 20px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #ccc;
  text-align: center;
  border-top: 1px solid #222;
  box-shadow: 0 -4px 10px rgba(255, 177, 0, 0.05);
  position: relative;
}

footer::before {
  content: "";
  position: absolute;
  top: 0; left: 50%;
  transform: translateX(-50%);
  width: 60px; height: 3px;
  background: linear-gradient(90deg, transparent, #ffb100, transparent);
  border-radius: 4px;
}

footer .logo {
  font-size: 1.5rem;
  color: #ffb100;
  font-weight: 700;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}

footer p {
  max-width: 500px;
  margin: 6px auto 16px;
  color: #aaa;
  line-height: 1.5;
  font-size: 0.9rem;
}

footer .contact-info {
  font-size: 0.85rem;
  line-height: 1.6;
  color: #aaa;
}

footer .social-links a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #1e1e21;
  color: #ffb100;
  font-size: 1rem;
  transition: all 0.3s ease;
  border: 1px solid #2a2a2d;
}

footer .social-links a:hover {
  background: #ffb100;
  color: #000;
  transform: translateY(-3px);
  box-shadow: 0 0 10px rgba(255, 177, 0, 0.4);
}

.footer-bottom {
  text-align: center;
  padding: 14px;
  background: #0b0b0d;
  color: #666;
  font-size: 0.85rem;
  border-top: 1px solid #222;
  letter-spacing: 0.4px;
}

@media (max-width: 768px) {
  footer {
    padding: 30px 25px 15px;
  }
  footer p {
    font-size: 0.85rem;
  }
  footer .social-links a {
    width: 30px; height: 30px; font-size: 0.95rem;
  }
}
</style>

<footer>
  <div class="logo">🎥 FlixCinema</div>
  <p>Experience the latest blockbusters and timeless classics,   
     comfort, sound, and style in every seat.</p>

  <div class="contact-info">
    <div>Email: <a href="mailto:support@flixcinema.com" style="color:#ffb100;text-decoration:none;">support@flixcinema.com</a></div>
    <div>Phone: +65 12345678</div>
    <div>Address: 123 Main Street, Singapore</div>
  </div>
</footer>

<div class="footer-bottom">
  © <?= $year ?> FlixCinema — All rights reserved.
</div>

<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a2e0e6ad12.js" crossorigin="anonymous"></script>
