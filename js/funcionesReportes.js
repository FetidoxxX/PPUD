let currentReportData = null; // Almacena los datos del reporte actual para PDF
let currentReportTitle = ''; // Almacena el título del reporte actual para PDF
let activeTabId = 'ofertas-pane'; // Pestaña activa por defecto

// Las variables globales son inicializadas en gestion_reportes.php con 'const'.
// Por lo tanto, no se deben declarar aquí con 'let' para evitar el SyntaxError.
// Simplemente se usarán como variables globales disponibles.

/**
 * Inicializa la lógica de la página de reportes.
 */
function inicializarReportes() {
  // Eventos para los selectores de tipo de reporte en cada pestaña
  $('#tipoReporteOfertas').on('change', handleReportTypeChange);
  $('#tipoReporteEstudiantes').on('change', handleReportTypeChange);
  $('#tipoReporteEmpresas').on('change', handleReportTypeChange);
  $('#tipoReporteReferencias').on('change', handleReportTypeChange);

  // Eventos para los botones de generar reporte en cada pestaña
  $('#btnGenerarReporteOfertas').on('click', function () {
    generarReporte('ofertas-pane');
  });
  $('#btnGenerarReporteEstudiantes').on('click', function () {
    generarReporte('estudiantes-pane');
  });
  $('#btnGenerarReporteEmpresas').on('click', function () {
    generarReporte('empresas-pane');
  });
  $('#btnGenerarReporteReferencias').on('click', function () {
    generarReporte('referencias-pane');
  });

  // Evento para el botón de descargar PDF
  $('#btnDescargarPDF').on('click', descargarPDF);

  // Manejar el cambio de pestaña para resetear los filtros y el reporte
  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    activeTabId = $(e.target).attr('data-bs-target').substring(1); // Elimina el '#'
    resetReportDisplay();
    // Ocultar todos los filtros al cambiar de pestaña
    $('.filtro-container').hide();
    // Resetear selectores a la opción por defecto
    $('.report-type-select').val('');
    $('.filter-select').val('');
    $('.filter-input').val('');
  });

  // Inicializar tooltips de Bootstrap
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Cargar las opciones iniciales para los filtros (si ya hay datos globales)
  // Ofertas
  if (typeof GLOBAL_MODALIDADES !== 'undefined') {
    populateSelect(
      'idModalidadOfertas',
      GLOBAL_MODALIDADES,
      'id_modalidad',
      ''
    );
  }
  if (typeof GLOBAL_EMPRESAS !== 'undefined') {
    populateSelect('idEmpresaOfertas', GLOBAL_EMPRESAS, 'idEmpresa', '');
  }
  // Filtrar estados para Ofertas: activo, inactivo, vencido
  if (typeof GLOBAL_ESTADOS !== 'undefined') {
    const estadosOfertas = GLOBAL_ESTADOS.filter((estado) =>
      ['activo', 'inactivo', 'vencida'].includes(estado.nombre.toLowerCase())
    );
    populateSelect('idEstadoOfertas', estadosOfertas, 'id_estado', '');
  }

  // Estudiantes
  if (typeof GLOBAL_CARRERAS !== 'undefined') {
    populateSelect('idCarreraEstudiantes', GLOBAL_CARRERAS, 'id_carrera', '');
  }
  // Filtrar estados para Estudiantes: activo, inactivo
  if (typeof GLOBAL_ESTADOS !== 'undefined') {
    const estadosEstudiantes = GLOBAL_ESTADOS.filter((estado) =>
      ['activo', 'inactivo'].includes(estado.nombre.toLowerCase())
    );
    populateSelect('idEstadoEstudiantes', estadosEstudiantes, 'id_estado', '');
  }

  // Empresas
  // Filtrar estados para Empresas: activo, inactivo
  if (typeof GLOBAL_ESTADOS !== 'undefined') {
    const estadosEmpresas = GLOBAL_ESTADOS.filter((estado) =>
      ['activo', 'inactivo'].includes(estado.nombre.toLowerCase())
    );
    populateSelect('idEstadoEmpresas', estadosEmpresas, 'id_estado', '');
  }

  // Referencias
  if (typeof GLOBAL_TIPOS_REFERENCIA !== 'undefined') {
    populateSelect(
      'idTipoReferenciaReferencias',
      GLOBAL_TIPOS_REFERENCIA,
      'id_tipo_referencia',
      ''
    );
  }
  // Filtrar estados para Referencias: activo, inactivo
  if (typeof GLOBAL_ESTADOS !== 'undefined') {
    const estadosReferencias = GLOBAL_ESTADOS.filter((estado) =>
      ['activo', 'inactivo'].includes(estado.nombre.toLowerCase())
    );
    populateSelect('idEstadoReferencias', estadosReferencias, 'id_estado', '');
  }
  if (typeof GLOBAL_EMPRESAS !== 'undefined') {
    populateSelect('idEmpresaReferencias', GLOBAL_EMPRESAS, 'idEmpresa', '');
  }
  if (typeof GLOBAL_ESTUDIANTES !== 'undefined') {
    populateSelect(
      'idEstudianteReferencias',
      GLOBAL_ESTUDIANTES,
      'idEstudiante',
      ''
    );
  }
}

/**
 * Maneja el evento change de los selectores de tipo de reporte.
 * Muestra/oculta los filtros adicionales según el tipo de reporte seleccionado.
 */
function handleReportTypeChange(event) {
  const selectedReportType = $(event.target).val();
  const tabId = $(event.target).closest('.tab-pane').attr('id');

  // Ocultar todos los filtros de la pestaña actual primero
  $(`#${tabId} .filtro-container`).hide();
  // Resetear valores de los filtros
  $(`#${tabId} .filter-select`).val('');
  $(`#${tabId} .filter-input`).val('');

  // Mostrar los filtros relevantes
  if (tabId === 'ofertas-pane') {
    if (selectedReportType === 'ofertas_por_fecha') {
      $('#filtroFechaInicioOfertasContainer').show();
      $('#filtroFechaFinOfertasContainer').show();
    } else if (selectedReportType === 'ofertas_por_modalidad') {
      $('#filtroModalidadOfertasContainer').show();
    } else if (selectedReportType === 'ofertas_por_empresa') {
      $('#filtroEmpresaOfertasContainer').show();
    } else if (selectedReportType === 'ofertas_por_estado_oferta') {
      $('#filtroEstadoOfertasContainer').show();
    } else if (selectedReportType === 'top_ofertas_interes') {
      $('#filtroLimiteTopOfertasContainer').show();
    }
  } else if (tabId === 'estudiantes-pane') {
    if (selectedReportType === 'estudiantes_registrados') {
      // No hay filtros adicionales para este tipo de reporte
    } else if (selectedReportType === 'estudiantes_por_carrera') {
      $('#filtroCarreraEstudiantesContainer').show();
    } else if (selectedReportType === 'estudiantes_por_estado') {
      $('#filtroEstadoEstudiantesContainer').show();
    } else if (selectedReportType === 'top_estudiantes_interesados_ofertas') {
      $('#filtroLimiteTopEstudiantesContainer').show();
    }
  } else if (tabId === 'empresas-pane') {
    if (selectedReportType === 'empresas_por_estado') {
      $('#filtroEstadoEmpresasContainer').show();
    } else if (selectedReportType === 'empresas_con_mas_ofertas') {
      $('#filtroLimiteTopEmpresasOfertasContainer').show();
    } else if (selectedReportType === 'empresas_con_mas_referencias_emitidas') {
      $('#filtroLimiteTopEmpresasReferenciasContainer').show();
    }
  } else if (tabId === 'referencias-pane') {
    if (selectedReportType === 'referencias_por_estado') {
      $('#filtroEstadoReferenciasContainer').show();
    } else if (selectedReportType === 'referencias_por_tipo') {
      $('#filtroTipoReferenciaReferenciasContainer').show();
    } else if (selectedReportType === 'referencias_por_empresa') {
      $('#filtroEmpresaReferenciasContainer').show();
    } else if (tipoReporte === 'referencias_por_estudiante') {
      $('#filtroEstudianteReferenciasContainer').show();
    }
  }
}

/**
 * Genera el reporte según la pestaña activa y los filtros seleccionados.
 * @param {string} tabId - El ID de la pestaña activa (e.g., 'ofertas-pane').
 */
function generarReporte(tabId) {
  let tipoReporte = '';
  let params = {
    action: 'generar_reporte',
  };
  let isValid = true;

  // Determinar el tipo de reporte y los parámetros específicos de la pestaña
  if (tabId === 'ofertas-pane') {
    tipoReporte = $('#tipoReporteOfertas').val();
    params.tipo_reporte = tipoReporte;

    if (!tipoReporte) {
      isValid = false;
    } else if (tipoReporte === 'ofertas_por_fecha') {
      const fechaInicio = $('#fechaInicioOfertas').val();
      const fechaFin = $('#fechaFinOfertas').val();
      if (!fechaInicio || !fechaFin) {
        mostrarError(
          'Por favor, ingrese ambas fechas para el reporte por fecha.'
        );
        isValid = false;
      } else {
        params.fecha_inicio = fechaInicio;
        params.fecha_fin = fechaFin;
      }
    } else if (tipoReporte === 'ofertas_por_modalidad') {
      const idModalidad = $('#idModalidadOfertas').val();
      if (idModalidad) params.id_modalidad = idModalidad;
    } else if (tipoReporte === 'ofertas_por_empresa') {
      const idEmpresa = $('#idEmpresaOfertas').val();
      if (idEmpresa) params.id_empresa = idEmpresa;
    } else if (tipoReporte === 'ofertas_por_estado_oferta') {
      const idEstadoOferta = $('#idEstadoOfertas').val();
      if (idEstadoOferta) params.id_estado_oferta = idEstadoOferta;
    } else if (tipoReporte === 'top_ofertas_interes') {
      const limiteTop = $('#limiteTopOfertas').val();
      if (limiteTop) params.limite_top = limiteTop;
    }
  } else if (tabId === 'estudiantes-pane') {
    tipoReporte = $('#tipoReporteEstudiantes').val();
    params.tipo_reporte = tipoReporte;

    if (!tipoReporte) {
      isValid = false;
    } else if (tipoReporte === 'estudiantes_por_carrera') {
      const idCarrera = $('#idCarreraEstudiantes').val();
      if (idCarrera) params.id_carrera = idCarrera;
    } else if (tipoReporte === 'estudiantes_por_estado') {
      const idEstadoEstudiante = $('#idEstadoEstudiantes').val();
      if (idEstadoEstudiante) params.id_estado_estudiante = idEstadoEstudiante;
    } else if (tipoReporte === 'top_estudiantes_interesados_ofertas') {
      const limiteTop = $('#limiteTopEstudiantes').val();
      if (limiteTop) params.limite_top = limiteTop;
    }
  } else if (tabId === 'empresas-pane') {
    tipoReporte = $('#tipoReporteEmpresas').val();
    params.tipo_reporte = tipoReporte;

    if (!tipoReporte) {
      isValid = false;
    } else if (tipoReporte === 'empresas_por_estado') {
      const idEstado = $('#idEstadoEmpresas').val();
      if (idEstado) params.id_estado = idEstado;
    } else if (tipoReporte === 'empresas_con_mas_ofertas') {
      const limiteTop = $('#limiteTopEmpresasOfertas').val();
      if (limiteTop) params.limite_top = limiteTop;
    } else if (tipoReporte === 'empresas_con_mas_referencias_emitidas') {
      const limiteTop = $('#limiteTopEmpresasReferencias').val();
      if (limiteTop) params.limite_top = limiteTop;
    }
  } else if (tabId === 'referencias-pane') {
    tipoReporte = $('#tipoReporteReferencias').val(); // <--- Captura el valor del selector principal
    params.tipo_reporte = tipoReporte;

    if (!tipoReporte) {
      isValid = false;
    } else if (tipoReporte === 'referencias_por_estado') {
      const idEstado = $('#idEstadoReferencias').val();
      if (idEstado) params.id_estado = idEstado;
    } else if (tipoReporte === 'referencias_por_tipo') {
      const idTipoReferencia = $('#idTipoReferenciaReferencias').val(); // <--- Captura el valor del filtro dependiente
      if (idTipoReferencia) params.id_tipo_referencia = idTipoReferencia;
    } else if (tipoReporte === 'referencias_por_empresa') {
      const idEmpresa = $('#idEmpresaReferencias').val(); // Usar el ID correcto
      if (idEmpresa) params.id_empresa = idEmpresa;
    } else if (tipoReporte === 'referencias_por_estudiante') {
      const idEstudiante = $('#idEstudianteReferencias').val(); // Usar el ID correcto
      if (idEstudiante) params.id_estudiante = idEstudiante;
    }
  }

  if (!isValid) {
    mostrarError(
      'Por favor, seleccione un tipo de reporte y complete los filtros necesarios.'
    );
    return;
  }

  // Mostrar spinner de carga
  $('#reporteResultadosContainer').html(
    '<div class="text-center my-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2">Generando reporte...</p></div>'
  );
  $('#contenedorReporte').show(); // Mostrar el contenedor del reporte

  $.ajax({
    url: '../CONTROLADOR/ajax_reportes.php',
    type: 'GET', // Usamos GET ya que son solicitudes de datos
    data: params,
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success) {
        currentReportData = respuesta.datos;
        currentReportTitle = respuesta.titulo;

        if (currentReportData && currentReportData.length > 0) {
          $('#reporteResultadosContainer').html(respuesta.html);
          $('#tituloReporteDisplay').text(respuesta.titulo); // Actualizar el título del reporte
          $('#btnDescargarPDF').show(); // Mostrar botón de descarga
          mostrarExito(respuesta.message);
        } else {
          // Si no hay datos, mostrar un mensaje al usuario
          $('#reporteResultadosContainer').html(
            '<div class="alert alert-info text-center">' +
              '<i class="fas fa-info-circle me-2"></i> No se encontraron resultados para el reporte seleccionado con los parámetros actuales. ' +
              'Por favor, intente con otros valores o criterios de búsqueda.' +
              '</div>'
          );
          $('#tituloReporteDisplay').text(
            respuesta.titulo + ' (Sin Resultados)'
          );
          $('#btnDescargarPDF').hide(); // Ocultar botón de descarga si no hay datos
          mostrarExito('Reporte generado, pero sin resultados.');
        }
      } else {
        mostrarError(respuesta.message || 'Error al generar el reporte.');
        $('#reporteResultadosContainer').html(
          '<div class="alert alert-warning">No se pudo generar el reporte: ' +
            (respuesta.message || 'Error desconocido') +
            '</div>'
        );
        $('#tituloReporteDisplay').text('📄 Resultado del Reporte'); // Resetear el título
        $('#btnDescargarPDF').hide();
      }
    },
    error: function (xhr, status, error) {
      console.error('Error AJAX al generar reporte:', error);
      mostrarError('Error de conexión al servidor al generar el reporte.');
      $('#reporteResultadosContainer').html(
        '<div class="alert alert-danger">Error al cargar el reporte. Por favor, intente de nuevo.</div>'
      );
      $('#tituloReporteDisplay').text('📄 Resultado del Reporte'); // Resetear el título
      $('#btnDescargarPDF').hide();
    },
  });
}

/**
 * Descarga el reporte actual como un archivo PDF con diseño moderno y estético.
 */
function descargarPDF() {
  if (!currentReportData || currentReportData.length === 0) {
    mostrarError('No hay datos de reporte para descargar.');
    return;
  }

  // Initialize jsPDF with landscape orientation and A4 size
  const doc = new jspdf.jsPDF('l', 'mm', 'a4');
  const pageWidth = doc.internal.pageSize.width;
  const pageHeight = doc.internal.pageSize.height;

  // --- MODERN HEADER WITH GRADIENT ---
  // Blue color from pruebaAdmin.php: #0d6efd (RGB: 13, 110, 253)
  const headerColor = [13, 110, 253];
  const gradientHeight = 50;
  const gradientSteps = 20;

  for (let i = 0; i < gradientSteps; i++) {
    const opacity = 1 - (i / gradientSteps) * 0.3;
    // Use the base header color for the gradient
    const r = Math.floor(headerColor[0] + (i / gradientSteps) * 20);
    const g = Math.floor(headerColor[1] + (i / gradientSteps) * 30);
    const b = Math.floor(headerColor[2] + (i / gradientSteps) * 0); // Keep blue

    doc.setFillColor(r, g, b);
    doc.setGState(new doc.GState({ opacity: opacity }));
    doc.rect(
      0,
      i * (gradientHeight / gradientSteps),
      pageWidth,
      gradientHeight / gradientSteps,
      'F'
    );
  }

  // Reset opacity
  doc.setGState(new doc.GState({ opacity: 1 }));

  // --- LOGO AND HEADER INFORMATION ---
  const logoSize = 35;
  const logoX = 15;
  const logoY = 8;

  // Add logo
  doc.addImage('../IMG/logoPPUD.png', 'PNG', logoX, logoY, logoSize, logoSize);

  // Header texts with improved typography
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(36); // Increased font size for main title
  doc.setTextColor(255, 255, 255);
  doc.text('PPUD', pageWidth / 2, 20, { align: 'center' }); // Centered

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(14); // Slightly larger for main subtitle
  doc.text('Portal de Gestión de Prácticas y Pasantías', pageWidth / 2, 30, {
    align: 'center',
  }); // Centered
  doc.setFontSize(12); // Slightly larger for secondary subtitle
  doc.text(
    'Universidad Distrital Francisco José de Caldas',
    pageWidth / 2,
    38,
    { align: 'center' }
  ); // Centered

  // Date with modern style - Moved below the University name and centered
  const now = new Date();
  const date = now.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
  const time = now.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
  });

  doc.setFontSize(9);
  doc.setTextColor(230, 230, 230);
  doc.text(
    `Reporte generado automáticamente el ${date} a las ${time}`,
    pageWidth / 2,
    43,
    { align: 'center' }
  ); // Centered

  // --- DECORATIVE LINE (REMOVED) ---
  // doc.setDrawColor(255, 255, 255);
  // doc.setLineWidth(0.5);
  // doc.line(15, 45, pageWidth - 15, 45);

  // --- REPORT TITLE WITH MODERN STYLE (REMOVED AS SEPARATE TEXT) ---
  // doc.setTextColor(44, 62, 80);
  // doc.setFont('helvetica', 'bold');
  // doc.setFontSize(20);
  const titleY = 65; // Still used as a reference point for table start

  // Decorative line under the title (REMOVED)
  // doc.setDrawColor(52, 152, 219);
  // doc.setLineWidth(3);
  // doc.line(15, titleY + 5, 80, titleY + 5);

  // Prepare user info
  const userId =
    typeof GLOBAL_USER_ID !== 'undefined' ? GLOBAL_USER_ID : 'ID_Desconocido';
  const userName =
    typeof GLOBAL_USER_NAME !== 'undefined'
      ? GLOBAL_USER_NAME
      : 'Nombre del Usuario Desconocido';

  // --- REPORT METADATA TABLE ---
  // Organized into two rows, two columns visually
  const reportMetadata = [
    ['Tipo de Informe', currentReportTitle, 'Generado por', 'Sistema PPUD'],
    [
      'Total de Registros',
      currentReportData.length,
      'Usuario',
      `${userId} - ${userName}`,
    ],
  ];

  doc.autoTable({
    body: reportMetadata,
    startY: 55, // Adjusted startY to place it closer to the main header
    theme: 'plain', // Simple theme for a key-value list
    styles: {
      fontSize: 10,
      cellPadding: 3, // Increased padding for better aesthetics
      textColor: [50, 50, 50], // Slightly darker text for values
      lineColor: [220, 220, 220], // Lighter lines for the internal grid
      lineWidth: 0.1,
    },
    columnStyles: {
      0: {
        fontStyle: 'bold',
        textColor: [44, 62, 80],
        fillColor: [230, 240, 250],
        cellWidth: 45,
        halign: 'left',
      }, // Key column 1 (left side)
      1: { cellWidth: 'auto', halign: 'left' }, // Value column 1 (left side)
      2: {
        fontStyle: 'bold',
        textColor: [44, 62, 80],
        fillColor: [230, 240, 250],
        cellWidth: 45,
        halign: 'left',
      }, // Key column 2 (right side)
      3: { cellWidth: 'auto', halign: 'left' }, // Value column 2 (right side)
    },
    margin: { horizontal: 'auto' }, // Center the table horizontally
    tableWidth: 'wrap', // Let autoTable determine the width
    tableLineColor: [180, 180, 180], // Subtle border around the entire table
    tableLineWidth: 0.2,
  });

  // --- PREPARE DATA FOR THE MAIN TABLE ---
  const headers = Object.keys(currentReportData[0]).map((key) => {
    // Formatear nombres de columnas para que sean más legibles en el PDF
    // Eliminar caracteres no alfanuméricos y luego capitalizar cada palabra
    return key
      .replace(/[^a-zA-Z0-9_ ]/g, '') // Eliminar caracteres no alfanuméricos (excepto guiones bajos y espacios)
      .replace(/_/g, ' ') // Reemplazar guiones bajos por espacios
      .split(' ')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  });

  const data = currentReportData.map((row) =>
    Object.values(row).map((value) => {
      // Limpiar valores de datos para eliminar caracteres no procesables
      return String(value).replace(/[^a-zA-Z0-9_ \-.,/():]/g, ''); // Permite letras, números, espacios, guiones, puntos, comas, barras, paréntesis, dos puntos
    })
  );

  // --- TABLA CON DISEÑO ORIGINAL Y TONALIDADES AZULES ---
  doc.autoTable({
    head: [headers],
    body: data,
    startY: doc.autoTable.previous.finalY + 10, // Start after the metadata table with some spacing
    theme: 'striped', // Tema 'striped' para el aspecto original
    styles: {
      fontSize: 8,
      cellPadding: 2,
      textColor: [0, 0, 0], // Texto negro estándar
      lineColor: [180, 180, 180], // Líneas grises claras
      lineWidth: 0.1,
    },
    headStyles: {
      fillColor: [52, 152, 219], // Azul para el fondo del encabezado
      textColor: [255, 255, 255], // Texto blanco en el encabezado
      fontStyle: 'bold',
      fontSize: 9, // Ajustado a 9 para ser acorde al cuerpo de la tabla
      cellPadding: 4, // Ajustado el padding para mejor estética
    },
    alternateRowStyles: {
      fillColor: [240, 248, 255], // AliceBlue para filas alternas (tonalidad azul)
    },
    didDrawPage: function (data) {
      // --- FOOTER MODERNO ---
      const footerY = pageHeight - 20;

      // Línea decorativa en el footer
      doc.setDrawColor(220, 220, 220);
      doc.setLineWidth(0.5);
      doc.line(15, footerY - 5, pageWidth - 15, footerY - 5);

      // Información del footer
      doc.setFontSize(8);
      doc.setTextColor(108, 117, 125);
      doc.setFont('helvetica', 'normal');

      // Lado izquierdo del footer
      doc.text('PPUD - Portal de Gestión Universitaria', 15, footerY);

      // Lado derecho del footer - número de página
      const pageNum = `Página ${doc.internal.getNumberOfPages()}`;
      const pageNumWidth = doc.getTextWidth(pageNum);
      doc.text(pageNum, pageWidth - 15 - pageNumWidth, footerY);

      // URL o información adicional
      doc.setFontSize(7);
      doc.setTextColor(150, 150, 150);
      doc.text('www.udistrital.edu.co', 15, footerY + 8);
    },
    margin: { top: 10, right: 15, bottom: 30, left: 15 },
  });

  // --- MARCA DE AGUA (OPCIONAL) ---
  doc.setGState(new doc.GState({ opacity: 0.1 }));
  doc.setFontSize(60);
  doc.setTextColor(200, 200, 200);
  doc.text('PPUD', pageWidth / 2 - 30, pageHeight / 2, {
    angle: 45,
    align: 'center',
  });
  doc.setGState(new doc.GState({ opacity: 1 }));

  // --- GUARDAR ARCHIVO ---
  const fileName = currentReportTitle
    .replace(/ /g, '_')
    .replace(/[^\w\s-]/g, '')
    .toLowerCase();

  const timestamp = new Date().toISOString().slice(0, 10);
  doc.save(`${fileName}_${timestamp}.pdf`);

  // Mostrar mensaje de éxito
  console.log('✅ PDF generado exitosamente');
}

/**
 * Resetea la visualización del reporte y oculta el botón de descarga.
 */
function resetReportDisplay() {
  $('#reporteResultadosContainer').empty();
  $('#btnDescargarPDF').hide();
  currentReportData = null;
  currentReportTitle = '';
  $('#tituloReporteDisplay').text('📄 Resultado del Reporte'); // Resetear el título
  $('#contenedorReporte').hide(); // Ocultar el contenedor del reporte
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
 * Rellena un elemento <select> con datos.
 * @param {string} selectId - El ID del elemento select.
 * @param {Array} data - Array de objetos con 'id' y 'nombre'.
 * @param {string} idKey - La clave del ID en los objetos de datos (ej. 'id_estado', 'idEmpresa').
 * @param {string} selectedValue - El valor que debe estar seleccionado por defecto.
 */
function populateSelect(selectId, data, idKey, selectedValue = '') {
  const selectElement = $(`#${selectId}`);
  selectElement.empty();
  selectElement.append('<option value="">Seleccione...</option>'); // Opción por defecto
  data.forEach((item) => {
    const isSelected = item[idKey] == selectedValue ? 'selected' : '';
    // Asegurarse de que 'nombre' es la propiedad correcta para mostrar
    const displayValue =
      item.nombre ||
      item.titulo ||
      item.nombres ||
      (item.nombre && item.apellidos ? item.nombre + ' ' + item.apellidos : ''); // Adaptar según la estructura del dato
    if (displayValue) {
      selectElement.append(
        `<option value="${item[idKey]}" ${isSelected}>${displayValue}</option>`
      );
    }
  });
}
