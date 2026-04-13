import os
import glob
import re

directory = "/Users/selene/Documents/crea-soluciones-web"
html_files = glob.glob(os.path.join(directory, "*.html"))

count = 0
for file_path in html_files:
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    # Find footer logos using alt="CREA" and wrap them if not already wrapped
    # The header has alt="CREA Soluciones Logo"
    def wrap_with_a(match):
        return f'<a href="index.html">{match.group(0)}</a>'

    # We match the <img...> tag and verify it's not preceded directly by an anchor tag.
    # We do this simply by replacing only if it's inside <div class="footer-brand">
    
    def footer_brand_replace(match):
        block = match.group(0)
        # If the image inside the footer brand is not surrounded by an <a> tag
        if '<a href="index.html"><img' not in block:
            block = re.sub(r'(<img[^>]*logo-crea-soluciones\.webp[^>]*>)', r'<a href="index.html">\1</a>', block)
        return block

    new_content = re.sub(r'<div class="footer-brand">.*?</div>', footer_brand_replace, content, flags=re.DOTALL)
    
    if new_content != content:
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"Updated {os.path.basename(file_path)}")
        count += 1

print(f"Done. {count} footers updated.")
