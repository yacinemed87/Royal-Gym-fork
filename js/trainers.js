// Wait until page is loaded
document.addEventListener("DOMContentLoaded", () => {

  // -------------------------------
  // 1. Animation on scroll
  // -------------------------------
  const trainers = document.querySelectorAll(".trainer-container article");

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }
    });
  }, { threshold: 0.3 });

  trainers.forEach(trainer => {
    trainer.classList.add("hidden");
    observer.observe(trainer);
  });


  // -------------------------------
  // 2. Click on "View Full Profile"
  // -------------------------------
  const buttons = document.querySelectorAll(".trainer-container a");

  buttons.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();

      const trainerCard = btn.closest("article");
      const name = trainerCard.querySelector("figcaption").textContent;
      const specialty = trainerCard.querySelector("h3").textContent;

      alert(`Trainer: ${name}\n${specialty}`);
    });
  });


  // -------------------------------
  // 3. Hover effect (extra style via JS)
  // -------------------------------
  trainers.forEach(card => {
    card.addEventListener("mouseenter", () => {
      card.style.transform = "scale(1.05)";
      card.style.transition = "0.3s";
    });

    card.addEventListener("mouseleave", () => {
      card.style.transform = "scale(1)";
    });
  });

});