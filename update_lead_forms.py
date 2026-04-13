import os
import glob
import re

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

original_form = re.compile(
    r'<form class="lead-form">\s*<div class="form-group">\s*<input type="text" placeholder="Nombre completo" required>\s*</div>\s*<div class="form-group">\s*<input type="email" placeholder="Correo electrónico corporativo" required>\s*</div>\s*<div class="form-group">\s*<input type="text" placeholder="Empresa" required>\s*</div>\s*<button type="submit" class="btn btn-primary btn-full">Recibir Informe <i class="ph ph-arrow-right"></i></button>\s*</form>',
    re.DOTALL
)

replacement_form = """<form id="form-informes" class="lead-form" action="procesar_informe.php" method="POST" novalidate>
          <div class="form-group">
            <input id="form-name-informe" type="text" name="name" placeholder="Nombre completo" required>
            <span class="error-message" id="error-name-informe" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-top: 4px;">Este campo es obligatorio</span>
          </div>
          <div class="form-group">
            <input id="form-email-informe" type="email" name="email" placeholder="Correo electrónico corporativo" required>
            <span class="error-message" id="error-email-informe" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-top: 4px;">Ingresa un correo corporativo válido</span>
          </div>
          <div class="form-group">
            <input id="form-company-informe" type="text" name="company" placeholder="Empresa" required>
            <span class="error-message" id="error-company-informe" style="color: #ff6b6b; font-size: 0.85rem; display: none; margin-top: 4px;">Este campo es obligatorio</span>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Recibir Informe <i class="ph ph-arrow-right"></i></button>
        </form>"""

count = 0
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    new_content = original_form.sub(replacement_form, content)
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")
        count += 1

print(f"Done. {count} modals updated.")
