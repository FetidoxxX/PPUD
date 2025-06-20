<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
  require_once './class/class_oferta.php';
  require_once './class/class_empresa.php'; // Necesario para obtener el nombre de la empresa si es necesario
} catch (Throwable $e) {
  error_log("FATAL ERROR in ajax_Gofertas.php initial setup: " . $e->getMessage() . " on line " . $e->getLine());
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
  error_log("DEBUG AUTH FAIL (ajax_Gofertas): usuario_id is set? " . (isset($_SESSION['usuario_id']) ? 'Yes' : 'No') . " | rol is administrador? " . (($_SESSION['rol'] ?? 'none') === 'administrador' ? 'Yes' : 'No'));
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Administrador en el Sistema.', 'redirect' => 'index.php']);
  exit();
}

$ofertaObj = new Oferta();
$empresaObj = new Empresa(); // Instancia de Empresa para obtener nombres

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? null;

switch ($action) {
  case 'listar':
    $busqueda = $_GET['busqueda'] ?? '';
    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);

    // Obtener solo ofertas activas para la vista de administración
    $ofertas = $ofertaObj->obtenerOfertasActivas($busqueda, $limit, $offset);
    $total_ofertas = $ofertaObj->contarOfertasActivas($busqueda);

    ob_start();
    if (empty($ofertas)) {
      ?>
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="text-muted">
            <div class="display-1">💼</div>
            <h5><?php echo empty($busqueda) ? 'No hay ofertas activas registradas' : 'No se encontraron resultados'; ?>
            </h5>
            <p class="mb-0">
              <?php echo empty($busqueda) ? 'Aún no se han registrado ofertas activas en el sistema' : 'Intenta con otros términos de búsqueda'; ?>
            </p>
          </div>
        </td>
      </tr>
      <?php
    } else {
      foreach ($ofertas as $oferta) {
        ?>
        <tr>
          <td><span class="badge bg-primary"><?php echo htmlspecialchars($oferta['idOferta']); ?></span></td>
          <td><?php echo htmlspecialchars($oferta['titulo']); ?></td>
          <td><?php echo htmlspecialchars($oferta['empresa_nombre']); ?></td>
          <td><?php echo htmlspecialchars($oferta['modalidad_nombre']); ?></td>
          <td><?php echo htmlspecialchars($oferta['tipo_oferta_nombre']); ?></td>
          <td><?php echo htmlspecialchars($oferta['fecha_vencimiento']); ?></td>
          <td><span
              class="badge bg-<?php echo ($oferta['estado_nombre'] == 'activo') ? 'success' : (($oferta['estado_nombre'] == 'vencida') ? 'danger' : 'secondary'); ?>">
              <?php echo htmlspecialchars($oferta['estado_nombre']); ?>
            </span></td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-outline-primary" onclick="verDetalleOferta(<?php echo $oferta['idOferta']; ?>)"
                title="Ver detalles">
                👁️
                <!-- Ícono de ojo -->
              </button>
              <button class="btn btn-sm btn-outline-warning" onclick="editarOferta(<?php echo $oferta['idOferta']; ?>)"
                title="Editar">
                ✏️
                <!-- Ícono de lápiz -->
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="desactivarOferta(<?php echo $oferta['idOferta']; ?>)"
                title="Desactivar">
                🗑️
              </button>
            </div>
          </td>
        </tr>
        <?php
      }
    }
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html, 'total' => $total_ofertas]);
    break;

  case 'obtener':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionado.']);
      exit();
    }
    $oferta = $ofertaObj->obtenerPorId($id);
    if ($oferta) {
      // Obtener carreras asociadas para el checkbox en el modal de edición
      $carreras_asociadas = $ofertaObj->obtenerCarrerasAsociadas($id);
      $oferta['carreras_dirigidas'] = $carreras_asociadas;

      // Opcional: Obtener los nombres de las carreras asociadas para mostrar en detalle
      $carreras_nombres = [];
      foreach ($carreras_asociadas as $id_carrera) {
        $carrera_data = $ofertaObj->obtenerCarreraPorId($id_carrera);
        if ($carrera_data) {
          $carreras_nombres[] = $carrera_data['nombre'];
        }
      }
      $oferta['carreras_dirigidas_nombres'] = $carreras_nombres;


      echo json_encode(['success' => true, 'oferta' => $oferta]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Oferta no encontrada.']);
    }
    break;

  case 'detalle_html': // Nueva acción para generar el HTML del detalle de la oferta
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionado para el detalle.']);
      exit();
    }
    $oferta = $ofertaObj->obtenerPorId($id);
    if (!$oferta) {
      echo json_encode(['success' => false, 'message' => 'Oferta no encontrada para el detalle.']);
      exit();
    }

    // Obtener carreras asociadas para mostrar en el detalle
    $carreras_asociadas_ids = $ofertaObj->obtenerCarrerasAsociadas($id);
    $carreras_asociadas_nombres = [];
    foreach ($carreras_asociadas_ids as $carrera_id) {
      $carrera_data = $ofertaObj->obtenerCarreraPorId($carrera_id);
      if ($carrera_data) {
        $carreras_asociadas_nombres[] = htmlspecialchars($carrera_data['nombre']);
      }
    }
    $carreras_dirigidas_str = !empty($carreras_asociadas_nombres) ? implode(', ', $carreras_asociadas_nombres) : '<span class="text-muted">No especificadas</span>';

    ob_start();
    ?>
    <div class="row">
      <div class="col-md-12">
        <h5 class="mb-3 text-primary"><i class="fas fa-info-circle me-2"></i> Información General</h5>
        <table class="table table-borderless table-sm">
          <tbody>
            <tr>
              <th scope="row" class="fw-bold">ID Oferta:</th>
              <td><?php echo htmlspecialchars($oferta['idOferta']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Título:</th>
              <td><?php echo htmlspecialchars($oferta['titulo']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Empresa:</th>
              <td><?php echo htmlspecialchars($oferta['empresa_nombre']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Estado:</th>
              <td><span
                  class="badge bg-<?php echo ($oferta['estado_nombre'] == 'activo') ? 'success' : (($oferta['estado_nombre'] == 'vencida') ? 'danger' : 'secondary'); ?>">
                  <?php echo htmlspecialchars($oferta['estado_nombre']); ?>
                </span></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Modalidad:</th>
              <td><?php echo htmlspecialchars($oferta['modalidad_nombre']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Tipo de Oferta:</th>
              <td><?php echo htmlspecialchars($oferta['tipo_oferta_nombre']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Área Conocimiento:</th>
              <td><?php echo htmlspecialchars($oferta['area_conocimiento_nombre']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Fecha Vencimiento:</th>
              <td><?php echo htmlspecialchars($oferta['fecha_vencimiento']); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-md-12">
        <h5 class="mb-3 text-primary"><i class="fas fa-file-alt me-2"></i> Descripción y Requisitos</h5>
        <table class="table table-borderless table-sm">
          <tbody>
            <tr>
              <th scope="row" class="fw-bold">Descripción:</th>
              <td><?php echo nl2br(htmlspecialchars($oferta['descripcion'])); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Requisitos:</th>
              <td><?php echo nl2br(htmlspecialchars($oferta['requisitos'])); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Beneficios:</th>
              <td><?php echo nl2br(htmlspecialchars($oferta['beneficios'] ?? 'N/A')); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Habilidades Requeridas:</th>
              <td><?php echo nl2br(htmlspecialchars($oferta['habilidades_requeridas'] ?? 'N/A')); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-md-12">
        <h5 class="mb-3 text-primary"><i class="fas fa-calendar-alt me-2"></i> Detalles de Tiempo y Remuneración</h5>
        <table class="table table-borderless table-sm">
          <tbody>
            <tr>
              <th scope="row" class="fw-bold">Duración (meses):</th>
              <td><?php echo htmlspecialchars($oferta['duracion_meses']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Horario:</th>
              <td><?php echo htmlspecialchars($oferta['horario'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Remuneración:</th>
              <td><?php echo htmlspecialchars($oferta['remuneracion'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Fecha de Inicio:</th>
              <td><?php echo htmlspecialchars($oferta['fecha_inicio'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Fecha de Fin:</th>
              <td><?php echo htmlspecialchars($oferta['fecha_fin'] ?? 'N/A'); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-md-12">
        <h5 class="mb-3 text-primary"><i class="fas fa-chart-line me-2"></i> Requisitos Académicos y Cupos</h5>
        <table class="table table-borderless table-sm">
          <tbody>
            <tr>
              <th scope="row" class="fw-bold">Semestre Mínimo:</th>
              <td><?php echo htmlspecialchars($oferta['semestre_minimo'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Promedio Mínimo:</th>
              <td><?php echo htmlspecialchars($oferta['promedio_minimo'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Cupos Disponibles:</th>
              <td><?php echo htmlspecialchars($oferta['cupos_disponibles']); ?></td>
            </tr>
            <tr>
              <th scope="row" class="fw-bold">Carreras Dirigidas:</th>
              <td><?php echo $carreras_dirigidas_str; ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html]);
    break;

  case 'crear':
    $datos = [
      'titulo' => $_POST['titulo'] ?? '',
      'descripcion' => $_POST['descripcion'] ?? '',
      'requisitos' => $_POST['requisitos'] ?? '',
      'beneficios' => $_POST['beneficios'] ?? null,
      'modalidad_id_modalidad' => $_POST['modalidad_id_modalidad'] ?? '',
      'tipo_oferta_id_tipo_oferta' => $_POST['tipo_oferta_id_tipo_oferta'] ?? '',
      'duracion_meses' => $_POST['duracion_meses'] ?? '',
      'horario' => $_POST['horario'] ?? null,
      'remuneracion' => $_POST['remuneracion'] ?? null,
      'area_conocimiento_id_area' => $_POST['area_conocimiento_id_area'] ?? '',
      'semestre_minimo' => $_POST['semestre_minimo'] ?? null,
      'promedio_minimo' => $_POST['promedio_minimo'] ?? null,
      'habilidades_requeridas' => $_POST['habilidades_requeridas'] ?? null,
      'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
      'fecha_fin' => $_POST['fecha_fin'] ?? null,
      'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
      'cupos_disponibles' => $_POST['cupos_disponibles'] ?? 1,
      'empresa_idEmpresa' => $_POST['empresa_idEmpresa'] ?? '', // El ID de la empresa debe venir del formulario
      // El estado por defecto se establecerá en la clase Oferta si no se proporciona
    ];

    $resultado = $ofertaObj->registrar($datos);

    // Si se registró con éxito y hay carreras dirigidas, asociarlas
    if ($resultado['success'] && isset($resultado['idOferta']) && isset($_POST['carreras_dirigidas'])) {
      $idOferta = $resultado['idOferta'];
      $carreras_dirigidas = json_decode($_POST['carreras_dirigidas'], true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $ofertaObj->asociarCarreras($idOferta, $carreras_dirigidas);
      } else {
        error_log("Error al decodificar JSON de carreras dirigidas: " . json_last_error_msg());
      }
    }
    echo json_encode($resultado);
    break;

  case 'actualizar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionado para actualizar.']);
      exit();
    }
    $datos = [
      'titulo' => $_POST['titulo'] ?? '',
      'descripcion' => $_POST['descripcion'] ?? '',
      'requisitos' => $_POST['requisitos'] ?? '',
      'beneficios' => $_POST['beneficios'] ?? null,
      'modalidad_id_modalidad' => $_POST['modalidad_id_modalidad'] ?? '',
      'tipo_oferta_id_tipo_oferta' => $_POST['tipo_oferta_id_tipo_oferta'] ?? '',
      'duracion_meses' => $_POST['duracion_meses'] ?? '',
      'horario' => $_POST['horario'] ?? null,
      'remuneracion' => $_POST['remuneracion'] ?? null,
      'area_conocimiento_id_area' => $_POST['area_conocimiento_id_area'] ?? '',
      'semestre_minimo' => $_POST['semestre_minimo'] ?? null,
      'promedio_minimo' => $_POST['promedio_minimo'] ?? null,
      'habilidades_requeridas' => $_POST['habilidades_requeridas'] ?? null,
      'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
      'fecha_fin' => $_POST['fecha_fin'] ?? null,
      'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
      'cupos_disponibles' => $_POST['cupos_disponibles'] ?? 1,
      'estado_id_estado' => $_POST['estado_id_estado'] ?? '', // El estado se puede actualizar en la edición
      'empresa_idEmpresa' => $_POST['empresa_idEmpresa'] ?? '', // Se puede actualizar la empresa asociada
    ];

    $resultado = $ofertaObj->actualizar($id, $datos);

    // Actualizar carreras dirigidas si se enviaron
    if ($resultado['success'] && isset($_POST['carreras_dirigidas'])) {
      $carreras_dirigidas = json_decode($_POST['carreras_dirigidas'], true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $ofertaObj->asociarCarreras($id, $carreras_dirigidas);
      } else {
        error_log("Error al decodificar JSON de carreras dirigidas en actualización: " . json_last_error_msg());
      }
    }
    echo json_encode($resultado);
    break;

  case 'desactivar': // Cambiar estado a inactivo en lugar de eliminar
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionado para desactivar.']);
      exit();
    }
    $resultado = $ofertaObj->desactivar($id);
    echo json_encode($resultado);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}
?>