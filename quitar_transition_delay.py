#!/usr/bin/env python3
"""
Elimina los `transition-delay` en línea de las tarjetas.

Venían de una versión anterior en la que la entrada se animaba con
transiciones de CSS. Hoy la entrada la controla GSAP, que anima estilos en
línea y no pasa por las transiciones, así que estos valores ya no escalonan
nada al aparecer.

Pero no eran inofensivos: .tech-item y .white-hover-card declaran
`transition` y tienen efecto hover, de modo que el delay se aplicaba a ESE
efecto y retrasaba hasta 300ms la reacción al pasar el cursor.

El escalonado de entrada ahora lo hace GSAP con `stagger`, en js/main.js.

Uso:
    python3 quitar_transition_delay.py            # aplica los cambios
    python3 quitar_transition_delay.py --dry-run  # solo reporta
"""

import re
import sys
from pathlib import Path

RAIZ = Path(__file__).parent

# Solo dentro de un atributo style=""; se contempla que sea la única
# declaración o que vaya acompañada de otras.
PATRON = re.compile(r'\s*transition-delay:\s*[0-9.]+m?s\s*;?')


def limpiar_style(match):
    """Reescribe un atributo style sin la declaración transition-delay."""
    contenido = match.group(1)
    nuevo = PATRON.sub("", contenido).strip()
    nuevo = re.sub(r";\s*;", ";", nuevo).strip().strip(";").strip()
    return f' style="{nuevo}"' if nuevo else ""


def procesar(ruta: Path, dry_run: bool = False):
    original = ruta.read_text(encoding="utf-8")
    if "transition-delay" not in original:
        return 0

    cuantos = len(re.findall(r"transition-delay", original))

    # Se opera solo sobre atributos style que contengan la declaración.
    resultado = re.sub(
        r' style="([^"]*transition-delay[^"]*)"',
        limpiar_style,
        original,
    )

    restantes = len(re.findall(r"transition-delay", resultado))
    quitados = cuantos - restantes

    if quitados and not dry_run:
        ruta.write_text(resultado, encoding="utf-8")

    if restantes:
        print(f"  AVISO: quedan {restantes} en {ruta.name} (fuera de un atributo style)")

    return quitados


def main():
    dry_run = "--dry-run" in sys.argv
    total = 0

    for ruta in sorted(RAIZ.glob("*.html")):
        n = procesar(ruta, dry_run)
        if n:
            print(f"  {ruta.name:42s} {n} quitado(s)")
            total += n

    print()
    print(f"Total eliminados: {total}")
    if dry_run:
        print("(--dry-run: no se escribió ningún archivo)")


if __name__ == "__main__":
    main()
