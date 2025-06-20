<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <title>Bienvenida - PPUD</title>

</head>

<body class="bg-light d-flex flex-column min-vh-100">
  <!-- Contenido principal -->
  <!-- Barra de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container flex-grow-1">
      <a class="navbar-brand fw-bold" href="index.php">
        <span class="text-warning">PPUD</span> - Plataforma de Prácticas Profesionales
      </a>
    </div>
  </nav>

  <!-- Jumbotron de bienvenida -->
  <div class="bg-primary text-white py-5 mb-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-4 fw-bold">¡Bienvenido a PPUD!</h1>
          <p class="lead">Plataforma integral para la gestión de pasantias y prácticas profesionales universitarias</p>
          <p class="mb-0">Seleccione su rol para acceder al sistema</p>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <!-- Tarjetas de roles -->
    <div class="row g-4 justify-content-center mb-5">
      <!-- Estudiante -->
      <div class="col-lg-4 col-md-6">
        <form method="POST" action="login.php">
          <input type="hidden" name="rol" value="estudiante">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
              <div class="display-1 text-primary mb-3">🎓</div>
              <h4 class="card-title text-primary fw-bold">Estudiante</h4>
              <p class="card-text text-muted mb-4">
                Busca prácticas profesionales, gestiona tu perfil académico y postúlate a ofertas laborales
              </p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
              <button type="submit" class="btn btn-primary btn-lg w-100">
                <span class="fw-bold">Acceder como Estudiante</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Empresa -->
      <div class="col-lg-4 col-md-6">
        <form method="POST" action="login.php">
          <input type="hidden" name="rol" value="empresa">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
              <div class="display-1 text-success mb-3">🏢</div>
              <h4 class="card-title text-success fw-bold">Empresa</h4>
              <p class="card-text text-muted mb-4">
                Publica ofertas de prácticas, gestiona candidatos y encuentra el talento que necesitas
              </p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
              <button type="submit" class="btn btn-success btn-lg w-100">
                <span class="fw-bold">Acceder como Empresa</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Administrador -->
      <div class="col-lg-4 col-md-6">
        <form method="POST" action="login.php">
          <input type="hidden" name="rol" value="administrador">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
              <div class="display-1 text-warning mb-3">⚙</div>
              <h4 class="card-title text-warning fw-bold">Administrador</h4>
              <p class="card-text text-muted mb-4">
                Gestiona la plataforma completa, usuarios, configuraciones y supervisión del sistema
              </p>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
              <button type="submit" class="btn btn-warning btn-lg w-100">
                <span class="fw-bold">Acceder como Admin</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-4 mt-auto">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> PPUD - Plataforma de Prácticas y Pasantias Profesionales.
            Todos los
            derechos reservados.</p>
          <small class="text-muted">Conectando estudiantes con oportunidades profesionales</small>
        </div>
      </div>
    </div>
  </footer>

  <script src="./bootstrap/js/bootstrap.min.js"></script>
</body>

</html>