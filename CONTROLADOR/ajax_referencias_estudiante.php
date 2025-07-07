<?php
session_start();
header('Content-Type: application/json');

try {
  require_once '../MODELO/class_empresa.php';
  require_once '../MODELO/class_referencia.php';
  require_once '../MODELO/class_estudiante.php'; // Para obtener el nombre del estudiante si es necesario en las referencias

} catch (Throwable $e) {
  error_log("ERROR CRÍTICO (ajax_referencias_estudiante - Carga Inicial): " . $e->getMessage() . " en línea " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del sistema al cargar módulos.']);
  exit();
}

// Verificar sesión de estudiante
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

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Estudiante.']);
  exit();
}

$empresaObj = new Empresa();
$referenciaObj = new Referencia();
$estudianteObj = new Estudiante();
$idEstudianteLogueado = $_SESSION['usuario_id']; // ID del estudiante logueado

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
  case 'obtener_perfil_empresa_completo':
    $idEmpresa = $_GET['idEmpresa'] ?? '';
    if (empty($idEmpresa)) {
      echo json_encode(['success' => false, 'message' => 'ID de empresa no proporcionado.']);
      exit();
    }

    try {
      $empresa_data = $empresaObj->obtenerPorId($idEmpresa);

      if ($empresa_data) {
        // Formatear la salida para la visualización en el modal
        $nombre = htmlspecialchars($empresa_data['nombre'] ?? 'N/A');
        $correo = htmlspecialchars($empresa_data['correo'] ?? 'N/A');
        $telefono = htmlspecialchars($empresa_data['telefono'] ?? 'N/A');
        $direccion = htmlspecialchars($empresa_data['direccion'] ?? 'N/A');
        $ciudad_nombre = htmlspecialchars($empresa_data['ciudad_nombre'] ?? 'N/A');
        $n_doc = htmlspecialchars($empresa_data['n_doc'] ?? 'N/A');
        $tipo_documento_nombre = htmlspecialchars($empresa_data['tipo_documento_nombre'] ?? 'N/A');
        $sector_nombre = htmlspecialchars($empresa_data['sector_nombre'] ?? 'N/A');
        $numero_empleados = htmlspecialchars($empresa_data['numero_empleados'] ?? 'N/A');
        $descripcion = htmlspecialchars($empresa_data['descripcion'] ?? 'No hay descripción disponible.');
        $contacto_nombres = htmlspecialchars($empresa_data['contacto_nombres'] ?? 'N/A');
        $contacto_apellidos = htmlspecialchars($empresa_data['contacto_apellidos'] ?? 'N/A');
        $contacto_cargo = htmlspecialchars($empresa_data['contacto_cargo'] ?? 'N/A');
        $ano_fundacion = htmlspecialchars($empresa_data['ano_fundacion'] ?? 'N/A');

        $sitio_web = 'N/A';
        if (!empty($empresa_data['sitio_web'])) {
          $url = htmlspecialchars($empresa_data['sitio_web']);
          if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
          }
          $sitio_web = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($empresa_data['sitio_web']) . '</a>';
        }

        $html_content = '
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="mb-2"><strong><i class="fas fa-building me-2"></i>Nombre:</strong> ' . $nombre . '</div>
                        <div class="mb-2"><strong><i class="fas fa-envelope me-2"></i>Correo:</strong> ' . $correo . '</div>
                        <div class="mb-2"><strong><i class="fas fa-phone me-2"></i>Teléfono:</strong> ' . $telefono . '</div>
                        <div class="mb-2"><strong><i class="fas fa-map-marker-alt me-2"></i>Dirección:</strong> ' . $direccion . '</div>
                        <div class="mb-2"><strong><i class="fas fa-city me-2"></i>Ciudad:</strong> ' . $ciudad_nombre . '</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="mb-2"><strong><i class="fas fa-id-card me-2"></i>NIT:</strong> ' . $n_doc . '</div>
                        <div class="mb-2"><strong><i class="fas fa-file-alt me-2"></i>Tipo de Documento:</strong> ' . $tipo_documento_nombre . '</div>
                        <div class="mb-2"><strong><i class="fas fa-industry me-2"></i>Sector:</strong> ' . $sector_nombre . '</div>
                        <div class="mb-2"><strong><i class="fas fa-globe me-2"></i>Sitio Web:</strong> ' . $sitio_web . '</div>
                        <div class="mb-2"><strong><i class="fas fa-users me-2"></i>Empleados:</strong> ' . $numero_empleados . '</div>
                        <div class="mb-2"><strong><i class="fas fa-calendar me-2"></i>Año de Fundación:</strong> ' . $ano_fundacion . '</div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6><strong><i class="fas fa-info-circle me-2"></i>Descripción:</strong></h6>
                    <p class="text-muted">' . $descripcion . '</p>
                </div>
                <div class="mt-3">
                    <h6><strong><i class="fas fa-user-tie me-2"></i>Contacto Principal:</strong></h6>
                    <div class="ps-3">
                        <div class="mb-1"><strong>Nombres:</strong> ' . $contacto_nombres . '</div>
                        <div class="mb-1"><strong>Apellidos:</strong> ' . $contacto_apellidos . '</div>
                        <div class="mb-1"><strong>Cargo:</strong> ' . $contacto_cargo . '</div>
                    </div>
                </div>
            ';
        echo json_encode(['success' => true, 'html' => $html_content, 'empresa_nombre' => $nombre]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Perfil de empresa no encontrado.']);
      }
    } catch (Exception $e) {
      error_log("ERROR (ajax_referencias_estudiante - obtener_perfil_empresa_completo): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener el perfil de la empresa: ' . $e->getMessage()]);
    }
    break;

  case 'crear_referencia':
    // --- INICIO DEPURACIÓN ---
    error_log("DEBUG (ajax_referencias_estudiante - crear_referencia): Datos recibidos en POST: " . print_r($_POST, true));
    error_log("DEBUG (ajax_referencias_estudiante - crear_referencia): ID Estudiante Logueado: " . $idEstudianteLogueado);
    // --- FIN DEPURACIÓN ---

    try {
      $datos = [
        'comentario' => $_POST['comentario'] ?? '',
        'puntuacion' => $_POST['puntuacion'] ?? null,
        // Aseguramos que el tipo de referencia se obtenga del POST o se asigne por defecto
        'tipo_referencia_id_tipo_referencia' => $_POST['tipo_referencia_id_tipo_referencia'] ?? 1,
        'estudiante_idEstudiante' => $idEstudianteLogueado, // ID del estudiante de la sesión
        'empresa_idEmpresa' => $_POST['empresa_idEmpresa'] ?? ''
      ];

      // --- INICIO DEPURACIÓN ---
      error_log("DEBUG (ajax_referencias_estudiante - crear_referencia): Datos a registrar: " . print_r($datos, true));
      // --- FIN DEPURACIÓN ---

      if (empty($datos['comentario']) || empty($datos['tipo_referencia_id_tipo_referencia']) || empty($datos['estudiante_idEstudiante']) || empty($datos['empresa_idEmpresa'])) {
        error_log("ERROR (ajax_referencias_estudiante - crear_referencia): Datos incompletos detectados. Comentario: " . (empty($datos['comentario']) ? 'VACIO' : 'OK') . ", Tipo Ref: " . (empty($datos['tipo_referencia_id_tipo_referencia']) ? 'VACIO' : 'OK') . ", Estudiante ID: " . (empty($datos['estudiante_idEstudiante']) ? 'VACIO' : 'OK') . ", Empresa ID: " . (empty($datos['empresa_idEmpresa']) ? 'VACIO' : 'OK'));
        echo json_encode(['success' => false, 'message' => 'Datos incompletos para crear la referencia.']);
        exit();
      }

      // ** ANTES: Bloque de validación que impedía múltiples referencias de estudiante a empresa. **
      // ** AHORA: Este bloque ha sido eliminado para permitir que un estudiante genere varias referencias a la misma empresa. **
      // $referencias_existentes = $referenciaObj->obtenerTodas($datos['empresa_idEmpresa'], $idEstudianteLogueado, 1, 1, 0, 1);
      // if (!empty($referencias_existentes)) {
      //   echo json_encode(['success' => false, 'message' => 'Ya has creado una referencia activa para esta empresa. Solo puedes tener una referencia activa por empresa.']);
      //   exit();
      // }

      $resultado = $referenciaObj->registrar($datos);
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referencias_estudiante - crear_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
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
        // Verificar que la referencia pertenece al estudiante logueado
        if ($referencia['estudiante_idEstudiante'] != $idEstudianteLogueado) {
          echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver esta referencia.']);
          exit();
        }
        // Verificar la restricción de 24 horas para edición
        $fecha_creacion = new DateTime($referencia['fecha_creacion']);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_actual->getTimestamp() - $fecha_creacion->getTimestamp();
        if ($diferencia > 86400) { // 86400 segundos = 24 horas
          echo json_encode(['success' => false, 'message' => 'El tiempo de edición para esta referencia ha expirado (más de 24 horas).']);
          exit();
        }

        echo json_encode(['success' => true, 'data' => $referencia]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Referencia no encontrada.']);
      }
    } catch (Exception $e) {
      error_log("ERROR (ajax_referencias_estudiante - obtener_referencia_por_id): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'actualizar_referencia':
    try {
      $idReferencia = $_POST['idReferencia'] ?? '';
      $datos = [
        'comentario' => $_POST['comentario'] ?? '',
        'puntuacion' => $_POST['puntuacion'] ?? null,
        // Aseguramos que el tipo de referencia se obtenga del POST o se asigne por defecto
        'tipo_referencia_id_tipo_referencia' => $_POST['tipo_referencia_id_tipo_referencia'] ?? 1,
      ];

      if (empty($idReferencia)) {
        echo json_encode(['success' => false, 'message' => 'ID de referencia no proporcionado para actualizar.']);
        exit();
      }

      // Llamar al nuevo método que incluye la lógica de validación de 24 horas y propiedad
      $resultado = $referenciaObj->actualizarReferenciaEstudiante($idReferencia, $idEstudianteLogueado, $datos);
      echo json_encode($resultado);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referencias_estudiante - actualizar_referencia): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al actualizar la referencia: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_referencias_empresa_perfil':
    try {
      $idEmpresa = $_GET['idEmpresa'] ?? '';
      $idEstudiante = $_GET['idEstudiante'] ?? ''; // El ID del estudiante logueado

      if (empty($idEmpresa) || empty($idEstudiante)) {
        echo json_encode(['success' => false, 'message' => 'ID de empresa o estudiante no proporcionado.']);
        exit();
      }

      // Obtener las referencias asociadas a esta empresa, incluyendo solo las de tipo 'estudiante_a_empresa' (ID 1)
      // y que estén activas (ID 1).
      // Se pasa el idEmpresa y el idEstudiante para filtrar correctamente
      $referencias = $referenciaObj->obtenerTodas($idEmpresa, null, 1, 100, 0, 1);

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
          $puede_editar = ($diferencia_segundos <= 86400); // 86400 segundos = 24 horas

          // Botón de editar, solo si la referencia fue creada por el estudiante logueado Y el tiempo no ha expirado
          $action_buttons = '';
          if ($ref['estudiante_idEstudiante'] == $idEstudiante && $puede_editar) {
            $action_buttons = '
              <div class="d-inline-flex gap-2 ms-auto">
                <button type="button" class="btn btn-warning btn-sm edit-reference-btn-estudiante"
                  data-id-referencia="' . htmlspecialchars($ref['idReferencia']) . '"
                  data-comentario="' . htmlspecialchars($ref['comentario']) . '"
                  data-puntuacion="' . htmlspecialchars($ref['puntuacion'] ?? '') . '"
                  data-fecha-creacion="' . htmlspecialchars($ref['fecha_creacion']) . '"
                  title="Editar Referencia">
                  <i class="fas fa-pencil-alt"></i>
                </button>
              </div>';
          } else if ($ref['estudiante_idEstudiante'] == $idEstudiante && !$puede_editar) {
            $action_buttons = '
              <div class="d-inline-flex gap-2 ms-auto">
                <button type="button" class="btn btn-secondary btn-sm" disabled
                  title="Tiempo de edición expirado (más de 24 horas)">
                  <i class="fas fa-pencil-alt"></i>
                </button>
              </div>';
          }

          // Obtener el nombre del estudiante que hizo la referencia
          $estudiante_referencia_data = $estudianteObj->obtenerPorId($ref['estudiante_idEstudiante']);
          $estudiante_nombre_completo = 'Estudiante Desconocido';
          if ($estudiante_referencia_data) {
            $estudiante_nombre_completo = htmlspecialchars($estudiante_referencia_data['nombre'] . ' ' . $estudiante_referencia_data['apellidos']);
          }

          $html_referencias .= '
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h6 class="card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-graduate me-2"></i>' . $estudiante_nombre_completo . '</span>
                <div class="d-flex align-items-center">
                  ' . $puntuacion_html . '
                  ' . $action_buttons . '
                </div>
              </h6>
              <p class="card-text text-muted">' . htmlspecialchars($ref['comentario']) . '</p>
              <p class="card-text"><small class="text-muted">Fecha: ' . date('d/m/Y H:i', strtotime($ref['fecha_creacion'])) . '</small></p>
            </div>
          </div>';
        }
      } else {
        $html_referencias = '<p class="text-muted text-center py-3">No hay referencias de estudiantes para esta empresa.</p>';
      }

      echo json_encode(['success' => true, 'html' => $html_referencias]);
    } catch (Exception $e) {
      error_log("ERROR (ajax_referencias_estudiante - obtener_referencias_empresa_perfil): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al cargar las referencias de la empresa: ' . $e->getMessage()]);
    }
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}