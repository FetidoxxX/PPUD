<?php
session_start();
$inn = 500;
if (isset($_SESSION['timeout'])) {
  $_session_life = time() - $_SESSION['timeout'];
  if ($_session_life > $inn) {
    session_destroy();
    // Redirects using JavaScript and SweetAlert2 to display the message
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

// If the user is not authenticated or is not a company, redirects.
// Assumes 'usuario_id' is set upon login and 'rol' is 'empresa'
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empresa') {
  $_SESSION['usuario'] = NULL; // Clears the session just in case
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
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Módulo Empresa - PPUD</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' media='screen' href='../bootstrap/css/bootstrap.min.css'>
  <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Style for card hover effect */
    .card-hover-shadow {
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card-hover-shadow:hover {
      transform: translateY(-5px);
      /* Slightly lifts the card */
      box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .25) !important;
      /* Larger shadow on hover */
    }
  </style>
</head>

<body>
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
            <a class="nav-link active" href="pruebaEmpresa.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_oferta_empresa.php">Gestionar Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_estudiantes_empresa.php">Gestionar Estudiantes</a>
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

  <!-- Welcome Jumbotron -->
  <div class="bg-success text-white py-5 mb-4">
    <!-- Changed to bg-success -->
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">Bienvenido al Módulo de Empresa</h1>
          <p class="lead">Desde aquí podrás gestionar tus ofertas, estudiantes y tu perfil.</p>
          <p class="mb-0">Conectado como: <span
              class="badge bg-light text-dark fs-6"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content: Management Options -->
  <div class="container">
    <div class="row mb-4">
      <div class="col-12 text-center">
        <h2>Panel de Gestión</h2>
      </div>
    </div>
    <div class="row g-4 mb-5 justify-content-center">

      <!-- Card: Manage Offers -->
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-lg border-0 rounded-4 card-hover-shadow">
          <div class="card-body text-center p-4">
            <div class="display-1 text-success mb-3"><i class="fas fa-bullhorn"></i></div> <!-- Icono de megáfono -->
            <h5 class="card-title fw-bold">Gestionar Ofertas</h5>
            <p class="card-text text-muted">Crea, edita, visualiza y desactiva tus ofertas de prácticas y pasantías.</p>
          </div>
          <div class="card-footer bg-transparent border-0 text-center pb-4">
            <a href="gestion_oferta_empresa.php" class="btn btn-success btn-lg w-100 rounded-pill">
              <!-- Changed to btn-success -->
              <i class="fas fa-arrow-right me-2"></i> Ir a Ofertas
            </a>
          </div>
        </div>
      </div>

      <!-- Card: Manage Students -->
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-lg border-0 rounded-4 card-hover-shadow">
          <div class="card-body text-center p-4">
            <div class="display-1 text-info mb-3"><i class="fas fa-users"></i></div> <!-- Icono de grupo de usuarios -->
            <h5 class="card-title fw-bold">Gestionar Estudiantes</h5>
            <p class="card-text text-muted">Revisa las postulaciones de estudiantes, gestiona sus estados y datos.</p>
          </div>
          <div class="card-footer bg-transparent border-0 text-center pb-4">
            <a href="gestion_estudiantes_empresa.php" class="btn btn-info btn-lg w-100 rounded-pill">
              <i class="fas fa-arrow-right me-2"></i> Ir a Estudiantes
            </a>
          </div>
        </div>
      </div>

      <!-- Card: My Profile -->
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-lg border-0 rounded-4 card-hover-shadow">
          <div class="card-body text-center p-4">
            <div class="display-1 text-warning mb-3"><i class="fas fa-user-cog"></i></div>
            <!-- Icono de usuario con engranaje -->
            <h5 class="card-title fw-bold">Mi Perfil</h5>
            <p class="card-text text-muted">Actualiza la información de tu empresa y datos de contacto.</p>
          </div>
          <div class="card-footer bg-transparent border-0 text-center pb-4">
            <a href="perfil_empresa.php" class="btn btn-warning btn-lg w-100 rounded-pill">
              <i class="fas fa-arrow-right me-2"></i> Ver Perfil
            </a>
          </div>
        </div>
      </div>

    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Módulo Empresa</li>
      </ol>
    </nav>
  </div>

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
  <script src="../js/funciones.js"></script>
  <!-- La función mostrarPerfilEmpresa() ya no es necesaria aquí, el enlace es directo a perfil_empresa.php -->
</body>

</html>