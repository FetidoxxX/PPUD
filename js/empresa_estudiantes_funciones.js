/**
 * empresa_estudiantes_funciones.js
 *
 * Este archivo contiene las funciones JavaScript para la gestión de estudiantes
 * por parte de una empresa, incluyendo la carga del listado de estudiantes,
 * la visualización de su perfil completo y la gestión de referencias.
 */

// NOTA: EMPRESA_ID_MODULO se espera que sea una constante global definida en el script PHP
// que incluye este archivo JS (e.g., en gestion_estudiantes_empresa.php).
// No se declara aquí para evitar conflictos con la declaración 'const' de PHP.

let globalCarrerasDisponibles = [];
let globalTiposReferencia = [];
let globalEstadosReferencia = [];

/**
 * Inicializa las funciones de gestión de estudiantes para la empresa.
 * Se llama al cargar el DOM.
 * @param {Array} carreras - Array de objetos de carreras disponibles.
 * @param {Array} tiposReferencia - Array de objetos de tipos de referencia disponibles.
 * @param {Array} estadosReferencia - Array de objetos de estados de referencia disponibles.
 */
function initializeEmpresaEstudiantes(
  carreras,
  tiposReferencia,
  estadosReferencia
) {
  globalCarrerasDisponibles = carreras;
  globalTiposReferencia = tiposReferencia;
  globalEstadosReferencia = estadosReferencia;

  // Verificación de EMPRESA_ID_MODULO: Asegura que la variable global esté disponible.
  // Si por alguna razón no se ha definido en PHP, se intenta un fallback.
  if (
    typeof EMPRESA_ID_MODULO === 'undefined' ||
    EMPRESA_ID_MODULO === null ||
    EMPRESA_ID_MODULO === ''
  ) {
    console.warn(
      'EMPRESA_ID_MODULO no está definido o está vacío. Intentando obtenerlo del DOM.'
    );
    // Intentar obtenerlo de un input hidden si existe
    EMPRESA_ID_MODULO = $('#empresaEstudiantesReferenciaEmpresaId').val();
    if (!EMPRESA_ID_MODULO) {
      console.error(
        'EMPRESA_ID_MODULO sigue sin estar disponible. Algunas funcionalidades (como referencias) podrían fallar.'
      );
    }
  }

  cargarListadoEstudiantes(); // Cargar el listado inicial de estudiantes

  // Evento para el botón de búsqueda
  $('#searchStudentBtn').on('click', function () {
    cargarListadoEstudiantes(1, $('#searchStudentInput').val());
  });

  // Evento para la tecla Enter en el campo de búsqueda
  $('#searchStudentInput').on('keypress', function (e) {
    if (e.which === 13) {
      // 13 es el código de la tecla Enter
      cargarListadoEstudiantes(1, $('#searchStudentInput').val());
    }
  });

  // Delegación de eventos para los botones "Ver Perfil" en las tarjetas de estudiantes
  $(document).on('click', '.view-student-profile-modulo', function (e) {
    e.preventDefault();
    const studentId = $(this).data('id');
    const studentName = $(this).data('name');
    $('#empresaEstudiantesPerfilModalLabel').text('Perfil de ' + studentName);
    loadStudentProfileForCompany(studentId);
    $('#empresaEstudiantesPerfilModal').modal('show');

    // Actualizar el data-student-id y data-student-name del formulario de crear referencia
    $('#createReferenceFormModulo').data('student-id', studentId);
    $('#createReferenceFormModulo').data('student-name', studentName);
    $('#empresaEstudiantesReferenciaEstudianteId').val(studentId); // También para el input hidden del modal de referencia
  });

  // Delegación de eventos para el formulario de crear referencia
  $(document).on('submit', '#createReferenceFormModulo', function (e) {
    e.preventDefault();
    // Resetear el formulario del modal de referencia antes de abrirlo para crear
    $('#empresaEstudiantesReferenciaForm')[0].reset();
    $('#empresaEstudiantesCurrentEditingReferenceId').val(''); // Asegurarse de que no hay ID de referencia para edición
    $('#empresaEstudiantesReferenciaModalLabel').text('Crear Nueva Referencia'); // Título del modal
    $('#empresaEstudiantesSaveReferenceBtn').html(
      '<i class="fas fa-save me-2"></i>Guardar Referencia'
    ); // Texto e icono del botón

    // Pre-llenar el ID del estudiante en el modal de referencia
    const studentId = $(this).data('student-id');
    const studentName = $(this).data('student-name');
    $('#empresaEstudiantesReferenciaEstudianteId').val(studentId);
    // Establecer el tipo de referencia por defecto a 2 (empresa_a_estudiante)
    $('#empresaEstudiantesTipoReferencia').val(2);

    // Abrir el modal de creación/edición de referencia
    $('#empresaEstudiantesReferenciaModal').modal('show');
  });

  // Manejo del envío del formulario de referencia (crear/editar)
  $('#empresaEstudiantesReferenciaForm').submit(function (e) {
    e.preventDefault();
    const idReferencia = $(
      '#empresaEstudiantesCurrentEditingReferenceId'
    ).val(); // Obtener ID para saber si es edición

    let url = '../CONTROLADOR/empresa_estudiantes_ajax.php';
    let action = '';

    const formData = {
      comentario: $('#empresaEstudiantesComentario').val(),
      puntuacion: $('#empresaEstudiantesPuntuacion').val(),
      tipo_referencia_id_tipo_referencia: $(
        '#empresaEstudiantesTipoReferencia'
      ).val(), // Asegurar que se envía
      estudiante_idEstudiante: $(
        '#empresaEstudiantesReferenciaEstudianteId'
      ).val(),
      empresa_idEmpresa: EMPRESA_ID_MODULO, // Asegurar que el ID de la empresa logueada se envía
    };

    if (idReferencia) {
      action = 'actualizar_referencia';
      formData.idReferencia = idReferencia; // Añadir el ID de referencia para la actualización
    } else {
      action = 'crear_referencia';
    }
    formData.action = action; // Añadir la acción al objeto formData

    console.log('DEBUG: Datos a enviar para referencia:', formData); // DEBUG

    $.ajax({
      url: url,
      type: 'POST',
      data: formData, // Enviar el objeto directamente
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          Swal.fire('¡Éxito!', response.message, 'success');
          $('#empresaEstudiantesReferenciaModal').modal('hide');
          // Recargar las referencias del estudiante en el perfil
          const currentStudentId = $(
            '#empresaEstudiantesReferenciaEstudianteId'
          ).val();
          // Asegurarse de que EMPRESA_ID_MODULO esté definido antes de usarlo
          if (
            typeof EMPRESA_ID_MODULO !== 'undefined' &&
            EMPRESA_ID_MODULO !== null &&
            EMPRESA_ID_MODULO !== ''
          ) {
            loadStudentReferencesForCompany(
              currentStudentId,
              EMPRESA_ID_MODULO
            );
          } else {
            console.error(
              'EMPRESA_ID_MODULO no está disponible, no se pueden recargar las referencias.'
            );
          }
        } else {
          Swal.fire('Error', response.message, 'error');
        }
      },
      error: function (xhr, status, error) {
        console.error('Error en la operación de referencia:', {
          xhr,
          status,
          error,
        });
        Swal.fire(
          'Error',
          'No se pudo realizar la operación. Intente de nuevo.',
          'error'
        );
      },
    });
  });

  // Delegación de eventos para los botones de editar referencia
  $(document).on('click', '.edit-reference-btn-modulo', function () {
    const idReferencia = $(this).data('id');
    loadReferenceForEdit(idReferencia);
  });

  // Delegación de eventos para los botones de eliminar referencia
  $(document).on('click', '.delete-reference-btn-modulo', function () {
    const idReferencia = $(this).data('id');
    Swal.fire({
      title: '¿Está seguro?',
      text: '¡No podrá revertir esto!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminarla!',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (result.isConfirmed) {
        deleteReference(idReferencia);
      }
    });
  });
}

/**
 * Carga el listado de estudiantes activos con paginación y búsqueda.
 * @param {number} page - La página actual a cargar.
 * @param {string} busqueda - Término de búsqueda.
 */
function cargarListadoEstudiantes(page = 1, busqueda = '') {
  const studentListContainer = $('#studentListContainer');
  studentListContainer.html(
    '<p class="text-center text-muted w-100 py-4"><i class="fas fa-spinner fa-spin me-2"></i>Cargando estudiantes...</p>'
  );
  $('#studentPagination').empty();

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php',
    type: 'GET',
    data: {
      action: 'obtener_listado_estudiantes',
      page: page,
      busqueda: busqueda,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        studentListContainer.html(response.html);
        renderPagination(
          response.totalEstudiantes,
          response.limit,
          response.currentPage
        );
      } else {
        studentListContainer.html(
          `<p class="text-center text-danger w-100 py-4">${response.message}</p>`
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar listado de estudiantes:', {
        xhr,
        status,
        error,
      });
      studentListContainer.html(
        '<p class="text-center text-danger w-100 py-4">Error al cargar estudiantes. Intente de nuevo.</p>'
      );
    },
  });
}

/**
 * Renderiza los controles de paginación.
 * @param {number} totalItems - Número total de elementos.
 * @param {number} limit - Número de elementos por página.
 * @param {number} currentPage - Página actual.
 */
function renderPagination(totalItems, limit, currentPage) {
  const totalPages = Math.ceil(totalItems / limit);
  const paginationContainer = $('#studentPagination');
  paginationContainer.empty();

  if (totalPages <= 1) {
    return;
  }

  let paginationHtml = '';

  // Botón "Anterior"
  paginationHtml += `<li class="page-item ${
    currentPage === 1 ? 'disabled' : ''
  }">
                        <a class="page-link" href="#" data-page="${
                          currentPage - 1
                        }">Anterior</a>
                      </li>`;

  // Números de página
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, currentPage + 2);

  if (startPage > 1) {
    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
    if (startPage > 2) {
      paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    paginationHtml += `<li class="page-item ${
      i === currentPage ? 'active' : ''
    }">
                          <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
  }

  // Botón "Siguiente"
  paginationHtml += `<li class="page-item ${
    currentPage === totalPages ? 'disabled' : ''
  }">
                        <a class="page-link" href="#" data-page="${
                          currentPage + 1
                        }">Siguiente</a>
                      </li>`;

  paginationContainer.html(paginationHtml);

  // Evento de clic para los enlaces de paginación
  paginationContainer.find('.page-link').on('click', function (e) {
    e.preventDefault();
    const newPage = parseInt($(this).data('page'));
    if (!isNaN(newPage) && newPage > 0 && newPage <= totalPages) {
      cargarListadoEstudiantes(newPage, $('#searchStudentInput').val());
    }
  });
}

/**
 * Carga el perfil completo de un estudiante para ser visualizado por la empresa.
 * @param {string} studentId - El ID del estudiante cuyo perfil se cargará.
 */
function loadStudentProfileForCompany(studentId) {
  const perfilContentContainer = $('#empresaEstudiantesPerfilContent');

  perfilContentContainer.html(
    '<p class="text-muted text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Cargando perfil...</p>'
  );

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php',
    type: 'GET',
    data: {
      action: 'obtener_perfil_estudiante_completo',
      idEstudiante: studentId,
    },
    dataType: 'json',
    success: function (response) {
      console.log('Respuesta completa del AJAX:', response); // Log de la respuesta completa
      if (response.success && response.data) {
        const estudiante = response.data;
        console.log('Objeto estudiante recibido:', estudiante); // Log del objeto estudiante

        let carrerasInteresNombres = 'No especificadas';
        if (
          estudiante.carreras_interes_ids &&
          globalCarrerasDisponibles.length > 0
        ) {
          const nombres = estudiante.carreras_interes_ids
            .map((id) => {
              const carrera = globalCarrerasDisponibles.find(
                (c) => parseInt(c.id_carrera) === parseInt(id)
              );
              return carrera ? carrera.nombre : null;
            })
            .filter((nombre) => nombre !== null);
          if (nombres.length > 0) {
            carrerasInteresNombres = nombres.join(', ');
          }
        }

        let perfilHtml = `
          <div class="row">
            <div class="col-md-6 mb-3">
              <strong>Nombre:</strong> ${estudiante.nombre || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Apellidos:</strong> ${estudiante.apellidos || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Correo:</strong> ${estudiante.correo || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Teléfono:</strong> ${estudiante.telefono || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Fecha de Nacimiento:</strong> ${
                estudiante.fechaNac
                  ? new Date(estudiante.fechaNac).toLocaleDateString()
                  : 'N/A'
              }
            </div>
            <div class="col-md-6 mb-3">
              <strong>Tipo Documento:</strong> ${
                estudiante.tipo_documento_nombre || 'N/A'
              }
            </div>
            <div class="col-md-6 mb-3">
              <strong>Número Documento:</strong> ${estudiante.n_doc || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Dirección:</strong> ${estudiante.direccion || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Ciudad:</strong> ${estudiante.ciudad_nombre || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Código Estudiante:</strong> ${
                estudiante.codigo_estudiante || 'N/A'
              }
            </div>
            <div class="col-md-6 mb-3">
              <strong>Carrera Principal:</strong> ${
                estudiante.carrera_nombre || 'N/A'
              }
            </div>
            <div class="col-md-6 mb-3">
              <strong>Semestre:</strong> ${estudiante.semestre || 'N/A'}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Promedio Académico:</strong> ${
                estudiante.promedio_academico || 'N/A'
              }
            </div>
            <div class="col-12 mb-3">
              <strong>Carreras de Interés:</strong> ${carrerasInteresNombres}
            </div>
            <div class="col-12 mb-3">
              <strong>Habilidades:</strong> ${estudiante.habilidades || 'N/A'}
            </div>
            <div class="col-12 mb-3">
              <strong>Experiencia Laboral:</strong> ${
                estudiante.experiencia_laboral || 'N/A'
              }
            </div>
            <div class="col-12 mb-3">
              <strong>Certificaciones:</strong> ${
                estudiante.certificaciones || 'N/A'
              }
            </div>
            <div class="col-12 mb-3">
              <strong>Idiomas:</strong> ${estudiante.idiomas || 'N/A'}
            </div>
            <div class="col-12 mb-3">
              <strong>Objetivos Profesionales:</strong> ${
                estudiante.objetivos_profesionales || 'N/A'
              }
            </div>
          </div>
        `;
        perfilContentContainer.html(perfilHtml);

        // Lógica para el botón de descarga de Hoja de Vida
        console.log(
          'Valor de estudiante.hoja_vida_path:',
          estudiante.hoja_vida_path
        );
        // Se asegura de que la ruta exista y no sea una cadena vacía o solo espacios en blanco.
        if (
          typeof estudiante.hoja_vida_path === 'string' &&
          estudiante.hoja_vida_path.trim() !== ''
        ) {
          const downloadLink = estudiante.hoja_vida_path.trim();
          console.log('Ruta de descarga construida:', downloadLink);
          // Insertar el botón directamente en el perfilContentContainer
          perfilContentContainer.append(`
            <div class="mb-3 text-center">
              <a href="${downloadLink}" target="_blank" class="btn btn-success">
                <i class="fas fa-download me-2"></i> Descargar Hoja de Vida
              </a>
            </div>
          `);
          console.log(
            'Botón de descarga de CV insertado directamente en perfilContentContainer.'
          );
        } else {
          perfilContentContainer.append(`
            <div class="mb-3 text-center">
              <p class="text-muted">El estudiante no ha cargado una hoja de vida.</p>
            </div>
          `);
          console.log('Mensaje de CV no disponible insertado.');
        }

        // Cargar las referencias del estudiante
        // Asegurarse de que EMPRESA_ID_MODULO esté definido antes de usarlo
        if (
          typeof EMPRESA_ID_MODULO !== 'undefined' &&
          EMPRESA_ID_MODULO !== null &&
          EMPRESA_ID_MODULO !== ''
        ) {
          loadStudentReferencesForCompany(studentId, EMPRESA_ID_MODULO);
        } else {
          console.error(
            'EMPRESA_ID_MODULO no está definido, no se pueden cargar las referencias del estudiante.'
          );
          $('#empresaEstudiantesReferenciasListContainer').html(
            '<p class="text-danger text-center py-3">Error: ID de empresa no disponible para cargar referencias.</p>'
          );
        }
      } else {
        perfilContentContainer.html(
          `<p class="text-center text-danger py-3">${response.message}</p>`
        );
        // Si el perfil no se carga, asegurar que el mensaje de CV no disponible también se muestre o se limpie
        perfilContentContainer.append(`
            <div class="mb-3 text-center">
              <p class="text-muted">No se pudo cargar la información de la hoja de vida.</p>
            </div>
          `);
        $('#empresaEstudiantesReferenciasListContainer').html(
          '<p class="text-muted text-center py-3">No hay referencias para este estudiante.</p>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar el perfil del estudiante:', {
        xhr,
        status,
        error,
      });
      perfilContentContainer.html(
        '<p class="text-center text-danger py-3">Error de conexión al cargar el perfil. Intente de nuevo.</p>'
      );
      perfilContentContainer.append(`
            <div class="mb-3 text-center">
              <p class="text-danger">Error al cargar la información de la hoja de vida.</p>
            </div>
          `);
      $('#empresaEstudiantesReferenciasListContainer').html(
        '<p class="text-danger text-center py-3">Error de conexión al cargar referencias.</p>'
      );
    },
  });
}

/**
 * Carga las referencias de un estudiante específicas para la vista de la empresa.
 * @param {string} studentId - El ID del estudiante.
 * @param {string} empresaId - El ID de la empresa logueada para verificar permisos de edición/eliminación.
 */
function loadStudentReferencesForCompany(studentId, empresaId) {
  const referenciasListContainer = $(
    '#empresaEstudiantesReferenciasListContainer'
  );
  referenciasListContainer.html(
    '<p class="text-muted text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Cargando referencias...</p>'
  );

  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php',
    type: 'GET',
    data: {
      action: 'obtener_referencias_estudiante_perfil',
      idEstudiante: studentId,
      empresa_id: empresaId, // Pasar el ID de la empresa para la lógica de botones
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.html) {
        referenciasListContainer.html(response.html);
      } else {
        referenciasListContainer.html(
          '<p class="text-muted text-center py-3">No hay referencias para este estudiante.</p>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar referencias del estudiante (Empresa):', {
        xhr,
        status,
        error,
      });
      referenciasListContainer.html(
        '<p class="text-danger text-center py-3">Error de conexión al cargar referencias.</p>'
      );
    },
  });
}

/**
 * Carga los datos de una referencia específica para su edición.
 * @param {string} idReferencia - El ID de la referencia a editar.
 */
function loadReferenceForEdit(idReferencia) {
  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php',
    type: 'GET',
    data: {
      action: 'obtener_referencia_por_id',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.data) {
        const ref = response.data;
        $('#empresaEstudiantesCurrentEditingReferenceId').val(ref.idReferencia);
        $('#empresaEstudiantesReferenciaEstudianteId').val(
          ref.estudiante_idEstudiante
        );
        $('#empresaEstudiantesPuntuacion').val(ref.puntuacion);
        $('#empresaEstudiantesComentario').val(ref.comentario);
        // Asegurarse de que el input hidden para el tipo de referencia tenga el valor 2 (empresa_a_estudiante)
        $('#empresaEstudiantesTipoReferencia').val(
          ref.tipo_referencia_id_tipo_referencia
        );

        $('#empresaEstudiantesReferenciaModalLabel').text('Editar Referencia');
        $('#empresaEstudiantesSaveReferenceBtn').html(
          '<i class="fas fa-save me-2"></i>Actualizar Referencia'
        );
        $('#empresaEstudiantesReferenciaModal').modal('show');
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar referencia para edición:', {
        xhr,
        status,
        error,
      });
      Swal.fire(
        'Error',
        'No se pudo cargar la referencia para edición. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Elimina (desactiva) una referencia.
 * @param {string} idReferencia - El ID de la referencia a eliminar.
 */
function deleteReference(idReferencia) {
  $.ajax({
    url: '../CONTROLADOR/empresa_estudiantes_ajax.php',
    type: 'POST',
    data: {
      action: 'eliminar_referencia',
      idReferencia: idReferencia,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        Swal.fire('¡Eliminada!', response.message, 'success');
        // Recargar las referencias del estudiante en el perfil
        const currentStudentId = $(
          '#empresaEstudiantesReferenciaEstudianteId'
        ).val();
        // Asegurarse de que EMPRESA_ID_MODULO esté definido antes de usarlo
        if (
          typeof EMPRESA_ID_MODULO !== 'undefined' &&
          EMPRESA_ID_MODULO !== null &&
          EMPRESA_ID_MODULO !== ''
        ) {
          loadStudentReferencesForCompany(currentStudentId, EMPRESA_ID_MODULO);
        } else {
          console.error(
            'EMPRESA_ID_MODULO no está definido, no se pueden recargar las referencias después de eliminar.'
          );
        }
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al eliminar referencia:', {
        xhr,
        status,
        error,
      });
      Swal.fire(
        'Error',
        'No se pudo eliminar la referencia. Intente de nuevo.',
        'error'
      );
    },
  });
}
