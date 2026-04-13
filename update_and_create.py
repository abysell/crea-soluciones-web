import os
import glob
import re

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

# Update menus
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    new_content = content.replace('<a href="#">Reporte Vivienda Institucional en Renta</a>', '<a href="reporte-vivienda-institucional-mexico.html">Reporte Vivienda Institucional en Renta</a>')
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated menu in {os.path.basename(file_path)}")

# Create the new page based on reporte-parques-industriales.html
base_page = os.path.join(directory, "reporte-parques-industriales.html")
new_page = os.path.join(directory, "reporte-vivienda-institucional-mexico.html")

with open(base_page, "r", encoding="utf-8") as f:
    template = f.read()

# Replace title
template = re.sub(r'<title>.*?</title>', '<title>Reporte de Vivienda Institucional | CREA Soluciones</title>', template)
# Replace hero header
template = re.sub(
    r'<h1 class="text-white gsap-fade-up".*?</h1>',
    r'<h1 class="text-white gsap-fade-up" style="font-family: var(--font-display); font-size: clamp(2.5rem, 4vw, 3.5rem); margin-bottom: 1rem;">Reporte de Vivienda Institucional en México <br> actualizado al primer semestre de 2024</h1>',
    template, flags=re.DOTALL
)

# Replace section content: we want to replace everything from <!-- REPORT DETAILS GRID --> to <!-- SECCION 2: TABLA DE CONTENIDOS (ACORDEON) -->
# Or wait, I can just do a multi-line substitution or replace it exactly.
content_to_insert = """
    <!-- REPORT DETAILS GRID -->
    <section class="section-pad bg-light">
      <div class="container" style="max-width: 1200px; margin: 0 auto;">
        
        <h2 class="text-center gsap-fade-up" style="margin-bottom: 4rem; font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: #4b5563;">CREA elabora el informe más completo del mercado inmobiliario residencial</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 60px; align-items: center;">
          
          <!-- LEFT: LIST -->
          <div>
            <p style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; margin-bottom: 15px; color: var(--neutral-dark);">Incluye:</p>
            <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Noto Serif', serif; font-size: 1.15rem; color: var(--neutral-dark); line-height: 1.6; margin-bottom: 30px;">
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Los proyectos en las principales Zonas metropolitana de México.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Cantidad de desarrollos de vivienda en renta institucional y su estatus (operación, construcción y proyecto) por estado.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Pronóstico de inversión de vivienda en renta en México.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Indicadores macroeconómicos / políticas públicas que impactan las áreas metropolitanas.</span>
              </li>
            </ul>

            <p style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; margin-bottom: 15px; color: var(--neutral-dark);">Además, contiene:</p>
            <ul style="list-style: none; padding: 0; margin: 0; font-family: 'Noto Serif', serif; font-size: 1.15rem; color: var(--neutral-dark); line-height: 1.6;">
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Nombres de las principales empresas con sus proyectos y unidades en renta.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Grandes empresas desarrolladoras y proyectos como Greystar, Metrobuildings, otros.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Composición poblacional (%) por niveles socioeconómicos de las zonas metropolitanas.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Crecimiento de la vivienda en renta institucional por zona metropolitana.</span>
              </li>
              <li style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                <i class="ph-fill ph-check-circle" style="color: var(--brand-cyan); font-size: 1.5rem; margin-top: 2px;"></i>
                <span class="gsap-fade-up">Información de las principales empresas desarrolladoras que incluye:<br>Ubicación, Operador, Año de inauguración, Estatus, Unidades, Prototipos, Tamaño promedio, Renta promedio, Ocupación, Perfil del inquilino.</span>
              </li>
            </ul>
          </div>

          <!-- RIGHT: IMAGE -->
          <div class="gsap-fade-up text-center">
            <img src="./img/reporte-vivienda-institucional-mexico.webp" alt="Reporte Vivienda Institucional" style="width: 100%; max-width: 500px; height: auto; display: block; margin: 0 auto; filter: drop-shadow(0px 20px 30px rgba(0,0,0,0.15)); border-radius: 4px;">
          </div>
          
        </div>
      </div>
    </section>
"""

# Find borders
start_idx = template.find('<!-- REPORT DETAILS GRID -->')
end_idx = template.find('<!-- SECCION 2: TABLA DE CONTENIDOS (ACORDEON) -->')

if start_idx != -1 and end_idx != -1:
    template = template[:start_idx] + content_to_insert + "\n    " + template[end_idx:]

# Remove SECCION 2: TABLA DE CONTENIDOS (ACORDEON) and everything until </main> 
# since the prompt didn't specify we need an accordion for this page, or we could leave it but the prompt just says:
# "eN EL CONTENIDO, en la sección 1 vamos a poner el titulo ... después 2 columnas ... En la segunda columna la imagen"
# If we want to be safe, we can remove the accordion, but keeping it empty is weird. Let's remove the accordion.
acc_start_idx = template.find('<!-- SECCION 2: TABLA DE CONTENIDOS (ACORDEON) -->')
main_end_idx = template.find('</main>', acc_start_idx)
if acc_start_idx != -1 and main_end_idx != -1:
    template = template[:acc_start_idx] + template[main_end_idx:]

with open(new_page, "w", encoding="utf-8") as f:
    f.write(template)

print("Created reporte-vivienda-institucional-mexico.html")

