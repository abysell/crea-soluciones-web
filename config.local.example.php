<?php
/**
 * Plantilla de config.local.php.
 *
 * Copia este archivo como config.local.php y rellena los valores. El original
 * sí va al repositorio; la copia con las claves reales queda excluida por
 * .gitignore y se sube al servidor por separado.
 *
 *     cp config.local.example.php config.local.php
 *
 * Si config.local.php no existe, el sitio sigue funcionando: reCAPTCHA y Zoho
 * quedan inactivos y el honeypot, la sanitización y el filtro de enlaces
 * siguen protegiendo los formularios.
 */

// --- reCAPTCHA v3 -----------------------------------------------------------
// https://www.google.com/recaptcha/admin
// La site key además debe copiarse en js/main.js -> RECAPTCHA_SITE_KEY.
define("CS_RECAPTCHA_SECRET",  "");
define("CS_RECAPTCHA_SITEKEY", "");

// --- Zoho CRM (Web to Lead) -------------------------------------------------
// Zoho CRM > Configuración > Desarrollador > Formularios web > Clientes
// potenciales. Copia los valores de estos dos campos ocultos del HTML generado.
define("CS_ZOHO_XNQSJSDP", "");
define("CS_ZOHO_XMIWTLD",  "");
