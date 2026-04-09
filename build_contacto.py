import os
import glob

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

# Create contacto.html by extracting header and footer from index.html
with open(os.path.join(directory, 'index.html'), 'r', encoding='utf-8') as f:
    content = f.read()

head_end = content.find('  <main>')
foot_start = content.find('  <footer id="footer"')

header_html = content[:head_end]
footer_html = content[foot_start:]

main_html = """  <main>
    <section class="hero dark-layer" style="min-height: 60vh;">
      <img src="./img/contacto_hero.png" alt="Comunícate con nosotros" class="hero-bg">
      <div class="container hero-content text-center">
        <span class="cintillo gsap-fade-up">CREA Soluciones</span>
        <h1 class="gsap-fade-up" style="font-size: clamp(3rem, 5vw, 4.5rem);">Comunícate con nosotros</h1>
        <p class="subtitle gsap-fade-up">Estamos listos para transformar la inteligencia del mercado en resultados rentables para tu proyecto.</p>
      </div>
    </section>

    <section class="contact-page-section section-pad" style="background-color: #F8F9FA;">
      <div class="container">
        <div class="split-layout-tech" style="align-items: flex-start;">
          <!-- Left: Info -->
          <div class="contact-info gsap-fade-up">
            <h2 class="section-title mb-4">Información de Contacto</h2>
            <p style="font-size: 1.1rem; color: var(--neutral-dark); margin-bottom: 2rem;">¿Tienes un proyecto en mente o necesitas consultoría experta? Nuestro equipo está preparado para brindarte la mejor atención. Contáctanos por cualquiera de nuestros canales.</p>
            <ul style="list-style: none; padding: 0; margin: 0; color: var(--neutral-dark); line-height: 1.8; font-size: 1.05rem;">
              <li class="mb-4">
                <strong style="display: flex; align-items: center; gap: 8px; color: var(--black);"><i class="ph ph-clock" style="color: var(--brand-cyan); font-size: 1.5rem;"></i> Horario de Atención:</strong>
                <span style="display: block; padding-left: 32px;">Lunes a viernes de 9:00 am a 6:00 pm.</span>
              </li>
              <li class="mb-4">
                <strong style="display: flex; align-items: center; gap: 8px; color: var(--black);"><i class="ph ph-phone" style="color: var(--brand-cyan); font-size: 1.5rem;"></i> Teléfonos:</strong>
                <span style="display: block; padding-left: 32px;">+52 55 4597 3919 | +52 55 5277 8044<br>+52 55 5276 9531</span>
              </li>
              <li class="mb-4">
                <strong style="display: flex; align-items: center; gap: 8px; color: var(--black);"><i class="ph ph-envelope-simple" style="color: var(--brand-cyan); font-size: 1.5rem;"></i> Correo Electrónico:</strong>
                <span style="display: block; padding-left: 32px;"><a href="mailto:info@creasoluciones.com.mx" style="color: inherit; text-decoration: none;">info@creasoluciones.com.mx</a></span>
              </li>
              <li class="mb-4">
                <strong style="display: flex; align-items: center; gap: 8px; color: var(--black);"><i class="ph ph-map-pin" style="color: var(--brand-cyan); font-size: 1.5rem;"></i> Oficinas Principales:</strong>
                <span style="display: block; padding-left: 32px; margin-top: 8px;">
                  <strong style="color: var(--black);">Ciudad de México:</strong><br>Av. Coyoacán 1622 Edif. 4 Piso 2 Of. "A", Del Valle, C.P. 03100<br><br>
                  <strong style="color: var(--black);">Guadalajara:</strong><br>Herrera y Cairo 2835 Cuarto Piso Fracc. Terranova 44689<br><br>
                  <strong style="color: var(--black);">Monterrey:</strong><br>José Clemente Orozco 335 Desp. 304, Torre Novo, Valle Oriente 66269
                </span>
              </li>
            </ul>
          </div>
          <!-- Right: Form -->
          <div class="contact-form-container gsap-fade-up" style="background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); width: 100%;">
            <h3 style="font-family: var(--font-display); font-size: 1.8rem; margin-bottom: 24px; color: var(--black);">Déjanos un mensaje</h3>
            <form class="minimal-form contact-page-form">
              <input type="text" name="name" placeholder="Nombre Completo*" required style="width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; font-family: var(--font-body); outline: none;">
              <input type="email" name="email" placeholder="Correo electrónico corporativo*" required style="width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; font-family: var(--font-body); outline: none;">
              <input type="text" name="empresa" placeholder="Empresa*" required style="width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; font-family: var(--font-body); outline: none;">
              <input type="tel" name="phone" pattern="[0-9]*" placeholder="Teléfono*" required style="width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; font-family: var(--font-body); outline: none;">
              <select name="service" required style="width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; font-family: var(--font-body); color: var(--neutral-dark); outline: none;">
                <option value="" disabled selected>Solución de interés*</option>
                <option value="estudio-mercado">Estudio de Mercado</option>
                <option value="mayor-uso">Estudio de Mayor y Mejor Uso</option>
                <option value="reposicionamiento">Reposicionamiento / Reconversión</option>
                <option value="test-fit">Test Fit Arquitectónico</option>
                <option value="avaluo">Avalúo Inmobiliario</option>
                <option value="otros">Otra Solución</option>
              </select>
              <textarea name="message" placeholder="Compártenos de forma breve los objetivos de tu proyecto...*" required style="width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 6px; font-family: var(--font-body); height: 120px; resize: vertical; outline: none;"></textarea>
              <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 15px 0;">Enviar Mensaje <i class="ph ph-paper-plane-tilt"></i></button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>
"""

with open(os.path.join(directory, 'contacto.html'), 'w', encoding='utf-8') as f:
    f.write(header_html + main_html + footer_html)

print("Created contacto.html")

# Now update the menus inside all html files including the new contacto.html
html_files = glob.glob(os.path.join(directory, "*.html"))
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Update navigation menu link
    new_content = content.replace('<a href="#footer">Contacto</a>', '<a href="contacto.html">Contacto</a>')
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")

print("Process finished.")
