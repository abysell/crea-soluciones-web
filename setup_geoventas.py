import os
from PIL import Image

# Convert hero image to webp
img_path = "/Users/selene/.gemini/antigravity/brain/8546ffc2-97c8-4fd8-9192-eb160aa4721b/geoventas_hero_1775676128141.png"
out_path = "/Users/selene/Documents/crea-soluciones-web/img/geoventas-hero.webp"

if os.path.exists(img_path):
    im = Image.open(img_path)
    # The image is usually a square around 1024x1024 or similar, good enough for background
    im.save(out_path, "webp")
    print("Saved geoventas-hero.webp")

