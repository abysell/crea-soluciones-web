import os

dir_path = "/Users/selene/Documents/crea-soluciones-web"
contact_file = os.path.join(dir_path, "contacto.html")

with open(contact_file, "r", encoding="utf-8") as f:
    html = f.read()

# Replace Hero Data
html = html.replace(">Comunícate con nosotros</h1>", ">¡Mensaje enviado con éxito!</h1>")
html = html.replace(">Estamos listos para transformar la inteligencia del mercado en resultados rentables para tu proyecto.</p>", ">Hemos recibido tu solicitud. Un consultor estratégico analizará tu perfil y se pondrá en contacto contigo a la brevedad.</p>")

# Find and remove the split layout form section
start_section = html.find('<section class="contact-page-section')
end_section = html.find('</section>', start_section) + len('</section>')

if start_section != -1 and end_section != -1:
    # Insert a nicely designed thank you block
    replacement = '''
    <section class="section-pad text-center" style="background-color: #F8F9FA; min-height: 40vh; display: flex; align-items: center; justify-content: center;">
      <div class="container" style="padding: 60px 0;">
         <i class="ph-fill ph-check-circle" style="font-size: 5rem; color: var(--brand-cyan); margin-bottom: 20px;"></i>
         <h2 style="font-family: var(--font-display); font-size: 2rem; color: var(--black); margin-bottom: 20px;">Recepción Exitosa</h2>
         <p style="font-size: 1.1rem; color: var(--neutral-dark); margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">Nuestro equipo se comunicará contigo a los datos de contacto proporcionados en un plazo menor a 24 horas hábiles.</p>
         <a href="index.html" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 30px;">Volver al Inicio</a>
      </div>
    </section>
'''
    html = html[:start_section] + replacement + html[end_section:]

# Clean up any residual JS validation form logic from the thank you page bottom
start_js = html.find('<script>\n    document.addEventListener("DOMContentLoaded", () => {\n      const form = document.getElementById("form-contacto');
if start_js != -1:
    end_js = html.find('</script>', start_js) + len('</script>')
    if end_js != -1:
        html = html[:start_js] + html[end_js:]

with open(os.path.join(dir_path, "gracias.html"), "w", encoding="utf-8") as f:
    f.write(html)
print("gracias.html generated")
