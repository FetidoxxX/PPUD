<?php
session_start(); // ¡CRUCIAL! Debe ser la primera línea, sin espacios ni salida antes.

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

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
  // Si no hay usuario_id o el rol no es 'estudiante', se considera acceso denegado
  $_SESSION['usuario'] = NULL; // Limpia la variable de usuario por seguridad
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
        text: 'Debe iniciar sesión como Estudiante en el Sistema.'
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

// Incluir archivos necesarios para el manejo de ofertas
require_once '../MODELO/class_oferta.php';
require_once '../MODELO/class_empresa.php'; // Para obtener el perfil de la empresa
require_once '../MODELO/class_estudiante.php'; // Necesario para manejar el interés del estudiante

// Crear instancias de las clases. Cada clase ahora maneja su propia conexión internamente.
$ofertaObj = null;
$empresaObj = null;
$estudianteObj = null;
$referenciaObj = null; // Se mantiene, aunque su uso principal está en funcionesReferenciasEstudiante.js

try {
  $ofertaObj = new Oferta();
  $empresaObj = new Empresa();
  $estudianteObj = new Estudiante();
  // No es necesario instanciar Referencia aquí si solo se usa en ajax_referencias_estudiante.php
  // $referenciaObj = new Referencia();
} catch (Throwable $e) {
  error_log("ERROR (pruebaEstudiante): Fallo al cargar datos estáticos: " . $e->getMessage() . " en línea " . $e->getLine());
  // Puedes mostrar un SweetAlert de error aquí si quieres que el usuario vea el problema inmediatamente
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Portal de Prácticas y Pasantías UD</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' media='screen' href='../bootstrap/css/bootstrap.min.css'>
  <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
  .card-offer {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  }

  .card-offer:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  }

  .company-link {
    color: #0d6efd;
    /* Bootstrap primary color */
    text-decoration: none;
  }

  .company-link:hover {
    text-decoration: underline;
  }
  </style>
</head>

<body class="bg-light">
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="pruebaEstudiante.php">Módulo Estudiante</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="pruebaEstudiante.php">Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="perfil_estudiante.php">Mi Perfil</a>
          </li>
        </ul>

        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="perfil_estudiante.php">Mi Perfil</a></li>
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
  <div class="bg-primary text-white py-5 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">Bienvenido al Portal de Prácticas</h1>
          <p class="lead">Encuentra tu oportunidad ideal.</p>
          <div class="input-group input-group-lg mt-4">
            <input type="text" class="form-control rounded-pill pe-5" id="busquedaOfertas"
              placeholder="Buscar ofertas...">
            <span class="input-group-text bg-transparent border-0 position-absolute end-0"
              style="z-index: 10; cursor: pointer;" id="searchIcon">
              <i class="fas fa-search text-primary"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <h2 class="mb-4 text-primary"><i class="fas fa-briefcase me-2"></i>Ofertas Disponibles</h2>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="ofertasContainer">
      <!-- Las ofertas se cargarán aquí -->
    </div>

    <div class="d-flex justify-content-center mt-4">
      <button class="btn btn-primary btn-lg" id="loadMoreBtn">Cargar más ofertas</button>
    </div>
  </div>

  <!-- Modal de Detalle de Oferta -->
  <div class="modal fade" id="detalleOfertaModal" tabindex="-1" aria-labelledby="detalleOfertaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <!-- MODIFICADO: Cambiado de modal-xl a modal-lg -->
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="detalleOfertaModalLabel">Detalles de la Oferta</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <h4 id="modal_titulo" class="text-primary mb-3"></h4>
          <h6 class="text-muted mb-3">Publicado por: <a href="#" id="modal_empresa_nombre" class="company-link"></a>
          </h6>

          <hr class="my-4">

          <div class="row">
            <div class="col-md-6">
              <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Información General</h5>
              <ul class="list-unstyled">
                <li class="mb-2"><strong><i class="fas fa-handshake me-2 text-info"></i>Modalidad:</strong> <span
                    id="modal_modalidad"></span></li>
                <li class="mb-2"><strong><i class="fas fa-tag me-2 text-info"></i>Tipo de Oferta:</strong> <span
                    id="modal_tipo_oferta"></span></li>
                <li class="mb-2"><strong><i class="fas fa-graduation-cap me-2 text-info"></i>Área de
                    Conocimiento:</strong> <span id="modal_area_conocimiento"></span></li>
                <li class="mb-2"><strong><i class="fas fa-hourglass-half me-2 text-info"></i>Duración:</strong> <span
                    id="modal_duracion_meses"></span> meses</li>
                <li class="mb-2"><strong><i class="fas fa-calendar-alt me-2 text-info"></i>Vencimiento:</strong> <span
                    id="modal_fecha_vencimiento"></span></li>
              </ul>
            </div>
            <div class="col-md-6">
              <h5 class="text-primary mb-3"><i class="fas fa-list-alt me-2"></i>Detalles Adicionales</h5>
              <ul class="list-unstyled">
                <li class="mb-2"><strong><i class="fas fa-clock me-2 text-info"></i>Horario:</strong> <span
                    id="modal_horario"></span></li>
                <li class="mb-2"><strong><i class="fas fa-dollar-sign me-2 text-info"></i>Remuneración:</strong> <span
                    id="modal_remuneracion"></span></li>
                <li class="mb-2"><strong><i class="fas fa-list-ol me-2 text-info"></i>Semestre Mínimo:</strong> <span
                    id="modal_semestre_minimo"></span></li>
                <li class="mb-2"><strong><i class="fas fa-percent me-2 text-info"></i>Promedio Mínimo:</strong> <span
                    id="modal_promedio_minimo"></span></li>
                <li class="mb-2"><strong><i class="fas fa-users me-2 text-info"></i>Cupos Disponibles:</strong> <span
                    id="modal_cupos_disponibles"></span></li>
              </ul>
            </div>
          </div>

          <hr class="my-4">

          <div class="row">
            <div class="col-md-6">
              <h5 class="text-primary mb-3"><i class="fas fa-file-alt me-2"></i>Descripción de la Oferta</h5>
              <p id="modal_descripcion" class="mb-4"></p>
            </div>
            <div class="col-md-6">
              <h5 class="text-primary mb-3"><i class="fas fa-clipboard-list me-2"></i>Requisitos</h5>
              <p id="modal_requisitos" class="mb-4"></p>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <h5 class="text-primary mb-3"><i class="fas fa-gift me-2"></i>Beneficios</h5>
              <p id="modal_beneficios" class="mb-4"></p>
            </div>
            <div class="col-md-6">
              <h5 class="text-primary mb-3"><i class="fas fa-code-branch me-2"></i>Carreras Dirigidas</h5>
              <p id="modal_carreras_dirigidas" class="mb-4"></p>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <h5 class="text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>Habilidades Requeridas</h5>
              <p id="modal_habilidades_requeridas" class="mb-4"></p>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnInteres" data-oferta-id="" data-interes-mostrado="false"
            onclick="toggleInteres(this)">
            <i class="far fa-star me-2"></i>Me Interesa
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Nuevo Modal para Perfil de Empresa (para estudiantes) - Incluido primero -->
  <?php include 'modal_perfil_empresa_estudiante.php'; ?>

  <!-- Modal para Crear/Editar Referencia (Específico de este módulo) - Incluido después para mayor z-index -->
  <?php include 'modal_referencia_estudiante_empresa.php'; ?>


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

  <input type="hidden" id="idEstudiante" value="<?php echo htmlspecialchars($_SESSION['usuario_id']); ?>">


  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <script src="../js/funcionesOfertasE.js"></script>
  <!-- Incluye el JS específico para las funciones de referencia de estudiante a empresa -->
  <script src="../js/funcionesReferenciasEstudiante.js"></script>
  <script>
  // Variable global para el ID del estudiante, accesible por JavaScript
  const ESTUDIANTE_ID_MODULO = '<?php echo htmlspecialchars($_SESSION['usuario_id']); ?>';

  $(document).ready(function() {
    cargarOfertas(); // Cargar ofertas al iniciar la página

    $('#searchIcon').on('click', function() {
      cargarOfertas(false);
    });

    $('#busquedaOfertas').on('keypress', function(e) {
      if (e.which === 13) {
        cargarOfertas(false);
      }
    });

    $('#loadMoreBtn').on('click', function() {
      loadMoreOffers();
    });
  });
  </script>
</body>

</html>