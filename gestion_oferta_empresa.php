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
      <link rel='stylesheet' href='./sw/dist/sweetalert2.min.css'>
      <script src='./sw/dist/sweetalert2.min.js'></script>
    </head>
    <body>
      <script type='text/javascript'>
        Swal.fire({
          icon: 'error',
          title: '¡Error!',
          text: 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location = './index.php';
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
    <link rel='stylesheet' href='./sw/dist/sweetalert2.min.css'>
    <script src='./sw/dist/sweetalert2.min.js'></script>
  </head>
  <body>
    <script type='text/javascript'>
      Swal.fire({
        icon: 'error',
        title: '¡ERROR!',
        text: 'Debe iniciar sesión como Empresa en el Sistema.'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location = './index.php';
        }
      });
    </script>
  </body>
  </html>
  ";
  exit();
}

// Incluir archivos necesarios para el manejo de ofertas, estudiantes y referencias
require_once './class/class_oferta.php';
require_once './class/class_estudiante.php'; // Incluir la clase Estudiante para el perfil
require_once './class/class_referencia.php'; // Incluir la clase Referencia para la nueva funcionalidad

$ofertaObj = null;
$referenciaObj = null; // Declarar la variable para la instancia de Referencia

$modalidades = [];
$tipos_oferta = [];
$areas_conocimiento = [];
$carreras = [];
$estados = [];
$tipos_referencia = []; // Nueva variable para tipos de referencia
$estados_referencia = []; // Nueva variable para estados de referencia (si se usan)

try {
  $ofertaObj = new Oferta();
  $referenciaObj = new Referencia(); // Instanciar la clase Referencia

  $modalidades = $ofertaObj->obtenerModalidades();
  $tipos_oferta = $ofertaObj->obtenerTiposOferta();
  $areas_conocimiento = $ofertaObj->obtenerAreasConocimiento();
  $carreras = $ofertaObj->obtenerCarreras();
  $estados = $ofertaObj->obtenerEstados();

  // Obtener datos para la nueva funcionalidad de referencias
  $tipos_referencia = $referenciaObj->obtenerTiposReferencia();
  $estados_referencia = $referenciaObj->obtenerEstados(); // Asume que la tabla 'estado' es genérica para varios elementos
} catch (Throwable $e) {
  error_log("ERROR (gestion_oferta_empresa): Fallo al cargar datos estáticos: " . $e->getMessage() . " en línea " . $e->getLine());
  // Puedes mostrar un SweetAlert de error aquí si quieres que el usuario vea el problema inmediatamente
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Gestión de Ofertas - Empresa</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' media='screen' href='./bootstrap/css/bootstrap.min.css'>
  <link rel='stylesheet' href='./sw/dist/sweetalert2.min.css'>
  <!-- Font Awesome para iconos (asegúrate de tenerlo en tu proyecto si usas íconos) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="pruebaEmpresa.php">Módulo Empresa</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="pruebaEmpresa.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="gestion_oferta_empresa.php">Gestión de Ofertas</a>
          </li>
        </ul>

        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#" onclick="mostrarPerfilEmpresa()">Mi Perfil</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <form action="salir.php" method="post" class="d-inline">
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
          <h1 class="display-4 fw-bold">Gestión de Ofertas</h1>
          <p class="lead">Crea, visualiza y edita tus ofertas de prácticas y pasantías.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row mb-4 align-items-center">
      <div class="col-md-6">
        <button class="btn btn-primary btn-lg rounded-pill shadow-sm" data-bs-toggle="modal"
          data-bs-target="#ofertaModal" onclick="resetFormAndOpenModal()">
          <i class="fas fa-plus me-2"></i> Crear Nueva Oferta
        </button>
      </div>
      <div class="col-md-6 d-flex justify-content-end">
        <div class="input-group" style="max-width: 350px;">
          <input type="text" id="busquedaOfertas" class="form-control rounded-start-pill"
            placeholder="Buscar ofertas por título o área...">
          <button class="btn btn-outline-secondary rounded-end-pill" type="button" onclick="cargarOfertas()">
            <i class="fas fa-search me-1"></i> Buscar
          </button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <h5 class="mb-3 text-secondary">Mis Ofertas Publicadas</h5>
        <div id="ofertasContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <!-- Las ofertas se cargarán aquí como tarjetas vía AJAX -->
        </div>
        <div class="d-flex justify-content-center mt-4">
          <button id="loadMoreBtn" class="btn btn-outline-primary" onclick="loadMoreOffers()">Cargar más
            ofertas</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para Crear/Editar Oferta -->
  <?php include 'modal_oferta.php'; ?>

  <!-- Modal para Listar Estudiantes Interesados -->
  <?php include 'modal_estudiantes_interesados.php'; ?>

  <!-- Modal para Ver Perfil de Estudiante (para empresas) -->
  <?php include 'modal_perfil_estudiante_empresa.php'; ?>

  <!-- Nuevo Modal para Crear Referencia -->
  <?php include 'modal_crear_referencia.php'; ?>


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

  <script src="./js/jquery-3.6.1.min.js"></script>
  <script src="./bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="./sw/dist/sweetalert2.min.js"></script>
  <!-- Incluye el archivo de JavaScript para las funciones de oferta -->
  <script src="./js/funcionesOfertas.js"></script>
  <!-- Incluye el nuevo archivo de JavaScript para las funciones de referencias -->
  <script src="./js/funcionesReferenciasE.js"></script>
  <script>
    // Pasar las variables PHP a la función de inicialización de JavaScript de ofertas
    $(document).ready(function () {
      initializeGestionOfertas(
        <?php echo json_encode($modalidades); ?>,
        <?php echo json_encode($tipos_oferta); ?>,
        <?php echo json_encode($areas_conocimiento); ?>,
        <?php echo json_encode($carreras); ?>,
        <?php echo json_encode($estados); ?>
      );
      // Pasar las variables PHP a la función de inicialización de JavaScript de referencias
      initializeReferenciasE(
        <?php echo json_encode($tipos_referencia); ?>,
        <?php echo json_encode($estados_referencia); ?>
      );
    });
    // La función mostrarPerfilEmpresa ahora reside en funcionesOfertas.js y es llamada directamente.
  </script>
</body>

</html>