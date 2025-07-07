<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
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
include_once '../MODELO/class_empresa.php';
include_once '../MODELO/class_administrador.php'; // Necesario para la validación de estado del admin

// Crear conexión y instancia de empresa
$empresa = new Empresa();
$estados = $empresa->obtenerEstados(); // Obtener todos los estados

// Solo obtener tipos de documento para el modal de edición
$tipos_documento = $empresa->obtenerTiposDocumento();

// Validación de estado del administrador (copiado de pruebaAdmin.php)
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
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Empresas</title>
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
        <a href="gestion_empresas.php" class="list-group-item list-group-item-action bg-dark text-white active">Gestión
          Empresas</a>
        <a href="gestion_ofertas.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Ofertas</a>
        <a href="gestion_referencias.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Referencias</a>
        <a href="gestion_admin.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
          Administradores</a>
        <a href="gestion_varios.php" class="list-group-item list-group-item-action bg-dark text-white">Gestión
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
      <div class="bg-success text-white py-4 mb-4">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-8 mx-auto text-center">
              <h1 class="display-5 fw-bold">🏢 Gestión de Empresas</h1>
              <p class="lead">Administra y gestiona todas las empresas registradas en el sistema</p>
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
            <li class="breadcrumb-item active" aria-current="page">Gestión de Empresas</li>
          </ol>
        </nav>

        <!-- Card de búsqueda y estadísticas -->
        <div class="row mb-4">
          <div class="col-md-8">
            <div class="card shadow-sm">
              <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">🔍 Búsqueda de Empresas</h5>
              </div>
              <div class="card-body">
                <div class="input-group">
                  <input type="text" class="form-control" id="busquedaInput"
                    placeholder="Buscar por nombre, correo, teléfono, documento o ID...">
                  <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">Limpiar</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm text-white bg-warning">
              <div class="card-header">📊 Estadísticas</div>
              <div class="card-body">
                <h4 class="card-title text-center" id="totalEmpresas">0</h4>
                <p class="card-text text-center mb-0" id="textoEstadistica">Total de empresas</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla de empresas -->
        <div class="card shadow-sm">
          <div class="card-header bg-dark text-white">
            <h5 class="card-title mb-0">🏢 Lista de Empresas</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Tipo Documento</th>
                    <th>Documento</th>
                    <th>Estado</th> <!-- Nueva columna para el estado -->
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaEmpresas">
                  <!-- Contenido cargado dinámicamente -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal Ver Detalle -->
        <div class="modal fade" id="modalDetalle" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title">🏢 Detalle de la Empresa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body" id="contenidoDetalle">
                <!-- Contenido cargado dinámicamente -->
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Editar -->
        <div class="modal fade" id="modalEditar" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">✏️ Editar Empresa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <form id="formEditar">
                <div class="modal-body">
                  <input type="hidden" id="editId">

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Empresa</label>
                        <input type="text" class="form-control" id="editNombre" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Correo</label>
                        <input type="email" class="form-control" id="editCorreo" required>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" class="form-control" id="editTelefono" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Documento</label>
                        <select class="form-select" id="editTipoDoc" required>
                          <?php foreach ($tipos_documento as $tipo): ?>
                            <option value="<?php echo $tipo['id_tipo']; ?>">
                              <?php echo htmlspecialchars($tipo['nombre']); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-bold">Dirección</label>
                    <textarea class="form-control" id="editDireccion" rows="3" required></textarea>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-bold">Estado</label>
                    <select class="form-select" id="editEstado" required>
                      <?php foreach ($estados as $estado): ?>
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
          <small class="text-muted">Gestión de Empresas - Desarrollado con Bootstrap <?php echo date('Y'); ?></small>
        </div>
      </div>
    </div>
  </footer>

  <script src="../js/jquery-3.6.1.min.js"></script>
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <script src="../js/funciones.js"></script>
  <script>
    // Variables globales
    let timeoutBusqueda;
    let busquedaActual = '';

    // Cargar empresas al iniciar la página
    document.addEventListener('DOMContentLoaded', function () {
      cargarEmpresas();
    });

    // Función principal para cargar empresas vía AJAX
    function cargarEmpresas(busqueda = '') {
      fetch(`../CONTROLADOR/ajax_empresa.php?action=listar&busqueda=${encodeURIComponent(busqueda)}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('tablaEmpresas').innerHTML = data.html;
            document.getElementById('totalEmpresas').textContent = data.total;
            document.getElementById('textoEstadistica').textContent =
              busqueda ? 'Resultados encontrados' : 'Total de empresas';
            busquedaActual = busqueda;
          } else {
            mostrarError('Error al cargar empresas');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          mostrarError('Error de conexión');
        });
    }

    // Búsqueda en tiempo real con debounce
    document.getElementById('busquedaInput').addEventListener('input', function (e) {
      const valor = e.target.value.trim();

      clearTimeout(timeoutBusqueda);
      timeoutBusqueda = setTimeout(() => {
        cargarEmpresas(valor);
      }, 300); // Esperar 300ms después de que el usuario deje de escribir
    });

    // Limpiar búsqueda
    function limpiarBusqueda() {
      document.getElementById('busquedaInput').value = '';
      cargarEmpresas();
    }

    // Función para mostrar perfil
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

    // Ver detalle de la empresa
    function verDetalle(id) {
      fetch(`../CONTROLADOR/ajax_empresa.php?action=detalle&id=${id}`)
        .then(response => response.text())
        .then(data => {
          document.getElementById('contenidoDetalle').innerHTML = data;
          new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        })
        .catch(error => {
          mostrarError('No se pudo cargar la información');
        });
    }

    // Editar empresa
    function editarEmpresa(id) {
      fetch(`../CONTROLADOR/ajax_empresa.php?action=obtener&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const emp = data.empresa;
            document.getElementById('editId').value = emp.idEmpresa;
            document.getElementById('editNombre').value = emp.nombre;
            document.getElementById('editCorreo').value = emp.correo;
            document.getElementById('editTelefono').value = emp.telefono;
            document.getElementById('editTipoDoc').value = emp.tipo_documento_id_tipo;
            document.getElementById('editDireccion').value = emp.direccion;
            document.getElementById('editEstado').value = emp.estado_id_estado; // Cargar el estado

            new bootstrap.Modal(document.getElementById('modalEditar')).show();
          } else {
            mostrarError(data.message);
          }
        })
        .catch(error => {
          mostrarError('No se pudo cargar la información');
        });
    }

    // Manejar formulario de edición
    document.getElementById('formEditar').addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData();
      formData.append('action', 'actualizar');
      formData.append('id', document.getElementById('editId').value);
      formData.append('nombre', document.getElementById('editNombre').value);
      formData.append('correo', document.getElementById('editCorreo').value);
      formData.append('telefono', document.getElementById('editTelefono').value);
      formData.append('tipo_documento', document.getElementById('editTipoDoc').value);
      formData.append('direccion', document.getElementById('editDireccion').value);
      formData.append('estado_id_estado', document.getElementById('editEstado').value); // Enviar el estado

      fetch('../CONTROLADOR/ajax_empresa.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
            mostrarExito(data.message);
            cargarEmpresas(busquedaActual); // Recargar con la búsqueda actual
          } else {
            mostrarError(data.message);
          }
        })
        .catch(error => {
          mostrarError('Error al actualizar empresa');
        });
    });

    // Eliminar empresa (cambiar a inactivo)
    function eliminarEmpresa(id) {
      Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción desactivará la empresa, pero su información se mantendrá.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('action', 'eliminar');
          formData.append('id', id);

          fetch('../CONTROLADOR/ajax_empresa.php', { // Corrected path here
            method: 'POST',
            body: formData
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                mostrarExito(data.message);
                cargarEmpresas(busquedaActual); // Recargar con la búsqueda actual
              } else {
                mostrarError(data.message);
              }
            })
            .catch(error => {
              mostrarError('Error al desactivar empresa');
            });
        }
      });
    }

    // Funciones de utilidad para mostrar mensajes
    function mostrarExito(mensaje) {
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: mensaje,
        timer: 2000,
        showConfirmButton: false
      });
    }

    function mostrarError(mensaje) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje
      });
    }

    // Script para el toggle de la barra lateral
    $("#menu-toggle").click(function (e) {
      e.preventDefault();
      $("#wrapper").toggleClass("toggled");
    });

    // Tooltip para elementos que lo necesiten
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  </script>
</body>

</html>