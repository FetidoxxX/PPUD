<?php
require_once 'class_conec.php';

class Catalogo
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
      error_log("ERROR CRÍTICO: Fallo al conectar a la base de datos en Catalogo::__construct: " . $e->getMessage());
      $this->conexion = null;
    }
  }

  public function __destruct()
  {
    if ($this->conexion) {
      mysqli_close($this->conexion);
    }
  }

  /**
   * Determina el nombre de la columna ID para una tabla de catálogo dada.
   * @param string $nombreTabla Nombre de la tabla.
   * @return string El nombre de la columna ID.
   */
  public function obtenerNombreColumnaId($nombreTabla)
  {
    // Casos específicos donde el ID no sigue el patrón 'id_<nombre_tabla>'
    if ($nombreTabla === 'tipo_documento') {
      return 'id_tipo'; // Para la tabla tipo_documento, el ID es id_tipo
    } elseif ($nombreTabla === 'area_conocimiento') {
      return 'id_area'; // Para la tabla area_conocimiento, el ID es id_area
    } elseif ($nombreTabla === 'empresa') {
      return 'idEmpresa'; // Para la tabla empresa, el ID es idEmpresa
    } elseif ($nombreTabla === 'estudiante') {
      return 'idEstudiante'; // Para la tabla estudiante, el ID es idEstudiante
    } elseif ($nombreTabla === 'administrador') {
      return 'idAdministrador'; // Para la tabla administrador, el ID es idAdministrador
    } elseif ($nombreTabla === 'oferta') {
      return 'idOferta'; // Para la tabla oferta, el ID es idOferta
    } elseif ($nombreTabla === 'referencia') {
      return 'idReferencia'; // Para la tabla referencia, el ID es idReferencia
    }
    // Para la mayoría de las tablas de catálogo, el ID es 'id_<nombre_tabla>'
    return 'id_' . $nombreTabla;
  }

  /**
   * Obtiene los nombres, tipos y si son anulables de las columnas de una tabla.
   * @param string $nombreTabla Nombre de la tabla.
   * @return array Array asociativo con 'column_name' => ['DATA_TYPE' => 'tipo', 'IS_NULLABLE' => 'YES/NO'].
   */
  public function obtenerColumnasTabla($nombreTabla)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerColumnasTabla.");
      return [];
    }
    $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);

    // Obtener el nombre de la base de datos actual
    $dbResult = mysqli_query($this->conexion, "SELECT DATABASE()");
    $databaseName = mysqli_fetch_row($dbResult)[0];

    $sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$databaseName' AND TABLE_NAME = '$nombreTabla'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerColumnasTabla $nombreTabla): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }

    $columnas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $columnas[$fila['COLUMN_NAME']] = ['DATA_TYPE' => $fila['DATA_TYPE'], 'IS_NULLABLE' => $fila['IS_NULLABLE']];
    }
    return $columnas;
  }

  /**
   * Lista todos los elementos de una tabla de catálogo.
   * @param string $nombreTabla Nombre de la tabla.
   * @param string $busqueda Término de búsqueda (opcional).
   * @return array Lista de elementos.
   */
  public function listarTodos($nombreTabla, $busqueda = '')
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en listarTodos ($nombreTabla).");
      return [];
    }
    $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $condicionWhere = '';
    if (!empty($busqueda)) {
      $columnasTabla = $this->obtenerColumnasTabla($nombreTabla);
      $searchableColumns = [];
      foreach ($columnasTabla as $columna => $info) {
        $tipo = $info['DATA_TYPE'];
        // Excluir columnas ID, de fecha/hora y numéricas de la búsqueda de texto general
        if (strpos($columna, 'id_') === 0 || strpos($columna, 'Id') !== false || in_array($tipo, ['timestamp', 'date', 'datetime', 'int', 'decimal', 'float', 'double', 'tinyint', 'smallint', 'mediumint', 'bigint', 'year'])) {
          continue;
        }
        $searchableColumns[] = "`" . mysqli_real_escape_string($this->conexion, $columna) . "` LIKE '%$busqueda%'";
      }
      if (!empty($searchableColumns)) {
        $condicionWhere = "WHERE " . implode(' OR ', $searchableColumns);
      }
    }

    // Siempre seleccionar todas las columnas para listar, para que el frontend decida qué mostrar
    // Se asume que 'nombre' siempre existe para ordenar. Si no, se podría ordenar por la columna ID.
    $orderBy = $this->columnaExiste($nombreTabla, 'nombre') ? 'nombre' : $this->obtenerNombreColumnaId($nombreTabla);
    $consultaSql = "SELECT * FROM `$nombreTabla` $condicionWhere ORDER BY `$orderBy`";
    $resultadoConsulta = mysqli_query($this->conexion, $consultaSql);
    if (!$resultadoConsulta) {
      error_log("ERROR DB (listarTodos $nombreTabla): " . mysqli_error($this->conexion) . " SQL: " . $consultaSql);
      return [];
    }

    $elementos = [];
    while ($fila = mysqli_fetch_assoc($resultadoConsulta)) {
      $elementos[] = $fila;
    }
    return $elementos;
  }

  /**
   * Obtiene un elemento de una tabla de catálogo por su ID.
   * @param string $nombreTabla Nombre de la tabla.
   * @param int $id ID del elemento.
   * @return array|false Los datos del elemento o false si no se encuentra.
   */
  public function obtenerPorId($nombreTabla, $id)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorId ($nombreTabla).");
      return false;
    }
    $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);
    $id = (int) mysqli_real_escape_string($this->conexion, $id);
    $nombreColumnaId = $this->obtenerNombreColumnaId($nombreTabla);

    $columnas = '*'; // Seleccionar todas las columnas para el detalle
    $consultaSql = "SELECT $columnas FROM `$nombreTabla` WHERE `$nombreColumnaId`='$id'";
    $resultadoConsulta = mysqli_query($this->conexion, $consultaSql);
    if (!$resultadoConsulta) {
      error_log("ERROR DB (obtenerPorId $nombreTabla): " . mysqli_error($this->conexion) . " SQL: " . $consultaSql);
      return false;
    }
    return mysqli_fetch_assoc($resultadoConsulta);
  }

  /**
   * Registra un nuevo elemento en una tabla de catálogo.
   * @param string $nombreTabla Nombre de la tabla.
   * @param array $datos Array asociativo con los datos.
   * @return array Resultado de la operación (éxito/error, mensaje, y nuevo ID).
   */
  public function registrar($nombreTabla, $datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al registrar ($nombreTabla).");
      }
      $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);

      // Validar que el campo 'nombre' exista y no esté vacío si la tabla lo tiene
      if ($this->columnaExiste($nombreTabla, 'nombre') && (!isset($datos['nombre']) || trim($datos['nombre']) === '')) {
        return ['success' => false, 'errors' => ['nombre' => 'El nombre es requerido.'], 'message' => 'Errores de validación.'];
      }

      // Validar unicidad del nombre si la tabla tiene la columna 'nombre'
      if ($this->columnaExiste($nombreTabla, 'nombre') && $this->existeNombre($nombreTabla, $datos['nombre'])) {
        return ['success' => false, 'errors' => ['nombre' => "Ya existe un elemento con el nombre '" . htmlspecialchars($datos['nombre']) . "' en esta tabla."], 'message' => "Ya existe un elemento con el nombre '" . htmlspecialchars($datos['nombre']) . "' en esta tabla."];
      }

      $columnasTabla = $this->obtenerColumnasTabla($nombreTabla);
      $insertColumns = [];
      $insertValues = [];

      foreach ($datos as $key => $value) {
        // Omitir columnas ID, de creación/actualización y de recuperación de código
        // Asumimos que las columnas ID comienzan con 'id_' o contienen 'Id'
        // También se excluyen contraseñas y rutas de archivos por seguridad/manejo específico
        if (strpos($key, 'id_') === 0 || strpos($key, 'Id') !== false || $key === 'fecha_creacion' || $key === 'fecha_actualizacion' || $key === 'codigo_recuperacion' || $key === 'codigo_expira_en' || $key === 'contrasena' || $key === 'hoja_vida_path') {
          continue;
        }

        if (array_key_exists($key, $columnasTabla)) {
          $insertColumns[] = "`" . mysqli_real_escape_string($this->conexion, $key) . "`";
          // Manejar diferentes tipos de datos para un correcto entrecomillado
          $dataType = $columnasTabla[$key]['DATA_TYPE'];
          if (in_array($dataType, ['int', 'decimal', 'float', 'double', 'tinyint', 'smallint', 'mediumint', 'bigint', 'year'])) {
            $insertValues[] = (is_numeric($value) && $value !== '') ? mysqli_real_escape_string($this->conexion, $value) : 'NULL'; // Almacenar NULL para valores numéricos vacíos
          } else if ($dataType === 'date') {
            $insertValues[] = "'" . mysqli_real_escape_string($this->conexion, date('Y-m-d', strtotime($value))) . "'";
          } else if ($dataType === 'datetime' || $dataType === 'timestamp') {
            $insertValues[] = "'" . mysqli_real_escape_string($this->conexion, date('Y-m-d H:i:s', strtotime($value))) . "'";
          } else {
            $insertValues[] = "'" . mysqli_real_escape_string($this->conexion, $value) . "'";
          }
        }
      }

      if (empty($insertColumns)) {
        throw new Exception("No se proporcionaron datos válidos para insertar.");
      }

      $consultaSql = "INSERT INTO `$nombreTabla` (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
      if (mysqli_query($this->conexion, $consultaSql)) {
        return ['success' => true, 'message' => 'Elemento registrado correctamente.', 'id' => mysqli_insert_id($this->conexion)];
      } else {
        throw new Exception("Error al registrar elemento: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("Error en registrar ($nombreTabla): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Actualiza un elemento en una tabla de catálogo.
   * @param string $nombreTabla Nombre de la tabla.
   * @param int $id ID del elemento a actualizar.
   * @param array $datos Array asociativo con los datos.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function actualizar($nombreTabla, $id, $datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al actualizar ($nombreTabla).");
      }
      $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);
      $id = (int) mysqli_real_escape_string($this->conexion, $id);
      $nombreColumnaId = $this->obtenerNombreColumnaId($nombreTabla);

      // Validar que el campo 'nombre' exista y no esté vacío si la tabla lo tiene
      if ($this->columnaExiste($nombreTabla, 'nombre') && (!isset($datos['nombre']) || trim($datos['nombre']) === '')) {
        return ['success' => false, 'errors' => ['nombre' => 'El nombre es requerido.'], 'message' => 'Errores de validación.'];
      }

      // Validar unicidad del nombre, excluyendo el elemento actual si la tabla tiene la columna 'nombre'
      if ($this->columnaExiste($nombreTabla, 'nombre') && $this->existeNombre($nombreTabla, $datos['nombre'], $id, $nombreColumnaId)) {
        return ['success' => false, 'errors' => ['nombre' => "Ya existe otro elemento con el nombre '" . htmlspecialchars($datos['nombre']) . "' en esta tabla."], 'message' => "Ya existe otro elemento con el nombre '" . htmlspecialchars($datos['nombre']) . "' en esta tabla."];
      }

      $columnasTabla = $this->obtenerColumnasTabla($nombreTabla);
      $actualizaciones = [];

      foreach ($datos as $key => $value) {
        // Omitir columnas ID, de creación y la columna ID principal
        // También se excluyen contraseñas y rutas de archivos por seguridad/manejo específico
        if (strpos($key, 'id_') === 0 || strpos($key, 'Id') !== false || $key === 'fecha_creacion' || $key === $nombreColumnaId || $key === 'codigo_recuperacion' || $key === 'codigo_expira_en' || $key === 'contrasena' || $key === 'hoja_vida_path') {
          continue;
        }

        if (array_key_exists($key, $columnasTabla)) {
          $dataType = $columnasTabla[$key]['DATA_TYPE'];
          $escapedValue = '';
          if (in_array($dataType, ['int', 'decimal', 'float', 'double', 'tinyint', 'smallint', 'mediumint', 'bigint', 'year'])) {
            $escapedValue = (is_numeric($value) && $value !== '') ? mysqli_real_escape_string($this->conexion, $value) : 'NULL';
          } else if ($dataType === 'date') {
            $escapedValue = "'" . mysqli_real_escape_string($this->conexion, date('Y-m-d', strtotime($value))) . "'";
          } else if ($dataType === 'datetime' || $dataType === 'timestamp') {
            $escapedValue = "'" . mysqli_real_escape_string($this->conexion, date('Y-m-d H:i:s', strtotime($value))) . "'";
          } else {
            $escapedValue = "'" . mysqli_real_escape_string($this->conexion, $value) . "'";
          }
          $actualizaciones[] = "`" . mysqli_real_escape_string($this->conexion, $key) . "` = " . $escapedValue;
        }
      }

      if (empty($actualizaciones)) {
        throw new Exception("No se proporcionaron datos válidos para actualizar.");
      }

      $consultaSql = "UPDATE `$nombreTabla` SET " . implode(', ', $actualizaciones) . " WHERE `$nombreColumnaId`='$id'";
      if (mysqli_query($this->conexion, $consultaSql)) {
        return ['success' => true, 'message' => 'Elemento actualizado correctamente.'];
      } else {
        error_log("ERROR DB (actualizar $nombreTabla): " . mysqli_error($this->conexion) . " SQL: " . $consultaSql);
        throw new Exception("Error al actualizar elemento: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("Error en actualizar ($nombreTabla): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Elimina un elemento de una tabla de catálogo.
   * NOTA: Esto es una eliminación física. Asegúrate de que no haya dependencias de clave foránea.
   * @param string $nombreTabla Nombre de la tabla.
   * @param int $id ID del elemento a eliminar.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function eliminar($nombreTabla, $id)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al eliminar ($nombreTabla).");
      }
      $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);
      $id = (int) mysqli_real_escape_string($this->conexion, $id);
      $nombreColumnaId = $this->obtenerNombreColumnaId($nombreTabla);

      $consultaSql = "DELETE FROM `$nombreTabla` WHERE `$nombreColumnaId`='$id'";
      if (mysqli_query($this->conexion, $consultaSql)) {
        return ['success' => true, 'message' => 'Elemento eliminado correctamente.'];
      } else {
        // Capturar error de clave foránea si existe
        if (mysqli_errno($this->conexion) == 1451) { // Error 1451: Cannot delete or update a parent row: a foreign key constraint fails
          throw new Exception("No se puede eliminar el elemento porque está siendo utilizado en otras partes del sistema.");
        } else {
          throw new Exception("Error al eliminar elemento: " . mysqli_error($this->conexion));
        }
      }
    } catch (Exception $e) {
      error_log("Error en eliminar ($nombreTabla): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Verifica si un nombre ya existe en una tabla de catálogo.
   * @param string $nombreTabla Nombre de la tabla.
   * @param string $nombre El nombre a verificar.
   * @param int|null $excluirId ID del elemento a excluir de la verificación (para actualizaciones).
   * @param string|null $nombreColumnaIdParam Nombre de la columna ID (opcional, se obtiene si no se provee).
   * @return bool True si el nombre ya existe, false en caso contrario.
   */
  public function existeNombre($nombreTabla, $nombre, $excluirId = null, $nombreColumnaIdParam = null)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeNombre ($nombreTabla).");
      return false;
    }
    $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);
    $nombre = mysqli_real_escape_string($this->conexion, $nombre);
    $nombreColumnaId = $nombreColumnaIdParam ?? $this->obtenerNombreColumnaId($nombreTabla);

    $consultaSql = "SELECT COUNT(*) FROM `$nombreTabla` WHERE nombre = '$nombre'";
    if ($excluirId !== null) {
      $excluirId = (int) mysqli_real_escape_string($this->conexion, $excluirId);
      $consultaSql .= " AND `$nombreColumnaId` != '$excluirId'";
    }

    $resultadoConsulta = mysqli_query($this->conexion, $consultaSql);
    if (!$resultadoConsulta) {
      error_log("ERROR DB (existeNombre $nombreTabla): " . mysqli_error($this->conexion) . " SQL: " . $consultaSql);
      return false;
    }
    $fila = mysqli_fetch_row($resultadoConsulta);
    return $fila[0] > 0;
  }

  /**
   * Verifica si una columna existe en una tabla dada.
   * @param string $nombreTabla Nombre de la tabla.
   * @param string $nombreColumna Nombre de la columna.
   * @return bool True si la columna existe, false en caso contrario.
   */
  private function columnaExiste($nombreTabla, $nombreColumna)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en columnaExiste.");
      return false;
    }
    $nombreTabla = mysqli_real_escape_string($this->conexion, $nombreTabla);
    $nombreColumna = mysqli_real_escape_string($this->conexion, $nombreColumna);

    $sql = "SHOW COLUMNS FROM `$nombreTabla` LIKE '$nombreColumna'";
    $resultado = mysqli_query($this->conexion, $sql);
    return mysqli_num_rows($resultado) > 0;
  }
}