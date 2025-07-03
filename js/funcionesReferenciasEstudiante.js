let lastReferenceModalTriggerEstudiante = null;
let currentEditingReferenceIdEstudiante = null;

/**
 * Carga el perfil completo de la empresa y sus referencias en un modal.
 * Esta función es el punto de entrada para mostrar el perfil de la empresa
 * desde la vista del estudiante.
 * @param {string} idEmpresa - El ID de la empresa a mostrar.
 * @param {string} empresaNombre - El nombre de la empresa.
 */
function loadCompanyProfileModalForStudent(idEmpresa, empresaNombre) {
  console.log(
    'DEBUG: loadCompanyProfileModalForStudent called with idEmpresa:',
    idEmpresa,
    'empresaNombre:',
    empresaNombre
  ); // DEBUG
  const detalleEmpresaModal = $('#detalleEmpresaModal');
  const contenidoDetalleEmpresa = $('#contenidoDetalleEmpresa');
  const detalleEmpresaModalLabel = $('#detalleEmpresaModalLabel');
  const createReferenceFormEstudiante = $('#createReferenceFormEstudiante');
  const empresaReferenciasListContainer = $('#empresaReferenciasListContainer');

  // Limpiar y mostrar mensajes de carga
  detalleEmpresaModalLabel.text('Perfil de ' + empresaNombre);
  contenidoDetalleEmpresa.html(
    '<p class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i>Cargando perfil de la empresa...</p>'
  );
  empresaReferenciasListContainer.html(
    '<p class="text-muted text-center">Cargando referencias...</p>'
  );

  // Almacenar el ID y nombre de la empresa en el formulario de crear referencia dentro del modal
  // MODIFICADO: Establecer el data-company-id directamente en el formulario
  createReferenceFormEstudiante.attr('data-company-id', idEmpresa);
  createReferenceFormEstudiante.data('company-name', empresaNombre);
  // También establecer el valor en el input hidden del modal de referencia para la empresa
  // Esto es para cuando el modal de referencia se abre, ya tenga el ID de la empresa
  $('#estudianteEmpresaReferenciaEmpresaId').val(idEmpresa);
  console.log(
    'DEBUG: Value set for #estudianteEmpresaReferenciaEmpresaId (in loadCompanyProfileModalForStudent):',
    $('#estudianteEmpresaReferenciaEmpresaId').val()
  ); // DEBUG

  // Cargar el perfil de la empresa
  $.ajax({
    url: '../CONTROLADOR/ajax_referencias_estudiante.php', // Controlador para el estudiante
    type: 'GET',
    data: {
      action: 'obtener_perfil_empresa_completo',
      idEmpresa: idEmpresa,
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'DEBUG (funcionesReferenciasEstudiante.js - loadCompanyProfileModalForStudent success):',
        response
      );
      if (response.success && response.html) {
        contenidoDetalleEmpresa.html(response.html);
        // El nombre de la empresa en el título del modal ya se actualizó arriba
        // y el nombre en el jumbotron de la página principal no es relevante aquí.

        // Cargar y mostrar las referencias para esta empresa
        loadAndDisplayReferencesEstudiante(idEmpresa);

        // Mostrar el modal
        new bootstrap.Modal(detalleEmpresaModal).show();
      } else {
        contenidoDetalleEmpresa.html(
          '<p class="text-center text-danger py-5">Error al cargar el perfil de la empresa: ' +
            response.message +
            '</p>'
        );
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error(
        'ERROR (funcionesReferenciasEstudiante.js - loadCompanyProfileModalForStudent error):',
        {
          xhr,
          status,
          error,
        }
      );
      contenidoDetalleEmpresa.html(
        '<p class="text-center text-danger py-5">Error de conexión al cargar el perfil de la empresa.</p>'
      );
      Swal.fire(
        'Error de Conexión',
        'No se pudo cargar el perfil de la empresa. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Carga y muestra las referencias de una empresa.
 * @param {string} idEmpresa - El ID de la empresa.
 */
function loadAndDisplayReferencesEstudiante(idEmpresa) {
  console.log(
    'DEBUG: loadAndDisplayReferencesEstudiante called with idEmpresa:',
    idEmpresa,
    'ESTUDIANTE_ID_MODULO:',
    ESTUDIANTE_ID_MODULO
  ); // DEBUG
  const referenciasListContainer = $('#empresaReferenciasListContainer');
  referenciasListContainer.html(
    '<p class="text-muted text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Cargando referencias...</p>'
  );

  $.ajax({
    url: '../CONTROLADOR/ajax_referencias_estudiante.php', // Controlador para el estudiante
    type: 'GET',
    data: {
      action: 'obtener_referencias_empresa_perfil',
      idEmpresa: idEmpresa,
      idEstudiante: ESTUDIANTE_ID_MODULO, // ID del estudiante logueado (desde variable global en pruebaEstudiante.php)
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'DEBUG (funcionesReferenciasEstudiante.js - loadAndDisplayReferencesEstudiante success):',
        response
      );
      if (response.success && response.html) {
        referenciasListContainer.html(response.html);
      } else {
        referenciasListContainer.html(
          '<p class="text-muted text-center py-3">No hay referencias para esta empresa.</p>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error(
        'ERROR (funcionesReferenciasEstudiante.js - loadAndDisplayReferencesEstudiante error):',
        {
          xhr,
          status,
          error,
        }
      );
      referenciasListContainer.html(
        '<p class="text-danger text-center py-3">Error al cargar las referencias.</p>'
      );
    },
  });
}

// Manejar el clic en el botón "Crear Referencia" dentro del perfil de empresa (delegación de eventos)
$(document).on('submit', '#createReferenceFormEstudiante', function (event) {
  event.preventDefault();
  lastReferenceModalTriggerEstudiante = this; // Guardar el elemento que activó el modal
  // MODIFICADO: Obtener el companyId del atributo data-company-id del formulario
  const companyId = $(this).attr('data-company-id');
  const companyName = $(this).data('company-name');
  console.log(
    'DEBUG: Clic en "Crear Referencia". companyId del formulario:',
    companyId,
    'companyName:',
    companyName
  ); // DEBUG
  openCreateReferenceModalEstudiante(companyId, companyName);
});

// Manejar el clic en el botón de edición de referencia (delegación de eventos)
$(document).on('click', '.edit-reference-btn-estudiante', function () {
  const idReferencia = $(this).data('idReferencia');
  // Obtenemos el ID y nombre de la empresa del modal de perfil de empresa, ya que son necesarios
  // para el contexto de la referencia.
  const companyId = $('#createReferenceFormEstudiante').attr('data-company-id'); // MODIFICADO: Obtener del data-attribute
  const companyName = $('#createReferenceFormEstudiante').data('company-name');
  console.log(
    'DEBUG: Clic en "Editar Referencia". idReferencia:',
    idReferencia,
    'companyId:',
    companyId,
    'companyName:',
    companyName
  ); // DEBUG

  // Llamar a la función que carga los datos de la referencia desde el servidor
  loadReferenceForEditEstudiante(idReferencia, companyId, companyName);
});

/**
 * Abre el modal para crear una nueva referencia.
 * @param {string} companyId - El ID de la empresa a la que se le creará la referencia.
 * @param {string} companyName - El nombre de la empresa.
 */
function openCreateReferenceModalEstudiante(companyId, companyName) {
  console.log(
    'DEBUG: openCreateReferenceModalEstudiante called with companyId:',
    companyId,
    'companyName:',
    companyName
  ); // DEBUG

  if (!companyId) {
    Swal.fire(
      'Error',
      'No se pudo obtener el ID de la empresa para crear la referencia. Por favor, intente recargar la página o contacte a soporte.',
      'error'
    );
    console.error(
      'ERROR: companyId es vacío en openCreateReferenceModalEstudiante. No se puede crear la referencia.'
    );
    return; // Detener la ejecución si companyId es vacío
  }

  currentEditingReferenceIdEstudiante = null; // Asegurarse de que es una creación
  $('#estudianteEmpresaReferenciaModalLabel').text(
    'Crear Referencia para ' + companyName
  );
  $('#estudianteEmpresaSaveReferenceBtn')
    .html('<i class="fas fa-save me-2"></i>Guardar Referencia')
    .prop('disabled', false); // Habilitar el botón
  $('#estudianteEmpresaPuntuacion').val('');
  $('#estudianteEmpresaComentario').val('');
  $('#estudianteEmpresaCurrentEditingReferenceId').val(''); // Limpiar el ID de edición

  // Establecer el ID de la empresa en el input hidden del modal de referencia
  $('#estudianteEmpresaReferenciaEmpresaId').val(companyId);
  console.log(
    'DEBUG: Value set for #estudianteEmpresaReferenciaEmpresaId (in openCreateReferenceModalEstudiante):',
    $('#estudianteEmpresaReferenciaEmpresaId').val()
  ); // DEBUG
  // El ID del estudiante ya está en el input hidden del modal
  // Asegurarse de que el input hidden para el tipo de referencia tenga el valor 1 (estudiante_a_empresa)
  $('#estudianteEmpresaTipoReferencia').val(1);

  new bootstrap.Modal($('#estudianteEmpresaReferenciaModal')).show();
}

/**
 * Carga los datos de una referencia existente en el modal de edición.
 * Realiza una llamada AJAX para obtener los datos más recientes.
 * @param {string} idReferencia - El ID de la referencia a editar.
 * @param {string} companyId - El ID de la empresa asociada (para el contexto, no para la llamada).
 * @param {string} companyName - El nombre de la empresa asociada (para el contexto, no para la llamada).
 */
function loadReferenceForEditEstudiante(idReferencia, companyId, companyName) {
  console.log(
    'DEBUG: loadReferenceForEditEstudiante called with idReferencia:',
    idReferencia,
    'companyId:',
    companyId,
    'companyName:',
    companyName
  ); // DEBUG
  currentEditingReferenceIdEstudiante = idReferencia;
  $('#estudianteEmpresaReferenciaModalLabel').text('Editar Referencia');
  $('#estudianteEmpresaSaveReferenceBtn').html(
    '<i class="fas fa-save me-2"></i>Actualizar Referencia'
  );

  Swal.fire({
    title: 'Cargando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referencias_estudiante.php', // Controlador para el estudiante
    type: 'GET',
    data: {
      action: 'obtener_referencia_por_id',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log(
        'Respuesta de carga de referencia (AJAX - Módulo Estudiante):',
        response
      );
      if (response.success && response.data) {
        const ref = response.data;
        $('#estudianteEmpresaReferenciaEstudianteId').val(
          ref.estudiante_idEstudiante
        );
        $('#estudianteEmpresaReferenciaEmpresaId').val(ref.empresa_idEmpresa);
        $('#estudianteEmpresaCurrentEditingReferenceId').val(ref.idReferencia);
        $('#estudianteEmpresaPuntuacion').val(ref.puntuacion);
        $('#estudianteEmpresaComentario').val(ref.comentario);
        // Asegurarse de que el input hidden para el tipo de referencia tenga el valor 1 (estudiante_a_empresa)
        $('#estudianteEmpresaTipoReferencia').val(
          ref.tipo_referencia_id_tipo_referencia
        );

        // Validar si han pasado más de 24 horas (validación cliente-side)
        const createdDate = new Date(ref.fecha_creacion);
        const now = new Date();
        const diffHours = (now - createdDate) / (1000 * 60 * 60);

        if (diffHours > 24) {
          Swal.fire(
            'Edición no permitida',
            'Han pasado más de 24 horas desde la creación de esta referencia. No se puede editar.',
            'warning'
          );
          $('#estudianteEmpresaSaveReferenceBtn').prop('disabled', true); // Deshabilitar el botón de guardar
        } else {
          $('#estudianteEmpresaSaveReferenceBtn').prop('disabled', false); // Habilitar el botón
        }

        new bootstrap.Modal($('#estudianteEmpresaReferenciaModal')).show();
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error(
        'Error al cargar referencia para edición (AJAX - Módulo Estudiante):',
        {
          xhr,
          status,
          error,
        }
      );
      Swal.fire(
        'Error de Conexión',
        'No se pudo cargar la referencia para edición. Intente de nuevo.',
        'error'
      );
    },
  });
}

// Manejar el envío del formulario de referencia (crear o actualizar)
$(document).on('submit', '#estudianteEmpresaReferenciaForm', function (event) {
  event.preventDefault(); // Evitar el envío normal del formulario

  console.log('DEBUG: Formulario estudianteEmpresaReferenciaForm enviado.'); // DEBUG
  if (currentEditingReferenceIdEstudiante) {
    // Si hay un ID de referencia en edición, es una actualización
    updateReferenceEstudiante();
  } else {
    // Si no, es una nueva creación
    saveReferenceEstudiante();
  }
});

/**
 * Guarda una nueva referencia en la base de datos.
 */
function saveReferenceEstudiante() {
  const empresaIdVal = $('#estudianteEmpresaReferenciaEmpresaId').val();
  console.log(
    'DEBUG: Valor de #estudianteEmpresaReferenciaEmpresaId antes de formData:',
    empresaIdVal
  ); // DEBUG

  const formData = {
    action: 'crear_referencia',
    estudiante_idEstudiante: $(
      '#estudianteEmpresaReferenciaEstudianteId'
    ).val(),
    empresa_idEmpresa: empresaIdVal, // Usar el valor capturado
    tipo_referencia_id_tipo_referencia: $(
      '#estudianteEmpresaTipoReferencia'
    ).val(), // Enviar el tipo de referencia
    puntuacion: $('#estudianteEmpresaPuntuacion').val(),
    comentario: $('#estudianteEmpresaComentario').val(),
  };

  console.log(
    'DEBUG: formData being sent for saveReferenceEstudiante:',
    formData
  ); // DEBUG

  Swal.fire({
    title: 'Guardando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referencias_estudiante.php', // Controlador para el estudiante
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log(
        'Respuesta de creación (AJAX - Módulo Estudiante):',
        response
      );
      if (response.success) {
        Swal.fire('¡Éxito!', response.message, 'success');
        $('#estudianteEmpresaReferenciaModal').modal('hide'); // Cerrar el modal
        // Recargar las referencias de la empresa
        const companyId = $('#estudianteEmpresaReferenciaEmpresaId').val(); // Obtener el ID de la empresa del modal de referencia
        if (companyId) {
          loadAndDisplayReferencesEstudiante(companyId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al guardar referencia (AJAX - Módulo Estudiante):', {
        xhr,
        status,
        error,
      });
      Swal.fire(
        'Error de Conexión',
        'No se pudo guardar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Actualiza una referencia existente en la base de datos.
 */
function updateReferenceEstudiante() {
  const formData = {
    action: 'actualizar_referencia',
    idReferencia: currentEditingReferenceIdEstudiante,
    puntuacion: $('#estudianteEmpresaPuntuacion').val(),
    comentario: $('#estudianteEmpresaComentario').val(),
  };

  console.log(
    'DEBUG: formData being sent for updateReferenceEstudiante:',
    formData
  ); // DEBUG

  Swal.fire({
    title: 'Actualizando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/ajax_referencias_estudiante.php', // Controlador para el estudiante
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log(
        'Respuesta de actualización (AJAX - Módulo Estudiante):',
        response
      );
      if (response.success) {
        Swal.fire('¡Actualizada!', response.message, 'success');
        $('#estudianteEmpresaReferenciaModal').modal('hide'); // Cerrar el modal
        // Recargar las referencias de la empresa
        const companyId = $('#estudianteEmpresaReferenciaEmpresaId').val(); // Obtener el ID de la empresa del modal de referencia
        if (companyId) {
          loadAndDisplayReferencesEstudiante(companyId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error(
        'Error al actualizar referencia (AJAX - Módulo Estudiante):',
        {
          xhr,
          status,
          error,
        }
      );
      Swal.fire(
        'Error de Conexión',
        'No se pudo actualizar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

// Resetear el formulario y la variable de edición al cerrar el modal de referencia
$('#estudianteEmpresaReferenciaModal').on('hidden.bs.modal', function () {
  console.log(
    'DEBUG: estudianteEmpresaReferenciaModal cerrado. Reseteando formulario y variables.'
  ); // DEBUG
  currentEditingReferenceIdEstudiante = null;
  $('#estudianteEmpresaReferenciaForm')[0].reset(); // Limpiar el formulario
  $('#estudianteEmpresaCurrentEditingReferenceId').val(''); // Asegurarse de que el hidden input también se limpie
  // Devolver el foco al elemento que activó el modal si existe
  if (lastReferenceModalTriggerEstudiante) {
    $(lastReferenceModalTriggerEstudiante).focus();
    lastReferenceModalTriggerEstudiante = null;
  }
});
