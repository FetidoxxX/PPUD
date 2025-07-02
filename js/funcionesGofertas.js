// Variable global para almacenar las ofertas paginadas y el total
let currentPage = 0;
const itemsPerPage = 10; // Ajustable según necesidad
let totalOffers = 0;
let busquedaActual = '';

// Variables globales para los datos estáticos de los selectores, inicializadas por PHP
let globalModalidades = [];
let globalTiposOferta = [];
let globalAreasConocimiento = [];
let globalCarrerasDisponibles = [];
let globalEstadosDisponibles = [];

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

  cargarOfertas(); // Cargar ofertas al iniciar la página

  // Búsqueda en tiempo real con debounce
  let timeoutBusqueda;
  $('#busquedaInput').on('input', function (e) {
    const valor = e.target.value.trim();
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => {
      busquedaActual = valor; // Actualizar la búsqueda actual
      currentPage = 0; // Resetear a la primera página en cada nueva búsqueda
      cargarOfertas(busquedaActual, currentPage * itemsPerPage);
    }, 300);
  });

  // Manejar el envío del formulario de oferta
  $('#ofertaForm').submit(function (event) {
    event.preventDefault();
    saveOferta();
  });

  // Limpiar formulario y reestablecer título del modal al cerrar
  $('#ofertaModal').on('hidden.bs.modal', function () {
    resetOfertaForm();
  });
}

/**
 * Carga las ofertas desde el servidor y las muestra en la tabla.
 * @param {string} busqueda - Término de búsqueda (opcional).
 * @param {number} offset - Desplazamiento para la paginación.
 */
function cargarOfertas(busqueda = '', offset = 0) {
  // Ruta actualizada para el controlador: usamos ../ para subir desde 'js/' y luego entrar a 'CONTROLADOR/'
  const url = `../CONTROLADOR/ajax_Gofertas.php?action=listar&busqueda=${encodeURIComponent(
    busqueda
  )}&limit=${itemsPerPage}&offset=${offset}`;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        $('#tablaOfertas').html(response.html);
        totalOffers = response.total;
        $('#totalOfertas').text(totalOffers);
        // Modificación aquí: Eliminar el conteo numérico del texto de la estadística
        $('#textoEstadistica').text(
          busqueda ? 'Resultados encontrados' : 'Total de ofertas'
        );
        renderPagination(totalOffers, offset);
      } else {
        mostrarError(response.message);
        $('#tablaOfertas').html(
          '<tr><td colspan="8" class="text-center text-muted">Error al cargar las ofertas.</td></tr>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar ofertas:', error);
      mostrarError('Error de conexión al cargar ofertas.');
      $('#tablaOfertas').html(
        '<tr><td colspan="8" class="text-center text-danger">Error de conexión al cargar ofertas.</td></tr>'
      );
    },
  });
}

/**
 * Renderiza los controles de paginación.
 * @param {number} totalItems - Número total de elementos.
 * @param {number} currentOffset - Desplazamiento actual.
 */
function renderPagination(totalItems, currentOffset) {
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  currentPage = Math.floor(currentOffset / itemsPerPage);
  const paginationControls = $('#paginationControls');
  paginationControls.empty();

  if (totalPages <= 1) {
    return; // No mostrar paginación si solo hay una página o ninguna
  }

  // Botón "Anterior"
  paginationControls.append(`
    <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePage(${
        currentPage - 1
      })">Anterior</a>
    </li>
  `);

  // Números de página
  let startPage = Math.max(0, currentPage - 2);
  let endPage = Math.min(totalPages - 1, currentPage + 2);

  if (startPage > 0) {
    paginationControls.append(
      '<li class="page-item disabled"><span class="page-link">...</span></li>'
    );
  }

  for (let i = startPage; i <= endPage; i++) {
    paginationControls.append(`
      <li class="page-item ${i === currentPage ? 'active' : ''}">
        <a class="page-link" href="#" onclick="changePage(${i})">${i + 1}</a>
      </li>
    `);
  }

  if (endPage < totalPages - 1) {
    paginationControls.append(
      '<li class="page-item disabled"><span class="page-link">...</span></li>'
    );
  }

  // Botón "Siguiente"
  paginationControls.append(`
    <li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePage(${
        currentPage + 1
      })">Siguiente</a>
    </li>
  `);
}

/**
 * Cambia la página de la tabla de ofertas.
 * @param {number} page - El número de página (base 0).
 */
function changePage(page) {
  const totalPages = Math.ceil(totalOffers / itemsPerPage);
  if (page >= 0 && page < totalPages) {
    currentPage = page;
    cargarOfertas(busquedaActual, currentPage * itemsPerPage);
  }
}

/**
 * Limpia el campo de búsqueda y recarga las ofertas.
 */
function limpiarBusqueda() {
  $('#busquedaInput').val('');
  busquedaActual = '';
  currentPage = 0;
  cargarOfertas();
}

/**
 * Abre el modal para crear una nueva oferta.
 */
function crearOferta() {
  resetOfertaForm();
  $('#ofertaModalLabel').text('Crear Nueva Oferta');
  $('#btnGuardarOferta')
    .text('Guardar Oferta')
    .removeClass('btn-warning')
    .addClass('btn-primary');

  // Limpiar y cargar las opciones de los selectores para creación
  renderSelectOptions(
    globalModalidades,
    'modalidad_id_modalidad',
    'id_modalidad'
  );
  renderSelectOptions(
    globalTiposOferta,
    'tipo_oferta_id_tipo_oferta',
    'id_tipo_oferta'
  );
  renderSelectOptions(
    globalAreasConocimiento,
    'area_conocimiento_id_area',
    'id_area'
  );
  renderSelectOptions(
    globalEstadosDisponibles,
    'estado_id_estado',
    'id_estado'
  );

  // Asegurarse de que el estado por defecto sea "Activo" (asumiendo que ID 1 es activo)
  const estadoActivo = globalEstadosDisponibles.find(
    (estado) => estado.nombre.toLowerCase() === 'activo'
  );
  if (estadoActivo) {
    $('#estado_id_estado').val(estadoActivo.id_estado);
  }

  renderCarrerasCheckboxes(
    globalCarrerasDisponibles,
    [],
    'carrerasDirigidasContainer'
  );
  $('#ofertaModal').modal('show');
}

/**
 * Abre el modal para editar una oferta existente.
 * @param {number} idOferta - El ID de la oferta a editar.
 */
function editarOferta(idOferta) {
  resetOfertaForm(); // Limpiar el formulario primero
  $('#ofertaModalLabel').text('Editar Oferta');
  $('#btnGuardarOferta')
    .text('Actualizar Oferta')
    .removeClass('btn-primary')
    .addClass('btn-warning');
  $('#ofertaId').val(idOferta); // Establecer el ID de la oferta en el campo oculto

  $.ajax({
    url: '../CONTROLADOR/ajax_Gofertas.php', // Ruta actualizada para el controlador
    type: 'GET',
    data: { action: 'obtener', id: idOferta },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        const oferta = response.oferta;
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
        $('#promedio_minimo').val(oferta.promedio_promedio); // Corregido: asumí un typo, si es diferente ajusta aquí
        $('#cupos_disponibles').val(oferta.cupos_disponibles);
        $('#habilidades_requeridas').val(oferta.habilidades_requeridas);
        $('#fecha_inicio').val(oferta.fecha_inicio);
        $('#fecha_fin').val(oferta.fecha_fin);
        $('#fecha_vencimiento').val(oferta.fecha_vencimiento);
        $('#estado_id_estado').val(oferta.estado_id_estado);
        $('#empresa_idEmpresa').val(oferta.empresa_idEmpresa); // Rellenar ID de empresa

        // Cargar opciones de selectores y seleccionar valor actual
        renderSelectOptions(
          globalModalidades,
          'modalidad_id_modalidad',
          'id_modalidad',
          oferta.modalidad_id_modalidad
        );
        renderSelectOptions(
          globalTiposOferta,
          'tipo_oferta_id_tipo_oferta',
          'id_tipo_oferta',
          oferta.tipo_oferta_id_tipo_oferta
        );
        renderSelectOptions(
          globalAreasConocimiento,
          'area_conocimiento_id_area',
          'id_area',
          oferta.area_conocimiento_id_area
        );
        renderSelectOptions(
          globalEstadosDisponibles,
          'estado_id_estado',
          'id_estado',
          oferta.estado_id_estado
        );

        // Cargar carreras dirigidas
        renderCarrerasCheckboxes(
          globalCarrerasDisponibles,
          oferta.carreras_dirigidas,
          'carrerasDirigidasContainer'
        );

        $('#ofertaModal').modal('show');
      } else {
        mostrarError(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener oferta para editar:', error);
      mostrarError('Error de conexión al obtener oferta para editar.');
    },
  });
}

/**
 * Resetea el formulario del modal de ofertas.
 */
function resetOfertaForm() {
  $('#ofertaForm')[0].reset();
  $('#ofertaId').val(''); // Limpiar ID de oferta
  $('#ofertaModalLabel').text('Crear Nueva Oferta');
  $('#btnGuardarOferta')
    .text('Guardar Oferta')
    .removeClass('btn-warning')
    .addClass('btn-primary');
  $('#carrerasDirigidasContainer').empty(); // Limpiar checkboxes de carreras
}

/**
 * Guarda o actualiza una oferta.
 */
function saveOferta() {
  const idOferta = $('#ofertaId').val();
  const action = idOferta ? 'actualizar' : 'crear';

  const formData = new FormData($('#ofertaForm')[0]);
  formData.append('action', action);

  // Asegurarse de que el ID de la oferta se envíe si la acción es 'actualizar'
  if (action === 'actualizar') {
    formData.append('id', idOferta); // Añadir el ID explícitamente para actualización
  }

  // Validaciones del lado del cliente
  const titulo = $('#titulo').val().trim();
  const descripcion = $('#descripcion').val().trim();
  const requisitos = $('#requisitos').val().trim();
  const modalidad = $('#modalidad_id_modalidad').val();
  const tipoOferta = $('#tipo_oferta_id_tipo_oferta').val();
  const duracionMeses = $('#duracion_meses').val();
  const areaConocimiento = $('#area_conocimiento_id_area').val();
  const fechaVencimiento = $('#fecha_vencimiento').val();
  const cuposDisponibles = $('#cupos_disponibles').val();
  const estado = $('#estado_id_estado').val();
  const empresaId = $('#empresa_idEmpresa').val(); // Obtener ID de la empresa

  if (
    !titulo ||
    !descripcion ||
    !requisitos ||
    !modalidad ||
    !tipoOferta ||
    !duracionMeses ||
    !areaConocimiento ||
    !fechaVencimiento ||
    !cuposDisponibles ||
    !estado ||
    !empresaId
  ) {
    mostrarError('Por favor, complete todos los campos obligatorios (*).');
    return;
  }

  if (duracionMeses <= 0) {
    mostrarError('La duración en meses debe ser un número positivo.');
    return;
  }

  if (cuposDisponibles <= 0) {
    mostrarError('Los cupos disponibles deben ser un número positivo.');
    return;
  }

  // Validación de fechas
  const fechaInicio = $('#fecha_inicio').val();
  const fechaFin = $('#fecha_fin').val();

  if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
    mostrarError(
      'La fecha de inicio no puede ser posterior a la fecha de fin.'
    );
    return;
  }

  // Validar que la fecha de vencimiento no sea anterior a la fecha actual solo en creación
  if (
    action === 'crear' &&
    fechaVencimiento &&
    new Date(fechaVencimiento) < new Date()
  ) {
    mostrarError('La fecha de vencimiento no puede ser en el pasado.');
    return;
  }

  // Recopilar carreras dirigidas
  const carrerasDirigidas = [];
  $('#carrerasDirigidasContainer input[type="checkbox"]:checked').each(
    function () {
      carrerasDirigidas.push($(this).val());
    }
  );
  formData.append('carreras_dirigidas', JSON.stringify(carrerasDirigidas));

  $.ajax({
    url: '../CONTROLADOR/ajax_Gofertas.php', // Ruta actualizada para el controlador
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        mostrarExito(response.message);
        $('#ofertaModal').modal('hide');
        cargarOfertas(busquedaActual, currentPage * itemsPerPage); // Recargar la tabla
      } else {
        mostrarError(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al guardar oferta:', error);
      mostrarError('Error de conexión al guardar oferta.');
    },
  });
}

/**
 * Desactiva una oferta (cambia su estado a inactivo).
 * @param {number} idOferta - El ID de la oferta a desactivar.
 */
function desactivarOferta(idOferta) {
  Swal.fire({
    title: '¿Está seguro de desactivar esta oferta?',
    text: 'La oferta cambiará a estado "Inactiva" y ya no será visible para los estudiantes.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('action', 'desactivar');
      formData.append('id', idOferta);

      $.ajax({
        url: '../CONTROLADOR/ajax_Gofertas.php', // Ruta actualizada para el controlador
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            mostrarExito(response.message);
            cargarOfertas(busquedaActual, currentPage * itemsPerPage); // Recargar la tabla
          } else {
            mostrarError(response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('Error al desactivar oferta:', error);
          mostrarError('Error de conexión al desactivar oferta.');
        },
      });
    }
  });
}

/**
 * Muestra el detalle de una oferta en un modal de Bootstrap.
 * @param {number} idOferta - El ID de la oferta.
 */
function verDetalleOferta(idOferta) {
  $.ajax({
    url: '../CONTROLADOR/ajax_Gofertas.php', // Ruta actualizada para el controlador
    type: 'GET',
    data: { action: 'detalle_html', id: idOferta }, // Llamar a la nueva acción que retorna HTML
    dataType: 'json', // Esperar JSON que contenga el HTML
    success: function (response) {
      if (response.success && response.html) {
        $('#contenidoDetalleOferta').html(response.html); // Inyectar el HTML en el modal
        new bootstrap.Modal(
          document.getElementById('modalDetalleOferta')
        ).show(); // Mostrar el modal de Bootstrap
      } else {
        mostrarError(
          response.message || 'No se pudo cargar el detalle de la oferta.'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener detalle de oferta:', error);
      mostrarError('Error de conexión al obtener detalle de oferta.');
    },
  });
}

/**
 * Funciones de utilidad para mostrar mensajes SweetAlert2.
 */
function mostrarExito(mensaje) {
  Swal.fire({
    icon: 'success',
    title: 'Éxito',
    text: mensaje,
    timer: 2000,
    showConfirmButton: false,
  });
}

function mostrarError(mensaje) {
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: mensaje,
  });
}

/**
 * Renderiza las opciones para un selector (select).
 * @param {Array} data - El array de datos (ej. modalidades, tipos_oferta).
 * @param {string} selectId - El ID del elemento select.
 * @param {string} idKey - La clave que representa el ID en cada objeto de datos.
 * @param {*} selectedValue - El valor que debe ser pre-seleccionado (opcional).
 */
function renderSelectOptions(data, selectId, idKey, selectedValue = '') {
  const selectElement = $(`#${selectId}`);
  selectElement.empty();
  selectElement.append('<option value="">Seleccione...</option>'); // Opción por defecto
  data.forEach((item) => {
    const isSelected = item[idKey] == selectedValue ? 'selected' : '';
    selectElement.append(
      `<option value="${item[idKey]}" ${isSelected}>${item.nombre}</option>`
    );
  });
}

/**
 * Renderiza los checkboxes para las carreras dirigidas.
 * @param {Array} allCarreras - Todas las carreras disponibles.
 * @param {Array} selectedCarrerasIds - IDs de las carreras ya asociadas a la oferta.
 * @param {string} containerId - El ID del contenedor donde se renderizarán los checkboxes.
 */
function renderCarrerasCheckboxes(
  allCarreras,
  selectedCarrerasIds,
  containerId
) {
  const container = $(`#${containerId}`);
  container.empty();
  if (allCarreras.length === 0) {
    container.append('<p class="text-muted">No hay carreras disponibles.</p>');
    return;
  }

  allCarreras.forEach((carrera) => {
    const isChecked = selectedCarrerasIds.includes(carrera.id_carrera)
      ? 'checked'
      : '';
    container.append(`
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="checkbox" id="carrera_${carrera.id_carrera}" value="${carrera.id_carrera}" ${isChecked}>
        <label class="form-check-label" for="carrera_${carrera.id_carrera}">${carrera.nombre}</label>
      </div>
    `);
  });
}
