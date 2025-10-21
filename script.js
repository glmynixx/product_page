let isNavigating = false; // flag to prevent recursive calls

document.addEventListener('DOMContentLoaded', function () {
  const navLinks = document.querySelectorAll('.nav-link[data-page]');
  const sections = document.querySelectorAll('.page-content');
  const frontSection = document.querySelector('.front');
  const currentCategory = new URLSearchParams(window.location.search).get('category') || null;

  // function to show section
  window.showSection = function (category) {
    if (isNavigating) return; // Prevent recursion
    isNavigating = true;

    // hide front section for categories
    if (frontSection) frontSection.style.display = 'none';

    // hide all sections
    sections.forEach(section => section.style.display = 'none');

    // show target section
    const targetSection = document.getElementById(category);
    if (targetSection) targetSection.style.display = 'block';

    // update active nav
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.dataset.page === category) {
        link.classList.add('active');
      }
    });

    // update URL if different
    const currentUrl = new URL(window.location);
    if (currentUrl.searchParams.get('category') !== category) {
      currentUrl.searchParams.set('category', category);
      window.history.pushState({}, '', currentUrl);
    }

    // reset flag
    setTimeout(() => {
      isNavigating = false;
    }, 100);
  };

  // initial load
  if (currentCategory && currentCategory !== 'home') {
    if (frontSection) frontSection.style.display = 'none';
    showSection(currentCategory);
  } else {
    if (frontSection) frontSection.style.display = 'block';
    sections.forEach(section => section.style.display = 'none');
  }

  // intercept nav clicks
  navLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const category = this.dataset.page;
      if (category !== 'cart' && category !== 'profile') {
        showSection(category);
      }
    });
  });

  // handle browser back/forward
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

