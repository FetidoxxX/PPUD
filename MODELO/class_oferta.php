<?php
require_once 'class_conec.php';

class Oferta
{
  private $conexion;

  public function __construct()
  {
    try {
      $this->conexion = Conectar::conec();
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida por Conectar::conec().");
      }
    } catch (Exception $e) {

      error_log("ERROR CRÍTICO: Fallo al conectar a la base de datos en Oferta::__construct: " . $e->getMessage());
      $this->conexion = null; // Asegura que la conexión sea nula si falló
    }
  }

  public function __destruct()
  {
    if ($this->conexion) {
      mysqli_close($this->conexion);
    }
  }

  /**
   * Registra una nueva oferta en la base de datos.
   * @param array $datos Array asociativo con los datos de la oferta.
   * @return array Resultado de la operación (éxito/error, mensaje, y nuevo ID).
   */
  public function registrar($datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al registrar oferta.");
      }

      // Campos requeridos para registrar una oferta
      $campos_requeridos = [
        'titulo',
        'descripcion',
        'requisitos',
        'modalidad_id_modalidad',
        'tipo_oferta_id_tipo_oferta',
        'duracion_meses',
        'area_conocimiento_id_area',
        'fecha_vencimiento',
        'empresa_idEmpresa'
      ];

      // Valida que todos los campos requeridos estén presentes y no vacíos
      foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
          throw new Exception("El campo '$campo' es requerido.");
        }
      }

      // Escapar datos para prevenir inyecciones SQL
      $titulo = mysqli_real_escape_string($this->conexion, $datos['titulo']);
      $descripcion = mysqli_real_escape_string($this->conexion, $datos['descripcion']);
      $requisitos = mysqli_real_escape_string($this->conexion, $datos['requisitos']);
      $beneficios = isset($datos['beneficios']) ? mysqli_real_escape_string($this->conexion, $datos['beneficios']) : NULL;
      $modalidad_id_modalidad = (int) $datos['modalidad_id_modalidad'];
      $tipo_oferta_id_tipo_oferta = (int) $datos['tipo_oferta_id_tipo_oferta'];
      $duracion_meses = (int) $datos['duracion_meses'];
      $horario = isset($datos['horario']) ? mysqli_real_escape_string($this->conexion, $datos['horario']) : NULL;
      $remuneracion = isset($datos['remuneracion']) ? mysqli_real_escape_string($this->conexion, $datos['remuneracion']) : NULL;
      $area_conocimiento_id_area = (int) $datos['area_conocimiento_id_area'];
      $semestre_minimo = isset($datos['semestre_minimo']) ? (int) $datos['semestre_minimo'] : NULL;
      $promedio_minimo = isset($datos['promedio_minimo']) ? (float) $datos['promedio_minimo'] : NULL;
      $habilidades_requeridas = isset($datos['habilidades_requeridas']) ? mysqli_real_escape_string($this->conexion, $datos['habilidades_requeridas']) : NULL;
      $fecha_inicio = isset($datos['fecha_inicio']) ? mysqli_real_escape_string($this->conexion, $datos['fecha_inicio']) : NULL;
      $fecha_fin = isset($datos['fecha_fin']) ? mysqli_real_escape_string($this->conexion, $datos['fecha_fin']) : NULL;
      $fecha_vencimiento = mysqli_real_escape_string($this->conexion, $datos['fecha_vencimiento']);
      $cupos_disponibles = isset($datos['cupos_disponibles']) ? (int) $datos['cupos_disponibles'] : 1;
      $empresa_idEmpresa = (int) $datos['empresa_idEmpresa'];

      // Consulta SQL para insertar la nueva oferta
      $sql = "INSERT INTO oferta (
                        titulo, descripcion, requisitos, beneficios, modalidad_id_modalidad,
                        tipo_oferta_id_tipo_oferta, duracion_meses, horario, remuneracion,
                        area_conocimiento_id_area, semestre_minimo, promedio_minimo,
                        habilidades_requeridas, fecha_inicio, fecha_fin, fecha_vencimiento,
                        cupos_disponibles, empresa_idEmpresa
                    ) VALUES (
                        '$titulo', '$descripcion', '$requisitos', " . ($beneficios ? "'$beneficios'" : "NULL") . ", $modalidad_id_modalidad,
                        $tipo_oferta_id_tipo_oferta, $duracion_meses, " . ($horario ? "'$horario'" : "NULL") . ", " . ($remuneracion ? "'$remuneracion'" : "NULL") . ",
                        $area_conocimiento_id_area, " . ($semestre_minimo ? "$semestre_minimo" : "NULL") . ", " . ($promedio_minimo ? "$promedio_minimo" : "NULL") . ",
                        " . ($habilidades_requeridas ? "'$habilidades_requeridas'" : "NULL") . ", " . ($fecha_inicio ? "'$fecha_inicio'" : "NULL") . ", " . ($fecha_fin ? "'$fecha_fin'" : "NULL") . ", '$fecha_vencimiento',
                        $cupos_disponibles, $empresa_idEmpresa
                    )";

      // Ejecuta la consulta
      if (mysqli_query($this->conexion, $sql)) {
        $id_insertado = mysqli_insert_id($this->conexion); // Obtener el ID de la oferta recién insertada
        return ['success' => true, 'message' => 'Oferta registrada correctamente.', 'idOferta' => $id_insertado];
      } else {
        error_log("ERROR DB (registrar oferta): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error en la base de datos al registrar oferta: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (registrar oferta): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function obtenerPorId($idOferta)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorId.");
      return false;
    }
    $idOferta = mysqli_real_escape_string($this->conexion, $idOferta);
    $sql = "SELECT o.*,
                       m.nombre as modalidad_nombre,
                       toferta.nombre as tipo_oferta_nombre,
                       ac.nombre as area_conocimiento_nombre,
                       e.nombre as empresa_nombre,
                       e.idEmpresa as empresa_idEmpresa,
                       est.nombre as estado_nombre
                FROM oferta o
                LEFT JOIN modalidad m ON o.modalidad_id_modalidad = m.id_modalidad
                LEFT JOIN tipo_oferta toferta ON o.tipo_oferta_id_tipo_oferta = toferta.id_tipo_oferta
                LEFT JOIN area_conocimiento ac ON o.area_conocimiento_id_area = ac.id_area
                LEFT JOIN empresa e ON o.empresa_idEmpresa = e.idEmpresa
                LEFT JOIN estado est ON o.estado_id_estado = est.id_estado
                WHERE o.idOferta = '$idOferta'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerPorId: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    return mysqli_fetch_assoc($resultado);
  }

  /**
   * @param string $busqueda Término de búsqueda.
   * @param int $limite Número máximo de resultados a devolver.
   * @param int $offset Número de resultados a omitir.
   * @return array Lista de ofertas activas.
   */
  public function obtenerOfertasActivas($busqueda = '', $limite = 50, $offset = 0)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerOfertasActivas.");
      return [];
    }
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $estado_activo_oferta_id = $this->getIdEstadoPorNombre('activo');
    $estado_vencida_oferta_id = $this->getIdEstadoPorNombre('vencida');
    $estado_activo_empresa_id = $this->getIdEstadoPorNombre('activo'); // Asumiendo que 'activo' para empresa es el mismo ID

    // Actualizar el estado de las ofertas vencidas antes de la consulta principal
    if ($estado_vencida_oferta_id !== false && $estado_activo_oferta_id !== false) {
      $fecha_actual = date('Y-m-d');
      $sql_update_expired = "UPDATE oferta
                               SET estado_id_estado = $estado_vencida_oferta_id
                               WHERE fecha_vencimiento < '$fecha_actual'
                               AND estado_id_estado = $estado_activo_oferta_id";
      if ($this->conexion) {
        $update_res = mysqli_query($this->conexion, $sql_update_expired);
        if (!$update_res) {
          error_log("ERROR DB (update expired): " . mysqli_error($this->conexion) . " SQL: " . $sql_update_expired);
        }
      } else {
        error_log("ERROR: No se pudo actualizar ofertas vencidas, conexión DB no establecida.");
      }
    } else {
      error_log("ADVERTENCIA: No se pudieron obtener los IDs de estado 'activo' o 'vencida' para actualizar ofertas.");
    }

    // Si el estado activo de la oferta o empresa no se encontró, no tiene sentido continuar
    if ($estado_activo_oferta_id === false || $estado_activo_empresa_id === false) {
      error_log("ERROR: No se pudo obtener el ID del estado 'activo' para ofertas o empresas. No se pueden cargar ofertas activas.");
      return [];
    }


    $where = "WHERE o.estado_id_estado = $estado_activo_oferta_id
              AND e.estado_id_estado = $estado_activo_empresa_id"; // Filtrar por ofertas activas Y empresas activas

    if (!empty($busqueda)) {
      $where .= " AND (o.titulo LIKE '%$busqueda%'
                        OR o.descripcion LIKE '%$busqueda%'
                        OR e.nombre LIKE '%$busqueda%'
                        OR ac.nombre LIKE '%$busqueda%')";
    }

    $sql = "SELECT o.*,
                       m.nombre as modalidad_nombre,
                       toferta.nombre as tipo_oferta_nombre,
                       ac.nombre as area_conocimiento_nombre,
                       e.nombre as empresa_nombre,
                       e.idEmpresa as empresa_idEmpresa,
                       est.nombre as estado_nombre
                FROM oferta o
                LEFT JOIN modalidad m ON o.modalidad_id_modalidad = m.id_modalidad
                LEFT JOIN tipo_oferta toferta ON o.tipo_oferta_id_tipo_oferta = toferta.id_tipo_oferta
                LEFT JOIN area_conocimiento ac ON o.area_conocimiento_id_area = ac.id_area
                JOIN empresa e ON o.empresa_idEmpresa = e.idEmpresa -- Usar JOIN para asegurar que la empresa exista y esté activa
                LEFT JOIN estado est ON o.estado_id_estado = est.id_estado
                $where
                ORDER BY o.fecha_creacion DESC
                LIMIT $limite OFFSET $offset";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerOfertasActivas: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $ofertas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $ofertas[] = $fila;
    }

    return $ofertas;
  }

  /**
   * Cuenta el total de ofertas activas, opcionalmente filtradas por un término de búsqueda.
   * @param string $busqueda Término de búsqueda.
   * @return int Número total de ofertas.
   */
  public function contarOfertasActivas($busqueda = '')
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en contarOfertasActivas.");
      return 0;
    }
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $estado_activo_oferta_id = $this->getIdEstadoPorNombre('activo');
    $estado_activo_empresa_id = $this->getIdEstadoPorNombre('activo');

    if ($estado_activo_oferta_id === false || $estado_activo_empresa_id === false) {
      error_log("ERROR: No se pudo obtener el ID del estado 'activo' para ofertas o empresas. No se pueden contar ofertas activas.");
      return 0;
    }

    $where = "WHERE o.estado_id_estado = $estado_activo_oferta_id
              AND e.estado_id_estado = $estado_activo_empresa_id"; // Filtrar por ofertas activas Y empresas activas

    if (!empty($busqueda)) {
      $where .= " AND (o.titulo LIKE '%$busqueda%'
                        OR o.descripcion LIKE '%$busqueda%'
                        OR e.nombre LIKE '%$busqueda%'
                        OR ac.nombre LIKE '%$busqueda%')";
    }

    $sql = "SELECT COUNT(*) as total
                FROM oferta o
                JOIN empresa e ON o.empresa_idEmpresa = e.idEmpresa -- Usar JOIN para asegurar que la empresa exista y esté activa
                LEFT JOIN area_conocimiento ac ON o.area_conocimiento_id_area = ac.id_area
                $where";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en contarOfertasActivas: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return 0;
    }
    $fila = mysqli_fetch_assoc($resultado);

    return $fila['total'];
  }


  public function obtenerOfertasPorEmpresa($idEmpresa, $busqueda = '', $limite = 50, $offset = 0)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerOfertasPorEmpresa.");
      return [];
    }
    $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $estado_inactivo_id = $this->getIdEstadoPorNombre('inactivo');
    $estado_vencida_id = $this->getIdEstadoPorNombre('vencida');
    $estado_activo_id = $this->getIdEstadoPorNombre('activo');

    if ($estado_vencida_id !== false && $estado_activo_id !== false) {
      $fecha_actual = date('Y-m-d');
      $sql_update_expired = "UPDATE oferta
                               SET estado_id_estado = $estado_vencida_id
                               WHERE fecha_vencimiento < '$fecha_actual'
                               AND estado_id_estado = $estado_activo_id";
      if ($this->conexion) {
        $update_res = mysqli_query($this->conexion, $sql_update_expired);
        if (!$update_res) {
          error_log("ERROR DB (update expired company): " . mysqli_error($this->conexion) . " SQL: " . $sql_update_expired);
        }
      } else {
        error_log("ERROR: No se pudo actualizar ofertas vencidas para empresa, conexión DB no establecida.");
      }
    }

    $where = "WHERE o.empresa_idEmpresa = '$idEmpresa'";
    if ($estado_inactivo_id !== false) {
      $where .= " AND o.estado_id_estado <> $estado_inactivo_id";
    }

    if (!empty($busqueda)) {
      $where .= " AND (o.titulo LIKE '%$busqueda%'
                        OR o.descripcion LIKE '%$busqueda%'
                        OR ac.nombre LIKE '%$busqueda%')";
    }

    $sql = "SELECT o.*,
                       m.nombre as modalidad_nombre,
                       toferta.nombre as tipo_oferta_nombre,
                       ac.nombre as area_conocimiento_nombre,
                       est.nombre as estado_nombre
                FROM oferta o
                LEFT JOIN modalidad m ON o.modalidad_id_modalidad = m.id_modalidad
                LEFT JOIN tipo_oferta toferta ON o.tipo_oferta_id_tipo_oferta = toferta.id_tipo_oferta
                LEFT JOIN area_conocimiento ac ON o.area_conocimiento_id_area = ac.id_area
                LEFT JOIN empresa e ON o.empresa_idEmpresa = e.idEmpresa
                LEFT JOIN estado est ON o.estado_id_estado = est.id_estado
                $where
                ORDER BY o.fecha_creacion DESC
                LIMIT $limite OFFSET $offset";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerOfertasPorEmpresa: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $ofertas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $ofertas[] = $fila;
    }

    return $ofertas;
  }

  public function contarOfertasPorEmpresa($idEmpresa, $busqueda = '')
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en contarOfertasPorEmpresa.");
      return 0;
    }
    $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $estado_inactivo_id = $this->getIdEstadoPorNombre('inactivo');

    $where = "WHERE o.empresa_idEmpresa = '$idEmpresa'";
    if ($estado_inactivo_id !== false) {
      $where .= " AND o.estado_id_estado <> $estado_inactivo_id";
    }

    if (!empty($busqueda)) {
      $where .= " AND (o.titulo LIKE '%$busqueda%'
                        OR o.descripcion LIKE '%$busqueda%'
                        OR ac.nombre LIKE '%$busqueda%')";
    }

    $sql = "SELECT COUNT(*) as total
                FROM oferta o
                LEFT JOIN area_conocimiento ac ON o.area_conocimiento_id_area = ac.id_area
                $where";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en contarOfertasPorEmpresa: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return 0;
    }
    $fila = mysqli_fetch_assoc($resultado);

    return $fila['total'];
  }

  public function actualizar($idOferta, $datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al actualizar oferta.");
      }
      $campos_permitidos = [
        'titulo',
        'descripcion',
        'requisitos',
        'beneficios',
        'modalidad_id_modalidad',
        'tipo_oferta_id_tipo_oferta',
        'duracion_meses',
        'horario',
        'remuneracion',
        'area_conocimiento_id_area',
        'semestre_minimo',
        'promedio_minimo',
        'habilidades_requeridas',
        'fecha_inicio',
        'fecha_fin',
        'fecha_vencimiento',
        'cupos_disponibles',
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
            if (in_array($campo, ['modalidad_id_modalidad', 'tipo_oferta_id_tipo_oferta', 'duracion_meses', 'area_conocimiento_id_area', 'semestre_minimo', 'cupos_disponibles', 'estado_id_estado'])) {
              $updates[] = "$campo=" . (int) $escaped_valor;
            } elseif ($campo === 'promedio_minimo') {
              $updates[] = "$campo=" . (float) $escaped_valor;
            } else {
              $updates[] = "$campo='$escaped_valor'";
            }
          }
        }
      }

      if (empty($updates)) {
        throw new Exception("No hay datos para actualizar.");
      }

      $idOferta = mysqli_real_escape_string($this->conexion, $idOferta);
      $sql = "UPDATE oferta SET " . implode(', ', $updates) . " WHERE idOferta='$idOferta'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Oferta actualizada correctamente.'];
      } else {
        error_log("ERROR DB (actualizar oferta): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al actualizar oferta: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (actualizar oferta): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function desactivar($idOferta)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al desactivar oferta.");
      }
      $idOferta = mysqli_real_escape_string($this->conexion, $idOferta);

      $estado_inactivo_id = $this->getIdEstadoPorNombre('inactivo');
      if ($estado_inactivo_id === false) {
        throw new Exception("El estado 'inactivo' no se encontró en la base de datos.");
      }

      if (!$this->obtenerPorId($idOferta)) {
        throw new Exception("La oferta no existe.");
      }

      $sql = "UPDATE oferta SET estado_id_estado = $estado_inactivo_id WHERE idOferta='$idOferta'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Oferta desactivada correctamente.'];
      } else {
        error_log("ERROR DB (desactivar oferta): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al desactivar oferta: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (desactivar oferta): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

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

  public function obtenerModalidades()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerModalidades.");
      return [];
    }
    $sql = "SELECT * FROM modalidad ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerModalidades: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $modalidades = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $modalidades[] = $fila;
    }
    return $modalidades;
  }

  public function obtenerTiposOferta()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTiposOferta.");
      return [];
    }
    $sql = "SELECT * FROM tipo_oferta ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerTiposOferta: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $tipos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $tipos[] = $fila;
    }
    return $tipos;
  }

  public function obtenerAreasConocimiento()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerAreasConocimiento.");
      return [];
    }
    $sql = "SELECT * FROM area_conocimiento ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerAreasConocimiento: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $areas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $areas[] = $fila;
    }
    return $areas;
  }

  public function obtenerEstados()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEstados.");
      return [];
    }
    $sql = "SELECT * FROM estado ORDER BY nombre";
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

  public function obtenerCarreras()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerCarreras.");
      return [];
    }
    $sql = "SELECT * FROM carrera ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerCarreras: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $carreras = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $carreras[] = $fila;
    }
    return $carreras;
  }

  public function obtenerCarreraPorId($idCarrera)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerCarreraPorId.");
      return false;
    }
    $idCarrera = (int) mysqli_real_escape_string($this->conexion, $idCarrera);
    $sql = "SELECT * FROM carrera WHERE id_carrera = $idCarrera";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerCarreraPorId: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    return mysqli_fetch_assoc($resultado);
  }

  public function asociarCarreras($idOferta, $idCarreras)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al asociar carreras.");
      }
      $this->desasociarTodasLasCarreras($idOferta);

      if (empty($idCarreras)) {
        return true;
      }

      $values = [];
      foreach ($idCarreras as $idCarrera) {
        $idCarrera = (int) mysqli_real_escape_string($this->conexion, $idCarrera);
        $values[] = "('$idOferta', '$idCarrera')";
      }

      $sql = "INSERT INTO oferta_carrera_dirigida (oferta_idOferta, carrera_id_carrera) VALUES " . implode(',', $values);
      if (mysqli_query($this->conexion, $sql)) {
        return true;
      } else {
        error_log("ERROR DB (asociar carreras): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al asociar carreras: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR: Fallo en asociarCarreras: " . $e->getMessage() . " en línea " . $e->getLine());
      return false;
    }
  }

  public function desasociarTodasLasCarreras($idOferta)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al desasociar carreras.");
      }
      $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);
      $sql = "DELETE FROM oferta_carrera_dirigida WHERE oferta_idOferta = '$idOferta'";
      if (mysqli_query($this->conexion, $sql)) {
        return true;
      } else {
        error_log("ERROR DB (desasociar carreras): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al desasociar carreras: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR: Fallo en desasociarTodasLasCarreras: " . $e->getMessage() . " en línea " . $e->getLine());
      return false;
    }
  }

  public function obtenerCarrerasAsociadas($idOferta)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerCarrerasAsociadas.");
      return [];
    }
    $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);
    $sql = "SELECT carrera_id_carrera FROM oferta_carrera_dirigida WHERE oferta_idOferta = '$idOferta'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerCarrerasAsociadas: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $carreras = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $carreras[] = $fila['carrera_id_carrera'];
    }
    return $carreras;
  }
  /**
   * Cuenta el número de estudiantes interesados en una oferta específica.
   *
   * @param int $idOferta El ID de la oferta.
   * @return int El número total de interesados.
   */
  public function contarInteresadosPorOferta($idOferta)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en contarInteresadosPorOferta.");
      return 0;
    }
    $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);
    $sql = "SELECT COUNT(*) AS total_interesados
              FROM interes_estudiante_oferta ieo
              JOIN estudiante e ON ieo.estudiante_idEstudiante = e.idEstudiante
              WHERE ieo.oferta_idOferta = '$idOferta' AND e.estado_id_estado = 1"; // Filtrar por estado activo (ID 1)
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (contarInteresadosPorOferta): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return 0;
    }
    $fila = mysqli_fetch_assoc($resultado);
    return $fila['total_interesados'] ?? 0;
  }

  /**
   * Obtiene un listado de estudiantes que han mostrado interés en una oferta específica y están activos.
   *
   * @param int $idOferta El ID de la oferta.
   * @return array Un array de objetos de estudiantes interesados (idEstudiante, nombre, apellidos, carrera_nombre, fecha_interes).
   */
  public function obtenerEstudiantesInteresados($idOferta)
  {
    $estudiantes = [];
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEstudiantesInteresados.");
      return [];
    }
    $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);
    $sql = "SELECT
                  ieo.estudiante_idEstudiante AS idEstudiante,
                  e.nombre,
                  e.apellidos,
                  c.nombre AS carrera_nombre,
                  ieo.fecha_interes
              FROM
                  interes_estudiante_oferta ieo
              JOIN
                  estudiante e ON ieo.estudiante_idEstudiante = e.idEstudiante
              LEFT JOIN
                  carrera c ON e.carrera_id_carrera = c.id_carrera
              WHERE
                  ieo.oferta_idOferta = '$idOferta' AND e.estado_id_estado = 1
              ORDER BY
                  ieo.fecha_interes DESC";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerEstudiantesInteresados): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $estudiantes[] = $fila;
    }
    return $estudiantes;
  }

  /**
   * Obtiene ofertas publicadas dentro de un rango de fechas.
   *
   * @param string $fechaInicio Fecha de inicio del rango (YYYY-MM-DD).
   * @param string $fechaFin Fecha de fin del rango (YYYY-MM-DD).
   * @return array Un array de arrays asociativos con los datos de las ofertas.
   */
  public function obtenerOfertasPorRangoFecha($fechaInicio, $fechaFin)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerOfertasPorRangoFecha.");
      return [];
    }
    $fechaInicio = mysqli_real_escape_string($this->conexion, $fechaInicio);
    $fechaFin = mysqli_real_escape_string($this->conexion, $fechaFin);

    $sql = "SELECT
                o.idOferta,
                o.titulo,
                o.descripcion,
                o.fecha_creacion AS fecha_publicacion,
                o.fecha_vencimiento,
                e.nombre AS empresa_nombre,
                m.nombre AS modalidad_nombre,
                toferta.nombre AS tipo_oferta_nombre,
                est.nombre AS estado_nombre
            FROM
                oferta o
            JOIN
                empresa e ON o.empresa_idEmpresa = e.idEmpresa
            LEFT JOIN
                modalidad m ON o.modalidad_id_modalidad = m.id_modalidad
            LEFT JOIN
                tipo_oferta toferta ON o.tipo_oferta_id_tipo_oferta = toferta.id_tipo_oferta
            LEFT JOIN
                estado est ON o.estado_id_estado = est.id_estado
            WHERE
                o.fecha_creacion BETWEEN '$fechaInicio' AND '$fechaFin'
            ORDER BY
                o.fecha_creacion DESC";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerOfertasPorRangoFecha): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $ofertas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $ofertas[] = $fila;
    }
    return $ofertas;
  }

  /**
   * Obtiene el top N de ofertas con más estudiantes interesados.
   *
   * @param int $limite El número máximo de ofertas a devolver.
   * @return array Un array de arrays asociativos con los datos de las ofertas.
   */
  public function obtenerTopOfertasInteres($limite = 5)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTopOfertasInteres.");
      return [];
    }
    $limite = (int) $limite;
    $sql = "SELECT
                o.idOferta,
                o.titulo,
                e.nombre AS empresa_nombre,
                COUNT(ieo.estudiante_idEstudiante) AS total_interesados
            FROM
                oferta o
            JOIN
                empresa e ON o.empresa_idEmpresa = e.idEmpresa
            LEFT JOIN
                interes_estudiante_oferta ieo ON o.idOferta = ieo.oferta_idOferta
            GROUP BY
                o.idOferta, o.titulo, e.nombre
            ORDER BY
                total_interesados DESC
            LIMIT $limite";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerTopOfertasInteres): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $ofertas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $ofertas[] = $fila;
    }
    return $ofertas;
  }
}