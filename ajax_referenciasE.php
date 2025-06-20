<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
  require_once './class/class_referencia.php'; // Incluir la clase Referencia
  require_once './class/class_estudiante.php'; // Necesario para obtener el nombre del estudiante si se desea en el modal de referencia
  // Opcional: incluir otras clases si las acciones lo requieren
} catch (Throwable $e) {
  error_log("FATAL ERROR in ajax_referenciasE.php initial setup: " . $e->getMessage() . " on line " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}

// Verificar si la sesión de usuario está activa y es una empresa
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

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
  error_log("DEBUG AUTH FAIL (ajax_referenciasE): usuario_id is set? " . (isset($_SESSION['usuario_id']) ? 'Yes' : 'No') . " | rol is empresa? " . (($_SESSION['rol'] ?? 'none') === 'empresa' ? 'Yes' : 'No'));
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Empresa en el Sistema.', 'redirect' => 'index.php']);
  exit();
}

$referenciaObj = new Referencia();
$estudianteObj = new Estudiante(); // Se necesita para obtener el nombre completo del estudiante

$idEmpresa = $_SESSION['usuario_id']; // ID de la empresa logueada

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'crear_referencia':
    $errores = [];
    if (empty($_POST['comentario'])) {
      $errores[] = 'El comentario de la referencia es obligatorio.';
    }
    // Ya no es necesario validar tipo_referencia_id_tipo_referencia desde el POST directamente,
    // ya que siempre debería ser 2 desde el campo oculto o se puede forzar aquí.
    // if (empty($_POST['tipo_referencia_id_tipo_referencia'])) {
    //   $errores[] = 'El tipo de referencia es obligatorio.';
    // }
    if (empty($_POST['estudiante_idEstudiante'])) {
      $errores[] = 'El ID del estudiante es obligatorio.';
    }

    if (!empty($errores)) {
      echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
      break;
    }

    // Forzar el tipo de referencia a 2 (empresa_a_estudiante) ya que se crea desde el módulo de empresa.
    $tipo_referencia_id_tipo_referencia = 2;

    $datos_referencia = [
      'comentario' => $_POST['comentario'],
      'puntuacion' => $_POST['puntuacion'] ?? null, // Puede ser null
      'tipo_referencia_id_tipo_referencia' => $tipo_referencia_id_tipo_referencia,
      'estudiante_idEstudiante' => $_POST['estudiante_idEstudiante'],
      'empresa_idEmpresa' => $idEmpresa, // La empresa logueada es quien crea la referencia
    ];

    $resultado = $referenciaObj->registrar($datos_referencia);
    echo json_encode($resultado);
    break;

  case 'obtener_tipos_referencia':
    $tipos = $referenciaObj->obtenerTiposReferencia();
    echo json_encode(['success' => true, 'data' => $tipos]);
    break;

  case 'obtener_estados_referencia': // Opcional, si los estados de referencia se gestionan dinámicamente
    $estados = $referenciaObj->obtenerEstados();
    echo json_encode(['success' => true, 'data' => $estados]);
    break;

  case 'obtener_referencias_estudiante':
    $idEstudiante = $_GET['idEstudiante'] ?? '';
    if (empty($idEstudiante)) {
      echo json_encode(['success' => false, 'message' => 'ID de estudiante no proporcionada.']);
      break;
    }

    // Asegurarse de que solo se pidan referencias de tipo 'empresa_a_estudiante' (ID 2)
    $referencias = $referenciaObj->obtenerTodas(null, $idEstudiante, 2);

    $html_referencias = '';
    if (!empty($referencias)) {
      foreach ($referencias as $ref) {
        // Log para depuración, si es necesario
        error_log("DEBUG: Referencia tipo para display: " . $ref['tipo_referencia_nombre'] . " (ID: " . $ref['tipo_referencia_id_tipo_referencia'] . ") para Estudiante ID: " . $idEstudiante);

        $puntuacion_html = '';
        if (isset($ref['puntuacion']) && $ref['puntuacion'] !== null) {
          $puntuacion_html = '<span class="badge bg-warning text-dark me-2"><i class="fas fa-star"></i> ' . htmlspecialchars($ref['puntuacion']) . '</span>';
        }
        $html_referencias .= '
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h6 class="card-title d-flex justify-content-between align-items-center">
                <!-- Muestra el nombre de la empresa en lugar del tipo de referencia -->
                <span><i class="fas fa-building me-2"></i>' . htmlspecialchars($ref['empresa_nombre']) . '</span>
                ' . $puntuacion_html . '
              </h6>
              <p class="card-text text-muted">' . htmlspecialchars($ref['comentario']) . '</p>
              <!-- Muestra solo la fecha de creación -->
              <p class="card-text"><small class="text-muted">Fecha: ' . date('d/m/Y', strtotime($ref['fecha_creacion'])) . '</small></p>
            </div>
          </div>';
      }
    } else {
      $html_referencias = '<p class="text-muted text-center py-3">No hay referencias registradas para este estudiante.</p>';
    }
    echo json_encode(['success' => true, 'html' => $html_referencias]);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}