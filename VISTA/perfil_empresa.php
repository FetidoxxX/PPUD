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
    <link rel='stylesheet' href='./sw/dist/sweetalert2.min.css'>
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

// Incluir archivos necesarios para el manejo de la empresa
require_once '../MODELO/class_empresa.php';

// Crear instancia de la clase Empresa. La conexión se maneja internamente.
$empresaObj = new Empresa();

// Obtener datos estáticos para los selectores del formulario
$tipos_documento = $empresaObj->obtenerTiposDocumento();
$ciudades = $empresaObj->obtenerCiudades();
$sectores = $empresaObj->obtenerSectores();
$estados = $empresaObj->obtenerEstados();

// La conexión se cerrará automáticamente cuando el script termine y el objeto $empresaObj sea destruido.
// No es necesario llamar a mysqli_close().
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <title>Mi Perfil - Empresa</title>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <link rel='stylesheet' type='text/css' media='screen' href='../bootstrap/css/bootstrap.min.css'>
  <link rel='stylesheet' href='../sw/dist/sweetalert2.min.css'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .profile-section {
      margin-bottom: 2rem;
    }

    .profile-card {
      border-radius: 1rem;
      overflow: hidden;
    }

    .profile-header {
      background-color: #28a745;
      /* Verde de éxito de Bootstrap */
      color: white;
      padding: 1.5rem;
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .profile-header i {
      font-size: 2.5rem;
    }

    .info-item {
      display: flex;
      align-items: center;
      margin-bottom: 0.75rem;
    }

    .info-item i {
      color: #6c757d;
      /* Gris de texto secundario */
      margin-right: 0.75rem;
      width: 1.25rem;
      /* Ancho fijo para alinear iconos */
      text-align: center;
    }

    .info-label {
      font-weight: bold;
      color: #343a40;
      /* Gris oscuro para las etiquetas */
      flex-shrink: 0;
      /* Evita que la etiqueta se encoja */
      min-width: 120px;
      /* Ancho mínimo para las etiquetas */
    }

    .info-value {
      color: #495057;
      /* Gris un poco más claro para los valores */
      flex-grow: 1;
      /* Permite que el valor ocupe el espacio restante */
    }

    /* Estilo para los campos de formulario cuando están en modo de solo lectura */
    .form-control[readonly],
    .form-select[disabled],
    .form-control-plaintext {
      background-color: #e9ecef;
      /* Un gris claro para indicar no editable */
      opacity: 1;
      /* Asegura que no sea transparente */
    }
  </style>
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
            <a class="nav-link" href="gestion_oferta_empresa.php">Gestionar Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_estudiantes_empresa.php">Gestionar Estudiantes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="perfil_empresa.php">Mi Perfil</a>
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

  <!-- Jumbotron de Perfil de Empresa -->
  <div class="bg-success text-white py-5 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">Perfil de mi Empresa</h1>
          <p class="lead">Aquí puedes ver y actualizar la información de tu organización.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="container mb-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-lg border-0 profile-card">
          <div class="profile-header">
            <i class="fas fa-building"></i>
            <h4 class="mb-0">Detalles de la Empresa</h4>
          </div>
          <div class="card-body p-4">
            <form id="empresaProfileForm">
              <input type="hidden" id="idEmpresa" name="idEmpresa"
                value="<?php echo htmlspecialchars($_SESSION['usuario_id']); ?>">

              <!-- View Mode Display -->
              <div id="viewMode" class="profile-section">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-id-badge"></i>
                      <span class="info-label">ID Empresa:</span>
                      <span class="info-value" id="view_idEmpresa"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-building"></i>
                      <span class="info-label">Nombre:</span>
                      <span class="info-value" id="view_nombre"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-at"></i>
                      <span class="info-label">Correo:</span>
                      <span class="info-value" id="view_correo"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-phone-alt"></i>
                      <span class="info-label">Teléfono:</span>
                      <span class="info-value" id="view_telefono"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-map-marker-alt"></i>
                      <span class="info-label">Dirección:</span>
                      <span class="info-value" id="view_direccion"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-file-alt"></i>
                      <span class="info-label">Documento (NIT):</span>
                      <span class="info-value" id="view_n_doc"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-id-card"></i>
                      <span class="info-label">Tipo Doc.:</span>
                      <span class="info-value" id="view_tipo_documento_id_tipo"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-city"></i>
                      <span class="info-label">Ciudad:</span>
                      <span class="info-value" id="view_ciudad_id_ciudad"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-industry"></i>
                      <span class="info-label">Sector:</span>
                      <span class="info-value" id="view_sector_id_sector"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-globe"></i>
                      <span class="info-label">Sitio Web:</span>
                      <span class="info-value" id="view_sitio_web"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-users"></i>
                      <span class="info-label">Empleados:</span>
                      <span class="info-value" id="view_numero_empleados"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-calendar-alt"></i>
                      <span class="info-label">Fundación:</span>
                      <span class="info-value" id="view_ano_fundacion"></span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <div class="info-item">
                      <i class="fas fa-check-circle"></i>
                      <span class="info-label">Estado Cuenta:</span>
                      <span class="info-value" id="view_estado_id_estado"></span>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <span class="info-label d-block mb-1"><i class="fas fa-info-circle me-2"></i> Descripción:</span>
                  <p class="info-value-block" id="view_descripcion"></p>
                </div>
              </div>

              <!-- Edit Mode Form (initially hidden/readonly) -->
              <div id="editMode" style="display:none;">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="nombre" class="form-label fw-bold">Nombre de la Empresa <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                  </div>
                  <div class="col-md-6">
                    <label for="correo" class="form-label fw-bold">Correo Electrónico <span
                        class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="correo" name="correo" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="telefono" class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required>
                  </div>
                  <div class="col-md-6">
                    <label for="direccion" class="form-label fw-bold">Dirección <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="direccion" name="direccion" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="n_doc" class="form-label fw-bold">Número de Documento (NIT) <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="n_doc" name="n_doc" required readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="tipo_documento_id_tipo" class="form-label fw-bold">Tipo de Documento <span
                        class="text-danger">*</span></label>
                    <select class="form-select" id="tipo_documento_id_tipo" name="tipo_documento_id_tipo" required>
                      <!-- Opciones cargadas por JS -->
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="ciudad_id_ciudad" class="form-label fw-bold">Ciudad <span
                        class="text-danger">*</span></label>
                    <select class="form-select" id="ciudad_id_ciudad" name="ciudad_id_ciudad" required>
                      <!-- Opciones cargadas por JS -->
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="sector_id_sector" class="form-label fw-bold">Sector Empresarial</label>
                    <select class="form-select" id="sector_id_sector" name="sector_id_sector">
                      <!-- Opciones cargadas por JS -->
                    </select>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="descripcion" class="form-label fw-bold">Descripción de la Empresa</label>
                  <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="sitio_web" class="form-label fw-bold">Sitio Web</label>
                    <input type="url" class="form-control" id="sitio_web" name="sitio_web"
                      placeholder="https://www.tuempresa.com">
                  </div>
                  <div class="col-md-6">
                    <label for="numero_empleados" class="form-label fw-bold">Número de Empleados</label>
                    <input type="number" class="form-control" id="numero_empleados" name="numero_empleados" min="1">
                  </div>
                </div>

                <div class="row mb-4">
                  <div class="col-md-6">
                    <label for="ano_fundacion" class="form-label fw-bold">Año de Fundación</label>
                    <input type="number" class="form-control" id="ano_fundacion" name="ano_fundacion" min="1900"
                      max="<?php echo date('Y'); ?>">
                  </div>
                  <div class="col-md-6">
                    <label for="estado_id_estado" class="form-label fw-bold">Estado de la Cuenta</label>
                    <select class="form-select" id="estado_id_estado" name="estado_id_estado">
                      <!-- Opciones cargadas por JS -->
                    </select>
                  </div>
                </div>
              </div> <!-- End editMode -->

              <div class="profile-header mt-4">
                <i class="fas fa-address-book"></i>
                <h5 class="mb-0">Persona de Contacto</h5>
              </div>
              <div class="card-body p-4 pt-3">
                <!-- View Mode Display for Contact -->
                <div id="viewModeContact" class="profile-section">
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span class="info-label">Nombres:</span>
                        <span class="info-value" id="view_contacto_nombres"></span>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span class="info-label">Apellidos:</span>
                        <span class="info-value" id="view_contacto_apellidos"></span>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="info-item">
                        <i class="fas fa-user-tie"></i>
                        <span class="info-label">Cargo:</span>
                        <span class="info-value" id="view_contacto_cargo"></span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Edit Mode Form for Contact (initially hidden/readonly) -->
                <div id="editModeContact" style="display:none;">
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label for="contacto_nombres" class="form-label">Nombres</label>
                      <input type="text" class="form-control" id="contacto_nombres" name="contacto_nombres">
                    </div>
                    <div class="col-md-4">
                      <label for="contacto_apellidos" class="form-label">Apellidos</label>
                      <input type="text" class="form-control" id="contacto_apellidos" name="contacto_apellidos">
                    </div>
                    <div class="col-md-4">
                      <label for="contacto_cargo" class="form-label">Cargo</label>
                      <input type="text" class="form-control" id="contacto_cargo" name="contacto_cargo">
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-warning btn-lg rounded-pill" id="editButton"
                  onclick="toggleEditMode(true)">
                  <i class="fas fa-edit me-2"></i> Editar Perfil
                </button>
                <button type="submit" class="btn btn-success btn-lg rounded-pill" id="saveButton" style="display:none;">
                  <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
                <button type="button" class="btn btn-secondary btn-lg rounded-pill" id="cancelButton"
                  style="display:none;" onclick="toggleEditMode(false)">
                  <i class="fas fa-times me-2"></i> Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mt-4">
    <ol class="breadcrumb container">
      <li class="breadcrumb-item"><a href="pruebaEmpresa.php">Módulo Empresa</a></li>
      <li class="breadcrumb-item active" aria-current="page">Mi Perfil</li>
    </ol>
  </nav>

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
  <script src="../js/funcionesEmpresaPerfil.js"></script>
  <script>
    // Variables PHP pasadas al JavaScript
    const tiposDocumentoData = <?php echo json_encode($tipos_documento); ?>;
    const ciudadesData = <?php echo json_encode($ciudades); ?>;
    const sectoresData = <?php echo json_encode($sectores); ?>;
    const estadosData = <?php echo json_encode($estados); ?>;

    $(document).ready(function () {
      // Inicializar las opciones de los selectores al cargar la página
      renderSelectOptions(tiposDocumentoData, 'tipo_documento_id_tipo', 'id_tipo', 'nombre');
      renderSelectOptions(ciudadesData, 'ciudad_id_ciudad', 'id_ciudad', 'nombre');
      renderSelectOptions(sectoresData, 'sector_id_sector', 'id_sector', 'nombre');
      renderSelectOptions(estadosData, 'estado_id_estado', 'id_estado', 'nombre');

      loadCompanyProfile(); // Cargar los datos de la empresa al inicio

      // Manejar el envío del formulario de perfil
      $('#empresaProfileForm').submit(function (event) {
        event.preventDefault(); // Evita el envío tradicional del formulario
        saveCompanyProfile();
      });
    });

    // Función para mostrar el perfil de la empresa en un modal (se mantiene si otras partes de la app lo usan)
    function mostrarPerfilEmpresaModal() {
      Swal.fire({
        title: 'Perfil de Empresa',
        html: `
          <div class="text-start">
            <div class="mb-2"><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?></div>
            <div class="mb-2"><strong>ID de Empresa:</strong> <?php echo htmlspecialchars($_SESSION['usuario_id']); ?></div>
            <div class="mb-2"><strong>Tipo de Cuenta:</strong> <span class="badge bg-success">Empresa</span></div>
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