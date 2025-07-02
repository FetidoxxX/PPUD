<?php
session_start();
ob_start(); // Iniciar el almacenamiento en búfer de salida para capturar cualquier salida inesperada

header('Content-Type: application/json'); // Establecer el tipo de contenido como JSON

try {
  require_once '../MODELO/class_oferta.php';
  require_once '../MODELO/class_empresa.php'; // Incluir la clase Empresa
  require_once '../MODELO/class_estudiante.php'; // Incluir la clase Estudiante
  require_once '../MODELO/class_referencia.php'; // Incluir la clase Referencia

  // Verificar si la sesión de usuario está activa y es una empresa
  $inn = 500;
  if (isset($_SESSION['timeout'])) {
    $_session_life = time() - $_SESSION['timeout'];
    if ($_session_life > $inn) {
      session_destroy();
      ob_end_clean(); // Limpiar el búfer antes de enviar la respuesta JSON
      echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
      exit();
    }
  }
  $_SESSION['timeout'] = time();

  if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
    error_log("DEBUG AUTH FAIL: usuario_id is set? " . (isset($_SESSION['usuario_id']) ? 'Yes' : 'No') . " | rol is empresa? " . (($_SESSION['rol'] ?? 'none') === 'empresa' ? 'Yes' : 'No'));
    ob_end_clean(); // Limpiar el búfer antes de enviar la respuesta JSON
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Empresa en el Sistema.', 'redirect' => '../index.php']);
    exit();
  }

  // Crear instancias de las clases. Cada clase manejará su propia conexión internamente.
  $ofertaObj = new Oferta();
  $empresaObj = new Empresa();
  $estudianteObj = new Estudiante(); // Instancia para manejar estudiantes
  // $referenciaObj está instanciado en ajax_referenciasE.php, no es necesario aquí a menos que se use directamente.
} catch (Throwable $e) {
  error_log("FATAL ERROR in ajax_Mempresa.php initial setup: " . $e->getMessage() . " on line " . $e->getLine());
  ob_end_clean(); // Limpiar el búfer antes de enviar la respuesta de error
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}


// Obtener la ID de la empresa desde la sesión
$idEmpresa = $_SESSION['usuario_id'];

// Determinar la acción a realizar
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'crear_oferta':
    // Validar campos obligatorios
    $errores = [];
    if (empty($_POST['titulo']))
      $errores[] = 'El título es obligatorio.';
    if (empty($_POST['descripcion']))
      $errores[] = 'La descripción es obligatoria.';
    if (empty($_POST['modalidad_id_modalidad']))
      $errores[] = 'La modalidad es obligatoria.';
    if (empty($_POST['tipo_oferta_id_tipo_oferta']))
      $errores[] = 'El tipo de oferta es obligatorio.';
    if (empty($_POST['area_conocimiento_id_area']))
      $errores[] = 'El área de conocimiento es obligatoria.';
    if (empty($_POST['fecha_vencimiento']))
      $errores[] = 'La fecha de vencimiento es obligatoria.';
    if (!isset($_POST['cupos_disponibles']) || !is_numeric($_POST['cupos_disponibles']) || (int) $_POST['cupos_disponibles'] <= 0)
      $errores[] = 'Los cupos disponibles deben ser un número positivo.';

    $carreras_dirigidas_raw = json_decode($_POST['carreras_dirigidas'] ?? '[]', true);
    if (!is_array($carreras_dirigidas_raw) || empty($carreras_dirigidas_raw)) {
      $errores[] = 'Debe seleccionar al menos una carrera dirigida.';
    }

    if (!empty($errores)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
      break;
    }

    $datos_oferta = [
      'titulo' => $_POST['titulo'] ?? '',
      'descripcion' => $_POST['descripcion'] ?? '',
      'requisitos' => $_POST['requisitos'] ?? '',
      'beneficios' => $_POST['beneficios'] ?? NULL,
      'modalidad_id_modalidad' => $_POST['modalidad_id_modalidad'] ?? '',
      'tipo_oferta_id_tipo_oferta' => $_POST['tipo_oferta_id_tipo_oferta'] ?? '',
      'duracion_meses' => $_POST['duracion_meses'] ?? '',
      'horario' => $_POST['horario'] ?? NULL,
      'remuneracion' => $_POST['remuneracion'] ?? NULL,
      'area_conocimiento_id_area' => $_POST['area_conocimiento_id_area'] ?? '',
      'semestre_minimo' => $_POST['semestre_minimo'] ?? NULL,
      'promedio_minimo' => $_POST['promedio_minimo'] ?? NULL,
      'habilidades_requeridas' => $_POST['habilidades_requeridas'] ?? NULL,
      'fecha_inicio' => $_POST['fecha_inicio'] ?? NULL,
      'fecha_fin' => $_POST['fecha_fin'] ?? NULL,
      'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
      'cupos_disponibles' => $_POST['cupos_disponibles'] ?? 1,
      'empresa_idEmpresa' => $idEmpresa
    ];

    $resultado = $ofertaObj->registrar($datos_oferta);

    // Si la oferta se creó con éxito y se enviaron carreras, asociarlas.
    if ($resultado['success'] && isset($resultado['idOferta'])) {
      $idUltimaOferta = $resultado['idOferta']; // Usar el ID devuelto por el método registrar
      if (is_array($carreras_dirigidas_raw) && !empty($carreras_dirigidas_raw)) {
        $ofertaObj->asociarCarreras($idUltimaOferta, $carreras_dirigidas_raw);
      }
    }
    ob_end_clean();
    echo json_encode($resultado);
    break;

  case 'obtener_ofertas_empresa':
    $busqueda = $_GET['busqueda'] ?? '';
    $limite = $_GET['limite'] ?? 10;
    $offset = $_GET['offset'] ?? 0;

    error_log("DEBUG: Obtener ofertas para empresa ID: " . $idEmpresa . ", Búsqueda: " . $busqueda . ", Límite: " . $limite . ", Offset: " . $offset);

    $ofertas = $ofertaObj->obtenerOfertasPorEmpresa($idEmpresa, $busqueda, $limite, $offset);
    $total = $ofertaObj->contarOfertasPorEmpresa($idEmpresa, $busqueda);

    // Para cada oferta, obtener el conteo de interesados
    foreach ($ofertas as &$oferta) {
      $oferta['total_interesados'] = $ofertaObj->contarInteresadosPorOferta($oferta['idOferta']);
    }
    unset($oferta); // Romper la referencia al último elemento

    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $ofertas, 'total' => $total]);
    break;

  case 'obtener_oferta_por_id':
    $idOferta = $_GET['id'] ?? '';
    if (empty($idOferta)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada.']);
      break;
    }
    $oferta = $ofertaObj->obtenerPorId($idOferta);
    if ($oferta && $oferta['empresa_idEmpresa'] == $idEmpresa) {
      $carreras_asociadas = $ofertaObj->obtenerCarrerasAsociadas($idOferta);
      $oferta['carreras_asociadas'] = $carreras_asociadas;
      ob_end_clean();
      echo json_encode(['success' => true, 'data' => $oferta]);
    } else {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'Oferta no encontrada o no pertenece a esta empresa.']);
    }
    break;

  case 'actualizar_oferta':
    $idOferta = $_POST['idOferta'] ?? '';
    if (empty($idOferta)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada para actualizar.']);
      break;
    }

    $oferta_existente = $ofertaObj->obtenerPorId($idOferta);
    if (!$oferta_existente || $oferta_existente['empresa_idEmpresa'] != $idEmpresa) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'No tienes permiso para actualizar esta oferta.']);
      break;
    }

    // Validar campos obligatorios para la actualización
    $errores = [];
    if (empty($_POST['titulo']))
      $errores[] = 'El título es obligatorio.';
    if (empty($_POST['descripcion']))
      $errores[] = 'La descripción es obligatoria.';
    if (empty($_POST['modalidad_id_modalidad']))
      $errores[] = 'La modalidad es obligatoria.';
    if (empty($_POST['tipo_oferta_id_tipo_oferta']))
      $errores[] = 'El tipo de oferta es obligatorio.';
    if (empty($_POST['area_conocimiento_id_area']))
      $errores[] = 'El área de conocimiento es obligatoria.';
    if (empty($_POST['fecha_vencimiento']))
      $errores[] = 'La fecha de vencimiento es obligatoria.';
    if (!isset($_POST['cupos_disponibles']) || !is_numeric($_POST['cupos_disponibles']) || (int) $_POST['cupos_disponibles'] <= 0)
      $errores[] = 'Los cupos disponibles deben ser un número positivo.';

    $carreras_dirigidas_raw = json_decode($_POST['carreras_dirigidas'] ?? '[]', true);
    if (!is_array($carreras_dirigidas_raw) || empty($carreras_dirigidas_raw)) {
      $errores[] = 'Debe seleccionar al menos una carrera dirigida.';
    }

    if (!empty($errores)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => implode(' ', $errores)]);
      break;
    }

    $datos_actualizar = [
      'titulo' => $_POST['titulo'] ?? NULL,
      'descripcion' => $_POST['descripcion'] ?? NULL,
      'requisitos' => $_POST['requisitos'] ?? NULL,
      'beneficios' => $_POST['beneficios'] ?? NULL,
      'modalidad_id_modalidad' => $_POST['modalidad_id_modalidad'] ?? NULL,
      'tipo_oferta_id_tipo_oferta' => $_POST['tipo_oferta_id_tipo_oferta'] ?? NULL,
      'duracion_meses' => $_POST['duracion_meses'] ?? NULL,
      'horario' => $_POST['horario'] ?? NULL,
      'remuneracion' => $_POST['remuneracion'] ?? NULL,
      'area_conocimiento_id_area' => $_POST['area_conocimiento_id_area'] ?? NULL,
      'semestre_minimo' => $_POST['semestre_minimo'] ?? NULL,
      'promedio_minimo' => $_POST['promedio_minimo'] ?? NULL,
      'habilidades_requeridas' => $_POST['habilidades_requeridas'] ?? NULL,
      'fecha_inicio' => $_POST['fecha_inicio'] ?? NULL,
      'fecha_fin' => $_POST['fecha_fin'] ?? NULL,
      'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? NULL,
      'cupos_disponibles' => $_POST['cupos_disponibles'] ?? NULL,
      'estado_id_estado' => $_POST['estado_id_estado'] ?? NULL
    ];

    $resultado = $ofertaObj->actualizar($idOferta, $datos_actualizar);

    if ($resultado['success']) {
      if (is_array($carreras_dirigidas_raw)) {
        $ofertaObj->asociarCarreras($idOferta, $carreras_dirigidas_raw);
      }
    }
    ob_end_clean();
    echo json_encode($resultado);
    break;

  case 'desactivar_oferta':
    $idOferta = $_POST['idOferta'] ?? '';
    if (empty($idOferta)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada para desactivar.']);
      break;
    }

    $oferta_existente = $ofertaObj->obtenerPorId($idOferta);
    if (!$oferta_existente || $oferta_existente['empresa_idEmpresa'] != $idEmpresa) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'No tienes permiso para desactivar esta oferta.']);
      break;
    }

    $resultado = $ofertaObj->desactivar($idOferta);
    ob_end_clean();
    echo json_encode($resultado);
    break;

  case 'render_interesados_list_html':
    $idOferta = $_GET['idOferta'] ?? '';
    if (empty($idOferta)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada.']);
      break;
    }
    // Verificar que la oferta pertenece a la empresa logueada
    $oferta = $ofertaObj->obtenerPorId($idOferta);
    if (!$oferta || $oferta['empresa_idEmpresa'] != $idEmpresa) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver los interesados de esta oferta.']);
      break;
    }

    $estudiantes_interesados = $ofertaObj->obtenerEstudiantesInteresados($idOferta);

    $html_list = '';
    if (!empty($estudiantes_interesados)) {
      foreach ($estudiantes_interesados as $estudiante) {
        $html_list .= '
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="#" class="text-decoration-none text-primary fw-bold" onclick="viewStudentProfileForCompany(\'' . htmlspecialchars($estudiante['idEstudiante']) . '\', this); return false;">
                        <i class="fas fa-user-circle me-2"></i>' . htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']) . '
                    </a>
                    <span class="badge bg-info rounded-pill">' . htmlspecialchars($estudiante['carrera_nombre']) . '</span>
                </li>';
      }
    } else {
      $html_list = '<li class="list-group-item text-center text-muted py-3">No hay estudiantes interesados en esta oferta.</li>';
    }
    ob_end_clean();
    echo json_encode(['success' => true, 'html' => $html_list]);
    break;

  case 'render_perfil_estudiante_html':
    $idEstudiante = $_GET['id'] ?? '';
    if (empty($idEstudiante)) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'ID de estudiante no proporcionada.']);
      break;
    }

    // Obtener el perfil del estudiante
    $estudiante_data = $estudianteObj->obtenerPorIdParaEmpresa($idEstudiante);

    if ($estudiante_data) {
      $html_content = '
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="mb-2"><strong><i class="fas fa-user me-2"></i>Nombre:</strong> ' . htmlspecialchars($estudiante_data['nombre'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-user me-2"></i>Apellidos:</strong> ' . htmlspecialchars($estudiante_data['apellidos'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-envelope me-2"></i>Correo:</strong> ' . htmlspecialchars($estudiante_data['correo'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-phone me-2"></i>Teléfono:</strong> ' . htmlspecialchars($estudiante_data['telefono'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-id-card me-2"></i>Documento:</strong> ' . htmlspecialchars($estudiante_data['tipo_documento_nombre'] ?? 'N/A') . ' - ' . htmlspecialchars($estudiante_data['n_doc'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-map-marker-alt me-2"></i>Dirección:</strong> ' . htmlspecialchars($estudiante_data['direccion'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-city me-2"></i>Ciudad:</strong> ' . htmlspecialchars($estudiante_data['ciudad_nombre'] ?? 'N/A') . '</div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="mb-2"><strong><i class="fas fa-calendar-alt me-2"></i>Fecha Nacimiento:</strong> ' . htmlspecialchars($estudiante_data['fechaNac'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-graduation-cap me-2"></i>Código Estudiante:</strong> ' . htmlspecialchars($estudiante_data['codigo_estudiante'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-book me-2"></i>Carrera:</strong> ' . htmlspecialchars($estudiante_data['carrera_nombre'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-layer-group me-2"></i>Semestre:</strong> ' . htmlspecialchars($estudiante_data['semestre'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-percentage me-2"></i>Promedio:</strong> ' . htmlspecialchars($estudiante_data['promedio_academico'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-clock me-2"></i>Disponibilidad:</strong> ' . htmlspecialchars($estudiante_data['disponibilidad_nombre'] ?? 'N/A') . '</div>
                    <div class="mb-2"><strong><i class="fas fa-info-circle me-2"></i>Estado:</strong> <span class="badge bg-' . (($estudiante_data['estado_nombre'] ?? '') === 'activo' ? 'success' : 'secondary') . '">' . htmlspecialchars(strtoupper($estudiante_data['estado_nombre'] ?? 'N/A')) . '</span></div>
                </div>
            </div>
            <div class="mt-3">
                <h6><strong><i class="fas fa-lightbulb me-2"></i>Habilidades:</strong></h6>
                <p class="text-muted">' . htmlspecialchars($estudiante_data['habilidades'] ?? 'No especificado') . '</p>
            </div>
            <div class="mt-3">
                <h6><strong><i class="fas fa-briefcase me-2"></i>Experiencia Laboral:</strong></h6>
                <p class="text-muted">' . htmlspecialchars($estudiante_data['experiencia_laboral'] ?? 'No especificado') . '</p>
            </div>
            <div class="mt-3">
                <h6><strong><i class="fas fa-certificate me-2"></i>Certificaciones:</strong></h6>
                <p class="text-muted">' . htmlspecialchars($estudiante_data['certificaciones'] ?? 'No especificado') . '</p>
            </div>
            <div class="mt-3">
                <h6><strong><i class="fas fa-language me-2"></i>Idiomas:</strong></h6>
                <p class="text-muted">' . htmlspecialchars($estudiante_data['idiomas'] ?? 'No especificado') . '</p>
            </div>
            <div class="mt-3">
                <h6><strong><i class="fas fa-bullseye me-2"></i>Objetivos Profesionales:</strong></h6>
                <p class="text-muted">' . htmlspecialchars($estudiante_data['objetivos_profesionales'] ?? 'No especificado') . '</p>
            </div>
            <div class="mt-3">
                <h6><strong><i class="fas fa-cogs me-2"></i>Carreras de Interés:</strong></h6>
                <ul class="list-group list-group-flush">';
      if (!empty($estudiante_data['carreras_interes_nombres'])) {
        foreach ($estudiante_data['carreras_interes_nombres'] as $carrera_interes) {
          $html_content .= '<li class="list-group-item">' . htmlspecialchars($carrera_interes) . '</li>';
        }
      } else {
        $html_content .= '<li class="list-group-item text-muted">No hay carreras de interés especificadas.</li>';
      }
      $html_content .= '</ul></div>';

      ob_end_clean(); // Limpiar el búfer antes de enviar la respuesta JSON
      echo json_encode(['success' => true, 'html' => $html_content, 'data' => ['nombre' => $estudiante_data['nombre'], 'apellidos' => $estudiante_data['apellidos']]]);
    } else {
      ob_end_clean(); // Limpiar el búfer antes de enviar la respuesta JSON
      echo json_encode(['success' => false, 'message' => 'Perfil de estudiante no encontrado.']);
    }
    break;


  // Acciones para el perfil de la empresa
  case 'obtener_empresa_perfil':
    $empresa_id = $_GET['id'] ?? $idEmpresa; // Usar ID de sesión por defecto
    $empresa_data = $empresaObj->obtenerPorId($empresa_id);
    if ($empresa_data) {
      ob_end_clean();
      echo json_encode(['success' => true, 'data' => $empresa_data]);
    } else {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'Empresa no encontrada.']);
    }
    break;

  case 'actualizar_empresa_perfil':
    $empresa_id_to_update = $_POST['idEmpresa'] ?? '';
    if (empty($empresa_id_to_update) || $empresa_id_to_update != $idEmpresa) {
      ob_end_clean();
      echo json_encode(['success' => false, 'message' => 'ID de empresa no proporcionada o no coincide con la sesión.']);
      break;
    }

    $datos_empresa = [
      'nombre' => $_POST['nombre'] ?? NULL,
      'correo' => $_POST['correo'] ?? NULL,
      'telefono' => $_POST['telefono'] ?? NULL,
      'direccion' => $_POST['direccion'] ?? NULL,
      'n_doc' => $_POST['n_doc'] ?? NULL,
      'tipo_documento_id_tipo' => $_POST['tipo_documento_id_tipo'] ?? NULL,
      'ciudad_id_ciudad' => $_POST['ciudad_id_ciudad'] ?? NULL,
      'descripcion' => $_POST['descripcion'] ?? NULL,
      'sector_id_sector' => $_POST['sector_id_sector'] ?? NULL,
      'sitio_web' => $_POST['sitio_web'] ?? NULL,
      'numero_empleados' => $_POST['numero_empleados'] ?? NULL,
      'ano_fundacion' => $_POST['ano_fundacion'] ?? NULL,
      'contacto_nombres' => $_POST['contacto_nombres'] ?? NULL,
      'contacto_apellidos' => $_POST['contacto_apellidos'] ?? NULL,
      'contacto_cargo' => $_POST['contacto_cargo'] ?? NULL,
      'estado_id_estado' => $_POST['estado_id_estado'] ?? NULL
    ];

    $resultado = $empresaObj->actualizar($empresa_id_to_update, $datos_empresa);
    ob_end_clean();
    echo json_encode($resultado);
    break;

  // Acciones para obtener datos de selectores
  case 'obtener_modalidades':
    $modalidades = $ofertaObj->obtenerModalidades();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $modalidades]);
    break;

  case 'obtener_tipos_oferta':
    $tipos_oferta = $ofertaObj->obtenerTiposOferta();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $tipos_oferta]);
    break;

  case 'obtener_areas_conocimiento':
    $areas_conocimiento = $ofertaObj->obtenerAreasConocimiento();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $areas_conocimiento]);
    break;

  case 'obtener_estados':
    $estados = $ofertaObj->obtenerEstados();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $estados]);
    break;

  case 'obtener_carreras':
    $carreras = $ofertaObj->obtenerCarreras();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $carreras]);
    break;

  case 'obtener_tipos_documento':
    $tipos_documento = $empresaObj->obtenerTiposDocumento();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $tipos_documento]);
    break;

  case 'obtener_ciudades':
    $ciudades = $empresaObj->obtenerCiudades();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $ciudades]);
    break;

  case 'obtener_sectores':
    $sectores = $empresaObj->obtenerSectores();
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $sectores]);
    break;

  default:
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}