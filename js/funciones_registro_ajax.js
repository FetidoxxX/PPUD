// Función para limpiar el formulario
function limpiarRegistro() {
  document.getElementById('formRegistro').reset();
  // Limpiar mensajes de feedback de contraseña
  var feedbackElement = document.getElementById('contrasena-feedback');
  if (feedbackElement) {
    feedbackElement.textContent = '';
    feedbackElement.className = 'small';
  }
}

function mostrarError(titulo, mensaje) {
  Swal.fire({
    icon: 'error',
    title: titulo,
    html: mensaje,
    confirmButtonColor: '#dc3545',
  });
}

// Función para mostrar mensajes de éxito con SweetAlert2
function mostrarExito(mensaje) {
  Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: mensaje,
    confirmButtonColor: '#28a745',
  });
}

// Función para validar que un texto contenga solo letras y espacios (incluye tildes y ñ)
function validarSoloLetras(texto) {
  var regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
  return regex.test(texto);
}

// Función para validar que un texto contenga solo números
function validarSoloNumeros(texto) {
  var regex = /^[0-9]+$/;
  return regex.test(texto);
}

// Función para validar formato de correo electrónico
function validarFormatoCorreo(correo) {
  var re = /\S+@\S+\.\S+/;
  return re.test(correo);
}

$(document).ready(function () {
  // Manejador para el botón de limpiar
  $('#btnLimpiar').on('click', function () {
    limpiarRegistro();
  });

  // Validación de contraseña en tiempo real
  $('#contrasena').on('keyup', function () {
    var contrasena = $(this).val();
    var mensaje = '';
    var color = '';
    if (contrasena.length === 0) {
      mensaje = '';
    } else if (contrasena.length < 6) {
      mensaje = 'Muy corta (mínimo 6 caracteres)';
      color = 'text-danger';
    } else if (contrasena.length < 8) {
      mensaje = 'Débil (recomendado: 8+ caracteres)';
      color = 'text-warning';
    } else {
      mensaje = 'Fuerte';
      color = 'text-success';
    }
    $('#contrasena-feedback')
      .text(mensaje)
      .removeClass()
      .addClass('small ' + color);
  });

  // Manejador del submit del formulario
  $('#formRegistro').on('submit', function (e) {
    e.preventDefault(); // Evitar el envío normal del formulario

    var form = this;
    var rol = $(form).find('input[name="rol"]').val();
    var formData = new FormData(form);

    // Variable para almacenar el primer error encontrado
    var firstErrorField = '';
    var firstErrorMessage = '';

    // Función auxiliar para registrar el primer error y detener la validación
    function recordError(field, message) {
      if (!firstErrorField) {
        // Solo registra el primer error
        firstErrorField = field;
        firstErrorMessage = message;
      }
    }

    // Se detendrán en el primer error encontrado.

    // Validar Contraseñas
    var contrasena = $('#contrasena').val();
    var confirmar_contrasena = $('#confirmar_contrasena').val();
    if (contrasena.trim() === '') {
      recordError(
        'contrasena',
        'La contraseña es obligatoria para el registro.'
      );
    } else if (confirmar_contrasena.trim() === '') {
      recordError(
        'confirmar_contrasena',
        'La confirmación de contraseña es obligatoria para el registro.'
      );
    } else if (contrasena !== confirmar_contrasena) {
      recordError('confirmar_contrasena', 'Las contraseñas no coinciden.');
    } else if (contrasena.length < 6) {
      recordError(
        'contrasena',
        'La contraseña debe tener al menos 6 caracteres.'
      );
    }

    // Si ya hay un error de contraseña, detenemos aquí para mostrarlo
    if (firstErrorField) {
      mostrarError('Error de Validación', firstErrorMessage);
      return;
    }

    // Validar campos comunes siempre requeridos
    if ($('#tipo_documento').val() === '') {
      recordError(
        'tipo_documento',
        'El Tipo de Documento es obligatorio para el registro.'
      );
    }
    if (firstErrorField) {
      mostrarError('Error de Validación', firstErrorMessage);
      return;
    }

    if ($('#n_doc').val().trim() === '') {
      recordError(
        'n_doc',
        'El Número de Documento es obligatorio para el registro.'
      );
    } else if (!validarSoloNumeros($('#n_doc').val().trim())) {
      recordError(
        'n_doc',
        'El Número de Documento debe contener solo números.'
      );
    }
    if (firstErrorField) {
      mostrarError('Error de Validación', firstErrorMessage);
      return;
    }

    if ($('#correo').val().trim() === '') {
      recordError(
        'correo',
        'El Correo Electrónico es obligatorio para el registro.'
      );
    } else if (!validarFormatoCorreo($('#correo').val().trim())) {
      recordError('correo', 'El formato del Correo Electrónico no es válido.');
    }
    if (firstErrorField) {
      mostrarError('Error de Validación', firstErrorMessage);
      return;
    }

    if ($('#telefono').val().trim() === '') {
      recordError('telefono', 'El Teléfono es obligatorio para el registro.');
    } else if (!validarSoloNumeros($('#telefono').val().trim())) {
      recordError('telefono', 'El Teléfono debe contener solo números.');
    }
    if (firstErrorField) {
      mostrarError('Error de Validación', firstErrorMessage);
      return;
    }

    if ($('#direccion').val().trim() === '') {
      recordError('direccion', 'La Dirección es obligatoria para el registro.');
    }
    if (firstErrorField) {
      mostrarError('Error de Validación', firstErrorMessage);
      return;
    }

    // Validaciones específicas del lado del cliente según el rol (se detienen en el primer error)
    if (rol === 'estudiante') {
      // CAMBIO: 'idEstudiante' se mantiene como está
      if ($('#idEstudiante').val().trim() === '') {
        recordError(
          'idEstudiante',
          'El ID de Estudiante es obligatorio para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      // CAMBIO: 'nombres' a 'nombre'
      if ($('#nombre').val().trim() === '') {
        recordError('nombre', 'El Nombre es obligatorio para el registro.');
      } else if (!validarSoloLetras($('#nombre').val().trim())) {
        recordError(
          'nombre',
          'El Nombre solo puede contener letras y espacios.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#apellidos').val().trim() === '') {
        recordError(
          'apellidos',
          'Los Apellidos son obligatorios para el registro.'
        );
      } else if (!validarSoloLetras($('#apellidos').val().trim())) {
        recordError(
          'apellidos',
          'Los Apellidos solo pueden contener letras y espacios.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#fechaNac').val().trim() === '') {
        recordError(
          'fechaNac',
          'La Fecha de Nacimiento es obligatoria para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#carrera_id_carrera').val() === '') {
        recordError(
          'carrera_id_carrera',
          'La Carrera es obligatoria para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if (
        $('#semestre').val().trim() === '' ||
        !validarSoloNumeros($('#semestre').val()) ||
        parseInt($('#semestre').val()) <= 0
      ) {
        recordError('semestre', 'El Semestre debe ser un número positivo.');
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#promedio_academico').val().trim() === '') {
        recordError(
          'promedio_academico',
          'El Promedio Académico es obligatorio para el registro.'
        );
      } else if (
        !/^\d+(\.\d{1,2})?$/.test($('#promedio_academico').val().trim())
      ) {
        recordError(
          'promedio_academico',
          'El Promedio Académico debe ser numérico (ej. 3.5).'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#ciudad_id_ciudad').val() === '') {
        recordError(
          'ciudad_id_ciudad',
          'La Ciudad es obligatoria para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }
    } else if (rol === 'empresa') {
      if ($('#idEmpresa').val().trim() === '') {
        recordError(
          'idEmpresa',
          'El ID de Empresa es obligatorio para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#nombre_empresa').val().trim() === '') {
        recordError(
          'nombre_empresa',
          'El Nombre de la Empresa es obligatorio para el registro.'
        );
      } else if (!validarSoloLetras($('#nombre_empresa').val().trim())) {
        recordError(
          'nombre_empresa',
          'El Nombre de la Empresa solo puede contener letras y espacios.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#sector_empresarial').val() === '') {
        recordError(
          'sector_empresarial',
          'El Sector Empresarial es obligatorio para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#descripcion').val().trim() === '') {
        recordError(
          'descripcion',
          'La Descripción de la Empresa es obligatoria para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#ciudad_id_ciudad').val() === '') {
        recordError(
          'ciudad_id_ciudad',
          'La Ciudad es obligatoria para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }
    } else if (rol === 'administrador') {
      if ($('#idAdministrador').val().trim() === '') {
        recordError(
          'idAdministrador',
          'El ID de Administrador es obligatorio para el registro.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#nombres').val().trim() === '') {
        recordError(
          'nombres',
          'Los Nombres son obligatorios para el registro.'
        );
      } else if (!validarSoloLetras($('#nombres').val().trim())) {
        recordError(
          'nombres',
          'Los Nombres solo pueden contener letras y espacios.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }

      if ($('#apellidos').val().trim() === '') {
        recordError(
          'apellidos',
          'Los Apellidos son obligatorios para el registro.'
        );
      } else if (!validarSoloLetras($('#apellidos').val().trim())) {
        recordError(
          'apellidos',
          'Los Apellidos solo pueden contener letras y espacios.'
        );
      }
      if (firstErrorField) {
        mostrarError('Error de Validación', firstErrorMessage);
        return;
      }
    }

    // Si firstErrorField sigue vacío, significa que todas las validaciones del cliente pasaron.
    // Procedemos con el envío AJAX.
    Swal.fire({
      title: '¿Confirmar registro?',
      text: '¿Está seguro de que desea registrar este usuario?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, registrar',
      cancelButtonText: 'Cancelar',
      showLoaderOnConfirm: true,
      preConfirm: () => {
        return $.ajax({
          type: 'POST',
          url: '../CONTROLADOR/ajax_registro.php',
          data: formData,
          processData: false, // Necesario para FormData
          contentType: false, // Necesario para FormData
          dataType: 'json',
        })
          .done(function (response) {
            if (response.success) {
              mostrarExito(response.message);
              // Redirigir al login después de 2 segundos o cuando el usuario presione OK
              setTimeout(() => {
                window.location.href = 'login.php?rol=' + rol;
              }, 2000);
            } else {
              // Mostrar errores del servidor
              if (response.errors) {
                let serverErrorMessages = '';
                for (let field in response.errors) {
                  serverErrorMessages += `${response.errors[field]}<br>`;
                }
                mostrarError('Errores de Registro', serverErrorMessages);
              } else {
                mostrarError(
                  'Error de Registro',
                  response.message ||
                    'Ocurrió un error desconocido al registrar el usuario.'
                );
              }
            }
          })
          .fail(function (xhr, status, error) {
            console.error('Error AJAX:', xhr.responseText); // Log de error AJAX
            mostrarError(
              'Error de Conexión',
              'No se pudo conectar con el servidor. Intente de nuevo más tarde.'
            );
          });
      },
      allowOutsideClick: () => !Swal.isLoading(),
    });
  });
});
