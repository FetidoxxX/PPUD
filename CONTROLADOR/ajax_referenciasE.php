<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
  require_once '../MODELO/class_referencia.php'; // Incluir la clase Referencia
  require_once '../MODELO/class_estudiante.php'; // Necesario para obtener el nombre del estudiante si se desea en el modal de referencia
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
    echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. No tiene permisos para realizar esta acción.', 'redirect' => '../index.php']);
  exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$idEmpresa = $_SESSION['usuario_id']; // ID de la empresa logueada

$referenciaObj = new Referencia();

switch ($action) {
  case 'obtener_referencias_estudiante':
    $idEstudiante = $_GET['idEstudiante'] ?? null;
    if (!$idEstudiante) {
      echo json_encode(['success' => false, 'message' => 'ID de estudiante no proporcionado.']);
      exit();
    }

    try {
      // Obtener referencias de tipo "empresa a estudiante" (asumimos tipo_referencia_id = 2 para este caso)
      // Y AHORA FILTRANDO POR ESTADO ACTIVO (ID = 1)
      $referencias = $referenciaObj->obtenerTodas(null, $idEstudiante, 2, 10, 0, 1); // El último parámetro es el ID del estado (1 = Activo)

      $html_referencias = '';
      if (!empty($referencias)) {
        foreach ($referencias as $ref) {
          $puntuacion_html = '';
          if ($ref['puntuacion'] !== null) {
            $puntuacion_html = '<span class="badge bg-warning text-dark me-2"><i class="fas fa-star"></i> ' . htmlspecialchars(number_format($ref['puntuacion'], 1)) . '</span>';
          }

          $boton_editar_html = '';
          $boton_eliminar_html = '';

          // Lógica para mostrar/ocultar botones de editar/eliminar
          // Solo si la referencia es de la empresa actual y tiene menos de 24h
          $fecha_creacion = new DateTime($ref['fecha_creacion']);
          $fecha_actual = new DateTime();
          $diferencia = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();

          if ($ref['empresa_idEmpresa'] == $idEmpresa && $diferencia <= 86400) { // 86400 segundos = 24 horas
            // Se crean formularios para cada botón de acción
            $boton_editar_html = '
            <form class="d-inline-block edit-reference-form me-2" data-id-referencia="' . $ref['idReferencia'] . '">
              <input type="hidden" name="idReferencia" value="' . $ref['idReferencia'] . '">
              <button type="submit" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-edit"></i>
              </button>
            </form>';
            $boton_eliminar_html = '
            <form class="d-inline-block delete-reference-form" data-id-referencia="' . $ref['idReferencia'] . '">
              <input type="hidden" name="idReferencia" value="' . $ref['idReferencia'] . '">
              <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash-alt"></i>
              </button>
            </form>';
          }

          $html_referencias .= '
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h6 class="card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-building me-2"></i>' . htmlspecialchars($ref['empresa_nombre']) . '</span>
                <div>
                  ' . $puntuacion_html . '
                  ' . $boton_editar_html . '
                  ' . $boton_eliminar_html . '
                </div>
              </h6>
              <p class="card-text text-muted">' . htmlspecialchars($ref['comentario']) . '</p>
              <p class="card-text"><small class="text-muted">Fecha: ' . date('d/m/Y', strtotime($ref['fecha_creacion'])) . '</small></p>
            </div>
          </div>';
        }
      } else {
        $html_referencias = '<p class="text-muted text-center py-3">No hay referencias registradas para este estudiante.</p>';
      }

      echo json_encode(['success' => true, 'html' => $html_referencias]);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referenciasE - obtener_referencias_estudiante): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al cargar las referencias: ' . $e->getMessage()]);
    }
    break;

  case 'crear_referencia':
    $comentario = $_POST['comentario'] ?? '';
    $puntuacion = $_POST['puntuacion'] ?? null;
    $tipo_referencia_id = $_POST['tipo_referencia_id_tipo_referencia'] ?? 2; // Asume 2 para empresa a estudiante
    $estudiante_id = $_POST['estudiante_idEstudiante'] ?? null;

    if (empty($comentario) || !$estudiante_id) {
      echo json_encode(['success' => false, 'message' => 'Datos incompletos para crear la referencia.']);
      exit();
    }

    $datos_referencia = [
      'comentario' => $comentario,
      'puntuacion' => $puntuacion,
      'tipo_referencia_id_tipo_referencia' => $tipo_referencia_id,
      'estudiante_idEstudiante' => $estudiante_id,
      'empresa_idEmpresa' => $idEmpresa,
    ];

    try {
      $resultado = $referenciaObj->registrar($datos_referencia);
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referenciasE - crear_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al crear la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_referencia_por_id':
    $idReferencia = $_GET['idReferencia'] ?? null;

    if (!$idReferencia) {
      echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado.']);
      exit();
    }

    try {
      $referencia = $referenciaObj->obtenerPorId($idReferencia); // Se obtiene sin filtro de estado para verificar permisos

      if ($referencia) {
        // Verificar que la referencia pertenece a la empresa logueada y si está dentro de las 24 horas
        $fecha_creacion = new DateTime($referencia['fecha_creacion']);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();

        if ($referencia['empresa_idEmpresa'] == $idEmpresa && $diferencia <= 86400) {
          echo json_encode(['success' => true, 'data' => $referencia]);
        } else {
          echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar esta referencia o el tiempo de edición ha expirado.']);
        }
      } else {
        echo json_encode(['success' => false, 'message' => 'Referencia no encontrada.']);
      }
    } catch (Exception $e) {
      error_log("ERROR (ajax_referenciasE - obtener_referencia_por_id): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'editar_referencia':
    $idReferencia = $_POST['idReferencia'] ?? null;
    $comentario = $_POST['comentario'] ?? '';
    $puntuacion = $_POST['puntuacion'] ?? null;
    $tipo_referencia_id = $_POST['tipo_referencia_id_tipo_referencia'] ?? 2; // Asume 2 para empresa a estudiante

    if (!$idReferencia || empty($comentario)) {
      echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar la referencia.']);
      exit();
    }

    try {
      // Primero, verificar que la referencia pertenece a la empresa logueada y si está dentro de las 24 horas
      $referenciaExistente = $referenciaObj->obtenerPorId($idReferencia);

      if (!$referenciaExistente) {
        echo json_encode(['success' => false, 'message' => 'Referencia no encontrada.']);
        exit();
      }

      // Además, verifica que la referencia esté activa para poder editarla.
      // Asumiendo que el ID para 'Activo' es 1
      if ($referenciaExistente['estado_id_estado'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Esta referencia no puede ser editada porque no está activa.']);
        exit();
      }

      $fecha_creacion = new DateTime($referenciaExistente['fecha_creacion']);
      $fecha_actual = new DateTime();
      $diferencia = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();

      if ($referenciaExistente['empresa_idEmpresa'] != $idEmpresa || $diferencia > 86400) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar esta referencia o el tiempo de edición ha expirado.']);
        exit();
      }

      $datos_actualizar = [
        'comentario' => $comentario,
        'puntuacion' => $puntuacion,
        'tipo_referencia_id_tipo_referencia' => $tipo_referencia_id,
      ];

      $resultado = $referenciaObj->actualizar($idReferencia, $datos_actualizar);
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referenciasE - editar_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al actualizar la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'eliminar_referencia':
    $idReferencia = $_POST['idReferencia'] ?? null;

    if (!$idReferencia) {
      echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para eliminar.']);
      exit();
    }

    try {
      // Primero, verificar que la referencia pertenece a la empresa logueada y si está dentro de las 24 horas
      $referenciaExistente = $referenciaObj->obtenerPorId($idReferencia); // Se obtiene sin filtro de estado

      if (!$referenciaExistente) {
        echo json_encode(['success' => false, 'message' => 'Referencia no encontrada.']);
        exit();
      }

      // Además, verifica que la referencia esté activa para poder eliminarla.
      if ($referenciaExistente['estado_id_estado'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Esta referencia ya ha sido eliminada o no está activa.']);
        exit();
      }

      $fecha_creacion = new DateTime($referenciaExistente['fecha_creacion']);
      $fecha_actual = new DateTime();
      $diferencia = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();

      if ($referenciaExistente['empresa_idEmpresa'] != $idEmpresa || $diferencia > 86400) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta referencia o el tiempo de edición/eliminación ha expirado.']);
        exit();
      }

      $resultado = $referenciaObj->eliminar($idReferencia); // Llama al método que ahora la desactiva
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referenciasE - eliminar_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al eliminar la referencia: ' . $e->getMessage()]);
    }
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}

?>