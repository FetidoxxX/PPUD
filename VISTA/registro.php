<?php

include_once '../MODELO/class_oferta.php'; // Para obtener carreras (estudiante)
include_once '../MODELO/class_empresa.php'; // Para obtener tipos de documento, ciudades y sectores (empresa/admin)

$ofertaObj = new Oferta();
$empresaObj = new Empresa(); // Usamos Empresa para obtener datos generales como tipos de documento, ciudades, sectores

// Recibir rol por POST o GET
$rol = '';
if (isset($_POST['rol'])) {
  $rol = $_POST['rol'];
} elseif (isset($_GET['rol'])) {
  $rol = $_GET['rol'];
}

// Si no hay rol o no es válido, redirigir a index.php
$roles_validos = ['estudiante', 'empresa', 'administrador'];
if (!$rol || !in_array($rol, $roles_validos)) {
  header('Location: ../index.php');
  exit();
}

// Configurar colores, títulos e iconos según el rol
$config_rol = [
  'estudiante' => [
    'color' => 'primary',
    'icono' => '🎓',
    'titulo' => 'Registro de Estudiante',
    'descripcion' => 'Complete todos los campos para crear su cuenta estudiantil'
  ],
  'empresa' => [
    'color' => 'success',
    'icono' => '🏢',
    'titulo' => 'Registro de Empresa',
    'descripcion' => 'Únase a nuestra plataforma para ofrecer prácticas profesionales'
  ],
  'administrador' => [
    'color' => 'warning',
    'icono' => '⚙️',
    'titulo' => 'Registro de Administrador',
    'descripcion' => 'Acceso restringido - Solo personal autorizado'
  ]
];
$config = $config_rol[$rol];

// Obtener datos para los selectores según el rol
$tiposDoc = $empresaObj->obtenerTiposDocumento();
$carreras = ($rol === 'estudiante') ? $ofertaObj->obtenerCarreras() : [];
$ciudades = ($rol === 'estudiante' || $rol === 'empresa') ? $empresaObj->obtenerCiudades() : []; // Ambas necesitan ciudades
$sectores = ($rol === 'empresa') ? $empresaObj->obtenerSectores() : [];

?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../sw/dist/sweetalert2.min.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <title><?php echo $config['titulo']; ?> - PPUD</title>
</head>

<body class="bg-light">
  <div class="container mt-4 mb-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow border-<?php echo $config['color']; ?>">
          <div
            class="card-header bg-<?php echo $config['color']; ?> text-<?php echo ($rol === 'administrador') ? 'dark' : 'white'; ?> ">
            <div class="text-center">
              <div class="mb-2">
                <div
                  class="bg-<?php echo ($rol === 'administrador') ? 'dark' : 'white'; ?> bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center"
                  style="width: 60px; height: 60px;">
                  <span class="fs-2"><?php echo $config['icono']; ?></span>
                </div>
              </div>
              <h3 class="mb-0"><?php echo $config['titulo']; ?></h3>
              <small class="opacity-75"><?php echo $config['descripcion']; ?></small>
            </div>
          </div>
          <div class="card-body">
            <?php if ($rol === 'administrador'): ?>
              <div class="alert alert-warning mb-4">
                <span class="material-icons me-2">security</span>
                <strong>Acceso Restringido:</strong> Este formulario es exclusivo para personal administrativo
                autorizado.
              </div>
            <?php endif; ?>

            <form id="formRegistro" name="formRegistro" action="#" method="post">
              <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">

              <div class="row">
                <!-- Campos de Autenticación -->
                <div class="col-12">
                  <h5 class="text-muted mb-3">Credenciales de Acceso</h5>
                </div>

                <?php if ($rol === 'estudiante'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="idEstudiante" class="form-label">
                      <span class="material-icons me-2">person</span>ID Estudiante *
                    </label>
                    <input type="text" name="idEstudiante" id="idEstudiante" class="form-control"
                      placeholder="Ej: EST001">
                  </div>
                <?php elseif ($rol === 'empresa'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="idEmpresa" class="form-label">
                      <span class="material-icons me-2">business</span>ID Empresa *
                    </label>
                    <input type="text" name="idEmpresa" id="idEmpresa" class="form-control" placeholder="Ej: EMP001">
                  </div>
                <?php elseif ($rol === 'administrador'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="idAdministrador" class="form-label">
                      <span class="material-icons me-2">admin_panel_settings</span>ID Administrador *
                    </label>
                    <input type="text" name="idAdministrador" id="idAdministrador" class="form-control"
                      placeholder="Ej: ADM001">
                  </div>
                <?php endif; ?>

                <div class="col-md-6 mb-3">
                  <label for="contrasena" class="form-label">
                    <span class="material-icons me-2">lock</span>Contraseña *
                  </label>
                  <input type="password" name="contrasena" id="contrasena" class="form-control">
                  <div id="contrasena-feedback" class="small"></div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="confirmar_contrasena" class="form-label">
                    <span class="material-icons me-2">lock</span>Confirmar Contraseña *
                  </label>
                  <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control">
                </div>

                <!-- Información Personal/Empresarial -->
                <div class="col-12">
                  <hr>
                  <h5 class="text-muted mb-3">Información
                    <?php echo ($rol === 'empresa') ? 'de la Empresa' : 'Personal'; ?>
                  </h5>
                </div>

                <?php if ($rol === 'estudiante'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">
                      <span class="material-icons me-2">person</span>Nombre(s) *
                    </label>
                    <input type="text" name="nombre" id="nombre" class="form-control">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="apellidos" class="form-label">
                      <span class="material-icons me-2">person</span>Apellidos *
                    </label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control">
                  </div>
                <?php elseif ($rol === 'administrador'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="nombres" class="form-label">
                      <span class="material-icons me-2">person</span>Nombres *
                    </label>
                    <input type="text" name="nombres" id="nombres" class="form-control">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="apellidos" class="form-label">
                      <span class="material-icons me-2">person</span>Apellidos *
                    </label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control">
                  </div>
                <?php elseif ($rol === 'empresa'): ?>
                  <div class="col-md-12 mb-3">
                    <label for="nombre_empresa" class="form-label">
                      <span class="material-icons me-2">business</span>Nombre de la Empresa *
                    </label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" class="form-control"
                      placeholder="Nombre completo de la empresa">
                  </div>
                <?php endif; ?>

                <div class="col-md-6 mb-3">
                  <label for="tipo_documento" class="form-label">
                    <span class="material-icons me-2">badge</span>Tipo de Documento *
                  </label>
                  <select name="tipo_documento" id="tipo_documento" class="form-select">
                    <option value="">Seleccione...</option>
                    <?php
                    // Se simplifica la lógica. 'obtenerTiposDocumento()' siempre devuelve un array.
                    if (!empty($tiposDoc)) { // Verifica si el array no está vacío
                      foreach ($tiposDoc as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['id_tipo']) ?>"><?= htmlspecialchars($tipo['nombre']) ?>
                        </option>
                      <?php endforeach;
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="n_doc" class="form-label">
                    <span class="material-icons me-2">credit_card</span>Número de Documento *
                  </label>
                  <input type="text" name="n_doc" id="n_doc" class="form-control">
                </div>

                <!-- Campos específicos de Estudiante -->
                <?php if ($rol === 'estudiante'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="fechaNac" class="form-label">
                      <span class="material-icons me-2">cake</span>Fecha de Nacimiento *
                    </label>
                    <input type="date" name="fechaNac" id="fechaNac" class="form-control">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="carrera_id_carrera" class="form-label">
                      <span class="material-icons me-2">school</span>Carrera *
                    </label>
                    <select name="carrera_id_carrera" id="carrera_id_carrera" class="form-select">
                      <option value="">Seleccione su carrera...</option>
                      <?php if (!empty($carreras)) {
                        foreach ($carreras as $carrera): ?>
                          <option value="<?= htmlspecialchars($carrera['id_carrera']) ?>">
                            <?= htmlspecialchars($carrera['nombre']) ?>
                          </option>
                        <?php endforeach;
                      } ?>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="semestre" class="form-label">
                      <span class="material-icons me-2">grade</span>Semestre actual *
                    </label>
                    <input type="number" name="semestre" id="semestre" class="form-control" placeholder="Ej: 5" min="1">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="promedio_academico" class="form-label">
                      <span class="material-icons me-2">star_half</span>Promedio Académico *
                    </label>
                    <input type="text" name="promedio_academico" id="promedio_academico" class="form-control"
                      placeholder="Ej: 4.0 (usar punto)">
                  </div>
                <?php endif; ?>

                <!-- Campos específicos de Empresa -->
                <?php if ($rol === 'empresa'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="sector_empresarial" class="form-label">
                      <span class="material-icons me-2">work</span>Sector Empresarial *
                    </label>
                    <select name="sector_empresarial" id="sector_empresarial" class="form-select">
                      <option value="">Seleccione un sector...</option>
                      <?php if (!empty($sectores)) {
                        foreach ($sectores as $sector): ?>
                          <option value="<?= htmlspecialchars($sector['id_sector']) ?>">
                            <?= htmlspecialchars($sector['nombre']) ?>
                          </option>
                        <?php endforeach;
                      } ?>
                    </select>
                  </div>
                  <div class="col-md-12 mb-3">
                    <label for="descripcion" class="form-label">
                      <span class="material-icons me-2">description</span>Descripción de la Empresa *
                    </label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3"
                      placeholder="Breve descripción de la empresa"></textarea>
                  </div>
                <?php endif; ?>

                <!-- Información de Contacto (Común a casi todos) -->
                <div class="col-12">
                  <hr>
                  <h5 class="text-muted mb-3">Información de Contacto</h5>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="correo" class="form-label">
                    <span class="material-icons me-2">email</span>Correo Electrónico *
                  </label>
                  <input type="email" name="correo" id="correo" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="telefono" class="form-label">
                    <span class="material-icons me-2">phone</span>Teléfono *
                  </label>
                  <input type="tel" name="telefono" id="telefono" class="form-control">
                </div>
                <div class="col-12 mb-3">
                  <label for="direccion" class="form-label">
                    <span class="material-icons me-2">home</span>Dirección *
                  </label>
                  <textarea name="direccion" id="direccion" class="form-control" rows="2"
                    placeholder="Ingrese su dirección completa"></textarea>
                </div>
                <!-- Ciudad (Común a Estudiante y Empresa) -->
                <?php if ($rol === 'estudiante' || $rol === 'empresa'): ?>
                  <div class="col-md-6 mb-3">
                    <label for="ciudad_id_ciudad" class="form-label">
                      <span class="material-icons me-2">location_city</span>Ciudad *
                    </label>
                    <select name="ciudad_id_ciudad" id="ciudad_id_ciudad" class="form-select">
                      <option value="">Seleccione su ciudad...</option>
                      <?php
                      // Verificamos si hay ciudades disponibles
                      if (!empty($ciudades)) {
                        // Iteramos sobre las ciudades y las mostramos en el select
                        foreach ($ciudades as $ciudad): ?>
                          <option value="<?= htmlspecialchars($ciudad['id_ciudad']) ?>">
                            <?= htmlspecialchars($ciudad['nombre']) ?>
                          </option>
                        <?php endforeach;
                      } ?>
                    </select>
                  </div>
                <?php endif; ?>

              </div>

              <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-<?php echo $config['color']; ?> btn-lg">
                  <span class="material-icons me-2">
                    <?php echo ($rol === 'estudiante') ? 'person_add' : (($rol === 'empresa') ? 'business' : 'admin_panel_settings'); ?>
                  </span>
                  Registrar <?php echo ucfirst($rol); ?>
                </button>
                <button type="button" class="btn btn-outline-warning" id="btnLimpiar">
                  <span class="material-icons me-2">clear</span>
                  Limpiar Formulario
                </button>
                <a href="login.php?rol=<?php echo urlencode($rol); ?>" class="btn btn-outline-secondary">
                  <span class="material-icons me-2">arrow_back</span>
                  Volver al Login
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <script src="../js/funciones_registro_ajax.js"></script> <!-- Nuevo archivo JS para la lógica AJAX -->
</body>

</html>