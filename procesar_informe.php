<?php
/**
 * Procesa el formulario de descarga de informes (form-informes).
 * Los controles de seguridad viven en seguridad.php y se configuran en config.php.
 */

require_once __DIR__ . "/seguridad.php";
require_once __DIR__ . "/zoho.php";

// Las peticiones GET muestran la página de error del redirect, o van al inicio.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    cs_atender_get();
}

// --- 1. Honeypot y trampa de tiempo -----------------------------------------
if (!cs_honeypot_superado()) {
    cs_descartar_en_silencio();
}

// --- 2. reCAPTCHA v3 ---------------------------------------------------------
$token = isset($_POST["recaptcha_token"]) ? $_POST["recaptcha_token"] : "";
$veredicto = cs_verificar_recaptcha($token, "informe");

if ($veredicto === "expirado") {
    // Token vencido o repetido: casi siempre es una persona recargando la
    // página. Se le avisa en lugar de descartarlo como si fuera un bot.
    cs_rechazar("expirado");
} elseif ($veredicto !== "ok") {
    cs_descartar_en_silencio();
}

// --- 3. Sanitización ---------------------------------------------------------
$name    = cs_sanitizar_encabezado($_POST["name"] ?? "", 120);
$email   = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
$company = cs_sanitizar($_POST["company"] ?? "", 120);

// Informe solicitado: llega como clave desde el campo oculto del modal.
// El título que se imprime en el correo sale del catálogo, nunca del POST,
// así que este campo no puede usarse para inyectar texto.
$clave_informe = cs_sanitizar($_POST["informe"] ?? "", 60);
$catalogo      = cs_catalogo_informes();

if ($clave_informe !== "" && !isset($catalogo[$clave_informe])) {
    // Clave desconocida: o se agregó un botón sin actualizar el catálogo,
    // o alguien manipuló el campo. Se registra y se sigue: el lead es válido.
    cs_registrar("informe", "clave fuera de catálogo: " . $clave_informe);
    $clave_informe = "";
}

$informe    = CS_INFORME_SIN_IDENTIFICAR;
$formulario = CS_ZOHO_FORMULARIO_INFORME;

if ($clave_informe !== "") {
    $informe    = $catalogo[$clave_informe]["titulo"];
    $formulario = $catalogo[$clave_informe]["formulario"];
}

// Campaña de origen (UTMs y clic de Google Ads), capturada por js/main.js.
$atribucion = cs_atribucion_recibida();

// --- 4. Validación de obligatorios ------------------------------------------
if ($name === "" || $email === "" || $company === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    cs_rechazar("campos");
}

// --- 5. Bloqueo de enlaces ---------------------------------------------------
// El correo queda fuera: siempre contiene un dominio y ya se validó arriba.
if (cs_algun_campo_con_enlace(array($name, $company))) {
    cs_registrar("enlace", "envío con enlaces de " . $email);
    cs_rechazar("enlace");
}

// --- 6. Envío ----------------------------------------------------------------
$asunto = cs_asunto("Solicitud de Informe Estratégico Web: " . $name);

$cuerpo  = "Se ha solicitado la descarga de un Informe Estratégico desde la página web.\n\n";
$cuerpo .= "RECURSO SOLICITADO:\n";
$cuerpo .= "----------------------------------------\n";
$cuerpo .= "$informe\n\n";

$cuerpo .= "DATOS DEL SOLICITANTE:\n";
$cuerpo .= "----------------------------------------\n";
$cuerpo .= "Nombre Completo: $name\n";
$cuerpo .= "Correo Corporativo: $email\n";
$cuerpo .= "Empresa: $company\n";

// La sección de campaña solo aparece si hay algo que mostrar.
if (cs_hay_atribucion($atribucion)) {
    $cuerpo .= "\nCAMPAÑA DE ORIGEN:\n";
    $cuerpo .= "----------------------------------------\n";
    foreach ($atribucion as $clave => $valor) {
        if ($valor !== "") {
            $cuerpo .= $clave . ": " . $valor . "\n";
        }
    }
}

$encabezados  = "From: " . CS_REMITENTE . "\r\n";
$encabezados .= "Reply-To: " . $email . "\r\n";
$encabezados .= "MIME-Version: 1.0\r\n";
$encabezados .= "Content-Type: text/plain; charset=UTF-8\r\n";
$encabezados .= "Content-Transfer-Encoding: 8bit\r\n";

$correo_enviado = mail(CS_DESTINATARIO, $asunto, $cuerpo, $encabezados);

// --- 7. Zoho CRM -------------------------------------------------------------
// Igual que en el formulario de contacto, va después del correo: este es la
// fuente de verdad del lead y se asegura primero. Un fallo aquí se registra
// pero no cambia lo que ve el visitante.
cs_enviar_a_zoho(array(
    "nombre"     => $name,
    "correo"     => $email,
    "empresa"    => $company,
    "informe"    => $informe,
    "formulario" => $formulario,
    "atribucion" => $atribucion,
), "informe");

if ($correo_enviado) {
    header("Location: " . CS_PAGINA_EXITO);
    exit;
}

cs_registrar("envio", "mail() devolvió false para " . $email);
cs_rechazar("envio");
