// search.js

// Wait until DOM is fully loaded before running
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const servicesContainer = document.getElementById('servicesContainer'); // Assuming you have this container
    const cards = document.querySelectorAll('.service-card'); // Replace '.service-card' with your card class
  
    searchInput.addEventListener('input', function () {
      const query = this.value.toLowerCase();
      let visibleCount = 0;
  
      cards.forEach(card => {
        const name = card.querySelector('.service-name').textContent.toLowerCase();
        if (name.includes(query)) {
          card.style.display = "block";
          visibleCount++;
        } else {
          card.style.display = "none";
        }
      });
  
      if (visibleCount === 0) {
        if (!document.getElementById('noResult')) {
          const noResult = document.createElement('p');
          noResult.id = 'noResult';
          noResult.className = 'text-gray-500 col-span-3 text-center';
          noResult.textContent = 'No matching services found.';
          servicesContainer.appendChild(noResult);
        }
      } else {
        const existing = document.getElementById('noResult');
        if (existing) existing.remove();
      }
    });
  });
  


  