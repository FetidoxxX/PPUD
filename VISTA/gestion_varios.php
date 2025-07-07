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

// Definir las tablas de catálogo que se gestionarán
$tablasCatalogo = [
  ['name' => 'estado', 'display' => 'Estados'],
  ['name' => 'tipo_documento', 'display' => 'Tipos de Documento'],
  ['name' => 'modalidad', 'display' => 'Modalidades'],
  ['name' => 'tipo_oferta', 'display' => 'Tipos de Oferta'],
  ['name' => 'tipo_referencia', 'display' => 'Tipos de Referencia'],
  ['name' => 'area_conocimiento', 'display' => 'Áreas de Conocimiento'],
  ['name' => 'carrera', 'display' => 'Carreras'],
  ['name' => 'ciudad', 'display' => 'Ciudades'] // Se mantiene 'ciudad'
];

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Varios</title>
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
        <a href="gestion_varios.php" class="list-group-item list-group-item-action bg-dark text-white active">Gestión
          Varios</a>
        <a href="gestion_reportes.php" class="list-group-item list-group-item-action bg-dark text-white">Reportes</a>
        <!-- Nuevas opciones para Perfil y Cerrar Sesión en el menú lateral -->
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white" onclick="mostrarPerfil()">Mi
          Perfil</a>
        <form action="../salir.php" method="post" class="d-inline">
          <button type="submit"
            class="list-group-item list-group-item-action bg-dark text-danger w-100 text-start">Cerrar Sesión</button>
        </form>
      </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
      <!-- Jumbotron de bienvenida -->
      <div class="bg-secondary text-white py-4 mb-4">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-8 mx-auto text-center">
              <h1 class="display-5 fw-bold">🗄️ Gestión de Datos Varios</h1>
              <p class="lead">Administra los catálogos y datos auxiliares del sistema</p>
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
            <li class="breadcrumb-item active" aria-current="page">Gestión de Varios</li>
          </ol>
        </nav>

        <!-- Segunda barra de navegación (Pestañas de Catálogo) -->
        <ul class="nav nav-pills nav-fill mb-4 bg-dark text-white rounded" id="pestañasCatalogo" role="tablist">
          <?php foreach ($tablasCatalogo as $index => $tabla): ?>
            <li class="nav-item" role="presentation">
              <button class="nav-link <?php echo ($index === 0) ? 'active' : ''; ?>"
                id="<?php echo $tabla['name']; ?>-tab" data-bs-toggle="pill"
                data-bs-target="#<?php echo $tabla['name']; ?>-pane" type="button" role="tab"
                aria-controls="<?php echo $tabla['name']; ?>-pane"
                aria-selected="<?php echo ($index === 0) ? 'true' : 'false'; ?>"
                onclick="cargarTabla('<?php echo $tabla['name']; ?>', '<?php echo $tabla['display']; ?>')">
                <?php echo $tabla['display']; ?>
              </button>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- Contenido dinámico de la tabla seleccionada -->
        <div class="tab-content" id="contenidoPestañaCatalogo">
          <div class="tab-pane fade show active" id="panelTablaActual" role="tabpanel"
            aria-labelledby="currentTable-tab">
            <!-- Contenido de la tabla (búsqueda, botón crear, tabla, estadísticas) se cargará aquí dinámicamente -->
            <div class="row mb-4">
              <div class="col-md-8">
                <div class="card shadow-sm">
                  <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">🔍 Búsqueda en <span id="nombreTablaActualMostrar"></span></h5>
                  </div>
                  <div class="card-body">
                    <div class="input-group">
                      <input type="text" class="form-control" id="entradaBusqueda" placeholder="Buscar por nombre...">
                      <button class="btn btn-outline-secondary" type="button"
                        onclick="limpiarBusqueda()">Limpiar</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card shadow-sm text-white bg-secondary">
                  <div class="card-header">📊 Estadísticas</div>
                  <div class="card-body">
                    <h4 class="card-title text-center" id="totalElementos">0</h4>
                    <p class="card-text text-center mb-0" id="textoEstadistica">Total de elementos</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-3 text-end">
              <button type="button" class="btn btn-success rounded-pill px-4" id="btnCrearElemento">
                <i class="fas fa-plus-circle me-2"></i>Crear Nuevo <span id="textoBtnCrearElemento"></span>
              </button>
            </div>

            <div class="card shadow-sm">
              <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0">📋 Lista de <span id="nombreListaElementosTabla"></span></h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead id="tablaEncabezados" class="table-dark">
                      <!-- Los encabezados de la tabla se cargarán dinámicamente aquí por JavaScript -->
                    </thead>
                    <tbody id="tablaElementos">
                      <!-- Contenido de la tabla cargado dinámicamente -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modals -->
        <?php include 'modal_catalogo_crud.php'; ?>
        <?php include 'modal_detalle_catalogo.php'; ?>

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
          <small class="text-muted">Gestión de Varios - Desarrollado con Bootstrap
            <?php echo date('Y'); ?></small>
        </div>
      </div>
    </div>
  </footer>

  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <script src="../js/funcionesGvarios.js"></script>
  <script>
    // Pasar las tablas de catálogo a la función de inicialización de JavaScript
    $(document).ready(function () {
      inicializarGestionVarios(<?php echo json_encode($tablasCatalogo); ?>);

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