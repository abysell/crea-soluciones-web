import os
import glob

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

for file_path in html_files:
    if os.path.basename(file_path) == "index.html":
        continue
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    new_content = content.replace('<a href="#portfolio">Casos de Éxito</a>', '<a href="casos-de-exito.html">Casos de Éxito</a>')
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")

