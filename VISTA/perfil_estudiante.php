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
      <link rel='stylesheet' href='../SW/dist/sweetalert2.min.css'>
      <script src='../SW/dist/sweetalert2.min.js'></script>
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
    <link rel='stylesheet' href='./SW/dist/sweetalert2.min.css'>
    <script src='./SW/dist/sweetalert2.min.js'></script>
  </head>
  <body>
    <script type='text/javascript'>
      Swal.fire({
        icon: 'error',
        title: '¡ERROR!',
        text: ' Debe iniciar Sesión como Estudiante en el Sistema'
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil de Estudiante - PPUD</title>
  <link rel="stylesheet" href="../BOOTSTRAP/css/bootstrap.min.css">
  <link rel="stylesheet" href="../SW/dist/sweetalert2.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-light">
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="pruebaEstudiante.php">
        <span class="text-primary">PPUD</span> - Portal Estudiantil
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="pruebaEstudiante.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="perfil_estudiante.php">Mi Perfil</a>
          </li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#" onclick="mostrarPerfilEstudiante()">Ver Perfil</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="../salir.php">Cerrar Sesión</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Jumbotron de bienvenida -->
  <div class="bg-primary text-white py-4 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-5 fw-bold">🎓 Mi Perfil de Estudiante</h1>
          <p class="lead">Actualiza tu información personal y académica</p>
        </div>
      </div>
    </div>
  </div>

  <div class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="card shadow mb-4">
          <!-- Añadido mb-4 para espacio entre tarjetas -->
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Datos Personales y Académicos</h5>
          </div>
          <div class="card-body">
            <form id="studentProfileForm">
              <input type="hidden" id="idEstudiante" name="idEstudiante"
                value="<?php echo htmlspecialchars($_SESSION['usuario_id'] ?? ''); ?>">
              <!-- Input hidden para mantener el path actual de la hoja de vida -->
              <input type="hidden" id="hoja_vida_path_current" name="hoja_vida_path_current">

              <!-- Campos de visualización (Modo Lectura) -->
              <div id="viewMode">
                <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Información Personal</h6>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Nombre:</strong> <span id="viewNombre"></span>
                  </div>
                  <div class="col-md-6">
                    <strong>Apellidos:</strong> <span id="viewApellidos"></span>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Tipo Documento:</strong> <span id="viewTipoDocNombre"></span>
                  </div>
                  <div class="col-md-6">
                    <strong>Número Documento:</strong> <span id="viewNDoc"></span>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Fecha de Nacimiento:</strong> <span id="viewFechaNac"></span>
                  </div>
                  <div class="col-md-6">
                    <strong>Ciudad:</strong> <span id="viewCiudadNombre"></span>
                  </div>
                </div>
                <div class="mb-4">
                  <strong>Dirección:</strong> <span id="viewDireccion"></span>
                </div>

                <h6 class="text-primary mb-3"><i class="fas fa-at me-2"></i>Información de Contacto</h6>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Correo:</strong> <span id="viewCorreo"></span>
                  </div>
                  <div class="col-md-6">
                    <strong>Teléfono:</strong> <span id="viewTelefono"></span>
                  </div>
                </div>
                <hr class="my-4">

                <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Información Académica</h6>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Código Estudiante:</strong> <span id="viewCodigoEstudiante"></span>
                  </div>
                  <div class="col-md-6">
                    <strong>Carrera Principal:</strong> <span id="viewCarreraPrincipal"></span>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col-md-6">
                    <strong>Semestre:</strong> <span id="viewSemestre"></span>
                  </div>
                  <div class="col-md-6">
                    <strong>Promedio Académico:</strong> <span id="viewPromedioAcademico"></span>
                  </div>
                </div>
                <div class="mb-4">
                  <strong>Carreras de Interés:</strong>
                  <ul id="viewCarrerasInteresList" class="list-group list-group-flush mt-2">
                    <!-- Las carreras de interés se cargarán aquí -->
                  </ul>
                </div>
                <hr class="my-4">

                <h6 class="text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>Habilidades e Intereses</h6>
                <div class="mb-3">
                  <strong>Habilidades:</strong> <span id="viewHabilidades"></span>
                </div>
                <div class="mb-3">
                  <strong>Experiencia Laboral:</strong> <span id="viewExperienciaLaboral"></span>
                </div>
                <div class="mb-3">
                  <strong>Certificaciones:</strong> <span id="viewCertificaciones"></span>
                </div>
                <div class="mb-3">
                  <strong>Idiomas:</strong> <span id="viewIdiomas"></span>
                </div>
                <div class="mb-3">
                  <strong>Objetivos Profesionales:</strong> <span id="viewObjetivosProfesionales"></span>
                </div>
                <hr class="my-4">

                <h6 class="text-primary mb-3"><i class="fas fa-file-pdf me-2"></i>Hoja de Vida (PDF)</h6>
                <div class="mb-3" id="viewHojaVida">
                  <!-- El enlace a la hoja de vida se cargará aquí por JS -->
                  No cargada
                </div>
              </div>

              <!-- Campos de Edición (Modo Edición) -->
              <div id="editMode" style="display:none;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="apellidos" class="form-label">Apellidos</label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="fechaNac" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fechaNac" name="fechaNac" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="n_doc" class="form-label">Número de Documento</label>
                    <input type="text" class="form-control" id="n_doc" name="n_doc" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="tipo_documento_id_tipo" class="form-label">Tipo de Documento</label>
                    <select class="form-select" id="tipo_documento_id_tipo" name="tipo_documento_id_tipo" required>
                      <!-- Opciones cargadas por JS -->
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="ciudad_id_ciudad" class="form-label">Ciudad de Residencia</label>
                    <select class="form-select" id="ciudad_id_ciudad" name="ciudad_id_ciudad" required>
                      <!-- Opciones cargadas por JS -->
                    </select>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="direccion" class="form-label">Dirección</label>
                  <input type="text" class="form-control" id="direccion" name="direccion" required>
                </div>
                <div class="mb-3">
                  <label for="codigo_estudiante" class="form-label">Código de Estudiante</label>
                  <input type="text" class="form-control" id="codigo_estudiante" name="codigo_estudiante">
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="semestre" class="form-label">Semestre Actual</label>
                    <input type="number" class="form-control" id="semestre" name="semestre" min="1" max="12">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="promedio_academico" class="form-label">Promedio Académico</label>
                    <input type="number" class="form-control" id="promedio_academico" name="promedio_academico"
                      step="0.01" min="0" max="5">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="habilidades" class="form-label">Habilidades (separadas por coma)</label>
                  <textarea class="form-control" id="habilidades" name="habilidades" rows="2"></textarea>
                </div>
                <div class="mb-3">
                  <label for="experiencia_laboral" class="form-label">Experiencia Laboral</label>
                  <textarea class="form-control" id="experiencia_laboral" name="experiencia_laboral"
                    rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label for="certificaciones" class="form-label">Certificaciones</label>
                  <textarea class="form-control" id="certificaciones" name="certificaciones" rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label for="idiomas" class="form-label">Idiomas</label>
                  <textarea class="form-control" id="idiomas" name="idiomas" rows="2"></textarea>
                </div>
                <div class="mb-3">
                  <label for="objetivos_profesionales" class="form-label">Objetivos Profesionales</label>
                  <textarea class="form-control" id="objetivos_profesionales" name="objetivos_profesionales"
                    rows="3"></textarea>
                </div>
                <div class="mb-3">
                  <label for="carrera_id_carrera" class="form-label">Carrera Principal</label>
                  <select class="form-select" id="carrera_id_carrera" name="carrera_id_carrera">
                    <!-- Opciones cargadas por JS -->
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Carreras de Interés:</label>
                  <div id="carrerasInteresCheckboxes" class="row">
                    <!-- Checkboxes de carreras se cargarán con JavaScript -->
                  </div>
                </div>

                <!-- Sección para cargar Hoja de Vida (PDF) -->
                <div class="mb-3">
                  <label for="hoja_vida_pdf" class="form-label">Subir Hoja de Vida (PDF, máx. 5MB)</label>
                  <input class="form-control" type="file" id="hoja_vida_pdf" name="hoja_vida_pdf" accept=".pdf">
                  <div id="currentHojaVidaContainer" class="mt-2" style="display:none;">
                    <small class="text-muted">Hoja de Vida actual: <span id="currentHojaVidaLink"></span></small>
                  </div>
                </div>
              </div>

              <!-- Botones de acción -->
              <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-success me-2" id="editProfileBtn">
                  <i class="fas fa-edit me-2"></i>Editar Perfil
                </button>
                <button type="submit" class="btn btn-success me-2" id="saveProfileBtn" style="display:none;">
                  <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
                <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display:none;">
                  <i class="fas fa-times-circle me-2"></i>Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sección de Referencias del Estudiante -->
        <div class="card shadow mb-4">
          <!-- Añadido mb-4 para espacio entre tarjetas -->
          <div class="card-header bg-primary text-white">
            <!-- Color unificado a bg-primary -->
            <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Referencias Recibidas</h5>
          </div>
          <div class="card-body">
            <div id="referenciasEstudianteList" class="list-group">
              <!-- Las referencias se cargarán aquí por JavaScript -->
              <p class="text-muted text-center">Cargando referencias...</p>
            </div>
          </div>
        </div>

        <!-- Sección de Cambio de Contraseña -->
        <div class="card shadow mb-4">
          <!-- Añadido mb-4 para espacio entre tarjetas -->
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <!-- Color unificado y flex para botón -->
            <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Cambiar Contraseña</h5>
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapsePassword" aria-expanded="false" aria-controls="collapsePassword">
              <i class="fas fa-chevron-down"></i> <!-- Icono para indicar colapsable -->
            </button>
          </div>
          <div class="collapse" id="collapsePassword">
            <!-- Contenido colapsable -->
            <div class="card-body">
              <form id="changePasswordForm">
                <div class="mb-3">
                  <label for="current_password" class="form-label">Contraseña Actual</label>
                  <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                  <label for="new_password" class="form-label">Nueva Contraseña</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                  <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña</label>
                  <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password"
                    required>
                </div>
                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-warning">
                    <i class="fas fa-key me-2"></i>Guardar Nueva Contraseña
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
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

  <script src="../JS/jquery-3.6.1.min.js"></script>
  <script src="../BOOTSTRAP/js/bootstrap.bundle.min.js"></script>
  <script src="../SW/dist/sweetalert2.min.js"></script>
  <script src="../JS/perfilE.js"></script> <!-- Script JS para la lógica del perfil -->
</body>

</html>