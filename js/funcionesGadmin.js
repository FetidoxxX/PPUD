// Variable global para almacenar el total de administradores
let busquedaActualAdmin = '';

// Variables globales para los datos estáticos de los selectores, inicializadas por PHP
let globalTiposDocumentoAdmin = [];
let globalEstadosAdmin = [];
let globalCiudadesAdmin = [];

/**
 * Inicializa la gestión de administradores.
 * @param {Array} tipos_documento - Tipos de documento disponibles.
 * @param {Array} estados - Estados disponibles.
 * @param {Array} ciudades - Ciudades disponibles.
 */
function initializeGestionAdministradores(tipos_documento, estados, ciudades) {
  globalTiposDocumentoAdmin = tipos_documento;
  globalEstadosAdmin = estados;
  globalCiudadesAdmin = ciudades;

  // Siempre cargar todos los administradores al iniciar la página
  cargarAdministradores();

  // Búsqueda en tiempo real con debounce
  let timeoutBusquedaAdmin;
  $('#busquedaInput').on('input', function (e) {
    const valor = e.target.value.trim();
    clearTimeout(timeoutBusquedaAdmin);
    timeoutBusquedaAdmin = setTimeout(() => {
      busquedaActualAdmin = valor; // Actualizar la búsqueda actual
      cargarAdministradores(busquedaActualAdmin); // Ya no se pasa 'mostrarTodos'
    }, 300);
  });

  // Manejar el envío del formulario de administrador
  $('#administradorForm').submit(function (event) {
    event.preventDefault();
    saveAdministrador();
  });

  // Limpiar formulario y reestablecer título del modal al cerrar
  $('#administradorModal').on('hidden.bs.modal', function () {
    resetAdministradorForm();
  });
}

/**
 * Carga los administradores desde el servidor y los muestra en la tabla.
 * Siempre incluye administradores inactivos.
 * @param {string} busqueda - Término de búsqueda (opcional).
 */
function cargarAdministradores(busqueda = '') {
  // Siempre se envía 'mostrarTodos=true' para incluir inactivos
  const url = `../CONTROLADOR/ajax_Gadmin.php?action=listar&busqueda=${encodeURIComponent(
    busqueda
  )}&mostrarTodos=true`;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        $('#tablaAdministradores').html(response.html);
        $('#totalAdministradores').text(response.total);
        // El texto de la estadística siempre será "Total de administradores"
        $('#textoEstadistica').text(
          busqueda ? 'Resultados encontrados' : 'Total de administradores'
        );
      } else {
        mostrarError(response.message);
        $('#tablaAdministradores').html(
          '<tr><td colspan="9" class="text-center text-muted">Error al cargar los administradores.</td></tr>'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar administradores:', error);
      mostrarError('Error de conexión al cargar administradores.');
      $('#tablaAdministradores').html(
        '<tr><td colspan="9" class="text-center text-danger">Error de conexión al cargar administradores.</td></tr>'
      );
    },
  });
}

/**
 * Limpia el campo de búsqueda y recarga los administradores.
 */
function limpiarBusqueda() {
  $('#busquedaInput').val('');
  busquedaActualAdmin = '';
  // Recargar administradores, siempre mostrando todos
  cargarAdministradores();
}

/**
 * Abre el modal para editar un administrador existente.
 * @param {string} idAdministrador - El ID del administrador a editar.
 */
function editarAdministrador(idAdministrador) {
  resetAdministradorForm(); // Limpiar el formulario primero
  $('#administradorModalLabel').text('Editar Administrador');
  $('#btnGuardarAdministrador')
    .text('Actualizar Administrador')
    .removeClass('btn-primary')
    .addClass('btn-danger');
  $('#adminId').val(idAdministrador).prop('disabled', true); // Deshabilitar ID para edición
  // La contraseña no se edita desde aquí, así que no se muestra el campo de contraseña.

  $.ajax({
    url: '../CONTROLADOR/ajax_Gadmin.php',
    type: 'GET',
    data: { action: 'obtener', id: idAdministrador },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        const admin = response.administrador;
        $('#adminId').val(admin.idAdministrador);
        $('#nombres').val(admin.nombres);
        $('#apellidos').val(admin.apellidos);
        $('#correo').val(admin.correo);
        $('#telefono').val(admin.telefono);
        $('#n_doc').val(admin.n_doc);

        // Cargar opciones de selectores y seleccionar valor actual
        renderSelectOptions(
          globalTiposDocumentoAdmin,
          'tipo_documento_id_tipo',
          'id_tipo',
          admin.tipo_documento_id_tipo
        );
        renderSelectOptions(
          globalEstadosAdmin,
          'estado_id_estado',
          'id_estado',
          admin.estado_id_estado
        ); // Ahora se puede cambiar el estado
        renderSelectOptions(
          globalCiudadesAdmin,
          'ciudad_id_ciudad',
          'id_ciudad',
          admin.ciudad_id_ciudad
        );

        $('#administradorModal').modal('show');
      } else {
        mostrarError(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener administrador para editar:', error);
      mostrarError('Error de conexión al obtener administrador para editar.');
    },
  });
}

/**
 * Resetea el formulario del modal de administradores.
 */
function resetAdministradorForm() {
  $('#administradorForm')[0].reset();
  $('#adminId').val(''); // Limpiar ID de administrador
  $('#administradorModalLabel').text('Crear Nuevo Administrador'); // Mantener para consistencia, aunque la creación esté deshabilitada
  $('#btnGuardarAdministrador')
    .text('Guardar Administrador')
    .removeClass('btn-warning')
    .addClass('btn-primary');
  $('#adminId').prop('disabled', false); // Habilitar ID por defecto (para un posible futuro re-habilitar creación)
  // Se ha removido la lógica de contraseña ya que no se permite cambiar desde este módulo.
}

/**
 * Guarda o actualiza un administrador.
 */
function saveAdministrador() {
  const idAdministrador = $('#adminId').val();
  const action = 'actualizar'; // Solo permitimos la acción de 'actualizar'

  const formData = new FormData($('#administradorForm')[0]);
  formData.append('action', action);
  formData.append('id', idAdministrador);
  formData.delete('contrasena'); // Asegurarse de que no se envíe el campo de contraseña

  // Validaciones del lado del cliente
  const nombres = $('#nombres').val().trim();
  const apellidos = $('#apellidos').val().trim();
  const correo = $('#correo').val().trim();
  const n_doc = $('#n_doc').val().trim();
  const tipoDocumento = $('#tipo_documento_id_tipo').val();
  const estado = $('#estado_id_estado').val(); // Obtener el valor del estado

  if (
    !nombres ||
    !apellidos ||
    !correo ||
    !n_doc ||
    !tipoDocumento ||
    !estado // Validar el estado
  ) {
    mostrarError('Por favor, complete todos los campos obligatorios (*).');
    return;
  }

  // Validación de formato de correo
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(correo)) {
    mostrarError('Por favor, ingrese un formato de correo electrónico válido.');
    return;
  }

  $.ajax({
    url: '../CONTROLADOR/ajax_Gadmin.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        mostrarExito(response.message);
        $('#administradorModal').modal('hide');
        cargarAdministradores(busquedaActualAdmin); // Recargar la tabla, siempre mostrando todos
      } else {
        mostrarError(response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al guardar administrador:', error);
      mostrarError('Error de conexión al guardar administrador.');
    },
  });
}

/**
 * Desactiva un administrador (cambia su estado a inactivo).
 * @param {string} idAdministrador - El ID del administrador a desactivar.
 */
function desactivarAdministrador(idAdministrador) {
  Swal.fire({
    title: '¿Está seguro de desactivar este administrador?',
    text: 'El administrador cambiará a estado "Inactivo".',
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
      formData.append('id', idAdministrador);

      $.ajax({
        url: '../CONTROLADOR/ajax_Gadmin.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            mostrarExito(response.message);
            cargarAdministradores(busquedaActualAdmin); // Recargar la tabla, siempre mostrando todos
          } else {
            mostrarError(response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error('Error al desactivar administrador:', error);
          mostrarError('Error de conexión al desactivar administrador.');
        },
      });
    }
  });
}

/**
 * Muestra el detalle de un administrador en un modal de Bootstrap.
 * @param {string} idAdministrador - El ID del administrador.
 */
function verDetalleAdministrador(idAdministrador) {
  $.ajax({
    url: '../CONTROLADOR/ajax_Gadmin.php',
    type: 'GET',
    data: { action: 'detalle_html', id: idAdministrador },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.html) {
        $('#contenidoDetalleAdministrador').html(response.html);
        new bootstrap.Modal(
          document.getElementById('modalDetalleAdministrador')
        ).show();
      } else {
        mostrarError(
          response.message || 'No se pudo cargar el detalle del administrador.'
        );
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al obtener detalle de administrador:', error);
      mostrarError('Error de conexión al obtener detalle de administrador.');
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
 * @param {Array} data - El array de datos (ej. tipos de documento, estados, ciudades).
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
