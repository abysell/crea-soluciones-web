<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"] ?? ""));
    $email = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
    $company = strip_tags(trim($_POST["company"] ?? ""));

    if (empty($name) || empty($email) || empty($company) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Por favor completa todos los campos requeridos correctamente.";
        exit;
    }

    $recipient = "info@creasoluciones.com.mx";
    $subject = "Solicitud de Informe Estratégico Web: $name";

    $email_content = "Se ha solicitado la descarga de un Informe Estratégico desde la página web.\n\n";
    $email_content .= "DATOS DEL SOLICITANTE:\n";
    $email_content .= "----------------------------------------\n";
    $email_content .= "Nombre Completo: $name\n";
    $email_content .= "Correo Corporativo: $email\n";
    $email_content .= "Empresa: $company\n\n";

    $email_headers = "From: webmaster@creasoluciones.com.mx\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "MIME-Version: 1.0\r\n";
    $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($recipient, $subject, $email_content, $email_headers)) {
        header("Location: gracias.html");
        exit;
    } else {
        http_response_code(500);
        echo "Lo sentimos, hubo un problema técnico al procesar tu solicitud. Intenta de nuevo más tarde.";
    }
} else {
    header("Location: index.html");
    exit;
}
?>
