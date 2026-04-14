import os
import glob

def inject_mobile_button():
    files = glob.glob('*.html')
    for file in files:
        with open(file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if '<button class="mobile-toggle"' in content:
            continue
            
        # The button needs to go inside <div class="header-container">
        # specifically right after <div class="header-actions">...</div>
        # or before </header>
        
        # We can find <div class="header-actions">
        new_content = content.replace(
            '<div class="header-actions">', 
            '<button class="mobile-toggle" id="mobile-toggle"><i class="ph ph-list"></i></button>\n      <div class="header-actions">'
        )
        
        
        with open(file, 'w', encoding='utf-8') as f:
            f.write(new_content)
            
inject_mobile_button()
