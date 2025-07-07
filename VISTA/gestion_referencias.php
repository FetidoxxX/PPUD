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
include_once '../MODELO/class_referencia.php';

// Crear instancias de las clases
$referenciaObj = new Referencia();

// Obtener datos para los selectores del modal de edición (tipos de referencia y estados)
$tipos_referencia = $referenciaObj->obtenerTiposReferencia();
$estados_referencia = $referenciaObj->obtenerEstados();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Referencias</title>
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
            <a class="nav-link active" href="gestion_referencias.php">Gestión Referencias</a>
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
  <div class="bg-info text-white py-4 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-5 fw-bold">⭐ Gestión de Referencias</h1>
          <p class="lead">Administra y gestiona las referencias de estudiantes y empresas en el sistema</p>
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
        <li class="breadcrumb-item active" aria-current="page">Gestión de Referencias</li>
      </ol>
    </nav>

    <!-- Card de búsqueda y estadísticas -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">🔍 Búsqueda y Filtrado de Referencias</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-8">
                <input type="text" class="form-control" id="busquedaInput"
                  placeholder="Buscar por comentario, estudiante, empresa o tipo...">
              </div>
              <div class="col-md-4">
                <select class="form-select" id="filtroTipoReferencia">
                  <option value="">Todos los Tipos</option>
                  <?php foreach ($tipos_referencia as $tipo): ?>
                    <option value="<?php echo $tipo['id_tipo_referencia']; ?>">
                      <?php echo htmlspecialchars($tipo['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12 text-end">
                <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">Limpiar
                  Filtros</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm text-white bg-dark">
          <div class="card-header">📊 Estadísticas</div>
          <div class="card-body">
            <h4 class="card-title text-center" id="totalReferencias">0</h4>
            <p class="card-text text-center mb-0" id="textoEstadistica">Total de referencias</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de referencias -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0">📜 Lista de Referencias</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Comentario</th>
                <th>Puntuación</th>
                <th>Tipo</th>
                <th>Estudiante</th>
                <th>Empresa</th>
                <th>Fecha Creación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaReferencias">
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

    <!-- Modal para Ver Detalle de Referencia -->
    <div class="modal fade" id="modalDetalleReferencia" tabindex="-1" aria-labelledby="modalDetalleReferenciaLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="modalDetalleReferenciaLabel">👁️ Detalle de la Referencia</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
              aria-label="Cerrar"></button>
          </div>
          <div class="modal-body" id="contenidoDetalleReferencia">
            <!-- Contenido cargado dinámicamente -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Editar Referencia -->
    <div class="modal fade" id="modalEditarReferencia" tabindex="-1" aria-labelledby="modalEditarReferenciaLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-warning text-white">
            <h5 class="modal-title" id="modalEditarReferenciaLabel">✏️ Editar Referencia</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
              aria-label="Cerrar"></button>
          </div>
          <form id="formEditarReferencia">
            <div class="modal-body">
              <input type="hidden" id="editReferenciaId" name="idReferencia">

              <div class="mb-3">
                <label for="editComentario" class="form-label">Comentario <span class="text-danger">*</span></label>
                <textarea class="form-control" id="editComentario" name="comentario" rows="5" required></textarea>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="editPuntuacion" class="form-label">Puntuación (0.0 - 5.0)</label>
                  <input type="number" class="form-control" id="editPuntuacion" name="puntuacion" min="0.0" max="5.0"
                    step="0.1">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="editTipoReferencia" class="form-label">Tipo de Referencia <span
                      class="text-danger">*</span></label>
                  <select class="form-select" id="editTipoReferencia" name="tipo_referencia_id_tipo_referencia"
                    required>
                    <?php foreach ($tipos_referencia as $tipo): ?>
                      <option value="<?php echo $tipo['id_tipo_referencia']; ?>">
                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label for="editEstadoReferencia" class="form-label">Estado de la Referencia <span
                    class="text-danger">*</span></label>
                <select class="form-select" id="editEstadoReferencia" name="estado_id_estado" required>
                  <?php foreach ($estados_referencia as $estado): ?>
                    <option value="<?php echo $estado['id_estado']; ?>">
                      <?php echo htmlspecialchars($estado['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-warning">Guardar Cambios</button>
            </div>
          </form>
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
            <small class="text-muted">Gestión de Referencias - Desarrollado con Bootstrap
              <?php echo date('Y'); ?></small>
          </div>
        </div>
      </div>
    </footer>

    <script src="../js/jquery-3.6.1.min.js"></script>
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../sw/dist/sweetalert2.min.js"></script>
    <script src="../js/funcionesGreferencias.js"></script>
    <script>
      // Pasar las variables PHP a la función de inicialización de JavaScript
      $(document).ready(function () {
        initializeGestionReferencias(
          <?php echo json_encode($tipos_referencia); ?>,
          <?php echo json_encode($estados_referencia); ?>
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