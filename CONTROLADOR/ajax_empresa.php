<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'No autorizado']);
  exit();
}

// Incluir archivos necesarios
include_once '../MODELO/class_empresa.php';

// Crear conexión y instancia de empresa
$empresa = new Empresa();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';

switch ($action) {
  case 'detalle':
    if (empty($id)) {
      echo '<div class="alert alert-danger">ID no proporcionado</div>';
      exit();
    }

    $emp = $empresa->obtenerPorId($id);
    if (!$emp) {
      echo '<div class="alert alert-danger">Empresa no encontrada</div>';
      exit();
    }
    ?>
    <div class="row">
      <div class="col-md-6">
        <table class="table table-borderless">
          <tr>
            <th>ID Empresa:</th>
            <td><?php echo htmlspecialchars($emp['idEmpresa']); ?></td>
          </tr>
          <tr>
            <th>Nombre:</th>
            <td><?php echo htmlspecialchars($emp['nombre']); ?></td>
          </tr>
          <tr>
            <th>Correo:</th>
            <td><?php echo htmlspecialchars($emp['correo']); ?></td>
          </tr>
          <tr>
            <th>Teléfono:</th>
            <td><?php echo htmlspecialchars($emp['telefono']); ?></td>
          </tr>
        </table>
      </div>
      <div class="col-md-6">
        <table class="table table-borderless">
          <tr>
            <th>Tipo de Documento:</th>
            <td><?php echo htmlspecialchars($emp['tipo_documento_nombre'] ?? 'No especificado'); ?></td>
          </tr>
          <tr>
            <th>Dirección:</th>
            <td><?php echo htmlspecialchars($emp['direccion']); ?></td>
          </tr>
          <tr>
            <th>Fecha de Registro:</th>
            <td>
              <?php echo isset($emp['fecha_registro']) ? date('d/m/Y', strtotime($emp['fecha_registro'])) : 'No disponible'; ?>
            </td>
          </tr>
          <tr>
            <th>Estado:</th>
            <td><?php echo htmlspecialchars($emp['estado_nombre'] ?? 'N/A'); ?></td>
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

    $emp = $empresa->obtenerPorId($id);
    if (!$emp) {
      echo json_encode(['success' => false, 'message' => 'Empresa no encontrada']);
      exit();
    }

    echo json_encode([
      'success' => true,
      'empresa' => $emp
    ]);
    break;

  case 'listar':
    $busqueda = $_GET['busqueda'] ?? '';
    $empresas = $empresa->obtenerTodos($busqueda); // Ahora obtiene todos (activos e inactivos)
    $total_empresas = $empresa->contarTotal($busqueda); // Contar todas las empresas

    // Generar HTML de la tabla
    ob_start();
    ?>
    <?php if (empty($empresas)): ?>
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="text-muted">
            <div class="display-1">🏢</div>
            <h5><?php echo empty($busqueda) ? 'No hay empresas registradas' : 'No se encontraron resultados'; ?></h5>
            <p class="mb-0">
              <?php echo empty($busqueda) ? 'Aún no se han registrado empresas en el sistema' : 'Intenta con otros términos de búsqueda'; ?>
            </p>
          </div>
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($empresas as $emp): ?>
        <tr>
          <td><span class="badge bg-success"><?php echo htmlspecialchars($emp['idEmpresa']); ?></span></td>
          <td><?php echo htmlspecialchars($emp['nombre']); ?></td>
          <td><?php echo htmlspecialchars($emp['correo']); ?></td>
          <td><?php echo htmlspecialchars($emp['telefono']); ?></td>
          <td><?php echo htmlspecialchars($emp['tipo_documento_nombre'] ?? 'N/A'); ?></td>
          <td><?php echo htmlspecialchars($emp['n_doc']); ?></td>
          <td>
            <?php
            $estado_badge_class = '';
            if ($emp['estado_id_estado'] == 1) {
              $estado_badge_class = 'bg-success'; // Activo
            } elseif ($emp['estado_id_estado'] == 2) {
              $estado_badge_class = 'bg-danger'; // Inactivo
            } else {
              $estado_badge_class = 'bg-secondary'; // Otro estado
            }
            ?>
            <span
              class="badge <?php echo $estado_badge_class; ?>"><?php echo htmlspecialchars($emp['estado_nombre'] ?? 'Desconocido'); ?></span>
          </td>
          <td>
            <div class="btn-group" role="group">
              <button class="btn btn-sm btn-outline-primary" onclick="verDetalle('<?php echo $emp['idEmpresa']; ?>')"
                title="Ver detalles">
                👁️
              </button>
              <button class="btn btn-sm btn-outline-warning" onclick="editarEmpresa('<?php echo $emp['idEmpresa']; ?>')"
                title="Editar">
                ✏️
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="eliminarEmpresa('<?php echo $emp['idEmpresa']; ?>')"
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
      'total' => $total_empresas // Enviar el total correcto
    ]);
    break;

  case 'actualizar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
      exit();
    }

    $datos = [
      'nombre' => $_POST['nombre'] ?? '',
      'correo' => $_POST['correo'] ?? '',
      'telefono' => $_POST['telefono'] ?? '',
      'direccion' => $_POST['direccion'] ?? '',
      'tipo_documento_id_tipo' => $_POST['tipo_documento'] ?? '',
      'estado_id_estado' => $_POST['estado_id_estado'] ?? '' // Recibir el estado
    ];

    $resultado = $empresa->actualizar($id, $datos);
    echo json_encode($resultado);
    break;

  case 'eliminar':
    if (empty($id)) {
      echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
      exit();
    }

    $resultado = $empresa->eliminar($id);
    echo json_encode($resultado);
    break;

  case 'buscar':
    $termino = $_GET['termino'] ?? '';
    $empresas = $empresa->obtenerTodos($termino); // Ahora obtiene todos (activos e inactivos)

    echo json_encode([
      'success' => true,
      'empresas' => $empresas,
      'total' => count($empresas)
    ]);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    break;
}
?>