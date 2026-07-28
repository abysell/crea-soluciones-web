/* ============================================================
   SEGURIDAD DE FORMULARIOS — reCAPTCHA v3 + trampa de tiempo
   ------------------------------------------------------------
   Pega aquí la SITE KEY (llave pública) de reCAPTCHA v3.
   La SECRET KEY va en config.php, nunca en este archivo.
   Mientras el valor siga en PENDIENTE_SITE_KEY, reCAPTCHA no
   se carga y los formularios funcionan con honeypot y filtros.
   ============================================================ */
const RECAPTCHA_SITE_KEY = "PENDIENTE_SITE_KEY";

// Momento en que se cargó la página, para medir cuánto tardó el llenado.
const CS_INICIO_PAGINA = Date.now();

const csRecaptchaConfigurado = () =>
  typeof RECAPTCHA_SITE_KEY === "string" &&
  RECAPTCHA_SITE_KEY.length > 0 &&
  RECAPTCHA_SITE_KEY.indexOf("PENDIENTE") === -1;

/** Inserta el script de reCAPTCHA una sola vez y solo si hay formularios en la página. */
function csCargarRecaptcha() {
  if (!csRecaptchaConfigurado()) return;
  if (document.querySelector('script[src*="recaptcha/api.js"]')) return;

  const script = document.createElement("script");
  script.src = "https://www.google.com/recaptcha/api.js?render=" + RECAPTCHA_SITE_KEY;
  script.async = true;
  script.defer = true;
  document.head.appendChild(script);
}

/** Crea o actualiza un input oculto dentro del formulario. */
function csCampoOculto(form, nombre, valor) {
  let campo = form.querySelector('input[name="' + nombre + '"][type="hidden"]');
  if (!campo) {
    campo = document.createElement("input");
    campo.type = "hidden";
    campo.name = nombre;
    form.appendChild(campo);
  }
  campo.value = valor;
}

/* ============================================================
   ATRIBUCIÓN DE CAMPAÑA (UTMs + Google Ads)
   ------------------------------------------------------------
   Los parámetros solo existen en la URL de aterrizaje: en cuanto
   la persona navega a otra página, se pierden. Por eso se guardan
   al llegar y se recuperan al enviar el formulario, que suele
   ocurrir varias páginas después.

   Se usa sessionStorage y no localStorage: el dato vive solo
   mientras la pestaña siga abierta y se borra al cerrarla. No
   queda nada almacenado en el equipo del visitante entre visitas.
   ============================================================ */

const CS_ATRIBUCION_CLAVES = [
  "utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content", "gclid"
];

const CS_ATRIBUCION_ALMACEN = "cs_atribucion";

/**
 * Lee los parámetros de la URL actual y los guarda si hay alguno.
 *
 * Gana la última campaña: si alguien llega por un anuncio nuevo, ese clic
 * describe mejor su intención actual que uno anterior.
 */
function csCapturarAtribucion() {
  let params;
  try {
    params = new URLSearchParams(window.location.search);
  } catch (e) {
    return;
  }

  const encontrados = {};

  CS_ATRIBUCION_CLAVES.forEach((clave) => {
    const valor = params.get(clave);
    if (valor) {
      encontrados[clave] = valor.slice(0, 200);
    }
  });

  // wbraid y gbraid sustituyen al gclid cuando el clic viene de campañas
  // afectadas por las restricciones de privacidad de iOS.
  if (!encontrados.gclid) {
    ["wbraid", "gbraid"].forEach((clave) => {
      const valor = params.get(clave);
      if (valor && !encontrados.gclid) {
        encontrados.gclid = valor.slice(0, 200);
      }
    });
  }

  if (Object.keys(encontrados).length === 0) {
    return;
  }

  try {
    sessionStorage.setItem(CS_ATRIBUCION_ALMACEN, JSON.stringify(encontrados));
  } catch (e) {
    // Navegación privada o almacenamiento bloqueado: se sigue sin atribución.
  }
}

/**
 * Devuelve la atribución de esta pestaña, o un objeto vacío si no hay.
 *
 * No hace falta comprobar caducidad: sessionStorage se vacía solo al cerrar
 * la pestaña, así que el dato nunca sobrevive a la visita.
 */
function csAtribucionGuardada() {
  try {
    const crudo = sessionStorage.getItem(CS_ATRIBUCION_ALMACEN);
    return crudo ? (JSON.parse(crudo) || {}) : {};
  } catch (e) {
    return {};
  }
}

/** Adjunta la atribución al formulario como campos ocultos. */
function csAdjuntarAtribucion(form) {
  const datos = csAtribucionGuardada();
  CS_ATRIBUCION_CLAVES.forEach((clave) => {
    csCampoOculto(form, clave, datos[clave] || "");
  });
}

// Se ejecuta de inmediato, sin esperar a DOMContentLoaded: cuanto antes se
// capture, menor el riesgo de perder el dato por una redirección temprana.
csCapturarAtribucion();

/**
 * Devuelve los botones de envío a su estado normal.
 *
 * Hace falta porque al enviar se deshabilita el botón y se le pone
 * "Enviando...". Si el servidor rechaza el envío y la persona vuelve atrás,
 * el navegador restaura la página desde el bfcache tal como quedó: con el
 * botón deshabilitado para siempre. Este evento la devuelve a su estado útil.
 */
function csRestaurarBotones() {
  document.querySelectorAll("[data-cs-texto-original]").forEach((boton) => {
    boton.innerHTML = boton.dataset.csTextoOriginal;
    boton.disabled = false;
    delete boton.dataset.csTextoOriginal;
  });
}

// pageshow cubre tanto la carga normal como la restauración desde bfcache
// (a diferencia de DOMContentLoaded, que no se dispara al volver atrás).
window.addEventListener("pageshow", csRestaurarBotones);

/**
 * Adjunta los datos de seguridad y envía el formulario.
 * Se llama solo cuando la validación en pantalla ya pasó.
 *
 * @param {HTMLFormElement} form
 * @param {string} accion  "contacto" | "informe" (debe coincidir con el PHP)
 */
function csEnviarConRecaptcha(form, accion) {
  const boton = form.querySelector('button[type="submit"], input[type="submit"]');

  const enviar = (token) => {
    csCampoOculto(form, "cs_elapsed", String((Date.now() - CS_INICIO_PAGINA) / 1000));
    csCampoOculto(form, "recaptcha_token", token || "");
    csAdjuntarAtribucion(form);
    // form.submit() no vuelve a disparar el evento submit: no hay bucle.
    form.submit();
  };

  if (boton) {
    // El texto se guarda en el DOM, no en una variable: al volver atrás el
    // navegador restaura la página desde el bfcache y las variables de esta
    // función ya no existen, pero el atributo sí. Ver csRestaurarBotones().
    boton.dataset.csTextoOriginal = boton.innerHTML;
    boton.disabled = true;
    boton.innerHTML = "Enviando...";
  }

  const restaurarBoton = () => csRestaurarBotones();

  if (!csRecaptchaConfigurado() || typeof grecaptcha === "undefined") {
    enviar("");
    return;
  }

  // Red de seguridad: si Google no responde en 8 s, se envía sin token
  // y el servidor decide según CS_RECAPTCHA_FAIL_OPEN.
  let resuelto = false;
  const respaldo = setTimeout(() => {
    if (!resuelto) {
      resuelto = true;
      enviar("");
    }
  }, 8000);

  grecaptcha.ready(() => {
    grecaptcha
      .execute(RECAPTCHA_SITE_KEY, { action: accion })
      .then((token) => {
        if (resuelto) return;
        resuelto = true;
        clearTimeout(respaldo);
        enviar(token);
      })
      .catch(() => {
        if (resuelto) return;
        resuelto = true;
        clearTimeout(respaldo);
        restaurarBoton();
        enviar("");
      });
  });
}

document.addEventListener('DOMContentLoaded', () => {

  // 1. GSAP & SCROLLTRIGGER REGISTRATION
  gsap.registerPlugin(ScrollTrigger, TextPlugin);

  // 1.5 CARGA DE RECAPTCHA (solo si la página tiene formularios)
  if (document.querySelector("#form-footer, #form-contacto, #form-informes")) {
    csCargarRecaptcha();
  }

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

      // Los dos recursos comparten el mismo modal, así que hay que registrar
      // cuál se pidió. El botón lo declara en data-informe y aquí viaja al
      // campo oculto del formulario. Solo se manda la clave: el título para el
      // correo lo resuelve el servidor con el catálogo de config.php.
      const campoInforme = document.getElementById('form-informe-tipo');
      if (campoInforme) {
        campoInforme.value = btn.dataset.informe || '';
      }

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

      // El envío siempre se detiene aquí: reCAPTCHA v3 necesita generar el
      // token antes de mandar el POST. csEnviarConRecaptcha() lo reanuda.
      e.preventDefault();
      if (isValid) {
        csEnviarConRecaptcha(footerForm, "contacto");
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

  // 7.5 CONTACT PAGE FORM VALIDATION (contacto.html y sectores.html)
  // Este formulario no tenía validación propia: se enviaba sin revisar nada.
  const contactoForm = document.getElementById("form-contacto");
  if (contactoForm) {
    contactoForm.addEventListener("submit", function(e) {
      let isValid = true;

      const fields = [
        { id: "form-name", errorId: "error-name" },
        { id: "form-phone", errorId: "error-phone" },
        { id: "form-service", errorId: "error-service" },
        { id: "form-stage", errorId: "error-stage" }
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

      const email = document.getElementById("form-email");
      const emailError = document.getElementById("error-email");
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

      e.preventDefault();
      if (isValid) {
        csEnviarConRecaptcha(contactoForm, "contacto");
      }
    });

    const allContactoInputs = contactoForm.querySelectorAll("input, select, textarea");
    allContactoInputs.forEach(input => {
      input.addEventListener("input", () => {
        input.style.borderColor = "rgba(0,0,0,0.1)";
        const error = document.getElementById("error-" + input.name);
        if (error) error.style.display = "none";
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

      // Mismo motivo que en el formulario del footer: se detiene el envío
      // para adjuntar el token de reCAPTCHA v3.
      e.preventDefault();
      if (isValid) {
        csEnviarConRecaptcha(informeForm, "informe");
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

  // 9. SMOOTH SCROLL & HASH HANDLING WITH HEADER OFFSET
  const handleAnchorScroll = (hash) => {
    const target = document.querySelector(hash);
    if (target) {
      const headerOffset = document.getElementById('main-header')?.offsetHeight || 80;
      const targetPosition = target.getBoundingClientRect().top + window.scrollY;
      window.scrollTo({
        top: targetPosition - headerOffset,
        behavior: 'smooth'
      });
    }
  };

  // Handle cross-page hash on load
  if (window.location.hash) {
    // Timeout to allow GSAP and page rendering to settle
    setTimeout(() => {
      handleAnchorScroll(window.location.hash);
    }, 400);
  }

  // Handle in-page anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      // On mobile, dropdown/sub-dropdown toggles just expand their submenu;
      // they shouldn't also scroll and close the mobile menu.
      if (window.innerWidth <= 1024 && this.matches('.dropbtn, .sub-dropbtn')) {
        return;
      }

      const href = this.getAttribute('href');
      if (href !== '#') {
        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          handleAnchorScroll(href);
          history.pushState(null, null, href);
          
          // Close mobile menu if open
          const navLinks = document.querySelector('.nav-links');
          const mobileToggle = document.getElementById('mobile-toggle');
          if (navLinks && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            const icon = mobileToggle.querySelector('i');
            if (icon) {
              icon.classList.remove('ph-x');
              icon.classList.add('ph-list');
            }
          }
        }
      }
    });
  });

});
