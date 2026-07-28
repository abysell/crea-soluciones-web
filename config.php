<?php
/**
 * Configuración central de los formularios de CREA Soluciones.
 *
 * IMPORTANTE: sustituye los valores marcados como PENDIENTE antes de subir a producción.
 * La site key (pública) también debe copiarse en js/main.js -> RECAPTCHA_SITE_KEY.
 */

// ---------------------------------------------------------------------------
// Correo
// ---------------------------------------------------------------------------
define("CS_DESTINATARIO", "info@creasoluciones.com.mx");
define("CS_REMITENTE",    "webmaster@creasoluciones.com.mx");
define("CS_PAGINA_EXITO", "gracias.html");
define("CS_PAGINA_INICIO", "index.html");

// ---------------------------------------------------------------------------
// reCAPTCHA v3  (https://www.google.com/recaptcha/admin)
// ---------------------------------------------------------------------------
define("CS_RECAPTCHA_ACTIVO",  true);
define("CS_RECAPTCHA_SECRET",  "PENDIENTE_SECRET_KEY");   // llave privada (servidor)
define("CS_RECAPTCHA_SITEKEY", "PENDIENTE_SITE_KEY");     // llave pública (informativa)

// Puntaje mínimo aceptado. 1.0 = casi seguro humano, 0.0 = casi seguro bot.
// 0.5 es el valor recomendado por Google. Súbelo si sigue entrando spam.
define("CS_RECAPTCHA_MIN_SCORE", 0.5);

// Si Google no responde (red caída, cURL bloqueado por el hosting):
//   true  = se deja pasar el envío y se registra el incidente (no se pierden leads)
//   false = se rechaza el envío
define("CS_RECAPTCHA_FAIL_OPEN", true);

// ---------------------------------------------------------------------------
// Honeypot y trampa de tiempo
// ---------------------------------------------------------------------------
// Nombre del campo señuelo. Debe coincidir con el input oculto de los formularios.
define("CS_HONEYPOT_CAMPO", "website");

// Segundos mínimos que un humano tarda en llenar el formulario.
// Un envío más rápido se considera automatizado.
define("CS_SEGUNDOS_MINIMOS", 3);

// ---------------------------------------------------------------------------
// Registro de bloqueos
// ---------------------------------------------------------------------------
// El registro contiene IPs y correos de prospectos: nunca debe poder leerse
// desde el navegador. Hay dos protecciones y conviene entender ambas.
//
// 1. La extensión .php. Si alguien pide /logs/formularios.log.php, el servidor
//    ejecuta el archivo en vez de mostrarlo, y su primera línea es un exit().
//    No se revela nada. Funciona igual en Apache, Nginx y LiteSpeed, porque no
//    depende de .htaccess (que Nginx ignora por completo).
//
// 2. logs/.htaccess, que niega el acceso a la carpeta. Solo aplica en Apache;
//    se conserva como refuerzo.
//
// Si prefieres sacar el registro del directorio público, cambia la ruta por
// algo como dirname(__DIR__) . "/logs-crea/formularios.log". Ten en cuenta
// que el usuario de PHP debe poder escribir ahí: si no puede, seguridad.php
// vuelve automáticamente a la ruta interna para no quedarse sin registro.
define("CS_LOG_ACTIVO", true);
define("CS_LOG_ARCHIVO", __DIR__ . "/logs/formularios.log.php");
