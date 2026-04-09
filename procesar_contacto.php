<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"] ?? ""));
    $email = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST["phone"] ?? ""));
    $service = strip_tags(trim($_POST["service"] ?? ""));
    $stage = strip_tags(trim($_POST["stage"] ?? ""));
    $message = trim($_POST["message"] ?? "Sin mensaje proporcionado.");

    if (empty($name) || empty($email) || empty($phone) || empty($service) || empty($stage) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Por favor completa todos los campos requeridos correctamente.";
        exit;
    }

    $recipient = "info@creasoluciones.com.mx";
    $subject = "Nuevo contacto Web: $name";

    $email_content = "Has recibido una nueva solicitud de contacto desde la p\xC3\xA1gina web.\n\n";
    $email_content .= "INFORMACI\xC3\x93N DEL PROSPECTO:\n";
    $email_content .= "----------------------------------------\n";
    $email_content .= "Nombre Completo: $name\n";
    $email_content .= "Correo electr\xC3\xB3nico: $email\n";
    $email_content .= "Tel\xC3\xA9fono: $phone\n\n";
    
    $email_content .= "DETALLES DEL PROYECTO:\n";
    $email_content .= "----------------------------------------\n";
    $email_content .= "Soluci\xC3\xB3n de inter\xC3\xA9s: $service\n";
    $email_content .= "Etapa del proyecto: $stage\n\n";
    
    $email_content .= "MENSAJE / OBJETIVOS:\n";
    $email_content .= "----------------------------------------\n";
    $email_content .= "$message\n";

    $email_headers = "From: webmaster@creasoluciones.com.mx\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "MIME-Version: 1.0\r\n";
    $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($recipient, $subject, $email_content, $email_headers)) {
        header("Location: gracias.html");
        exit;
    } else {
        http_response_code(500);
        echo "Lo sentimos, hubo un problema técnico al enviar tu mensaje. Intenta de nuevo más tarde o comunícate vía telefónica.";
    }
} else {
    header("Location: index.html");
    exit;
}
?>
