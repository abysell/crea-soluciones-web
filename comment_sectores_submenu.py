import os
import glob
import re

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

pattern = re.compile(
    r'(<a href="sectores\.html" class="dropbtn">Sectores.*?</a>\s*)<div class="dropdown-content">(.*?)</div>',
    re.DOTALL
)

count = 0
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    def repl(m):
        # We ensure it's the right submenu by checking for some known items
        if "Centros Comerciales" in m.group(2):
            return m.group(1) + "<!-- <div class=\"dropdown-content\">" + m.group(2) + "</div> -->"
        return m.group(0)

    new_content = pattern.sub(repl, content)
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")
        count += 1

print(f"Done updating menus. {count} files changed.")
