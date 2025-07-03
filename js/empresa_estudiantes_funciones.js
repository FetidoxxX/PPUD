let globalCarrerasModulo = [];
let globalTiposReferenciaModulo = [];
let globalEstadosReferenciaModulo = [];
let currentStudentListPageModulo = 1;
let totalStudentPagesModulo = 1;
let currentSearchQueryModulo = '';

// Variable global para almacenar el elemento que activó el modal de referencia, para devolver el foco
let lastReferenceModalTriggerModulo = null;
// Variable global para almacenar el ID de la referencia que se está editando
let currentEditingReferenceIdModulo = null;

/**
 * Inicializa la lógica de JavaScript para la gestión de estudiantes en el módulo de empresa.
 * @param {Array} carreras - Array de objetos con todas las carreras disponibles.
 * @param {Array} tiposReferencia - Array de objetos con los tipos de referencia.
 * @param {Array} estadosReferencia - Array de objetos con los estados de referencia.
 */
function initializeEmpresaEstudiantes(
  carreras,
  tiposReferencia,
  estadosReferencia
) {
  globalCarrerasModulo = carreras;
  globalTiposReferenciaModulo = tiposReferencia;
  globalEstadosReferenciaModulo = estadosReferencia;

  // DEBUG: Log globalCarrerasModulo para verificar que se carga correctamente
  console.log(
    'DEBUG: globalCarrerasModulo en initializeEmpresaEstudiantes:',
    globalCarrerasModulo
  );

  loadStudentListModulo(currentStudentListPageModulo, currentSearchQueryModulo);

  // Event listener para la búsqueda
  $('#searchStudentInput').on('keypress', function (e) {
    if (e.which === 13) {
      // Enter key
      $('#searchStudentBtn').click();
    }
  });

  $('#searchStudentBtn').on('click', function () {
    currentSearchQueryModulo = $('#searchStudentInput').val();
    currentStudentListPageModulo = 1; // Reset to first page on new search
    loadStudentListModulo(
      currentStudentListPageModulo,
      currentSearchQueryModulo
    );
  });

  // Event listener para la paginación
  $('#studentPagination').on('click', '.page-link', function (e) {
    e.preventDefault();
    const page = $(this).data('page');
    if (page && page !== currentStudentListPageModulo) {
      currentStudentListPageModulo = page;
      loadStudentListModulo(
        currentStudentListPageModulo,
        currentSearchQueryModulo
      );
    }
  });

  // Delegación de eventos para los botones "Ver Perfil" y nombres de estudiante
  $(document).on('click', '.view-student-profile-modulo', function (e) {
    e.preventDefault();
    const studentId = $(this).data('id');
    const studentName = $(this).data('name');
    showStudentProfileModalModulo(studentId, studentName);
  });

  // Manejar el clic en el botón "Crear Referencia" dentro del modal de perfil de estudiante
  $(document).on('submit', '.create-reference-form-modulo', function (event) {
    event.preventDefault();
    lastReferenceModalTriggerModulo = this; // Guardar el elemento que activó el modal
    const studentId = $(this).data('student-id');
    const studentName = $(this).data('student-name');
    openCreateReferenceModalModulo(studentId, studentName);
  });

  // Manejar el envío del formulario de referencia (creación/edición)
  $('#empresaEstudiantesReferenciaForm').submit(function (event) {
    event.preventDefault();
    console.log(
      'Formulario de referencia (empresaEstudiantesReferenciaForm) enviado. Previniendo default.'
    );
    if (currentEditingReferenceIdModulo) {
      updateReferenceModulo();
    } else {
      saveReferenceModulo();
    }
  });

  // Escuchar el evento de cierre del modal de referencia para limpiar el formulario y resetear el ID de edición
  $('#empresaEstudiantesReferenciaModal').on('hidden.bs.modal', function () {
    $('#empresaEstudiantesReferenciaForm')[0].reset(); // Limpiar el formulario
    currentEditingReferenceIdModulo = null; // Resetear el ID de la referencia que se está editando
    $('#empresaEstudiantesReferenciaModalLabel').text('Crear Nueva Referencia'); // Resetear título del modal
    $('#empresaEstudiantesSaveReferenceBtn').html(
      '<i class="fas fa-save me-2"></i>Guardar Referencia'
    ); // Resetear texto del botón
    if (lastReferenceModalTriggerModulo) {
      $(lastReferenceModalTriggerModulo).focus(); // Devolver el foco al elemento que abrió el modal
      lastReferenceModalTriggerModulo = null;
    }
  });

  // Delegación de eventos para los botones de editar/eliminar referencias dentro del modal de perfil
  $(document).on('click', '.edit-reference-btn-modulo', function () {
    const idReferencia = $(this).data('id');
    const studentId = $('#empresaEstudiantesPerfilModal').data('student-id');
    const studentName = $('#empresaEstudiantesPerfilModal').data(
      'student-name'
    );
    loadReferenceForEditModulo(idReferencia, studentId, studentName);
  });

  $(document).on('click', '.delete-reference-btn-modulo', function () {
    const idReferencia = $(this).data('id');
    confirmAndDeleteReferenceModulo(idReferencia);
  });
}

/**
 * Carga el listado de estudiantes desde el servidor y lo muestra.
 * @param {number} page - La página actual a cargar.
 * @param {string} busqueda - Término de búsqueda.
 */
function loadStudentListModulo(page, busqueda) {
  const studentListContainer = $('#studentListContainer');
  studentListContainer.html(
    '<p class="text-center text-muted w-100 py-4"><i class="fas fa-spinner fa-spin me-2"></i>Cargando estudiantes...</p>'
  );
  $('#studentPagination').empty();

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'GET',
    data: {
      action: 'obtener_listado_estudiantes',
      page: page,
      busqueda: busqueda,
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'DEBUG (empresa_estudiantes_funciones.js - loadStudentListModulo success):',
        response
      );
      if (response.success) {
        studentListContainer.html(response.html);
        totalStudentPagesModulo = Math.ceil(
          response.totalEstudiantes / response.limit
        );
        renderPaginationModulo(response.currentPage, totalStudentPagesModulo);
      } else {
        studentListContainer.html(
          '<p class="text-center text-danger w-100 py-4">Error al cargar estudiantes: ' +
            response.message +
            '</p>'
        );
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error(
        'ERROR (empresa_estudiantes_funciones.js - loadStudentListModulo error):',
        { xhr, status, error }
      );
      studentListContainer.html(
        '<p class="text-center text-danger w-100 py-4">Error de conexión al cargar el listado de estudiantes.</p>'
      );
      Swal.fire(
        'Error de Conexión',
        'No se pudo cargar el listado de estudiantes. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Renderiza los controles de paginación.
 * @param {number} currentPage - La página actual.
 * @param {number} totalPages - El número total de páginas.
 */
function renderPaginationModulo(currentPage, totalPages) {
  const paginationContainer = $('#studentPagination');
  paginationContainer.empty();

  if (totalPages <= 1) {
    return;
  }

  let paginationHtml = '';

  // Previous button
  paginationHtml += `<li class="page-item ${
    currentPage === 1 ? 'disabled' : ''
  }">
                        <a class="page-link" href="#" data-page="${
                          currentPage - 1
                        }" aria-label="Previous">
                          <span aria-hidden="true">&laquo;</span>
                        </a>
                      </li>`;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    paginationHtml += `<li class="page-item ${
      currentPage === i ? 'active' : ''
    }">
                          <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
  }

  // Next button
  paginationHtml += `<li class="page-item ${
    currentPage === totalPages ? 'disabled' : ''
  }">
                        <a class="page-link" href="#" data-page="${
                          currentPage + 1
                        }" aria-label="Next">
                          <span aria-hidden="true">&raquo;</span>
                        </a>
                      </li>`;

  paginationContainer.html(paginationHtml);
}

/**
 * Muestra el modal con el perfil completo de un estudiante.
 * @param {string} idEstudiante - El ID del estudiante a mostrar.
 * @param {string} studentName - El nombre completo del estudiante.
 */
function showStudentProfileModalModulo(idEstudiante, studentName) {
  const perfilEstudianteModal = $('#empresaEstudiantesPerfilModal');
  const perfilEstudianteContent = $('#empresaEstudiantesPerfilContent');
  const perfilEstudianteModalLabel = $('#empresaEstudiantesPerfilModalLabel');
  const btnCrearReferenciaEstudiante = $('#btnCrearReferenciaEstudianteModulo');
  const createReferenceForm = $('#createReferenceFormModulo');

  perfilEstudianteModalLabel.text('Perfil de ' + studentName);
  perfilEstudianteContent.html(
    '<p class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i>Cargando perfil...</p>'
  );
  $('#empresaEstudiantesReferenciasListContainer').html(
    '<p class="text-muted text-center">Cargando referencias...</p>'
  );

  // Almacenar el ID y nombre del estudiante en el modal para uso posterior (e.g., crear referencia)
  perfilEstudianteModal.data('student-id', idEstudiante);
  perfilEstudianteModal.data('student-name', studentName);
  createReferenceForm.data('student-id', idEstudiante);
  createReferenceForm.data('student-name', studentName);

  // Mostrar el botón de crear referencia
  btnCrearReferenciaEstudiante.show();

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'GET',
    data: {
      action: 'obtener_perfil_estudiante_completo',
      idEstudiante: idEstudiante,
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'DEBUG (empresa_estudiantes_funciones.js - showStudentProfileModalModulo success):',
        response
      );
      if (response.success && response.data) {
        const estudiante = response.data;

        // DEBUG: Log estudiante.carreras_interes_ids para verificar los IDs que vienen del backend
        console.log(
          'DEBUG: estudiante.carreras_interes_ids:',
          estudiante.carreras_interes_ids
        );

        // Construir el HTML para el modo de visualización del perfil
        let profileHtml = `
          <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Información Personal</h6>
          <div class="row mb-3">
            <div class="col-md-6"><strong>Nombre:</strong> ${
              estudiante.nombre || 'N/A'
            }</div>
            <div class="col-md-6"><strong>Apellidos:</strong> ${
              estudiante.apellidos || 'N/A'
            }</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6"><strong>Tipo Documento:</strong> ${
              estudiante.tipo_documento_nombre || 'N/A'
            }</div>
            <div class="col-md-6"><strong>Número Documento:</strong> ${
              estudiante.n_doc || 'N/A'
            }</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6"><strong>Fecha de Nacimiento:</strong> ${
              estudiante.fechaNac
                ? new Date(estudiante.fechaNac).toLocaleDateString()
                : 'N/A'
            }</div>
            <div class="col-md-6"><strong>Ciudad:</strong> ${
              estudiante.ciudad_nombre || 'N/A'
            }</div>
          </div>
          <div class="mb-4"><strong>Dirección:</strong> ${
            estudiante.direccion || 'N/A'
          }</div>

          <h6 class="text-primary mb-3"><i class="fas fa-at me-2"></i>Información de Contacto</h6>
          <div class="row mb-3">
            <div class="col-md-6"><strong>Correo:</strong> ${
              estudiante.correo || 'N/A'
            }</div>
            <div class="col-md-6"><strong>Teléfono:</strong> ${
              estudiante.telefono || 'N/A'
            }</div>
          </div>
          <hr class="my-4">

          <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Información Académica</h6>
          <div class="row mb-3">
            <div class="col-md-6"><strong>Código Estudiante:</strong> ${
              estudiante.codigo_estudiante || 'N/A'
            }</div>
            <div class="col-md-6"><strong>Carrera Principal:</strong> ${
              estudiante.carrera_nombre || 'N/A'
            }</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6"><strong>Semestre:</strong> ${
              estudiante.semestre || 'N/A'
            }</div>
            <div class="col-md-6"><strong>Promedio Académico:</strong> ${
              estudiante.promedio_academico || 'N/A'
            }</div>
          </div>
          <div class="mb-4">
            <strong>Carreras de Interés:</strong>
            <ul class="list-group list-group-flush mt-2">
              ${
                estudiante.carreras_interes_ids &&
                estudiante.carreras_interes_ids.length > 0
                  ? estudiante.carreras_interes_ids
                      .map((id) => {
                        const carrera = globalCarrerasModulo.find((c) => {
                          // DEBUG: Log para ver la comparación de tipos y valores
                          console.log(
                            `DEBUG: Comparando ID de interés: ${id} (tipo: ${typeof id}) con ID de carrera global: ${
                              c.id_carrera
                            } (tipo: ${typeof c.id_carrera})`
                          );
                          return parseInt(c.id_carrera) === id;
                        });
                        return `<li class="list-group-item py-1">${
                          carrera
                            ? carrera.nombre
                            : 'Carrera desconocida (ID: ' + id + ')'
                        }</li>`;
                      })
                      .join('')
                  : '<li class="list-group-item py-1 text-muted">No se han seleccionado carreras de interés.</li>'
              }
            </ul>
          </div>
          <hr class="my-4">

          <h6 class="text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>Habilidades e Intereses</h6>
          <div class="mb-3"><strong>Habilidades:</strong> ${
            estudiante.habilidades || 'N/A'
          }</div>
          <div class="mb-3"><strong>Experiencia Laboral:</strong> ${
            estudiante.experiencia_laboral || 'N/A'
          }</div>
          <div class="mb-3"><strong>Certificaciones:</strong> ${
            estudiante.certificaciones || 'N/A'
          }</div>
          <div class="mb-3"><strong>Idiomas:</strong> ${
            estudiante.idiomas || 'N/A'
          }</div>
          <div class="mb-3"><strong>Objetivos Profesionales:</strong> ${
            estudiante.objetivos_profesionales || 'N/A'
          }</div>
        `;
        perfilEstudianteContent.html(profileHtml);

        // Cargar y mostrar las referencias para este estudiante
        loadAndDisplayReferencesModulo(idEstudiante);

        // Mostrar el modal
        new bootstrap.Modal(perfilEstudianteModal).show();
      } else {
        perfilEstudianteContent.html(
          '<p class="text-center text-danger py-5">Error al cargar el perfil: ' +
            response.message +
            '</p>'
        );
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error(
        'ERROR (empresa_estudiantes_funciones.js - showStudentProfileModalModulo error):',
        { xhr, status, error }
      );
      perfilEstudianteContent.html(
        '<p class="text-center text-danger py-5">Error de conexión al cargar el perfil del estudiante.</p>'
      );
      Swal.fire(
        'Error de Conexión',
        'No se pudo cargar el perfil del estudiante. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Carga y muestra las referencias de un estudiante dentro del modal de perfil.
 * @param {string} studentId - El ID del estudiante.
 */
function loadAndDisplayReferencesModulo(studentId) {
  const referenciasListContainer = $(
    '#empresaEstudiantesReferenciasListContainer'
  );
  referenciasListContainer.html(
    '<p class="text-muted text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Cargando referencias...</p>'
  );

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'GET',
    data: {
      action: 'obtener_referencias_estudiante_perfil',
      idEstudiante: studentId,
      empresa_id: EMPRESA_ID_MODULO, // Usar la variable global JavaScript de este módulo
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'DEBUG (empresa_estudiantes_funciones.js - loadAndDisplayReferencesModulo success):',
        response
      );
      if (response.success && response.html) {
        referenciasListContainer.html(response.html);
      } else {
        referenciasListContainer.html(
          '<p class="text-muted text-center py-3">No hay referencias para este estudiante.</p>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error(
        'ERROR (empresa_estudiantes_funciones.js - loadAndDisplayReferencesModulo error):',
        { xhr, status, error }
      );
      referenciasListContainer.html(
        '<p class="text-danger text-center py-3">Error al cargar las referencias.</p>'
      );
    },
  });
}

/**
 * Abre el modal para crear una nueva referencia.
 * @param {string} studentId - El ID del estudiante al que se le creará la referencia.
 * @param {string} studentName - El nombre del estudiante.
 */
function openCreateReferenceModalModulo(studentId, studentName) {
  currentEditingReferenceIdModulo = null; // Asegurarse de que es una creación
  $('#empresaEstudiantesReferenciaModalLabel').text(
    'Crear Referencia para ' + studentName
  );
  $('#empresaEstudiantesSaveReferenceBtn').html(
    '<i class="fas fa-save me-2"></i>Guardar Referencia'
  );

  $('#empresaEstudiantesReferenciaEstudianteId').val(studentId);
  // No se renderiza el select de tipos de referencia, se asume el tipo "empresa_a_estudiante" (ID 2)

  new bootstrap.Modal($('#empresaEstudiantesReferenciaModal')).show();
}

/**
 * Guarda una nueva referencia en la base de datos.
 */
function saveReferenceModulo() {
  const formData = {
    action: 'crear_referencia',
    estudiante_idEstudiante: $(
      '#empresaEstudiantesReferenciaEstudianteId'
    ).val(),
    empresa_idEmpresa: EMPRESA_ID_MODULO, // Usar la variable global de este módulo
    tipo_referencia_id_tipo_referencia: 2, // Se asume tipo "empresa_a_estudiante" (ID 2)
    puntuacion: $('#empresaEstudiantesPuntuacion').val(),
    comentario: $('#empresaEstudiantesComentario').val(),
  };

  Swal.fire({
    title: 'Guardando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log('Respuesta de creación (AJAX - Módulo):', response);
      if (response.success) {
        Swal.fire('¡Éxito!', response.message, 'success');
        $('#empresaEstudiantesReferenciaModal').modal('hide'); // Cerrar el modal
        // Recargar las referencias del estudiante
        const studentId = $('#empresaEstudiantesPerfilModal').data(
          'student-id'
        );
        if (studentId) {
          loadAndDisplayReferencesModulo(studentId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al guardar referencia (AJAX - Módulo):', {
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
 * Carga los datos de una referencia existente en el modal de edición.
 * @param {string} idReferencia - El ID de la referencia a editar.
 * @param {string} studentId - El ID del estudiante asociado.
 * @param {string} studentName - El nombre del estudiante asociado.
 */
function loadReferenceForEditModulo(idReferencia, studentId, studentName) {
  currentEditingReferenceIdModulo = idReferencia;
  $('#empresaEstudiantesReferenciaModalLabel').text('Editar Referencia');
  $('#empresaEstudiantesSaveReferenceBtn').html(
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
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'GET',
    data: {
      action: 'obtener_referencia_por_id',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log(
        'Respuesta de carga de referencia (AJAX - Módulo):',
        response
      );
      if (response.success && response.data) {
        const ref = response.data;
        $('#empresaEstudiantesReferenciaEstudianteId').val(studentId);
        $('#empresaEstudiantesReferenciaEmpresaId').val(ref.empresa_idEmpresa);
        $('#empresaEstudiantesCurrentEditingReferenceId').val(ref.idReferencia);
        // No se renderiza el select de tipos de referencia, se asume el tipo "empresa_a_estudiante" (ID 2)
        $('#empresaEstudiantesPuntuacion').val(ref.puntuacion);
        $('#empresaEstudiantesComentario').val(ref.comentario);

        new bootstrap.Modal($('#empresaEstudiantesReferenciaModal')).show();
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error(
        'Error al cargar referencia para edición (AJAX - Módulo):',
        { xhr, status, error }
      );
      Swal.fire(
        'Error de Conexión',
        'No se pudo cargar la referencia para edición. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Actualiza una referencia existente en la base de datos.
 */
function updateReferenceModulo() {
  const formData = {
    action: 'actualizar_referencia',
    idReferencia: currentEditingReferenceIdModulo,
    tipo_referencia_id_tipo_referencia: 2, // Se asume tipo "empresa_a_estudiante" (ID 2)
    puntuacion: $('#empresaEstudiantesPuntuacion').val(),
    comentario: $('#empresaEstudiantesComentario').val(),
  };

  Swal.fire({
    title: 'Actualizando referencia...',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log('Respuesta de actualización (AJAX - Módulo):', response);
      if (response.success) {
        Swal.fire('¡Actualizada!', response.message, 'success');
        $('#empresaEstudiantesReferenciaModal').modal('hide'); // Cerrar el modal
        // Recargar las referencias del estudiante
        const studentId = $('#empresaEstudiantesPerfilModal').data(
          'student-id'
        );
        if (studentId) {
          loadAndDisplayReferencesModulo(studentId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al actualizar referencia (AJAX - Módulo):', {
        xhr,
        status,
        error,
      });
      Swal.fire(
        'Error de Conexión',
        'No se pudo actualizar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Confirma y "elimina" (desactiva) una referencia.
 * @param {string} idReferencia - El ID de la referencia a eliminar.
 */
function confirmAndDeleteReferenceModulo(idReferencia) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción desactivará la referencia. ¡No podrás revertirla!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      deleteReferenceModulo(idReferencia);
    }
  });
}

/**
 * Realiza la solicitud AJAX para "eliminar" (desactivar) una referencia.
 * @param {string} idReferencia - El ID de la referencia a desactivar.
 */
function deleteReferenceModulo(idReferencia) {
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
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php', // Usar el nuevo controlador
    type: 'POST',
    data: {
      action: 'eliminar_referencia',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      Swal.close();
      console.log('Respuesta de eliminación (AJAX - Módulo):', response);
      if (response.success) {
        Swal.fire('¡Desactivada!', response.message, 'success');
        // Recargar las referencias del estudiante después de eliminar/desactivar
        const studentId = $('#empresaEstudiantesPerfilModal').data(
          'student-id'
        );
        if (studentId) {
          loadAndDisplayReferencesModulo(studentId);
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.close();
      console.error('Error al eliminar referencia (AJAX - Módulo):', {
        xhr,
        status,
        error,
      });
      Swal.fire(
        'Error de Conexión',
        'No se pudo desactivar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Función de utilidad para renderizar opciones en un <select>.
 * Esta función ya no se usa para el tipo de referencia en este módulo,
 * pero se mantiene por si se necesita para otros selectores en el futuro.
 * @param {Array} data - El array de objetos con los datos (ej: [{id: 1, nombre: 'Opción 1'}]).
 * @param {string} selectId - El ID del elemento <select> en el DOM.
 * @param {string} valueKey - La clave del objeto a usar como 'value' de la opción (ej: 'id_tipo').
 * @param {string} textKey - La clave del objeto a usar como texto visible de la opción (ej: 'nombre').
 * @param {string|number} selectedValue - El valor que debe estar pre-seleccionado.
 */
function renderSelectOptionsModulo(
  data,
  selectId,
  valueKey,
  textKey,
  selectedValue = null
) {
  const selectElement = $(`#${selectId}`);
  selectElement.empty();
  selectElement.append('<option value="">Seleccione...</option>'); // Opción por defecto

  data.forEach((item) => {
    const option = `<option value="${item[valueKey]}">${item[textKey]}</option>`;
    selectElement.append(option);
  });

  if (selectedValue !== null) {
    selectElement.val(selectedValue);
  }
}
