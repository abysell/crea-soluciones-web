<?php
/**
 * Capa de seguridad compartida por los formularios de CREA Soluciones.
 *
 * Cubre cuatro controles:
 *   1. Honeypot + trampa de tiempo   -> cs_honeypot_superado()
 *   2. Sanitización de campos        -> cs_sanitizar() / cs_sanitizar_encabezado()
 *   3. Bloqueo de enlaces            -> cs_contiene_enlaces()
 *   4. reCAPTCHA v3                  -> cs_verificar_recaptcha()
 */

require_once __DIR__ . "/config.php";

// ---------------------------------------------------------------------------
// 1. Sanitización
// ---------------------------------------------------------------------------

/**
 * Limpia un valor recibido por POST: quita etiquetas, entidades disfrazadas,
 * caracteres de control y recorta la longitud.
 */
function cs_sanitizar($valor, $maxlen = 500)
{
    if (!is_string($valor)) {
        return "";
    }

    // Fuerza UTF-8 válido: descarta bytes malformados que rompen los regex.
    if (function_exists("mb_convert_encoding")) {
        $valor = mb_convert_encoding($valor, "UTF-8", "UTF-8");
    }

    // Doble pasada: hay payloads que esconden etiquetas dentro de entidades HTML.
    $valor = strip_tags($valor);
    $valor = html_entity_decode($valor, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $valor = strip_tags($valor);

    // Elimina caracteres de control excepto salto de línea y tabulador.
    $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', "", $valor);

    // Colapsa saltos de línea múltiples y espacios sobrantes.
    $valor = preg_replace("/\r\n|\r/", "\n", $valor);
    $valor = preg_replace("/\n{3,}/", "\n\n", $valor);
    $valor = trim($valor);

    if (function_exists("mb_substr")) {
        return mb_substr($valor, 0, $maxlen, "UTF-8");
    }

    return substr($valor, 0, $maxlen);
}

/**
 * Versión para valores que se insertan en encabezados de correo (Subject, Reply-To).
 * Elimina cualquier salto de línea para evitar inyección de encabezados.
 */
function cs_sanitizar_encabezado($valor, $maxlen = 150)
{
    $valor = cs_sanitizar($valor, $maxlen);
    return trim(preg_replace('/[\r\n\t]+/', " ", $valor));
}

/**
 * Codifica un asunto con acentos según RFC 2047 para que no se vea corrupto.
 */
function cs_asunto($texto)
{
    $texto = cs_sanitizar_encabezado($texto, 200);

    if (function_exists("mb_encode_mimeheader")) {
        $anterior = mb_internal_encoding();
        mb_internal_encoding("UTF-8");
        $texto = mb_encode_mimeheader($texto, "UTF-8", "B");
        mb_internal_encoding($anterior);
    }

    return $texto;
}

// ---------------------------------------------------------------------------
// 2. Bloqueo de enlaces
// ---------------------------------------------------------------------------

/**
 * Detecta cualquier forma de enlace en un valor de texto.
 *
 * NO debe aplicarse al campo de correo electrónico: un email contiene un
 * dominio por definición y siempre daría positivo. Ese campo se valida
 * aparte con FILTER_VALIDATE_EMAIL.
 */
function cs_contiene_enlaces($valor)
{
    if ($valor === "" || $valor === null) {
        return false;
    }

    // Lista de dominios de primer nivel. Se omiten a propósito .es, .de, .me
    // y .ve: son palabras del español y provocarían falsos positivos en textos
    // como "S.A. de C.V.". Esos dominios igual se detectan si vienen con
    // http:// o www. delante, que es como los escribe el spam real.
    $tlds = "com|net|org|info|biz|io|co|ru|cn|xyz|top|club|online|site|shop"
          . "|link|click|live|store|icu|pw|cc|tv|app|dev|ai|bet|casino|loan"
          . "|work|tk|ml|ga|cf|gq|mx|us|uk|fr|it|br|ar|cl|pe";

    $patrones = array(
        '~https?\s*:\s*/~i',                        // http:// y https:// (aun con espacios)
        '~\bwww\d{0,3}\s*\.~i',                     // www.
        '~<\s*a[\s>]~i',                            // etiqueta <a> que sobrevivió
        '~\[\s*url~i',                              // BBCode [url]
        '~\b(?:mailto|javascript|data|vbscript|ftp|file|tel|whatsapp)\s*:~i',
        '~\b\d{1,3}(?:\.\d{1,3}){3}\b~',            // IP literal

        // dominio.tld escrito de corrido, sin espacios alrededor del punto
        '~[a-z0-9][a-z0-9-]*\.(?:' . $tlds . ')\b~i',

        // dominio ofuscado: "ejemplo (dot) com", "ejemplo punto com"
        '~[a-z0-9][a-z0-9-]*\s*[\(\[]?\s*(?:dot|punto)\s*[\)\]]?\s*(?:' . $tlds . ')\b~i',
    );

    foreach ($patrones as $patron) {
        if (preg_match($patron, $valor)) {
            return true;
        }
    }

    return false;
}

/**
 * Recorre los campos indicados y devuelve true si alguno contiene un enlace.
 */
function cs_algun_campo_con_enlace(array $campos)
{
    foreach ($campos as $valor) {
        if (cs_contiene_enlaces($valor)) {
            return true;
        }
    }

    return false;
}

// ---------------------------------------------------------------------------
// 2.b Atribución de campaña
// ---------------------------------------------------------------------------

/**
 * Recoge y limpia los parámetros de campaña que envió el navegador.
 *
 * Estos valores salen de la URL de aterrizaje, así que son de lo más expuesto
 * que recibe el formulario: cualquiera puede repartir un enlace con
 * ?utm_source=<lo que sea> y ese texto acabaría en el correo y en el CRM.
 *
 * Por eso se limitan a un juego de caracteres conservador. Y si uno trae un
 * enlace, se descarta ESE campo en lugar de rechazar el envío: perder un lead
 * legítimo por una etiqueta de campaña mal formada sería mucho peor que
 * quedarse sin saber de qué anuncio vino.
 *
 * @return array claves utm_source, utm_medium, utm_campaign, utm_term,
 *               utm_content y gclid, siempre presentes (cadena vacía si no hay)
 */
function cs_atribucion_recibida()
{
    $claves = array(
        "utm_source", "utm_medium", "utm_campaign",
        "utm_term", "utm_content", "gclid",
    );

    $limpios = array();

    foreach ($claves as $clave) {
        $valor = cs_sanitizar($_POST[$clave] ?? "", 200);

        // Los identificadores de campaña son alfanuméricos con separadores
        // simples. Todo lo demás se elimina.
        $valor = preg_replace('/[^\p{L}\p{N} ._\-\+\|:@\/]/u', "", $valor);
        $valor = trim(preg_replace('/\s+/', " ", $valor));

        if ($valor !== "" && cs_contiene_enlaces($valor)) {
            cs_registrar("atribucion", "enlace descartado en " . $clave . ": " . substr($valor, 0, 60));
            $valor = "";
        }

        $limpios[$clave] = $valor;
    }

    return $limpios;
}

/** true si al menos un parámetro de campaña trae valor. */
function cs_hay_atribucion(array $atribucion)
{
    foreach ($atribucion as $valor) {
        if ($valor !== "") {
            return true;
        }
    }

    return false;
}

// ---------------------------------------------------------------------------
// 3. Honeypot y trampa de tiempo
// ---------------------------------------------------------------------------

/**
 * true  = el envío parece humano
 * false = el señuelo fue llenado o el formulario se envió demasiado rápido
 */
function cs_honeypot_superado()
{
    $senuelo = isset($_POST[CS_HONEYPOT_CAMPO]) ? trim($_POST[CS_HONEYPOT_CAMPO]) : "";
    if ($senuelo !== "") {
        cs_registrar("honeypot", "campo señuelo llenado con: " . substr($senuelo, 0, 80));
        return false;
    }

    // cs_elapsed lo escribe js/main.js: segundos transcurridos desde que cargó la página.
    // Si no llega, el envío no pasó por nuestro JavaScript.
    if (!isset($_POST["cs_elapsed"]) || !is_numeric($_POST["cs_elapsed"])) {
        cs_registrar("honeypot", "envío sin marca de tiempo (cs_elapsed ausente)");
        return false;
    }

    if ((float) $_POST["cs_elapsed"] < CS_SEGUNDOS_MINIMOS) {
        cs_registrar("honeypot", "envío en " . $_POST["cs_elapsed"] . "s (mínimo " . CS_SEGUNDOS_MINIMOS . "s)");
        return false;
    }

    return true;
}

// ---------------------------------------------------------------------------
// 4. reCAPTCHA v3
// ---------------------------------------------------------------------------

/**
 * Verifica el token contra la API de Google.
 *
 * Devuelve un veredicto en lugar de un booleano porque "no verificado" y
 * "es un bot" exigen respuestas distintas: al bot se le descarta en silencio,
 * pero a una persona con el token vencido hay que decirle qué pasó.
 *
 * @param string $token   valor de $_POST["recaptcha_token"]
 * @param string $accion  acción esperada ("contacto" | "informe")
 * @return string  "ok" | "bot" | "expirado"
 */
function cs_verificar_recaptcha($token, $accion)
{
    if (!CS_RECAPTCHA_ACTIVO) {
        return "ok";
    }

    if (CS_RECAPTCHA_SECRET === "" || CS_RECAPTCHA_SECRET === "PENDIENTE_SECRET_KEY") {
        cs_registrar("recaptcha", "secret key sin configurar: verificación omitida");
        return "ok";
    }

    $token = is_string($token) ? trim($token) : "";
    if ($token === "") {
        // Token ausente NO significa bot. Cuando la ejecución llega hasta aquí,
        // el envío ya superó el honeypot y la trampa de tiempo: un bot que no
        // ejecuta nuestro JavaScript no manda cs_elapsed y se descarta antes.
        //
        // Nuestro propio JS manda el token vacío cuando grecaptcha no está
        // definido (bloqueadores, extensiones de privacidad, redes que filtran
        // a Google), cuando Google no responde en 8 segundos o cuando execute()
        // falla. Tratarlo como bot hacía que esos visitantes vieran la página
        // de gracias sin que se enviara el correo: el lead se perdía en
        // silencio y sin sintoma visible.
        //
        // Manda CS_RECAPTCHA_FAIL_OPEN, igual que con los demás fallos ajenos
        // al visitante.
        cs_registrar("recaptcha", "token ausente; fail_open=" . (CS_RECAPTCHA_FAIL_OPEN ? "si" : "no"));
        return CS_RECAPTCHA_FAIL_OPEN ? "ok" : "expirado";
    }

    $respuesta = cs_peticion_recaptcha(array(
        "secret"   => CS_RECAPTCHA_SECRET,
        "response" => $token,
        "remoteip" => cs_ip_cliente(),
    ));

    // Error de transporte: Google no respondió.
    if ($respuesta === null) {
        cs_registrar("recaptcha", "sin respuesta de Google; fail_open=" . (CS_RECAPTCHA_FAIL_OPEN ? "si" : "no"));
        return CS_RECAPTCHA_FAIL_OPEN ? "ok" : "expirado";
    }

    $datos = json_decode($respuesta, true);
    if (!is_array($datos)) {
        cs_registrar("recaptcha", "respuesta ilegible de Google");
        return CS_RECAPTCHA_FAIL_OPEN ? "ok" : "expirado";
    }

    if (empty($datos["success"])) {
        $errores = isset($datos["error-codes"]) ? (array) $datos["error-codes"] : array();
        cs_registrar("recaptcha", "verificación fallida: " . ($errores ? implode(", ", $errores) : "sin detalle"));

        // timeout-or-duplicate = el token ya se usó o venció. Le pasa a personas
        // reales que recargan la página o hacen doble clic, no a los bots.
        // Devolver "bot" aquí mandaría al usuario a gracias.html sin enviar nada.
        if (in_array("timeout-or-duplicate", $errores, true)) {
            return "expirado";
        }

        // Principio: en reCAPTCHA v3 quien decide si es un bot es el SCORE.
        // Los códigos de error dicen "no se pudo verificar", que es otra cosa.
        // Todos ellos apuntan a un problema nuestro o del navegador del
        // visitante, así que manda CS_RECAPTCHA_FAIL_OPEN.
        //
        // invalid-input-response es el más importante de la lista: aparece
        // cuando la site key de js/main.js y la secret de config.local.php no
        // pertenecen al mismo sitio de reCAPTCHA. Como son dos valores en dos
        // archivos distintos, ese desajuste es fácil de provocar, y tratarlo
        // como bot descartaría el 100% de los leads mostrando "gracias" sin
        // ningún síntoma visible.
        $fallos_ajenos_al_visitante = array(
            "missing-input-secret",
            "invalid-input-secret",
            "invalid-input-response",
            "missing-input-response",
            "bad-request",
            "browser-error",
        );

        if (array_intersect($errores, $fallos_ajenos_al_visitante)) {
            return CS_RECAPTCHA_FAIL_OPEN ? "ok" : "expirado";
        }

        // Código no contemplado. Se aplica el mismo criterio: sin score no hay
        // evidencia de que sea un bot, y el honeypot ya filtró antes.
        cs_registrar("recaptcha", "código no contemplado; se aplica fail_open");
        return CS_RECAPTCHA_FAIL_OPEN ? "ok" : "expirado";
    }

    // La acción debe coincidir con la declarada en el frontend.
    if (isset($datos["action"]) && $datos["action"] !== $accion) {
        cs_registrar("recaptcha", "acción inesperada: " . $datos["action"] . " (esperada: " . $accion . ")");
        return "bot";
    }

    $score = isset($datos["score"]) ? (float) $datos["score"] : 0.0;
    if ($score < CS_RECAPTCHA_MIN_SCORE) {
        cs_registrar("recaptcha", "score " . $score . " por debajo del mínimo " . CS_RECAPTCHA_MIN_SCORE);
        return "bot";
    }

    return "ok";
}

/**
 * Envía la verificación con cURL y, si no está disponible, con file_get_contents.
 * Devuelve el cuerpo de la respuesta o null si hubo error de transporte.
 */
function cs_peticion_recaptcha(array $campos)
{
    $url = "https://www.google.com/recaptcha/api/siteverify";

    if (function_exists("curl_init")) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($campos),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ));
        $cuerpo = curl_exec($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // curl_close() quedó obsoleta en PHP 8.5 y emitiría un aviso que
        // rompería el header("Location: ...") posterior.
        if (PHP_VERSION_ID < 80500) {
            curl_close($ch);
        }

        if ($cuerpo !== false && $codigo === 200) {
            return $cuerpo;
        }

        return null;
    }

    $contexto = stream_context_create(array(
        "http" => array(
            "method"        => "POST",
            "header"        => "Content-Type: application/x-www-form-urlencoded\r\n",
            "content"       => http_build_query($campos),
            "timeout"       => 10,
            "ignore_errors" => true,
        ),
    ));

    $cuerpo = @file_get_contents($url, false, $contexto);

    return $cuerpo === false ? null : $cuerpo;
}

// ---------------------------------------------------------------------------
// Utilidades
// ---------------------------------------------------------------------------

function cs_ip_cliente()
{
    return isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
}

/**
 * Primera línea del registro.
 *
 * Si alguien pide el archivo por el navegador, el servidor lo ejecuta como PHP
 * y este exit() corta todo antes de imprimir una sola línea del contenido. Es
 * lo que protege el registro en Nginx y LiteSpeed, donde .htaccess no existe.
 */
function cs_guarda_log()
{
    return "<?php exit('Acceso denegado.'); ?>" . PHP_EOL;
}

/**
 * Resuelve a qué archivo se escribe el registro.
 *
 * Intenta primero la ruta de config.php y, si no puede escribir ahí (típico al
 * moverla fuera del directorio público sin dar permisos al usuario de PHP),
 * cae a la ruta interna. Así un problema de permisos degrada el registro en
 * lugar de dejarnos sin ninguno.
 *
 * @return string|false ruta utilizable, o false si ninguna lo es
 */
function cs_archivo_log()
{
    static $resuelto = null;

    if ($resuelto !== null) {
        return $resuelto;
    }

    $candidatos = array_unique(array(
        CS_LOG_ARCHIVO,
        __DIR__ . "/logs/formularios.log.php",
    ));

    foreach ($candidatos as $ruta) {
        $carpeta = dirname($ruta);

        if (!is_dir($carpeta)) {
            @mkdir($carpeta, 0755, true);
        }

        if (!is_dir($carpeta) || !is_writable($carpeta)) {
            continue;
        }

        if (!file_exists($ruta)) {
            if (@file_put_contents($ruta, cs_guarda_log(), LOCK_EX) === false) {
                continue;
            }
            @chmod($ruta, 0640);
        }

        if (is_writable($ruta)) {
            $resuelto = $ruta;
            return $resuelto;
        }
    }

    $resuelto = false;
    return $resuelto;
}

/**
 * Registra un bloqueo. Nunca interrumpe el flujo del formulario:
 * si el registro falla, el envío sigue su curso normal.
 */
function cs_registrar($tipo, $detalle)
{
    if (!CS_LOG_ACTIVO) {
        return;
    }

    $archivo = cs_archivo_log();
    if ($archivo === false) {
        return;
    }

    $linea = sprintf(
        "[%s] %s | ip=%s | %s%s",
        date("Y-m-d H:i:s"),
        strtoupper($tipo),
        cs_ip_cliente(),
        $detalle,
        PHP_EOL
    );

    @file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
}

/**
 * Descarta el envío simulando éxito.
 *
 * Se usa con bots (honeypot y reCAPTCHA): si les devolviéramos un error
 * sabrían qué ajustar para el siguiente intento. El correo nunca se envía.
 */
function cs_descartar_en_silencio()
{
    header("Location: " . CS_PAGINA_EXITO);
    exit;
}

/**
 * Catálogo de errores visibles.
 *
 * Se identifican por clave y no por texto libre porque el mensaje viaja en la
 * URL: así nada de lo que escribe el usuario se refleja en la página.
 */
function cs_catalogo_errores()
{
    return array(
        "campos" => array(
            400,
            "Por favor completa todos los campos requeridos correctamente.",
        ),
        "opcion" => array(
            400,
            "Selecciona una opción válida en los campos de solución y etapa del proyecto.",
        ),
        "enlace" => array(
            400,
            "Por seguridad no aceptamos enlaces ni direcciones web en el formulario. Retíralos e inténtalo de nuevo.",
        ),
        "expirado" => array(
            400,
            "La validación de seguridad expiró. Vuelve al formulario, recarga la página e inténtalo de nuevo.",
        ),
        "envio" => array(
            500,
            "Hubo un problema técnico al enviar tu mensaje. Intenta de nuevo más tarde o comunícate vía telefónica.",
        ),
    );
}

/**
 * Rechaza el envío redirigiendo a la página de error (patrón POST/Redirect/GET).
 *
 * No se imprime el error como respuesta al POST: si se hiciera, al recargar
 * con F5 el navegador reenviaría el formulario con el mismo token de reCAPTCHA,
 * Google lo marcaría como duplicado y el usuario terminaría en gracias.html sin
 * que se enviara nada. Con el redirect 303 la recarga es un GET inofensivo.
 *
 * @param string $codigo clave de cs_catalogo_errores()
 */
function cs_rechazar($codigo)
{
    $destino = basename($_SERVER["SCRIPT_NAME"]) . "?error=" . rawurlencode($codigo);
    header("Location: " . $destino, true, 303);
    exit;
}

/**
 * Dibuja la página de error. Se llama en la petición GET posterior al redirect.
 *
 * @param string $codigo clave de cs_catalogo_errores()
 */
function cs_pagina_error($codigo)
{
    $catalogo = cs_catalogo_errores();

    if (!isset($catalogo[$codigo])) {
        header("Location: " . CS_PAGINA_INICIO);
        exit;
    }

    list($estado, $mensaje) = $catalogo[$codigo];

    http_response_code($estado);
    header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control: no-store");

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<meta name="robots" content="noindex">'
       . '<title>No pudimos enviar tu mensaje | CREA Soluciones</title>'
       . '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
       . 'background:#F8F9FA;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#0E1111;padding:24px}'
       . '.caja{max-width:520px;background:#fff;padding:40px;border-radius:12px;'
       . 'box-shadow:0 10px 40px rgba(0,0,0,.08);text-align:center}'
       . 'h1{font-size:1.5rem;margin:0 0 16px}p{line-height:1.6;color:#4a5050;margin:0 0 24px}'
       . 'a{display:inline-block;background:#0E1111;color:#fff;text-decoration:none;'
       . 'padding:14px 28px;border-radius:6px;font-weight:600}</style></head><body>'
       . '<div class="caja"><h1>No pudimos enviar tu mensaje</h1><p>'
       . htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8")
       . '</p><a href="javascript:history.back()">Volver al formulario</a></div></body></html>';

    exit;
}

/**
 * Atiende las peticiones que no son POST.
 *
 * Si traen ?error=, muestran la página de error del redirect; si no, se manda
 * al inicio. Recargar la página de error solo repite este GET, sin reenviar nada.
 */
function cs_atender_get()
{
    if (isset($_GET["error"])) {
        cs_pagina_error($_GET["error"]);
    }

    header("Location: " . CS_PAGINA_INICIO);
    exit;
}
