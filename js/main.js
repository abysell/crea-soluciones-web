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

  // 5. INTERACTIVE TABS
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabPanes = document.querySelectorAll('.tab-pane');

  if (tabBtns.length > 0) {
    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        // Remove active class from all
        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));

        // Add active class to clicked
        btn.classList.add('active');
        const targetId = btn.getAttribute('data-target');
        const targetPane = document.getElementById(targetId);
        if (targetPane) {
          targetPane.classList.add('active');
        }
      });
    });
  }

  // 6. MAP SLIDER AUTO-SCROLL
  const mapSliders = document.querySelectorAll('.map-slider');
  mapSliders.forEach(slider => {
    let scrollAmount = 0;
    let isUserInteracting = false;
    
    // Eventos táctiles para pausar temporalmente auto-scroll
    slider.addEventListener('mouseenter', () => isUserInteracting = true);
    slider.addEventListener('mouseleave', () => isUserInteracting = false);
    slider.addEventListener('touchstart', () => isUserInteracting = true);
    slider.addEventListener('touchend', () => { setTimeout(() => isUserInteracting = false, 2000); });

    // Funcionalidad de las Flechas Next / Prev
    const wrapper = slider.closest('.tech-item');
    if (wrapper) {
      const prevBtn = wrapper.querySelector('.slider-prev');
      const nextBtn = wrapper.querySelector('.slider-next');

      if (prevBtn) {
        prevBtn.addEventListener('click', () => {
          isUserInteracting = true; // pausar slider temporalmente mientras se cliquea
          scrollAmount -= slider.clientWidth;
          if (scrollAmount < 0) {
            scrollAmount = slider.scrollWidth - slider.clientWidth;
          }
          slider.scrollTo({ left: scrollAmount, behavior: 'smooth' });
          setTimeout(() => isUserInteracting = false, 5000); // Reanudar tras 5s
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', () => {
          isUserInteracting = true; // pausar slider temporalmente mientras se cliquea
          scrollAmount += slider.clientWidth;
          if (Math.round(scrollAmount) >= slider.scrollWidth) {
            scrollAmount = 0;
          }
          slider.scrollTo({ left: scrollAmount, behavior: 'smooth' });
          setTimeout(() => isUserInteracting = false, 5000); // Reanudar tras 5s
        });
      }
    }

    setInterval(() => {
      if(isUserInteracting) return;
      
      scrollAmount += slider.clientWidth;
      // Si llegamos al final, resetear al cero
      if (Math.round(scrollAmount) >= slider.scrollWidth) {
        scrollAmount = 0;
      }
      
      slider.scrollTo({
        left: scrollAmount,
        behavior: 'smooth'
      });
    }, 5000); // Cambia cada 5 segundos
  });

});
