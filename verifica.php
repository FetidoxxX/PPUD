<?php
include("./class/class_log.php");

// Verificar que los datos lleguen correctamente
if (!isset($_POST['user']) || !isset($_POST['passw']) || !isset($_POST['rol'])) {
  echo "<!DOCTYPE html>
    <html>
    <head>
        <link rel='stylesheet' href='./sw/dist/sweetalert2.min.css'>
        <script src='./sw/dist/sweetalert2.min.js'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Datos incompletos. Por favor, seleccione un rol e ingrese sus credenciales.'
            }).then(() => {
                window.location = './index.php';
            });
        </script>
    </body>
    </html>";
  exit;
}

$log = new Login();
$user = $_POST['user'];
$pass = $_POST['passw'];
$rol = $_POST['rol'];

$log->validar($user, $pass, $rol);
?>