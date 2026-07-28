<?php
/**
 * Envío de leads a Zoho CRM mediante Web to Lead.
 *
 * Se hace de servidor a servidor con cURL, no desde el navegador. Eso permite
 * que el formulario siga pasando por nuestra validación y filtros antispam
 * antes de que el dato llegue al CRM.
 *
 * Regla de oro: este archivo NUNCA debe impedir que el formulario termine. El
 * correo ya salió cuando se le invoca; si Zoho falla, se registra el problema
 * y el visitante ve su página de gracias igual.
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/seguridad.php";

/**
 * Manda un lead a Zoho CRM.
 *
 * @param array $datos claves: nombre, correo, telefono, servicio, etapa, mensaje
 * @return bool true si Zoho acepto el envio
 */
function cs_enviar_a_zoho(array $datos)
{
    if (!CS_ZOHO_ACTIVO) {
        return false;
    }

    if (CS_ZOHO_XNQSJSDP === "" || strpos(CS_ZOHO_XNQSJSDP, "PENDIENTE") === 0) {
        cs_registrar("zoho", "claves sin configurar: envío omitido");
        return false;
    }

    if (!function_exists("curl_init")) {
        cs_registrar("zoho", "cURL no disponible en el servidor: envío omitido");
        return false;
    }

    // Campos de control que Zoho exige para aceptar el formulario.
    $campos = array(
        "xnQsjsdp"   => CS_ZOHO_XNQSJSDP,
        "xmIwtLD"    => CS_ZOHO_XMIWTLD,
        "actionType" => CS_ZOHO_ACTIONTYPE,
        "returnURL"  => CS_ZOHO_RETURN_URL,

        // Campo de seguimiento de Google Ads. El formulario lo manda vacío.
        "zc_gad"     => "",

        // Honeypot propio de Zoho (el nombre es base64 de "honeypot"). Debe
        // viajar vacío, igual que en un envío legítimo desde el navegador:
        // si llevara contenido, Zoho descartaría el lead por spam.
        "aG9uZXlwb3Q" => "",
    );

    // Campos del lead. Se limpian de saltos de línea en los valores cortos para
    // que un dato raro no descoloque el formulario que interpreta Zoho.
    foreach (cs_zoho_campos($datos) as $nombre => $valor) {
        $campos[$nombre] = is_string($valor) ? $valor : (string) $valor;
    }

    $ch = curl_init(CS_ZOHO_URL);
    curl_setopt_array($ch, array(
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($campos),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => CS_ZOHO_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        // Zoho responde con una redirección a returnURL. No hace falta seguirla:
        // el lead ya quedó creado y seguirla solo añadiría latencia.
        CURLOPT_FOLLOWLOCATION => false,
    ));

    $cuerpo = curl_exec($ch);
    $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);

    // curl_close() quedó obsoleta en PHP 8.5 y su aviso rompería el
    // header("Location: ...") que viene después.
    if (PHP_VERSION_ID < 80500) {
        curl_close($ch);
    }

    if ($cuerpo === false) {
        cs_registrar("zoho", "fallo de conexión: " . $error);
        return false;
    }

    // Web to Lead no devuelve un estado legible por máquina: responde 200 o una
    // redirección 302 hacia returnURL. Solo podemos confirmar que el POST se
    // entregó, no que el registro se haya creado con todos sus campos. Por eso
    // el correo sigue siendo la fuente de verdad de cada lead.
    if ($codigo === 200 || ($codigo >= 300 && $codigo < 400)) {
        return true;
    }

    cs_registrar("zoho", "respuesta inesperada: HTTP " . $codigo);
    return false;
}
