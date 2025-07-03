<?php
require_once 'class_conec.php'; // Asegúrate de que esta ruta sea correcta para tu archivo class_conec.php

class Referencia
{
  private $conexion;

  /**
   * Constructor de la clase.
   * Inicializa la conexión a la base de datos internamente.
   */
  public function __construct()
  {
    try {
      $this->conexion = Conectar::conec();
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida por Conectar::conec().");
      }
    } catch (Exception $e) {
      error_log("ERROR CRÍTICO: Fallo al conectar a la base de datos en Referencia::__construct: " . $e->getMessage());
      $this->conexion = null;
    }
  }

  /**
   * Destructor de la clase.
   * Cierra la conexión a la base de datos.
   */
  public function __destruct()
  {
    if ($this->conexion) {
      mysqli_close($this->conexion);
    }
  }

  /**
   * Registra una nueva referencia en la base de datos.
   *
   * @param array $datos Array asociativo con los datos de la referencia.
   * Debe contener: 'comentario' (string), 'tipo_referencia_id_tipo_referencia' (int),
   * 'estudiante_idEstudiante' (int), 'empresa_idEmpresa' (int).
   * 'puntuacion' (float, opcional).
   * @return array Resultado de la operación (éxito/error, mensaje, id de la referencia si éxito).
   */
  public function registrar($datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al registrar referencia.");
      }

      // Validar campos requeridos
      if (empty($datos['comentario']) || empty($datos['tipo_referencia_id_tipo_referencia']) || empty($datos['estudiante_idEstudiante']) || empty($datos['empresa_idEmpresa'])) {
        throw new Exception("Datos incompletos para registrar la referencia.");
      }

      $comentario = mysqli_real_escape_string($this->conexion, $datos['comentario']);
      $puntuacion = isset($datos['puntuacion']) && is_numeric($datos['puntuacion']) ? (float) $datos['puntuacion'] : 'NULL';
      $tipo_referencia_id_tipo_referencia = (int) $datos['tipo_referencia_id_tipo_referencia'];
      $estudiante_idEstudiante = mysqli_real_escape_string($this->conexion, $datos['estudiante_idEstudiante']);
      $empresa_idEmpresa = (int) $datos['empresa_idEmpresa'];
      $estado_activo_id = 1; // Asumiendo que el ID para 'Activo' es 1

      $sql = "INSERT INTO referencia (comentario, puntuacion, tipo_referencia_id_tipo_referencia, estudiante_idEstudiante, empresa_idEmpresa, fecha_creacion, fecha_actualizacion, estado_id_estado)
                VALUES ('$comentario', " . ($puntuacion === 'NULL' ? 'NULL' : $puntuacion) . ", $tipo_referencia_id_tipo_referencia, '$estudiante_idEstudiante', $empresa_idEmpresa, NOW(), NOW(), $estado_activo_id)";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Referencia registrada correctamente.', 'idReferencia' => mysqli_insert_id($this->conexion)];
      } else {
        error_log("ERROR DB (registrar referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al registrar: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR (registrar referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Obtiene una referencia por su ID.
   *
   * @param int $idReferencia El ID de la referencia.
   * @return array|false Un array asociativo con los datos de la referencia, o false si no se encuentra.
   */
  public function obtenerPorId($idReferencia)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorId (Referencia).");
      return false;
    }
    $idReferencia = (int) $idReferencia;
    // Agregamos el filtro por estado activo si es para visualización general
    // Sin embargo, para edición/eliminación se necesita obtenerla independientemente del estado actual para verificar permisos
    $sql = "SELECT r.*, tr.nombre AS tipo_referencia_nombre, e.nombre AS empresa_nombre, est.nombre AS estudiante_nombre, est.apellidos AS estudiante_apellidos, s.nombre AS estado_nombre
            FROM referencia r
            LEFT JOIN tipo_referencia tr ON r.tipo_referencia_id_tipo_referencia = tr.id_tipo_referencia
            LEFT JOIN empresa e ON r.empresa_idEmpresa = e.idEmpresa
            LEFT JOIN estudiante est ON r.estudiante_idEstudiante = est.idEstudiante
            LEFT JOIN estado s ON r.estado_id_estado = s.id_estado
            WHERE r.idReferencia = $idReferencia"; // No se filtra por estado aquí, ya que se usa para obtener la referencia a editar/eliminar.
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerPorId referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    return mysqli_fetch_assoc($resultado);
  }

  /**
   * Actualiza los datos de una referencia existente.
   *
   * @param int $idReferencia El ID de la referencia a actualizar.
   * @param array $datos Array asociativo con los datos a actualizar.
   * Puede contener: 'comentario' (string), 'puntuacion' (float),
   * 'tipo_referencia_id_tipo_referencia' (int), 'estado_id_estado' (int).
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function actualizar($idReferencia, $datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida para actualizar referencia.");
      }

      $idReferencia = (int) $idReferencia;
      $updates = [];

      if (isset($datos['comentario'])) {
        $updates[] = "comentario = '" . mysqli_real_escape_string($this->conexion, $datos['comentario']) . "'";
      }
      if (isset($datos['puntuacion'])) {
        $puntuacion = is_numeric($datos['puntuacion']) ? (float) $datos['puntuacion'] : 'NULL';
        $updates[] = "puntuacion = " . ($puntuacion === 'NULL' ? 'NULL' : $puntuacion);
      }
      if (isset($datos['tipo_referencia_id_tipo_referencia'])) {
        $updates[] = "tipo_referencia_id_tipo_referencia = " . (int) $datos['tipo_referencia_id_tipo_referencia'];
      }
      if (isset($datos['estado_id_estado'])) {
        $updates[] = "estado_id_estado = " . (int) $datos['estado_id_estado'];
      }

      if (empty($updates)) {
        return ['success' => false, 'message' => 'No hay datos para actualizar.'];
      }

      $updates[] = "fecha_actualizacion = NOW()";
      $sql = "UPDATE referencia SET " . implode(', ', $updates) . " WHERE idReferencia = $idReferencia";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Referencia actualizada correctamente.'];
      } else {
        error_log("ERROR DB (actualizar referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al actualizar: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR (actualizar referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Cambia el estado de una referencia a 'inactiva' (simulando una eliminación lógica).
   *
   * @param int $idReferencia El ID de la referencia a "eliminar" (inactivar).
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function eliminar($idReferencia)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida para eliminar referencia.");
      }

      $idReferencia = (int) $idReferencia;
      $estado_inactivo_id = 2; // Asumiendo que el ID para 'Inactivo' es 2

      $sql = "UPDATE referencia SET estado_id_estado = $estado_inactivo_id, fecha_actualizacion = NOW() WHERE idReferencia = $idReferencia";

      if (mysqli_query($this->conexion, $sql)) {
        if (mysqli_affected_rows($this->conexion) > 0) {
          return ['success' => true, 'message' => 'Referencia eliminada (desactivada) correctamente.'];
        } else {
          return ['success' => false, 'message' => 'No se encontró la referencia o ya estaba inactiva.'];
        }
      } else {
        error_log("ERROR DB (eliminar referencia - inactivar): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al eliminar (desactivar): " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR (eliminar referencia - inactivar): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Obtiene todas las referencias, opcionalmente filtradas por empresa y/o estudiante y por estado.
   *
   * @param int|null $idEmpresa ID de la empresa para filtrar.
   * @param int|null $idEstudiante ID del estudiante para filtrar.
   * @param int|null $tipoReferenciaIdToInclude ID del tipo de referencia a incluir.
   * @param int $limit Límite de resultados.
   * @param int $offset Desplazamiento de resultados.
   * @param int $estado_id_estado ID del estado para filtrar (por defecto, 1 para activas).
   * @return array Un array de arrays asociativos con los datos de las referencias.
   */
  public function obtenerTodas($idEmpresa = null, $idEstudiante = null, $tipoReferenciaIdToInclude = null, $limit = 10, $offset = 0, $estado_id_estado = 1)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTodas (Referencia).");
      return [];
    }
    $sql = "SELECT r.*, tr.nombre AS tipo_referencia_nombre, e.nombre AS empresa_nombre, e.idEmpresa AS empresa_idEmpresa,
                   est.nombre AS estudiante_nombre, est.apellidos AS estudiante_apellidos, s.nombre AS estado_nombre
            FROM referencia r
            LEFT JOIN tipo_referencia tr ON r.tipo_referencia_id_tipo_referencia = tr.id_tipo_referencia
            LEFT JOIN empresa e ON r.empresa_idEmpresa = e.idEmpresa
            LEFT JOIN estudiante est ON r.estudiante_idEstudiante = est.idEstudiante
            LEFT JOIN estado s ON r.estado_id_estado = s.id_estado";

    $conditions = [];
    if ($idEmpresa !== null) {
      $idEmpresa = (int) $idEmpresa;
      $conditions[] = "r.empresa_idEmpresa = '$idEmpresa'";
    }
    if ($idEstudiante !== null) {
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $conditions[] = "r.estudiante_idEstudiante = '$idEstudiante'";
    }
    if ($tipoReferenciaIdToInclude !== null) {
      $tipoReferenciaIdToInclude = (int) $tipoReferenciaIdToInclude;
      $conditions[] = "r.tipo_referencia_id_tipo_referencia = '$tipoReferenciaIdToInclude'";
    }
    // Añadir el filtro de estado
    if ($estado_id_estado !== null) {
      $estado_id_estado = (int) $estado_id_estado;
      $conditions[] = "r.estado_id_estado = $estado_id_estado";
    }

    if (!empty($conditions)) {
      $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY r.fecha_creacion DESC LIMIT $limit OFFSET $offset";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerTodas referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $referencias = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $referencias[] = $fila;
    }
    return $referencias;
  }

  /**
   * Obtiene la cantidad total de referencias, opcionalmente filtradas por empresa y/o estudiante.
   *
   * @param int|null $idEmpresa ID de la empresa para filtrar.
   * @param int|null $idEstudiante ID del estudiante para filtrar.
   * @param int|null $tipoReferenciaIdToInclude ID del tipo de referencia a incluir.
   * @param int $estado_id_estado ID del estado para filtrar (por defecto, 1 para activas).
   * @return int El número total de referencias.
   */
  public function contarReferencias($idEmpresa = null, $idEstudiante = null, $tipoReferenciaIdToInclude = null, $estado_id_estado = 1)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en contarReferencias (Referencia).");
      return 0;
    }

    $sql = "SELECT COUNT(*) AS total FROM referencia r";
    $conditions = [];

    if ($idEmpresa !== null) {
      $idEmpresa = (int) $idEmpresa;
      $conditions[] = "r.empresa_idEmpresa = '$idEmpresa'";
    }
    if ($idEstudiante !== null) {
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $conditions[] = "r.estudiante_idEstudiante = '$idEstudiante'";
    }
    if ($tipoReferenciaIdToInclude !== null) {
      $tipoReferenciaIdToInclude = (int) $tipoReferenciaIdToInclude;
      $conditions[] = "r.tipo_referencia_id_tipo_referencia = '$tipoReferenciaIdToInclude'";
    }
    // Añadir el filtro de estado
    if ($estado_id_estado !== null) {
      $estado_id_estado = (int) $estado_id_estado;
      $conditions[] = "r.estado_id_estado = $estado_id_estado";
    }

    if (!empty($conditions)) {
      $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (contarReferencias referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return 0;
    }
    $fila = mysqli_fetch_assoc($resultado);
    return (int) $fila['total'];
  }


  /**
   * Obtiene todos los tipos de referencia disponibles desde la tabla `tipo_referencia`.
   *
   * @return array Un array de arrays asociativos con 'id_tipo_referencia' y 'nombre'.
   */
  public function obtenerTiposReferencia()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTiposReferencia.");
      return [];
    }
    $sql = "SELECT id_tipo_referencia, nombre FROM tipo_referencia ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerTiposReferencia: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $tipos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $tipos[] = $fila;
    }
    return $tipos;
  }

  /**
   * Obtiene todos los estados disponibles desde la tabla `estado` (útil para referencias activas/inactivas si se implementa).
   *
   * @return array Un array de arrays asociativos con 'id_estado' y 'nombre'.
   */
  public function obtenerEstados()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEstados.");
      return [];
    }
    $sql = "SELECT id_estado, nombre FROM estado ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerEstados: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $estados = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $estados[] = $fila;
    }
    return $estados;
  }
}