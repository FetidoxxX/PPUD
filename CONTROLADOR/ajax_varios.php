<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
  require_once '../MODELO/class_catalogo.php';
  require_once '../MODELO/class_administrador.php'; // Para la validación de estado del administrador
} catch (Throwable $e) {
  error_log("ERROR CRÍTICO en ajax_varios.php al cargar módulos: " . $e->getMessage() . " en línea " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}

// Verificar autenticación y rol de administrador
$tiempoInactividad = 500; // Segundos
if (isset($_SESSION['timeout'])) {
  $_vidaSesion = time() - $_SESSION['timeout'];
  if ($_vidaSesion > $tiempoInactividad) {
    session_destroy();
    echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
  error_log("FALLO DE AUTENTICACIÓN (ajax_varios): usuario_id establecido? " . (isset($_SESSION['usuario_id']) ? 'Sí' : 'No') . " | rol es administrador? " . (($_SESSION['rol'] ?? 'ninguno') === 'administrador' ? 'Sí' : 'No'));
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Administrador en el Sistema.', 'redirect' => '../index.php']);
  exit();
}

// Validación de estado del administrador
$administradorObj = new Administrador();
$idAdmin = $_SESSION['usuario_id'];
$datosAdmin = $administradorObj->obtenerPorId($idAdmin);
$idInactivo = $administradorObj->getIdEstadoPorNombre('inactivo');

if ($datosAdmin && $idInactivo !== false && $datosAdmin['estado_id_estado'] == $idInactivo) {
  session_destroy();
  echo json_encode(['success' => false, 'message' => 'Su cuenta ha sido desactivada. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
  exit();
}


$catalogoObj = new Catalogo();

$accion = $_POST['action'] ?? $_GET['action'] ?? '';
$nombreTabla = $_POST['nombreTabla'] ?? $_GET['nombreTabla'] ?? '';
$id = $_POST['id'] ?? $_GET['id'] ?? null;
// La descripción y el nombre ahora se obtienen dinámicamente del array $datos

// Lista de tablas permitidas para gestión (actualizada para incluir todas las tablas que se pueden gestionar)
$tablasPermitidas = ['estado', 'tipo_documento', 'modalidad', 'tipo_oferta', 'tipo_referencia', 'area_conocimiento', 'carrera', 'ciudad', 'disponibilidad_horaria', 'sector_empresarial', 'administrador', 'empresa', 'estudiante', 'oferta', 'referencia'];


if (!in_array($nombreTabla, $tablasPermitidas)) {
  echo json_encode(['success' => false, 'message' => 'Tabla no válida.']);
  exit();
}

// Recolectar todos los datos del POST para operaciones de crear/actualizar
$datos = [];
foreach ($_POST as $key => $value) {
  if (!in_array($key, ['action', 'nombreTabla', 'id'])) { // Excluir parámetros de control
    $datos[$key] = $value;
  }
}


switch ($accion) {
  case 'listar':
    $busqueda = $_GET['busqueda'] ?? '';
    $elementos = $catalogoObj->listarTodos($nombreTabla, $busqueda); // Esto ya selecciona todas las columnas
    $columnasTablaInfo = $catalogoObj->obtenerColumnasTabla($nombreTabla); // Obtener información del esquema

    // No se genera thead aquí, se pasa la información al frontend para que lo genere
    // Se pasa el esquema de la tabla para que JavaScript pueda construir el thead y tbody dinámicamente
    echo json_encode(['success' => true, 'elementos' => $elementos, 'total' => count($elementos), 'schema' => $columnasTablaInfo]);
    break;

  case 'obtener':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de elemento no proporcionado.']);
      exit();
    }
    $elemento = $catalogoObj->obtenerPorId($nombreTabla, $id);
    if ($elemento) {
      echo json_encode(['success' => true, 'item' => $elemento]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Elemento no encontrado.']);
    }
    break;

  case 'detalle_html': // Generar HTML para el modal de detalle
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de elemento no proporcionado para el detalle.']);
      exit();
    }
    $elemento = $catalogoObj->obtenerPorId($nombreTabla, $id);
    if (!$elemento) {
      echo json_encode(['success' => false, 'message' => 'Elemento no encontrado para el detalle.']);
      exit();
    }

    ob_start();
    $nombreColumnaId = $catalogoObj->obtenerNombreColumnaId($nombreTabla);
    ?>
    <div class="row">
      <div class="col-md-12">
        <h5 class="mb-3 text-dark"><i class="fas fa-info-circle me-2"></i> Información del Elemento</h5>
        <table class="table table-borderless table-sm">
          <tbody>
            <?php
            foreach ($elemento as $key => $value) {
              // Omitir la columna ID si ya se muestra o si es la clave primaria manejada por separado
              if ($key === $nombreColumnaId) {
                continue;
              }
              // Formatear fechas si parecen ser campos de fecha/hora
              if (strpos($key, 'fecha_') === 0 || $key === 'codigo_expira_en' || strpos($key, '_at') !== false) { // Heurística para campos de fecha
                if ($value && $value !== '0000-00-00 00:00:00') { // Evitar fechas nulas o cero
                  $value = htmlspecialchars(date('d/m/Y H:i:s', strtotime($value)));
                } else {
                  $value = 'N/A';
                }
              } else {
                $value = htmlspecialchars($value);
              }
              // Capitalizar y reemplazar guiones bajos para la visualización
              $displayKey = ucwords(str_replace('_', ' ', $key));
              ?>
              <tr>
                <th scope="row" class="fw-bold"><?php echo $displayKey; ?>:</th>
                <td><?php echo $value; ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html]);
    break;

  case 'crear':
    $resultado = $catalogoObj->registrar($nombreTabla, $datos);
    echo json_encode($resultado);
    break;

  case 'actualizar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de elemento no proporcionado para actualizar.']);
      exit();
    }
    $resultado = $catalogoObj->actualizar($nombreTabla, $id, $datos);
    echo json_encode($resultado);
    break;

  case 'eliminar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de elemento no proporcionado para eliminar.']);
      exit();
    }
    $resultado = $catalogoObj->eliminar($nombreTabla, $id);
    echo json_encode($resultado);
    break;

  case 'get_table_schema': // Nuevo caso para obtener el esquema de la tabla
    $columnas = $catalogoObj->obtenerColumnasTabla($nombreTabla);
    echo json_encode(['success' => true, 'schema' => $columnas]);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}