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
        text: 'Acceso denegado. Debe iniciar sesión como Estudiante en el Sistema.'
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
$ofertaObj = new Oferta();
$empresaObj = new Empresa();
$estudianteObj = new Estudiante(); // Instancia para manejar intereses
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Módulo Estudiante - PPUD</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' media='screen' href='../bootstrap/css/bootstrap.min.css'>
  <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Estilos adicionales si son necesarios, siguiendo la estética de los otros módulos */
    .card-offer {
      border-radius: 0.75rem;
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
      overflow: hidden;
    }

    .card-offer:hover {
      transform: translateY(-5px);
      box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .card-offer .card-header {
      background-color: #f8f9fa;
      /* Light background for header */
      border-bottom: 1px solid #e9ecef;
      padding: 0.75rem 1.25rem;
      font-weight: bold;
    }

    .card-offer .card-title {
      color: #0d6efd;
      /* Primary color for title */
    }

    .card-offer .card-subtitle {
      color: #6c757d;
      /* Secondary color for subtitle */
    }

    .card-offer .list-unstyled li {
      margin-bottom: 0.25rem;
    }

    .card-offer .list-unstyled i {
      color: #0dcaf0;
      /* Info color for icons */
      width: 1.25rem;
      /* Fixed width for icon alignment */
      text-align: center;
    }

    .btn-interes {
      background-color: #28a745;
      /* Success color */
      color: white;
      border: none;
      transition: background-color 0.2s;
    }

    .btn-interes:hover {
      background-color: #218838;
      /* Darker success on hover */
    }

    .company-link {
      cursor: pointer;
      color: #0d6efd;
      /* Primary color for links */
      text-decoration: none;
      font-weight: bold;
    }

    .company-link:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="pruebaEstudiante.php">Módulo Estudiante</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" href="pruebaEstudiante.php">Inicio</a>
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

  <div class="bg-primary text-white py-5 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">Bienvenido al Módulo de Estudiantes</h1>
          <p class="lead">Explora oportunidades de prácticas y pasantías.</p>
          <p class="mb-0">Conectado como: <span
              class="badge bg-light text-dark fs-6"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span></p>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row mb-4 align-items-center">
      <div class="col-md-6">
        <h2 class="mb-0 text-primary">Ofertas de Prácticas y Pasantías</h2>
      </div>
      <div class="col-md-6 d-flex justify-content-end">
        <div class="input-group" style="max-width: 350px;">
          <input type="text" id="busquedaOfertas" class="form-control rounded-start-pill"
            placeholder="Buscar ofertas por título, empresa o área...">
          <button class="btn btn-outline-primary rounded-end-pill" type="button" onclick="cargarOfertas()">
            <i class="fas fa-search me-1"></i> Buscar
          </button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div id="ofertasContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        </div>
        <div class="d-flex justify-content-center mt-4">
          <button id="loadMoreBtn" class="btn btn-outline-primary" onclick="loadMoreOffers()">Cargar más
            ofertas</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="detalleOfertaModal" tabindex="-1" aria-labelledby="detalleOfertaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="detalleOfertaModalLabel">Detalle de la Oferta</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <h4 id="modal_titulo" class="text-primary mb-3"></h4>
          <p class="text-muted mb-4">Publicado por: <a href="#" id="modal_empresa_nombre" class="company-link"></a></p>

          <div class="mb-3">
            <strong>Descripción:</strong>
            <p id="modal_descripcion"></p>
          </div>
          <div class="mb-3">
            <strong>Requisitos:</strong>
            <p id="modal_requisitos"></p>
          </div>
          <div class="mb-3">
            <strong>Beneficios:</strong>
            <p id="modal_beneficios"></p>
          </div>

          <div class="row">
            <div class="col-md-6">
              <ul class="list-unstyled">
                <li><strong><i class="fas fa-handshake me-2 text-info"></i>Modalidad:</strong> <span
                    id="modal_modalidad"></span></li>
                <li><strong><i class="fas fa-tag me-2 text-info"></i>Tipo de Oferta:</strong> <span
                    id="modal_tipo_oferta"></span></li>
                <li><strong><i class="fas fa-graduation-cap me-2 text-info"></i>Área de Conocimiento:</strong> <span
                    id="modal_area_conocimiento"></span></li>
                <li><strong><i class="fas fa-hourglass-half me-2 text-info"></i>Duración:</strong> <span
                    id="modal_duracion_meses"></span> meses</li>
                <li><strong><i class="fas fa-calendar-alt me-2 text-info"></i>Vencimiento:</strong> <span
                    id="modal_fecha_vencimiento"></span></li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul class="list-unstyled">
                <li><strong><i class="fas fa-clock me-2 text-info"></i>Horario:</strong> <span
                    id="modal_horario"></span></li>
                <li><strong><i class="fas fa-dollar-sign me-2 text-info"></i>Remuneración:</strong> <span
                    id="modal_remuneracion"></span></li>
                <li><strong><i class="fas fa-list-ol me-2 text-info"></i>Semestre Mínimo:</strong> <span
                    id="modal_semestre_minimo"></span></li>
                <li><strong><i class="fas fa-percent me-2 text-info"></i>Promedio Mínimo:</strong> <span
                    id="modal_promedio_minimo"></span></li>
                <li><strong><i class="fas fa-users me-2 text-info"></i>Cupos Disponibles:</strong> <span
                    id="modal_cupos_disponibles"></span></li>
              </ul>
            </div>
          </div>

          <div class="mb-3">
            <strong>Habilidades Requeridas:</strong>
            <p id="modal_habilidades_requeridas"></p>
          </div>
          <div class="mb-3">
            <strong>Carreras Dirigidas:</strong>
            <p id="modal_carreras_dirigidas"></p>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnInteres" onclick="mostrarInteres()">
            <i class="fas fa-star me-2"></i>Demostrar Interés
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="detalleEmpresaModal" tabindex="-1" aria-labelledby="detalleEmpresaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="detalleEmpresaModalLabel">Perfil de la Empresa</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="contenidoDetalleEmpresa">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>


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
  <script src="../js/perfilE.js"></script>
  <script>
    $(document).ready(function () {
      cargarOfertas(); // Cargar ofertas al inicio
    });
  </script>
</body>

</html>