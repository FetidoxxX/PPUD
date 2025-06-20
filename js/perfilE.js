/**
 * perfilE.js
 *
 * Este archivo contiene las funciones JavaScript para gestionar el perfil del estudiante,
 * incluyendo la carga, visualización, edición y actualización de sus datos personales,
 * académicos y carreras de interés, así como el cambio de contraseña.
 */

// Variables globales para almacenar los datos estáticos de los selectores
let globalTiposDocumento = [];
let globalCiudades = [];
let globalCarreras = []; // Todas las carreras disponibles
let globalCarrerasInteresEstudiante = []; // IDs de las carreras de interés del estudiante actual

/**
 * Función de utilidad para renderizar opciones en un <select>.
 * @param {Array} data - El array de objetos con los datos (ej: [{id: 1, nombre: 'Opción 1'}]).
 * @param {string} selectId - El ID del elemento <select> en el DOM.
 * @param {string} valueKey - La clave del objeto a usar como 'value' de la opción (ej: 'id_tipo').
 * @param {string} textKey - La clave del objeto a usar como texto visible de la opción (ej: 'nombre').
 * @param {string|number} selectedValue - El valor que debe estar pre-seleccionado.
 */
function renderSelectOptions(
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

/**
 * Renderiza los checkboxes para las carreras de interés.
 * @param {Array} allCarreras - Todas las carreras disponibles.
 * @param {Array} selectedCarrerasIds - IDs de las carreras ya seleccionadas por el estudiante.
 */
function renderCarrerasInteresCheckboxes(allCarreras, selectedCarrerasIds) {
  const container = $('#carrerasInteresCheckboxes');
  container.empty();

  if (allCarreras.length === 0) {
    container.append('<p class="text-muted">No hay carreras disponibles.</p>');
    return;
  }

  allCarreras.forEach((carrera) => {
    const isChecked = selectedCarrerasIds.includes(
      parseInt(carrera.id_carrera)
    );
    const checkboxHtml = `
      <div class="col-md-6 col-lg-4 mb-2">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="${
            carrera.id_carrera
          }"
                 id="carrera_${carrera.id_carrera}" name="carreras_interes[]" ${
      isChecked ? 'checked' : ''
    }>
          <label class="form-check-label" for="carrera_${carrera.id_carrera}">
            ${carrera.nombre}
          </label>
        </div>
      </div>
    `;
    container.append(checkboxHtml);
  });
}

/**
 * Alterna entre el modo de visualización y el modo de edición del perfil.
 * @param {boolean} editMode - True para modo edición, False para modo visualización.
 */
function toggleEditMode(editMode) {
  $('#viewMode').toggle(!editMode);
  $('#editMode').toggle(editMode);
  $('#editProfileBtn').toggle(!editMode);
  $('#saveProfileBtn').toggle(editMode);
  $('#cancelEditBtn').toggle(editMode);
}

/**
 * Carga los datos del perfil del estudiante desde el servidor y rellena el formulario.
 */
function loadStudentProfile() {
  const idEstudiante = $('#idEstudiante').val();
  if (!idEstudiante) {
    console.error('ID de estudiante no disponible para cargar el perfil.');
    Swal.fire(
      'Error',
      'No se pudo obtener el ID del estudiante. Por favor, recargue la página.',
      'error'
    );
    return;
  }

  $.ajax({
    url: 'ajax_perfilE.php',
    type: 'GET',
    data: {
      action: 'obtener_perfil',
      id: idEstudiante,
    },
    dataType: 'json',
    success: function (response) {
      console.log('DEBUG (perfilE.js - loadStudentProfile success):', response); // Debugging
      if (response.success && response.data) {
        const estudiante = response.data;
        // Asegura que carreras_interes_ids sea un array, incluso si está vacío o nulo
        globalCarrerasInteresEstudiante = Array.isArray(
          estudiante.carreras_interes_ids
        )
          ? estudiante.carreras_interes_ids
          : [];

        // Rellenar modo de visualización
        $('#viewNombre').text(estudiante.nombre || 'N/A');
        $('#viewApellidos').text(estudiante.apellidos || 'N/A');
        $('#viewCorreo').text(estudiante.correo || 'N/A');
        $('#viewTelefono').text(estudiante.telefono || 'N/A');
        $('#viewFechaNac').text(
          estudiante.fechaNac
            ? new Date(estudiante.fechaNac).toLocaleDateString()
            : 'N/A'
        );
        $('#viewNDoc').text(estudiante.n_doc || 'N/A');
        $('#viewTipoDocNombre').text(estudiante.tipo_documento_nombre || 'N/A');
        $('#viewDireccion').text(estudiante.direccion || 'N/A');
        $('#viewCiudadNombre').text(estudiante.ciudad_nombre || 'N/A');
        $('#viewCodigoEstudiante').text(estudiante.codigo_estudiante || 'N/A');
        $('#viewSemestre').text(estudiante.semestre || 'N/A');
        $('#viewPromedioAcademico').text(
          estudiante.promedio_academico || 'N/A'
        );
        $('#viewHabilidades').text(estudiante.habilidades || 'N/A');
        $('#viewExperienciaLaboral').text(
          estudiante.experiencia_laboral || 'N/A'
        );
        $('#viewCertificaciones').text(estudiante.certificaciones || 'N/A');
        $('#viewIdiomas').text(estudiante.idiomas || 'N/A');
        $('#viewObjetivosProfesionales').text(
          estudiante.objetivos_profesionales || 'N/A'
        );
        $('#viewCarreraPrincipal').text(estudiante.carrera_nombre || 'N/A');

        // Renderizar carreras de interés en modo visualización
        const carrerasInteresUl = $('#viewCarrerasInteresList');
        carrerasInteresUl.empty();
        if (globalCarrerasInteresEstudiante.length > 0) {
          const nombresCarrerasInteres = globalCarreras
            .filter((c) =>
              globalCarrerasInteresEstudiante.includes(parseInt(c.id_carrera))
            )
            .map((c) => c.nombre);
          if (nombresCarrerasInteres.length > 0) {
            nombresCarrerasInteres.forEach((nombre) => {
              carrerasInteresUl.append(
                `<li class="list-group-item py-1">${nombre}</li>`
              );
            });
          } else {
            carrerasInteresUl.append(
              '<li class="list-group-item py-1 text-muted">No se han seleccionado carreras de interés.</li>'
            );
          }
        } else {
          carrerasInteresUl.append(
            '<li class="list-group-item py-1 text-muted">No se han seleccionado carreras de interés.</li>'
          );
        }

        // Rellenar modo de edición
        $('#nombre').val(estudiante.nombre);
        $('#apellidos').val(estudiante.apellidos);
        $('#correo').val(estudiante.correo);
        $('#telefono').val(estudiante.telefono);
        $('#fechaNac').val(estudiante.fechaNac); // Asegurarse que el formato de fecha sea 'YYYY-MM-DD'
        $('#n_doc').val(estudiante.n_doc);
        $('#direccion').val(estudiante.direccion);
        $('#codigo_estudiante').val(estudiante.codigo_estudiante);
        $('#semestre').val(estudiante.semestre);
        $('#promedio_academico').val(estudiante.promedio_academico);
        $('#habilidades').val(estudiante.habilidades);
        $('#experiencia_laboral').val(estudiante.experiencia_laboral);
        $('#certificaciones').val(estudiante.certificaciones);
        $('#idiomas').val(estudiante.idiomas);
        $('#objetivos_profesionales').val(estudiante.objetivos_profesionales);

        // Rellenar selectores (primero datos maestros, luego seleccionar valor)
        renderSelectOptions(
          globalTiposDocumento,
          'tipo_documento_id_tipo',
          'id_tipo',
          'nombre',
          estudiante.tipo_documento_id_tipo
        );
        renderSelectOptions(
          globalCiudades,
          'ciudad_id_ciudad',
          'id_ciudad',
          'nombre',
          estudiante.ciudad_id_ciudad
        );
        renderSelectOptions(
          globalCarreras,
          'carrera_id_carrera',
          'id_carrera',
          'nombre',
          estudiante.carrera_id_carrera
        );

        // Renderizar checkboxes de carreras de interés
        renderCarrerasInteresCheckboxes(
          globalCarreras,
          globalCarrerasInteresEstudiante
        );

        toggleEditMode(false); // Asegurarse de que inicia en modo visualización
      } else {
        Swal.fire('Error', response.message, 'error');
        console.error('Error al cargar perfil:', response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar el perfil:', { xhr, status, error });
      try {
        const errorResponse = JSON.parse(xhr.responseText);
        Swal.fire(
          'Error del Servidor',
          errorResponse.message ||
            'No se pudo cargar el perfil del estudiante. Intente de nuevo.',
          'error'
        );
      } catch (e) {
        Swal.fire(
          'Error de conexión',
          'No se pudo cargar el perfil del estudiante. Intente de nuevo.',
          'error'
        );
      }
    },
  });
}

/**
 * Carga los datos maestros (tipos de documento, ciudades, carreras) desde el servidor.
 */
function loadMasterData() {
  const requests = [
    $.ajax({
      url: 'ajax_perfilE.php',
      type: 'GET',
      data: { action: 'obtener_tipos_documento' },
      dataType: 'json',
    }),
    $.ajax({
      url: 'ajax_perfilE.php',
      type: 'GET',
      data: { action: 'obtener_ciudades' },
      dataType: 'json',
    }),
    $.ajax({
      url: 'ajax_perfilE.php',
      type: 'GET',
      data: { action: 'obtener_carreras' },
      dataType: 'json',
    }),
  ];

  $.when(...requests)
    .done(function (tiposDocResponse, ciudadesResponse, carrerasResponse) {
      if (tiposDocResponse[0].success) {
        globalTiposDocumento = tiposDocResponse[0].data;
      } else {
        console.error(
          'Error al cargar tipos de documento:',
          tiposDocResponse[0].message
        );
        Swal.fire(
          'Error',
          'No se pudieron cargar los tipos de documento.',
          'error'
        );
        return;
      }

      if (ciudadesResponse[0].success) {
        globalCiudades = ciudadesResponse[0].data;
      } else {
        console.error('Error al cargar ciudades:', ciudadesResponse[0].message);
        Swal.fire('Error', 'No se pudieron cargar las ciudades.', 'error');
        return;
      }

      if (carrerasResponse[0].success) {
        globalCarreras = carrerasResponse[0].data;
      } else {
        console.error('Error al cargar carreras:', carrerasResponse[0].message);
        Swal.fire('Error', 'No se pudieron cargar las carreras.', 'error');
        return;
      }

      // Una vez que todos los datos maestros estén cargados, cargar el perfil del estudiante
      loadStudentProfile();
    })
    .fail(function (xhr, status, error) {
      console.error('Error al cargar datos maestros:', { xhr, status, error });
      try {
        const errorResponse = JSON.parse(xhr.responseText);
        Swal.fire(
          'Error del Servidor',
          errorResponse.message ||
            'No se pudieron cargar los datos necesarios para el perfil. Intente de nuevo.',
          'error'
        );
      } catch (e) {
        Swal.fire(
          'Error de conexión',
          'No se pudieron cargar los datos necesarios para el perfil. Intente de nuevo.',
          'error'
        );
      }
    });
}

/**
 * Maneja el envío del formulario de actualización del perfil.
 */
$('#studentProfileForm').submit(function (event) {
  event.preventDefault(); // Evita el envío tradicional del formulario

  const formData = new FormData(this);
  formData.append('action', 'actualizar_perfil');
  formData.append('idEstudiante', $('#idEstudiante').val()); // Asegurar que el ID se envía explícitamente

  // Recolectar IDs de carreras de interés seleccionadas
  const selectedCarreras = [];
  $('input[name="carreras_interes[]"]:checked').each(function () {
    selectedCarreras.push($(this).val());
  });
  // Es crucial eliminar cualquier entrada previa y luego añadir cada elemento del array.
  // FormData.append() con [] crea múltiples entradas si ya existe una.
  // Al usar .delete() primero, nos aseguramos de que no haya duplicados si el formulario se envía múltiples veces.
  formData.delete('carreras_interes[]');
  selectedCarreras.forEach((carreraId) => {
    formData.append('carreras_interes[]', carreraId);
  });

  $.ajax({
    url: 'ajax_perfilE.php',
    type: 'POST',
    data: formData,
    processData: false, // Importante para FormData
    contentType: false, // Importante para FormData
    dataType: 'json',
    success: function (response) {
      console.log('DEBUG (perfilE.js - updateProfile success):', response); // Debugging
      if (response.success) {
        Swal.fire('¡Éxito!', response.message, 'success').then(() => {
          loadStudentProfile(); // Recargar el perfil para mostrar los datos actualizados
        });
      } else {
        Swal.fire('Error', response.message, 'error');
        console.error('Error al actualizar perfil:', response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al actualizar el perfil:', { xhr, status, error });
      try {
        const errorResponse = JSON.parse(xhr.responseText);
        Swal.fire(
          'Error del Servidor',
          errorResponse.message ||
            'No se pudo actualizar el perfil. Intente de nuevo.',
          'error'
        );
      } catch (e) {
        Swal.fire(
          'Error de conexión',
          'No se pudo actualizar el perfil. Intente de nuevo.',
          'error'
        );
      }
    },
  });
});

/**
 * Maneja el envío del formulario de cambio de contraseña.
 */
$('#changePasswordForm').submit(function (event) {
  event.preventDefault();

  const formData = new FormData(this);
  formData.append('action', 'cambiar_contrasena');
  formData.append('idEstudiante', $('#idEstudiante').val()); // Asegurar que el ID se envía explícitamente

  $.ajax({
    url: 'ajax_perfilE.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (response) {
      console.log('DEBUG (perfilE.js - changePassword success):', response); // Debugging
      if (response.success) {
        Swal.fire('¡Éxito!', response.message, 'success').then(() => {
          $('#changePasswordForm')[0].reset(); // Limpiar el formulario
        });
      } else {
        Swal.fire('Error', response.message, 'error');
        console.error('Error al cambiar contraseña:', response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cambiar contraseña:', { xhr, status, error });
      try {
        const errorResponse = JSON.parse(xhr.responseText);
        Swal.fire(
          'Error del Servidor',
          errorResponse.message ||
            'No se pudo cambiar la contraseña. Intente de nuevo.',
          'error'
        );
      } catch (e) {
        Swal.fire(
          'Error de conexión',
          'No se pudo cambiar la contraseña. Intente de nuevo.',
          'error'
        );
      }
    },
  });
});

// Event listeners para los botones de edición
$(document).ready(function () {
  loadMasterData(); // Cargar datos maestros al inicio

  $('#editProfileBtn').on('click', function () {
    toggleEditMode(true);
  });

  $('#cancelEditBtn').on('click', function () {
    // Recargar el perfil para descartar los cambios no guardados
    loadStudentProfile();
    // No ocultar los botones de guardado/cancelar inmediatamente
    // Esto se maneja dentro de loadStudentProfile al togglear a modo de visualización.
  });
});

// Función para mostrar el perfil básico del estudiante (usada por el botón de navbar)
function mostrarPerfilEstudiante() {
  Swal.fire({
    title: 'Perfil de Estudiante',
    html: `
            <div class="text-start">
                <div class="mb-2"><strong>Usuario:</strong> ${'<?php echo htmlspecialchars($_SESSION["usuario"]); ?>'}</div>
                <div class="mb-2"><strong>ID de Estudiante:</strong> ${'<?php echo htmlspecialchars($_SESSION["usuario_id"]); ?>'}</div>
                <div class="mb-2"><strong>Tipo de Cuenta:</strong> <span class="badge bg-primary">Estudiante</span></div>
                <div class="mb-2"><strong>Sesión iniciada:</strong> ${'<?php echo date("d/m/Y H:i:s"); ?>'}</div>
                <div class="mb-2"><strong>Estado:</strong> <span class="badge bg-success">Activo</span></div>
            </div>
        `,
    icon: 'info',
    confirmButtonText: 'Cerrar',
    confirmButtonColor: '#0d6efd',
  });
}
