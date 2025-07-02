let globalTiposReferencia = [];
let globalEstadosReferencia = []; // Si se necesita para la creación/visualización

// Variable global para almacenar el elemento que activó el modal de referencia, para devolver el foco
let lastReferenceModalTrigger = null;

/**
 * Inicializa las variables globales para los selectores del modal de referencias.
 * @param {Array} tiposReferencia - Array de objetos con los tipos de referencia.
 * @param {Array} estadosReferencia - Array de objetos con los estados de referencia.
 */
function initializeReferenciasE(tiposReferencia, estadosReferencia) {
  globalTiposReferencia = tiposReferencia;
  globalEstadosReferencia = estadosReferencia;

  // Manejar el envío del formulario de referencia
  $('#referenciaForm').submit(function (event) {
    event.preventDefault();
    saveReference();
  });

  // Escuchar el evento de cierre del modal para limpiar el formulario
  $('#crearReferenciaModal').on('hidden.bs.modal', function () {
    resetReferenceForm();
    // Restaurar el foco al elemento que abrió este modal, si existe
    if (lastReferenceModalTrigger) {
      lastReferenceModalTrigger.focus();
      lastReferenceModalTrigger = null; // Limpiar después de usar
    }
  });
}

/**
 * Abre el modal para crear una nueva referencia.
 * @param {string} studentId - El ID del estudiante al que se le creará la referencia.
 * @param {string} studentName - El nombre completo del estudiante para mostrar en el título del modal.
 * @param {HTMLElement} triggeringElement - El elemento que activó este modal.
 */
function openCreateReferenceModal(studentId, studentName, triggeringElement) {
  // Guardar el elemento que activó este modal
  lastReferenceModalTrigger = triggeringElement;

  // Cerrar el modal del perfil del estudiante si está abierto para evitar superposición
  const perfilEstudianteModal = bootstrap.Modal.getInstance(
    document.getElementById('perfilEstudianteModal')
  );
  if (perfilEstudianteModal) {
    perfilEstudianteModal.hide();
    // Añadir un listener one-time para asegurar que el foco se maneje después de que el modal se oculte completamente
    $('#perfilEstudianteModal').one('hidden.bs.modal', function () {
      // Llamar a showCreateReferenceModal solo después de que perfilEstudianteModal se haya ocultado.
      showCreateReferenceModal(studentId, studentName);
    });
  } else {
    showCreateReferenceModal(studentId, studentName);
  }
}

/**
 * Función auxiliar para mostrar el modal de creación de referencia.
 * @param {string} studentId - El ID del estudiante.
 * @param {string} studentName - El nombre del estudiante.
 */
function showCreateReferenceModal(studentId, studentName) {
  // 1. Resetear el formulario primero para limpiar cualquier estado previo.
  resetReferenceForm();

  // 2. Luego establecer el ID del estudiante en el campo oculto del formulario
  $('#referenciaEstudianteId').val(studentId);
  // 3. Mostrar el nombre del estudiante en el título del modal
  $('#referenciaEstudianteNombre').text(studentName);

  // Mostrar el modal de creación de referencia
  const crearReferenciaModalElement = document.getElementById(
    'crearReferenciaModal'
  );
  new bootstrap.Modal(crearReferenciaModalElement).show();

  // Al mostrar el modal, asegúrate de que el foco esté dentro de él.
  // Por ejemplo, en el botón de cerrar.
  $('#crearReferenciaModal .btn-close').focus();
}

/**
 * Resetea el formulario de creación de referencia.
 */
function resetReferenceForm() {
  $('#referenciaForm')[0].reset();
  // No limpiar #referenciaEstudianteId aquí.
  // Su valor será establecido explícitamente por showCreateReferenceModal.
  $('#referenciaEstudianteNombre').text('');
  // El tipo de referencia ya no es un select, su valor es fijo en el HTML.
  // $('#referenciaTipo').val('');
}

/**
 * Guarda la referencia enviando los datos al servidor.
 */
function saveReference() {
  const estudianteId = $('#referenciaEstudianteId').val();
  const comentario = $('#referenciaComentario').val().trim();
  const puntuacion = $('#referenciaPuntuacion').val().trim();
  // Ahora el tipo de referencia se obtiene directamente del campo oculto, su valor es fijo (2)
  const tipoReferencia = $('#referenciaTipo').val(); // Debería ser '2'

  // --- DEBUGGING CONSOLE LOGS ---
  console.log('Validando referencia...');
  console.log(
    'Estudiante ID:',
    estudianteId,
    ' (Length:',
    estudianteId.length,
    ')'
  ); // Added length for ID
  console.log('Comentario:', comentario, ' (Length:', comentario.length, ')');
  console.log('Tipo de Referencia:', tipoReferencia); // Debería ser 2
  console.log('Puntuación:', puntuacion);
  // --- END DEBUGGING ---

  if (!comentario || !estudianteId) {
    // Ya no se valida tipoReferencia aquí
    Swal.fire(
      'Error de Validación',
      'El comentario y el ID del estudiante son obligatorios.',
      'error'
    );
    return;
  }

  const formData = new FormData();
  formData.append('action', 'crear_referencia');
  formData.append('estudiante_idEstudiante', estudianteId);
  formData.append('comentario', comentario);
  formData.append('tipo_referencia_id_tipo_referencia', tipoReferencia); // Esto enviará el valor '2'
  if (puntuacion) {
    formData.append('puntuacion', puntuacion);
  }

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php', // Nuevo archivo AJAX para referencias
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        Swal.fire('Éxito', response.message, 'success');
        $('#crearReferenciaModal').modal('hide');
        // Recargar las referencias en el perfil del estudiante
        loadAndDisplayReferences(estudianteId);
        // Volver a abrir el modal de perfil de estudiante si el usuario lo desea
        // Esto puede ser manejado por el usuario o decidir si reabrir automáticamente
        // Actualmente, el flujo es que al cerrar el modal de referencia, el usuario vuelve a la lista de interesados o donde estaba antes.
        // Si queremos reabrir el perfil automáticamente:
        const studentName = $('#referenciaEstudianteNombre').text(); // Obtener el nombre para reabrir
        // Asegurarse de que viewStudentProfileForCompany reciba el triggeringElement para un correcto manejo de foco
        viewStudentProfileForCompany(estudianteId, lastReferenceModalTrigger);
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al guardar referencia:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo guardar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Carga y muestra las referencias existentes para un estudiante dado.
 * @param {string} studentId - El ID del estudiante.
 */
function loadAndDisplayReferences(studentId) {
  const referenciasListContainer = $('#referenciasListContainer');
  referenciasListContainer.html(
    '<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando referencias...</div>'
  ); // Mensaje de carga

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php', // Nuevo archivo AJAX para referencias
    type: 'GET',
    data: {
      action: 'obtener_referencias_estudiante',
      idEstudiante: studentId,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.html) {
        referenciasListContainer.html(response.html);
      } else {
        referenciasListContainer.html(
          '<p class="text-muted text-center py-3">No hay referencias registradas para este estudiante.</p>'
        );
        // Opcional: Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar referencias:', error);
      referenciasListContainer.html(
        '<p class="text-danger text-center py-3">Error de conexión al cargar referencias.</p>'
      );
      Swal.fire(
        'Error de conexión',
        'No se pudieron cargar las referencias. Intente de nuevo.',
        'error'
      );
    },
  });
}

// Función auxiliar para renderizar opciones de select (ya no se usa para tipo de referencia)
function renderModalOptions(data, selectId, idKey) {
  const selectElement = $(`#${selectId}`);
  selectElement.empty();
  selectElement.append('<option value="">Seleccione...</option>');
  data.forEach((item) => {
    selectElement.append(
      `<option value="${item[idKey]}">${item.nombre}</option>`
    );
  });
}
