<?php
session_start();
$inn = 500;
if (isset($_SESSION['timeout'])) {
  $_session_life = time() - $_SESSION['timeout'];
  if ($_session_life > $inn) {
    session_destroy();
    header("location:./index.php");
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
include_once '../MODELO/class_estudiante.php';
include_once '../MODELO/class_empresa.php'; // Incluir la clase Empresa para obtener tipos de documento y estados

// Crear instancias de las clases
$estudiante = new Estudiante();
$empresaObj = new Empresa(); // Instancia de Empresa para obtener estados (ya que Estudiante no tiene este método)
$estados = $empresaObj->obtenerEstados(); // Obtener todos los estados

// Solo obtener tipos de documento para el modal de edición
$tipos_documento = $empresaObj->obtenerTiposDocumento(); // Usar la instancia de Empresa
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Estudiantes</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../sw/dist/sweetalert2.min.css">
  <script type="text/javascript" language="Javascript" src="./js/funciones.js"></script>
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
            <a class="nav-link " href="pruebaAdmin.php">Inicio</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="gestion_estudiantes.php">Gestión Estudiantes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_empresas.php">Gestión Empresas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_ofertas.php">Gestión Ofertas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestion_referencias.php">Gestión Referencias</a>
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
  <div class="bg-primary text-white py-4 mb-4">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h1 class="display-5 fw-bold">📚 Gestión de Estudiantes</h1>
          <p class="lead">Administra y gestiona todos los estudiantes registrados en el sistema</p>
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
        <li class="breadcrumb-item active" aria-current="page">Gestión de Estudiantes</li>
      </ol>
    </nav>

    <!-- Card de búsqueda y estadísticas -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">🔍 Búsqueda de Estudiantes</h5>
          </div>
          <div class="card-body">
            <div class="input-group">
              <input type="text" class="form-control" id="busquedaInput"
                placeholder="Buscar por nombre, apellidos, correo, documento o ID...">
              <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">Limpiar</button>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm text-white bg-info">
          <div class="card-header">📊 Estadísticas</div>
          <div class="card-body">
            <h4 class="card-title text-center" id="totalEstudiantes">0</h4>
            <p class="card-text text-center mb-0" id="textoEstadistica">Total de estudiantes</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de estudiantes -->
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0">👥 Lista de Estudiantes</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Documento</th>
                <th>Edad</th>
                <th>Estado</th> <!-- Nueva columna para el estado -->
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaEstudiantes">
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
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">👤 Detalle del Estudiante</h5>
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
            <h5 class="modal-title">✏️ Editar Estudiante</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form id="formEditar">
            <div class="modal-body">
              <input type="hidden" id="editId">

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" class="form-control" id="editNombre" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Apellidos</label>
                    <input type="text" class="form-control" id="editApellidos" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Correo</label>
                    <input type="email" class="form-control" id="editCorreo" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" class="form-control" id="editTelefono" required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="editFechaNac" required>
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

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Gestión Administrativa. Todos los derechos
              reservados.</p>
            <small class="text-muted">Gestión de Estudiantes - Desarrollado con Bootstrap
              <?php echo date('Y'); ?></small>
          </div>
        </div>
      </div>
    </footer>

    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <script src="../sw/dist/sweetalert2.min.js"></script>
    <script src="../js/jquery-3.6.1.min.js"></script>

    <script>
      // Variables globales
      let timeoutBusqueda;
      let busquedaActual = '';

      // Cargar estudiantes al iniciar la página
      document.addEventListener('DOMContentLoaded', function () {
        cargarEstudiantes();
      });

      // Función principal para cargar estudiantes vía AJAX
      function cargarEstudiantes(busqueda = '') {
        fetch(`../CONTROLADOR/ajax_estudiante.php?action=listar&busqueda=${encodeURIComponent(busqueda)}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              document.getElementById('tablaEstudiantes').innerHTML = data.html;
              document.getElementById('totalEstudiantes').textContent = data.total;
              document.getElementById('textoEstadistica').textContent =
                busqueda ? 'Resultados encontrados' : 'Total de estudiantes';
              busquedaActual = busqueda;
            } else {
              mostrarError('Error al cargar estudiantes');
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
          cargarEstudiantes(valor);
        }, 300); // Esperar 300ms después de que el usuario deje de escribir
      });

      // Limpiar búsqueda
      function limpiarBusqueda() {
        document.getElementById('busquedaInput').value = '';
        cargarEstudiantes();
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

      // Ver detalle del estudiante
      function verDetalle(id) {
        fetch(`../CONTROLADOR/ajax_estudiante.php?action=detalle&id=${id}`)
          .then(response => response.text())
          .then(data => {
            document.getElementById('contenidoDetalle').innerHTML = data;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
          })
          .catch(error => {
            mostrarError('No se pudo cargar la información');
          });
      }

      // Editar estudiante
      function editarEstudiante(id) {
        fetch(`../CONTROLADOR/ajax_estudiante.php?action=obtener&id=${id}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const est = data.estudiante;
              document.getElementById('editId').value = est.idEstudiante;
              document.getElementById('editNombre').value = est.nombre;
              document.getElementById('editApellidos').value = est.apellidos;
              document.getElementById('editCorreo').value = est.correo;
              document.getElementById('editTelefono').value = est.telefono;
              document.getElementById('editFechaNac').value = est.fechaNac;
              document.getElementById('editTipoDoc').value = est.tipo_documento_id_tipo;
              document.getElementById('editDireccion').value = est.direccion;
              document.getElementById('editEstado').value = est.estado_id_estado; // Cargar el estado

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
        formData.append('apellidos', document.getElementById('editApellidos').value);
        formData.append('correo', document.getElementById('editCorreo').value);
        formData.append('telefono', document.getElementById('editTelefono').value);
        formData.append('fechaNac', document.getElementById('editFechaNac').value);
        formData.append('tipo_documento', document.getElementById('editTipoDoc').value);
        formData.append('direccion', document.getElementById('editDireccion').value);
        formData.append('estado_id_estado', document.getElementById('editEstado').value); // Enviar el estado

        fetch('../CONTROLADOR/ajax_estudiante.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
              mostrarExito(data.message);
              cargarEstudiantes(busquedaActual); // Recargar con la búsqueda actual
            } else {
              mostrarError(data.message);
            }
          })
          .catch(error => {
            mostrarError('Error al actualizar estudiante');
          });
      });

      // Eliminar estudiante (cambiar a inactivo)
      function eliminarEstudiante(id) {
        Swal.fire({
          title: '¿Está seguro?',
          text: 'Esta acción desactivará al estudiante, pero su información se mantendrá.',
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

            fetch('../CONTROLADOR/ajax_estudiante.php', {
              method: 'POST',
              body: formData
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  mostrarExito(data.message);
                  cargarEstudiantes(busquedaActual); // Recargar con la búsqueda actual
                } else {
                  mostrarError(data.message);
                }
              })
              .catch(error => {
                mostrarError('Error al desactivar estudiante');
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

      // Tooltip para elementos que lo necesiten
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    </script>
</body>

</html>