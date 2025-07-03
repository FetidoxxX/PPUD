<?php
session_start();

header('Content-Type: application/json');

// --- INICIO ROBUSTA INCLUSIÓN DE ARCHIVOS Y MANEJO DE SESIÓN/AUTENTICACIÓN ---
try {
  // Las clases ahora manejan la conexión internamente, no es necesario class_conec.php aquí
  require_once '../MODELO/class_estudiante.php';
  require_once '../MODELO/class_oferta.php'; // Para obtener las carreras
  require_once '../MODELO/class_empresa.php'; // Para obtener ciudades y tipos de documento, sectores y estados
  require_once '../MODELO/class_referencia.php'; // NUEVO: Incluir la clase Referencia

  // Verificar si la sesión de usuario está activa y es un estudiante
  $inn = 500; // Tiempo de inactividad
  if (isset($_SESSION['timeout'])) {
    $_session_life = time() - $_SESSION['timeout'];
    if ($_session_life > $inn) {
      session_destroy();
      error_log("DEBUG (ajax_perfilE): Sesión expirada para usuario ID: " . ($_SESSION['usuario_id'] ?? 'N/A'));
      echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
      exit();
    }
  }
  $_SESSION['timeout'] = time();

  if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    error_log("DEBUG (ajax_perfilE): Acceso denegado para rol: " . ($_SESSION['rol'] ?? 'N/A') . " usuario ID: " . ($_SESSION['usuario_id'] ?? 'N/A'));
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Estudiante en el Sistema.', 'redirect' => '../index.php']);
    exit();
  }

  $idEstudiante = $_SESSION['usuario_id']; // ID del estudiante logueado

  $estudianteObj = new Estudiante();
  $ofertaObj = new Oferta(); // Para las carreras
  $empresaObj = new Empresa(); // Para tipos de documento, ciudades, sectores y estados
  $referenciaObj = new Referencia(); // NUEVO: Instancia de la clase Referencia

} catch (Throwable $e) {
  // Captura errores fatales en la carga de clases o conexión inicial
  error_log("ERROR CRÍTICO (ajax_perfilE - Carga Inicial): " . $e->getMessage() . " en línea " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del sistema. Por favor, intente más tarde.']);
  exit();
}
// --- FIN ROBUSTA INCLUSIÓN DE ARCHIVOS Y MANEJO DE SESIÓN/AUTENTICACIÓN ---


$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
  case 'obtener_perfil':
    try {
      $estudiante_data = $estudianteObj->obtenerPorId($idEstudiante);
      if ($estudiante_data) {
        // Obtener carreras de interés
        $carreras_interes = $estudianteObj->obtenerCarrerasDeInteres($idEstudiante);
        $estudiante_data['carreras_interes_ids'] = $carreras_interes;

        error_log("DEBUG (ajax_perfilE): Perfil de estudiante obtenido exitosamente para ID: " . $idEstudiante);
        echo json_encode(['success' => true, 'data' => $estudiante_data]);
      } else {
        error_log("ERROR (ajax_perfilE): No se encontraron datos para estudiante ID: " . $idEstudiante);
        echo json_encode(['success' => false, 'message' => 'Perfil de estudiante no encontrado.']);
      }
    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE): Excepción en obtener_perfil: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener el perfil: ' . $e->getMessage()]);
    }
    break;

  case 'actualizar_perfil':
    try {
      // Usar $_POST directamente ya que FormData lo envía así
      $datos = [
        'nombre' => $_POST['nombre'] ?? '',
        'apellidos' => $_POST['apellidos'] ?? '',
        'correo' => $_POST['correo'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'fechaNac' => $_POST['fechaNac'] ?? '',
        'n_doc' => $_POST['n_doc'] ?? '',
        'direccion' => $_POST['direccion'] ?? '',
        'codigo_estudiante' => $_POST['codigo_estudiante'] ?? '',
        'semestre' => $_POST['semestre'] ?? '',
        'promedio_academico' => $_POST['promedio_academico'] ?? '',
        'habilidades' => $_POST['habilidades'] ?? '',
        'experiencia_laboral' => $_POST['experiencia_laboral'] ?? '',
        'certificaciones' => $_POST['certificaciones'] ?? '',
        'idiomas' => $_POST['idiomas'] ?? '',
        'objetivos_profesionales' => $_POST['objetivos_profesionales'] ?? '',
        'tipo_documento_id_tipo' => $_POST['tipo_documento_id_tipo'] ?? '',
        'ciudad_id_ciudad' => $_POST['ciudad_id_ciudad'] ?? '',
        'carrera_id_carrera' => $_POST['carrera_id_carrera'] ?? ''
      ];

      // Las carreras de interés vienen como un array (pueden estar vacías)
      // Asegurarse de que $_POST['carreras_interes'] es un array para evitar warnings
      $carreras_interes_ids = isset($_POST['carreras_interes']) && is_array($_POST['carreras_interes'])
        ? $_POST['carreras_interes'] : [];

      $resultado = $estudianteObj->actualizar($idEstudiante, $datos, $carreras_interes_ids);
      error_log("DEBUG (ajax_perfilE): Intento de actualizar perfil para ID: " . $idEstudiante . ". Resultado: " . json_encode($resultado));
      echo json_encode($resultado);

    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE): Excepción en actualizar_perfil: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
    }
    break;

  case 'cambiar_contrasena':
    try {
      $contrasenaActual = $_POST['current_password'] ?? '';
      $contrasenaNueva = $_POST['new_password'] ?? '';
      $confirmarContrasena = $_POST['confirm_new_password'] ?? '';

      if (empty($contrasenaActual) || empty($contrasenaNueva) || empty($confirmarContrasena)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos de contraseña son requeridos.']);
        break;
      }
      if ($contrasenaNueva !== $confirmarContrasena) {
        echo json_encode(['success' => false, 'message' => 'La nueva contraseña y su confirmación no coinciden.']);
        break;
      }

      $resultado = $estudianteObj->cambiarContrasena($idEstudiante, $contrasenaActual, $contrasenaNueva);
      error_log("DEBUG (ajax_perfilE): Intento de cambiar contraseña para ID: " . $idEstudiante . ". Resultado: " . json_encode($resultado));
      echo json_encode($resultado);

    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE): Excepción en cambiar_contrasena: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al cambiar la contraseña: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_carreras':
    try {
      $carreras = $ofertaObj->obtenerCarreras(); // Reutilizamos el método de Oferta
      error_log("DEBUG (ajax_perfilE): Carreras obtenidas: " . count($carreras));
      echo json_encode(['success' => true, 'data' => $carreras]);
    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE): Excepción en obtener_carreras: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener carreras: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_tipos_documento':
    try {
      $tipos_documento = $empresaObj->obtenerTiposDocumento(); // Reutilizamos el método de Empresa
      error_log("DEBUG (ajax_perfilE): Tipos de documento obtenidos: " . count($tipos_documento));
      echo json_encode(['success' => true, 'data' => $tipos_documento]);
    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE): Excepción en obtener_tipos_documento: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener tipos de documento: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_ciudades':
    try {
      $ciudades = $empresaObj->obtenerCiudades(); // Reutilizamos el método de Empresa
      error_log("DEBUG (ajax_perfilE): Ciudades obtenidas: " . count($ciudades));
      echo json_encode(['success' => true, 'data' => $ciudades]);
    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE): Excepción en obtener_ciudades: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener ciudades: ' . $e->getMessage()]);
    }
    break;

  case 'obtener_referencias_estudiante_perfil': // NUEVA ACCIÓN
    error_log("DEBUG (ajax_perfilE - obtener_referencias_estudiante_perfil): ID Estudiante de sesión: " . $idEstudiante); // Log del ID del estudiante

    try {
      // Usamos el ID del estudiante de la sesión para asegurar que solo puede ver sus propias referencias
      $referencias = $referenciaObj->obtenerTodas(null, $idEstudiante, null, 100, 0, 1); // Obtener todas las referencias activas para este estudiante

      error_log("DEBUG (ajax_perfilE - obtener_referencias_estudiante_perfil): Referencias obtenidas: " . var_export($referencias, true)); // Log del array de referencias

      $html_referencias = '';
      if (!empty($referencias)) {
        foreach ($referencias as $ref) {
          $puntuacion_html = '';
          if ($ref['puntuacion'] !== null) {
            $puntuacion_html = '<span class="badge bg-warning text-dark me-2"><i class="fas fa-star"></i> ' . htmlspecialchars(number_format($ref['puntuacion'], 1)) . '</span>';
          }

          $html_referencias .= '
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h6 class="card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-building me-2"></i>' . htmlspecialchars($ref['empresa_nombre']) . '</span>
                <div>
                  ' . $puntuacion_html . '
                  <!-- Eliminado: <span class="badge bg-primary">' . htmlspecialchars($ref['tipo_referencia_nombre']) . '</span> -->
                </div>
              </h6>
              <p class="card-text text-muted">' . htmlspecialchars($ref['comentario']) . '</p>
              <p class="card-text"><small class="text-muted">Fecha: ' . date('d/m/Y', strtotime($ref['fecha_creacion'])) . '</small></p>
            </div>
          </div>';
        }
      } else {
        $html_referencias = '<p class="text-muted text-center py-3">No has recibido referencias aún.</p>';
      }

      error_log("DEBUG (ajax_perfilE - obtener_referencias_estudiante_perfil): HTML generado: " . $html_referencias); // Log del HTML final
      echo json_encode(['success' => true, 'html' => $html_referencias]);
    } catch (Exception $e) {
      error_log("ERROR (ajax_perfilE - obtener_referencias_estudiante_perfil): " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al cargar tus referencias: ' . $e->getMessage()]);
    }
    break;

  default:
    error_log("ERROR (ajax_perfilE): Acción no válida o no proporcionada: " . $action);
    echo json_encode(['success' => false, 'message' => 'Acción no válida o no proporcionada.']);
    break;
}

?>