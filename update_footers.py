import os
import glob
import re

dir_path = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(dir_path, "*.html"))

# Read a sample to see exactly how the block looks
sample_file = os.path.join(dir_path, "index.html")
with open(sample_file, "r", encoding="utf-8") as f:
    sample_html = f.read()

start_tag = '<div class="footer-contact-form">'
end_tag = '</div>\n    </div>'

start_idx = sample_html.find(start_tag)
end_idx = sample_html.find(end_tag, start_idx) + len('</div>')

if start_idx == -1 or end_idx == -1:
    print("Could not find footer form block in sample.")
    exit(1)

old_footer_form = sample_html[start_idx:end_idx]

# We are going to replace the `<form class="minimal-form">` section
new_footer_form = '''<div class="footer-contact-form">
        <h4 style="font-family: var(--font-display); font-size: 1.2rem; margin-bottom: 24px; color: var(--neutral-light);">Contáctanos</h4>
        <form id="form-footer" action="procesar_contacto.php" method="POST" class="minimal-form" novalidate>
          <input id="form-name-footer" type="text" name="name" placeholder="Nombre Completo*" required>
          <span class="error-message" id="error-name-footer" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-bottom: 8px;">Este campo es obligatorio</span>
          
          <input id="form-email-footer" type="email" name="email" placeholder="Correo electrónico*" required>
          <span class="error-message" id="error-email-footer" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-bottom: 8px;">Ingresa un correo electrónico válido</span>
          
          <input id="form-phone-footer" type="tel" name="phone" pattern="[0-9]*" placeholder="Teléfono*" required>
          <span class="error-message" id="error-phone-footer" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-bottom: 8px;">Este campo es obligatorio</span>
          
          <select id="form-service-footer" name="service" required>
            <option value="" disabled selected>Servicio a contratar*</option>
            <option value="estudio-mercado">Estudio de Mercado</option>
            <option value="estudio-financiero">Estudio Financiero</option>
            <option value="reposicionamiento">Estudio de Reposicionamiento</option>
            <option value="geomarketing">Geomarketing</option>
            <option value="avaluo">Avalúo</option>
            <option value="reporte">Reporte</option>
            <option value="otros">Otros</option>
          </select>
          <span class="error-message" id="error-service-footer" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-bottom: 8px;">Selecciona una opción</span>

          <select id="form-stage-footer" name="stage" required>
            <option value="" disabled selected>Etapa del proyecto*</option>
            <option value="idea">Idea del Proyecto</option>
            <option value="estudios">Estudios de Mercado y Financiero</option>
            <option value="anteproyecto">Anteproyecto Arquitectónico</option>
            <option value="comercializacion">Comercialización</option>
            <option value="expansion">Expansión</option>
            <option value="otra">Otra</option>
          </select>
          <span class="error-message" id="error-stage-footer" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-bottom: 8px;">Selecciona una opción</span>

          <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Solicitar Información <i class="ph ph-paper-plane-tilt"></i></button>
        </form>
      </div>'''

for html_file in html_files:
    with open(html_file, "r", encoding="utf-8") as f:
        html = f.read()
    
    # We find the exact block and replace it
    start = html.find(start_tag)
    end = html.find(end_tag, start) + len('</div>')
    
    if start != -1 and end != -1:
        html = html[:start] + new_footer_form + html[end:]
        with open(html_file, "w", encoding="utf-8") as f:
            f.write(html)
        print(f"Updated {os.path.basename(html_file)}")
    else:
        print(f"Form not found in {os.path.basename(html_file)}")

print("Process finished.")
