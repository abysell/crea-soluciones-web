<?php
/**
 * Procesa el formulario de contacto (form-contacto y form-footer).
 * Los controles de seguridad viven en seguridad.php y se configuran en config.php.
 */

require_once __DIR__ . "/seguridad.php";
require_once __DIR__ . "/zoho.php";

// Las peticiones GET muestran la página de error del redirect, o van al inicio.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    cs_atender_get();
}

// --- 1. Honeypot y trampa de tiempo -----------------------------------------
// Un bot no sabe que falló: se le devuelve la página de éxito sin enviar nada.
if (!cs_honeypot_superado()) {
    cs_descartar_en_silencio();
}

// --- 2. reCAPTCHA v3 ---------------------------------------------------------
$token = isset($_POST["recaptcha_token"]) ? $_POST["recaptcha_token"] : "";
$veredicto = cs_verificar_recaptcha($token, "contacto");

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
$phone   = cs_sanitizar($_POST["phone"] ?? "", 30);
$service = cs_sanitizar($_POST["service"] ?? "", 60);
$stage   = cs_sanitizar($_POST["stage"] ?? "", 60);
$message = cs_sanitizar($_POST["message"] ?? "", 2000);

// El teléfono solo conserva dígitos y separadores habituales.
$phone = preg_replace('/[^0-9+()\-\s]/', "", $phone);
$phone = trim(preg_replace('/\s+/', " ", $phone));

// Campaña de origen (UTMs y clic de Google Ads), capturada por js/main.js.
$atribucion = cs_atribucion_recibida();

// --- 4. Validación de obligatorios ------------------------------------------
if ($name === "" || $email === "" || $phone === "" || $service === "" || $stage === ""
    || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    cs_rechazar("campos");
}

// Las listas desplegables solo aceptan los valores que publica el formulario.
$servicios_validos = array(
    "estudio-mercado", "estudio-financiero", "mayor-uso", "reposicionamiento",
    "geomarketing", "test-fit", "avaluo", "reporte", "otros",
);
$etapas_validas = array(
    "idea", "estudios", "anteproyecto", "comercializacion", "expansion", "otra",
);

if (!in_array($service, $servicios_validos, true) || !in_array($stage, $etapas_validas, true)) {
    cs_registrar("valores", "opción fuera de catálogo: service=$service stage=$stage");
    cs_rechazar("opcion");
}

// --- 5. Bloqueo de enlaces ---------------------------------------------------
// El correo queda fuera: siempre contiene un dominio y ya se validó arriba.
if (cs_algun_campo_con_enlace(array($name, $phone, $service, $stage, $message))) {
    cs_registrar("enlace", "envío con enlaces de " . $email);
    cs_rechazar("enlace");
}

// --- 6. Envío ----------------------------------------------------------------
$asunto = cs_asunto("Nuevo contacto Web: " . $name);

$cuerpo  = "Has recibido una nueva solicitud de contacto desde la página web.\n\n";
$cuerpo .= "INFORMACIÓN DEL PROSPECTO:\n";
$cuerpo .= "----------------------------------------\n";
$cuerpo .= "Nombre Completo: $name\n";
$cuerpo .= "Correo electrónico: $email\n";
$cuerpo .= "Teléfono: $phone\n\n";

$cuerpo .= "DETALLES DEL PROYECTO:\n";
$cuerpo .= "----------------------------------------\n";
$cuerpo .= "Solución de interés: $service\n";
$cuerpo .= "Etapa del proyecto: $stage\n\n";

$cuerpo .= "MENSAJE / OBJETIVOS:\n";
$cuerpo .= "----------------------------------------\n";
$cuerpo .= ($message !== "" ? $message : "Sin mensaje proporcionado.") . "\n";

// La sección de campaña solo aparece si hay algo que mostrar: en el tráfico
// directo estaría siempre vacía y solo estorbaría al leer el correo.
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
// Va después del correo a propósito: el correo es la fuente de verdad de cada
// lead, así que se asegura primero. Un fallo aquí se registra pero no cambia
// lo que ve el visitante ni tumba el envío; el dato ya está en la bandeja.
cs_enviar_a_zoho(array(
    "nombre"     => $name,
    "correo"     => $email,
    "telefono"   => $phone,
    "servicio"   => $service,
    "etapa"      => $stage,
    "mensaje"    => $message,
    "atribucion" => $atribucion,
));

if ($correo_enviado) {
    header("Location: " . CS_PAGINA_EXITO);
    exit;
}

cs_registrar("envio", "mail() devolvió false para " . $email);
cs_rechazar("envio");
