<?php
// 1. CORRECCIÓN: Ruta correcta al archivo de configuración.
// Se usa __DIR__ para obtener el directorio actual y luego se retrocede (..) para ir a /config.
require_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Estas rutas son correctas porque PHPMailer está dentro del directorio 'data'.
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class Correo {

    public static function enviarCorreo($forEmail, $forName, $asunto, $body) {
        $mail = new PHPMailer(true); // Habilita excepciones

try {
            // 2. MEJORA: La depuración SMTP solo se activa si existe una constante DEBUG_MAIL y es true.
            // Debes definirla en tu config.php: define('DEBUG_MAIL', true); // o false para producción
            if (defined('DEBUG_MAIL') && DEBUG_MAIL) {
                $mail->SMTPDebug = 2; // Muestra traza detallada
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };
            }

            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
    $mail->Port       = 587;  
    
 // 3. MEJORA: Codificación simplificada. PHPMailer lo maneja automáticamente.
    $mail->CharSet = 'UTF-8';
  

            // Remitente y destinatario (sin codificación manual)
            $mail->setFrom(MAIL_USER, 'Administración Biblioteca');
            $mail->addAddress($forEmail, $forName);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto; // Asunto directo, sin codificar
    $mail->Body    = '
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
                    ' . nl2br(htmlspecialchars($body)) . '
    </body>
    </html>';
            $mail->AltBody = htmlspecialchars($body); // Versión en texto plano (también sanitizada)

            // 4. CORRECIÓN: Lógica de envío simplificada. Si send() falla, lanza una excepción.
            $mail->send();
            
            // Si llega aquí, el correo se envió con éxito.
        error_log("Correo enviado exitosamente a: $forEmail");
            return ["success" => true, "mensaje" => "Correo enviado correctamente."];

        } catch (Exception $e) {
            // 5. MEJORA: Mensaje de error detallado para facilitar la depuración.
            // Se registra el error completo en el log del servidor.
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            // Se devuelve un mensaje más informativo.
            return ["success" => false, "mensaje" => 'El correo no pudo ser enviado. Error: ' . $mail->ErrorInfo];
    }
    }
}

