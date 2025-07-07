<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
  require_once '../MODELO/class_administrador.php';
} catch (Throwable $e) {
  error_log("FATAL ERROR in ajax_Gadmin.php initial setup: " . $e->getMessage() . " on line " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}

// Verificar autenticación y rol de administrador
$inn = 500;
if (isset($_SESSION['timeout'])) {
  $_session_life = time() - $_SESSION['timeout'];
  if ($_session_life > $inn) {
    session_destroy();
    echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
  error_log("DEBUG AUTH FAIL (ajax_Gadmin): usuario_id is set? " . (isset($_SESSION['usuario_id']) ? 'Yes' : 'No') . " | rol is administrador? " . (($_SESSION['rol'] ?? 'none') === 'administrador' ? 'Yes' : 'No'));
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Administrador en el Sistema.', 'redirect' => '../index.php']);
  exit();
}

$administradorObj = new Administrador();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? null;

switch ($action) {
  case 'listar':
    $busqueda = $_GET['busqueda'] ?? '';
    // Por defecto, solo listar administradores activos, a menos que la búsqueda se realice
    // Si hay búsqueda, se incluyen inactivos para permitir encontrarlos.
    $incluirInactivos = !empty($busqueda);
    $administradores = $administradorObj->listarTodos($busqueda, $incluirInactivos);

    ob_start();
    if (empty($administradores)) {
      ?>
      <tr>
        <td colspan="9" class="text-center py-4">
          <div class="text-muted">
            <div class="display-1">⚙️</div>
            <h5>
              <?php echo empty($busqueda) ? 'No hay administradores activos registrados' : 'No se encontraron resultados'; ?>
            </h5>
            <p class="mb-0">
              <?php echo empty($busqueda) ? 'Aún no se han registrado administradores activos en el sistema' : 'Intenta con otros términos de búsqueda'; ?>
            </p>
          </div>
        </td>
      </tr>
      <?php
    } else {
      foreach ($administradores as $admin) {
        ?>
        <tr>
          <td><span class="badge bg-danger"><?php echo htmlspecialchars($admin['idAdministrador']); ?></span></td>
          <td><?php echo htmlspecialchars($admin['nombres']); ?></td>
          <td><?php echo htmlspecialchars($admin['apellidos']); ?></td>
          <td><?php echo htmlspecialchars($admin['correo']); ?></td>
          <td><?php echo htmlspecialchars($admin['telefono'] ?? 'N/A'); ?></td>
          <td><?php echo htmlspecialchars($admin['n_doc']); ?></td>
          <td><?php echo htmlspecialchars($admin['tipo_documento_nombre'] ?? 'N/A'); ?></td>
          <td><span class="badge bg-<?php echo ($admin['estado_nombre'] == 'activo') ? 'success' : 'danger'; ?>">
              <?php echo htmlspecialchars($admin['estado_nombre']); ?>
            </span></td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-outline-danger"
                onclick="verDetalleAdministrador(<?php echo $admin['idAdministrador']; ?>)" title="Ver detalles">
                👁️
              </button>
              <button class="btn btn-sm btn-outline-warning"
                onclick="editarAdministrador(<?php echo $admin['idAdministrador']; ?>)" title="Editar">
                ✏️
              </button>
              <button class="btn btn-sm btn-outline-danger"
                onclick="desactivarAdministrador(<?php echo $admin['idAdministrador']; ?>)" title="Desactivar">
                🗑️
              </button>
            </div>
          </td>
        </tr>
        <?php
      }
    }
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html, 'total' => count($administradores)]);
    break;

  case 'obtener':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de administrador no proporcionado.']);
      exit();
    }
    $admin = $administradorObj->obtenerPorId($id);
    if ($admin) {
      echo json_encode(['success' => true, 'administrador' => $admin]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Administrador no encontrado.']);
    }
    break;

  case 'detalle_html': // Generar HTML para el modal de detalle
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de administrador no proporcionado para el detalle.']);
      exit();
    }
    $admin = $administradorObj->obtenerPorId($id);
    if (!$admin) {
      echo json_encode(['success' => false, 'message' => 'Administrador no encontrado para el detalle.']);
      exit();
    }

    ob_start();
    ?>
    <div class="row">
      <div class="col-md-12">
        <h5 class="mb-3 text-danger"><i class="fas fa-user-circle me-2"></i> Información del Administrador</h5>
        <table class="table table-borderless table-sm">
          <tbody>
            <tr>
              <th scope="row" class="fw-bold">ID Administrador:</th>
              <td><?php echo htmlspecialchars($admin['idAdministrador']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Nombres:</th>
              <td><?php echo htmlspecialchars($admin['nombres']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Apellidos:</th>
              <td><?php echo htmlspecialchars($admin['apellidos']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Correo:</th>
              <td><?php echo htmlspecialchars($admin['correo']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Teléfono:</th>
              <td><?php echo htmlspecialchars($admin['telefono'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Tipo de Documento:</th>
              <td><?php echo htmlspecialchars($admin['tipo_documento_nombre'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Número de Documento:</th>
              <td><?php echo htmlspecialchars($admin['n_doc']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Ciudad:</th>
              <td><?php echo htmlspecialchars($admin['ciudad_nombre'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Estado:</th>
              <td>
                <span class="badge bg-<?php echo ($admin['estado_nombre'] == 'activo') ? 'success' : 'danger'; ?>">
                  <?php echo htmlspecialchars($admin['estado_nombre']); ?>
                </span>
              </td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Fecha Creación:</th>
              <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($admin['fecha_creacion']))); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Última Actualización:</th>
              <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($admin['fecha_actualizacion']))); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html]);
    break;


  case 'actualizar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de administrador no proporcionado para actualizar.']);
      exit();
    }
    $datos = [
      'nombres' => $_POST['nombres'] ?? '',
      'apellidos' => $_POST['apellidos'] ?? '',
      'correo' => $_POST['correo'] ?? '',
      'telefono' => $_POST['telefono'] ?? null,
      'n_doc' => $_POST['n_doc'] ?? '',
      'tipo_documento_id_tipo' => $_POST['tipo_documento_id_tipo'] ?? '',
      'ciudad_id_ciudad' => $_POST['ciudad_id_ciudad'] ?? null,
      'estado_id_estado' => $_POST['estado_id_estado'] ?? null
    ];

    $resultado = $administradorObj->actualizar($id, $datos);
    echo json_encode($resultado);
    break;

  case 'desactivar': // Cambiar el nombre de 'eliminar' a 'desactivar' para mayor claridad
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de administrador no proporcionado para desactivar.']);
      exit();
    }
    $resultado = $administradorObj->desactivar($id); // Llama al nuevo método de desactivación
    echo json_encode($resultado);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}
?>