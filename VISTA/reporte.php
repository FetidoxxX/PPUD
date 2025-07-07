<?php
// Incluir las clases necesarias
// Las rutas son relativas a la ubicación de reporte.php (dentro de VISTA/)
require_once '../MODELO/class_conec.php';
require_once '../MODELO/class_estudiante.php';

// Crear una instancia de la clase Estudiante
$estudianteObj = new Estudiante();

// Obtener todos los estudiantes
// El método obtenerTodos() de class_estudiante.php ya trae los datos necesarios,
// incluyendo 'estado_nombre' y 'carrera_nombre' si las uniones son exitosas.
$estudiantes = $estudianteObj->obtenerTodos();

// Preparar los datos para jsPDF AutoTable
$data_for_pdf = [];
foreach ($estudiantes as $est) {
  // Formatear los datos del estudiante para la tabla PDF
  // Asegúrate de que las claves del array $est coincidan con los nombres de las columnas en tu base de datos
  // o con los alias definidos en el método obtenerTodos() de class_estudiante.php.
  $data_for_pdf[] = [
    htmlspecialchars($est['idEstudiante']),
    htmlspecialchars($est['nombre'] . ' ' . $est['apellidos']),
    htmlspecialchars($est['correo']),
    // Usamos 'carrera_nombre' si existe, de lo contrario 'estado_nombre', o 'Desconocido'
    htmlspecialchars($est['carrera_nombre'] ?? ($est['estado_nombre'] ?? 'Desconocido'))
  ];
}

// Codificar el array PHP a una cadena JSON para usar en JavaScript
$json_data_for_pdf = json_encode($data_for_pdf);

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte de Estudiantes</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="../sw/dist/sweetalert2.min.css">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- jQuery -->
  <script type="text/javascript" src="../js/jquery-3.6.1.min.js"></script>
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="../sw/dist/sweetalert2.min.js"></script>
  <!-- jsPDF library (CDN) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
    }

    .container {
      margin-top: 50px;
    }

    .panel {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }

    .navbar {
      margin-bottom: 30px;
    }

    .btn-generate {
      background-color: #28a745;
      /* Green color for generate button */
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 1.1em;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-generate:hover {
      background-color: #218838;
      /* Darker green on hover */
    }

    /* Estilos para la tabla HTML, si decides mostrarla */
    #tablaEstudiantes {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    #tablaEstudiantes th,
    #tablaEstudiantes td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    #tablaEstudiantes th {
      background-color: #f2f2f2;
    }
  </style>
</head>

<body>
  <br>
  <div class="container">
    <div class="panel panel-default">
      <nav class="navbar navbar-dark bg-dark rounded-top">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">REPORTE DE ESTUDIANTES</a>
        </div>
      </nav>

      <div class="panel-body">
        <div class="row">
          <div class="col-md-12 text-center">
            <h1>Generar Reporte de Estudiantes en PDF</h1>
            <p class="lead">Haga clic en el botón para generar un PDF con la lista completa de estudiantes.</p>
            <br>
          </div>
          <div class="col-md-12 text-center">
            <button id="GenerarReporteEstudiantes" class="btn btn-generate">
              <i class="fas fa-file-pdf me-2"></i> Generar PDF de Estudiantes
            </button>
            <br>
          </div>
        </div>

        <!-- Tabla HTML para mostrar los datos, opcionalmente puedes quitarla si solo quieres el PDF -->
        <div class="row mt-4">
          <div class="col-md-12">
            <h2>Listado de Estudiantes</h2>
            <table id="tablaEstudiantes" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nombre Completo</th>
                  <th>Correo</th>
                  <th>Programa/Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($estudiantes as $fila): ?>
                  <tr>
                    <td><?= htmlspecialchars($fila['idEstudiante']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre'] . ' ' . $fila['apellidos']) ?></td>
                    <td><?= htmlspecialchars($fila['correo']) ?></td>
                    <td><?= htmlspecialchars($fila['carrera_nombre'] ?? ($fila['estado_nombre'] ?? 'Desconocido')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Fin de la tabla HTML -->

      </div>

      <div class="panel-footer text-center mt-4">
        <p>&copy; <?php echo date('Y'); ?> Portal de Prácticas y Pasantías UD. Todos los derechos reservados.</p>
      </div>
    </div><!-- /.Cierra-default-panel -->
  </div><!-- /.container-fluid -->

  <script>
    $(document).ready(function () {
      $("#GenerarReporteEstudiantes").click(function () {
        // Asegúrate de que jspdf esté definido antes de intentar usarlo
        if (typeof jspdf === 'undefined' || typeof jspdf.jsPDF === 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Error de Carga',
            text: 'La librería jsPDF no se cargó correctamente. Por favor, verifica las rutas.'
          });
          console.error('jsPDF library not loaded.');
          return;
        }

        var pdf = new jspdf.jsPDF(); // Usa jspdf.jsPDF() para versiones más nuevas
        pdf.text("Reporte de Estudiantes", 20, 20);

        // Las columnas para el PDF deben coincidir con el orden de los datos en $data_for_pdf
        var columns = ["ID", "Nombre Completo", "Correo", "Programa/Estado"];
        var data = <?php echo $json_data_for_pdf; ?>;

        pdf.autoTable({
          head: [columns],
          body: data,
          startY: 30, // Inicia la tabla debajo del título
          tableWidth: 'auto', // Deja que autoTable ajuste ancho total
          theme: 'striped', // Añade un estilo básico
          styles: {
            font: 'helvetica',
            fontSize: 10,
            cellPadding: 3,
            valign: 'middle',
            halign: 'left'
          },
          headStyles: {
            fillColor: [33, 37, 41], // Fondo oscuro para el encabezado
            textColor: [255, 255, 255], // Texto blanco
            fontStyle: 'bold',
            halign: 'center'
          },
          // Se eliminan los columnStyles explícitos para que autoTable los gestione automáticamente
          // columnStyles: {
          //   0: { cellWidth: 15, halign: 'center' }, // ID
          //   1: { cellWidth: 40 }, // Nombre Completo
          //   2: { cellWidth: 40 }, // Correo
          //   3: { cellWidth: 30, halign: 'left' } // Programa/Estado
          // },
          didDrawPage: function (data) {
            // Pie de página
            var str = "Página " + pdf.internal.getNumberOfPages();
            pdf.setFontSize(10);
            pdf.text(str, data.settings.margin.left, pdf.internal.pageSize.height - 10);
          }
        });

        pdf.save('reporte_estudiantes.pdf');

        Swal.fire({
          icon: 'success',
          title: '¡PDF Generado!',
          text: 'El reporte de estudiantes se ha generado correctamente.',
          timer: 2000,
          showConfirmButton: false
        });
      });
    });
  </script>
</body>

</html>