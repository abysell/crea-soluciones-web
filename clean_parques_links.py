import os
import glob
import re

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

# Match the exact injected `li` tag with optional whitespace padding around it.
# This prevents leaving blank ugly lines in the HTML.
original_snippet = re.compile(
    r'\s*<li><a href="\./reporte-parques-industriales\.html">Parques Industriales</a></li>'
)

count = 0
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    new_content = original_snippet.sub('', content)
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Removed orphaned link from {os.path.basename(file_path)}")
        count += 1

print(f"Done. Cleaned {count} files.")
