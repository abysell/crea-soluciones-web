import os
import glob
import re

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

count = 0
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    new_content = re.sub(
        r'<a href="sectores\.html" class="dropbtn">\s*Sectores\s*<i class="ph ph-caret-down"></i>\s*</a>',
        r'<a href="sectores.html" class="dropbtn">Sectores</a>',
        content
    )
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")
        count += 1

print(f"Done. {count} menus updated.")
