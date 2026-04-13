import os
from PIL import Image

def optimize_image(filepath):
    print(f"Processing {filepath}...")
    try:
        with Image.open(filepath) as img:
            file_ext = os.path.splitext(filepath)[1].lower()
            
            # If the image is extremely large, dial it down to a sensible HD width
            if img.width > 1920:
                ratio = 1920 / float(img.width)
                new_h = int(img.height * ratio)
                # Ensure it has an alpha channel if converting for Pillow resizing stability
                if img.mode in ('RGBA', 'LA') or (img.mode == 'P' and 'transparency' in img.info):
                    img = img.convert('RGBA')
                img = img.resize((1920, new_h), Image.Resampling.LANCZOS)
            
            if file_ext == '.png':
                # Use Palette modes with adaptive 256 colors lossy compression for PNG
                # Also ensure RGBA -> RGB if no alpha is strictly used, but P mostly handles it
                if img.mode == 'RGBA':
                    alpha = img.split()[3]
                    bg = Image.new("RGB", img.size, (255, 255, 255))
                    bg.paste(img, mask=alpha)
                    img = bg
                img = img.convert("P", palette=Image.ADAPTIVE, colors=256)
                img.save(filepath, format="PNG", optimize=True)
                
            elif file_ext == '.webp':
                # WebP rewrite with a tighter quality parameter
                img.save(filepath, format="WEBP", quality=60, method=4)
                
            print(f"Successfully optimized {filepath}")
    except Exception as e:
        print(f"Failed to optimize {filepath}: {e}")

images = [
    "img/hero_sectores.webp",
    "img/estudios-mercado-hero.webp",
    "img/test_fit_hero.webp",
    "img/financiamiento.webp",
    "img/casos-de-exito-hero.png",
    "img/contacto_hero.png"
]

base_dir = "/Users/selene/Documents/crea-soluciones-web"

for img_rel in images:
    full_path = os.path.join(base_dir, img_rel)
    if os.path.exists(full_path):
        orig_size = os.path.getsize(full_path)
        optimize_image(full_path)
        new_size = os.path.getsize(full_path)
        print(f"Size: {orig_size/1024:.1f} KB -> {new_size/1024:.1f} KB\n")
    else:
        print(f"NOT FOUND: {full_path}")
