// Variable global para almacenar las ofertas paginadas y el total
let currentPage = 0;
const itemsPerPage = 9; // Mostrar 9 ofertas por página en cards
let totalOffers = 0;

/**
 * Carga las ofertas activas desde el servidor para el estudiante.
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
    url: '../CONTROLADOR/ajax_ofertaE.php', // Nuevo archivo AJAX para estudiante
    type: 'GET',
    data: {
      action: 'obtener_ofertas_activas',
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
    container.append(`
      <div class="col-12 text-center py-4">
        <div class="text-muted">
          <div class="display-1">😞</div>
          <h5>No hay ofertas de prácticas o pasantías disponibles.</h5>
          <p class="mb-0">Vuelve más tarde o ajusta tu búsqueda.</p>
        </div>
      </div>
    `);
    $('#loadMoreBtn').hide();
    return;
  }

  ofertas.forEach((oferta) => {
    const interesBtnClass = oferta.interes_mostrado
      ? 'btn-success'
      : 'btn-outline-success';
    const interesBtnText = oferta.interes_mostrado
      ? '<i class="fas fa-star me-2"></i>Interesado'
      : '<i class="far fa-star me-2"></i>Me Interesa';

    const cardHtml = `
            <div class="col">
                <div class="card h-100 shadow-sm border-0 card-offer">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${oferta.titulo}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                          <a href="#" class="company-link" onclick="viewCompanyProfile('${
                            oferta.empresa_idEmpresa
                          }', '${encodeURIComponent(oferta.empresa_nombre)}')">
                            ${oferta.empresa_nombre}
                          </a>
                        </h6>
                        <p class="card-text">${oferta.descripcion.substring(
                          0,
                          100
                        )}${oferta.descripcion.length > 100 ? '...' : ''}</p>
                        <ul class="list-unstyled flex-grow-1">
                            <li><strong><i class="fas fa-handshake me-1 text-primary"></i>Modalidad:</strong> ${
                              oferta.modalidad_nombre
                            }</li>
                            <li><strong><i class="fas fa-map-marker-alt me-1 text-primary"></i>Área:</strong> ${
                              oferta.area_conocimiento_nombre
                            }</li>
                            <li><strong><i class="fas fa-calendar-alt me-1 text-primary"></i>Vencimiento:</strong> ${
                              oferta.fecha_vencimiento
                            }</li>
                        </ul>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-info rounded-pill" onclick="viewOfferDetail(${
                              oferta.idOferta
                            })">
                                <i class="fas fa-info-circle me-1"></i> Ver Detalles
                            </button>
                            <button class="btn btn-sm ${interesBtnClass} rounded-pill" data-oferta-id="${
      oferta.idOferta
    }" data-interes-mostrado="${
      oferta.interes_mostrado ? 'true' : 'false'
    }" onclick="toggleInteres(this)">
                                ${interesBtnText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    container.append(cardHtml);
  });
  updateLoadMoreButtonVisibility();
}

/**
 * Abre el modal de detalle de oferta y carga la información.
 * @param {number} idOferta - ID de la oferta a visualizar.
 */
function viewOfferDetail(idOferta) {
  $.ajax({
    url: '../CONTROLADOR/ajax_ofertaE.php',
    type: 'GET',
    data: {
      action: 'obtener_oferta_detalle',
      id: idOferta,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success && response.data) {
        const oferta = response.data;
        // Llenar campos del modal
        $('#modal_titulo').text(oferta.titulo);
        // MODIFICADO: Pasar el ID de la empresa y el nombre a la función viewCompanyProfile
        $('#modal_empresa_nombre')
          .text(oferta.empresa_nombre)
          .attr(
            'onclick',
            `viewCompanyProfile('${
              oferta.empresa_idEmpresa
            }', '${encodeURIComponent(oferta.empresa_nombre)}')`
          );
        $('#modal_descripcion').text(oferta.descripcion);
        $('#modal_requisitos').text(oferta.requisitos);
        $('#modal_beneficios').text(oferta.beneficios || 'No especificado');
        $('#modal_modalidad').text(oferta.modalidad_nombre);
        $('#modal_tipo_oferta').text(oferta.tipo_oferta_nombre);
        $('#modal_area_conocimiento').text(oferta.area_conocimiento_nombre);
        $('#modal_duracion_meses').text(oferta.duracion_meses);
        $('#modal_horario').text(oferta.horario || 'No especificado');
        $('#modal_remuneracion').text(oferta.remuneracion || 'No especificado');
        $('#modal_semestre_minimo').text(
          oferta.semestre_minimo || 'No especificado'
        );
        $('#modal_promedio_minimo').text(
          oferta.promedio_minimo || 'No especificado'
        );
        $('#modal_cupos_disponibles').text(oferta.cupos_disponibles);
        $('#modal_habilidades_requeridas').text(
          oferta.habilidades_requeridas || 'No especificado'
        );
        $('#modal_fecha_vencimiento').text(oferta.fecha_vencimiento);
        $('#modal_carreras_dirigidas').text(
          oferta.carreras_dirigidas_nombres || 'No especificado'
        );

        // Actualizar botón de interés
        const btnInteres = $('#btnInteres');
        btnInteres.attr('data-oferta-id', oferta.idOferta);
        if (oferta.interes_mostrado) {
          btnInteres
            .removeClass('btn-outline-success')
            .addClass('btn-success')
            .html('<i class="fas fa-star me-2"></i>Interesado');
          btnInteres.attr('data-interes-mostrado', 'true');
        } else {
          btnInteres
            .removeClass('btn-success')
            .addClass('btn-outline-success')
            .html('<i class="far fa-star me-2"></i>Me Interesa');
          btnInteres.attr('data-interes-mostrado', 'false');
        }

        new bootstrap.Modal(
          document.getElementById('detalleOfertaModal')
        ).show();
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.fire(
        'Error de conexión',
        'No se pudo cargar el detalle de la oferta. Intente de nuevo.',
        'error'
      );
    },
  });
}

/**
 * Abre el modal de perfil de empresa para estudiantes.
 * @param {string} idEmpresa - ID de la empresa.
 * @param {string} empresaNombre - Nombre de la empresa.
 */
function viewCompanyProfile(idEmpresa, empresaNombre) {
  // Cerrar el modal de oferta si está abierto para evitar superposición
  const ofertaModal = bootstrap.Modal.getInstance(
    document.getElementById('detalleOfertaModal')
  );
  if (ofertaModal) {
    ofertaModal.hide();
  }

  // Llamar a la función en funcionesReferenciasEstudiante.js para cargar y mostrar el modal de perfil de empresa
  // Se decodifica el nombre de la empresa por si contenía caracteres especiales codificados por encodeURIComponent
  loadCompanyProfileModalForStudent(
    idEmpresa,
    decodeURIComponent(empresaNombre)
  );
}

/**
 * Alterna el estado de "interés" de una oferta para el estudiante.
 * @param {HTMLElement} buttonElement - El botón que fue clickeado.
 */
function toggleInteres(buttonElement) {
  const idOferta = $(buttonElement).data('oferta-id');
  const interesMostrado = $(buttonElement).data('interes-mostrado');
  const action = interesMostrado ? 'eliminar_interes' : 'mostrar_interes';

  $.ajax({
    url: '../CONTROLADOR/ajax_ofertaE.php',
    type: 'POST',
    data: {
      action: action,
      idOferta: idOferta,
    },
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        // Actualizar el estado del botón en el modal y en la tarjeta (si está visible)
        if (action === 'mostrar_interes') {
          $(buttonElement)
            .removeClass('btn-outline-success')
            .addClass('btn-success')
            .html('<i class="fas fa-star me-2"></i>Interesado');
          $(buttonElement).attr('data-interes-mostrado', 'true');
          Swal.fire('¡Interés Registrado!', response.message, 'success');
        } else {
          $(buttonElement)
            .removeClass('btn-success')
            .addClass('btn-outline-success')
            .html('<i class="far fa-star me-2"></i>Me Interesa');
          $(buttonElement).attr('data-interes-mostrado', 'false');
          Swal.fire('¡Interés Eliminado!', response.message, 'info');
        }
        // Llamar a cargarOfertas para que la lista se actualice y el borde de la tarjeta cambie
        cargarOfertas(false); // Recargar todas las ofertas
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function (xhr, status, error) {
      Swal.fire(
        'Error de conexión',
        'No se pudo registrar/eliminar el interés. Intente de nuevo.',
        'error'
      );
    },
  });
}
