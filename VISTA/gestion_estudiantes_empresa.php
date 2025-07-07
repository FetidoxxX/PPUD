<?php
session_start();
$inn = 500;
if (isset($_SESSION['timeout'])) {
  $_session_life = time() - $_SESSION['timeout'];
  if ($_session_life > $inn) {
    session_destroy();
    echo "
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
          title: '¡Error!',
          text: 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location = '../index.php';
          }
        });
      </script>
    </body>
    </html>
    ";
    exit();
  }
}
$_SESSION['timeout'] = time();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
  $_SESSION['usuario'] = NULL;
  echo "
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
        title: '¡ERROR!',
        text: 'Debe iniciar sesión como Empresa en el Sistema.'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location = '../index.php';
        }
      });
    </script>
  </body>
  </html>
  ";
  exit();
}

// Incluir las clases necesarias para obtener los datos maestros para este módulo
require_once '../MODELO/class_oferta.php'; // Para obtener las carreras
require_once '../MODELO/class_referencia.php'; // Para obtener tipos de referencia y estados

$ofertaObj = new Oferta();
$referenciaObj = new Referencia();

$carreras_disponibles = $ofertaObj->obtenerCarreras();
$tipos_referencia_disponibles = $referenciaObj->obtenerTiposReferencia();
$estados_referencia_disponibles = $referenciaObj->obtenerEstados();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Gestión de Estudiantes - Empresa</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' media='screen' href='../bootstrap/css/bootstrap.min.css'>
  <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-light">
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="pruebaEmpresa.php">Módulo Empresa</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link " href="pruebaEmpresa.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " href="gestion_oferta_empresa.php">Gestionar Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="gestion_estudiantes_empresa.php">Gestionar Estudiantes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " href="perfil_empresa.php">Mi Perfil</a>
          </li>
        </ul>

        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="perfil_empresa.php">Mi Perfil</a></li>
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

  <!-- Jumbotron -->
  <div class="bg-info text-white py-5 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">Gestión de Estudiantes</h1>
          <p class="lead">Explora y gestiona los perfiles de los estudiantes activos en la plataforma.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="container mt-4">
    <div class="card shadow mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Listado de Estudiantes Activos</h5>
      </div>
      <div class="card-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="searchStudentInput"
            placeholder="Buscar por nombre, documento o código de estudiante...">
          <button class="btn btn-primary" type="button" id="searchStudentBtn"><i class="fas fa-search"></i>
            Buscar</button>
        </div>
        <div id="studentListContainer" class="row row-cols-1 row-cols-md-2 g-4">
          <!-- Student cards will be loaded here -->
          <p class="text-center text-muted w-100">Cargando estudiantes...</p>
        </div>
        <nav aria-label="Page navigation" class="mt-4">
          <ul class="pagination justify-content-center" id="studentPagination">
            <!-- Pagination will be loaded here -->
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Modal para Perfil del Estudiante (Específico de este módulo) -->
  <?php include 'empresa_estudiantes_modal_perfil.php'; ?>

  <!-- Modal para Crear/Editar Referencia (Específico de este módulo) -->
  <?php include 'empresa_estudiantes_modal_referencia.php'; ?>


  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> Portal de Prácticas y Pasantías UD. Todos los derechos
            reservados.</p>
          <small class="text-muted">Desarrollado con Bootstrap</small>
        </div>
      </div>
    </div>
  </footer>

  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <!-- Incluye el JS específico para este módulo -->
  <script src="../js/empresa_estudiantes_funciones.js"></script>
  <script>
  // Variable global para el ID de la empresa, accesible por JavaScript
  const EMPRESA_ID_MODULO = '<?php echo htmlspecialchars($_SESSION['usuario_id']); ?>';

  // Pasar las variables PHP a la función de inicialización de JavaScript
  $(document).ready(function() {
    initializeEmpresaEstudiantes(
      <?php echo json_encode($carreras_disponibles); ?>,
      <?php echo json_encode($tipos_referencia_disponibles); ?>,
      <?php echo json_encode($estados_referencia_disponibles); ?>
    );
  });
  </script>
</body>

</html>