import os
from PIL import Image
import tempfile

targets = [
    "Marina_Town_Center.png",
    "CETRAM_Santa_Martha.png",
    "The_Park_San_Luis_Potosi.png",
    "novena-mexico.png"
]

base_dir = "/Users/selene/Documents/crea-soluciones-web/img/casos-exito"

for filename in targets:
    path = os.path.join(base_dir, filename)
    if not os.path.exists(path):
        print(f"NOT FOUND: {filename}")
        continue
    
    orig_size = os.path.getsize(path)
    target_size = orig_size * 0.80
    
    with Image.open(path) as img:
        low = 0.5
        high = 1.0
        best_scale = 0.89
        
        with tempfile.NamedTemporaryFile(suffix=".png", delete=False) as tmp:
            tmp_path = tmp.name
        
        try:
            for _ in range(6): 
                mid = (low + high) / 2
                w = int(img.width * mid)
                h = int(img.height * mid)
                
                # Minimum viable dimension guard to not destroy images accidentally
                if w < 10 or h < 10:
                    break
                
                resized = img.resize((w, h), Image.Resampling.LANCZOS)
                resized.save(tmp_path, format="PNG", optimize=True)
                test_size = os.path.getsize(tmp_path)
                
                if abs(test_size - target_size) / target_size < 0.02:
                    best_scale = mid
                    break
                elif test_size > target_size:
                    high = mid
                else:
                    low = mid
                    best_scale = mid 
            
            w = int(img.width * best_scale)
            h = int(img.height * best_scale)
            final_img = img.resize((w, h), Image.Resampling.LANCZOS)
            final_img.save(path, format="PNG", optimize=True)
            
            new_size = os.path.getsize(path)
            print(f"{filename}: {orig_size/1024:.1f} KB -> {new_size/1024:.1f} KB ({new_size/orig_size:.1%})")
            
        except Exception as e:
            print(f"FAILED on {filename}: {e}")
        finally:
            if os.path.exists(tmp_path):
                os.remove(tmp_path)
