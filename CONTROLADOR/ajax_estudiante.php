<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'No autorizado']);
  exit();
}

// Incluir archivos necesarios
include_once '../MODELO/class_estudiante.php';
include_once '../MODELO/class_empresa.php'; // Incluir la clase Empresa para obtener tipos de documento si fuera necesario

// Crear instancias de las clases
$estudiante = new Estudiante();
$empresaObj = new Empresa(); // Se instancia para obtener tipos de documento y estados

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';

switch ($action) {
  case 'detalle':
    if (empty($id)) {
      echo '<div class="alert alert-danger">ID no proporcionado</div>';
      exit();
    }

    $est = $estudiante->obtenerPorId($id);
    if (!$est) {
      echo '<div class="alert alert-danger">Estudiante no encontrado</div>';
      exit();
    }
    ?>
    <div class="row">
      <div class="col-md-6">
        <table class="table table-borderless">
          <tr>
            <th>ID Estudiante:</th>
            <td><?php echo htmlspecialchars($est['idEstudiante']); ?></td>
          </tr>
          <tr>
            <th>Nombre Completo:</th>
            <td><?php echo htmlspecialchars($est['nombre'] . ' ' . $est['apellidos']); ?></td>
          </tr>
          <tr>
            <th>Correo:</th>
            <td><?php echo htmlspecialchars($est['correo']); ?></td>
          </tr>
          <tr>
            <th>Teléfono:</th>
            <td><?php echo htmlspecialchars($est['telefono']); ?></td>
          </tr>
          <tr>
            <th>Fecha de Nacimiento:</th>
            <td><?php echo date('d/m/Y', strtotime($est['fechaNac'])); ?></td>
          </tr>
        </table>
      </div>
      <div class="col-md-6">
        <table class="table table-borderless">
          <tr>
            <th>Número de Documento:</th>
            <td><?php echo htmlspecialchars($est['n_doc']); ?></td>
          </tr>
          <tr>
            <th>Tipo de Documento:</th>
            <td><?php echo htmlspecialchars($est['tipo_documento_nombre']); ?></td>
          </tr>
          <tr>
            <th>Dirección:</th>
            <td><?php echo htmlspecialchars($est['direccion']); ?></td>
          </tr>
          <tr>
            <th>Edad:</th>
            <td>
              <?php
              if (!empty($est['fechaNac'])) {
                $fecha_nac = new DateTime($est['fechaNac']);
                $hoy = new DateTime();
                $edad = $hoy->diff($fecha_nac)->y;
                echo $edad . ' años';
              } else {
                echo '<span class="text-muted">N/A</span>';
              }
              ?>
            </td>
          </tr>
          <tr>
            <th>Estado:</th>
            <td><?php echo htmlspecialchars($est['estado_nombre'] ?? 'N/A'); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <?php
    break;

  case 'obtener':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
      exit();
    }

    $est = $estudiante->obtenerPorId($id);
    if (!$est) {
      echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
      exit();
    }

    echo json_encode([
      'success' => true,
      'estudiante' => $est
    ]);
    break;

  case 'eliminar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
      exit();
    }

    $resultado = $estudiante->eliminar($id);
    echo json_encode($resultado);
    break;

  case 'actualizar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
      exit();
    }

    $datos = [
      'nombre' => $_POST['nombre'] ?? '',
      'apellidos' => $_POST['apellidos'] ?? '',
      'correo' => $_POST['correo'] ?? '',
      'telefono' => $_POST['telefono'] ?? '',
      'direccion' => $_POST['direccion'] ?? '',
      'fechaNac' => $_POST['fechaNac'] ?? '',
      'tipo_documento_id_tipo' => $_POST['tipo_documento'] ?? '',
      'estado_id_estado' => $_POST['estado_id_estado'] ?? '' // Recibir el estado
    ];

    $resultado = $estudiante->actualizar($id, $datos, []);
    echo json_encode($resultado);
    break;

  case 'listar':
    $busqueda = $_GET['busqueda'] ?? '';
    // Se restauran las llamadas a los métodos para obtener y contar TODOS los estudiantes (activos e inactivos)
    $estudiantes = $estudiante->obtenerTodos($busqueda);
    $total_estudiantes = $estudiante->contarEstudiantes($busqueda);

    // Generar HTML de la tabla
    ob_start();
    ?>
    <?php if (empty($estudiantes)): ?>
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="text-muted">
            <div class="display-1">📚</div>
            <h5><?php echo empty($busqueda) ? 'No hay estudiantes registrados' : 'No se encontraron resultados'; ?></h5>
            <p class="mb-0">
              <?php echo empty($busqueda) ? 'Aún no se han registrado estudiantes en el sistema' : 'Intenta con otros términos de búsqueda'; ?>
            </p>
          </div>
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($estudiantes as $est): ?>
        <tr>
          <td><span class="badge bg-primary"><?php echo htmlspecialchars($est['idEstudiante']); ?></span></td>
          <td><?php echo htmlspecialchars($est['nombre'] . ' ' . $est['apellidos']); ?></td>
          <td><?php echo htmlspecialchars($est['correo']); ?></td>
          <td><?php echo htmlspecialchars($est['telefono']); ?></td>
          <td><?php echo htmlspecialchars($est['n_doc']); ?></td>
          <td>
            <?php
            if (!empty($est['fechaNac'])) {
              $fecha_nac = new DateTime($est['fechaNac']);
              $hoy = new DateTime();
              $edad = $hoy->diff($fecha_nac)->y;
              echo $edad . ' años';
            } else {
              echo '<span class="text-muted">N/A</span>';
            }
            ?>
          </td>
          <td>
            <?php
            $estado_badge_class = '';
            if ($est['estado_id_estado'] == 1) {
              $estado_badge_class = 'bg-success'; // Activo
            } elseif ($est['estado_id_estado'] == 2) {
              $estado_badge_class = 'bg-danger'; // Inactivo
            } else {
              $estado_badge_class = 'bg-secondary'; // Otro estado
            }
            ?>
            <span
              class="badge <?php echo $estado_badge_class; ?>"><?php echo htmlspecialchars($est['estado_nombre'] ?? 'Desconocido'); ?></span>
          </td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-outline-primary" onclick="verDetalle('<?php echo $est['idEstudiante']; ?>')"
                title="Ver detalles">
                👁️
              </button>
              <button class="btn btn-sm btn-outline-warning" onclick="editarEstudiante('<?php echo $est['idEstudiante']; ?>')"
                title="Editar">
                ✏️
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="eliminarEstudiante('<?php echo $est['idEstudiante']; ?>')"
                title="Desactivar">
                🗑️
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php
    $html = ob_get_clean();

    echo json_encode([
      'success' => true,
      'html' => $html,
      'total' => $total_estudiantes // Enviar el total correcto
    ]);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    break;
}
?>