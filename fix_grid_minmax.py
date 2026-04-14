import os
import glob
import re

def fix_minmax():
    files = glob.glob('*.html')
    pattern = re.compile(r'minmax\((\d+)px,\s*1fr\)')
    
    for file in files:
        with open(file, 'r', encoding='utf-8') as f:
            content = f.read()
            
        new_content, count = pattern.subn(r'minmax(min(100%, \1px), 1fr)', content)
        
        if count > 0:
            with open(file, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Fixed {count} instances in {file}")

fix_minmax()
print("Done")
