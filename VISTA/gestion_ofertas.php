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

// Incluir archivos necesarios
include_once '../MODELO/class_oferta.php';

// Crear instancias de las clases
$ofertaObj = new Oferta();

// Obtener datos para los selectores del modal
$modalidades = $ofertaObj->obtenerModalidades();
$tipos_oferta = $ofertaObj->obtenerTiposOferta();
$areas_conocimiento = $ofertaObj->obtenerAreasConocimiento();
$carreras = $ofertaObj->obtenerCarreras(); // Para las carreras dirigidas
$estados = $ofertaObj->obtenerEstados(); // Para el estado de la oferta
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Ofertas</title>
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
            <a class="nav-link active" href="gestion_ofertas.php">Gestión Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_referencias.php">Gestión Referencias</a>
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
  <div class="bg-warning text-white py-4 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-5 fw-bold">💼 Gestión de Ofertas</h1>
          <p class="lead">Administra y gestiona todas las ofertas laborales y oportunidades en el sistema</p>
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
        <li class="breadcrumb-item active" aria-current="page">Gestión de Ofertas</li>
      </ol>
    </nav>

    <!-- Card de búsqueda y estadísticas -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header bg-warning text-white">
            <h5 class="card-title mb-0">🔍 Búsqueda de Ofertas</h5>
          </div>
          <div class="card-body">
            <div class="input-group">
              <input type="text" class="form-control" id="busquedaInput"
                placeholder="Buscar por título, descripción, empresa o área de conocimiento...">
              <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">Limpiar</button>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm text-white bg-dark">
          <!-- Cambiado de bg-info a bg-dark -->
          <div class="card-header">📊 Estadísticas</div>
          <div class="card-body">
            <h4 class="card-title text-center" id="totalOfertas">0</h4>
            <p class="card-text text-center mb-0" id="textoEstadistica">Total de ofertas activas</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón para crear nueva oferta -->
    <div class="mb-3 text-end">
      <button type="button" class="btn btn-success rounded-pill px-4" onclick="crearOferta()">
        <i class="fas fa-plus-circle me-2"></i>Crear Nueva Oferta
      </button>
    </div>

    <!-- Tabla de ofertas -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0">📋 Lista de Ofertas</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Empresa</th>
                <th>Modalidad</th>
                <th>Tipo</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaOfertas">
              <!-- Contenido cargado dinámicamente -->
            </tbody>
          </table>
        </div>
        <!-- Paginación -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center" id="paginationControls">
            <!-- Los controles de paginación se cargarán con JavaScript -->
          </ul>
        </nav>
      </div>
    </div>

    <!-- Modal para Crear/Editar Oferta -->
    <?php include 'modal_oferta_admin.php'; ?>

    <!-- Modal para Ver Detalle de Oferta -->
    <?php include 'modal_detalle_oferta.php'; ?>


    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Gestión Administrativa. Todos los derechos
              reservados.</p>
            <small class="text-muted">Gestión de Ofertas - Desarrollado con Bootstrap
              <?php echo date('Y'); ?></small>
          </div>
        </div>
      </div>
    </footer>

    <script src="../js/jquery-3.6.1.min.js"></script>
    <!-- Asegúrate de que bootstrap.bundle.min.js se carga después de jQuery si jQuery es una dependencia para componentes de Bootstrap -->
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../sw/dist/sweetalert2.min.js"></script>
    <script src="../js/funcionesGofertas.js"></script>
    <script>
      // Pasar las variables PHP a la función de inicialización de JavaScript
      $(document).ready(function () {
        initializeGestionOfertas(
          <?php echo json_encode($modalidades); ?>,
          <?php echo json_encode($tipos_oferta); ?>,
          <?php echo json_encode($areas_conocimiento); ?>,
          <?php echo json_encode($carreras); ?>,
          <?php echo json_encode($estados); ?>
        );
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

      // Tooltip para elementos que lo necesiten
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    </script>
</body>

</html>