<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
  require_once './class/class_referencia.php';

} catch (Throwable $e) {
  error_log("FATAL ERROR in ajax_Greferencias.php initial setup: " . $e->getMessage() . " on line " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}

// Verificar autenticación y rol de administrador
$inn = 500;
if (isset($_SESSION['timeout'])) {
  $_session_life = time() - $_SESSION['timeout'];
  if ($_session_life > $inn) {
    session_destroy();
    echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => 'index.php']);
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
  error_log("DEBUG AUTH FAIL (ajax_Greferencias): usuario_id is set? " . (isset($_SESSION['usuario_id']) ? 'Yes' : 'No') . " | rol is administrador? " . (($_SESSION['rol'] ?? 'none') === 'administrador' ? 'Yes' : 'No'));
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Administrador en el Sistema.', 'redirect' => 'index.php']);
  exit();
}

$referenciaObj = new Referencia();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? null;

switch ($action) {
  case 'listar':
    $busqueda = $_GET['busqueda'] ?? '';
    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);
    $tipo_referencia_id = $_GET['tipo_referencia_id'] ?? null; // Nuevo parámetro para el filtro

    // Si tipo_referencia_id está vacío, se pasa null a la función obtenerTodas para que no filtre por tipo
    $filtro_tipo = empty($tipo_referencia_id) ? null : (int) $tipo_referencia_id;

    $referencias = $referenciaObj->obtenerTodas(null, null, $filtro_tipo, $busqueda, $limit, $offset);
    $total_referencias = $referenciaObj->contarTodasReferencias($busqueda, $filtro_tipo);

    ob_start();
    if (empty($referencias)) {
      ?>
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="text-muted">
            <div class="display-1">⭐</div>
            <h5><?php echo empty($busqueda) ? 'No hay referencias registradas' : 'No se encontraron resultados'; ?></h5>
            <p class="mb-0">
              <?php echo empty($busqueda) ? 'Aún no se han registrado referencias en el sistema' : 'Intenta con otros términos de búsqueda'; ?>
            </p>
          </div>
        </td>
      </tr>
      <?php
    } else {
      foreach ($referencias as $referencia) {
        // Asegúrate de que los nombres de empresa y estudiante existan o se muestre 'N/A'
        $empresa_nombre = htmlspecialchars($referencia['empresa_nombre'] ?? 'N/A');
        $estudiante_nombre_completo = htmlspecialchars(($referencia['estudiante_nombre'] ?? '') . ' ' . ($referencia['estudiante_apellidos'] ?? ''));
        if (trim($estudiante_nombre_completo) === '') {
          $estudiante_nombre_completo = 'N/A';
        }
        ?>
        <tr>
          <td><span class="badge bg-info"><?php echo htmlspecialchars($referencia['idReferencia']); ?></span></td>
          <td>
            <?php echo htmlspecialchars(substr($referencia['comentario'], 0, 70)) . (strlen($referencia['comentario']) > 70 ? '...' : ''); ?>
          </td>
          <td><?php echo htmlspecialchars($referencia['puntuacion'] ?? 'N/A'); ?></td>
          <td><?php echo htmlspecialchars($referencia['tipo_referencia_nombre'] ?? 'N/A'); ?></td>
          <td><?php echo $estudiante_nombre_completo; ?></td>
          <td><?php echo $empresa_nombre; ?></td>
          <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($referencia['fecha_creacion']))); ?></td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-outline-primary"
                onclick="verDetalleReferencia(<?php echo $referencia['idReferencia']; ?>)" title="Ver detalles">
                👁️
              </button>
              <button class="btn btn-sm btn-outline-warning"
                onclick="editarReferencia(<?php echo $referencia['idReferencia']; ?>)" title="Editar">
                ✏️
              </button>
              <button class="btn btn-sm btn-outline-danger"
                onclick="eliminarReferencia(<?php echo $referencia['idReferencia']; ?>)" title="Eliminar">
                🗑️
              </button>
            </div>
          </td>
        </tr>
        <?php
      }
    }
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html, 'total' => $total_referencias]);
    break;

  case 'obtener':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado.']);
      exit();
    }
    $referencia = $referenciaObj->obtenerPorId($id);
    if ($referencia) {
      echo json_encode(['success' => true, 'referencia' => $referencia]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Referencia no encontrada.']);
    }
    break;

  case 'detalle_html': // Generar HTML para el modal de detalle
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para el detalle.']);
      exit();
    }
    $referencia = $referenciaObj->obtenerPorId($id);
    if (!$referencia) {
      echo json_encode(['success' => false, 'message' => 'Referencia no encontrada para el detalle.']);
      exit();
    }

    ob_start();
    ?>
    <div class="row">
      <div class="col-md-12">
        <table class="table table-borderless table-sm">
          <tbody>
            <tr>
              <th scope="row" class="fw-bold">ID Referencia:</th>
              <td><?php echo htmlspecialchars($referencia['idReferencia']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Tipo de Referencia:</th>
              <td><?php echo htmlspecialchars($referencia['tipo_referencia_nombre'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Estudiante:</th>
              <td>
                <?php echo htmlspecialchars(($referencia['estudiante_nombre'] ?? 'N/A') . ' ' . ($referencia['estudiante_apellidos'] ?? '')); ?>
              </td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Empresa:</th>
              <td><?php echo htmlspecialchars($referencia['empresa_nombre'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Puntuación:</th>
              <td>
                <?php if (isset($referencia['puntuacion']) && $referencia['puntuacion'] !== null): ?>
                  <span class="badge bg-warning text-dark"><i class="fas fa-star"></i>
                    <?php echo htmlspecialchars($referencia['puntuacion']); ?></span>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Estado:</th>
              <td>
                <?php
                $estado_nombre = htmlspecialchars($referencia['estado_nombre'] ?? 'N/A');
                $badge_class = 'bg-secondary';
                if ($estado_nombre == 'activo')
                  $badge_class = 'bg-success';
                if ($estado_nombre == 'inactivo')
                  $badge_class = 'bg-danger';
                ?>
                <span class="badge <?php echo $badge_class; ?>"><?php echo $estado_nombre; ?></span>
              </td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Comentario:</th>
              <td><?php echo nl2br(htmlspecialchars($referencia['comentario'])); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Fecha Creación:</th>
              <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($referencia['fecha_creacion']))); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Última Actualización:</th>
              <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($referencia['fecha_actualizacion']))); ?></td>
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
      echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para actualizar.']);
      exit();
    }
    $datos = [
      'comentario' => $_POST['comentario'] ?? '',
      'puntuacion' => $_POST['puntuacion'] ?? null,
      'tipo_referencia_id_tipo_referencia' => $_POST['tipo_referencia_id_tipo_referencia'] ?? '',
      'estado_id_estado' => $_POST['estado_id_estado'] ?? '',
    ];

    $resultado = $referenciaObj->actualizar($id, $datos);
    echo json_encode($resultado);
    break;

  case 'eliminar': // Cambia el estado a 'inactiva' en lugar de eliminar
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para desactivar.']);
      exit();
    }

    // Obtener el ID del estado 'inactiva'
    $estados = $referenciaObj->obtenerEstados();
    $inactiva_id = null;
    foreach ($estados as $estado) {
      if (strtolower($estado['nombre']) === 'inactiva' || strtolower($estado['nombre']) === 'inactivo') { // Manejar 'inactiva' o 'inactivo'
        $inactiva_id = $estado['id_estado'];
        break;
      }
    }

    if ($inactiva_id === null) {
      echo json_encode(['success' => false, 'message' => 'No se encontró el estado "inactiva" en la base de datos.']);
      exit();
    }

    $datos_actualizar = [
      'estado_id_estado' => $inactiva_id
    ];

    $resultado = $referenciaObj->actualizar($id, $datos_actualizar);
    if ($resultado['success']) {
      $resultado['message'] = 'Referencia desactivada correctamente.';
    } else {
      $resultado['message'] = 'Error al desactivar la referencia: ' . $resultado['message'];
    }
    echo json_encode($resultado);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}
?>