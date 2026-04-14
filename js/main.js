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

  // 2.5 MOBILE MENU LOGIC
  const mobileToggle = document.getElementById('mobile-toggle');
  const navLinks = document.querySelector('.nav-links');
  
  if (mobileToggle && navLinks) {
    // Dropdowns logic in mobile
    const dropdowns = navLinks.querySelectorAll('.dropdown, .sub-dropdown');
    dropdowns.forEach(dropdown => {
      const btn = dropdown.querySelector('.dropbtn, .sub-dropbtn');
      if (btn) {
        btn.addEventListener('click', (e) => {
          if (window.innerWidth <= 1024) {
            e.preventDefault();
            dropdown.classList.toggle('active');
          }
        });
      }
    });

    // Clone header action buttons into mobile menu
    const headerActions = document.querySelector('.header-actions');
    if (headerActions) {
      const mobileActionsContainer = document.createElement('div');
      mobileActionsContainer.className = 'mobile-header-btn-container';
      mobileActionsContainer.innerHTML = headerActions.innerHTML;
      navLinks.appendChild(mobileActionsContainer);
    }

    mobileToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      const icon = mobileToggle.querySelector('i');
      if (icon) {
        if (navLinks.classList.contains('active')) {
          icon.classList.remove('ph-list');
          icon.classList.add('ph-x');
        } else {
          icon.classList.remove('ph-x');
          icon.classList.add('ph-list');
        }
      }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
      if (navLinks.classList.contains('active') && !e.target.closest('.header-container')) {
        navLinks.classList.remove('active');
        const icon = mobileToggle.querySelector('i');
        if (icon) {
          icon.classList.remove('ph-x');
          icon.classList.add('ph-list');
        }
      }
    });
    
    // Close mobile menu when clicking on a standard link
    const regularLinks = navLinks.querySelectorAll('a:not(.dropbtn):not(.sub-dropbtn)');
    regularLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 1024) {
          navLinks.classList.remove('active');
          const icon = mobileToggle.querySelector('i');
          if (icon) {
            icon.classList.remove('ph-x');
            icon.classList.add('ph-list');
          }
        }
      });
    });
  }

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
  // 7. FOOTER FORM VALIDATION
  const footerForm = document.getElementById("form-footer");
  if (footerForm) {
    footerForm.addEventListener("submit", function(e) {
      let isValid = true;
      
      const fields = [
        { id: "form-name-footer", errorId: "error-name-footer" },
        { id: "form-phone-footer", errorId: "error-phone-footer" },
        { id: "form-service-footer", errorId: "error-service-footer" },
        { id: "form-stage-footer", errorId: "error-stage-footer" }
      ];

      fields.forEach(field => {
        const input = document.getElementById(field.id);
        const error = document.getElementById(field.errorId);
        if (input && error) {
            if (!input.value || input.value.trim() === "") {
              isValid = false;
              input.style.borderColor = "#ff6b6b";
              error.style.display = "block";
            } else {
              input.style.borderColor = "rgba(255,255,255,0.2)";
              error.style.display = "none";
            }
        }
      });

      const email = document.getElementById("form-email-footer");
      const emailError = document.getElementById("error-email-footer");
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && emailError) {
          if (!email.value || !emailRegex.test(email.value)) {
            isValid = false;
            email.style.borderColor = "#ff6b6b";
            emailError.style.display = "block";
          } else {
            email.style.borderColor = "rgba(255,255,255,0.2)";
            emailError.style.display = "none";
          }
      }

      if (!isValid) {
        e.preventDefault();
      }
    });

    const allFooterInputs = footerForm.querySelectorAll("input, select");
    allFooterInputs.forEach(input => {
      input.addEventListener("input", () => {
        input.style.borderColor = "rgba(255,255,255,0.2)";
        const error = document.getElementById("error-" + input.name + "-footer");
        if(error) error.style.display = "none";
      });
    });
  }

  // 8. INFORMES FORM VALIDATION
  const informeForm = document.getElementById("form-informes");
  if (informeForm) {
    informeForm.addEventListener("submit", function(e) {
      let isValid = true;
      
      const fields = [
        { id: "form-name-informe", errorId: "error-name-informe" },
        { id: "form-company-informe", errorId: "error-company-informe" }
      ];

      fields.forEach(field => {
        const input = document.getElementById(field.id);
        const error = document.getElementById(field.errorId);
        if (input && error) {
            if (!input.value || input.value.trim() === "") {
              isValid = false;
              input.style.borderColor = "#ff6b6b";
              error.style.display = "block";
            } else {
              input.style.borderColor = "rgba(0,0,0,0.1)"; 
              error.style.display = "none";
            }
        }
      });

      const email = document.getElementById("form-email-informe");
      const emailError = document.getElementById("error-email-informe");
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (email && emailError) {
          if (!email.value || !emailRegex.test(email.value)) {
            isValid = false;
            email.style.borderColor = "#ff6b6b";
            emailError.style.display = "block";
          } else {
            email.style.borderColor = "rgba(0,0,0,0.1)";
            emailError.style.display = "none";
          }
      }

      if (!isValid) {
        e.preventDefault();
      }
    });

    const allInformeInputs = informeForm.querySelectorAll("input");
    allInformeInputs.forEach(input => {
      input.addEventListener("input", () => {
        input.style.borderColor = "rgba(0,0,0,0.1)";
        const error = document.getElementById("error-" + input.name + "-informe");
        if(error) error.style.display = "none";
      });
    });
  }

});
