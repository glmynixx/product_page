let isNavigating = false; // flag to prevent recursive calls

document.addEventListener('DOMContentLoaded', function () {
  const navLinks = document.querySelectorAll('.nav-link[data-page]');
  const sections = document.querySelectorAll('.page-content');
  const frontSection = document.querySelector('.front');
  const currentCategory = new URLSearchParams(window.location.search).get('category') || null;

  // ✅ Function to show section
  window.showSection = function (category) {
    if (isNavigating) return; // Prevent recursion
    isNavigating = true;

    // Hide front section for categories
    if (frontSection) frontSection.style.display = 'none';

    // Hide all sections
    sections.forEach(section => section.style.display = 'none');

    // Show target section
    const targetSection = document.getElementById(category);
    if (targetSection) targetSection.style.display = 'block';

    // Update active nav
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.dataset.page === category) {
        link.classList.add('active');
      }
    });

    // Update URL if different
    const currentUrl = new URL(window.location);
    if (currentUrl.searchParams.get('category') !== category) {
      currentUrl.searchParams.set('category', category);
      window.history.pushState({}, '', currentUrl);
    }

    // Reset flag
    setTimeout(() => {
      isNavigating = false;
    }, 100);
  };

  // ✅ Initial load
  if (currentCategory && currentCategory !== 'home') {
    if (frontSection) frontSection.style.display = 'none';
    showSection(currentCategory);
  } else {
    if (frontSection) frontSection.style.display = 'block';
    sections.forEach(section => section.style.display = 'none');
  }

  // ✅ Intercept nav clicks
  navLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const category = this.dataset.page;
      if (category !== 'cart' && category !== 'profile') {
        showSection(category);
      }
    });
  });

  // ✅ Handle browser back/forward
  window.addEventListener('popstate', function () {
    if (isNavigating) return;

    const category = new URLSearchParams(window.location.search).get('category') || null;

    if (category && category !== 'home') {
      showSection(category);
    } else {
      if (frontSection) frontSection.style.display = 'block';
      sections.forEach(section => section.style.display = 'none');
    }
  });
});
