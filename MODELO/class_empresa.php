<?php
require_once 'class_conec.php'; // Asegúrate de que esta clase esté incluida para usar Conectar::conec()

class Empresa
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
      // Registrar el error si la conexión falla en el constructor
      error_log("ERROR CRÍTICO: Fallo al conectar a la base de datos en Empresa::__construct: " . $e->getMessage());
      $this->conexion = null; // Asegura que la conexión sea nula si falló
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
   * Registra una nueva empresa en la base de datos.
   * @param array $datos Array asociativo con los datos de la empresa.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function registrar($datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al registrar empresa.");
      }
      // Validar campos requeridos para el registro
      $campos_requeridos = ['idEmpresa', 'nombre', 'correo', 'telefono', 'direccion', 'contrasena', 'tipo_documento', 'n_doc'];

      foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
          throw new Exception("El campo '$campo' es requerido.");
        }
      }

      // Escapar datos
      $idEmpresa = mysqli_real_escape_string($this->conexion, $datos['idEmpresa']);
      $nombre = mysqli_real_escape_string($this->conexion, $datos['nombre']);
      $correo = mysqli_real_escape_string($this->conexion, $datos['correo']);
      $telefono = mysqli_real_escape_string($this->conexion, $datos['telefono']);
      $direccion = mysqli_real_escape_string($this->conexion, $datos['direccion']);
      $contrasena = mysqli_real_escape_string($this->conexion, $datos['contrasena']); // Asumimos que la contraseña ya está hasheada o se hasheará antes de llamar a este método
      $n_doc = mysqli_real_escape_string($this->conexion, $datos['n_doc']);
      $tipo_documento_id_tipo = (int) $datos['tipo_documento']; // Se espera 'tipo_documento' del formulario de registro
      $ciudad_id_ciudad = isset($datos['ciudad_id_ciudad']) ? (int) $datos['ciudad_id_ciudad'] : 1; // Asume Bogotá (ID 1) si no se provee
      $descripcion = isset($datos['descripcion']) ? mysqli_real_escape_string($this->conexion, $datos['descripcion']) : NULL;
      $sector_id_sector = isset($datos['sector_id_sector']) ? (int) $datos['sector_id_sector'] : NULL;
      $sitio_web = isset($datos['sitio_web']) ? mysqli_real_escape_string($this->conexion, $datos['sitio_web']) : NULL;
      $numero_empleados = isset($datos['numero_empleados']) ? (int) $datos['numero_empleados'] : NULL;
      $ano_fundacion = isset($datos['ano_fundacion']) ? (int) $datos['ano_fundacion'] : NULL;
      $contacto_nombres = isset($datos['contacto_nombres']) ? mysqli_real_escape_string($this->conexion, $datos['contacto_nombres']) : NULL;
      $contacto_apellidos = isset($datos['contacto_apellidos']) ? mysqli_real_escape_string($this->conexion, $datos['contacto_apellidos']) : NULL;
      $contacto_cargo = isset($datos['contacto_cargo']) ? mysqli_real_escape_string($this->conexion, $datos['contacto_cargo']) : NULL;
      $estado_id_estado = isset($datos['estado_id_estado']) ? (int) $datos['estado_id_estado'] : 1; // Asume estado activo por defecto

      // Validar formato de email
      if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido.");
      }

      // Verificar si ya existe el ID o el correo o el número de documento
      if ($this->existeEmpresa($idEmpresa)) {
        throw new Exception("Ya existe una empresa registrada con este ID.");
      }
      if ($this->existeCorreo($correo)) {
        throw new Exception("Ya existe una empresa registrada con este correo.");
      }
      if ($this->existeNdoc($n_doc)) {
        throw new Exception("Ya existe una empresa registrada con este número de documento (NIT).");
      }

      // Insertar empresa
      $sql = "INSERT INTO empresa (
                idEmpresa, nombre, correo, telefono, direccion, contrasena, n_doc,
                tipo_documento_id_tipo, ciudad_id_ciudad, descripcion, sector_id_sector,
                sitio_web, numero_empleados, ano_fundacion, contacto_nombres,
                contacto_apellidos, contacto_cargo, estado_id_estado
              ) VALUES (
                '$idEmpresa', '$nombre', '$correo', '$telefono', '$direccion', '$contrasena', '$n_doc',
                $tipo_documento_id_tipo, " . ($ciudad_id_ciudad ? "$ciudad_id_ciudad" : "NULL") . ", " . ($descripcion ? "'$descripcion'" : "NULL") . ", " . ($sector_id_sector ? "$sector_id_sector" : "NULL") . ",
                " . ($sitio_web ? "'$sitio_web'" : "NULL") . ", " . ($numero_empleados ? "$numero_empleados" : "NULL") . ", " . ($ano_fundacion ? "$ano_fundacion" : "NULL") . ",
                " . ($contacto_nombres ? "'$contacto_nombres'" : "NULL") . ", " . ($contacto_apellidos ? "'$contacto_apellidos'" : "NULL") . ", " . ($contacto_cargo ? "'$contacto_cargo'" : "NULL") . ", $estado_id_estado
              )";


      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Empresa registrada correctamente.'];
      } else {
        error_log("ERROR DB (registrar empresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al registrar: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (registrar empresa): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * Verifica si una empresa existe por su ID.
   * @param int $idEmpresa ID de la empresa.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeEmpresa($idEmpresa)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeEmpresa.");
      return false;
    }
    $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);
    $sql = "SELECT COUNT(*) FROM empresa WHERE idEmpresa='$idEmpresa'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeEmpresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Verifica si una empresa existe por su correo.
   * @param string $correo Correo de la empresa.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeCorreo($correo)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeCorreo.");
      return false;
    }
    $correo = mysqli_real_escape_string($this->conexion, $correo);
    $sql = "SELECT COUNT(*) FROM empresa WHERE correo='$correo'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeCorreo): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Verifica si una empresa existe por su número de documento.
   * @param string $n_doc Número de documento de la empresa.
   * @return bool True si existe, false en caso contrario.
   */
  public function existeNdoc($n_doc)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en existeNdoc.");
      return false;
    }
    $n_doc = mysqli_real_escape_string($this->conexion, $n_doc);
    $sql = "SELECT COUNT(*) FROM empresa WHERE n_doc='$n_doc'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (existeNdoc): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }
    $fila = mysqli_fetch_row($resultado);
    return $fila[0] > 0;
  }

  /**
   * Obtiene los datos de una empresa por su ID.
   * @param int $idEmpresa ID de la empresa.
   * @return array|false Datos de la empresa o false si no se encuentra.
   */
  public function obtenerPorId($idEmpresa)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerPorId (Empresa).");
      return false;
    }

    $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);

    $sql = "SELECT e.*,
                   COALESCE(td.nombre, 'No especificado') AS tipo_documento_nombre,
                   COALESCE(c.nombre, 'No especificado') AS ciudad_nombre,
                   COALESCE(s.nombre, 'No especificado') AS sector_nombre,
                   COALESCE(est.nombre, 'No especificado') AS estado_nombre
            FROM empresa e
            LEFT JOIN tipo_documento td ON e.tipo_documento_id_tipo = td.id_tipo
            LEFT JOIN ciudad c ON e.ciudad_id_ciudad = c.id_ciudad
            LEFT JOIN sector_empresarial s ON e.sector_id_sector = s.id_sector
            LEFT JOIN estado est ON e.estado_id_estado = est.id_estado
            WHERE e.idEmpresa = '$idEmpresa'
            LIMIT 1";

    error_log("DEBUG (Empresa::obtenerPorId): Ejecutando consulta para ID: " . $idEmpresa);
    error_log("DEBUG (Empresa::obtenerPorId): SQL: " . $sql);

    $resultado = mysqli_query($this->conexion, $sql);

    if (!$resultado) {
      error_log("ERROR DB (obtenerPorId Empresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }

    $datos = mysqli_fetch_assoc($resultado);

    if ($datos) {
      error_log("DEBUG (Empresa::obtenerPorId): Empresa encontrada: " . $datos['nombre']);
      return $datos;
    } else {
      error_log("DEBUG (Empresa::obtenerPorId): No se encontró empresa con ID: " . $idEmpresa);
      return false;
    }
  }
  /**
   * Valida las credenciales de inicio de sesión para una empresa.
   * @param string $idEmpresa ID de la empresa.
   * @param string $contrasena Contraseña de la empresa.
   * @return array|false Datos de la empresa si las credenciales son válidas, false en caso contrario.
   */
  public function validarCredenciales($idEmpresa, $contrasena)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en validarCredenciales (Empresa).");
      return false;
    }
    $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);
    $contrasena = mysqli_real_escape_string($this->conexion, $contrasena); // Asume que la contraseña no está hasheada para la comparación

    $sql = "SELECT e.*, td.nombre as tipo_documento_nombre
                FROM empresa e
                LEFT JOIN tipo_documento td ON e.tipo_documento_id_tipo = td.id_tipo
                WHERE e.idEmpresa='$idEmpresa' AND e.contrasena='$contrasena'";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (validarCredenciales Empresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return false;
    }

    if (mysqli_num_rows($resultado) == 1) {
      return mysqli_fetch_assoc($resultado);
    }
    return false;
  }

  /**
   * Obtiene una lista paginada y filtrada de todas las empresas (activas e inactivos).
   * Este método ahora se encarga de listar todas las empresas para el administrador.
   * @param string $busqueda Término de búsqueda.
   * @param int $limite Número máximo de resultados a devolver.
   * @param int $offset Número de resultados a omitir.
   * @return array Lista de empresas.
   */
  public function obtenerTodos($busqueda = '', $limite = 50, $offset = 0)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTodos (Empresa).");
      return [];
    }
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $where = ""; // Se eliminó el filtro de estado para que el admin vea todos
    if (!empty($busqueda)) {
      $where = "WHERE (e.nombre LIKE '%$busqueda%'
                OR e.correo LIKE '%$busqueda%'
                OR e.telefono LIKE '%$busqueda%'
                OR e.idEmpresa LIKE '%$busqueda%'
                OR e.n_doc LIKE '%$busqueda%')";
    }

    $sql = "SELECT e.*, td.nombre as tipo_documento_nombre, est.nombre AS estado_nombre
            FROM empresa e
            LEFT JOIN tipo_documento td ON e.tipo_documento_id_tipo = td.id_tipo
            LEFT JOIN estado est ON e.estado_id_estado = est.id_estado
            $where
            ORDER BY e.nombre
            LIMIT $limite OFFSET $offset";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerTodos Empresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $empresas = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
      $empresas[] = $fila;
    }

    return $empresas;
  }

  /**
   * Cuenta el total de empresas (activas e inactivos), opcionalmente filtradas por un término de búsqueda.
   * @param string $busqueda Término de búsqueda.
   * @return int Número total de empresas.
   */
  public function contarTotal($busqueda = '')
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en contarTotal (Empresa).");
      return 0;
    }
    $busqueda = mysqli_real_escape_string($this->conexion, $busqueda);

    $where = ""; // Se eliminó el filtro de estado para que el admin vea todos
    if (!empty($busqueda)) {
      $where = "WHERE (nombre LIKE '%$busqueda%'
                OR correo LIKE '%$busqueda%'
                OR telefono LIKE '%$busqueda%'
                OR idEmpresa LIKE '%$busqueda%'
                OR n_doc LIKE '%$busqueda%')";
    }

    $sql = "SELECT COUNT(*) as total FROM empresa $where";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (contarTotal Empresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return 0;
    }
    $fila = mysqli_fetch_assoc($resultado);

    return $fila['total'];
  }


  /**
   * Actualiza los datos de una empresa existente.
   * @param int $idEmpresa ID de la empresa a actualizar.
   * @param array $datos Array asociativo con los datos a actualizar.
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function actualizar($idEmpresa, $datos)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al actualizar empresa.");
      }
      $updates = [];
      $campos_permitidos = [
        'nombre',
        'correo',
        'telefono',
        'direccion',
        'n_doc',
        'tipo_documento_id_tipo',
        'ciudad_id_ciudad',
        'descripcion',
        'sector_id_sector',
        'sitio_web',
        'numero_empleados',
        'ano_fundacion',
        'contacto_nombres',
        'contacto_apellidos',
        'contacto_cargo',
        'estado_id_estado' // Ahora se permite actualizar el estado
      ];

      foreach ($campos_permitidos as $campo) {
        if (array_key_exists($campo, $datos)) {
          $valor = $datos[$campo];

          if ($campo === 'correo' && !empty($valor) && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido.");
          }

          if (in_array($campo, ['tipo_documento_id_tipo', 'ciudad_id_ciudad', 'sector_id_sector', 'numero_empleados', 'ano_fundacion', 'estado_id_estado'])) {
            $updates[] = "$campo = " . (empty($valor) && $valor !== 0 ? 'NULL' : (int) $valor);
          } else {
            $updates[] = "$campo = " . (empty($valor) ? 'NULL' : "'" . mysqli_real_escape_string($this->conexion, $valor) . "'");
          }
        }
      }

      if (empty($updates)) {
        throw new Exception("No hay datos para actualizar.");
      }

      $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);
      $sql = "UPDATE empresa SET " . implode(', ', $updates) . " WHERE idEmpresa='$idEmpresa'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Empresa actualizada correctamente.'];
      } else {
        error_log("ERROR DB (actualizar empresa): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al actualizar: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR (actualizar empresa): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  /**
   * "Elimina" una empresa cambiando su estado a inactivo.
   * @param string $idEmpresa ID de la empresa a "eliminar".
   * @return array Resultado de la operación (éxito/error, mensaje).
   */
  public function eliminar($idEmpresa)
  {
    try {
      if (!$this->conexion) {
        throw new Exception("Conexión a la base de datos no establecida al 'eliminar' empresa.");
      }
      $idEmpresa = mysqli_real_escape_string($this->conexion, $idEmpresa);

      if (!$this->existeEmpresa($idEmpresa)) {
        throw new Exception("La empresa no existe.");
      }

      // Asumiendo que el ID 2 en tu tabla 'estado' significa 'Inactivo'
      $estado_inactivo_id = 2;
      $sql = "UPDATE empresa SET estado_id_estado = $estado_inactivo_id WHERE idEmpresa = '$idEmpresa'";

      if (mysqli_query($this->conexion, $sql)) {
        return ['success' => true, 'message' => 'Empresa marcada como inactiva correctamente.'];
      } else {
        error_log("ERROR DB ('eliminar' empresa - cambiar estado): " . mysqli_error($this->conexion) . " SQL: " . $sql);
        throw new Exception("Error al cambiar el estado de la empresa a inactiva: " . mysqli_error($this->conexion));
      }

    } catch (Exception $e) {
      error_log("ERROR ('eliminar' empresa): " . $e->getMessage() . " en línea " . $e->getLine());
      return ['success' => false, 'message' => $e->getMessage()];
    }
  }

  public function obtenerTiposDocumento()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerTiposDocumento.");
      return [];
    }
    $sql = "SELECT * FROM tipo_documento ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerTiposDocumento: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $tipos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $tipos[] = $fila;
    }
    return $tipos;
  }

  public function obtenerCiudades()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerCiudades.");
      return [];
    }
    $sql = "SELECT * FROM ciudad ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerCiudades: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $ciudades = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $ciudades[] = $fila;
    }
    return $ciudades;
  }

  public function obtenerSectores()
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerSectores.");
      return [];
    }
    $sql = "SELECT * FROM sector_empresarial ORDER BY nombre";
    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB: Fallo en obtenerSectores: " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $sectores = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $sectores[] = $fila;
    }
    return $sectores;
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

  /**
   * Obtiene el ID de un estado a partir de su nombre.
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
   * Obtiene empresas filtradas por estado.
   *
   * @param int|null $idEstado ID del estado para filtrar, o null para todos los estados.
   * @return array Un array de arrays asociativos con los datos de las empresas.
   */
  public function obtenerEmpresasPorEstado($idEstado = null)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEmpresasPorEstado.");
      return [];
    }
    $sql = "SELECT
                em.idEmpresa,
                em.nombre,
                em.correo,
                em.telefono,
                em.direccion,
                td.nombre AS tipo_documento_nombre,
                em.n_doc,
                est.nombre AS estado_nombre,
                em.fecha_creacion
            FROM
                empresa em
            LEFT JOIN
                tipo_documento td ON em.tipo_documento_id_tipo = td.id_tipo
            LEFT JOIN
                estado est ON em.estado_id_estado = est.id_estado";
    if (!empty($idEstado)) {
      $idEstado = (int) mysqli_real_escape_string($this->conexion, $idEstado);
      $sql .= " WHERE em.estado_id_estado = $idEstado";
    }
    $sql .= " ORDER BY est.nombre, em.nombre";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerEmpresasPorEstado): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $empresas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $empresas[] = $fila;
    }
    return $empresas;
  }
  /**
   * Obtiene el top N de empresas con más ofertas publicadas.
   *
   * @param int $limite El número máximo de empresas a devolver.
   * @return array Un array de arrays asociativos con los datos de las empresas.
   */
  public function obtenerEmpresasConMasOfertas($limite = 5)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEmpresasConMasOfertas.");
      return [];
    }
    $limite = (int) $limite;
    $sql = "SELECT
                e.idEmpresa,
                e.nombre,
                COUNT(o.idOferta) AS total_ofertas
            FROM
                empresa e
            LEFT JOIN
                oferta o ON e.idEmpresa = o.empresa_idEmpresa
            GROUP BY
                e.idEmpresa, e.nombre
            ORDER BY
                total_ofertas DESC
            LIMIT $limite";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerEmpresasConMasOfertas): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $empresas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $empresas[] = $fila;
    }
    return $empresas;
  }

  /**
   * Obtiene el top N de empresas con más referencias emitidas.
   *
   * @param int $limite El número máximo de empresas a devolver.
   * @return array Un array de arrays asociativos con los datos de las empresas.
   */
  public function obtenerEmpresasConMasReferenciasEmitidas($limite = 5)
  {
    if (!$this->conexion) {
      error_log("ERROR: Conexión a la base de datos no establecida en obtenerEmpresasConMasReferenciasEmitidas.");
      return [];
    }
    $limite = (int) $limite;
    $sql = "SELECT
                e.idEmpresa,
                e.nombre,
                COUNT(r.idReferencia) AS total_referencias
            FROM
                empresa e
            LEFT JOIN
                referencia r ON e.idEmpresa = r.empresa_idEmpresa
            GROUP BY
                e.idEmpresa, e.nombre
            ORDER BY
                total_referencias DESC
            LIMIT $limite";

    $resultado = mysqli_query($this->conexion, $sql);
    if (!$resultado) {
      error_log("ERROR DB (obtenerEmpresasConMasReferenciasEmitidas): " . mysqli_error($this->conexion) . " SQL: " . $sql);
      return [];
    }
    $empresas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
      $empresas[] = $fila;
    }
    return $empresas;
  }
}