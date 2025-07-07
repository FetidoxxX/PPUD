<?php
session_start();
$inn = 500;
if (isset($_SESSION['timeout'])) {
  $_session_life = time() - $_SESSION['timeout'];
  if ($_session_life > $inn) {
    session_destroy();
    header("location:../index.php");
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!$_SESSION['usuario']) {
  $_SESSION['usuario'] = NULL;
  ?>
  <!DOCTYPE html>
  <html>

  <head>
    <meta charset='utf-8'>
    <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
    <script src='../sw/dist/sweetalert2.min.js'></script>
  </head>

  <body>
    <script type='text/javascript'>
      Swal.fire({
        icon: 'error',
        title: 'ERROR!!',
        text: ' Debe iniciar Session en el Sistema'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location = '../index.php';
        }
      });
    </script>
  </body>

  </html>
  <?php
  exit();
}

// Validación de estado de administrador
include_once '../MODELO/class_administrador.php';
include_once '../MODELO/class_catalogo.php'; // Para obtener datos de catálogos
$administradorObj = new Administrador();
$catalogoObj = new Catalogo();

if (isset($_SESSION['usuario_id'])) {
  $admin_id = $_SESSION['usuario_id'];
  $admin_data = $administradorObj->obtenerPorId($admin_id);
  $inactivo_id = $administradorObj->getIdEstadoPorNombre('inactivo');

  if ($admin_data && $inactivo_id !== false && $admin_data['estado_id_estado'] == $inactivo_id) {
    session_destroy();
    ?>
    <!DOCTYPE html>
    <html>

    <head>
      <meta charset='utf-8'>
      <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
      <script src='../sw/dist/sweetalert2.min.js'></script>
    </head>

    <body>
      <script type='text/javascript'>
        Swal.fire({
          icon: 'error',
          title: 'Acceso Denegado',
          text: 'Su cuenta ha sido desactivada. Por favor, contacte al administrador.'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location = '../index.php';
          }
        });
      </script>
    </body>

    </html>
    <?php
    exit();
  }
}

// Obtener datos para filtros dinámicos
$estados = $catalogoObj->listarTodos('estado');
$carreras = $catalogoObj->listarTodos('carrera');
$empresas = $catalogoObj->listarTodos('empresa'); // Asumiendo que existe un listarTodos en Empresa o se adapta Catalogo
$tipos_oferta = $catalogoObj->listarTodos('tipo_oferta');
$tipos_referencia = $catalogoObj->listarTodos('tipo_referencia');

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Reportes</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../sw/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="pruebaAdmin.php">Panel de Administración</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="pruebaAdmin.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_estudiantes.php">Gestión Estudiantes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_empresas.php">Gestión Empresas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_ofertas.php">Gestión Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_referencias.php">Gestión Referencias</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_admin.php">Gestión Administradores</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_varios.php">Gestión Varios</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="gestion_reportes.php">Reportes</a>
          </li>
        </ul>

        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
              Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" onclick="mostrarPerfil()">Mi Perfil</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <form action="../salir.php" method="post" class="d-inline">
                  <button type="submit" class="dropdown-item text-danger">Cerrar Sesión</button>
                </form>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Jumbotron de bienvenida -->
  <div class="bg-info text-white py-4 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-5 fw-bold">📊 Módulo de Reportes</h1>
          <p class="lead">Genera informes completos y detallados del sistema</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenido principal -->
  <div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="pruebaAdmin.php">Panel de Administración</a></li>
        <li class="breadcrumb-item active" aria-current="page">Reportes</li>
      </ol>
    </nav>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0">⚙️ Opciones de Reporte</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="tipoReporte" class="form-label">Tipo de Reporte:</label>
            <select class="form-select" id="tipoReporte">
              <option value="">Seleccione un tipo de reporte...</option>
              <option value="ofertas_por_fecha">Ofertas por Rango de Fechas</option>
              <option value="estudiantes_registrados">Estudiantes Registrados (General)</option>
              <option value="estudiantes_por_carrera">Estudiantes por Carrera</option>
              <option value="empresas_por_estado">Empresas por Estado</option>
              <option value="top_ofertas_interes">Top 5 Ofertas con Más Interesados</option>
              <option value="referencias_por_estado">Referencias por Estado</option>
            </select>
          </div>

          <div class="col-md-3" id="filtroFechaInicio" style="display: none;">
            <label for="fechaInicio" class="form-label">Fecha Inicio:</label>
            <input type="date" class="form-control" id="fechaInicio">
          </div>
          <div class="col-md-3" id="filtroFechaFin" style="display: none;">
            <label for="fechaFin" class="form-label">Fecha Fin:</label>
            <input type="date" class="form-control" id="fechaFin">
          </div>

          <div class="col-md-6" id="filtroCarrera" style="display: none;">
            <label for="idCarrera" class="form-label">Carrera:</label>
            <select class="form-select" id="idCarrera">
              <option value="">Todas las Carreras</option>
              <?php foreach ($carreras as $carrera): ?>
                <option value="<?php echo htmlspecialchars($carrera['id_carrera']); ?>">
                  <?php echo htmlspecialchars($carrera['nombre']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6" id="filtroEstado" style="display: none;">
            <label for="idEstado" class="form-label">Estado:</label>
            <select class="form-select" id="idEstado">
              <option value="">Todos los Estados</option>
              <?php foreach ($estados as $estado): ?>
                <option value="<?php echo htmlspecialchars($estado['id_estado']); ?>">
                  <?php echo htmlspecialchars($estado['nombre']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 text-end">
            <button class="btn btn-primary px-4 rounded-pill" id="btnGenerarReporte">
              <i class="fas fa-file-alt me-2"></i>Generar Reporte
            </button>
            <button class="btn btn-success px-4 rounded-pill ms-2" id="btnDescargarPDF" style="display: none;">
              <i class="fas fa-file-pdf me-2"></i>Descargar PDF
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="contenedorReporte" style="display: none;">
      <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">📄 Resultado del Reporte</h5>
      </div>
      <div class="card-body">
        <div id="areaReporte">
          <!-- El contenido del reporte se cargará aquí -->
          <p class="text-muted text-center py-5">Seleccione un tipo de reporte y genere para visualizarlo aquí.</p>
        </div>
      </div>
    </div>

  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Gestión Administrativa. Todos los derechos
            reservados.</p>
          <small class="text-muted">Módulo de Reportes - Desarrollado con Bootstrap</small>
        </div>
      </div>
    </div>
  </footer>

  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <!-- jsPDF y html2canvas para la descarga de PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <!-- ¡IMPORTANTE! Cargar jspdf-autotable ANTES de funcionesReportes.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="../js/funcionesReportes.js"></script>
  <script>
    // Pasar variables PHP a JavaScript
    const GLOBAL_CARRERAS = <?php echo json_encode($carreras); ?>;
    const GLOBAL_ESTADOS = <?php echo json_encode($estados); ?>;
    const GLOBAL_EMPRESAS = <?php echo json_encode($empresas); ?>;
    const GLOBAL_TIPOS_OFERTA = <?php echo json_encode($tipos_oferta); ?>;
    const GLOBAL_TIPOS_REFERENCIA = <?php echo json_encode($tipos_referencia); ?>;


    $(document).ready(function () {
      inicializarReportes();
    });

    // Función para mostrar perfil (ya existente en pruebaAdmin.php)
    function mostrarPerfil() {
      Swal.fire({
        title: 'Perfil de Usuario',
        html: `
          <div class="text-start">
            <div class="mb-2"><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?></div>
            <div class="mb-2"><strong>Tipo:</strong> <span class="badge bg-primary">Administrador</span></div>
            <div class="mb-2"><strong>Sesión iniciada:</strong> <?php echo date('d/m/Y H:i:s'); ?></div>
            <div class="mb-2"><strong>Estado:</strong> <span class="badge bg-success">Activo</span></div>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#0d6efd'
      });
    }
  </script>
</body>

</html>