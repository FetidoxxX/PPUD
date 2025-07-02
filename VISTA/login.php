<?php
// Recibir rol por POST o GET (para compatibilidad)
$rol = '';
if (isset($_POST['rol'])) {
  $rol = $_POST['rol'];
} elseif (isset($_GET['rol'])) {
  $rol = $_GET['rol'];
}

// Si no hay rol, redirigir a index.php
if (!$rol) {
  header('Location: ../index.php');
  exit();
}

// Validar que el rol sea válido
$roles_validos = ['estudiante', 'empresa', 'administrador'];
if (!in_array($rol, $roles_validos)) {
  header('Location: ../index.php');
  exit();
}

// Configurar colores y iconos según el rol
$config_rol = [
  'estudiante' => [
    'color' => 'primary',
    'icono' => '🎓',
    'titulo' => 'Portal Estudiantil',
    'descripcion' => 'Accede a tu cuenta para buscar prácticas profesionales'
  ],
  'empresa' => [
    'color' => 'success',
    'icono' => '🏢',
    'titulo' => 'Portal Empresarial',
    'descripcion' => 'Gestiona ofertas laborales y encuentra talento'
  ],
  'administrador' => [
    'color' => 'warning',
    'icono' => '⚙️',
    'titulo' => 'Panel Administrativo',
    'descripcion' => 'Administra la plataforma y supervisa el sistema'
  ]
];

$config = $config_rol[$rol];
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../sw/dist/sweetalert2.min.css">
  <script src="../js/funciones.js" defer></script>
  <title>Iniciar Sesión - PPUD</title>
</head>

<body onload="limpiar();" class="bg-light">
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="../index.php">
        <span class="text-warning">PPUD</span> - Plataforma de Prácticas
      </a>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="../index.php">
            <span class="me-1">←</span> Cambiar Rol
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Jumbotron específico del rol -->
  <div class="bg-<?php echo $config['color']; ?> text-white py-4 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <div class="display-1 mb-3"><?php echo $config['icono']; ?></div>
          <h2 class="fw-bold"><?php echo $config['titulo']; ?></h2>
          <p class="lead mb-0"><?php echo $config['descripcion']; ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-5 col-md-7">
        <!-- Card principal de login -->
        <div class="card shadow-sm border-0">
          <div class="card-header bg-<?php echo $config['color']; ?> text-white">
            <div class="text-center">
              <h4 class="mb-2 fw-bold">Iniciar Sesión</h4>
              <span class="badge bg-light text-dark fs-6">
                Acceso como <?php echo ucfirst($rol); ?>
              </span>
            </div>
          </div>

          <div class="card-body p-4">
            <form name="form" action="../CONTROLADOR/verifica.php" method="post">
              <!-- Campo oculto para enviar el rol -->
              <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">

              <div class="mb-4">
                <label for="user" class="form-label fw-bold">
                  <span class="me-2">👤</span>Usuario
                </label>
                <input type="text" name="user" id="user" class="form-control form-control-lg border-2"
                  placeholder="Digite su nombre de usuario" required>
              </div>

              <div class="mb-4">
                <label for="passw" class="form-label fw-bold">
                  <span class="me-2">🔒</span>Contraseña
                </label>
                <input type="password" name="passw" id="passw" class="form-control form-control-lg border-2"
                  placeholder="Digite su contraseña" required>
              </div>

              <div class="d-grid gap-3">
                <button type="submit" class="btn btn-<?php echo $config['color']; ?> btn-lg fw-bold">
                  <span class="me-2"></span>
                  Iniciar Sesión
                </button>
              </div>
            </form>
          </div>

          <div class="card-footer bg-light">
            <div class="text-center">
              <h6 class="text-muted mb-3">¿No tienes una cuenta?</h6>
              <a href="registro.php?rol=<?php echo urlencode($rol); ?>"
                class="btn btn-outline-<?php echo $config['color']; ?> btn-lg w-100 mb-3">
                <span class="me-2">✨</span>
                Crear Nueva Cuenta
              </a>
              <!--  recuperación de contraseña pasando el rol-->
              <div class="small mt-3">
                <a href="recuperacion.php?rol=<?php echo urlencode($rol); ?>" class="text-muted">¿Olvidaste tu
                  contraseña?</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mt-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page">Login <?php echo ucfirst($rol); ?></li>
      </ol>
    </nav>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> PPUD - Plataforma de Prácticas Profesionales.</p>
          <small class="text-muted">Acceso seguro para <?php echo $rol; ?>s</small>
        </div>
      </div>
    </div>
  </footer>

  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
</body>

</html>