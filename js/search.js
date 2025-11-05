// Simple client-side filter for movie cards on the homepage
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("movieSearch");
  if (!searchInput) return;

  const readCards = () =>
    Array.from(document.querySelectorAll(".movies-grid .movie-card"));

  let cards = readCards();

  searchInput.addEventListener("focus", () => {
    cards = readCards();
  });

  const filter = (term) => {
    const q = term.toLowerCase().trim();
    cards.forEach((card) => {
      const title = (card.querySelector("h3")?.innerText || "").toLowerCase();
      const ps = card.querySelectorAll("p");
      const desc = (ps[1]?.innerText || "").toLowerCase();
      const match = title.includes(q) || desc.includes(q);
      card.style.display = match ? "block" : "none";
      if (match) card.style.animation = "fadeIn 0.25s ease";
    });
  };

  // live filter
  searchInput.addEventListener("input", (e) => filter(e.target.value));
});
