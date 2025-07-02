<?php
session_start();

// Habilitar la visualización de errores para depuración (QUITAR EN PRODUCCIÓN)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// --- INICIO ROBUSTA INCLUSIÓN DE ARCHIVOS Y MANEJO DE SESIÓN/AUTENTICACIÓN ---
try {
  // Ya no es necesario incluir class_conec.php aquí, ya que las clases lo manejan internamente.
  require_once '../MODELO/class_oferta.php';
  require_once '../MODELO/class_empresa.php'; // Necesario para obtener perfil de empresa
  require_once '../MODELO/class_estudiante.php'; // Para registrar el interés

  // Verificar si la sesión de usuario está activa y es un estudiante
  $inn = 500; // Tiempo de inactividad
  if (isset($_SESSION['timeout'])) {
    $_session_life = time() - $_SESSION['timeout'];
    if ($_session_life > $inn) {
      session_destroy();
      error_log("DEBUG (ajax_ofertasE): Sesión expirada para usuario ID: " . ($_SESSION['usuario_id'] ?? 'N/A'));
      echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
      exit();
    }
  }
  $_SESSION['timeout'] = time();

  if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
    error_log("DEBUG (ajax_ofertasE): Acceso denegado. Usuario ID: " . ($_SESSION['usuario_id'] ?? 'N/A') . ", Rol: " . ($_SESSION['rol'] ?? 'N/A'));
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Estudiante en el Sistema.', 'redirect' => '../index.php']);
    exit();
  }

  // Crear instancias de las clases. Cada clase manejará su propia conexión internamente.
  $ofertaObj = new Oferta();
  $empresaObj = new Empresa();
  $estudianteObj = new Estudiante(); // Instancia para manejar intereses

} catch (Throwable $e) {
  error_log("FATAL ERROR in ajax_ofertaE.php initial setup: " . $e->getMessage() . " on line " . $e->getLine());
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}
// --- FIN ROBUSTA INCLUSIÓN DE ARCHIVOS Y MANEJO DE SESIÓN/AUTENTICACIÓN ---

$idEstudiante = $_SESSION['usuario_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
  case 'obtener_ofertas_activas':
    $busqueda = $_GET['busqueda'] ?? '';
    $limite = $_GET['limite'] ?? 9; // Límite para mostrar en cards
    $offset = $_GET['offset'] ?? 0;

    // --- LOGGING PARA DEPURACIÓN ---
    error_log("DEBUG (ajax_ofertasE): Recibida acción 'obtener_ofertas_activas'");
    error_log("DEBUG (ajax_ofertasE): Búsqueda: '" . $busqueda . "', Límite: " . $limite . ", Offset: " . $offset);
    // --- FIN LOGGING ---

    $ofertas = $ofertaObj->obtenerOfertasActivas($busqueda, $limite, $offset);
    $total = $ofertaObj->contarOfertasActivas($busqueda);

    // --- LOGGING PARA DEPURACIÓN ---
    error_log("DEBUG (ajax_ofertasE): Cantidad de ofertas obtenidas de DB: " . count($ofertas));
    error_log("DEBUG (ajax_ofertasE): Total de ofertas en DB: " . $total);
    // Puedes descomentar la siguiente línea para ver el contenido completo de las ofertas (¡solo para depuración, no en producción!)
    // error_log("DEBUG (ajax_ofertasE): Contenido de ofertas: " . json_encode($ofertas));
    // --- FIN LOGGING ---

    // Adjuntar la información de si el estudiante ha mostrado interés en cada oferta
    foreach ($ofertas as &$o) {
      $o['interes_mostrado'] = $estudianteObj->haMostradoInteres($idEstudiante, $o['idOferta']);
    }
    unset($o); // Romper la referencia al último elemento

    echo json_encode(['success' => true, 'data' => $ofertas, 'total' => $total]);
    break;

  case 'obtener_oferta_detalle':
    $idOferta = $_GET['id'] ?? '';
    if (empty($idOferta)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada.']);
      break;
    }
    $oferta = $ofertaObj->obtenerPorId($idOferta);
    if ($oferta) {
      // También obtener las carreras asociadas
      $carreras_asociadas = $ofertaObj->obtenerCarrerasAsociadas($idOferta);
      $nombres_carreras = [];
      foreach ($carreras_asociadas as $carrera_id) {
        $carrera_data = $ofertaObj->obtenerCarreraPorId($carrera_id); // Asumiendo que existe un método para esto
        if ($carrera_data) {
          $nombres_carreras[] = $carrera_data['nombre'];
        }
      }
      $oferta['carreras_dirigidas_nombres'] = implode(', ', $nombres_carreras);
      $oferta['interes_mostrado'] = $estudianteObj->haMostradoInteres($idEstudiante, $idOferta);

      echo json_encode(['success' => true, 'data' => $oferta]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Oferta no encontrada.']);
    }
    break;

  case 'obtener_perfil_empresa':
    $idEmpresa = $_GET['id'] ?? '';

    // --- LOGGING PARA DEPURACIÓN ---
    error_log("DEBUG (ajax_ofertasE): Recibida acción 'obtener_perfil_empresa' con ID: " . $idEmpresa);
    // --- FIN LOGGING ---

    if (empty($idEmpresa)) {
      error_log("ERROR (ajax_ofertasE): ID de empresa vacío en obtener_perfil_empresa");
      echo json_encode(['success' => false, 'message' => 'ID de empresa no proporcionada.']);
      break;
    }

    try {
      $empresa_data = $empresaObj->obtenerPorId($idEmpresa);

      // --- LOGGING PARA DEPURACIÓN ---
      error_log("DEBUG (ajax_ofertasE): Datos de empresa obtenidos: " . ($empresa_data ? "SÍ" : "NO"));
      if ($empresa_data) {
        error_log("DEBUG (ajax_ofertasE): Nombre empresa: " . ($empresa_data['nombre'] ?? 'N/A'));
      }
      // --- FIN LOGGING ---

      if ($empresa_data) {
        // Verificar y escapar todos los campos para evitar problemas de HTML
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

        // Manejo especial para sitio web
        $sitio_web = 'N/A';
        if (!empty($empresa_data['sitio_web'])) {
          $url = htmlspecialchars($empresa_data['sitio_web']);
          // Agregar http:// si no tiene protocolo
          if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
          }
          $sitio_web = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($empresa_data['sitio_web']) . '</a>';
        }

        // Formatear la salida para la visualización en el modal
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

        error_log("DEBUG (ajax_ofertasE): HTML generado correctamente para empresa ID: " . $idEmpresa);
        echo json_encode(['success' => true, 'html' => $html_content]);

      } else {
        error_log("ERROR (ajax_ofertasE): No se encontraron datos para empresa ID: " . $idEmpresa);
        echo json_encode(['success' => false, 'message' => 'Perfil de empresa no encontrado.']);
      }

    } catch (Exception $e) {
      error_log("ERROR (ajax_ofertasE): Excepción en obtener_perfil_empresa: " . $e->getMessage() . " en línea " . $e->getLine());
      echo json_encode(['success' => false, 'message' => 'Error al obtener el perfil de la empresa: ' . $e->getMessage()]);
    }
    break;

  case 'mostrar_interes':
    $idOferta = $_POST['idOferta'] ?? '';
    if (empty($idOferta)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada para mostrar interés.']);
      break;
    }

    $resultado = $estudianteObj->mostrarInteresOferta($idEstudiante, $idOferta);
    echo json_encode($resultado);
    break;

  case 'eliminar_interes':
    $idOferta = $_POST['idOferta'] ?? '';
    if (empty($idOferta)) {
      echo json_encode(['success' => false, 'message' => 'ID de oferta no proporcionada para eliminar interés.']);
      break;
    }

    $resultado = $estudianteObj->eliminarInteresOferta($idEstudiante, $idOferta);
    echo json_encode($resultado);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}

?>