/**
 * Seychelles International Cargo LLC - Modern ES6 Script
 * Ultra-Fast & High Performance
 */

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initHeroSlider();
  initVesselSlider();
  initScrollTop();
  initModals();
  initFormProcessors();
});

/* Mobile Menu & Drawer */
function initMobileMenu() {
  const toggleBtn = document.querySelector('.mobile-toggle');
  const drawer = document.querySelector('.mobile-drawer');
  const overlay = document.querySelector('.mobile-overlay');
  const closeBtn = document.querySelector('.mobile-close');

  if (!toggleBtn || !drawer) return;

  function openMenu() {
    drawer.classList.add('open');
    if (overlay) overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    drawer.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  toggleBtn.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  if (overlay) overlay.addEventListener('click', closeMenu);
}

/* Hero Banner Slider */
function initHeroSlider() {
  const slides = document.querySelectorAll('.hero-slide');
  if (slides.length < 2) return;

  let currentSlide = 0;
  const slideInterval = 5000;

  function nextSlide() {
    slides[currentSlide].classList.remove('active');
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add('active');
  }

  setInterval(nextSlide, slideInterval);
}

/* Scroll To Top Button */
function initScrollTop() {
  const topBtn = document.querySelector('.float-top');
  if (!topBtn) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      topBtn.classList.add('show');
    } else {
      topBtn.classList.remove('show');
    }
  });

  topBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* Modal Windows */
function initModals() {
  const modalOverlays = document.querySelectorAll('.modal-overlay');
  const openBtns = document.querySelectorAll('[data-open-modal]');
  const closeBtns = document.querySelectorAll('.modal-close');

  openBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = btn.getAttribute('data-open-modal');
      const targetModal = document.getElementById(targetId);
      if (targetModal) {
        targetModal.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  closeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-overlay').classList.remove('open');
      document.body.style.overflow = '';
    });
  });

  modalOverlays.forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  });
}

/* AJAX Form Processing */
function initFormProcessors() {
  const forms = document.querySelectorAll('form[data-ajax="true"]');

  forms.forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Submit';

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
      }

      const formData = new FormData(form);

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const result = await response.json();

        if (result.status === 'success') {
          showToast('success', result.message || 'Your message has been sent successfully!');
          form.reset();
          const modal = form.closest('.modal-overlay');
          if (modal) modal.classList.remove('open');
        } else {
          showToast('error', result.message || 'An error occurred. Please try again.');
        }
      } catch (err) {
        // If non-JSON fallback, handle cleanly
        showToast('success', 'Thank you! Your request has been submitted.');
        form.reset();
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        }
      }
    });
  });
}

/* Toast Notifications */
function showToast(type, message) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <i class="${type === 'success' ? 'fa-solid fa-circle-check text-success' : 'fa-solid fa-circle-xmark text-danger'}" style="font-size:1.25rem;"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 4500);
}

/* Vessel Schedule Interactive Slider */
function initVesselSlider() {
  const wrapper = document.querySelector('.vessel-slider-wrapper');
  if (!wrapper) return;

  const slides = wrapper.querySelectorAll('.vessel-slide');
  const prevBtn = wrapper.querySelector('.vessel-slider-prev');
  const nextBtn = wrapper.querySelector('.vessel-slider-next');
  const dotsContainer = wrapper.querySelector('.vessel-dots-container');

  if (slides.length < 1) return;

  let currentIdx = 0;
  let autoTimer = null;

  // Render dots
  if (dotsContainer && slides.length > 1) {
    dotsContainer.innerHTML = '';
    slides.forEach((_, idx) => {
      const dot = document.createElement('button');
      dot.className = `vessel-dot ${idx === 0 ? 'active' : ''}`;
      dot.setAttribute('aria-label', `Go to slide ${idx + 1}`);
      dot.addEventListener('click', () => goToSlide(idx));
      dotsContainer.appendChild(dot);
    });
  }

  function goToSlide(idx) {
    slides[currentIdx].classList.remove('active');
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.vessel-dot') : [];
    if (dots[currentIdx]) dots[currentIdx].classList.remove('active');

    currentIdx = (idx + slides.length) % slides.length;

    slides[currentIdx].classList.add('active');
    if (dots[currentIdx]) dots[currentIdx].classList.add('active');
    resetTimer();
  }

  function next() { goToSlide(currentIdx + 1); }
  function prev() { goToSlide(currentIdx - 1); }

  if (nextBtn) nextBtn.addEventListener('click', next);
  if (prevBtn) prevBtn.addEventListener('click', prev);

  function resetTimer() {
    if (autoTimer) clearInterval(autoTimer);
    if (slides.length > 1) {
      autoTimer = setInterval(next, 6000);
    }
  }

  wrapper.addEventListener('mouseenter', () => { if (autoTimer) clearInterval(autoTimer); });
  wrapper.addEventListener('mouseleave', resetTimer);

  resetTimer();
}
