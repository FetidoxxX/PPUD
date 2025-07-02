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

      $sql = "INSERT INTO referencia (comentario, puntuacion, tipo_referencia_id_tipo_referencia, estudiante_idEstudiante, empresa_idEmpresa, fecha_creacion, fecha_actualizacion)
                VALUES ('$comentario', " . ($puntuacion === 'NULL' ? 'NULL' : $puntuacion) . ", $tipo_referencia_id_tipo_referencia, '$estudiante_idEstudiante', $empresa_idEmpresa, NOW(), NOW())";

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
    $sql = "SELECT r.*, tr.nombre AS tipo_referencia_nombre, e.nombre AS empresa_nombre, est.nombre AS estudiante_nombre, est.apellidos AS estudiante_apellidos, s.nombre AS estado_nombre
            FROM referencia r
            LEFT JOIN tipo_referencia tr ON r.tipo_referencia_id_tipo_referencia = tr.id_tipo_referencia
            LEFT JOIN empresa e ON r.empresa_idEmpresa = e.idEmpresa
            LEFT JOIN estudiante est ON r.estudiante_idEstudiante = est.idEstudiante
            LEFT JOIN estado s ON r.estado_id_estado = s.id_estado
            WHERE r.idReferencia = $idReferencia";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerPorId referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    return mysqli_fetch_assoc($resultado);
  }

  /**
   * Obtiene todas las referencias, filtradas opcionalmente por empresa, estudiante, tipo específico, búsqueda, límite y offset.
   *
   * @param int|null $idEmpresa ID de la empresa (para referencias realizadas por esta empresa).
   * @param string|null $idEstudiante ID del estudiante (para referencias realizadas a este estudiante).
   * @param int|null $tipoReferenciaIdToInclude ID del tipo de referencia a incluir (ej. 2 para 'empresa_a_estudiante').
   * @param string $busqueda Término de búsqueda para filtrar (comentario, tipo, empresa, estudiante).
   * @param int $limit Límite de resultados.
   * @param int $offset Desplazamiento para paginación.
   * @return array Un array de arrays asociativos con los datos de las referencias.
   */
  public function obtenerTodas($idEmpresa = null, $idEstudiante = null, $tipoReferenciaIdToInclude = null, $busqueda = '', $limit = 10, $offset = 0)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTodas (Referencia).");
      return [];
    }

    $sql = "SELECT r.*,
                   tr.nombre AS tipo_referencia_nombre,
                   emp.nombre AS empresa_nombre,
                   est.nombre AS estudiante_nombre,
                   est.apellidos AS estudiante_apellidos,
                   s.nombre AS estado_nombre
            FROM referencia r
            LEFT JOIN tipo_referencia tr ON r.tipo_referencia_id_tipo_referencia = tr.id_tipo_referencia
            LEFT JOIN empresa emp ON r.empresa_idEmpresa = emp.idEmpresa
            LEFT JOIN estudiante est ON r.estudiante_idEstudiante = est.idEstudiante
            LEFT JOIN estado s ON r.estado_id_estado = s.id_estado";

    $conditions = [];
    if ($busqueda) {
      $busqueda_safe = mysqli_real_escape_string($this->conexion, $busqueda);
      $conditions[] = "(r.comentario LIKE '%$busqueda_safe%' OR
                          tr.nombre LIKE '%$busqueda_safe%' OR
                          emp.nombre LIKE '%$busqueda_safe%' OR
                          est.nombre LIKE '%$busqueda_safe%' OR
                          est.apellidos LIKE '%$busqueda_safe%')";
    }
    if ($idEmpresa !== null) {
      $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);
      $conditions[] = "r.empresa_idEmpresa = '$idEmpresa'";
    }
    if ($idEstudiante !== null) {
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $conditions[] = "r.estudiante_idEstudiante = '$idEstudiante'";
    }
    // Filtro directo por el ID del tipo de referencia a incluir
    if ($tipoReferenciaIdToInclude !== null) {
      $tipoReferenciaIdToInclude = (int) $tipoReferenciaIdToInclude; // Asegurar que sea un entero
      $conditions[] = "r.tipo_referencia_id_tipo_referencia = '$tipoReferenciaIdToInclude'";
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
   * Cuenta el total de referencias, opcionalmente filtradas por búsqueda y tipo de referencia.
   *
   * @param string $busqueda Término de búsqueda para filtrar.
   * @param int|null $tipoReferenciaIdFilter ID del tipo de referencia para filtrar (opcional).
   * @return int El número total de referencias.
   */
  public function contarTodasReferencias($busqueda = '', $tipoReferenciaIdFilter = null)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en contarTodasReferencias.");
      return 0;
    }

    $sql = "SELECT COUNT(*) AS total
            FROM referencia r
            LEFT JOIN tipo_referencia tr ON r.tipo_referencia_id_tipo_referencia = tr.id_tipo_referencia
            LEFT JOIN empresa emp ON r.empresa_idEmpresa = emp.idEmpresa
            LEFT JOIN estudiante est ON r.estudiante_idEstudiante = est.idEstudiante";

    $conditions = [];
    if ($busqueda) {
      $busqueda_safe = mysqli_real_escape_string($this->conexion, $busqueda);
      $conditions[] = "(r.comentario LIKE '%$busqueda_safe%' OR
                          tr.nombre LIKE '%$busqueda_safe%' OR
                          emp.nombre LIKE '%$busqueda_safe%' OR
                          est.nombre LIKE '%$busqueda_safe%' OR
                          est.apellidos LIKE '%$busqueda_safe%')";
    }
    if ($tipoReferenciaIdFilter !== null) {
      $tipoReferenciaIdFilter = (int) $tipoReferenciaIdFilter;
      $conditions[] = "r.tipo_referencia_id_tipo_referencia = '$tipoReferenciaIdFilter'";
    }

    if (!empty($conditions)) {
      $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (contarTodasReferencias): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return 0;
    }
    $fila = mysqli_fetch_assoc($resultado);
    return (int) $fila['total'];
  }


  /**
   * Actualiza los datos de una referencia existente.
   *
   * @param int $idReferencia El ID de la referencia a actualizar.
   * @param array $datos Array asociativo con los datos a actualizar (comentario, puntuacion, tipo_referencia_id_tipo_referencia, estado_id_estado).
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
      if (isset($datos['estado_id_estado'])) { // Asumiendo que las referencias pueden tener un estado
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
   * Elimina una referencia de la base de datos.
   *
   * @param int $idReferencia El ID de la referencia a eliminar.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function eliminar($idReferencia)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al eliminar referencia.");
      }
      $idReferencia = (int) $idReferencia;

      $sql = "DELETE FROM referencia WHERE idReferencia = $idReferencia";

      if (mysqli_query($this->conexion, $sql)) {
        if (mysqli_affected_rows($this->conexion) > 0) {
          return ['success' => true, 'message' => 'Referencia eliminada correctamente.'];
        } else {
          return ['success' => false, 'message' => 'La referencia no existe o ya fue eliminada.'];
        }
      } else {
        error_log("ERROR DB (eliminar referencia): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al eliminar: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR (eliminar referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
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
   * Obtiene todos los estados disponibles desde la tabla `estado`.
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