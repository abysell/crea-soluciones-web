#!/usr/bin/env python3
"""
Elimina el modal de descarga de informes de las páginas donde no se puede abrir.

El bloque <dialog id="lead-modal"> quedó replicado en cuatro archivos, pero solo
index.html tiene los botones .toggle-modal que lo activan. En las otras tres
páginas es markup inalcanzable: el CSS lo deja en opacity 0 y pointer-events
none hasta recibir la clase "active", que solo agrega el listener de esos
botones.

index.html se excluye de forma explícita: ahí el modal sí funciona.

Uso:
    python3 eliminar_modal_muerto.py            # aplica los cambios
    python3 eliminar_modal_muerto.py --dry-run  # solo reporta
"""

import re
import sys
from pathlib import Path

RAIZ = Path(__file__).parent

# El home es el único que puede abrir el modal.
EXCLUIDOS = {"index.html"}

# Captura el bloque completo, incluida la sangría de la línea de apertura y la
# línea en blanco que le sigue al cierre.
PATRON_MODAL = re.compile(
    r'[ \t]*<dialog\s+id="lead-modal".*?</dialog>\s*\n',
    re.IGNORECASE | re.DOTALL,
)

PATRON_DISPARADOR = re.compile(r'toggle-modal', re.IGNORECASE)


def procesar(ruta: Path, dry_run: bool = False):
    """Devuelve (tenia_modal, disparadores, eliminado, lineas_quitadas)."""
    original = ruta.read_text(encoding="utf-8")

    coincidencia = PATRON_MODAL.search(original)
    if not coincidencia:
        return False, 0, False, 0

    disparadores = len(PATRON_DISPARADOR.findall(original))

    # Salvaguarda: nunca borrar un modal que algo puede abrir, aunque el
    # archivo no esté en la lista de excluidos.
    if disparadores > 0:
        return True, disparadores, False, 0

    lineas = coincidencia.group(0).count("\n")
    resultado = PATRON_MODAL.sub("", original, count=1)

    if not dry_run:
        ruta.write_text(resultado, encoding="utf-8")

    return True, 0, True, lineas


def main():
    dry_run = "--dry-run" in sys.argv
    total = 0

    for ruta in sorted(RAIZ.glob("*.html")):
        if ruta.name in EXCLUIDOS:
            if '<dialog id="lead-modal"' in ruta.read_text(encoding="utf-8"):
                print(f"  {ruta.name:20s} excluido (el modal sí funciona aquí)")
            continue

        tenia, disparadores, eliminado, lineas = procesar(ruta, dry_run)
        if not tenia:
            continue

        if eliminado:
            print(f"  {ruta.name:20s} modal eliminado ({lineas} líneas)")
            total += lineas
        else:
            print(f"  {ruta.name:20s} CONSERVADO: tiene {disparadores} disparador(es)")

    print()
    print(f"Líneas eliminadas: {total}")
    if dry_run:
        print("(--dry-run: no se escribió ningún archivo)")


if __name__ == "__main__":
    main()
