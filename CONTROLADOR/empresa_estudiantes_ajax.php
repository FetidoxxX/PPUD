<?php
session_start();
header('Content-Type: application/json');

try {
  // Incluir las clases del MODELO (estas no se alteran, solo se usan)
  require_once '../MODELO/class_estudiante.php';
  require_once '../MODELO/class_referencia.php';
  require_once '../MODELO/class_oferta.php'; // Para obtener carreras en el perfil del estudiante

} catch (Throwable $e) {
  error_log("ERROR CRÍTICO (empresa_estudiantes_ajax - Carga Inicial): " . $e->getMessage() . " en línea " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del sistema al cargar módulos.']);
  exit();
}

// Verificar sesión de empresa
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
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Empresa.']);
  exit();
}

$estudianteObj = new Estudiante();
$referenciaObj = new Referencia();
$ofertaObj = new Oferta(); // Instancia para obtener carreras si es necesario
$idEmpresaLogueada = $_SESSION['usuario_id']; // ID de la empresa logueada

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
  case 'obtener_listado_estudiantes':
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 6; // Número de estudiantes por página
    $offset = ($page - 1) * $limit;
    $busqueda = $_GET['busqueda'] ?? '';

    try {
      $estudiantes = $estudianteObj->obtenerTodosActivos($busqueda, $limit, $offset);
      $totalEstudiantes = $estudianteObj->contarEstudiantesActivos($busqueda);

      $html = '';
      if (!empty($estudiantes)) {
        foreach ($estudiantes as $estudiante) {
          $carreras_interes_nombres = [];
          // Obtener los IDs de las carreras de interés del estudiante
          $carreras_interes_ids = $estudianteObj->obtenerCarrerasDeInteres($estudiante['idEstudiante']);

          // Mapear los IDs de carreras de interés a sus nombres usando la lista global de carreras
          // (Esta lista global se pasará desde PHP a JS en la vista principal)
          // Aquí en el backend, si no tenemos la lista completa de carreras, podemos hacer una consulta
          // o simplemente mostrar los IDs o un mensaje. Para este ejemplo, asumiremos que el JS
          // se encarga de mapear los nombres, y aquí solo pasamos los IDs si es necesario.
          // Para la tarjeta de resumen, mostraremos un mensaje si no hay nombres disponibles.
          if (!empty($carreras_interes_ids)) {
            // Si se necesita el nombre aquí, se requeriría otra consulta o un mapa de carreras
            // Para evitar consultas N+1, se puede obtener un mapa de todas las carreras al inicio
            // $carreras_map = array_column($ofertaObj->obtenerCarreras(), 'nombre', 'id_carrera');
            // $carreras_interes_nombres_array = array_map(function($id) use ($carreras_map) {
            //     return $carreras_map[$id] ?? "ID: " . $id;
            // }, $carreras_interes_ids);
            // $carreras_interes_nombres = implode(', ', $carreras_interes_nombres_array);
            $carreras_interes_nombres = 'Ver perfil para detalles'; // Simplificado para la tarjeta
          } else {
            $carreras_interes_nombres = 'Ninguna';
          }


          $html .= '
            <div class="col">
              <div class="card h-100 shadow-sm border-0 rounded-3">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title text-primary mb-2">
                    <a href="#" class="text-decoration-none view-student-profile-modulo"
                       data-id="' . htmlspecialchars($estudiante['idEstudiante']) . '"
                       data-name="' . htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']) . '">
                      <i class="fas fa-user-graduate me-2"></i>' . htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']) . '
                    </a>
                  </h5>
                  <h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($estudiante['codigo_estudiante'] ?? 'N/A') . '</h6>
                  <p class="card-text mb-1"><small><strong>Carrera:</strong> ' . htmlspecialchars($estudiante['carrera_nombre'] ?? 'No especificada') . '</small></p>
                  <p class="card-text mb-1"><small><strong>Semestre:</strong> ' . htmlspecialchars($estudiante['semestre'] ?? 'N/A') . '</small></p>
                  <p class="card-text mb-1"><small><strong>Promedio:</strong> ' . htmlspecialchars($estudiante['promedio_academico'] ?? 'N/A') . '</small></p>
                  <p class="card-text flex-grow-1"><small><strong>Intereses:</strong> ' . htmlspecialchars($carreras_interes_nombres) . '</small></p>
                  <div class="mt-auto text-end">
                    <button type="button" class="btn btn-outline-primary btn-sm view-student-profile-modulo"
                            data-id="' . htmlspecialchars($estudiante['idEstudiante']) . '"
                            data-name="' . htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']) . '">
                      Ver Perfil <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>';
        }
      } else {
        $html = '<p class="text-center text-muted w-100 py-4">No se encontraron estudiantes activos.</p>';
      }

      echo json_encode([
        'success' => true,
        'html' => $html,
        'totalEstudiantes' => $totalEstudiantes,
        'currentPage' => $page,
        'limit' => $limit
      ]);

    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - obtener_listado_estudiantes): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener el listado de estudiantes: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_perfil_estudiante_completo':
    $idEstudiante = $_GET['idEstudiante'] ?? '';
    if (empty($idEstudiante)) {
      echo json_encode(['success' => false, 'message' => 'ID de estudiante no proporcionado.']);
      exit();
    }

    try {
      // Usar obtenerPorIdParaEmpresa que ya trae referencias y carreras de interés (IDs)
      // Se asume que este método también trae la 'hoja_vida_path' del estudiante.
      $estudiante_data = $estudianteObj->obtenerPorIdParaEmpresa($idEstudiante);

      if ($estudiante_data) {
        echo json_encode(['success' => true, 'data' => $estudiante_data]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Perfil de estudiante no encontrado.']);
      }
    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - obtener_perfil_estudiante_completo): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al cargar el perfil completo del estudiante: ' . $e->getMessage()]);
    }
    break;

  case 'crear_referencia':
    error_log("DEBUG (empresa_estudiantes_ajax - crear_referencia): Datos recibidos en POST: " . print_r($_POST, true));

    try {
      $datos = [
        'comentario' => $_POST['comentario'] ?? '',
        'puntuacion' => $_POST['puntuacion'] ?? null,
        'tipo_referencia_id_tipo_referencia' => $_POST['tipo_referencia_id_tipo_referencia'] ?? 2, // Asignar 2 por defecto si no viene
        'estudiante_idEstudiante' => $_POST['estudiante_idEstudiante'] ?? '',
        'empresa_idEmpresa' => $idEmpresaLogueada // Usar el ID de la empresa de la sesión
      ];

      error_log("DEBUG (empresa_estudiantes_ajax - crear_referencia): Datos a registrar: " . print_r($datos, true));

      // Validación básica
      if (empty($datos['comentario']) || empty($datos['tipo_referencia_id_tipo_referencia']) || empty($datos['estudiante_idEstudiante']) || empty($datos['empresa_idEmpresa'])) {
        error_log("ERROR (empresa_estudiantes_ajax - crear_referencia): Datos incompletos detectados. Comentario: " . (empty($datos['comentario']) ? 'VACIO' : 'OK') . ", Tipo Ref: " . (empty($datos['tipo_referencia_id_tipo_referencia']) ? 'VACIO' : 'OK') . ", Estudiante ID: " . (empty($datos['estudiante_idEstudiante']) ? 'VACIO' : 'OK') . ", Empresa ID: " . (empty($datos['empresa_idEmpresa']) ? 'VACIO' : 'OK'));
        echo json_encode(['success' => false, 'message' => 'Datos incompletos para crear la referencia.']);
        exit();
      }

      $resultado = $referenciaObj->registrar($datos);
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - crear_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al crear la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_referencia_por_id':
    try {
      $idReferencia = $_GET['idReferencia'] ?? '';
      if (empty($idReferencia)) {
        echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado.']);
        exit();
      }

      $referencia = $referenciaObj->obtenerPorId($idReferencia);

      if ($referencia) {
        // Opcional: Verificar que la empresa logueada es la propietaria de la referencia
        if ($referencia['empresa_idEmpresa'] != $idEmpresaLogueada) {
          echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver esta referencia.']);
          exit();
        }
        echo json_encode(['success' => true, 'data' => $referencia]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Referencia no encontrada.']);
      }
    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - obtener_referencia_por_id): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'actualizar_referencia':
    try {
      $idReferencia = $_POST['idReferencia'] ?? '';
      $datos = [
        'comentario' => $_POST['comentario'] ?? '',
        'puntuacion' => $_POST['puntuacion'] ?? null,
        'tipo_referencia_id_tipo_referencia' => $_POST['tipo_referencia_id_tipo_referencia'] ?? 2, // Asignar 2 por defecto si no viene
        // 'estado_id_estado' => $_POST['estado_id_estado'] ?? null // Si se permite actualizar el estado
      ];

      if (empty($idReferencia)) {
        echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para actualizar.']);
        exit();
      }

      // Verificar que la referencia existe y pertenece a la empresa logueada
      $referenciaExistente = $referenciaObj->obtenerPorId($idReferencia);

      if (!$referenciaExistente || $referenciaExistente['empresa_idEmpresa'] != $idEmpresaLogueada) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para actualizar esta referencia o no existe.']);
        exit();
      }

      $fecha_creacion = new DateTime($referenciaExistente['fecha_creacion']);
      $fecha_actual = new DateTime();
      $diferencia = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();

      // Restricción de 24 horas para editar
      if ($diferencia > 86400) { // 86400 segundos = 24 horas
        echo json_encode(['success' => false, 'message' => 'El tiempo de edición para esta referencia ha expirado (más de 24 horas).']);
        exit();
      }

      $resultado = $referenciaObj->actualizar($idReferencia, $datos);
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - actualizar_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al actualizar la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'eliminar_referencia': // En realidad desactiva la referencia
    try {
      $idReferencia = $_POST['idReferencia'] ?? '';
      if (empty($idReferencia)) {
        echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para eliminar.']);
        exit();
      }

      // Verificar que la referencia existe y pertenece a la empresa logueada
      $referenciaExistente = $referenciaObj->obtenerPorId($idReferencia);

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

      // Restricción de 24 horas para eliminar
      if ($referenciaExistente['empresa_idEmpresa'] != $idEmpresaLogueada || $diferencia > 86400) { // 86400 segundos = 24 horas
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta referencia o el tiempo de eliminación ha expirado (más de 24 horas).']);
        exit();
      }

      $resultado = $referenciaObj->eliminar($idReferencia); // Llama al método que ahora la desactiva
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - eliminar_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al eliminar la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_referencias_estudiante_perfil':
    try {
      $idEstudiante = $_GET['idEstudiante'] ?? '';
      $empresa_id = $_GET['empresa_id'] ?? ''; // El ID de la empresa que está viendo el perfil

      if (empty($idEstudiante) || empty($empresa_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de estudiante o empresa no proporcionado.']);
        exit();
      }

      // Obtener las referencias asociadas a este estudiante, incluyendo solo las de tipo 'empresa_a_estudiante' (ID 2)
      // y que estén activas (ID 1).
      $referencias = $referenciaObj->obtenerTodas(null, $idEstudiante, 2, 100, 0, 1); // Filtrar por tipo 2 y estado 1

      $html_referencias = '';
      if (!empty($referencias)) {
        foreach ($referencias as $ref) {
          $puntuacion_html = '';
          if ($ref['puntuacion'] !== null) {
            $puntuacion_html = '<span class="badge bg-warning text-dark me-2"><i class="fas fa-star"></i> ' . htmlspecialchars(number_format($ref['puntuacion'], 1)) . '</span>';
          }

          // Calcular si han pasado más de 24 horas desde la creación
          $fecha_creacion = new DateTime($ref['fecha_creacion']);
          $fecha_actual = new DateTime();
          $diferencia_segundos = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();
          $puede_editar_eliminar = ($diferencia_segundos <= 86400); // 86400 segundos = 24 horas

          // Botones de editar y eliminar, solo si la referencia fue creada por la empresa logueada Y el tiempo no ha expirado
          $action_buttons = '';
          if ($ref['empresa_idEmpresa'] == $empresa_id && $puede_editar_eliminar) {
            $action_buttons = '
              <div class="d-inline-flex gap-2 ms-auto"> <!-- Usar d-inline-flex y ms-auto para alinear a la derecha -->
                <button type="button" class="btn btn-warning btn-sm edit-reference-btn-modulo" data-id="' . htmlspecialchars($ref['idReferencia']) . '">
                  <i class="fas fa-pencil-alt"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm delete-reference-btn-modulo" data-id="' . htmlspecialchars($ref['idReferencia']) . '">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>';
          }
          // Si el tiempo ha expirado o la empresa no es la creadora, no se muestran los botones ni el mensaje.

          $html_referencias .= '
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h6 class="card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-building me-2"></i>' . htmlspecialchars($ref['empresa_nombre']) . '</span>
                <div class="d-flex align-items-center"> <!-- Contenedor para puntuación y botones -->
                  ' . $puntuacion_html . '
                  ' . $action_buttons . '
                </div>
              </h6>
              <p class="card-text text-muted">' . htmlspecialchars($ref['comentario']) . '</p>
              <p class="card-text"><small class="text-muted">Fecha: ' . date('d/m/Y', strtotime($ref['fecha_creacion'])) . '</small></p>
            </div>
          </div>';
        }
      } else {
        $html_referencias = '<p class="text-muted text-center py-3">No hay referencias para este estudiante.</p>';
      }

      echo json_encode(['success' => true, 'html' => $html_referencias]);
    } catch (Exception $e) {
      error_log("ERROR (empresa_estudiantes_ajax - obtener_referencias_estudiante_perfil): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al cargar las referencias del estudiante: ' . $e->getMessage()]);
    }
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}