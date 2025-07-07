<?php
ob_start(); // Iniciar el búfer de salida para capturar cualquier advertencia o error de PHP
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mantener en 1 para depuración, cambiar a 0 en producción

header('Content-Type: application/json');

try {
  require_once '../MODELO/class_conec.php'; // Necesario para la conexión a la BD
  // Habilitar el reporte de errores de MySQLi para que lance excepciones en lugar de advertencias
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  require_once '../MODELO/class_administrador.php'; // Para la validación de estado del administrador
  require_once '../MODELO/class_catalogo.php'; // Para obtener nombres de catálogos
  require_once '../MODELO/class_oferta.php'; // Incluir la clase Oferta
  require_once '../MODELO/class_estudiante.php'; // Incluir la clase Estudiante
  require_once '../MODELO/class_empresa.php'; // Incluir la clase Empresa
  require_once '../MODELO/class_referencia.php'; // Incluir la clase Referencia
} catch (Throwable $e) {
  error_log("ERROR CRÍTICO en ajax_reportes.php al cargar módulos: " . $e->getMessage() . " en línea " . $e->getLine());
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Error crítico del servidor al cargar módulos: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')']);
  exit();
}

// Verificar autenticación y rol de administrador
$tiempoInactividad = 500; // Segundos
if (isset($_SESSION['timeout'])) {
  $_vidaSesion = time() - $_SESSION['timeout'];
  if ($_vidaSesion > $tiempoInactividad) {
    session_destroy();
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
  error_log("FALLO DE AUTENTICACIÓN (ajax_reportes): usuario_id establecido? " . (isset($_SESSION['usuario_id']) ? 'Sí' : 'No') . " | rol es administrador? " . (($_SESSION['rol'] ?? 'ninguno') === 'administrador' ? 'Sí' : 'No'));
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debe iniciar sesión como Administrador en el Sistema.', 'redirect' => '../index.php']);
  exit();
}

// Validación de estado del administrador
$administradorObj = new Administrador();
$idAdmin = $_SESSION['usuario_id'];
$datosAdmin = $administradorObj->obtenerPorId($idAdmin);
$idInactivo = $administradorObj->getIdEstadoPorNombre('inactivo');

if ($datosAdmin && $idInactivo !== false && $datosAdmin['estado_id_estado'] == $idInactivo) {
  session_destroy();
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Su cuenta ha sido desactivada. Por favor, inicie sesión nuevamente.', 'redirect' => '../index.php']);
  exit();
}

// Instanciar clases de modelo
$ofertaObj = new Oferta();
$estudianteObj = new Estudiante();
$empresaObj = new Empresa();
$referenciaObj = new Referencia();
$catalogoObj = new Catalogo(); // Para obtener nombres de catálogos

$accion = $_GET['action'] ?? $_POST['action'] ?? '';
$tipoReporte = $_GET['tipo_reporte'] ?? $_POST['tipo_reporte'] ?? '';

$respuesta = ['success' => false, 'message' => ''];

switch ($accion) {
  case 'generar_reporte':
    $htmlReporte = '';
    $datosReporte = [];
    $tituloReporte = 'Reporte Desconocido';

    try {
      switch ($tipoReporte) {
        case 'ofertas_por_fecha':
          $fechaInicio = $_GET['fecha_inicio'] ?? '';
          $fechaFin = $_GET['fecha_fin'] ?? '';

          if (empty($fechaInicio) || empty($fechaFin)) {
            throw new Exception("Fechas de inicio y fin son requeridas para este reporte.");
          }

          $tituloReporte = "Ofertas Publicadas entre $fechaInicio y $fechaFin";
          $datosReporte = $ofertaObj->obtenerOfertasPorRangoFecha($fechaInicio, $fechaFin);

          $htmlReporte .= '<table class="table table-bordered table-striped table-hover">';
          $htmlReporte .= '<thead class="table-dark"><tr><th>ID</th><th>Título</th><th>Empresa</th><th>Modalidad</th><th>Tipo</th><th>Publicación</th><th>Vencimiento</th><th>Estado</th></tr></thead>';
          $htmlReporte .= '<tbody>';
          foreach ($datosReporte as $fila) {
            $htmlReporte .= '<tr>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['idOferta'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['titulo'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['empresa_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['modalidad_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['tipo_oferta_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['fecha_publicacion'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['fecha_vencimiento'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['estado_nombre'] ?? '') . '</td>';
            $htmlReporte .= '</tr>';
          }
          $htmlReporte .= '</tbody></table>';
          $respuesta['success'] = true;
          $respuesta['message'] = 'Reporte de ofertas generado exitosamente.';
          break;

        case 'estudiantes_registrados':
          $tituloReporte = "Estudiantes Registrados";
          $datosReporte = $estudianteObj->obtenerEstudiantesRegistrados();

          $htmlReporte .= '<table class="table table-bordered table-striped table-hover">';
          $htmlReporte .= '<thead class="table-dark"><tr><th>ID</th><th>Nombre Completo</th><th>Documento</th><th>Carrera</th><th>Estado</th><th>Fecha Registro</th></tr></thead>';
          $htmlReporte .= '<tbody>';
          foreach ($datosReporte as $fila) {
            $htmlReporte .= '<tr>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['idEstudiante'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars(($fila['nombre'] ?? '') . ' ' . ($fila['apellidos'] ?? '')) . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['n_doc'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['carrera_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['estado_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['fecha_registro'] ?? '') . '</td>';
            $htmlReporte .= '</tr>';
          }
          $htmlReporte .= '</tbody></table>';
          $respuesta['success'] = true;
          $respuesta['message'] = 'Reporte de estudiantes generado exitosamente.';
          break;

        case 'estudiantes_por_carrera':
          $idCarrera = $_GET['id_carrera'] ?? '';
          $carreraNombre = 'Todas las Carreras';
          if (!empty($idCarrera)) {
            $carreraData = $catalogoObj->obtenerPorId('carrera', $idCarrera);
            if ($carreraData) {
              $carreraNombre = htmlspecialchars($carreraData['nombre'] ?? '');
            }
          }
          $tituloReporte = "Estudiantes por Carrera: $carreraNombre";
          $datosReporte = $estudianteObj->obtenerEstudiantesPorCarrera($idCarrera);

          $htmlReporte .= '<table class="table table-bordered table-striped table-hover">';
          $htmlReporte .= '<thead class="table-dark"><tr><th>ID</th><th>Nombre Completo</th><th>Documento</th><th>Carrera</th><th>Estado</th><th>Fecha Registro</th></tr></thead>';
          $htmlReporte .= '<tbody>';
          foreach ($datosReporte as $fila) {
            $htmlReporte .= '<tr>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['idEstudiante'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars(($fila['nombre'] ?? '') . ' ' . ($fila['apellidos'] ?? '')) . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['n_doc'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['carrera_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['estado_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['fecha_registro'] ?? '') . '</td>';
            $htmlReporte .= '</tr>';
          }
          $htmlReporte .= '</tbody></table>';
          $respuesta['success'] = true;
          $respuesta['message'] = 'Reporte de estudiantes por carrera generado exitosamente.';
          break;

        case 'empresas_por_estado':
          $idEstado = $_GET['id_estado'] ?? '';
          $estadoNombre = 'Todos los Estados';
          if (!empty($idEstado)) {
            $estadoData = $catalogoObj->obtenerPorId('estado', $idEstado);
            if ($estadoData) {
              $estadoNombre = htmlspecialchars($estadoData['nombre'] ?? '');
            }
          }
          $tituloReporte = "Empresas por Estado: $estadoNombre";
          $datosReporte = $empresaObj->obtenerEmpresasPorEstado($idEstado);

          $htmlReporte .= '<table class="table table-bordered table-striped table-hover">';
          $htmlReporte .= '<thead class="table-dark"><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Estado</th><th>Fecha Creación</th></tr></thead>';
          $htmlReporte .= '<tbody>';
          foreach ($datosReporte as $fila) {
            $htmlReporte .= '<tr>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['idEmpresa'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['correo'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['telefono'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['estado_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['fecha_creacion'] ?? '') . '</td>';
            $htmlReporte .= '</tr>';
          }
          $htmlReporte .= '</tbody></table>';
          $respuesta['success'] = true;
          $respuesta['message'] = 'Reporte de empresas por estado generado exitosamente.';
          break;

        case 'top_ofertas_interes':
          $limiteTop = isset($_GET['limite_top']) ? (int) $_GET['limite_top'] : 5; // Valor por defecto 5
          $tituloReporte = "Top $limiteTop Ofertas con Más Estudiantes Interesados";
          $datosReporte = $ofertaObj->obtenerTopOfertasInteres($limiteTop);

          $htmlReporte .= '<table class="table table-bordered table-striped table-hover">';
          $htmlReporte .= '<thead class="table-dark"><tr><th>ID Oferta</th><th>Título</th><th>Empresa</th><th>Interesados</th></tr></thead>';
          $htmlReporte .= '<tbody>';
          foreach ($datosReporte as $fila) {
            $htmlReporte .= '<tr>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['idOferta'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['titulo'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['empresa_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['total_interesados'] ?? '') . '</td>';
            $htmlReporte .= '</tr>';
          }
          $htmlReporte .= '</tbody></table>';
          $respuesta['success'] = true;
          $respuesta['message'] = 'Reporte Top ofertas generado exitosamente.';
          break;

        case 'referencias_por_estado':
          $idEstado = $_GET['id_estado'] ?? '';
          $estadoNombre = 'Todos los Estados';
          if (!empty($idEstado)) {
            $estadoData = $catalogoObj->obtenerPorId('estado', $idEstado);
            if ($estadoData) {
              $estadoNombre = htmlspecialchars($estadoData['nombre'] ?? '');
            }
          }
          $tituloReporte = "Referencias por Estado: $estadoNombre";
          $datosReporte = $referenciaObj->obtenerReferenciasPorEstado($idEstado);

          $htmlReporte .= '<table class="table table-bordered table-striped table-hover">';
          $htmlReporte .= '<thead class="table-dark"><tr><th>ID</th><th>Estudiante</th><th>Empresa</th><th>Tipo Referencia</th><th>Estado</th><th>Fecha Solicitud</th></tr></thead>';
          $htmlReporte .= '<tbody>';
          foreach ($datosReporte as $fila) {
            $htmlReporte .= '<tr>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['idReferencia'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars(($fila['estudiante_nombre'] ?? '') . ' ' . ($fila['estudiante_apellidos'] ?? '')) . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['empresa_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['tipo_referencia_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['estado_nombre'] ?? '') . '</td>';
            $htmlReporte .= '<td>' . htmlspecialchars($fila['fecha_solicitud'] ?? '') . '</td>';
            $htmlReporte .= '</tr>';
          }
          $htmlReporte .= '</tbody></table>';
          $respuesta['success'] = true;
          $respuesta['message'] = 'Reporte de referencias generado exitosamente.';
          break;

        default:
          throw new Exception('Tipo de reporte no válido.');
          break;
      }
      $respuesta['html'] = $htmlReporte;
      $respuesta['datos'] = $datosReporte; // Enviar los datos brutos para el PDF
      $respuesta['titulo'] = $tituloReporte;
      break;

    } catch (Exception $e) {
      $respuesta['success'] = false;
      $respuesta['message'] = $e->getMessage();
      error_log("ERROR en generar_reporte ($tipoReporte): " . $e->getMessage() . " en línea " . $e->getLine());
    }
    break;

  default:
    $respuesta['message'] = 'Acción no válida.';
    break;
}

ob_clean(); // Limpiar el búfer antes de enviar la respuesta final
echo json_encode($respuesta);
exit(); // Asegurar que no se imprima nada más