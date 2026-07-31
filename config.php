<?php
/**
 * Configuración central de los formularios de CREA Soluciones.
 *
 * Las claves reales NO van en este archivo: van en config.local.php, que está
 * excluido del repositorio. Este archivo solo trae marcadores PENDIENTE, así
 * que puede commitearse sin filtrar nada.
 *
 * Cómo funciona: config.local.php se carga primero y define las constantes
 * sensibles. Más abajo se usan con "defined() || define()", así que el valor
 * local gana y el marcador se ignora. Si el archivo no existe, todo sigue
 * funcionando con los marcadores (reCAPTCHA y Zoho quedan inactivos).
 *
 * La site key de reCAPTCHA vive además en js/main.js -> RECAPTCHA_SITE_KEY.
 * Esa sí hay que ponerla a mano: es pública y el navegador la necesita.
 */

if (is_file(__DIR__ . "/config.local.php")) {
    require_once __DIR__ . "/config.local.php";
}

// ---------------------------------------------------------------------------
// Correo
// ---------------------------------------------------------------------------
define("CS_DESTINATARIO", "info@creasoluciones.com.mx");
define("CS_REMITENTE",    "webmaster@creasoluciones.com.mx");
define("CS_PAGINA_EXITO", "gracias.html");
define("CS_PAGINA_INICIO", "index.html");

// ---------------------------------------------------------------------------
// Zoho CRM — Web to Lead
// ---------------------------------------------------------------------------
// El envío lo hace PHP con cURL, no el navegador: el formulario ya se manda a
// procesar_contacto.php y un <form> solo puede tener un destino. Enviarlo desde
// el servidor conserva toda la validación y el filtrado antispam.
//
// Los valores salen del formulario que genera Zoho en Configuración >
// Desarrollador > Formularios web > Clientes potenciales.
//
// IMPORTANTE: en el generador de Zoho deja DESACTIVADO el reCAPTCHA. Está
// pensado para envíos desde el navegador y bloquearía los nuestros, que salen
// del servidor. Nuestro propio reCAPTCHA ya filtró antes.
define("CS_ZOHO_ACTIVO", true);
define("CS_ZOHO_URL",    "https://crm.zoho.com/crm/WebToLeadForm");

defined("CS_ZOHO_XNQSJSDP") || define("CS_ZOHO_XNQSJSDP",   "PENDIENTE_XNQSJSDP");
defined("CS_ZOHO_XMIWTLD") || define("CS_ZOHO_XMIWTLD",    "PENDIENTE_XMIWTLD");
define("CS_ZOHO_ACTIONTYPE", "TGVhZHM=");   // base64 de "Leads"
define("CS_ZOHO_RETURN_URL", "https://creasoluciones.com.mx/");

// Valor del desplegable "Fuente de Cliente potencial" (Lead Source).
//
// "Formulario Web" es el valor que ya usan los leads existentes en el CRM, así
// que los nuevos quedan agrupados con ellos. Curiosamente NO aparece entre las
// opciones del formulario que generó Zoho: ese formulario expone solo un
// subconjunto del desplegable. Zoho valida contra la lista completa del campo,
// no contra la del formulario, por lo que el valor se acepta igual.
//
// Si algún día el campo llegara vacío en el CRM, es señal de que este texto ya
// no coincide con ninguna opción: revisa el desplegable en Zoho.
define("CS_ZOHO_LEAD_SOURCE", "Formulario Web");

// Estado inicial del lead. "Lead" es el que trae seleccionado el formulario.
define("CS_ZOHO_LEAD_STATUS", "Lead");

// Campo personalizado LEADCF4 ("Nombre_formulario"): distingue en el CRM de qué
// formulario vino cada lead. Se respeta la convención de los que ya existen,
// como "Formulario Web - Usos Mixtos".
define("CS_ZOHO_NOMBRE_FORMULARIO", "Formulario Web - Contacto");

// El formulario generado NO incluye "Company" y el único campo obligatorio es
// "Last Name", así que no se envía. Si algún día lo vuelves obligatorio en
// Zoho, pon aquí un valor por defecto y se empezará a mandar.
define("CS_ZOHO_EMPRESA_POR_DEFECTO", "");

// Segundos máximos de espera. Si Zoho tarda más, se aborta y se registra: el
// visitante no debe quedarse esperando y el correo ya salió.
define("CS_ZOHO_TIMEOUT", 8);

/**
 * Traduce el valor del desplegable "Solución de interés" del sitio al texto
 * exacto del campo LEADCF11 en Zoho.
 *
 * Zoho valida contra su lista de opciones: cualquier texto que no coincida
 * carácter por carácter deja el campo vacío, sin avisar.
 *
 * "mayor-uso" y "test-fit" no tienen opción equivalente en Zoho todavía, así
 * que caen en "Otros" y el servicio real se anota en la descripción para no
 * perder el dato. Si los agregas al desplegable de Zoho, actualiza este mapa.
 */
function cs_zoho_servicios()
{
    return array(
        "estudio-mercado"   => "Estudio de Mercado",
        "estudio-financiero" => "Estudio Financiero",
        "reposicionamiento" => "Estudio de Reposicionamiento",
        "geomarketing"      => "Geomarketing",
        "avaluo"            => "Avalúo",
        "reporte"           => "Reporte",
        "otros"             => "Otros",
        "mayor-uso"         => "Otros",   // sin equivalente en Zoho
        "test-fit"          => "Otros",   // sin equivalente en Zoho
    );
}

/**
 * Traduce la etapa del proyecto al texto exacto del campo LEADCF10 en Zoho.
 *
 * "otra" no tiene equivalente: se deja el campo sin valor y la selección
 * original queda anotada en la descripción.
 */
function cs_zoho_etapas()
{
    return array(
        "idea"             => "Idea del Proyecto",
        "estudios"         => "Estudios de Mercado y Financiero",
        "anteproyecto"     => "Anteproyecto Arquitectónico",
        "comercializacion" => "Comercialización",
        "expansion"        => "Expansión",
        "otra"             => "",   // sin equivalente en Zoho
    );
}

/**
 * Etiquetas legibles de los desplegables del sitio, para la descripción.
 */
function cs_etiquetas_sitio()
{
    return array(
        "estudio-mercado"    => "Estudio de Mercado",
        "estudio-financiero" => "Estudio Financiero",
        "mayor-uso"          => "Estudio de Mayor y Mejor Uso",
        "reposicionamiento"  => "Reposicionamiento / Reconversión",
        "geomarketing"       => "Geomarketing",
        "test-fit"           => "Test Fit Arquitectónico",
        "avaluo"             => "Avalúo Inmobiliario",
        "reporte"            => "Reporte",
        "otros"              => "Otra Solución",
        "idea"               => "Idea del Proyecto",
        "estudios"           => "Estudios de Mercado y Financiero",
        "anteproyecto"       => "Anteproyecto Arquitectónico",
        "comercializacion"   => "Comercialización",
        "expansion"          => "Expansión",
        "otra"               => "Otra",
    );
}

/**
 * Arma los campos del lead con los nombres exactos que espera Zoho.
 *
 * Los nombres llevan espacios ("Last Name", no "Last_Name") porque así los
 * genera Zoho. PHP los muestra con guion bajo al recibirlos, pero por la red
 * viajan con espacio codificado.
 *
 * @param array $d datos ya sanitizados del formulario
 * @return array
 */
function cs_zoho_campos(array $d)
{
    $servicios = cs_zoho_servicios();
    $etapas    = cs_zoho_etapas();
    $etiquetas = cs_etiquetas_sitio();

    $servicio_zoho = isset($servicios[$d["servicio"]]) ? $servicios[$d["servicio"]] : "Otros";
    $etapa_zoho    = isset($etapas[$d["etapa"]]) ? $etapas[$d["etapa"]] : "";

    $servicio_real = isset($etiquetas[$d["servicio"]]) ? $etiquetas[$d["servicio"]] : $d["servicio"];
    $etapa_real    = isset($etiquetas[$d["etapa"]]) ? $etiquetas[$d["etapa"]] : $d["etapa"];

    // La descripción repite la selección original del visitante. Es la red de
    // seguridad para los valores que Zoho no tiene en su lista: sin esto, un
    // "Test Fit Arquitectónico" llegaría al CRM solo como "Otros".
    $descripcion  = "Solución de interés: " . $servicio_real . "\n";
    $descripcion .= "Etapa del proyecto: " . $etapa_real . "\n\n";
    $descripcion .= "Mensaje:\n" . ($d["mensaje"] !== "" ? $d["mensaje"] : "Sin mensaje proporcionado.");

    $campos = array(
        "Last Name"   => $d["nombre"],
        "Email"       => $d["correo"],
        "Phone"       => $d["telefono"],
        "LEADCF11"    => $servicio_zoho,                  // Servicio a contratar
        "Lead Source" => CS_ZOHO_LEAD_SOURCE,
        "Lead Status" => CS_ZOHO_LEAD_STATUS,
        "LEADCF4"     => CS_ZOHO_NOMBRE_FORMULARIO,       // Nombre_formulario
        "Description" => $descripcion,
    );

    // Solo se manda la etapa si existe en la lista de Zoho: enviar un texto
    // fuera de catálogo no da error, simplemente deja el campo vacío.
    if ($etapa_zoho !== "") {
        $campos["LEADCF10"] = $etapa_zoho;                // Etapa del proyecto
    }

    if (CS_ZOHO_EMPRESA_POR_DEFECTO !== "") {
        $campos["Company"] = CS_ZOHO_EMPRESA_POR_DEFECTO;
    }

    return cs_zoho_agregar_atribucion($campos, $d);
}

/**
 * Añade los campos de campaña al lead. Lo comparten los dos formularios.
 *
 * Los nombres LEADCF corresponden a los campos que ya existen en el CRM;
 * se tomaron del formulario que generó Zoho.
 *
 * @param array $campos campos del lead ya armados
 * @param array $d      datos del formulario, con la clave "atribucion"
 * @return array
 */
function cs_zoho_agregar_atribucion(array $campos, array $d)
{
    $atribucion = isset($d["atribucion"]) && is_array($d["atribucion"]) ? $d["atribucion"] : array();

    $mapa_atribucion = array(
        "utm_source"   => "LEADCF6",
        "utm_campaign" => "LEADCF1",
        "utm_term"     => "LEADCF2",
        "utm_medium"   => "LEADCF3",
        "utm_content"  => "LEADCF8",
    );

    foreach ($mapa_atribucion as $origen => $campo_zoho) {
        if (!empty($atribucion[$origen])) {
            $campos[$campo_zoho] = $atribucion[$origen];
        }
    }

    // El clic de Google Ads viaja por partida doble: zc_gad es el campo de
    // control que Zoho usa para atribuir conversiones offline en Google Ads, y
    // LEADCF5 es el campo visible con el mismo nombre en la ficha del lead.
    if (!empty($atribucion["gclid"])) {
        $campos["zc_gad"]   = $atribucion["gclid"];
        $campos["LEADCF5"]  = $atribucion["gclid"];
    }

    return $campos;
}

// ---------------------------------------------------------------------------
// Catálogo de informes descargables
// ---------------------------------------------------------------------------
// Relaciona la clave que manda el formulario con dos textos: el título que
// aparece en el correo y en la descripción del lead, y el nombre de formulario
// que se registra en Zoho para distinguir de cuál de los dos recursos vino.
//
// El formulario solo envía la clave: los textos salen de aquí, así nadie puede
// inyectar contenido arbitrario manipulando el campo oculto.
//
// Las claves deben coincidir con los atributos data-informe de los botones
// .toggle-modal en index.html. Al agregar un informe nuevo, súmalo en ambos
// lados; si no, llegará como "No especificado".
function cs_catalogo_informes()
{
    return array(
        "demanda-inmobiliaria" => array(
            "titulo"     => "Informe Especial: Dónde se está creando demanda inmobiliaria en México y América",
            "formulario" => "Formulario Web - Informe Especial",
        ),
        "invertir-2026" => array(
            "titulo"     => "Guía Estratégica: Cómo invertir en bienes raíces en 2026 sin sobredimensionar el riesgo",
            "formulario" => "Formulario Web - Guía Estratégica",
        ),
    );
}

// Valores cuando el recurso no se pudo identificar: o se agregó un botón sin
// actualizar el catálogo, o alguien manipuló el campo oculto.
define("CS_INFORME_SIN_IDENTIFICAR", "No especificado (solicitud genérica)");
define("CS_ZOHO_FORMULARIO_INFORME", "Formulario Web - Informe");

// Valor de "Servicio a contratar" (LEADCF11) para las descargas de informes.
//
// Se usa "Otros" y no "Reporte" por lo que dice el nombre del campo: descargar
// un informe gratuito no es querer contratar un reporte. Marcarlos como
// "Reporte" mezclaría en una misma categoría a quien quiere encargar un estudio
// y a quien solo bajó un PDF, e inflaría cualquier conteo por servicio.
// "Otros" es un cajón de sastre, así que absorber esa ambigüedad es su función.
//
// No se pierde información: Nombre_formulario identifica de cuál de los dos
// recursos vino cada lead.
//
// Si prefieres que estos leads no entren en ninguna categoría, deja la cadena
// vacía y el campo no se enviará: aparecerá como "-None-" en la ficha.
define("CS_ZOHO_SERVICIO_INFORME", "Otros");

/**
 * Arma los campos del lead para el formulario de descarga de informes.
 *
 * Se manda al mismo formulario Web to Lead que el de contacto; lo que cambia
 * es la información:
 *
 *   - "Servicio a contratar" es siempre "Reporte", que es lo que en el fondo
 *     está pidiendo quien descarga uno de estos documentos.
 *   - "Nombre_formulario" distingue cuál de los dos recursos solicitó.
 *   - La descripción reproduce el bloque del correo, para que quien lea la
 *     ficha vea lo mismo que quien lee la bandeja.
 *
 * Este formulario sí pide la empresa, así que "Company" llega con un valor
 * real y no con el marcador del formulario de contacto.
 *
 * @param array $d claves: nombre, correo, empresa, informe, formulario, atribucion
 * @return array
 */
function cs_zoho_campos_informe(array $d)
{
    $descripcion  = "RECURSO SOLICITADO:\n";
    $descripcion .= "----------------------------------------\n";
    $descripcion .= $d["informe"];

    $campos = array(
        "Last Name"   => $d["nombre"],
        "Email"       => $d["correo"],
        "Company"     => $d["empresa"],
        "Lead Source" => CS_ZOHO_LEAD_SOURCE,
        "Lead Status" => CS_ZOHO_LEAD_STATUS,
        "LEADCF4"     => $d["formulario"],                // Nombre_formulario
        "Description" => $descripcion,
    );

    // Con la constante vacía el campo no se envía y queda como "-None-".
    if (CS_ZOHO_SERVICIO_INFORME !== "") {
        $campos["LEADCF11"] = CS_ZOHO_SERVICIO_INFORME;   // Servicio a contratar
    }

    return cs_zoho_agregar_atribucion($campos, $d);
}

// ---------------------------------------------------------------------------
// reCAPTCHA v3  (https://www.google.com/recaptcha/admin)
// ---------------------------------------------------------------------------
define("CS_RECAPTCHA_ACTIVO",  true);
defined("CS_RECAPTCHA_SECRET") || define("CS_RECAPTCHA_SECRET",  "PENDIENTE_SECRET_KEY");   // llave privada (servidor)
defined("CS_RECAPTCHA_SITEKEY") || define("CS_RECAPTCHA_SITEKEY", "PENDIENTE_SITE_KEY");     // llave pública (informativa)

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
