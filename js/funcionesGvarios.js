let nombreTablaActual = '';
let nombreTablaActualMostrar = '';
let busquedaActualVarios = '';

/**
 * Inicializa la gestión de varios catálogos.
 * @param {Array} tablasCatalogo - Array de objetos { name: 'nombre_tabla', display: 'Nombre a mostrar' }.
 */
function inicializarGestionVarios(tablasCatalogo) {
  // Cargar la primera tabla por defecto al iniciar
  if (tablasCatalogo.length > 0) {
    cargarTabla(tablasCatalogo[0].name, tablasCatalogo[0].display);
  }

  // Evento para el botón de crear
  $('#btnCrearElemento').on('click', function () {
    crearElemento(nombreTablaActual, nombreTablaActualMostrar);
  });

  // Manejar el envío del formulario del modal
  $('#catalogoForm').submit(function (evento) {
    evento.preventDefault();
    guardarElemento();
  });

  // Limpiar formulario y reestablecer título del modal al cerrar
  $('#catalogoModal').on('hidden.bs.modal', function () {
    restablecerFormularioCatalogo();
  });

  // Búsqueda en tiempo real con debounce
  let temporizadorBusquedaVarios;
  $('#entradaBusqueda').on('input', function (e) {
    const valor = e.target.value.trim();
    clearTimeout(temporizadorBusquedaVarios);
    temporizadorBusquedaVarios = setTimeout(() => {
      busquedaActualVarios = valor; // Actualizar la búsqueda actual
      cargarElementos(nombreTablaActual, busquedaActualVarios);
    }, 300);
  });
}

/**
 * Carga los elementos de una tabla específica y actualiza la interfaz.
 * @param {string} nombreTabla - El nombre de la tabla en la base de datos.
 * @param {string} nombreMostrar - El nombre a mostrar de la tabla (ej. "Estados").
 */
function cargarTabla(nombreTabla, nombreMostrar) {
  nombreTablaActual = nombreTabla;
  nombreTablaActualMostrar = nombreMostrar;
  busquedaActualVarios = ''; // Limpiar búsqueda al cambiar de tabla

  // Actualizar títulos
  $('#nombreTablaActualMostrar').text(nombreMostrar);
  $('#textoBtnCrearElemento').text(nombreMostrar);
  $('#nombreListaElementosTabla').text(nombreMostrar);
  $('#entradaBusqueda').attr(
    'placeholder',
    `Buscar por nombre en ${nombreMostrar}...`
  );
  $('#entradaBusqueda').val(''); // Limpiar el input de búsqueda

  // Desactivar y activar la pestaña correcta
  $('#pestañasCatalogo button')
    .removeClass('active')
    .attr('aria-selected', 'false');
  $(`#${nombreTabla}-tab`).addClass('active').attr('aria-selected', 'true');

  cargarElementos(nombreTabla);
}

/**
 * Carga los elementos de la tabla actual desde el servidor y los muestra en la tabla.
 * @param {string} nombreTabla - El nombre de la tabla.
 * @param {string} busqueda - Término de búsqueda (opcional).
 */
function cargarElementos(nombreTabla, busqueda = '') {
  const url = `../CONTROLADOR/ajax_varios.php?action=listar&nombreTabla=${encodeURIComponent(
    nombreTabla
  )}&busqueda=${encodeURIComponent(busqueda)}`;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success) {
        const elementos = respuesta.elementos; // Ahora recibimos los elementos directamente
        const columnasTablaInfo = respuesta.schema; // Y también el esquema de la tabla

        const nombreColumnaId = obtenerNombreColumnaIdFrontend(nombreTabla);

        let headerHtml = '<tr><th class="text-center">ID</th>';
        const displayedColumnsInHeader = [nombreColumnaId]; // Track columns for both header and body

        if (columnasTablaInfo.nombre) {
          headerHtml += '<th class="text-start">Nombre</th>'; // Align 'Nombre' to start
          displayedColumnsInHeader.push('nombre');
        }

        const commonExcludedForListing = [
          nombreColumnaId,
          'nombre', // Already handled
          'contrasena',
          'codigo_recuperacion',
          'codigo_expira_en',
          'hoja_vida_path',
          'fecha_creacion',
          'fecha_actualizacion',
          'tipo_documento_id_tipo',
          'ciudad_id_ciudad',
          'estado_id_estado',
          'modalidad_id_modalidad',
          'tipo_oferta_id_tipo_oferta',
          'tipo_referencia_id_tipo_referencia',
          'area_conocimiento_id_area',
          'carrera_id_carrera',
          'disponibilidad_id_disponibilidad',
          'sector_id_sector',
          'empresa_idEmpresa',
          'estudiante_idEstudiante',
          'oferta_idOferta',
          'id_oferta_carrera',
        ];

        // Add other dynamic columns
        for (const colName in columnasTablaInfo) {
          if (
            !commonExcludedForListing.includes(colName) &&
            !displayedColumnsInHeader.includes(colName)
          ) {
            const dataType = columnasTablaInfo[colName].DATA_TYPE;
            if (
              dataType.includes('varchar') ||
              dataType.includes('text') ||
              dataType.includes('int') ||
              dataType.includes('decimal') ||
              dataType.includes('float') ||
              dataType === 'year'
            ) {
              headerHtml += `<th class="text-start">${ucwords(
                colName.replace(/_/g, ' ')
              )}</th>`; // Align to start
              displayedColumnsInHeader.push(colName);
            }
          }
        }
        headerHtml += '<th class="text-center">Acciones</th></tr>'; // Actions column always last and centered
        $('#tablaEncabezados').html(headerHtml); // Actualizar el thead

        let bodyHtml = '';
        if (elementos.length === 0) {
          bodyHtml = `<tr><td colspan="${
            displayedColumnsInHeader.length + 1
          }" class="text-center py-4">
            <div class="text-muted">
              <div class="display-1">🤷‍♂️</div>
              <h5>${
                busqueda
                  ? 'No se encontraron resultados'
                  : 'No hay elementos registrados'
              }</h5>
              <p class="mb-0">${
                busqueda
                  ? 'Intenta con otros términos de búsqueda'
                  : 'Aún no se han registrado elementos en este catálogo'
              }</p>
            </div>
          </td></tr>`;
        } else {
          elementos.forEach((elemento) => {
            bodyHtml += '<tr>';
            bodyHtml += `<td class="text-center"><span class="badge bg-secondary">${
              elemento[nombreColumnaId] || 'N/A'
            }</span></td>`;

            displayedColumnsInHeader.forEach((colName) => {
              if (colName === nombreColumnaId) return; // Skip primary ID, already added

              let value = elemento[colName] || 'N/A';
              // Basic formatting for display
              if (
                colName.startsWith('fecha_') ||
                colName === 'codigo_expira_en' ||
                colName.includes('_at')
              ) {
                if (value && value !== '0000-00-00 00:00:00') {
                  value = new Date(value).toLocaleString(); // Format date/time
                } else {
                  value = 'N/A';
                }
              }
              // Apply text-start for general content, text-center for specific cases like numbers if desired
              const alignmentClass =
                colName === 'nombre' || colName.includes('descripcion')
                  ? 'text-start'
                  : 'text-center';
              bodyHtml += `<td class="${alignmentClass}">${htmlspecialchars(
                value
              )}</td>`;
            });

            bodyHtml += `<td class="text-center">
              <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-dark"
                  onclick="verDetalleElemento('${nombreTabla}', ${
              elemento[nombreColumnaId] || '0'
            })"
                  title="Ver detalles">
                  👁️
                </button>
                <button class="btn btn-sm btn-outline-warning"
                  onclick="editarElemento('${nombreTabla}', ${
              elemento[nombreColumnaId] || '0'
            })"
                  title="Editar">
                  ✏️
                </button>
                <button class="btn btn-sm btn-outline-danger"
                  onclick="eliminarElemento('${nombreTabla}', ${
              elemento[nombreColumnaId] || '0'
            })"
                  title="Eliminar">
                  🗑️
                </button>
              </div>
            </td>`;
            bodyHtml += '</tr>';
          });
        }
        $('#tablaElementos').html(bodyHtml); // Actualizar el tbody
        $('#totalElementos').text(respuesta.total);
        $('#textoEstadistica').text(
          busqueda ? 'Resultados encontrados' : 'Total de elementos'
        );
      } else {
        mostrarError(respuesta.message);
        $('#tablaEncabezados').html(''); // Clear headers on error
        $('#tablaElementos').html(
          `<tr><td colspan="3" class="text-center text-muted">Error al cargar los elementos.</td></tr>`
        );
      }
    },
    error: function (xhr, estado, error) {
      console.error('Error al cargar elementos:', error);
      mostrarError('Error de conexión al cargar elementos.');
      $('#tablaEncabezados').html(''); // Clear headers on error
      $('#tablaElementos').html(
        `<tr><td colspan="3" class="text-center text-danger">Error de conexión al cargar elementos.</td></tr>`
      );
    },
  });
}

/**
 * Limpia el campo de búsqueda y recarga los elementos de la tabla actual.
 */
function limpiarBusqueda() {
  $('#entradaBusqueda').val('');
  busquedaActualVarios = '';
  cargarElementos(nombreTablaActual);
}

/**
 * Genera dinámicamente los campos del formulario en el modal de catálogo.
 * @param {string} nombreTabla - El nombre de la tabla.
 * @param {Object} [elementoExistente=null] - Datos del elemento a editar (opcional).
 */
function generarCamposFormulario(nombreTabla, elementoExistente = null) {
  const modalBody = $('#catalogoForm .modal-body');
  modalBody.find('.dynamic-field-group').remove(); // Limpiar campos dinámicos previos

  $.ajax({
    url: '../CONTROLADOR/ajax_varios.php',
    type: 'GET',
    data: { action: 'get_table_schema', nombreTabla: nombreTabla },
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success && respuesta.schema) {
        const schema = respuesta.schema;
        let formHtml = '';
        const idColumna = obtenerNombreColumnaIdFrontend(nombreTabla); // Helper para obtener el nombre de la columna ID

        // Campos a excluir de la generación automática del formulario
        const excludedFields = [
          idColumna, // Primary ID column
          'fecha_creacion',
          'fecha_actualizacion',
          'codigo_recuperacion',
          'codigo_expira_en',
          'contrasena', // Password fields should be handled separately for security
          'hoja_vida_path', // File paths are not direct text inputs
          'id_oferta_carrera', // Specific ID for join table if it appears
        ];

        for (const fieldName in schema) {
          if (excludedFields.includes(fieldName)) {
            continue;
          }

          const columnInfo = schema[fieldName];
          const dataType = columnInfo.DATA_TYPE;
          const isNullable = columnInfo.IS_NULLABLE === 'YES';
          const displayLabel = ucwords(fieldName.replace(/_/g, ' '));
          let fieldValue = elementoExistente
            ? elementoExistente[fieldName] || ''
            : '';
          let inputType = 'text';
          let inputTag = 'input';
          let extraAttributes = '';

          // Determine input type and tag based on data type
          if (
            dataType.includes('int') ||
            dataType.includes('decimal') ||
            dataType.includes('float') ||
            dataType === 'year'
          ) {
            inputType = 'number';
          } else if (dataType === 'date') {
            inputType = 'date';
            if (fieldValue) {
              // Ensure date is in 'YYYY-MM-DD' format for input type="date"
              fieldValue = new Date(fieldValue).toISOString().split('T')[0];
            }
          } else if (dataType === 'datetime' || dataType === 'timestamp') {
            inputType = 'datetime-local';
            if (fieldValue) {
              const date = new Date(fieldValue);
              // Format for datetime-local input:紝-MM-DDTHH:MM
              fieldValue = date.toISOString().slice(0, 16);
            }
          } else if (
            dataType.includes('text') ||
            (dataType.includes('varchar') &&
              (fieldName.includes('descripcion') ||
                fieldName.includes('habilidades') ||
                fieldName.includes('experiencia') ||
                fieldName.includes('objetivos') ||
                fieldName.includes('certificaciones') ||
                fieldName.includes('idiomas') ||
                fieldName.includes('direccion') ||
                fieldName.includes('comentario') ||
                fieldName.includes('requisitos') ||
                fieldName.includes('beneficios') ||
                fieldName.includes('horario') ||
                fieldName.includes('remuneracion') ||
                fieldName.includes('sitio_web') ||
                fieldName.includes('facultad') ||
                fieldName.includes('apellidos') ||
                fieldName.includes('n_doc') ||
                fieldName.includes('codigo') ||
                fieldName.includes('titulo') ||
                fieldName.includes('correo') ||
                fieldName.includes('telefono') ||
                fieldName.includes('contacto_nombres') ||
                fieldName.includes('contacto_apellidos') ||
                fieldName.includes('contacto_cargo') ||
                fieldName.includes('comentario')))
          ) {
            // Usar textarea para campos de texto más largos o campos de texto específicos
            inputTag = 'textarea';
            extraAttributes = 'rows="3"';
          }

          // Set required attribute based on database schema (IS_NULLABLE)
          if (!isNullable) {
            extraAttributes += ' required';
          }

          // Special handling for 'nombre' field (always required for these catalog tables)
          if (fieldName === 'nombre' && isNullable === true) {
            extraAttributes += ' required';
          }

          if (inputTag === 'input') {
            formHtml += `
                <div class="mb-3 dynamic-field-group">
                  <label for="${fieldName}Elemento" class="form-label">${displayLabel} ${
              !isNullable ? '<span class="text-danger">*</span>' : ''
            }</label>
                  <input type="${inputType}" class="form-control" id="${fieldName}Elemento" name="${fieldName}" value="${fieldValue}" ${extraAttributes}>
                  <div class="invalid-feedback" id="retroalimentacion${capitalizeFirstLetter(
                    fieldName
                  )}Elemento"></div>
                </div>
              `;
          } else if (inputTag === 'textarea') {
            formHtml += `
                <div class="mb-3 dynamic-field-group">
                  <label for="${fieldName}Elemento" class="form-label">${displayLabel} ${
              !isNullable ? '<span class="text-danger">*</span>' : ''
            }</label>
                  <textarea class="form-control" id="${fieldName}Elemento" name="${fieldName}" ${extraAttributes}>${fieldValue}</textarea>
                  <div class="invalid-feedback" id="retroalimentacion${capitalizeFirstLetter(
                    fieldName
                  )}Elemento"></div>
                </div>
              `;
          }
        }
        modalBody.append(formHtml);
      } else {
        mostrarError(
          respuesta.message || 'No se pudo cargar el esquema de la tabla.'
        );
      }
    },
    error: function (xhr, estado, error) {
      console.error('Error al obtener esquema de tabla:', error);
      mostrarError('Error de conexión al obtener esquema de tabla.');
    },
  });
}

// Función auxiliar para capitalizar la primera letra
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

// Función auxiliar para convertir a formato de título (ej. 'nombre_tabla' -> 'Nombre Tabla')
function ucwords(str) {
  return (str + '').replace(/^(.)|\s+(.)/g, function ($1) {
    return $1.toUpperCase();
  });
}

// Helper para obtener el nombre de la columna ID (replicado de PHP para la lógica del frontend)
function obtenerNombreColumnaIdFrontend(nombreTabla) {
  if (nombreTabla === 'tipo_documento') {
    return 'id_tipo';
  } else if (nombreTabla === 'area_conocimiento') {
    return 'id_area';
  } else if (nombreTabla === 'empresa') {
    return 'idEmpresa';
  } else if (nombreTabla === 'estudiante') {
    return 'idEstudiante';
  } else if (nombreTabla === 'administrador') {
    return 'idAdministrador';
  } else if (nombreTabla === 'oferta') {
    return 'idOferta';
  } else if (nombreTabla === 'referencia') {
    return 'idReferencia';
  }
  return 'id_' + nombreTabla;
}

/**
 * Función para escapar caracteres HTML para evitar XSS.
 * @param {string} text - El texto a escapar.
 * @returns {string} El texto escapado.
 */
function htmlspecialchars(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  };
  return text.replace(/[&<>"']/g, function (m) {
    return map[m];
  });
}

/**
 * Abre el modal para crear un nuevo elemento de catálogo.
 * @param {string} nombreTabla - El nombre de la tabla.
 * @param {string} nombreMostrar - El nombre a mostrar de la tabla.
 */
function crearElemento(nombreTabla, nombreMostrar) {
  restablecerFormularioCatalogo();
  $('#catalogoModalLabel').text(`Crear Nuevo ${nombreMostrar.slice(0, -1)}`); // Quita la 's' final para singular
  $('#btnGuardarElemento')
    .text('Guardar')
    .removeClass('btn-warning')
    .addClass('btn-dark');
  $('#nombreTablaElemento').val(nombreTabla); // Establecer la tabla actual en el hidden input
  generarCamposFormulario(nombreTabla); // Llamada para generar campos dinámicos
  $('#catalogoModal').modal('show');
}

/**
 * Abre el modal para editar un elemento de catálogo existente.
 * @param {string} nombreTabla - El nombre de la tabla.
 * @param {number} id - El ID del elemento a editar.
 */
function editarElemento(nombreTabla, id) {
  restablecerFormularioCatalogo();
  $('#catalogoModalLabel').text(
    `Editar ${nombreTablaActualMostrar.slice(0, -1)}`
  );
  $('#btnGuardarElemento')
    .text('Actualizar')
    .removeClass('btn-dark')
    .addClass('btn-warning');
  $('#idElemento').val(id);
  $('#nombreTablaElemento').val(nombreTabla);

  $.ajax({
    url: '../CONTROLADOR/ajax_varios.php',
    type: 'GET',
    data: { action: 'obtener', nombreTabla: nombreTabla, id: id },
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success) {
        const elemento = respuesta.item;
        generarCamposFormulario(nombreTabla, elemento); // Generar y poblar campos
        $('#catalogoModal').modal('show');
      } else {
        mostrarError(respuesta.message);
      }
    },
    error: function (xhr, estado, error) {
      console.error('Error al obtener elemento para editar:', error);
      mostrarError('Error de conexión al obtener elemento para editar.');
    },
  });
}

/**
 * Resetea el formulario del modal de catálogo.
 */
function restablecerFormularioCatalogo() {
  $('#catalogoForm')[0].reset();
  $('#idElemento').val('');
  $('#nombreTablaElemento').val('');
  // Eliminar todos los campos dinámicamente añadidos y su retroalimentación
  $('#catalogoForm .modal-body').find('.dynamic-field-group').remove();
  // Asegurarse de que los inputs ocultos de ID y nombreTabla sigan ahí.
}

/**
 * Guarda o actualiza un elemento de catálogo.
 */
function guardarElemento() {
  const id = $('#idElemento').val();
  const nombreTabla = $('#nombreTablaElemento').val();
  const accion = id ? 'actualizar' : 'crear';

  // Recolectar todos los datos del formulario dinámicamente
  const datosFormulario = new FormData($('#catalogoForm')[0]);
  datosFormulario.append('action', accion);
  datosFormulario.append('nombreTabla', nombreTabla);

  // Validación frontend para 'nombre' (siempre requerido si existe en la tabla)
  const nombreElemento = $('#nombreElemento');
  let hayError = false;

  if (nombreElemento.length > 0) {
    // Check if 'nombre' field exists in the form
    const nombre = nombreElemento.val().trim();
    if (nombre === '') {
      nombreElemento.addClass('is-invalid');
      $('#retroalimentacionNombreElemento').text(
        'El nombre no puede estar vacío.'
      );
      hayError = true;
    } else {
      nombreElemento.removeClass('is-invalid');
      $('#retroalimentacionNombreElemento').text('');
    }
  }

  if (hayError) {
    mostrarError('Por favor, corrija los errores en el formulario.');
    return;
  }

  $.ajax({
    url: '../CONTROLADOR/ajax_varios.php',
    type: 'POST',
    data: datosFormulario,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success) {
        mostrarExito(respuesta.message);
        $('#catalogoModal').modal('hide');
        cargarElementos(nombreTablaActual, busquedaActualVarios);
      } else {
        if (respuesta.errors) {
          for (const fieldName in respuesta.errors) {
            $(`#${fieldName}Elemento`).addClass('is-invalid');
            $(
              `#retroalimentacion${capitalizeFirstLetter(fieldName)}Elemento`
            ).text(respuesta.errors[fieldName]);
          }
          mostrarError(respuesta.message || 'Errores de validación.');
        } else {
          mostrarError(respuesta.message);
        }
      }
    },
    error: function (xhr, estado, error) {
      console.error('Error al guardar elemento:', error);
      mostrarError('Error de conexión al guardar elemento.');
    },
  });
}

/**
 * Elimina un elemento de catálogo.
 * @param {string} nombreTabla - El nombre de la tabla.
 * @param {number} id - El ID del elemento a eliminar.
 */
function eliminarElemento(nombreTabla, id) {
  Swal.fire({
    title: '¿Está seguro de eliminar este elemento?',
    text: 'Esta acción no se puede deshacer. Si el elemento está en uso, la operación fallará.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
  }).then((resultado) => {
    if (resultado.isConfirmed) {
      const datosFormulario = new FormData();
      datosFormulario.append('action', 'eliminar');
      datosFormulario.append('nombreTabla', nombreTabla);
      datosFormulario.append('id', id);

      $.ajax({
        url: '../CONTROLADOR/ajax_varios.php',
        type: 'POST',
        data: datosFormulario,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (respuesta) {
          if (respuesta.success) {
            mostrarExito(respuesta.message);
            cargarElementos(nombreTablaActual, busquedaActualVarios); // Recargar la tabla
          } else {
            mostrarError(respuesta.message);
          }
        },
        error: function (xhr, estado, error) {
          console.error('Error al eliminar elemento:', error);
          mostrarError('Error de conexión al eliminar elemento.');
        },
      });
    }
  });
}

/**
 * Muestra el detalle de un elemento de catálogo en un modal de Bootstrap.
 * @param {string} nombreTabla - El nombre de la tabla.
 * @param {number} id - El ID del elemento.
 */
function verDetalleElemento(nombreTabla, id) {
  $.ajax({
    url: '../CONTROLADOR/ajax_varios.php',
    type: 'GET',
    data: { action: 'detalle_html', nombreTabla: nombreTabla, id: id },
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success && respuesta.html) {
        $('#contenidoDetalleCatalogo').html(respuesta.html);
        new bootstrap.Modal(
          document.getElementById('modalDetalleCatalogo')
        ).show();
      } else {
        mostrarError(
          respuesta.message || 'No se pudo cargar el detalle del elemento.'
        );
      }
    },
    error: function (xhr, estado, error) {
      console.error('Error al obtener detalle de elemento:', error);
      mostrarError('Error de conexión al obtener detalle de elemento.');
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
