<?php
// Establecer el encabezado para que la respuesta sea JSON
header('Content-Type: application/json');

// Incluir los archivos de clases necesarios
require_once '../MODELO/class_log.php';

// Asegurarse de que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la acción solicitada (solicitar_codigo o cambiar_contrasena)
    $accion = $_POST['accion'] ?? '';

    // Instanciar la clase Login
    $login = new Login();

    // Inicializar la respuesta
    $response = ['success' => false, 'message' => ''];

    // Manejar las diferentes acciones
    switch ($accion) {
        case 'solicitar_codigo':
            $email = $_POST['email'] ?? '';

            // Validar que el correo no esté vacío
            if (empty($email)) {
                $response['message'] = 'Por favor, ingrese su correo electrónico.';
                echo json_encode($response);
                exit();
            }

            // Intentar generar y guardar el código de recuperación
            $resultado_codigo = $login->generarYGuardarCodigoRecuperacion($email);

            if ($resultado_codigo['success']) {
                $codigo = $resultado_codigo['codigo'];

                // --- PLANTILLA DE CORREO MODERNA Y SIMPLE ---
                $para = $email;
                $asunto = '🔐 Código de Recuperación - PPUD';

                // Contenido HTML moderno del mensaje
                $mensaje = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recuperación de Contraseña</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    background-color: #f5f5f7;
                    color: #1d1d1f;
                    line-height: 1.5;
                }
                .container {
                    max-width: 520px;
                    margin: 40px auto;
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    overflow: hidden;
                }
                .header {
                    background: linear-gradient(135deg, #007AFF, #5856D6);
                    color: white;
                    text-align: center;
                    padding: 40px 30px;
                }
                .header h1 {
                    font-size: 24px;
                    font-weight: 600;
                    margin-top: 12px;
                }
                .icon {
                    font-size: 40px;
                    opacity: 0.9;
                }
                .content {
                    padding: 40px 30px;
                }
                .title {
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 16px;
                    color: #1d1d1f;
                }
                .description {
                    color: #86868b;
                    font-size: 15px;
                    margin-bottom: 32px;
                }
                .code-section {
                    background: #f5f5f7;
                    border-radius: 12px;
                    padding: 24px;
                    text-align: center;
                    margin: 24px 0;
                }
                .code-label {
                    font-size: 13px;
                    color: #86868b;
                    font-weight: 500;
                    margin-bottom: 8px;
                }
                .code {
                    font-size: 32px;
                    font-weight: 700;
                    color: #007AFF;
                    letter-spacing: 4px;
                    font-family: SF Mono, Monaco, Consolas, monospace;
                }
                .warning {
                    background: #fff3cd;
                    border-left: 4px solid #ffb020;
                    padding: 16px 20px;
                    border-radius: 8px;
                    margin: 24px 0;
                }
                .warning-text {
                    font-size: 14px;
                    color: #856404;
                }
                .steps {
                    background: #f0f9ff;
                    border-radius: 12px;
                    padding: 20px;
                    margin: 24px 0;
                }
                .steps-title {
                    font-size: 14px;
                    font-weight: 600;
                    color: #0369a1;
                    margin-bottom: 12px;
                }
                .steps-list {
                    font-size: 14px;
                    color: #0369a1;
                    line-height: 1.6;
                }
                .footer {
                    background: #f5f5f7;
                    padding: 24px 30px;
                    text-align: center;
                    border-top: 1px solid #e5e5e7;
                }
                .footer-text {
                    font-size: 13px;
                    color: #86868b;
                    margin-bottom: 8px;
                }
                .company {
                    font-size: 13px;
                    color: #1d1d1f;
                    font-weight: 500;
                }
                @media (max-width: 600px) {
                    .container {
                        margin: 20px;
                        border-radius: 12px;
                    }
                    .header, .content {
                        padding: 30px 20px;
                    }
                    .code {
                        font-size: 28px;
                        letter-spacing: 2px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="icon">🔐</div>
                    <h1>Recuperación de Contraseña</h1>
                </div>
                
                <div class="content">
                    <div class="title">¡Hola!</div>
                    
                    <div class="description">
                        Solicitas restablecer tu contraseña de PPUD. 
                        Usa el siguiente código para continuar:
                    </div>
                    
                    <div class="code-section">
                        <div class="code-label">Código de verificación</div>
                        <div class="code">' . htmlspecialchars($codigo) . '</div>
                    </div>
                    
                    <div class="warning">
                        <div class="warning-text">
                            ⏰ <strong>Válido por 15 minutos</strong>
                        </div>
                    </div>
                    
                    <div class="steps">
                        <div class="steps-title">Próximos pasos:</div>
                        <div class="steps-list">
                            1. Regresa a la página de recuperación<br>
                            2. Ingresa este código<br>
                            3. Crea tu nueva contraseña
                        </div>
                    </div>
                    
                    <div style="font-size: 13px; color: #86868b; text-align: center; margin-top: 24px;">
                        🛡️ Si no solicitaste este cambio, ignora este mensaje
                    </div>
                </div>
                
                <div class="footer">
                    <div class="footer-text">
                        Correo automático - No responder
                    </div>
                    <div class="company">
                        Equipo PPUD
                    </div>
                </div>
            </div>
        </body>
        </html>';

                // Encabezados del correo
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                $headers .= "From: Soporte PPUD <soporte@ppud.com>" . "\r\n";
                $headers .= "Reply-To: soporte@ppud.com" . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

                // Enviar el correo usando la función mail()
                if (mail($para, $asunto, $mensaje, $headers)) {
                    $response['success'] = true;
                    $response['message'] = 'Se ha enviado un código de recuperación a su correo electrónico. Verifique su bandeja de entrada y spam.';
                } else {
                    $response['message'] = 'Error al enviar el correo. Por favor, revise la configuración de su servidor SMTP (Mercury).';
                }

            } else {
                $response['message'] = $resultado_codigo['message'];
            }
            break;

        case 'cambiar_contrasena':
            $email = $_POST['email'] ?? '';
            $codigo = $_POST['codigo'] ?? '';
            $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';

            // Validar que todos los campos estén completos
            if (empty($email) || empty($codigo) || empty($nueva_contrasena)) {
                $response['message'] = 'Por favor, complete todos los campos.';
                echo json_encode($response);
                exit();
            }

            // Intentar verificar el código y actualizar la contraseña
            $resultado_actualizacion = $login->restablecerContrasena($email, $codigo, $nueva_contrasena);

            if ($resultado_actualizacion['success']) {
                $response['success'] = true;
                $response['message'] = 'Su contraseña ha sido restablecida exitosamente. Ahora puede iniciar sesión.';
            } else {
                $response['message'] = $resultado_actualizacion['message'];
            }
            break;

        default:
            $response['message'] = 'Acción no válida.';
            break;
    }

    // Devolver la respuesta en formato JSON
    echo json_encode($response);
    exit();
} else {
    // Si la solicitud no es POST, devolver un error
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}
?>