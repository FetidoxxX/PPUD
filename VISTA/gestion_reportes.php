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
include_once '../MODELO/class_empresa.php'; // Para obtener empresas
include_once '../MODELO/class_estudiante.php'; // Para obtener estudiantes
$administradorObj = new Administrador();
$catalogoObj = new Catalogo();
$empresaObj = new Empresa();
$estudianteObj = new Estudiante();


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
$empresas = $empresaObj->obtenerTodos(); // Usar el método de Empresa para obtener todas las empresas
$modalidades = $catalogoObj->listarTodos('modalidad');
$tipos_oferta = $catalogoObj->listarTodos('tipo_oferta');
$tipos_referencia = $catalogoObj->listarTodos('tipo_referencia');
$estudiantes = $estudianteObj->obtenerTodos(); // Obtener todos los estudiantes

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
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Custom styles for aesthetic improvements -->
  <link rel="stylesheet" href="../css/estiloMenu.css">
</head>

<body>
  <!-- Barra de navegación superior (para el logo y el perfil de usuario) -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-lg">
    <div class="container-fluid px-4">
      <button class="btn btn-dark" id="menu-toggle">
        <i class="fas fa-bars"></i>
      </button>
      <a class="navbar-brand fw-bold text-lg ms-3" href="pruebaAdmin.php">Panel de Administración</a>

      <div class="collapse navbar-collapse" id="topNavbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
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

  <div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
      <div class="sidebar-heading text-white p-3">Menú Principal</div>
      <div class="list-group list-group-flush">
        <a href="pruebaAdmin.php" class="list-group-item list-group-item-action bg-dark text-white">Inicio</a>
        <a href="gestion_estudiantes.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Estudiantes</a>
        <a href="gestion_empresas.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Empresas</a>
        <a href="gestion_ofertas.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Ofertas</a>
        <a href="gestion_referencias.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Referencias</a>
        <a href="gestion_admin.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Administradores</a>
        <a href="gestion_varios.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Varios</a>
        <a href="gestion_reportes.php"
          class="list-group-item list-group-item-action bg-dark text-white active">Reportes</a>
      </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <!-- Jumbotron de bienvenida -->
      <div class="bg-info text-white py-4 mb-4">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-8 mx-auto text-center">
              <h1 class="display-5 fw-bold">📊 Módulo de Reportes</h1>
              <p class="lead">Genera informes completos y detallados del sistema</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="container-fluid">
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
            <!-- Navegación por pestañas para los tipos de reportes -->
            <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ofertas-tab" data-bs-toggle="tab" data-bs-target="#ofertas-pane"
                  type="button" role="tab" aria-controls="ofertas-pane" aria-selected="true">Ofertas</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="estudiantes-tab" data-bs-toggle="tab" data-bs-target="#estudiantes-pane"
                  type="button" role="tab" aria-controls="estudiantes-pane" aria-selected="false">Estudiantes</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="empresas-tab" data-bs-toggle="tab" data-bs-target="#empresas-pane"
                  type="button" role="tab" aria-controls="empresas-pane" aria-selected="false">Empresas</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="referencias-tab" data-bs-toggle="tab" data-bs-target="#referencias-pane"
                  type="button" role="tab" aria-controls="referencias-pane" aria-selected="false">Referencias</button>
              </li>
            </ul>

            <!-- Contenido de las pestañas -->
            <div class="tab-content" id="reportTabContent">
              <!-- Pestaña de Ofertas -->
              <div class="tab-pane fade show active" id="ofertas-pane" role="tabpanel" aria-labelledby="ofertas-tab">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="tipoReporteOfertas" class="form-label">Tipo de Reporte de Ofertas:</label>
                    <select class="form-select report-type-select" id="tipoReporteOfertas">
                      <option value="">Seleccione un tipo de reporte...</option>
                      <option value="ofertas_por_fecha">Ofertas por Rango de Fechas</option>
                      <option value="ofertas_por_modalidad">Ofertas por Modalidad</option>
                      <option value="ofertas_por_empresa">Ofertas por Empresa</option>
                      <option value="ofertas_por_estado_oferta">Ofertas por Estado</option>
                      <option value="top_ofertas_interes">Top N Ofertas con Más Interesados</option>
                    </select>
                  </div>

                  <div class="col-md-3 filtro-container" id="filtroFechaInicioOfertasContainer" style="display: none;">
                    <label for="fechaInicioOfertas" class="form-label">Fecha Inicio:</label>
                    <input type="date" class="form-control filter-input" id="fechaInicioOfertas">
                  </div>
                  <div class="col-md-3 filtro-container" id="filtroFechaFinOfertasContainer" style="display: none;">
                    <label for="fechaFinOfertas" class="form-label">Fecha Fin:</label>
                    <input type="date" class="form-control filter-input" id="fechaFinOfertas">
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroModalidadOfertasContainer" style="display: none;">
                    <label for="idModalidadOfertas" class="form-label">Modalidad:</label>
                    <select class="form-select filter-select" id="idModalidadOfertas">
                      <option value="">Todas las Modalidades</option>
                      <?php foreach ($modalidades as $modalidad): ?>
                        <option value="<?php echo htmlspecialchars($modalidad['id_modalidad']); ?>">
                          <?php echo htmlspecialchars($modalidad['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEmpresaOfertasContainer" style="display: none;">
                    <label for="idEmpresaOfertas" class="form-label">Empresa:</label>
                    <select class="form-select filter-select" id="idEmpresaOfertas">
                      <option value="">Todas las Empresas</option>
                      <?php foreach ($empresas as $empresa): ?>
                        <option value="<?php echo htmlspecialchars($empresa['idEmpresa']); ?>">
                          <?php echo htmlspecialchars($empresa['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEstadoOfertasContainer" style="display: none;">
                    <label for="idEstadoOfertas" class="form-label">Estado de Oferta:</label>
                    <select class="form-select filter-select" id="idEstadoOfertas">
                      <option value="">Todos los Estados</option>
                      <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo htmlspecialchars($estado['id_estado']); ?>">
                          <?php echo htmlspecialchars($estado['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-3 filtro-container" id="filtroLimiteTopOfertasContainer" style="display: none;">
                    <label for="limiteTopOfertas" class="form-label">Límite (N):</label>
                    <input type="number" class="form-control filter-input" id="limiteTopOfertas" value="5" min="1">
                  </div>

                  <div class="col-12 text-end">
                    <button class="btn btn-primary px-4 rounded-pill" id="btnGenerarReporteOfertas">
                      <i class="fas fa-file-alt me-2"></i>Generar Reporte
                    </button>
                  </div>
                </div>
              </div>

              <!-- Pestaña de Estudiantes -->
              <div class="tab-pane fade" id="estudiantes-pane" role="tabpanel" aria-labelledby="estudiantes-tab">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="tipoReporteEstudiantes" class="form-label">Tipo de Reporte de Estudiantes:</label>
                    <select class="form-select report-type-select" id="tipoReporteEstudiantes">
                      <option value="">Seleccione un tipo de reporte...</option>
                      <option value="estudiantes_registrados">Estudiantes Registrados (General)</option>
                      <option value="estudiantes_por_carrera">Estudiantes por Carrera</option>
                      <option value="estudiantes_por_estado">Estudiantes por Estado</option>
                      <option value="top_estudiantes_interesados_ofertas">Top N Estudiantes con Más Intereses</option>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroCarreraEstudiantesContainer" style="display: none;">
                    <label for="idCarreraEstudiantes" class="form-label">Carrera:</label>
                    <select class="form-select filter-select" id="idCarreraEstudiantes">
                      <option value="">Todas las Carreras</option>
                      <?php foreach ($carreras as $carrera): ?>
                        <option value="<?php echo htmlspecialchars($carrera['id_carrera']); ?>">
                          <?php echo htmlspecialchars($carrera['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEstadoEstudiantesContainer" style="display: none;">
                    <label for="idEstadoEstudiantes" class="form-label">Estado de Estudiante:</label>
                    <select class="form-select filter-select" id="idEstadoEstudiantes">
                      <option value="">Todos los Estados</option>
                      <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo htmlspecialchars($estado['id_estado']); ?>">
                          <?php echo htmlspecialchars($estado['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-3 filtro-container" id="filtroLimiteTopEstudiantesContainer"
                    style="display: none;">
                    <label for="limiteTopEstudiantes" class="form-label">Límite (N):</label>
                    <input type="number" class="form-control filter-input" id="limiteTopEstudiantes" value="5" min="1">
                  </div>

                  <div class="col-12 text-end">
                    <button class="btn btn-primary px-4 rounded-pill" id="btnGenerarReporteEstudiantes">
                      <i class="fas fa-file-alt me-2"></i>Generar Reporte
                    </button>
                  </div>
                </div>
              </div>

              <!-- Pestaña de Empresas -->
              <div class="tab-pane fade" id="empresas-pane" role="tabpanel" aria-labelledby="empresas-tab">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="tipoReporteEmpresas" class="form-label">Tipo de Reporte de Empresas:</label>
                    <select class="form-select report-type-select" id="tipoReporteEmpresas">
                      <option value="">Seleccione un tipo de reporte...</option>
                      <option value="empresas_por_estado">Empresas por Estado</option>
                      <option value="empresas_con_mas_ofertas">Top N Empresas con Más Ofertas</option>
                      <option value="empresas_con_mas_referencias_emitidas">Top N Empresas con Más Referencias</option>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEstadoEmpresasContainer" style="display: none;">
                    <label for="idEstadoEmpresas" class="form-label">Estado de Empresa:</label>
                    <select class="form-select filter-select" id="idEstadoEmpresas">
                      <option value="">Todos los Estados</option>
                      <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo htmlspecialchars($estado['id_estado']); ?>">
                          <?php echo htmlspecialchars($estado['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-3 filtro-container" id="filtroLimiteTopEmpresasOfertasContainer"
                    style="display: none;">
                    <label for="limiteTopEmpresasOfertas" class="form-label">Límite (N):</label>
                    <input type="number" class="form-control filter-input" id="limiteTopEmpresasOfertas" value="5"
                      min="1">
                  </div>

                  <div class="col-md-3 filtro-container" id="filtroLimiteTopEmpresasReferenciasContainer"
                    style="display: none;">
                    <label for="limiteTopEmpresasReferencias" class="form-label">Límite (N):</label>
                    <input type="number" class="form-control filter-input" id="limiteTopEmpresasReferencias" value="5"
                      min="1">
                  </div>

                  <div class="col-12 text-end">
                    <button class="btn btn-primary px-4 rounded-pill" id="btnGenerarReporteEmpresas">
                      <i class="fas fa-file-alt me-2"></i>Generar Reporte
                    </button>
                  </div>
                </div>
              </div>

              <!-- Pestaña de Referencias -->
              <div class="tab-pane fade" id="referencias-pane" role="tabpanel" aria-labelledby="referencias-tab">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="tipoReporteReferencias" class="form-label">Tipo de Reporte de Referencias:</label>
                    <select class="form-select report-type-select" id="tipoReporteReferencias">
                      <option value="">Seleccione un tipo de reporte...</option>
                      <option value="referencias_por_estado">Referencias por Estado</option>
                      <option value="referencias_por_tipo">Referencias por Tipo</option>
                      <option value="referencias_por_empresa">Referencias por Empresa</option>
                      <option value="referencias_por_estudiante">Referencias por Estudiante</option>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEstadoReferenciasContainer" style="display: none;">
                    <label for="idEstadoReferencias" class="form-label">Estado de Referencia:</label>
                    <select class="form-select filter-select" id="idEstadoReferencias">
                      <option value="">Todos los Estados</option>
                      <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo htmlspecialchars($estado['id_estado']); ?>">
                          <?php echo htmlspecialchars($estado['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroTipoReferenciaReferenciasContainer"
                    style="display: none;">
                    <label for="idTipoReferenciaReferencias" class="form-label">Tipo de Referencia:</label>
                    <select class="form-select filter-select" id="idTipoReferenciaReferencias">
                      <option value="">Todos los Tipos</option>
                      <?php foreach ($tipos_referencia as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo['id_tipo_referencia']); ?>">
                          <?php echo htmlspecialchars($tipo['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEmpresaReferenciasContainer" style="display: none;">
                    <label for="idEmpresaReferencias" class="form-label">Empresa:</label>
                    <select class="form-select filter-select" id="idEmpresaReferencias">
                      <option value="">Todas las Empresas</option>
                      <?php foreach ($empresas as $empresa): ?>
                        <option value="<?php echo htmlspecialchars($empresa['idEmpresa']); ?>">
                          <?php echo htmlspecialchars($empresa['nombre']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-6 filtro-container" id="filtroEstudianteReferenciasContainer"
                    style="display: none;">
                    <label for="idEstudianteReferencias" class="form-label">Estudiante:</label>
                    <select class="form-select filter-select" id="idEstudianteReferencias">
                      <option value="">Todos los Estudiantes</option>
                      <?php foreach ($estudiantes as $estudiante): ?>
                        <option value="<?php echo htmlspecialchars($estudiante['idEstudiante']); ?>">
                          <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-12 text-end">
                    <button class="btn btn-primary px-4 rounded-pill" id="btnGenerarReporteReferencias">
                      <i class="fas fa-file-alt me-2"></i>Generar Reporte
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            <div class="text-end">
              <button class="btn btn-success px-4 rounded-pill ms-2" id="btnDescargarPDF" style="display: none;">
                <i class="fas fa-file-pdf me-2"></i>Descargar PDF
              </button>
            </div>

          </div>
        </div>

        <div class="card shadow-sm mb-4" id="contenedorReporte" style="display: none;">
          <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0" id="tituloReporteDisplay">📄 Resultado del Reporte</h5>
          </div>
          <div class="card-body">
            <div id="reporteResultadosContainer">
              <!-- El contenido del reporte se cargará aquí -->
              <p class="text-muted text-center py-5">Seleccione un tipo de reporte y genere para visualizarlo aquí.</p>
            </div>
          </div>
        </div>

      </div>
    </div>
    <!-- /#page-content-wrapper -->
  </div>
  <!-- /#wrapper -->

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container-fluid">
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
    const GLOBAL_MODALIDADES = <?php echo json_encode($modalidades); ?>;
    const GLOBAL_TIPOS_OFERTA = <?php echo json_encode($tipos_oferta); ?>;
    const GLOBAL_TIPOS_REFERENCIA = <?php echo json_encode($tipos_referencia); ?>;
    const GLOBAL_ESTUDIANTES = <?php echo json_encode($estudiantes); ?>;


    $(document).ready(function () {
      inicializarReportes();

      // Script para el toggle de la barra lateral
      $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
      });
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