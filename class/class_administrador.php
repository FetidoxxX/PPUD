<?php
require_once 'class_conec.php';

class Administrador
{
  private $conexion;

  /**
   * Constructor de la clase.
   * Inicializa la conexión a la base de datos internamente.
   */
  public function __construct()
  {
    $this->conexion = Conectar::conec();
  }

  public function registrar($datos)
  {
    try {
      // Validar campos requeridos
      $campos_requeridos = ['idAdministrador', 'nombres', 'apellidos', 'contrasena', 'tipo_documento', 'n_doc', 'telefono', 'correo'];

      foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
          throw new Exception("El campo $campo es requerido.");
        }
      }

      // Escapar datos
      $idAdministrador = mysqli_real_escape_string($this->conexion, $datos['idAdministrador']);
      $nombres = mysqli_real_escape_string($this->conexion, $datos['nombres']);
      $apellidos = mysqli_real_escape_string($this->conexion, $datos['apellidos']);
      $contrasena = mysqli_real_escape_string($this->conexion, $datos['contrasena']);
      $tipo_documento = (int) $datos['tipo_documento'];
      $n_doc = mysqli_real_escape_string($this->conexion, $datos['n_doc']);
      $telefono = mysqli_real_escape_string($this->conexion, $datos['telefono']);
      $correo = mysqli_real_escape_string($this->conexion, $datos['correo']);

      // No se verifica la existencia aquí, se hará en ajax_registro.php antes de llamar a este método.


      // Insertar administrador con los nuevos campos
      $sql = "INSERT INTO administrador (idAdministrador, contrasena, nombres, apellidos, tipo_documento_id_tipo, n_doc, telefono, correo)
                    VALUES ('$idAdministrador', '$contrasena', '$nombres', '$apellidos', $tipo_documento, '$n_doc', '$telefono', '$correo')";

      if (mysqli_query($this->conexion, $sql)) {
        return [
          'success' => true,
          'message' => 'Administrador registrado correctamente.'
        ];
      } else {
        throw new Exception("Error en la base de datos: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  /**
   * Verifica si un administrador existe por su ID.
   * @param string $idAdministrador ID del administrador.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeAdministrador($idAdministrador)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeAdministrador.");
      return false; // Asume que no existe para no bloquear la app si la conexión falla
    }
    $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);
    $sql = "SELECT COUNT(*) FROM administrador WHERE idAdministrador='$idAdministrador'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeAdministrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Verifica si un administrador existe por su correo.
   * @param string $correo Correo del administrador.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeCorreo($correo)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeCorreo (Administrador).");
      return false;
    }
    $correo = mysqli_real_escape_string($this->conexion, $correo);
    $sql = "SELECT COUNT(*) FROM administrador WHERE correo='$correo'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeCorreo Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Verifica si un administrador existe por su número de documento.
   * @param string $n_doc Número de documento del administrador.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeNdoc($n_doc)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeNdoc (Administrador).");
      return false;
    }
    $n_doc = mysqli_real_escape_string($this->conexion, $n_doc);
    $sql = "SELECT COUNT(*) FROM administrador WHERE n_doc='$n_doc'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeNdoc Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  public function obtenerPorId($idAdministrador)
  {
    $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);
    $sql = "SELECT * FROM administrador WHERE idAdministrador='$idAdministrador'";
    $resultado = mysqli_query($this->conexion, $sql);
    return mysqli_fetch_assoc($resultado);
  }

  public function validarCredenciales($idAdministrador, $contrasena)
  {
    $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);
    $contrasena = mysqli_real_escape_string($this->conexion, $contrasena);

    $sql = "SELECT a.*, td.nombre as tipo_documento_nombre
                FROM administrador a
                LEFT JOIN tipo_documento td ON a.tipo_documento_id_tipo = td.id_tipo
                WHERE a.idAdministrador='$idAdministrador' AND a.contrasena='$contrasena'";
    $resultado = mysqli_query($this->conexion, $sql);

    if (mysqli_num_rows($resultado) == 1) {
      return mysqli_fetch_assoc($resultado);
    }
    return false;
  }

  public function listarTodos()
  {
    $sql = "SELECT a.*, td.nombre as tipo_documento_nombre
                FROM administrador a
                LEFT JOIN tipo_documento td ON a.tipo_documento_id_tipo = td.id_tipo
                ORDER BY a.nombres, a.apellidos";
    $resultado = mysqli_query($this->conexion, $sql);

    $administradores = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
      $administradores[] = $row;
    }

    return $administradores;
  }

  public function eliminar($idAdministrador)
  {
    try {
      $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);

      // Verificar que el administrador existe usando el método específico
      if (!$this->existeAdministrador($idAdministrador)) {
        throw new Exception("El administrador no existe.");
      }

      $sql = "DELETE FROM administrador WHERE idAdministrador='$idAdministrador'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Administrador eliminado correctamente.'];
      } else {
        throw new Exception("Error al eliminar: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function cambiarContrasena($idAdministrador, $contrasenaActual, $contrasenaNueva)
  {
    try {
      // Verificar contraseña actual
      if (!$this->validarCredenciales($idAdministrador, $contrasenaActual)) {
        throw new Exception("La contraseña actual es incorrecta.");
      }

      $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);
      $contrasenaNueva = mysqli_real_escape_string($this->conexion, $contrasenaNueva);

      $sql = "UPDATE administrador SET contrasena='$contrasenaNueva' WHERE idAdministrador='$idAdministrador'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
      } else {
        throw new Exception("Error al actualizar contraseña: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }
}
?>