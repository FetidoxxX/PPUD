// Variable global para almacenar las ofertas paginadas y el total
let currentPage = 0;
const itemsPerPage = 6;
let totalOffers = 0;

// Variables globales para los datos estáticos de los selectores, inicializadas por PHP
let globalModalidades = [];
let globalTiposOferta = [];
let globalAreasConocimiento = [];
let globalCarrerasDisponibles = [];
let globalEstadosDisponibles = [];

// Variable global para almacenar el elemento que activó el modal, para devolver el foco
let lastFocusedElement = null;

/**
 * Inicializa la lógica de JavaScript de la página de gestión de ofertas.
 * Debe ser llamada después de que el DOM esté listo y las variables PHP estén disponibles.
 * @param {Array} modalidades - Datos de las modalidades.
 * @param {Array} tipos_oferta - Datos de los tipos de oferta.
 * @param {Array} areas_conocimiento - Datos de las áreas de conocimiento.
 * @param {Array} carreras_disponibles - Datos de las carreras disponibles.
 * @param {Array} estados_disponibles - Datos de los estados disponibles.
 */
function initializeGestionOfertas(
  modalidades,
  tipos_oferta,
  areas_conocimiento,
  carreras_disponibles,
  estados_disponibles
) {
  globalModalidades = modalidades;
  globalTiposOferta = tipos_oferta;
  globalAreasConocimiento = areas_conocimiento;
  globalCarrerasDisponibles = carreras_disponibles;
  globalEstadosDisponibles = estados_disponibles;

  cargarOfertas(); // Carga las primeras ofertas al iniciar
  renderCarrerasCheckboxes();
  renderModalOptions(
    globalModalidades,
    'modalidad_id_modalidad',
    'id_modalidad'
  );
  renderModalOptions(
    globalTiposOferta,
    'tipo_oferta_id_tipo_oferta',
    'id_tipo_oferta'
  );
  renderModalOptions(
    globalAreasConocimiento,
    'area_conocimiento_id_area',
    'id_area'
  );
  renderModalOptions(globalEstadosDisponibles, 'estado_id_estado', 'id_estado');

  // Escuchar el evento de cierre del modal para limpiar el formulario
  $('#ofertaModal').on('hidden.bs.modal', function () {
    resetForm();
    // Restaurar el foco al elemento que abrió este modal, si existe
    if (lastFocusedElement) {
      lastFocusedElement.focus();
      lastFocusedElement = null; // Limpiar después de usar
    }
  });

  // Manejar el envío del formulario de oferta
  $('#ofertaForm').submit(function (event) {
    event.preventDefault(); // Evita el envío tradicional del formulario
    guardarOferta();
  });

  // Manejar el evento de búsqueda
  $('#busquedaOfertas').on('keyup', function () {
    cargarOfertas();
  });

  // Capturar el elemento que abre el modal de oferta
  $('[data-bs-target="#ofertaModal"]').on('click', function () {
    lastFocusedElement = this;
  });
}

/**
 * Renderiza las opciones de un select en el modal.
 * @param {Array} data - Array de objetos con 'idKey' y 'nombre'.
 * @param {string} selectId - ID del elemento select HTML.
 * @param {string} idKey - Clave del ID en los objetos de datos.
 */
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

/**
 * Renderiza los checkboxes para seleccionar carreras en el modal.
 */
function renderCarrerasCheckboxes() {
  const container = $('#carrerasDirigidasContainer');
  container.empty();
  globalCarrerasDisponibles.forEach((carrera) => {
    container.append(`
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="carrera_${carrera.id_carrera}" value="${carrera.id_carrera}">
                <label class="form-check-label" for="carrera_${carrera.id_carrera}">${carrera.nombre}</label>
            </div>
        `);
  });
}

/**
 * Obtiene los IDs de las carreras seleccionadas en los checkboxes.
 * @returns {Array} - Array de IDs de carreras.
 */
function getSelectedCarreras() {
  const selectedCarreras = [];
  $('#carrerasDirigidasContainer input[type="checkbox"]:checked').each(
    function () {
      selectedCarreras.push($(this).val());
    }
  );
  return selectedCarreras;
}

/**
 * Marca los checkboxes de carreras según los IDs proporcionados.
 * @param {Array} carreras - Array de IDs de carreras a seleccionar.
 */
function selectCarrerasCheckboxes(carreras) {
  $('#carrerasDirigidasContainer input[type="checkbox"]').prop(
    'checked',
    false
  ); // Desmarcar todo primero
  carreras.forEach((id) => {
    $(`#carrera_${id}`).prop('checked', true);
  });
}

/**
 * Carga las ofertas de la empresa desde el servidor.
 * @param {boolean} append - Si es true, añade ofertas a las existentes; si es false, limpia y carga.
 */
function cargarOfertas(append = false) {
  const busqueda = $('#busquedaOfertas').val();
  const offset = append ? currentPage * itemsPerPage : 0;

  if (!append) {
    $('#ofertasContainer').empty(); // Limpia el contenedor si no estamos añadiendo más
    currentPage = 0; // Reinicia la página actual si es una nueva búsqueda o carga inicial
  }

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'GET',
    data: {
      action: 'obtener_ofertas_empresa',
      busqueda: busqueda,
      limite: itemsPerPage,
      offset: offset,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        totalOffers = response.total; // Actualiza el total de ofertas
        renderOfertasAsCards(response.data);
        currentPage++; // Incrementa la página para la próxima carga
        updateLoadMoreButtonVisibility();
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar ofertas:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudieron cargar las ofertas. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Carga más ofertas al hacer clic en el botón "Cargar más".
 */
function loadMoreOffers() {
  cargarOfertas(true); // Llama a cargarOfertas con append en true
}

/**
 * Actualiza la visibilidad del botón "Cargar más" basado en el número de ofertas cargadas.
 */
function updateLoadMoreButtonVisibility() {
  const loadedOffersCount = $('#ofertasContainer .col').length;
  if (loadedOffersCount >= totalOffers) {
    $('#loadMoreBtn').hide();
  } else {
    $('#loadMoreBtn').show();
  }
}

/**
 * Muestra las ofertas como tarjetas en el contenedor principal.
 * @param {Array} ofertas - Array de objetos de oferta.
 */
function renderOfertasAsCards(ofertas) {
  const container = $('#ofertasContainer');
  if (ofertas.length === 0 && container.children().length === 0) {
    container.append(
      '<div class="col-12 text-center py-4"><p class="lead text-muted">No hay ofertas publicadas por tu empresa.</p></div>'
    );
    $('#loadMoreBtn').hide();
    return;
  }

  ofertas.forEach((oferta) => {
    const isVencida = oferta.estado_nombre === 'vencida';
    // Clases para deshabilitar o cambiar estilo
    const editButtonClass = isVencida ? 'btn-secondary' : 'btn-warning';
    const deactivateButtonClass = isVencida ? 'btn-secondary' : 'btn-danger';
    const editDisabled = isVencida ? 'disabled' : '';
    const deactivateDisabled = isVencida ? 'disabled' : '';

    // Condicional para el botón "Interesados"
    const viewInterestedButtonHtml =
      oferta.total_interesados > 0
        ? `<button class="btn btn-sm btn-primary rounded-pill mt-2" onclick="viewInterestedStudents(${oferta.idOferta}, this)">
          <i class="fas fa-users me-1"></i> Interesados (${oferta.total_interesados})
       </button>`
        : `<button class="btn btn-sm btn-outline-secondary rounded-pill mt-2" disabled>
          <i class="fas fa-users me-1"></i> Sin Interesados
       </button>`;

    const cardHtml = `
            <div class="col">
                <div class="card h-100 shadow-lg border-0 rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-primary fw-bold mb-0">${
                                  oferta.titulo
                                }</h5>
                                <h6 class="card-subtitle text-muted">${
                                  oferta.tipo_oferta_nombre
                                } - ${oferta.modalidad_nombre}</h6>
                            </div>
                            <div class="d-flex flex-shrink-0">
                                <button class="btn btn-sm btn-info rounded-circle me-2" onclick="viewOffer(${
                                  oferta.idOferta
                                })" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm ${editButtonClass} rounded-circle me-2" onclick="editarOferta(${
      oferta.idOferta
    })" title="Editar" ${editDisabled}>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm ${deactivateButtonClass} rounded-circle" onclick="confirmarDesactivarOferta(${
      oferta.idOferta
    })" title="Desactivar" ${deactivateDisabled}>
                                    <i class="fas fa-archive"></i>
                                </button>
                            </div>
                        </div>
                        <p class="card-text text-muted">${oferta.descripcion.substring(
                          0,
                          100
                        )}${oferta.descripcion.length > 100 ? '...' : ''}</p>
                        <ul class="list-unstyled mb-0 mt-3">
                            <li><strong><i class="fas fa-tags me-1 text-info"></i> Área:</strong> ${
                              oferta.area_conocimiento_nombre
                            }</li>
                            <li><strong><i class="fas fa-calendar-alt me-1 text-info"></i> Vencimiento:</strong> ${
                              oferta.fecha_vencimiento
                            }</li>
                            <li><strong><i class="fas fa-hourglass-half me-1 text-info"></i> Duración:</strong> ${
                              oferta.duracion_meses
                            } meses</li>
                            <li><strong><i class="fas fa-users me-1 text-info"></i> Cupos:</strong> ${
                              oferta.cupos_disponibles
                            }</li>
                        </ul>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center bg-light border-top">
                        <span class="badge ${
                          oferta.estado_nombre === 'activo'
                            ? 'bg-success'
                            : oferta.estado_nombre === 'vencida'
                            ? 'bg-warning text-dark'
                            : 'bg-secondary'
                        }">
                            ${oferta.estado_nombre.toUpperCase()}
                        </span>
                        <small class="text-muted">ID: ${oferta.idOferta}</small>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pt-0 d-flex justify-content-center">
                      ${viewInterestedButtonHtml}
                    </div>
                </div>
            </div>
        `;
    container.append(cardHtml);
  });
  updateLoadMoreButtonVisibility();
}

/**
 * Resetea el formulario de oferta y lo prepara para crear una nueva.
 */
function resetFormAndOpenModal() {
  resetForm();
  $('#ofertaModalLabel').text('Crear Nueva Oferta');
  $('#ofertaId').val(''); // Asegurarse de que el ID esté vacío para una nueva creación
  // Establecer la fecha de vencimiento por defecto a 1 mes desde hoy
  const today = new Date();
  today.setMonth(today.getMonth() + 1);
  const defaultExpiryDate = today.toISOString().split('T')[0];
  $('#fecha_vencimiento').val(defaultExpiryDate);

  // Habilitar todos los campos para la creación
  $('#ofertaForm input, #ofertaForm select, #ofertaForm textarea').prop(
    'disabled',
    false
  );
  $('#ofertaForm button[type="submit"]').show(); // Mostrar el botón de guardar
  $('#estado_id_estado').val('1').prop('disabled', true); // Estado activo por defecto y no editable al crear
  $('#ofertaModal').modal('show');
}

/**
 * Resetea completamente el formulario de oferta.
 */
function resetForm() {
  $('#ofertaForm')[0].reset();
  $('#ofertaId').val(''); // Limpiar ID oculto
  $('#estado_id_estado').val('').prop('disabled', false); // Habilitar y limpiar estado (para edición si aplica)
  $('#carrerasDirigidasContainer input[type="checkbox"]').prop(
    'checked',
    false
  ); // Desmarcar todas las carreras
}

/**
 * Abre el modal de oferta en modo de solo lectura.
 * @param {number} idOferta - ID de la oferta a visualizar.
 */
function viewOffer(idOferta) {
  // Guardar el elemento que actualmente tiene el foco antes de abrir un nuevo modal
  lastFocusedElement = document.activeElement;

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'GET',
    data: {
      action: 'obtener_oferta_por_id',
      id: idOferta,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.data) {
        const oferta = response.data;
        $('#ofertaModalLabel').text('Ver Oferta'); // Cambiar título del modal

        // Llenar campos
        $('#ofertaId').val(oferta.idOferta);
        $('#titulo').val(oferta.titulo);
        $('#descripcion').val(oferta.descripcion);
        $('#requisitos').val(oferta.requisitos);
        $('#beneficios').val(oferta.beneficios);
        $('#modalidad_id_modalidad').val(oferta.modalidad_id_modalidad);
        $('#tipo_oferta_id_tipo_oferta').val(oferta.tipo_oferta_id_tipo_oferta);
        $('#duracion_meses').val(oferta.duracion_meses);
        $('#horario').val(oferta.horario);
        $('#remuneracion').val(oferta.remuneracion);
        $('#area_conocimiento_id_area').val(oferta.area_conocimiento_id_area);
        $('#semestre_minimo').val(oferta.semestre_minimo);
        $('#promedio_minimo').val(oferta.promedio_minimo);
        $('#habilidades_requeridas').val(oferta.habilidades_requeridas);
        $('#fecha_inicio').val(oferta.fecha_inicio);
        $('#fecha_fin').val(oferta.fecha_fin);
        $('#fecha_vencimiento').val(oferta.fecha_vencimiento);
        $('#cupos_disponibles').val(oferta.cupos_disponibles);
        $('#estado_id_estado').val(oferta.estado_id_estado);
        selectCarrerasCheckboxes(oferta.carreras_asociadas);

        // Deshabilitar todos los campos del formulario para solo lectura
        $('#ofertaForm input, #ofertaForm select, #ofertaForm textarea').prop(
          'disabled',
          true
        );
        $('#ofertaForm button[type="submit"]').hide(); // Ocultar el botón de guardar

        $('#ofertaModal').modal('show');
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener oferta para ver:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar la oferta para ver. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Carga los datos de una oferta en el modal para edición.
 * Si la oferta está vencida, la abre en modo de solo lectura.
 * @param {number} idOferta - ID de la oferta a editar.
 */
function editarOferta(idOferta) {
  // Guardar el elemento que actualmente tiene el foco antes de abrir un nuevo modal
  lastFocusedElement = document.activeElement;

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'GET',
    data: {
      action: 'obtener_oferta_por_id',
      id: idOferta,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.data) {
        const oferta = response.data;

        // Si la oferta está vencida, redirigir a viewOffer en lugar de editar
        if (oferta.estado_nombre === 'vencida') {
          Swal.fire(
            'Información',
            'Esta oferta está vencida y no puede ser editada. Se mostrará en modo de solo lectura.',
            'info'
          );
          viewOffer(idOferta); // Llamar a la función de ver
          return; // Sale de la función sin abrir el modal de edición
        }

        $('#ofertaModalLabel').text('Editar Oferta');
        $('#ofertaId').val(oferta.idOferta);
        $('#titulo').val(oferta.titulo);
        $('#descripcion').val(oferta.descripcion);
        $('#requisitos').val(oferta.requisitos);
        $('#beneficios').val(oferta.beneficios);
        $('#modalidad_id_modalidad').val(oferta.modalidad_id_modalidad);
        $('#tipo_oferta_id_tipo_oferta').val(oferta.tipo_oferta_id_tipo_oferta);
        $('#duracion_meses').val(oferta.duracion_meses);
        $('#horario').val(oferta.horario);
        $('#remuneracion').val(oferta.remuneracion);
        $('#area_conocimiento_id_area').val(oferta.area_conocimiento_id_area);
        $('#semestre_minimo').val(oferta.semestre_minimo);
        $('#promedio_minimo').val(oferta.promedio_minimo);
        $('#habilidades_requeridas').val(oferta.habilidades_requeridas);
        $('#fecha_inicio').val(oferta.fecha_inicio);
        $('#fecha_fin').val(oferta.fecha_fin);
        $('#fecha_vencimiento').val(oferta.fecha_vencimiento);
        $('#cupos_disponibles').val(oferta.cupos_disponibles);

        // Habilitar todos los campos para edición
        $('#ofertaForm input, #ofertaForm select, #ofertaForm textarea').prop(
          'disabled',
          false
        );
        $('#ofertaForm button[type="submit"]').show(); // Mostrar el botón de guardar

        $('#estado_id_estado')
          .val(oferta.estado_id_estado)
          .prop('disabled', false); // Habilitar edición de estado
        selectCarrerasCheckboxes(oferta.carreras_asociadas); // Seleccionar carreras asociadas

        $('#ofertaModal').modal('show');
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener oferta para edición:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar la oferta para edición. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Valida los campos del formulario de oferta.
 * @returns {boolean} - True si el formulario es válido, false en caso contrario.
 */
function validateOfertaForm() {
  const titulo = $('#titulo').val().trim();
  const descripcion = $('#descripcion').val().trim();
  const modalidad = $('#modalidad_id_modalidad').val();
  const tipoOferta = $('#tipo_oferta_id_tipo_oferta').val();
  const areaConocimiento = $('#area_conocimiento_id_area').val();
  const fechaVencimiento = $('#fecha_vencimiento').val().trim();
  const cuposDisponibles = $('#cupos_disponibles').val().trim();
  const selectedCarreras = getSelectedCarreras();

  if (!titulo) {
    Swal.fire(
      'Error de Validación',
      'El título de la oferta es obligatorio.',
      'error'
    );
    return false;
  }
  if (!descripcion) {
    Swal.fire(
      'Error de Validación',
      'La descripción de la oferta es obligatoria.',
      'error'
    );
    return false;
  }
  if (!modalidad) {
    Swal.fire('Error de Validación', 'La modalidad es obligatoria.', 'error');
    return false;
  }
  if (!tipoOferta) {
    Swal.fire(
      'Error de Validación',
      'El tipo de oferta es obligatorio.',
      'error'
    );
    return false;
  }
  if (!areaConocimiento) {
    Swal.fire(
      'Error de Validación',
      'El área de conocimiento es obligatoria.',
      'error'
    );
    return false;
  }
  if (!fechaVencimiento) {
    Swal.fire(
      'Error de Validación',
      'La fecha de vencimiento es obligatoria.',
      'error'
    );
    return false;
  }
  if (isNaN(cuposDisponibles) || parseInt(cuposDisponibles) <= 0) {
    Swal.fire(
      'Error de Validación',
      'Los cupos disponibles deben ser un número positivo.',
      'error'
    );
    return false;
  }
  if (selectedCarreras.length === 0) {
    Swal.fire(
      'Error de Validación',
      'Debe seleccionar al menos una carrera dirigida.',
      'error'
    );
    return false;
  }

  return true;
}

/**
 * Guarda (crea o actualiza) una oferta enviando los datos al servidor.
 */
function guardarOferta() {
  // Realizar validación antes de enviar
  if (!validateOfertaForm()) {
    return; // Detener la ejecución si la validación falla
  }

  const idOferta = $('#ofertaId').val();
  const action = idOferta ? 'actualizar_oferta' : 'crear_oferta';
  const selectedCarreras = getSelectedCarreras();

  const formData = new FormData($('#ofertaForm')[0]);
  formData.append('action', action);
  if (selectedCarreras.length > 0) {
    formData.append('carreras_dirigidas', JSON.stringify(selectedCarreras));
  } else {
    formData.append('carreras_dirigidas', '[]'); // Enviar un array vacío si no hay carreras seleccionadas
  }

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'POST',
    data: formData,
    processData: false, // Necesario para FormData
    contentType: false, // Necesario para FormData
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        Swal.fire('Éxito', response.message, 'success');
        $('#ofertaModal').modal('hide');
        // Recargar todas las ofertas desde el principio después de guardar/editar
        currentPage = 0; // Reiniciar la paginación al principio
        $('#ofertasContainer').empty(); // Limpiar el contenedor existente
        cargarOfertas(true); // Cargar las ofertas como una nueva tanda
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al guardar oferta:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo guardar la oferta. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Muestra una confirmación antes de desactivar una oferta.
 * @param {number} idOferta - ID de la oferta a desactivar.
 */
function confirmarDesactivarOferta(idOferta) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: '¡La oferta se desactivará y dejará de ser visible, pero no se eliminará permanentemente!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      desactivarOferta(idOferta);
    }
  });
}

/**
 * Desactiva una oferta enviando la solicitud al servidor.
 * @param {number} idOferta - ID de la oferta a desactivar.
 */
function desactivarOferta(idOferta) {
  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'POST',
    data: {
      action: 'desactivar_oferta',
      idOferta: idOferta,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        Swal.fire('Desactivada!', response.message, 'success');
        // Recargar todas las ofertas desde el principio después de desactivar
        currentPage = 0; // Reiniciar la paginación al principio
        $('#ofertasContainer').empty(); // Limpiar el contenedor existente
        cargarOfertas(true); // Cargar las ofertas como una nueva tanda
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al desactivar oferta:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo desactivar la oferta. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Muestra una alerta con el perfil de la empresa.
 */
function mostrarPerfilEmpresa() {
  Swal.fire({
    title: 'Perfil de Empresa',
    html: `
            <div class="text-start">
                <div class="mb-2"><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION["usuario"]); ?></div>
                <div class="mb-2"><strong>ID de Empresa:</strong> <?php echo htmlspecialchars($_SESSION["usuario_id"]); ?></div>
                <div class="mb-2"><strong>Tipo de Cuenta:</strong> <span class="badge bg-primary">Empresa</span></div>
                <div class="mb-2"><strong>Sesión iniciada:</strong> <?php echo date("d/m/Y H:i:s"); ?></div>
                <div class="mb-2"><strong>Estado:</strong> <span class="badge bg-success">Activo</span></div>
            </div>
        `,
    icon: 'info',
    confirmButtonText: 'Cerrar',
    confirmButtonColor: '#0d6efd',
  });
}

/**
 * Muestra el modal con el listado de estudiantes interesados en una oferta.
 * La lista de estudiantes es generada por PHP y recibida como HTML.
 * @param {number} idOferta - El ID de la oferta para la que se mostrarán los interesados.
 * @param {HTMLElement} triggeringElement - El elemento que activó este modal.
 */
function viewInterestedStudents(idOferta, triggeringElement) {
  // Guardar el elemento que activó este modal para devolver el foco al cerrarlo
  lastFocusedElement = triggeringElement;

  // Cerrar el modal de oferta si está abierto para evitar superposición
  const ofertaModal = bootstrap.Modal.getInstance(
    document.getElementById('ofertaModal')
  );
  if (ofertaModal) {
    ofertaModal.hide();
    // Añadir un listener one-time para asegurar que el foco se maneje después de que el modal se oculte completamente
    $('#ofertaModal').one('hidden.bs.modal', function () {
      showInterestedStudentsModal(idOferta);
    });
  } else {
    showInterestedStudentsModal(idOferta);
  }
}

/**
 * Función auxiliar para mostrar el modal de estudiantes interesados.
 * @param {number} idOferta - El ID de la oferta.
 */
function showInterestedStudentsModal(idOferta) {
  const interesadosList = $('#interesadosList');
  interesadosList.html(
    '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Cargando interesados...</div>'
  );
  $('#interesadosOfertaId').text(idOferta); // Mostrar el ID de la oferta

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'GET',
    data: {
      action: 'render_interesados_list_html',
      idOferta: idOferta,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.html) {
        interesadosList.html(response.html);
        const interesadosModalElement =
          document.getElementById('interesadosModal');
        new bootstrap.Modal(interesadosModalElement).show();

        // Al mostrar el modal, asegúrate de que el foco esté dentro de él.
        // Por ejemplo, en el botón de cerrar.
        $('#interesadosModal .btn-close').focus();
      } else {
        interesadosList.html(
          '<li class="list-group-item text-center text-danger">Error al cargar el listado: ' +
            response.message +
            '</li>'
        );
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener estudiantes interesados:', error);
      interesadosList.html(
        '<li class="list-group-item text-center text-danger">Error de conexión al cargar interesados.</li>'
      );
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar el listado de interesados. Intente de nuevo.',
        'error'
      );
    },
  });

  // Manejar el foco al cerrar el modal de interesados
  $('#interesadosModal').one('hidden.bs.modal', function () {
    if (lastFocusedElement) {
      lastFocusedElement.focus();
      lastFocusedElement = null;
    }
  });
}

/**
 * Muestra el perfil completo de un estudiante en un modal.
 * El contenido del perfil es generado por PHP y recibido como HTML.
 * @param {string} idEstudiante - El ID del estudiante cuyo perfil se mostrará.
 * @param {HTMLElement} triggeringElement - El elemento que activó este modal.
 */
function viewStudentProfileForCompany(idEstudiante, triggeringElement) {
  // Guardar el elemento que activó este modal para devolver el foco al cerrarlo
  lastFocusedElement = triggeringElement;

  // Cerrar el modal de interesados si está abierto para evitar superposición
  const interesadosModal = bootstrap.Modal.getInstance(
    document.getElementById('interesadosModal')
  );
  if (interesadosModal) {
    interesadosModal.hide();
    // Añadir un listener one-time para asegurar que el foco se maneje después de que el modal se oculte completamente
    $('#interesadosModal').one('hidden.bs.modal', function () {
      showStudentProfileModal(idEstudiante);
    });
  } else {
    showStudentProfileModal(idEstudiante);
  }
}

/**
 * Función auxiliar para mostrar el modal de perfil del estudiante.
 * @param {string} idEstudiante - El ID del estudiante.
 */
function showStudentProfileModal(idEstudiante) {
  console.log('Abriendo perfil de estudiante para ID:', idEstudiante);

  const perfilEstudianteContent = $('#perfilEstudianteContent');
  const referenciasListContainer = $('#referenciasListContainer');
  perfilEstudianteContent.html(
    '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x me-2"></i><br>Cargando perfil del estudiante...</div>'
  );
  referenciasListContainer.html(
    '<div class="text-center py-3 text-muted">Cargando referencias...</div>'
  );

  // CRÍTICO: Establece el ID del estudiante en el atributo data-student-id del modal principal.
  // Esto permite que funciones como loadAndDisplayReferences accedan al ID del estudiante.
  $('#perfilEstudianteModal').data('student-id', idEstudiante);

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php', // Se sigue usando ./CONTROLADOR/ajax_Mempresa.php para obtener el HTML completo del perfil
    type: 'GET',
    data: {
      action: 'render_perfil_estudiante_html',
      id: idEstudiante,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.html) {
        const studentData = response.data;
        if (studentData) {
          // Actualiza el título del modal con el nombre del estudiante
          $('#perfilEstudianteModalLabel').text(
            'Perfil del Estudiante - ' +
              studentData.nombre +
              ' ' +
              studentData.apellidos
          );
          // Los atributos onclick del botón de crear referencia ya no son necesarios si usas delegación
          // $('#btnCrearReferenciaEstudiante').attr(
          //   'onclick',
          //   `openCreateReferenceModal('${idEstudiante}', '${studentData.nombre} ${studentData.apellidos}', this)`
          // );
        } else {
          const studentNameMatch = response.html.match(
            /Nombre:<\/strong>\s*([^<]+)/
          );
          const studentName = studentNameMatch
            ? studentNameMatch[1].trim()
            : 'Estudiante Desconocido';
          $('#perfilEstudianteModalLabel').text(
            'Perfil del Estudiante - ' + studentName
          );
          // Los atributos onclick del botón de crear referencia ya no son necesarios si usas delegación
          // $('#btnCrearReferenciaEstudiante').attr(
          //   'onclick',
          //   `openCreateReferenceModal('${idEstudiante}', '${studentName}', this)`
          // );
        }

        perfilEstudianteContent.html(response.html);
        // CRÍTICO: Cargar y mostrar las referencias para este estudiante.
        // Se llama a esta función *después* de establecer el data-student-id en el modal.
        // Asegúrate de pasar el nombre completo si es posible para el botón de crear referencia.
        loadAndDisplayReferences(
          idEstudiante,
          studentData
            ? studentData.nombre + ' ' + studentData.apellidos
            : undefined
        );

        const perfilEstudianteModalElement = document.getElementById(
          'perfilEstudianteModal'
        );
        new bootstrap.Modal(perfilEstudianteModalElement).show();

        // Al mostrar el modal, asegúrate de que el foco esté dentro de él.
        // Por ejemplo, en el botón de cerrar.
        $('#perfilEstudianteModal .btn-close').focus();
      } else {
        perfilEstudianteContent.html(
          '<div class="text-center py-5 text-danger">Error al cargar el perfil: ' +
            response.message +
            '</div>'
        );
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener perfil del estudiante:', error);
      perfilEstudianteContent.html(
        '<div class="text-center py-5 text-danger">Error de conexión al cargar el perfil del estudiante.</div>'
      );
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar el perfil del estudiante. Intente de nuevo.',
        'error'
      );
    },
  });

  // Manejar el foco al cerrar el modal de perfil de estudiante
  $('#perfilEstudianteModal').one('hidden.bs.modal', function () {
    if (lastFocusedElement) {
      lastFocusedElement.focus();
      lastFocusedElement = null;
    }
  });
}
