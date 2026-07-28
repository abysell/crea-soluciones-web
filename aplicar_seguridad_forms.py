#!/usr/bin/env python3
"""
Inyecta el campo señuelo (honeypot) en todos los formularios del sitio.

Los tres formularios (form-footer, form-contacto, form-informes) están
duplicados en 24 archivos HTML. Este script los recorre y agrega el bloque
del honeypot justo después de la etiqueta <form> de apertura.

Es idempotente: si el bloque ya existe, el archivo se deja intacto.
Los demás campos de seguridad (recaptcha_token y cs_elapsed) los inserta
js/main.js en tiempo de ejecución, por eso no aparecen aquí.

Uso:
    python3 aplicar_seguridad_forms.py            # aplica los cambios
    python3 aplicar_seguridad_forms.py --dry-run  # solo reporta
"""

import re
import sys
from pathlib import Path

RAIZ = Path(__file__).parent

# Debe coincidir con CS_HONEYPOT_CAMPO en config.php
CAMPO_SENUELO = "website"

MARCA = "<!-- honeypot -->"

BLOQUE = f"""
          {MARCA}
          <div class="form-extra-field" aria-hidden="true">
            <label for="{{campo_id}}">No llenar este campo</label>
            <input type="text" id="{{campo_id}}" name="{CAMPO_SENUELO}" tabindex="-1" autocomplete="off">
          </div>
"""

# Solo los formularios que envían a nuestros procesadores PHP
FORMULARIOS = ("form-footer", "form-contacto", "form-informes")

PATRON_FORM = re.compile(
    r'(<form\s+id="(' + "|".join(FORMULARIOS) + r')"[^>]*>)',
    re.IGNORECASE,
)


def procesar(ruta: Path, dry_run: bool = False):
    """Devuelve (formularios_encontrados, formularios_modificados)."""
    original = ruta.read_text(encoding="utf-8")
    encontrados = 0
    modificados = 0

    def reemplazar(match):
        nonlocal encontrados, modificados
        encontrados += 1
        apertura, form_id = match.group(1), match.group(2)

        # Idempotencia: si el señuelo ya está en los 400 caracteres
        # siguientes a la apertura, no se vuelve a insertar.
        inicio = match.end()
        if MARCA in original[inicio:inicio + 400]:
            return apertura

        modificados += 1
        # Un id único por formulario para no romper la relación label/input
        campo_id = f"{form_id}-{CAMPO_SENUELO}"
        return apertura + BLOQUE.format(campo_id=campo_id).rstrip() + "\n"

    resultado = PATRON_FORM.sub(reemplazar, original)

    if modificados and not dry_run:
        ruta.write_text(resultado, encoding="utf-8")

    return encontrados, modificados


def main():
    dry_run = "--dry-run" in sys.argv
    total_encontrados = total_modificados = 0

    for ruta in sorted(RAIZ.glob("*.html")):
        encontrados, modificados = procesar(ruta, dry_run)
        if encontrados:
            estado = f"{modificados} inyectado(s)" if modificados else "ya protegido"
            print(f"  {ruta.name:45s} {encontrados} form(s) -> {estado}")
        total_encontrados += encontrados
        total_modificados += modificados

    print()
    print(f"Formularios encontrados: {total_encontrados}")
    print(f"Formularios modificados: {total_modificados}")
    if dry_run:
        print("(--dry-run: no se escribió ningún archivo)")


if __name__ == "__main__":
    main()
