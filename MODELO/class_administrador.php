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
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorId.");
      return false;
    }
    $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);
    $sql = "SELECT a.*, td.nombre as tipo_documento_nombre, c.nombre as ciudad_nombre, est.nombre as estado_nombre
                FROM administrador a
                LEFT JOIN tipo_documento td ON a.tipo_documento_id_tipo = td.id_tipo
                LEFT JOIN ciudad c ON a.ciudad_id_ciudad = c.id_ciudad
                LEFT JOIN estado est ON a.estado_id_estado = est.id_estado
                WHERE a.idAdministrador='$idAdministrador'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerPorId Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
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

  /**
   * Obtiene todos los administradores, opcionalmente filtrados por un término de búsqueda.
   * Por defecto, solo muestra administradores activos.
   * @param string $busqueda Término de búsqueda.
   * @param bool $incluirInactivos Si es true, incluye administradores inactivos en la búsqueda.
   * @return array Lista de administradores.
   */
  public function listarTodos($busqueda = '', $incluirInactivos = false)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en listarTodos (Administrador).");
      return [];
    }
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $where = '';
    if (!$incluirInactivos) {
      $estado_activo_id = $this->getIdEstadoPorNombre('activo');
      if ($estado_activo_id !== false) {
        $where = "WHERE a.estado_id_estado = $estado_activo_id";
      } else {
        error_log("ADVERTENCIA: No se encontró el ID del estado 'activo'. Listando todos los administradores.");
      }
    }

    if (!empty($busqueda)) {
      $search_clause = "(a.nombres LIKE '%$busqueda%' 
                        OR a.apellidos LIKE '%$busqueda%' 
                        OR a.correo LIKE '%$busqueda%'
                        OR a.n_doc LIKE '%$busqueda%'
                        OR a.idAdministrador LIKE '%$busqueda%')";
      if (empty($where)) {
        $where = "WHERE " . $search_clause;
      } else {
        $where .= " AND " . $search_clause;
      }
    }

    $sql = "SELECT a.*, td.nombre as tipo_documento_nombre, est.nombre as estado_nombre
                FROM administrador a
                LEFT JOIN tipo_documento td ON a.tipo_documento_id_tipo = td.id_tipo
                LEFT JOIN estado est ON a.estado_id_estado = est.id_estado
                $where
                ORDER BY a.nombres, a.apellidos";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (listarTodos Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }

    $administradores = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
      $administradores[] = $row;
    }

    return $administradores;
  }

  /**
   * Actualiza los datos de un administrador.
   * @param int $idAdministrador ID del administrador a actualizar.
   * @param array $datos Array asociativo con los datos a actualizar.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function actualizar($idAdministrador, $datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al actualizar administrador.");
      }

      // Campos permitidos para la actualización
      $campos_permitidos = [
        'nombres',
        'apellidos',
        'correo',
        'telefono',
        'n_doc',
        'tipo_documento_id_tipo',
        'ciudad_id_ciudad',
        'estado_id_estado'
      ];
      $updates = [];

      foreach ($campos_permitidos as $campo) {
        if (isset($datos[$campo])) {
          $valor = $datos[$campo];
          if ($valor === '' || $valor === null) {
            $updates[] = "$campo=NULL";
          } else {
            $escaped_valor = mysqli_real_escape_string($this->conexion, $valor);
            if (in_array($campo, ['tipo_documento_id_tipo', 'ciudad_id_ciudad', 'estado_id_estado'])) {
              $updates[] = "$campo=" . (int) $escaped_valor;
            } else {
              $updates[] = "$campo='$escaped_valor'";
            }
          }
        }
      }

      if (empty($updates)) {
        throw new Exception("No hay datos para actualizar.");
      }

      $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);
      $sql = "UPDATE administrador SET " . implode(', ', $updates) . ", fecha_actualizacion = CURRENT_TIMESTAMP() WHERE idAdministrador='$idAdministrador'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Administrador actualizado correctamente.'];
      } else {
        error_log("ERROR DB (actualizar administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al actualizar administrador: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (actualizar administrador): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }


  /**
   * Cambia el estado de un administrador a 'inactivo' en lugar de eliminarlo físicamente.
   * @param int $idAdministrador ID del administrador a desactivar.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function desactivar($idAdministrador)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al desactivar administrador.");
      }
      $idAdministrador = mysqli_real_escape_string($this->conexion, $idAdministrador);

      $estado_inactivo_id = $this->getIdEstadoPorNombre('inactivo');
      if ($estado_inactivo_id === false) {
        throw new Exception("El estado 'inactivo' no se encontró en la base de datos.");
      }

      if (!$this->obtenerPorId($idAdministrador)) {
        throw new Exception("El administrador no existe.");
      }

      $sql = "UPDATE administrador SET estado_id_estado = $estado_inactivo_id, fecha_actualizacion = CURRENT_TIMESTAMP() WHERE idAdministrador='$idAdministrador'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Administrador desactivado correctamente.'];
      } else {
        error_log("ERROR DB (desactivar administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al desactivar administrador: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (desactivar administrador): " . $e->getMessage() . " en línea " . $e->getLine());
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

  /**
   * Obtiene el ID de un estado dado su nombre.
   * @param string $nombreEstado El nombre del estado (ej. 'activo', 'inactivo').
   * @return int|false El ID del estado o false si no se encuentra.
   */
  public function getIdEstadoPorNombre($nombreEstado)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en getIdEstadoPorNombre.");
      return false;
    }
    $nombreEstado = mysqli_real_escape_string($this->conexion, $nombreEstado);
    $sql = "SELECT id_estado FROM estado WHERE nombre = '$nombreEstado'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en getIdEstadoPorNombre: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    if ($fila = mysqli_fetch_assoc($resultado)) {
      return (int) $fila['id_estado'];
    }
    error_log("ADVERTENCIA: Estado con nombre '$nombreEstado' no encontrado en la tabla 'estado'.");
    return false;
  }

  /**
   * Obtiene todos los tipos de documento.
   * @return array Lista de tipos de documento.
   */
  public function obtenerTiposDocumento()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTiposDocumento (Administrador).");
      return [];
    }
    $sql = "SELECT id_tipo, nombre FROM tipo_documento ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerTiposDocumento Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $tipos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $tipos[] = $fila;
    }
    return $tipos;
  }

  /**
   * Obtiene todos los estados.
   * @return array Lista de estados.
   */
  public function obtenerEstados()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEstados (Administrador).");
      return [];
    }
    $sql = "SELECT id_estado, nombre FROM estado ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerEstados Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $estados = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $estados[] = $fila;
    }
    return $estados;
  }

  /**
   * Obtiene todas las ciudades.
   * @return array Lista de ciudades.
   */
  public function obtenerCiudades()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerCiudades (Administrador).");
      return [];
    }
    $sql = "SELECT id_ciudad, nombre FROM ciudad ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerCiudades Administrador): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $ciudades = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $ciudades[] = $fila;
    }
    return $ciudades;
  }
}