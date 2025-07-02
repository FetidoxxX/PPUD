<?php
require_once 'class_conec.php'; // Asegúrate que esta ruta sea correcta
require_once 'class_referencia.php'; // Incluir la clase Referencia

class Estudiante
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
      // Registrar el error si la conexión falla en el constructor
      error_log("ERROR CRÍTICO: Fallo al conectar a la base de datos en Estudiante::__construct: " . $e->getMessage());
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
   * Registra un nuevo estudiante en la base de datos.
   * @param array $datos Array asociativo con los datos del estudiante.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function registrar($datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al registrar estudiante.");
      }
      // Validar campos requeridos
      $campos_requeridos = ['idEstudiante', 'nombre', 'apellidos', 'correo', 'telefono', 'fechaNac', 'n_doc', 'direccion', 'contrasena', 'tipo_documento'];

      foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
          throw new Exception("El campo $campo es requerido.");
        }
      }

      // Escapar datos
      $idEstudiante = mysqli_real_escape_string($this->conexion, $datos['idEstudiante']);
      $nombre = mysqli_real_escape_string($this->conexion, $datos['nombre']);
      $apellidos = mysqli_real_escape_string($this->conexion, $datos['apellidos']);
      $correo = mysqli_real_escape_string($this->conexion, $datos['correo']);
      $telefono = mysqli_real_escape_string($this->conexion, $datos['telefono']);
      $fechaNac = mysqli_real_escape_string($this->conexion, $datos['fechaNac']);
      $n_doc = mysqli_real_escape_string($this->conexion, $datos['n_doc']);
      $direccion = mysqli_real_escape_string($this->conexion, $datos['direccion']);
      $contrasena = mysqli_real_escape_string($this->conexion, $datos['contrasena']);
      $tipo_documento_id_tipo = (int) $datos['tipo_documento'];
      $ciudad_id_ciudad = isset($datos['ciudad_id_ciudad']) ? (int) $datos['ciudad_id_ciudad'] : 1; // Asume Bogotá (ID 1) si no se provee

      // Campos opcionales (actualizados según la tabla que proporcionaste)
      $codigo_estudiante = isset($datos['codigo_estudiante']) && $datos['codigo_estudiante'] !== '' ? mysqli_real_escape_string($this->conexion, $datos['codigo_estudiante']) : NULL;
      $semestre = isset($datos['semestre']) && $datos['semestre'] !== '' ? (int) $datos['semestre'] : NULL;
      $promedio_academico = isset($datos['promedio_academico']) && $datos['promedio_academico'] !== '' ? (float) $datos['promedio_academico'] : NULL;
      $habilidades = isset($datos['habilidades']) && $datos['habilidades'] !== '' ? mysqli_real_escape_string($this->conexion, $datos['habilidades']) : NULL;
      $experiencia_laboral = isset($datos['experiencia_laboral']) && $datos['experiencia_laboral'] !== '' ? mysqli_real_escape_string($this->conexion, $datos['experiencia_laboral']) : NULL;
      $certificaciones = isset($datos['certificaciones']) && $datos['certificaciones'] !== '' ? mysqli_real_escape_string($this->conexion, $datos['certificaciones']) : NULL;
      $idiomas = isset($datos['idiomas']) && $datos['idiomas'] !== '' ? mysqli_real_escape_string($this->conexion, $datos['idiomas']) : NULL;
      $objetivos_profesionales = isset($datos['objetivos_profesionales']) && $datos['objetivos_profesionales'] !== '' ? mysqli_real_escape_string($this->conexion, $datos['objetivos_profesionales']) : NULL;
      $carrera_id_carrera = isset($datos['carrera_id_carrera']) && $datos['carrera_id_carrera'] !== '' ? (int) $datos['carrera_id_carrera'] : NULL; // Asume NULL si no se provee para la carrera principal
      $disponibilidad_id_disponibilidad = isset($datos['disponibilidad_id_disponibilidad']) && $datos['disponibilidad_id_disponibilidad'] !== '' ? (int) $datos['disponibilidad_id_disponibilidad'] : NULL;
      // estado_id_estado es por defecto activo
      $estado_id_estado = 1;


      // Verificar si ya existe el ID o el correo o el número de documento
      if ($this->existeEstudiante($idEstudiante)) {
        throw new Exception("Ya existe un estudiante con ese ID.");
      }
      if ($this->existeCorreo($correo)) {
        throw new Exception("Ya existe un estudiante con ese correo.");
      }
      if ($this->existeNdoc($n_doc)) {
        throw new Exception("Ya existe un estudiante con ese número de documento.");
      }

      // Insertar estudiante
      $sql = "INSERT INTO estudiante (
                idEstudiante, contrasena, nombre, correo, telefono, apellidos, fechaNac, direccion, n_doc,
                tipo_documento_id_tipo, ciudad_id_ciudad, codigo_estudiante, carrera_id_carrera, semestre,
                promedio_academico, habilidades, experiencia_laboral, certificaciones, idiomas, objetivos_profesionales,
                disponibilidad_id_disponibilidad, estado_id_estado, fecha_creacion, fecha_actualizacion
              ) VALUES (
                '$idEstudiante', '$contrasena', '$nombre', '$correo', '$telefono', '$apellidos', '$fechaNac', '$direccion', '$n_doc',
                " . ($tipo_documento_id_tipo ? "$tipo_documento_id_tipo" : "NULL") . ",
                " . ($ciudad_id_ciudad ? "$ciudad_id_ciudad" : "NULL") . ",
                " . ($codigo_estudiante ? "'$codigo_estudiante'" : "NULL") . ",
                " . ($carrera_id_carrera ? "$carrera_id_carrera" : "NULL") . ",
                " . ($semestre ? "$semestre" : "NULL") . ",
                " . ($promedio_academico ? "$promedio_academico" : "NULL") . ",
                " . ($habilidades ? "'$habilidades'" : "NULL") . ",
                " . ($experiencia_laboral ? "'$experiencia_laboral'" : "NULL") . ",
                " . ($certificaciones ? "'$certificaciones'" : "NULL") . ",
                " . ($idiomas ? "'$idiomas'" : "NULL") . ",
                " . ($objetivos_profesionales ? "'$objetivos_profesionales'" : "NULL") . ",
                " . ($disponibilidad_id_disponibilidad ? "$disponibilidad_id_disponibilidad" : "NULL") . ",
                " . ($estado_id_estado ? "$estado_id_estado" : "NULL") . ",
                NOW(), NOW()
              )";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Estudiante registrado correctamente.'];
      } else {
        error_log("ERROR DB (registrar estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al registrar: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (registrar estudiante): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Verifica si un estudiante existe por su ID.
   * @param string $idEstudiante ID del estudiante.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeEstudiante($idEstudiante)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeEstudiante.");
      return false;
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
    $sql = "SELECT COUNT(*) FROM estudiante WHERE idEstudiante='$idEstudiante'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeEstudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Verifica si un estudiante existe por su correo.
   * @param string $correo Correo del estudiante.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeCorreo($correo)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeCorreo (Estudiante).");
      return false;
    }
    $correo = mysqli_real_escape_string($this->conexion, $correo);
    $sql = "SELECT COUNT(*) FROM estudiante WHERE correo='$correo'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeCorreo Estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Verifica si un estudiante existe por su número de documento.
   * @param string $n_doc Número de documento del estudiante.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeNdoc($n_doc)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeNdoc (Estudiante).");
      return false;
    }
    $n_doc = mysqli_real_escape_string($this->conexion, $n_doc);
    $sql = "SELECT COUNT(*) FROM estudiante WHERE n_doc='$n_doc'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeNdoc Estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Obtiene los datos de un estudiante por su ID.
   * @param string $idEstudiante ID del estudiante.
   * @return array|false Datos del estudiante o false si no se encuentra.
   */
  public function obtenerPorId($idEstudiante)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorId (Estudiante).");
      return false;
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
    $sql = "SELECT e.*,
                   td.nombre AS tipo_documento_nombre,
                   c.nombre AS ciudad_nombre,
                   ca.nombre AS carrera_nombre
            FROM estudiante e
            LEFT JOIN tipo_documento td ON e.tipo_documento_id_tipo = td.id_tipo
            LEFT JOIN ciudad c ON e.ciudad_id_ciudad = c.id_ciudad
            LEFT JOIN carrera ca ON e.carrera_id_carrera = ca.id_carrera
            WHERE e.idEstudiante = '$idEstudiante'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerPorId estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    return mysqli_fetch_assoc($resultado);
  }

  /**
   * Obtiene todos los estudiantes, opcionalmente filtrados por un término de búsqueda.
   * @param string $busqueda Término de búsqueda (nombre, apellidos, correo, documento, ID).
   * @return array Lista de estudiantes.
   */
  public function obtenerTodos($busqueda = '')
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTodos (Estudiante).");
      return [];
    }
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $sql = "SELECT e.*,
                   td.nombre AS tipo_documento_nombre,
                   c.nombre AS ciudad_nombre
            FROM estudiante e
            LEFT JOIN tipo_documento td ON e.tipo_documento_id_tipo = td.id_tipo
            LEFT JOIN ciudad c ON e.ciudad_id_ciudad = c.id_ciudad";

    if (!empty($busqueda)) {
      $sql .= " WHERE e.nombre LIKE '%$busqueda%'
                OR e.apellidos LIKE '%$busqueda%'
                OR e.correo LIKE '%$busqueda%'
                OR e.n_doc LIKE '%$busqueda%'
                OR e.idEstudiante LIKE '%$busqueda%'";
    }

    $sql .= " ORDER BY e.nombre ASC, e.apellidos ASC";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerTodos estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }

    $estudiantes = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $estudiantes[] = $fila;
    }
    return $estudiantes;
  }

  /**
   * Valida las credenciales de inicio de sesión para un estudiante.
   * @param string $idEstudiante ID del estudiante.
   * @param string $contrasena Contraseña del estudiante.
   * @return array|false Datos del estudiante si las credenciales son válidas, false en caso contrario.
   */
  public function validarCredenciales($idEstudiante, $contrasena)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en validarCredenciales (Estudiante).");
      return false;
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
    $contrasena = mysqli_real_escape_string($this->conexion, $contrasena); // Asume que la contraseña no está hasheada para la comparación

    $sql = "SELECT * FROM estudiante WHERE idEstudiante='$idEstudiante' AND contrasena='$contrasena'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (validarCredenciales estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }

    if (mysqli_num_rows($resultado) == 1) {
      return mysqli_fetch_assoc($resultado);
    }
    return false;
  }

  /**
   * Actualiza los datos de un estudiante existente.
   * @param string $idEstudiante ID del estudiante a actualizar.
   * @param array $datos Array asociativo con los datos a actualizar.
   * @param array $carreras_interes_ids Array de IDs de las carreras de interés.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function actualizar($idEstudiante, $datos, $carreras_interes_ids = [])
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida para actualizar estudiante.");
      }
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $updates = [];
      $campos_permitidos = [
        'nombre',
        'apellidos',
        'correo',
        'telefono',
        'fechaNac',
        'n_doc',
        'direccion',
        'codigo_estudiante',
        'semestre',
        'promedio_academico',
        'habilidades',
        'experiencia_laboral',
        'certificaciones',
        'idiomas',
        'objetivos_profesionales',
        'tipo_documento_id_tipo',
        'ciudad_id_ciudad',
        'carrera_id_carrera',
        'disponibilidad_id_disponibilidad',
        'estado_id_estado'
      ];

      foreach ($campos_permitidos as $campo) {
        // Asegurarse de que el campo exista en $datos y no sea vacío para evitar NULLs incorrectos o errores
        // Se permite que algunos campos sean opcionales (NULL en DB)
        if (array_key_exists($campo, $datos)) {
          $valor = $datos[$campo];

          if ($campo === 'correo' && !empty($valor) && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido.");
          }

          if (in_array($campo, ['tipo_documento_id_tipo', 'ciudad_id_ciudad', 'semestre', 'carrera_id_carrera', 'disponibilidad_id_disponibilidad', 'estado_id_estado'])) {
            $updates[] = "$campo = " . (empty($valor) && $valor !== 0 && $valor !== '0' ? 'NULL' : (int) $valor);
          } elseif ($campo === 'promedio_academico') {
            $updates[] = "$campo = " . (empty($valor) && $valor !== 0.0 && $valor !== '0' ? 'NULL' : (float) $valor);
          } else {
            // Para campos de texto, si es vacío, se guarda como NULL, si tiene valor, se escapa
            $updates[] = "$campo = " . (empty($valor) ? 'NULL' : "'" . mysqli_real_escape_string($this->conexion, $valor) . "'");
          }
        }
      }

      // Añadir la fecha_actualizacion automáticamente
      $updates[] = "fecha_actualizacion = NOW()";


      if (empty($updates) && empty($carreras_interes_ids)) {
        return ['success' => false, 'message' => 'No hay datos para actualizar.'];
      }

      if (!empty($updates)) {
        $sql = "UPDATE estudiante SET " . implode(', ', $updates) . " WHERE idEstudiante='$idEstudiante'";
        if (!mysqli_query($this->conexion, $sql)) {
          error_log("ERROR DB (actualizar estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
          throw new Exception("Error al actualizar datos personales: " . mysqli_error($this->conexion));
        }
      }

      // Actualizar carreras de interés solo si se proporcionan (puede ser un array vacío)
      // Se modificó la condición para aceptar un array vacío para borrar todas las relaciones
      if (is_array($carreras_interes_ids)) {
        $this->actualizarCarrerasDeInteres($idEstudiante, $carreras_interes_ids);
      }

      return ['success' => true, 'message' => 'Estudiante actualizado correctamente.'];

    } catch (Exception $e) {
      error_log("ERROR (actualizar estudiante): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Actualiza las carreras de interés de un estudiante.
   * @param string $idEstudiante ID del estudiante.
   * @param array $carreras_interes_ids Array de IDs de las carreras a asociar.
   * @return bool True si la operación fue exitosa, false en caso contrario.
   */
  private function actualizarCarrerasDeInteres($idEstudiante, $carreras_interes_ids)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida para actualizarCarrerasDeInteres.");
      return false;
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);

    // 1. Eliminar todas las asociaciones existentes para este estudiante
    $sql_delete = "DELETE FROM interes_estudiante_carrera WHERE estudiante_idEstudiante = '$idEstudiante'";
    if (!mysqli_query($this->conexion, $sql_delete)) {
      error_log("ERROR DB (actualizarCarrerasDeInteres - DELETE): " . mysqli_error($this->conexion) . " SQL: " . $sql_delete);
      throw new Exception("Error al eliminar carreras de interés existentes.");
    }

    // 2. Insertar las nuevas asociaciones
    if (!empty($carreras_interes_ids)) {
      $values = [];
      foreach ($carreras_interes_ids as $idCarrera) {
        $idCarrera = (int) mysqli_real_escape_string($this->conexion, $idCarrera);
        $values[] = "('$idEstudiante', $idCarrera)";
      }
      $sql_insert = "INSERT INTO interes_estudiante_carrera (estudiante_idEstudiante, carrera_id_carrera) VALUES " . implode(',', $values);
      if (!mysqli_query($this->conexion, $sql_insert)) {
        error_log("ERROR DB (actualizarCarrerasDeInteres - INSERT): " . mysqli_error($this->conexion) . " SQL: " . $sql_insert);
        throw new Exception("Error al insertar nuevas carreras de interés.");
      }
    }
    return true;
  }

  /**
   * Obtiene las IDs de las carreras de interés de un estudiante.
   * @param string $idEstudiante ID del estudiante.
   * @return array IDs de las carreras de interés.
   */
  public function obtenerCarrerasDeInteres($idEstudiante)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerCarrerasDeInteres.");
      return [];
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
    $sql = "SELECT iec.carrera_id_carrera, c.nombre FROM interes_estudiante_carrera iec JOIN carrera c ON iec.carrera_id_carrera = c.id_carrera WHERE estudiante_idEstudiante = '$idEstudiante'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerCarrerasDeInteres): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $carreras = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $carreras[] = $fila; // Devolvemos el objeto con ID y nombre
    }
    return $carreras;
  }


  public function eliminar($idEstudiante)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al eliminar estudiante.");
      }
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);

      // Verificar si el estudiante existe
      if (!$this->existeEstudiante($idEstudiante)) {
        throw new Exception("El estudiante no existe.");
      }

      $sql = "DELETE FROM estudiante WHERE idEstudiante='$idEstudiante'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Estudiante eliminado correctamente.'];
      } else {
        error_log("ERROR DB (eliminar estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al eliminar: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (eliminar estudiante): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function mostrarInteresOferta($idEstudiante, $idOferta)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida para mostrar interés.");
      }
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);

      // Verificar si ya existe el interés para evitar duplicados
      if ($this->haMostradoInteres($idEstudiante, $idOferta)) {
        return ['success' => false, 'message' => 'Ya has mostrado interés en esta oferta.'];
      }

      $sql = "INSERT INTO interes_estudiante_oferta (estudiante_idEstudiante, oferta_idOferta, fecha_interes)
              VALUES ('$idEstudiante', $idOferta, NOW())";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => '¡Interés registrado con éxito!'];
      } else {
        error_log("ERROR DB (mostrarInteres): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al registrar interés: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR (mostrarInteres): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function eliminarInteresOferta($idEstudiante, $idOferta)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida para eliminar interés.");
      }
      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);

      $sql = "DELETE FROM interes_estudiante_oferta WHERE estudiante_idEstudiante = '$idEstudiante' AND oferta_idOferta = $idOferta";

      if (mysqli_query($this->conexion, $sql)) {
        // Verificar si se eliminó alguna fila
        if (mysqli_affected_rows($this->conexion) > 0) {
          return ['success' => true, 'message' => 'Interés eliminado correctamente.'];
        } else {
          return ['success' => false, 'message' => 'No se encontró interés para eliminar en esta oferta.'];
        }
      } else {
        error_log("ERROR DB (eliminarInteres): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al eliminar interés: " . mysqli_error($this->conexion));
      }
    } catch (Exception $e) {
      error_log("ERROR (eliminarInteres): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Verifica si un estudiante ha mostrado interés en una oferta.
   * @param string $idEstudiante ID del estudiante.
   * @param int $idOferta ID de la oferta.
   * @return bool True si ha mostrado interés, false en caso contrario.
   */
  public function haMostradoInteres($idEstudiante, $idOferta)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en haMostradoInteres.");
      return false;
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
    $idOferta = (int) mysqli_real_escape_string($this->conexion, $idOferta);

    $sql = "SELECT COUNT(*) as count FROM interes_estudiante_oferta WHERE estudiante_idEstudiante = '$idEstudiante' AND oferta_idOferta = $idOferta";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (haMostradoInteres): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_assoc($resultado);
    return $fila['count'] > 0;
  }

  public function cambiarContrasena($idEstudiante, $contrasenaActual, $contrasenaNueva)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida para cambiar contraseña.");
      }
      // Verificar contraseña actual
      if (!$this->validarCredenciales($idEstudiante, $contrasenaActual)) {
        throw new Exception("La contraseña actual es incorrecta.");
      }

      $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
      $contrasenaNueva = mysqli_real_escape_string($this->conexion, $contrasenaNueva);

      $sql = "UPDATE estudiante SET contrasena='$contrasenaNueva' WHERE idEstudiante='$idEstudiante'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
      } else {
        error_log("ERROR DB (cambiarContrasena estudiante): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al actualizar contraseña: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (cambiarContrasena estudiante): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Obtiene el perfil completo de un estudiante, incluyendo datos relacionados
   *
   * @param int $idEstudiante El ID del estudiante.
   * @return array|null Un array asociativo con los datos del estudiante, o null si no se encuentra.
   */
  public function obtenerPorIdParaEmpresa($idEstudiante)
  {
    $estudiante = null;
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorIdParaEmpresa.");
      return null;
    }
    $idEstudiante = mysqli_real_escape_string($this->conexion, $idEstudiante);
    $sql = "SELECT
                  e.idEstudiante,
                  e.nombre,
                  e.apellidos,
                  e.correo,
                  e.telefono,
                  e.fechaNac,
                  e.direccion,
                  e.n_doc,
                  td.nombre AS tipo_documento_nombre,
                  c.nombre AS ciudad_nombre,
                  e.codigo_estudiante,
                  car.nombre AS carrera_nombre,
                  e.semestre,
                  e.promedio_academico,
                  e.habilidades,
                  e.experiencia_laboral,
                  e.certificaciones,
                  e.idiomas,
                  e.objetivos_profesionales,
                  dh.nombre AS disponibilidad_nombre,
                  est.nombre AS estado_nombre
              FROM
                  estudiante e
              LEFT JOIN
                  tipo_documento td ON e.tipo_documento_id_tipo = td.id_tipo
              LEFT JOIN
                  ciudad c ON e.ciudad_id_ciudad = c.id_ciudad
              LEFT JOIN
                  carrera car ON e.carrera_id_carrera = car.id_carrera
              LEFT JOIN
                  disponibilidad_horaria dh ON e.disponibilidad_id_disponibilidad = dh.id_disponibilidad
              LEFT JOIN
                  estado est ON e.estado_id_estado = est.id_estado
              WHERE
                  e.idEstudiante = '$idEstudiante'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerPorIdParaEmpresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return null;
    }
    $estudiante = mysqli_fetch_assoc($resultado);

    // Si se encontró el estudiante, obtener sus carreras de interés
    if ($estudiante) {
      $carreras_interes = $this->obtenerCarrerasDeInteres($idEstudiante);
      // Extraer solo los nombres para el listado en el perfil.
      $estudiante['carreras_interes_nombres'] = array_column($carreras_interes, 'nombre');

      // Obtener las referencias asociadas a este estudiante, incluyendo solo las de tipo 'empresa_a_estudiante'
      $referenciaObj = new Referencia();
      // 'empresa_a_estudiante' es el nombre del tipo de referencia que se desea incluir
      $estudiante['referencias'] = $referenciaObj->obtenerTodas(null, $idEstudiante, 2);
    }

    return $estudiante;
  }
}