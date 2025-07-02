<?php
// Recibir rol por GET
$rol = '';
if (isset($_GET['rol'])) {
  $rol = $_GET['rol'];
}

// Validar que el rol sea válido o establecer un valor por defecto
$roles_validos = ['estudiante', 'empresa', 'administrador'];
if (!in_array($rol, $roles_validos)) {
  $rol = 'default'; // Rol por defecto si no es válido o no se proporciona
}

// Configurar colores y iconos según el rol
$config_rol = [
  'estudiante' => [
    'color' => 'primary',
    'titulo' => 'Recuperación Estudiantil',
    'descripcion' => 'Ingrese su correo electrónico para restablecer la contraseña de su cuenta de estudiante.'
  ],
  'empresa' => [
    'color' => 'success',
    'titulo' => 'Recuperación Empresarial',
    'descripcion' => 'Ingrese su correo electrónico para restablecer la contraseña de su cuenta de empresa.'
  ],
  'administrador' => [
    'color' => 'warning',
    'titulo' => 'Recuperación Administrativa',
    'descripcion' => 'Ingrese su correo electrónico para restablecer la contraseña de su cuenta de administrador.'
  ],
  'default' => [ // Configuración por defecto si no se especifica un rol válido
    'color' => 'info',
    'titulo' => 'Recuperar Contraseña',
    'descripcion' => 'Ingrese su correo electrónico asociado a su cuenta para recibir un código de recuperación.'
  ]
];

// Asignar la configuración del rol actual
$config = $config_rol[$rol];

// Asegúrate de que SweetAlert2 esté correctamente enlazado.
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $config['titulo']; ?> - PPUD</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="../sw/dist/sweetalert2.min.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container flex-grow-1">
      <a class="navbar-brand fw-bold" href="../index.php">
        <span class="text-warning">PPUD</span> - Plataforma de Prácticas Profesionales
      </a>
    </div>
  </nav>

  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
          <!-- El color del card-header ahora es dinámico -->
          <div class="card-header bg-<?php echo $config['color']; ?> text-white text-center py-4">
            <h3 class="fw-light my-2"><?php echo $config['titulo']; ?></h3>
          </div>
          <div class="card-body p-4">
            <p class="text-center text-muted mb-4"><?php echo $config['descripcion']; ?></p>
            <!-- Formulario para solicitar el código de recuperación -->
            <form id="formSolicitarCodigo">
              <div class="form-floating mb-3">
                <input class="form-control" id="inputEmail" type="email" placeholder="nombre@ejemplo.com" name="email"
                  required />
                <label for="inputEmail">Correo Electrónico</label>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                <!-- Color del botón principal dinámico -->
                <button type="submit" class="btn btn-<?php echo $config['color']; ?> w-100">Enviar Código de
                  Recuperación</button>
              </div>
            </form>

            <!-- Sección para cambiar la contraseña (inicialmente oculta) -->
            <div id="seccionCambiarContrasena" style="display: none;">
              <hr class="my-4">
              <p class="text-center text-muted mb-4">Ingrese el código recibido y su nueva contraseña.</p>
              <form id="formCambiarContrasena">
                <div class="form-floating mb-3">
                  <input class="form-control" id="inputCodigo" type="text" placeholder="Su código" name="codigo"
                    required />
                  <label for="inputCodigo">Código de Verificación</label>
                </div>
                <div class="form-floating mb-3">
                  <input class="form-control" id="inputNuevaContrasena" type="password" placeholder="Nueva Contraseña"
                    name="nueva_contrasena" required />
                  <label for="inputNuevaContrasena">Nueva Contraseña</label>
                </div>
                <div class="form-floating mb-3">
                  <input class="form-control" id="inputConfirmarContrasena" type="password"
                    placeholder="Confirmar Nueva Contraseña" name="confirmar_contrasena" required />
                  <label for="inputConfirmarContrasena">Confirmar Nueva Contraseña</label>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                  <button type="submit" class="btn btn-success w-100">Cambiar Contraseña</button>
                </div>
              </form>
            </div>
          </div>
          <div class="card-footer text-center py-3">
            <div class="small"><a href="./login.php">Volver al Login</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-auto">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> PPUD - Plataforma de Prácticas y Pasantías Profesionales.
            Todos los
            derechos reservados.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function () {
      // Manejador para el formulario de solicitud de código
      $('#formSolicitarCodigo').on('submit', function (e) {
        e.preventDefault(); // Evitar el envío normal del formulario
        var email = $('#inputEmail').val(); // Obtener el correo electrónico

        $.ajax({
          type: 'POST',
          url: '../CONTROLADOR/ajax_recuperacion.php', // URL del script PHP que manejará la solicitud
          data: {
            accion: 'solicitar_codigo',
            email: email
          },
          dataType: 'json', // Esperar una respuesta JSON
          success: function (response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Código Enviado',
                text: response.message
              }).then(() => {
                // Ocultar el formulario de solicitud y mostrar el de cambio de contraseña
                $('#formSolicitarCodigo').hide();
                $('#seccionCambiarContrasena').show();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message
              });
            }
          },
          error: function (xhr, status, error) {
            // Manejo de errores de AJAX (problemas de conexión, etc.)
            Swal.fire({
              icon: 'error',
              title: 'Error de Conexión',
              text: 'No se pudo conectar con el servidor. Intente de nuevo más tarde.'
            });
            console.error(xhr.responseText); // Para depuración
          }
        });
      });

      // Manejador para el formulario de cambio de contraseña
      $('#formCambiarContrasena').on('submit', function (e) {
        e.preventDefault(); // Evitar el envío normal del formulario
        var codigo = $('#inputCodigo').val();
        var nueva_contrasena = $('#inputNuevaContrasena').val();
        var confirmar_contrasena = $('#inputConfirmarContrasena').val();
        var email = $('#inputEmail').val(); // Se necesita el correo para identificar al usuario en el backend

        // Validar que las contraseñas coincidan
        if (nueva_contrasena !== confirmar_contrasena) {
          Swal.fire({
            icon: 'warning',
            title: 'Contraseñas no Coinciden',
            text: 'La nueva contraseña y la confirmación no coinciden.'
          });
          return; // Detener la ejecución si no coinciden
        }

        $.ajax({
          type: 'POST',
          url: '../CONTROLADOR/ajax_recuperacion.php', // URL del script PHP que manejará la solicitud
          data: {
            accion: 'cambiar_contrasena',
            email: email,
            codigo: codigo,
            nueva_contrasena: nueva_contrasena
          },
          dataType: 'json', // Esperar una respuesta JSON
          success: function (response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Contraseña Actualizada',
                text: response.message
              }).then(() => {
                window.location.href = 'login.php'; // Redirigir al login después del éxito
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message
              });
            }
          },
          error: function (xhr, status, error) {
            // Manejo de errores de AJAX
            Swal.fire({
              icon: 'error',
              title: 'Error de Conexión',
              text: 'No se pudo conectar con el servidor. Intente de nuevo más tarde.'
            });
            console.error(xhr.responseText); // Para depuración
          }
        });
      });
    });
  </script>
</body>

</html>