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

// INICIO DE LA NUEVA VALIDACIÓN DE ESTADO DE USUARIO
include_once '../MODELO/class_administrador.php';
$administradorObj = new Administrador();

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
// FIN DE LA NUEVA VALIDACIÓN DE ESTADO DE USUARIO

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel de Administración</title>
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
            <a class="nav-link active" href="pruebaAdmin.php">Inicio</a>
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
          <!-- Nuevo módulo: Gestión Varios -->
          <li class="nav-item">
            <a class="nav-link" href="gestion_varios.php">Gestión Varios</a>
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
  <div class="bg-primary text-white py-5 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">Bienvenido al Panel de Administración</h1>
          <p class="lead">Gestiona todos los aspectos del sistema desde este panel centralizado</p>
          <p class="mb-0">Conectado como: <span
              class="badge bg-light text-dark fs-6"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenido principal -->
  <div class="container">
    <!-- Título de sección -->
    <div class="row mb-4">
      <div class="col-12">
        <h2 class="text-center mb-4">Módulos de Gestión</h2>
      </div>
    </div>

    <!-- Cards de gestión -->
    <div class="row g-4 mb-5">
      <!-- Gestión de Estudiantes -->
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <div class="display-1 text-primary mb-3">📚</div>
            <h5 class="card-title">Gestión de Estudiantes</h5>
            <p class="card-text">Administrar, editar y consultar información de estudiantes registrados</p>
          </div>
          <div class="card-footer bg-transparent">
            <a href="gestion_estudiantes.php" class="btn btn-primary w-100">Acceder</a>
          </div>
        </div>
      </div>

      <!-- Gestión de Empresas -->
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <div class="display-1 text-success mb-3">🏢</div>
            <h5 class="card-title">Gestión de Empresas</h5>
            <p class="card-text">Administrar empresas colaboradoras y sus datos de contacto</p>
          </div>
          <div class="card-footer bg-transparent">
            <a href="gestion_empresas.php" class="btn btn-success w-100">Acceder</a>
          </div>
        </div>
      </div>

      <!-- Gestión de Ofertas -->
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <div class="display-1 text-warning mb-3">💼</div>
            <h5 class="card-title">Gestión de Ofertas</h5>
            <p class="card-text">Administrar ofertas laborales y oportunidades de empleo</p>
          </div>
          <div class="card-footer bg-transparent">
            <a href="gestion_ofertas.php" class="btn btn-warning w-100">Acceder</a>
          </div>
        </div>
      </div>

      <!-- Gestión de Referencias -->
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <div class="display-1 text-info mb-3">⭐</div>
            <h5 class="card-title">Gestión de Referencias</h5>
            <p class="card-text">Administrar referencias laborales y recomendaciones</p>
          </div>
          <div class="card-footer bg-transparent">
            <a href="gestion_referencias.php" class="btn btn-info w-100">Acceder</a>
          </div>
        </div>
      </div>

      <!-- Gestión de Administradores (Nuevo Módulo) -->
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <div class="display-1 text-danger mb-3">⚙️</div>
            <h5 class="card-title">Gestión de Administradores</h5>
            <p class="card-text">Administrar usuarios con permisos de administrador</p>
          </div>
          <div class="card-footer bg-transparent">
            <a href="gestion_admin.php" class="btn btn-danger w-100">Acceder</a>
          </div>
        </div>
      </div>

      <!-- Nuevo módulo: Gestión Varios -->
      <div class="col-lg-3 col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-body text-center">
            <div class="display-1 text-secondary mb-3">🗄️</div>
            <h5 class="card-title">Gestión de Varios</h5>
            <p class="card-text">Administra los catálogos y datos auxiliares del sistema.</p>
          </div>
          <div class="card-footer bg-transparent">
            <a href="gestion_varios.php" class="btn btn-secondary w-100">Acceder</a>
          </div>
        </div>
      </div>
    </div>


    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Panel de Administración</li>
      </ol>
    </nav>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Gestión Administrativa. Todos los derechos
            reservados.</p>
          <small class="text-muted">Desarrollado con Bootstrap <?php echo date('Y'); ?></small>
        </div>
      </div>
    </div>
  </footer>

  <script src="../bootstrap/js/bootstrap.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <script src="../js/jquery-3.6.1.min.js"></script>

  <script>
    // Función para mostrar perfil
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