// Variable global para almacenar las referencias paginadas y el total
let currentPageReferencias = 0;
const itemsPerPageReferencias = 10; // Ajustable según necesidad
let totalReferencias = 0;
let busquedaActualReferencias = '';
let filtroTipoReferenciaActual = ''; // Nueva variable global para el filtro por tipo

// Variables globales para los datos estáticos de los selectores, inicializadas por PHP
let globalTiposReferencia = [];
let globalEstadosReferencia = [];

/**
 * Inicializa la lógica de JavaScript de la página de gestión de referencias.
 * Debe ser llamada después de que el DOM esté listo y las variables PHP estén disponibles.
 * @param {Array} tiposReferencia - Datos de los tipos de referencia.
 * @param {Array} estadosReferencia - Datos de los estados de referencia.
 */
function initializeGestionReferencias(tiposReferencia, estadosReferencia) {
  globalTiposReferencia = tiposReferencia;
  globalEstadosReferencia = estadosReferencia;

  cargarReferencias(); // Cargar referencias al iniciar la página

  // Búsqueda en tiempo real con debounce
  let timeoutBusquedaReferencias;
  $('#busquedaInput').on('input', function (e) {
    const valor = e.target.value.trim();
    clearTimeout(timeoutBusquedaReferencias);
    timeoutBusquedaReferencias = setTimeout(() => {
      busquedaActualReferencias = valor; // Actualizar la búsqueda actual
      currentPageReferencias = 0; // Resetear a la primera página en cada nueva búsqueda
      cargarReferencias(
        busquedaActualReferencias,
        filtroTipoReferenciaActual,
        currentPageReferencias * itemsPerPageReferencias
      );
    }, 300);
  });

  // Event listener para el filtro por tipo de referencia
  $('#filtroTipoReferencia').on('change', function () {
    filtroTipoReferenciaActual = $(this).val(); // Actualizar el filtro de tipo
    currentPageReferencias = 0; // Resetear a la primera página en cada nuevo filtro
    cargarReferencias(
      busquedaActualReferencias,
      filtroTipoReferenciaActual,
      currentPageReferencias * itemsPerPageReferencias
    );
  });

  // Manejar el envío del formulario de edición de referencia
  $('#formEditarReferencia').submit(function (event) {
    event.preventDefault();
    guardarCambiosReferencia();
  });

  // Limpiar formulario y reestablecer título del modal al cerrar
  $('#modalEditarReferencia').on('hidden.bs.modal', function () {
    resetearFormularioReferencia();
  });
}

/**
 * Carga las referencias desde el servidor y las muestra en la tabla.
 * @param {string} busqueda - Término de búsqueda (opcional).
 * @param {string} tipoReferenciaId - ID del tipo de referencia para filtrar (opcional).
 * @param {number} offset - Desplazamiento para la paginación.
 */
function cargarReferencias(busqueda = '', tipoReferenciaId = '', offset = 0) {
  const url = `ajax_Greferencias.php?action=listar&busqueda=${encodeURIComponent(
    busqueda
  )}&tipo_referencia_id=${encodeURIComponent(
    tipoReferenciaId
  )}&limit=${itemsPerPageReferencias}&offset=${offset}`;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        $('#tablaReferencias').html(response.html);
        totalReferencias = response.total;
        $('#totalReferencias').text(totalReferencias);
        $('#textoEstadistica').text(
          busqueda || tipoReferenciaId
            ? 'Resultados encontrados'
            : 'Total de referencias'
        );
        renderPaginationReferencias(totalReferencias, offset);
      } else {
        mostrarErrorSwal(response.message);
        $('#tablaReferencias').html(
          '<tr><td colspan="8" class="text-center text-muted">Error al cargar las referencias.</td></tr>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar referencias:', error);
      mostrarErrorSwal('Error de conexión al cargar referencias.');
      $('#tablaReferencias').html(
        '<tr><td colspan="8" class="text-center text-danger">Error de conexión al cargar referencias.</td></tr>'
      );
    },
  });
}

/**
 * Renderiza los controles de paginación para las referencias.
 * @param {number} totalItems - Número total de elementos.
 * @param {number} currentOffset - Desplazamiento actual.
 */
function renderPaginationReferencias(totalItems, currentOffset) {
  const totalPages = Math.ceil(totalItems / itemsPerPageReferencias);
  currentPageReferencias = Math.floor(currentOffset / itemsPerPageReferencias);
  const paginationControls = $('#paginationControls');
  paginationControls.empty();

  if (totalPages <= 1) {
    return; // No mostrar paginación si solo hay una página o ninguna
  }

  // Botón "Anterior"
  paginationControls.append(`
    <li class="page-item ${currentPageReferencias === 0 ? 'disabled' : ''}">
      <a class="page-link" href="#" onclick="changePageReferencias(${
        currentPageReferencias - 1
      })">Anterior</a>
    </li>
  `);

  // Números de página
  let startPage = Math.max(0, currentPageReferencias - 2);
  let endPage = Math.min(totalPages - 1, currentPageReferencias + 2);

  if (startPage > 0) {
    paginationControls.append(
      '<li class="page-item disabled"><span class="page-link">...</span></li>'
    );
  }

  for (let i = startPage; i <= endPage; i++) {
    paginationControls.append(`
      <li class="page-item ${i === currentPageReferencias ? 'active' : ''}">
        <a class="page-link" href="#" onclick="changePageReferencias(${i})">${
      i + 1
    }</a>
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
    <li class="page-item ${
      currentPageReferencias === totalPages - 1 ? 'disabled' : ''
    }">
      <a class="page-link" href="#" onclick="changePageReferencias(${
        currentPageReferencias + 1
      })">Siguiente</a>
    </li>
  `);
}

/**
 * Cambia la página de la tabla de referencias.
 * @param {number} page - El número de página (base 0).
 */
function changePageReferencias(page) {
  const totalPages = Math.ceil(totalReferencias / itemsPerPageReferencias);
  if (page >= 0 && page < totalPages) {
    currentPageReferencias = page;
    cargarReferencias(
      busquedaActualReferencias,
      filtroTipoReferenciaActual,
      currentPageReferencias * itemsPerPageReferencias
    );
  }
}

/**
 * Limpia el campo de búsqueda y recarga las referencias.
 */
function limpiarBusqueda() {
  $('#busquedaInput').val('');
  $('#filtroTipoReferencia').val(''); // Limpiar el filtro de tipo
  busquedaActualReferencias = '';
  filtroTipoReferenciaActual = '';
  currentPageReferencias = 0;
  cargarReferencias();
}

/**
 * Muestra el detalle de una referencia en un modal de Bootstrap.
 * @param {number} idReferencia - El ID de la referencia.
 */
function verDetalleReferencia(idReferencia) {
  $.ajax({
    url: 'ajax_Greferencias.php',
    type: 'GET',
    data: { action: 'detalle_html', id: idReferencia }, // Llamar a la acción que retorna HTML
    dataType: 'json', // Esperar JSON que contenga el HTML
    success: function (response) {
      if (response.success && response.html) {
        $('#contenidoDetalleReferencia').html(response.html); // Inyectar el HTML en el modal
        new bootstrap.Modal(
          document.getElementById('modalDetalleReferencia')
        ).show(); // Mostrar el modal de Bootstrap
      } else {
        mostrarErrorSwal(
          response.message || 'No se pudo cargar el detalle de la referencia.'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener detalle de referencia:', error);
      mostrarErrorSwal('Error de conexión al obtener detalle de referencia.');
    },
  });
}

/**
 * Abre el modal para editar una referencia existente.
 * @param {number} idReferencia - El ID de la referencia a editar.
 */
function editarReferencia(idReferencia) {
  resetearFormularioReferencia(); // Limpiar el formulario primero
  $('#modalEditarReferenciaLabel').text('✏️ Editar Referencia');
  $('#editReferenciaId').val(idReferencia); // Establecer el ID de la referencia en el campo oculto

  $.ajax({
    url: 'ajax_Greferencias.php',
    type: 'GET',
    data: { action: 'obtener', id: idReferencia },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        const referencia = response.referencia;
        $('#editComentario').val(referencia.comentario);
        $('#editPuntuacion').val(referencia.puntuacion);

        // Renderizar y seleccionar los tipos de referencia
        renderSelectOptions(
          globalTiposReferencia,
          'editTipoReferencia',
          'id_tipo_referencia',
          referencia.tipo_referencia_id_tipo_referencia
        );

        // Renderizar y seleccionar los estados de referencia
        renderSelectOptions(
          globalEstadosReferencia,
          'editEstadoReferencia',
          'id_estado',
          referencia.estado_id_estado
        );

        new bootstrap.Modal(
          document.getElementById('modalEditarReferencia')
        ).show();
      } else {
        mostrarErrorSwal(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener referencia para editar:', error);
      mostrarErrorSwal('Error de conexión al obtener referencia para editar.');
    },
  });
}

/**
 * Resetea el formulario del modal de edición de referencias.
 */
function resetearFormularioReferencia() {
  $('#formEditarReferencia')[0].reset();
  $('#editReferenciaId').val(''); // Limpiar ID de referencia
  $('#modalEditarReferenciaLabel').text('Editar Referencia');
}

/**
 * Guarda los cambios de una referencia existente.
 */
function guardarCambiosReferencia() {
  const idReferencia = $('#editReferenciaId').val();
  const comentario = $('#editComentario').val().trim();
  const puntuacion = $('#editPuntuacion').val().trim();
  const tipoReferenciaId = $('#editTipoReferencia').val();
  const estadoId = $('#editEstadoReferencia').val();

  // Validaciones del lado del cliente
  if (!idReferencia) {
    mostrarErrorSwal(
      'Error: ID de referencia no proporcionado para actualizar.'
    );
    return;
  }
  if (!comentario) {
    mostrarErrorSwal('El comentario de la referencia es obligatorio.');
    return;
  }
  if (!tipoReferenciaId) {
    mostrarErrorSwal('El tipo de referencia es obligatorio.');
    return;
  }
  if (!estadoId) {
    mostrarErrorSwal('El estado de la referencia es obligatorio.');
    return;
  }
  if (
    puntuacion &&
    (parseFloat(puntuacion) < 0 || parseFloat(puntuacion) > 5)
  ) {
    mostrarErrorSwal('La puntuación debe estar entre 0.0 y 5.0.');
    return;
  }

  const formData = new FormData();
  formData.append('action', 'actualizar');
  formData.append('id', idReferencia);
  formData.append('comentario', comentario);
  formData.append('puntuacion', puntuacion);
  formData.append('tipo_referencia_id_tipo_referencia', tipoReferenciaId);
  formData.append('estado_id_estado', estadoId);

  $.ajax({
    url: 'ajax_Greferencias.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        mostrarExitoSwal(response.message);
        $('#modalEditarReferencia').modal('hide');
        cargarReferencias(
          busquedaActualReferencias,
          filtroTipoReferenciaActual,
          currentPageReferencias * itemsPerPageReferencias
        ); // Recargar la tabla
      } else {
        mostrarErrorSwal(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al guardar referencia:', error);
      mostrarErrorSwal('Error de conexión al guardar referencia.');
    },
  });
}

/**
 * Desactiva una referencia (cambia su estado a inactiva).
 * @param {number} idReferencia - El ID de la referencia a desactivar.
 */
function eliminarReferencia(idReferencia) {
  Swal.fire({
    title: '¿Está seguro de desactivar esta referencia?',
    text: 'La referencia cambiará a estado "Inactiva" y ya no será visible.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, desactivar',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('action', 'eliminar'); // La acción 'eliminar' ahora manejará la desactivación
      formData.append('id', idReferencia);

      $.ajax({
        url: 'ajax_Greferencias.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            mostrarExitoSwal(response.message);
            cargarReferencias(
              busquedaActualReferencias,
              filtroTipoReferenciaActual,
              currentPageReferencias * itemsPerPageReferencias
            ); // Recargar la tabla
          } else {
            mostrarErrorSwal(response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('Error al desactivar referencia:', error);
          mostrarErrorSwal('Error de conexión al desactivar referencia.');
        },
      });
    }
  });
}

/**
 * Funciones de utilidad para mostrar mensajes SweetAlert2.
 */
function mostrarExitoSwal(mensaje) {
  Swal.fire({
    icon: 'success',
    title: 'Éxito',
    text: mensaje,
    timer: 2000,
    showConfirmButton: false,
  });
}

function mostrarErrorSwal(mensaje) {
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
