<?php
header('Content-Type: application/json'); // Indicar que la respuesta será JSON

// Cada una de estas clases contiene internamente la inclusión de 'class_conec.php'.
include_once './class/class_estudiante.php';
include_once './class/class_empresa.php';
include_once './class/class_administrador.php';


// Función de utilidad para validar formatos de correo
function validarFormatoCorreo($correo)
{
  return filter_var($correo, FILTER_VALIDATE_EMAIL);
}

// Función de utilidad para validar solo letras (incluye tildes y ñ)
function validarSoloLetras($texto)
{
  // Permite letras (mayúsculas y minúsculas), tildes, ñ/Ñ y espacios
  return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $texto); // 'u' para soporte UTF-8
}

// Función de utilidad para validar solo números
function validarSoloNumeros($texto)
{
  return preg_match('/^[0-9]+$/', $texto);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rol = $_POST['rol'] ?? '';
  $errors = []; // Array para acumular errores de validación

  // Obtener datos comunes a todos los roles
  $contrasena = $_POST['contrasena'] ?? '';
  $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
  $tipo_documento = $_POST['tipo_documento'] ?? '';
  $n_doc = $_POST['n_doc'] ?? '';
  $correo = $_POST['correo'] ?? '';
  $telefono = $_POST['telefono'] ?? '';
  $direccion = $_POST['direccion'] ?? '';

  // Validaciones de campos comunes a todos los roles
  if (empty($rol)) {
    $errors['rol'] = 'El rol es obligatorio.';
  }
  if (empty($contrasena)) {
    $errors['contrasena'] = 'La contraseña es obligatoria.';
  }
  if (empty($confirmar_contrasena)) {
    $errors['confirmar_contrasena'] = 'Debe confirmar la contraseña.';
  }
  if ($contrasena !== $confirmar_contrasena) {
    $errors['confirmar_contrasena'] = 'Las contraseñas no coinciden.';
  }
  if (strlen($contrasena) < 6) {
    $errors['contrasena'] = 'La contraseña debe tener al menos 6 caracteres.';
  }

  if (empty($tipo_documento) || !validarSoloNumeros($tipo_documento)) {
    $errors['tipo_documento'] = 'Debe seleccionar un tipo de documento válido.';
  }
  if (empty($n_doc)) {
    $errors['n_doc'] = 'El número de documento es obligatorio.';
  } else if (!validarSoloNumeros($n_doc)) {
    $errors['n_doc'] = 'El número de documento debe contener solo números.';
  }

  if (empty($correo)) {
    $errors['correo'] = 'El correo electrónico es obligatorio.';
  } else if (!validarFormatoCorreo($correo)) {
    $errors['correo'] = 'El formato del correo electrónico no es válido.';
  }

  if (empty($telefono)) {
    $errors['telefono'] = 'El teléfono es obligatorio.';
  } else if (!validarSoloNumeros($telefono)) {
    $errors['telefono'] = 'El teléfono debe contener solo números.';
  }

  if (empty($direccion)) {
    $errors['direccion'] = 'La dirección es obligatoria.';
  }


  // Validaciones específicas por rol e instanciación de clases para verificar unicidad
  switch ($rol) {
    case 'estudiante':
      $estudiante = new Estudiante(); // Instanciar la clase para usar sus métodos
      $idEstudiante = $_POST['idEstudiante'] ?? '';
      $nombre = $_POST['nombre'] ?? ''; // CAMBIO: 'nombres' a 'nombre'
      $apellidos = $_POST['apellidos'] ?? '';
      $fechaNac = $_POST['fechaNac'] ?? '';
      $carrera_id_carrera = $_POST['carrera_id_carrera'] ?? '';
      $semestre = $_POST['semestre'] ?? '';
      $promedio_academico = $_POST['promedio_academico'] ?? '';
      $ciudad_id_ciudad = $_POST['ciudad_id_ciudad'] ?? '';

      if (empty($idEstudiante)) {
        $errors['idEstudiante'] = 'El ID de estudiante es obligatorio.';
      } else if ($estudiante->existeEstudiante($idEstudiante)) {
        $errors['idEstudiante'] = 'El ID de estudiante ya está en uso.';
      }
      if (empty($nombre)) {
        $errors['nombre'] = 'El nombre es obligatorio.';
      } // CAMBIO: 'nombres' a 'nombre'
      else if (!validarSoloLetras($nombre)) {
        $errors['nombre'] = 'El nombre solo puede contener letras y espacios.';
      } // CAMBIO: 'nombres' a 'nombre'
      if (empty($apellidos)) {
        $errors['apellidos'] = 'Los apellidos son obligatorios.';
      } else if (!validarSoloLetras($apellidos)) {
        $errors['apellidos'] = 'Los apellidos solo pueden contener letras y espacios.';
      }
      if (empty($fechaNac)) {
        $errors['fechaNac'] = 'La fecha de nacimiento es obligatoria.';
      } else if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fechaNac)) {
        $errors['fechaNac'] = 'Formato de fecha de nacimiento inválido (YYYY-MM-DD).';
      }

      if (empty($carrera_id_carrera) || !validarSoloNumeros($carrera_id_carrera)) {
        $errors['carrera_id_carrera'] = 'Debe seleccionar una carrera válida.';
      }
      if (empty($semestre) || !validarSoloNumeros($semestre) || (int) $semestre <= 0) {
        $errors['semestre'] = 'El semestre debe ser un número positivo.';
      }
      if (empty($promedio_academico)) {
        $errors['promedio_academico'] = 'El promedio académico es obligatorio.';
      } else if (!preg_match("/^\d+(\.\d{1,2})?$/", $promedio_academico)) {
        $errors['promedio_academico'] = 'El promedio académico debe ser numérico (ej. 3.5).';
      }

      if (empty($ciudad_id_ciudad) || !validarSoloNumeros($ciudad_id_ciudad)) {
        $errors['ciudad_id_ciudad'] = 'Debe seleccionar una ciudad válida.';
      }

      // Validar unicidad de correo y número de documento utilizando los métodos existentes
      if ($estudiante->existeCorreo($correo)) {
        $errors['correo'] = 'El correo electrónico ya está registrado en una cuenta de estudiante.';
      }
      if ($estudiante->existeNdoc($n_doc)) {
        $errors['n_doc'] = 'El número de documento ya está registrado en una cuenta de estudiante.';
      }

      break;

    case 'empresa':
      $empresa = new Empresa(); // Instanciar la clase para usar sus métodos
      $idEmpresa = $_POST['idEmpresa'] ?? '';
      $nombre_empresa = $_POST['nombre_empresa'] ?? '';
      $sector_empresarial = $_POST['sector_empresarial'] ?? '';
      $descripcion = $_POST['descripcion'] ?? '';
      $ciudad_id_ciudad = $_POST['ciudad_id_ciudad'] ?? '';

      if (empty($idEmpresa)) {
        $errors['idEmpresa'] = 'El ID de empresa es obligatorio.';
      } else if ($empresa->existeEmpresa($idEmpresa)) {
        $errors['idEmpresa'] = 'El ID de empresa ya está en uso.';
      }
      if (empty($nombre_empresa)) {
        $errors['nombre_empresa'] = 'El nombre de la empresa es obligatorio.';
      } else if (!validarSoloLetras($nombre_empresa)) {
        $errors['nombre_empresa'] = 'El nombre de la empresa solo puede contener letras y espacios.';
      }

      if (empty($sector_empresarial) || !validarSoloNumeros($sector_empresarial)) {
        $errors['sector_empresarial'] = 'Debe seleccionar un sector empresarial válido.';
      }
      if (empty($descripcion)) {
        $errors['descripcion'] = 'La descripción de la empresa es obligatoria.';
      }

      if (empty($ciudad_id_ciudad) || !validarSoloNumeros($ciudad_id_ciudad)) {
        $errors['ciudad_id_ciudad'] = 'Debe seleccionar una ciudad válida.';
      }

      // Validar unicidad de correo y número de documento utilizando los métodos existentes
      if ($empresa->existeCorreo($correo)) {
        $errors['correo'] = 'El correo electrónico ya está registrado en una cuenta de empresa.';
      }
      if ($empresa->existeNdoc($n_doc)) {
        $errors['n_doc'] = 'El número de documento (NIT/RUC) ya está registrado en una cuenta de empresa.';
      }

      break;

    case 'administrador':
      $administrador = new Administrador(); // Instanciar la clase para usar sus métodos
      $idAdministrador = $_POST['idAdministrador'] ?? '';
      $nombres = $_POST['nombres'] ?? '';
      $apellidos = $_POST['apellidos'] ?? '';

      if (empty($idAdministrador)) {
        $errors['idAdministrador'] = 'El ID de administrador es obligatorio.';
      } else if ($administrador->existeAdministrador($idAdministrador)) {
        $errors['idAdministrador'] = 'El ID de administrador ya está en uso.';
      }
      if (empty($nombres)) {
        $errors['nombres'] = 'Los nombres son obligatorios.';
      } else if (!validarSoloLetras($nombres)) {
        $errors['nombres'] = 'Los nombres solo pueden contener letras y espacios.';
      }
      if (empty($apellidos)) {
        $errors['apellidos'] = 'Los apellidos son obligatorios.';
      } else if (!validarSoloLetras($apellidos)) {
        $errors['apellidos'] = 'Los apellidos solo pueden contener letras y espacios.';
      }

      // Ahora que los métodos existen en Administrador, se pueden llamar directamente:
      if ($administrador->existeCorreo($correo)) {
        $errors['correo'] = 'El correo electrónico ya está registrado en una cuenta de administrador.';
      }
      if ($administrador->existeNdoc($n_doc)) {
        $errors['n_doc'] = 'El número de documento ya está registrado en una cuenta de administrador.';
      }

      break;

    default:
      $errors['rol'] = 'Rol no válido.';
      break;
  }

  // Si hay errores, devolverlos
  if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
  }

  // Si no hay errores de validación, proceder con el registro
  try {
    $resultado = ['success' => false, 'message' => 'Error desconocido en el registro.'];

    // Prepara los datos que se pasarán a la función `registrar`
    $datos_registro = $_POST;

    switch ($rol) {
      case 'estudiante':
        // La instancia de $estudiante ya está creada arriba
        $resultado = $estudiante->registrar($datos_registro);
        break;
      case 'empresa':
        // La instancia de $empresa ya está creada arriba
        $resultado = $empresa->registrar($datos_registro);
        break;
      case 'administrador':
        // La instancia de $administrador ya está creada arriba
        $resultado = $administrador->registrar($datos_registro);
        break;
    }

    if ($resultado['success']) {
      echo json_encode(['success' => true, 'message' => $resultado['message']]);
    } else {
      // Captura el mensaje de error de la función registrar si falla la inserción por otras razones
      echo json_encode(['success' => false, 'message' => $resultado['message'] ?? 'Ocurrió un error al registrar.']);
    }

  } catch (Exception $e) {
    error_log("ERROR CRÍTICO en ajax_registro.php (procesamiento final): " . $e->getMessage() . " en línea " . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor al procesar el registro.']);
  }

} else {
  // Si no es una solicitud POST, devolver error de método no permitido
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}