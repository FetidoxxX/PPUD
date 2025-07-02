<?php
include('../MODELO/class_conec.php');
include('../MODELO/class_estudiante.php');
include('../MODELO/class_empresa.php');
include('../MODELO/class_administrador.php');

class Login
{
  private $conexion;

  public function __construct()
  {
    $this->conexion = Conectar::conec();
  }

  public function validar($user, $pass, $rol)
  {
    try {
      // Validar que los datos no estén vacíos
      if (empty($user) || empty($pass) || empty($rol)) {
        ?>
        <!DOCTYPE html>
        <html>

        <head>
          <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
          <script src='../sw/dist/sweetalert2.min.js'></script>
        </head>

        <body>
          <script type='text/javascript'>
            Swal.fire({
              icon: 'error',
              title: 'ERROR!!',
              text: 'Por favor complete todos los campos'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location = '../index.php';
              }
            });
          </script>
        </body>

        </html>
        <?php
        return;
      }

      $datosUsuario = null;

      switch ($rol) {
        case 'estudiante':
          $estudiante = new Estudiante();
          $datosUsuario = $estudiante->validarCredenciales($user, $pass);
          break;

        case 'empresa':
          $empresa = new Empresa();
          $datosUsuario = $empresa->validarCredenciales($user, $pass);
          break;

        case 'administrador':
          $administrador = new Administrador();
          $datosUsuario = $administrador->validarCredenciales($user, $pass);
          break;

        default:
          ?>
          <!DOCTYPE html>
          <html>

          <head>
            <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
            <script src='../sw/dist/sweetalert2.min.js'></script>
          </head>

          <body>
            <script type='text/javascript'>
              Swal.fire({
                icon: 'error',
                title: 'ERROR!!',
                text: 'Rol no válido'
              }).then((result) => {
                if (result.isConfirmed) {
                  window.location = '../index.php';
                }
              });
            </script>
          </body>

          </html>
          <?php
          return;
      }

      if ($datosUsuario) {
        $this->iniciarSesion($datosUsuario, $rol, $user);
      } else {
        ?>
        <!DOCTYPE html>
        <html>

        <head>
          <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
          <script src='../sw/dist/sweetalert2.min.js'></script>
        </head>

        <body>
          <script type='text/javascript'>
            Swal.fire({
              icon: 'error',
              title: 'ERROR!!',
              text: 'el usuario <?php echo $user; ?> o password no son correctos'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location = '../index.php';
              }
            });
          </script>
        </body>

        </html>
        <?php
      }

    } catch (Exception $e) {
      ?>
      <!DOCTYPE html>
      <html>

      <head>
        <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
        <script src='../sw/dist/sweetalert2.min.js'></script>
      </head>

      <body>
        <script type='text/javascript'>
          Swal.fire({
            icon: 'error',
            title: 'ERROR!!',
            text: 'Error en el sistema'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location = '../index.php';
            }
          });
        </script>
      </body>

      </html>
      <?php
    }
  }

  private function iniciarSesion($datosUsuario, $rol, $user)
  {
    session_start();

    $_SESSION['timeout'] = time();
    $_SESSION['rol'] = $rol; // Guardar el rol

    $destino = "index.php"; // Destino por defecto

    // Guardar datos específicos y configurar $_SESSION['usuario'] y $_SESSION['usuario_id'] según el rol
    switch ($rol) {
      case 'estudiante':
        $_SESSION['usuario_id'] = $datosUsuario['idEstudiante'];
        $_SESSION['usuario'] = $datosUsuario['nombre'] . ' ' . $datosUsuario['apellidos']; // Nombre completo para mostrar
        $_SESSION['idEstudiante'] = $datosUsuario['idEstudiante']; // Mantener si otras partes del código lo usan
        $_SESSION['nombre'] = $datosUsuario['nombre'];
        $_SESSION['apellidos'] = $datosUsuario['apellidos'];
        $_SESSION['correo'] = $datosUsuario['correo'];
        $destino = "../VISTA/pruebaEstudiante.php";
        break;

      case 'empresa':
        $_SESSION['usuario_id'] = $datosUsuario['idEmpresa'];
        $_SESSION['usuario'] = $datosUsuario['nombre']; // Nombre de la empresa para mostrar
        $_SESSION['idEmpresa'] = $datosUsuario['idEmpresa']; // Mantener si otras partes del código lo usan
        $_SESSION['nombre'] = $datosUsuario['nombre'];
        $_SESSION['correo'] = $datosUsuario['correo'];
        $destino = "../VISTA/pruebaEmpresa.php";
        break;

      case 'administrador':
        $_SESSION['usuario_id'] = $datosUsuario['idAdministrador'];
        $_SESSION['usuario'] = $datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos']; // Nombre completo para mostrar
        $_SESSION['idAdministrador'] = $datosUsuario['idAdministrador']; // Mantener si otras partes del código lo usan
        $_SESSION['nombres'] = $datosUsuario['nombres'];
        $_SESSION['apellidos'] = $datosUsuario['apellidos'];
        $destino = "../VISTA/pruebaAdmin.php";
        break;
    }

    // Estructura original de mensaje
    ?>
    <!DOCTYPE html>
    <html>

    <head>
      <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
      <script src='../sw/dist/sweetalert2.min.js'></script>
    </head>

    <body>
      <script type='text/javascript'>
        Swal.fire({
          icon: 'success',
          title: 'BIENVENIDO',
          text: '<?php echo $_SESSION['usuario']; ?> al Sistema'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location = './<?php echo $destino; ?>';
          }
        });
      </script>
    </body>

    </html>
    <?php
  }
  /**
   * Busca un usuario por correo electrónico en todas las tablas de roles.
   * @param string $email El correo electrónico a buscar.
   * @return array|null Un array asociativo con 'id', 'rol' y 'correo' si se encuentra, o null.
   */
  public function encontrarUsuarioPorEmail($email)
  {
    $email = mysqli_real_escape_string($this->conexion, $email);

    // Buscar en la tabla de estudiantes
    $sql_estudiante = "SELECT idEstudiante AS id, 'estudiante' AS rol, correo FROM estudiante WHERE correo = '$email'";
    $res_estudiante = mysqli_query($this->conexion, $sql_estudiante);
    if ($res_estudiante && mysqli_num_rows($res_estudiante) > 0) {
      $fila = mysqli_fetch_assoc($res_estudiante);
      return ['id' => $fila['id'], 'rol' => $fila['rol'], 'correo' => $fila['correo']];
    }

    // Buscar en la tabla de empresas
    $sql_empresa = "SELECT idEmpresa AS id, 'empresa' AS rol, correo FROM empresa WHERE correo = '$email'";
    $res_empresa = mysqli_query($this->conexion, $sql_empresa);
    if ($res_empresa && mysqli_num_rows($res_empresa) > 0) {
      $fila = mysqli_fetch_assoc($res_empresa);
      return ['id' => $fila['id'], 'rol' => $fila['rol'], 'correo' => $fila['correo']];
    }

    // Buscar en la tabla de administradores
    $sql_admin = "SELECT idAdministrador AS id, 'administrador' AS rol, correo FROM administrador WHERE correo = '$email'";
    $res_admin = mysqli_query($this->conexion, $sql_admin);
    if ($res_admin && mysqli_num_rows($res_admin) > 0) {
      $fila = mysqli_fetch_assoc($res_admin);
      return ['id' => $fila['id'], 'rol' => $fila['rol'], 'correo' => $fila['correo']];
    }

    return null; // Si el usuario no se encuentra en ninguna tabla
  }

  /**
   * @param string $email El correo electrónico del usuario.
   * @return array Resultado de la operación (success: bool, message: string, codigo: string|null).
   */
  public function generarYGuardarCodigoRecuperacion($email)
  {
    $usuario = $this->encontrarUsuarioPorEmail($email);

    if (!$usuario) {
      return ['success' => false, 'message' => 'Correo electrónico no registrado.'];
    }

    // Generar un código aleatorio seguro
    $codigo = bin2hex(random_bytes(3)); // 3 bytes generan 6 caracteres

    // Establecer la fecha de expiración (por ejemplo, 15 minutos a partir de ahora)
    $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $rol = $usuario['rol'];
    $id_campo = '';
    $tabla = '';

    // Determinar la tabla y el campo de ID según el rol del usuario
    switch ($rol) {
      case 'estudiante':
        $tabla = 'estudiante';
        $id_campo = 'idEstudiante';
        break;
      case 'empresa':
        $tabla = 'empresa';
        $id_campo = 'idEmpresa';
        break;
      case 'administrador':
        $tabla = 'administrador';
        $id_campo = 'idAdministrador';
        break;
      default:
        return ['success' => false, 'message' => 'Rol de usuario no válido.'];
    }

    $id_usuario = $usuario['id'];
    // Escapar los datos para prevenir inyecciones SQL
    $codigo_escaped = mysqli_real_escape_string($this->conexion, $codigo);
    $expiracion_escaped = mysqli_real_escape_string($this->conexion, $expiracion);

    // Actualizar la base de datos con el código de recuperación y la fecha de expiración
    $sql = "UPDATE $tabla SET codigo_recuperacion = '$codigo_escaped', codigo_expira_en = '$expiracion_escaped' WHERE $id_campo = '$id_usuario'";

    if (mysqli_query($this->conexion, $sql)) {
      return ['success' => true, 'message' => 'Código generado y guardado exitosamente.', 'codigo' => $codigo];
    } else {
      // Registrar el error en el log del servidor
      error_log("Error al guardar código de recuperación en $tabla: " . mysqli_error($this->conexion));
      return ['success' => false, 'message' => 'Error interno al generar el código de recuperación.'];
    }
  }

  /**
   * Restablece la contraseña de un usuario después de verificar el código de recuperación y su expiración.
   * @param string $email El correo electrónico del usuario.
   * @param string $codigo El código de recuperación proporcionado por el usuario.
   * @param string $nuevaContrasena La nueva contraseña a establecer.
   * @return array Resultado de la operación (success: bool, message: string).
   */
  public function restablecerContrasena($email, $codigo, $nuevaContrasena)
  {
    $usuario = $this->encontrarUsuarioPorEmail($email);

    if (!$usuario) {
      return ['success' => false, 'message' => 'Correo electrónico no registrado.'];
    }

    $rol = $usuario['rol'];
    $id_campo = '';
    $tabla = '';

    // Determinar la tabla y el campo de ID según el rol del usuario
    switch ($rol) {
      case 'estudiante':
        $tabla = 'estudiante';
        $id_campo = 'idEstudiante';
        break;
      case 'empresa':
        $tabla = 'empresa';
        $id_campo = 'idEmpresa';
        break;
      case 'administrador':
        $tabla = 'administrador';
        $id_campo = 'idAdministrador';
        break;
      default:
        return ['success' => false, 'message' => 'Rol de usuario no válido.'];
    }

    $id_usuario = $usuario['id'];
    // Escapar los datos para prevenir inyecciones SQL
    $codigo_escaped = mysqli_real_escape_string($this->conexion, $codigo);

    $nuevaContrasena_escaped = mysqli_real_escape_string($this->conexion, $nuevaContrasena);

    // Verificar el código de recuperación y que no haya expirado
    $sql_verificar = "SELECT * FROM $tabla WHERE $id_campo = '$id_usuario' AND codigo_recuperacion = '$codigo_escaped' AND codigo_expira_en > NOW()";
    $res_verificar = mysqli_query($this->conexion, $sql_verificar);

    if ($res_verificar && mysqli_num_rows($res_verificar) > 0) {
      // Si el código es válido y no ha expirado, proceder a actualizar la contraseña
      $sql_update = "UPDATE $tabla SET contrasena = '$nuevaContrasena_escaped', codigo_recuperacion = NULL, codigo_expira_en = NULL WHERE $id_campo = '$id_usuario'";

      if (mysqli_query($this->conexion, $sql_update)) {
        return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
      } else {
        // Registrar el error en el log del servidor
        error_log("Error al actualizar contraseña en $tabla: " . mysqli_error($this->conexion));
        return ['success' => false, 'message' => 'Error al actualizar la contraseña.'];
      }
    } else {
      return ['success' => false, 'message' => 'Código de recuperación inválido o expirado.'];
    }
  }
}
?>