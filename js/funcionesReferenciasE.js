let globalTiposReferencia = [];
let globalEstadosReferencia = [];

// Variable global para almacenar el elemento que activó el modal de referencia, para devolver el foco
let lastReferenceModalTrigger = null;

// Variable global para almacenar el ID de la referencia que se está editando
let currentEditingReferenceId = null;

/**
 * Inicializa las variables globales para los selectores del modal de referencias
 * y configura los manejadores de eventos para los formularios y modales.
 * @param {Array} tiposReferencia - Array de objetos con los tipos de referencia.
 * @param {Array} estadosReferencia - Array de objetos con los estados de referencia.
 */
function initializeReferenciasE(tiposReferencia, estadosReferencia) {
  globalTiposReferencia = tiposReferencia;
  globalEstadosReferencia = estadosReferencia;

  // Manejar el envío del formulario de referencia (creación)
  $('#referenciaForm').submit(function (event) {
    event.preventDefault();
    console.log(
      'Formulario de creación (referenciaForm) enviado. Previniendo default.'
    );
    saveReference();
  });

  // Manejar el envío del formulario de edición de referencia
  $('#editReferenciaForm').submit(function (event) {
    event.preventDefault();
    console.log(
      'Formulario de edición del modal (editReferenciaForm) enviado. Previniendo default.'
    );
    updateReference();
  });

  // Escuchar el evento de cierre del modal de creación para limpiar el formulario
  $('#crearReferenciaModal').on('hidden.bs.modal', function () {
    resetReferenceForm();
    // Restaurar el foco al elemento que abrió este modal, si existe
    if (lastReferenceModalTrigger) {
      lastReferenceModalTrigger.focus();
      lastReferenceModalTrigger = null; // Limpiar después de usar
    }
  });

  // Escuchar el evento de cierre del modal de edición para limpiar el ID y recargar referencias
  $('#editarReferenciaModal').on('hidden.bs.modal', function () {
    currentEditingReferenceId = null;
    console.log(
      'Modal de edición de referencia oculto. Intentando recargar referencias...'
    );
    const studentId = $('#perfilEstudianteModal').data('student-id');
    console.log('Student ID al ocultar modal de edición:', studentId);
    if (studentId) {
      loadAndDisplayReferences(studentId);
    } else {
      console.warn(
        'No se pudo obtener el studentId al cerrar el modal de edición para recargar referencias.'
      );
    }
  });

  // --- DELEGACIÓN DE EVENTOS PARA FORMULARIOS DE EDITAR Y ELIMINAR ---
  // Cambiado el target de la delegación a $(document) para mayor robustez.
  // Esto asegura que los listeners estén siempre activos, incluso si los elementos
  // se cargan dinámicamente o el DOM se modifica.

  // Escuchar el envío de formularios de edición
  $(document).on('submit', '.edit-reference-form', function (event) {
    event.preventDefault(); // Prevenir el envío normal del formulario
    console.log(
      'Formulario de edición de referencia (dinámico, .edit-reference-form) enviado. Previniendo default.'
    );
    const idReferencia = $(this).data('id-referencia'); // Obtener el ID del atributo data-id-referencia
    if (idReferencia) {
      abrirModalEditarReferencia(idReferencia);
    } else {
      console.error('Error: ID de referencia no encontrado para edición.');
      Swal.fire(
        'Error',
        'No se pudo obtener el ID de la referencia para editar.',
        'error'
      );
    }
  });

  // Escuchar el envío de formularios de eliminación
  $(document).on('submit', '.delete-reference-form', function (event) {
    event.preventDefault(); // Prevenir el envío normal del formulario
    console.log(
      'Formulario de eliminación de referencia (dinámico, .delete-reference-form) enviado. Previniendo default.'
    );
    const idReferencia = $(this).data('id-referencia'); // Obtener el ID del atributo data-id-referencia
    if (idReferencia) {
      confirmarEliminarReferencia(idReferencia);
    } else {
      console.error('Error: ID de referencia no encontrado para eliminación.');
      Swal.fire(
        'Error',
        'No se pudo obtener el ID de la referencia para eliminar.',
        'error'
      );
    }
  });

  // NUEVO: Escuchar el envío del formulario de creación de referencia
  $(document).on('submit', '.create-reference-form', function (event) {
    event.preventDefault();
    console.log(
      'Formulario de creación de referencia (dinámico, .create-reference-form) enviado. Previniendo default.'
    );
    const studentId = $(this).data('student-id');
    const studentName = $(this).data('student-name');
    // Pasamos el elemento que activó el modal para devolver el foco
    const triggerElement = $(this).find('button')[0];
    if (studentId && studentName) {
      abrirModalCrearReferencia(studentId, studentName, triggerElement);
    } else {
      console.error(
        'Error: ID o nombre de estudiante no encontrado para crear referencia.'
      );
      Swal.fire(
        'Error',
        'No se pudo obtener la información del estudiante para crear la referencia.',
        'error'
      );
    }
  });
}

/**
 * Abre el modal para crear una nueva referencia.
 * @param {string} studentId - El ID del estudiante al que se le creará la referencia.
 * @param {string} studentName - El nombre completo del estudiante.
 * @param {HTMLElement} triggerElement - El elemento del DOM que activó la apertura del modal.
 */
function abrirModalCrearReferencia(studentId, studentName, triggerElement) {
  lastReferenceModalTrigger = triggerElement;
  $('#referenciaEstudianteId').val(studentId);
  $('#referenciaEstudianteNombre').text(studentName);
  $('#crearReferenciaModal').modal('show');
}

/**
 * Guarda (crea) una nueva referencia.
 */
function saveReference() {
  const form = $('#referenciaForm');
  const formData = form.serialize();

  Swal.fire({
    title: 'Creando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php',
    type: 'POST',
    data: formData + '&action=crear_referencia',
    dataType: 'json',
    success: function (response) {
      Swal.close();
      if (response.success) {
        Swal.fire('¡Éxito!', response.message, 'success');
        $('#crearReferenciaModal').modal('hide');
        // Recargar las referencias del estudiante después de crear
        const studentId = $('#referenciaEstudianteId').val();
        console.log(
          'Referencia creada. Recargando referencias para studentId:',
          studentId
        );
        loadAndDisplayReferences(studentId);
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al crear referencia:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo crear la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Resetea el formulario de creación de referencia.
 */
function resetReferenceForm() {
  $('#referenciaForm')[0].reset();
  $('#referenciaEstudianteId').val('');
  $('#referenciaEstudianteNombre').text('');
  // Asegurarse de que el tipo de referencia sea el correcto si es un campo oculto
  $('#referenciaTipo').val(2); // Valor por defecto para "Empresa a Estudiante"
}

/**
 * Carga y muestra las referencias de un estudiante específico.
 * @param {string} studentId - El ID del estudiante cuyas referencias se cargarán.
 * @param {string} studentName - El nombre del estudiante (opcional, para actualizar el botón de crear referencia).
 */
function loadAndDisplayReferences(studentId, studentName = '') {
  // Añade studentName como parámetro opcional
  // Si studentId no se proporciona, intenta obtenerlo del modal principal
  const finalStudentId =
    studentId || $('#perfilEstudianteModal').data('student-id');

  if (!finalStudentId) {
    console.error(
      'Error: No se pudo obtener el ID del estudiante para cargar las referencias.'
    );
    $('#referenciasListContainer').html(
      '<p class="text-danger text-center py-3">Error: No se pudo cargar el ID del estudiante.</p>'
    );
    return; // Salir si no hay studentId
  }

  console.log(
    'Iniciando loadAndDisplayReferences para studentId:',
    finalStudentId
  );
  const referenciasListContainer = $('#referenciasListContainer');
  referenciasListContainer.html(
    '<p class="text-muted text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Cargando referencias...</p>'
  ); // Mensaje de carga

  // Actualizar los atributos data del formulario de crear referencia
  const createReferenceForm = $('#createReferenceForm');
  createReferenceForm.data('student-id', finalStudentId);
  // Solo actualiza el nombre si se proporciona
  if (studentName) {
    createReferenceForm.data('student-name', studentName);
  } else {
    // Si no se proporciona el nombre, intenta obtenerlo del perfil modal (si ya está cargado)
    const currentStudentName = $('#perfilEstudianteModalLabel')
      .text()
      .replace('Perfil del Estudiante - ', '');
    createReferenceForm.data('student-name', currentStudentName);
  }

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php', // Archivo AJAX para referencias
    type: 'GET',
    data: {
      action: 'obtener_referencias_estudiante',
      idEstudiante: finalStudentId,
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'Respuesta de AJAX (obtener_referencias_estudiante):',
        response
      );
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
      console.error('Error al cargar referencias (AJAX):', error);
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

/**
 * Abre el modal para editar una referencia, cargando sus datos.
 * @param {number} idReferencia - El ID de la referencia a editar.
 */
function abrirModalEditarReferencia(idReferencia) {
  currentEditingReferenceId = idReferencia; // Almacena el ID de la referencia que se está editando
  console.log('Abriendo modal de edición para idReferencia:', idReferencia);

  Swal.fire({
    title: 'Cargando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php',
    type: 'GET',
    data: {
      action: 'obtener_referencia_por_id',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log('Respuesta de AJAX (obtener_referencia_por_id):', response);
      if (response.success && response.data) {
        const ref = response.data;
        $('#editReferenciaId').val(ref.idReferencia);
        $('#editReferenciaEstudianteNombre').text(
          ref.estudiante_nombre + ' ' + ref.estudiante_apellidos
        );
        $('#editReferenciaComentario').val(ref.comentario);
        $('#editReferenciaPuntuacion').val(ref.puntuacion);
        // Asumiendo que el tipo de referencia siempre es 2 para estas operaciones
        $('#editReferenciaTipo').val(ref.tipo_referencia_id_tipo_referencia);

        $('#editarReferenciaModal').modal('show');
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al cargar referencia para edición (AJAX):', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar la referencia para edición. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Envía la solicitud para actualizar una referencia.
 */
function updateReference() {
  const form = $('#editReferenciaForm');
  const formData = form.serialize();
  console.log(
    'Enviando solicitud de actualización para idReferencia:',
    currentEditingReferenceId
  );

  Swal.fire({
    title: 'Actualizando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php',
    type: 'POST',
    data:
      formData +
      '&action=editar_referencia&idReferencia=' +
      currentEditingReferenceId,
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log('Respuesta de actualización (AJAX):', response);
      if (response.success) {
        Swal.fire('¡Éxito!', response.message, 'success');
        $('#editarReferenciaModal').modal('hide');
        // Recargar las referencias del estudiante después de actualizar
        const studentId = $('#perfilEstudianteModal').data('student-id'); // Obtener el ID del estudiante del modal padre
        console.log(
          'Actualización exitosa. Recargando referencias para studentId:',
          studentId
        );
        if (studentId) {
          loadAndDisplayReferences(studentId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al actualizar referencia (AJAX):', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo actualizar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Muestra una confirmación antes de eliminar una referencia (inactivar).
 * @param {number} idReferencia - El ID de la referencia a eliminar (inactivar).
 */
function confirmarEliminarReferencia(idReferencia) {
  console.log('Confirmando eliminación para idReferencia:', idReferencia);
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'La referencia se desactivará y no será visible para el estudiante. Podrás reactivarla si es necesario.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      eliminarReferencia(idReferencia);
    }
  });
}

/**
 * Envía la solicitud para eliminar (inactivar) una referencia.
 * @param {number} idReferencia - El ID de la referencia a eliminar (inactivar).
 */
function eliminarReferencia(idReferencia) {
  console.log(
    'Enviando solicitud de eliminación para idReferencia:',
    idReferencia
  );
  Swal.fire({
    title: 'Desactivando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referenciasE.php',
    type: 'POST',
    data: {
      action: 'eliminar_referencia',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log('Respuesta de eliminación (AJAX):', response);
      if (response.success) {
        Swal.fire('¡Desactivada!', response.message, 'success');
        // Recargar las referencias del estudiante después de eliminar/desactivar
        const studentId = $('#perfilEstudianteModal').data('student-id'); // Obtener el ID del estudiante del modal padre
        console.log(
          'Eliminación exitosa. Recargando referencias para studentId:',
          studentId
        );
        if (studentId) {
          loadAndDisplayReferences(studentId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al eliminar/desactivar referencia (AJAX):', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo completar la operación. Intente de nuevo.',
        'error'
      );
    },
  });
}
