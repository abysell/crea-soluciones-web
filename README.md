# Especificación Técnica y Guía de Continuidad - CREA Soluciones

Este documento recopila la lógica estructural, visual y de comportamiento construida sobre el rediseño del Home Page de CREA Soluciones. Su objetivo es asegurar la continuidad y la escalabilidad estética cuando se construyan las pantallas de páginas interiores.

---

## 1. Contexto de Diseño: "Tech-Premium Dashboard"
El objetivo principal del diseño ha sido posicionar a CREA Soluciones no como una consultora inmobiliaria tradicional, sino como una **firma tecnológica de inteligencia paramétrica (Tier 1)**. 
- **Inspiración Global:** Plataformas especializadas de Big Data, Fondos de Inversión y firmas como *Archidex* y *Spaciaz*.
- **Características Visuales:** Uso extenso de espacios en blanco/negro absoluto (Whitespace), separación estricta mediante líneas sutiles ortogonales al 10% de opacidad, y acentos de color neon/cyan confinados estrictamente a las interacciones del usuario (hover/focus).

---

## 2. Paleta de Colores (CSS Variables Globales)
Todos los colores deben consumirse exclusivamente desde las variables configuradas en la raíz de `css/style.css` (`:root { ... }`):

- **Color de Acento Principal:** `var(--brand-cyan)` — `#0098B4`. (Botones primarios, iconos activos, hover links).
- **Color Secundario / Riesgo:** `var(--brand-red)` — `#E30613`. (Usado escasamente para métricas o alertas).
- **Oscuridad Absoluta (Dark Mode):** `var(--black)` — `#111111`. (Fondos robustos como Footer, Dropdowns o Paneles de Datos).
- **Gris Editorial (Fondo Light):** `#F5F5F5` (O equivalente `var(--neutral-light)` para dar respiro como en la sección FAQs).

> **Aviso de Continuidad:** Nunca codificar colores "en crudo" (hardcoded). Si es oscuro, siempre usar `var(--black)` y no `#000` puro, ya que el `#111` provee una lectura premium y menos agresiva para pantallas OLED.

---

## 3. Sistema Tipográfico
La dualidad tipográfica es el núcleo del carácter de la marca:

- **1. Barlow (Sans-Serif):** 
  - *Identificador:* `var(--font-brand)`
  - *Uso:* Empleada en Títulos primarios (`h1`, `h2`), contadores numéricos grandes, menús de navegación y botones. Su geometría alargada refuerza el sentido "Tech".
- **2. Noto Serif (Serif):** 
  - *Identificador:* `var(--font-display)`
  - *Uso:* Empleada en subtítulos editoriales, descripciones largas de propuestas de valor, y bloques de lectura extensos ("Value Proposition"). Aporta el peso institucional y consultivo, equilibrando la frialdad tecnológica.
- **3. Roboto (Sans-Serif Secundaria):**
  - *Identificador:* `var(--font-body)`
  - *Uso:* Texto de cuerpo general (párrafos, información en formularios, listas de contacto en footer). Alta legibilidad en tamaños reducidos.

---

## 4. Sistema de Lógica Modular (Para Nuevas Páginas)

### 4.1 Rejilla Estructural y Espaciado (Layout)
- Toda sección mayor debe contenerse en un `<section class="section-pad">`. La clase `.section-pad` garantiza un espaciado vertical respirable (arriba/abajo) consistente en todo el sitio (aprox. `100px` en desktop y `80px` en móvil).
- Todos los componentes internos deben anclarse dentro de un `<div class="container">` para limitar el ancho máximo (`max-width: 1280px` en `style.css`) y mantener la visualización enfocada en equipos de escritorio ultra-panorámicos.

### 4.2 Cajas Divisorias ("Grid Dashboards")
Para la maqueta de listados corporativos complejos (e.g. Footer, FAQs) usamos `display: grid`.
Las cajas delimitadoras finas obedecyen a patrones como `border-right: 1px solid rgba(255, 255, 255, 0.1)` (en fondos negros) o `border-bottom: 1px solid black` (en fondos grises/claros). Si añades cajas estadísticas en páginas interiores, apégate a este estándar ortogonal de cuadrícula sin esquinas redondeadas dramáticas (el radio máximo autorizado varía de `4px` a `8px`).

### 4.3 Iconografía Gráfica
Usamos nativamente la suite open-source y premium de **[Phosphor Icons](https://phosphoricons.com/)** en su peso/estilo `Regular/Thin`.
- **Inyección HTML:** `<i class="ph ph-[nombre-del-icono]"></i>`.

### 4.4 Navegación Desplegable Multicapa (Sub-Dropdowns)
Para estructurar un nuevo ítem desplegable, el ecosistema ya soporta interacciones complejas. Usar la nomenclatura anidada: `.dropdown` > `.dropdown-content` > `.sub-dropdown` > `.sub-dropdown-content`. Las transiciones de caída u opacidad, así como el Dark-Mode adaptativo estricto, ya se calculan automáticamente sin JS.

---

## 5. Comportamientos Cinéticos y Animaciones Globales

### Scroll Animations (GreenSock GSAP & ScrollTrigger)
Todo elemento (cajas, textos, botones) que requiera aparecer orgánicamente mientras el usuario hace scroll hacia abajo en el futuro solo necesita adherir la clase **`gsap-fade-up`** en el DOM.
- El archivo `js/main.js` agrupa automáticamente todo contenedor marcado con `.gsap-fade-up` y lo activa (Fade-In + translación en Y de 40px) cuando cruza el punto de anclaje del 85% de la ventana, de forma que escalará a miles de páginas interiores sin tocar código JavaScript.

### Parallax de Cabecera (Hero Transitions)
El Header de navegación inicia transparente (con textos e iconos blancos). El script escucha el eje vertical y a partir de `> 50px` inserta la clase `.scrolled`, oscureciéndolo con fondo negro para proteger la lectura de submenús. No remover estas lógicas del `index.html` original al clonarlo.

---
---

## Preguntas e Inquietudes Pendientes (Para el Desarrollador Principal):
Para iniciar eficientemente las próximas vistas interiores, pregunto lo siguiente:

1. **Jerarquía Tipográfica en Interiores:** La variable *Noto Serif* aporta un toque súper editorial al Home. Cuando lleguemos a las interfaces "Puras" (por ejemplo, tableros llenos de datos del *SmartScan* o de investigación analítica compleja), ¿Prefieres que reduzcamos al máximo la *Noto Serif* y expresemos la data puramente con *Barlow/Roboto* para asemejarlo al 100% a un software, o mantenemos el espíritu de revista inmobilaria mezclando Serif?
2. **Cabeceras de Páginas Interiores:** ¿Las páginas como "Servicios de Modelos Financieros" van a tener su propio bloque fotográfico obscuro gigante (Hero Layout) en la cima como el Home, o serán fondos sólidos minimalistas (Negros/Blancos)?
3. **Botones e Inputs Base de Datos:** Ahora que el Footer tiene bordes que brillan en azul y sombras suaves (Efecto Neon Tech), ¿Libras ese estilo universal para todos los formularios grandes que construiremos dentro de las herramientas de Reportes, o solo es exclusivo para el contacto en Home?
