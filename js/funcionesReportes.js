let datosReporteActual = [];
let tituloReporteActual = '';

/**
 * Inicializa el módulo de reportes, configurando eventos y filtros.
 */
function inicializarReportes() {
  // Ocultar todos los filtros al inicio
  $('#filtroFechaInicio').hide();
  $('#filtroFechaFin').hide();
  $('#filtroCarrera').hide();
  $('#filtroEstado').hide();
  $('#contenedorReporte').hide();
  $('#btnDescargarPDF').hide();

  // Evento para el cambio de tipo de reporte
  $('#tipoReporte').on('change', function () {
    const tipoSeleccionado = $(this).val();
    mostrarOcultarFiltros(tipoSeleccionado);
  });

  // Evento para el botón de generar reporte
  $('#btnGenerarReporte').on('click', function () {
    generarReporte();
  });

  // Evento para el botón de descargar PDF
  $('#btnDescargarPDF').on('click', function () {
    descargarPDF();
  });
}

/**
 * Muestra u oculta los campos de filtro según el tipo de reporte seleccionado.
 * @param {string} tipoReporte - El valor del tipo de reporte seleccionado.
 */
function mostrarOcultarFiltros(tipoReporte) {
  // Ocultar todos los filtros primero
  $('#filtroFechaInicio').hide();
  $('#fechaInicio').val('');
  $('#filtroFechaFin').hide();
  $('#fechaFin').val('');
  $('#filtroCarrera').hide();
  $('#idCarrera').val('');
  $('#filtroEstado').hide();
  $('#idEstado').val('');

  // Mostrar filtros específicos
  switch (tipoReporte) {
    case 'ofertas_por_fecha':
      $('#filtroFechaInicio').show();
      $('#filtroFechaFin').show();
      break;
    case 'estudiantes_por_carrera':
      $('#filtroCarrera').show();
      break;
    case 'empresas_por_estado':
    case 'referencias_por_estado':
      $('#filtroEstado').show();
      break;
    // Otros casos si se añaden más reportes con filtros específicos
  }
  // Ocultar el contenedor del reporte y el botón de descarga al cambiar el tipo de reporte
  $('#contenedorReporte').hide();
  $('#btnDescargarPDF').hide();
  $('#areaReporte').html(
    '<p class="text-muted text-center py-5">Seleccione un tipo de reporte y genere para visualizarlo aquí.</p>'
  );
}

/**
 * Recopila los parámetros y genera el reporte mediante una llamada AJAX.
 */
function generarReporte() {
  const tipoReporte = $('#tipoReporte').val();
  if (!tipoReporte) {
    mostrarError('Por favor, seleccione un tipo de reporte.');
    return;
  }

  const parametros = {
    action: 'generar_reporte',
    tipo_reporte: tipoReporte,
  };

  // Añadir parámetros específicos según el tipo de reporte
  switch (tipoReporte) {
    case 'ofertas_por_fecha':
      const fechaInicio = $('#fechaInicio').val();
      const fechaFin = $('#fechaFin').val();
      if (!fechaInicio || !fechaFin) {
        mostrarError('Por favor, seleccione un rango de fechas.');
        return;
      }
      parametros.fecha_inicio = fechaInicio;
      parametros.fecha_fin = fechaFin;
      break;
    case 'estudiantes_por_carrera':
      const idCarrera = $('#idCarrera').val();
      if (idCarrera) {
        parametros.id_carrera = idCarrera;
      }
      break;
    case 'empresas_por_estado':
    case 'referencias_por_estado':
      const idEstado = $('#idEstado').val();
      if (idEstado) {
        parametros.id_estado = idEstado;
      }
      break;
  }

  // Mostrar spinner o indicador de carga
  $('#areaReporte').html(
    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2">Generando reporte...</p></div>'
  );
  $('#contenedorReporte').show();
  $('#btnDescargarPDF').hide(); // Ocultar mientras se genera

  $.ajax({
    url: '../CONTROLADOR/ajax_reportes.php',
    type: 'GET',
    data: parametros,
    dataType: 'json',
    success: function (respuesta) {
      if (respuesta.success) {
        $('#areaReporte').html(respuesta.html);
        datosReporteActual = respuesta.datos; // Guardar datos para el PDF
        tituloReporteActual = respuesta.titulo; // Guardar título para el PDF
        $('#btnDescargarPDF').show();
        mostrarExito(respuesta.message);
      } else {
        $('#areaReporte').html(
          '<p class="text-muted text-center py-5">Error al generar el reporte: ' +
            respuesta.message +
            '</p>'
        );
        mostrarError(respuesta.message);
        $('#btnDescargarPDF').hide();
      }
    },
    error: function (xhr, status, error) {
      console.error('Error AJAX al generar reporte:', error);
      $('#areaReporte').html(
        '<p class="text-muted text-center py-5">Error de conexión al generar el reporte.</p>'
      );
      mostrarError('Error de conexión al generar el reporte.');
      $('#btnDescargarPDF').hide();
    },
  });
}

/**
 * Descarga el reporte actual como un archivo PDF.
 */
function descargarPDF() {
  if (datosReporteActual.length === 0) {
    mostrarError('No hay datos para generar el PDF.');
    return;
  }

  const doc = new jspdf.jsPDF();
  let y = 10; // Posición inicial Y

  doc.setFontSize(16);
  doc.text(tituloReporteActual, 10, y);
  y += 10;

  doc.setFontSize(10);
  doc.text(`Fecha de Generación: ${new Date().toLocaleDateString()}`, 10, y);
  y += 10;

  // Obtener las cabeceras de la tabla del HTML generado para el PDF
  const headers = [];
  $('#areaReporte table thead th').each(function () {
    headers.push($(this).text());
  });

  // Convertir los datos a un formato de array de arrays para autoTable
  const data = datosReporteActual.map((row) => {
    const rowData = [];
    // Iterar sobre las cabeceras para asegurar el orden y la inclusión de datos
    headers.forEach((header) => {
      // Mapear el texto de la cabecera a la clave de datos
      let value = '';
      switch (header) {
        case 'ID':
          // Para ID, necesitamos el nombre de la columna real, que varía.
          // Asumimos que el primer campo de datosReporteActual es el ID.
          // Esto puede necesitar un mapeo más robusto si los IDs no son el primer campo.
          if (row.idOferta) value = row.idOferta;
          else if (row.idEstudiante) value = row.idEstudiante;
          else if (row.idEmpresa) value = row.idEmpresa;
          else if (row.idReferencia) value = row.idReferencia;
          break;
        case 'Título':
          value = row.titulo;
          break;
        case 'Empresa':
          value = row.empresa_nombre;
          break;
        case 'Modalidad':
          value = row.modalidad_nombre;
          break;
        case 'Tipo':
          value = row.tipo_oferta_nombre;
          break;
        case 'Publicación':
          value = row.fecha_publicacion;
          break;
        case 'Vencimiento':
          value = row.fecha_vencimiento;
          break;
        case 'Estado':
          value = row.estado_nombre;
          break;
        case 'Nombre Completo':
          value = `${row.nombre || ''} ${row.apellidos || ''}`.trim();
          break;
        case 'Documento':
          value = row.n_doc;
          break;
        case 'Carrera':
          value = row.carrera_nombre;
          break;
        case 'Fecha Registro':
          value = row.fecha_registro;
          break;
        case 'Teléfono':
          value = row.telefono;
          break;
        case 'Correo':
          value = row.correo;
          break;
        case 'Interesados':
          value = row.total_interesados;
          break;
        case 'ID Oferta':
          value = row.idOferta;
          break; // Para Top Ofertas
        case 'Estudiante':
          value = `${row.estudiante_nombre || ''} ${
            row.estudiante_apellidos || ''
          }`.trim();
          break;
        case 'Tipo Referencia':
          value = row.tipo_referencia_nombre;
          break;
        case 'Fecha Solicitud':
          value = row.fecha_solicitud;
          break;
        // Añadir más casos según las columnas de tus reportes
        default:
          value = '';
          break;
      }
      rowData.push(value);
    });
    return rowData;
  });

  // Usar jspdf-autotable para generar la tabla
  doc.autoTable({
    startY: y + 5,
    head: [headers],
    body: data,
    theme: 'striped',
    styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
    headStyles: { fillColor: [33, 37, 41], textColor: 255 }, // bg-dark
    alternateRowStyles: { fillColor: [248, 249, 250] }, // table-striped
    didDrawPage: function (data) {
      // Footer con número de página
      doc.setFontSize(8);
      doc.text(
        'Página ' + doc.internal.getNumberOfPages(),
        data.settings.margin.left,
        doc.internal.pageSize.height - 10
      );
    },
  });

  doc.save(
    `${tituloReporteActual.replace(/ /g, '_')}_${new Date()
      .toISOString()
      .slice(0, 10)}.pdf`
  );
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
