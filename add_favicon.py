import os
import glob

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

favicon_code = """  <!-- Favicon -->
  <link rel="icon" href="img/favicon.png" sizes="32x32">
  <link rel="apple-touch-icon" href="img/favicon.png">
</head>"""

count = 0
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()
        
    if "favicon.png" not in content:
        new_content = content.replace("</head>", favicon_code)
        
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        count += 1
        print(f"Added favicon to {os.path.basename(file_path)}")

print(f"Done! Favicon applied to {count} html files.")
