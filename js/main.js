document.addEventListener('DOMContentLoaded', () => {

  // 1. GSAP & SCROLLTRIGGER REGISTRATION
  gsap.registerPlugin(ScrollTrigger, TextPlugin);

  // 2. HEADER SCROLL STATE
  const header = document.getElementById('main-header');
  
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  // 3. GSAP ANIMATIONS
  const fadeUpElements = document.querySelectorAll('.gsap-fade-up');

  fadeUpElements.forEach(element => {
    gsap.from(element, {
      scrollTrigger: {
        trigger: element,
        start: 'top 85%',
        toggleActions: 'play none none reverse'
      },
      y: 40,
      opacity: 0,
      duration: 1,
      ease: 'power3.out'
    });
  });

  // Typewriter Effect (Value Prop)
  gsap.to("#typewriter-title", {
    text: {
      value: "Soluciones que facilitan<br>el éxito de tu inversión.",
      delimiter: ""
    },
    duration: 2.5,
    ease: "none",
    scrollTrigger: {
      trigger: ".value-prop-premium",
      start: "top 70%",
    }
  });

  // Hero Parallax
  gsap.to('.hero-bg', {
    scrollTrigger: {
      trigger: '.hero',
      start: 'top top',
      end: 'bottom top',
      scrub: true
    },
    y: 150,
    ease: 'none'
  });

  // Methodology Parallax
  gsap.to('.methodology-bg', {
    scrollTrigger: {
      trigger: '.methodology-premium',
      start: 'top bottom',
      end: 'bottom top',
      scrub: true
    },
    y: 200,
    ease: 'none'
  });

  // 4. MODAL LOGIC
  const modal = document.getElementById('lead-modal');
  const openModalBtns = document.querySelectorAll('.toggle-modal');
  const closeModalBtn = document.getElementById('close-modal');

  openModalBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
  };

  if(closeModalBtn) {
    closeModalBtn.addEventListener('click', closeModal);
  }

  if(modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
      closeModal();
    }
  });

});
