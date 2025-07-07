/**
 * Renderiza las opciones para un elemento <select> dado un array de datos.
 * @param {Array} data - Array de objetos a usar como opciones.
 * @param {string} selectId - El ID del elemento <select>.
 * @param {string} valueKey - La clave del objeto a usar como 'value' de la opción.
 * @param {string} textKey - La clave del objeto a usar como texto visible de la opción.
 */
function renderSelectOptions(data, selectId, valueKey, textKey) {
  const selectElement = $(`#${selectId}`);
  selectElement.empty();
  selectElement.append('<option value="">Seleccione...</option>'); // Default option
  data.forEach((item) => {
    selectElement.append(
      `<option value="${item[valueKey]}">${item[textKey]}</option>`
    );
  });
}

/**
 * Carga los datos del perfil de la empresa desde el servidor y los rellena en el formulario.
 */
function loadCompanyProfile() {
  const idEmpresa = $('#idEmpresa').val();
  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php',
    type: 'GET',
    data: {
      action: 'obtener_empresa_perfil',
      id: idEmpresa,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.data) {
        const empresa = response.data;
        // Populate form fields (for edit mode)
        $('#nombre').val(empresa.nombre);
        $('#correo').val(empresa.correo);
        $('#telefono').val(empresa.telefono);
        $('#direccion').val(empresa.direccion);
        $('#n_doc').val(empresa.n_doc);
        $('#descripcion').val(empresa.descripcion);
        $('#sitio_web').val(empresa.sitio_web);
        $('#numero_empleados').val(empresa.numero_empleados);
        $('#ano_fundacion').val(empresa.ano_fundacion);
        $('#contacto_nombres').val(empresa.contacto_nombres);
        $('#contacto_apellidos').val(empresa.contacto_apellidos);
        $('#contacto_cargo').val(empresa.contacto_cargo);
        $('#tipo_documento_id_tipo').val(empresa.tipo_documento_id_tipo);
        $('#ciudad_id_ciudad').val(empresa.ciudad_id_ciudad);
        $('#sector_id_sector').val(empresa.sector_id_sector);
        $('#estado_id_estado').val(empresa.estado_id_estado);

        // Populate view mode elements
        $('#view_idEmpresa').text(empresa.idEmpresa || 'N/A');
        $('#view_nombre').text(empresa.nombre || 'N/A');
        $('#view_correo').text(empresa.correo || 'N/A');
        $('#view_telefono').text(empresa.telefono || 'N/A');
        $('#view_direccion').text(empresa.direccion || 'N/A');
        $('#view_n_doc').text(empresa.n_doc || 'N/A');
        $('#view_tipo_documento_id_tipo').text(
          empresa.tipo_documento_nombre || 'N/A'
        );
        $('#view_ciudad_id_ciudad').text(empresa.ciudad_nombre || 'N/A');
        $('#view_sector_id_sector').text(empresa.sector_nombre || 'N/A');
        $('#view_descripcion').text(empresa.descripcion || 'No disponible');
        $('#view_sitio_web').html(
          empresa.sitio_web
            ? `<a href="${empresa.sitio_web}" target="_blank">${empresa.sitio_web}</a>`
            : 'N/A'
        );
        $('#view_numero_empleados').text(empresa.numero_empleados || 'N/A');
        $('#view_ano_fundacion').text(empresa.ano_fundacion || 'N/A');
        $('#view_estado_id_estado').text(empresa.estado_nombre || 'N/A');
        $('#view_contacto_nombres').text(empresa.contacto_nombres || 'N/A');
        $('#view_contacto_apellidos').text(empresa.contacto_apellidos || 'N/A');
        $('#view_contacto_cargo').text(empresa.contacto_cargo || 'N/A');

        toggleEditMode(false); // Start in view mode
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar el perfil de la empresa:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar el perfil de la empresa. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Carga las referencias recibidas por la empresa desde el servidor
 * y las muestra en la sección correspondiente del perfil.
 * @param {string} companyId - El ID de la empresa cuyas referencias se cargarán.
 */
function loadCompanyReferences(companyId) {
  const referenciasListContainer = $('#referenciasEmpresaListContainer');
  referenciasListContainer.html(
    '<p class="text-muted text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Cargando referencias...</p>'
  ); // Mensaje de carga

  $.ajax({
    url: '../CONTROLADOR/ajax_Mempresa.php', // Usamos ajax_Mempresa.php para obtener las referencias de la empresa
    type: 'GET',
    data: {
      action: 'obtener_referencias_empresa_perfil',
      idEmpresa: companyId,
    },
    dataType: 'json',
    success: function (response) {
      console.log(
        'Respuesta de AJAX (obtener_referencias_empresa_perfil):',
        response
      );
      if (response.success && response.html) {
        referenciasListContainer.html(response.html);
      } else {
        referenciasListContainer.html(
          '<p class="text-muted text-center py-3">No has recibido referencias de estudiantes aún.</p>'
        );
        // Opcional: Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar referencias de la empresa (AJAX):', error);
      referenciasListContainer.html(
        '<p class="text-danger text-center py-3">Error de conexión al cargar las referencias de estudiantes.</p>'
      );
      Swal.fire(
        'Error de conexión',
        'No se pudieron cargar las referencias de estudiantes. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Guarda los cambios del perfil de la empresa enviándolos al servidor.
 */
function saveCompanyProfile() {
  const formData = new FormData($('#empresaProfileForm')[0]);
  formData.append('action', 'actualizar_empresa_perfil');

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
        loadCompanyProfile(); // Recargar para mostrar los datos actualizados en modo vista
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al guardar el perfil de la empresa:', error);
      Swal.fire(
        'Error de conexión',
        'No se pudo guardar el perfil de la empresa. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Alterna entre el modo de visualización y edición del formulario.
 * @param {boolean} editMode - True para modo edición, False para modo visualización.
 */
function toggleEditMode(editMode) {
  const viewModeDiv = $('#viewMode');
  const editModeDiv = $('#editMode');
  const viewModeContactDiv = $('#viewModeContact');
  const editModeContactDiv = $('#editModeContact');
  const editButton = $('#editButton');
  const saveButton = $('#saveButton');
  const cancelButton = $('#cancelButton');

  if (editMode) {
    viewModeDiv.hide();
    viewModeContactDiv.hide();
    editModeDiv.show();
    editModeContactDiv.show();
    editButton.hide();
    saveButton.show();
    cancelButton.show();
  } else {
    editModeDiv.hide();
    editModeContactDiv.hide();
    viewModeDiv.show();
    viewModeContactDiv.show();
    editButton.show();
    saveButton.hide();
    cancelButton.hide();
    // Recargar los datos al cancelar para descartar cambios
    if (event && event.type === 'click') {
      // Only reload if cancelled by button click
      loadCompanyProfile();
    }
  }
}

/**
 * Muestra una alerta modal con el perfil básico de la empresa.
 * Esta función es una utilidad para mostrar un resumen rápido en un modal.
 */
function mostrarPerfilEmpresaModal() {
  Swal.fire({
    title: 'Perfil de Empresa',
    html: `
            <div class="text-start">
                <div class="mb-2"><strong>Usuario:</strong> ${'<?php echo htmlspecialchars($_SESSION["usuario"]); ?>'}</div>
                <div class="mb-2"><strong>ID de Empresa:</strong> ${'<?php echo htmlspecialchars($_SESSION["usuario_id"]); ?>'}</div>
                <div class="mb-2"><strong>Tipo de Cuenta:</strong> <span class="badge bg-success">Empresa</span></div>
                <div class="mb-2"><strong>Sesión iniciada:</strong> ${'<?php echo date("d/m/Y H:i:s"); ?>'}</div>
                <div class="mb-2"><strong>Estado:</strong> <span class="badge bg-success">Activo</span></div>
            </div>
        `,
    icon: 'info',
    confirmButtonText: 'Cerrar',
    confirmButtonColor: '#0d6efd',
  });
}
