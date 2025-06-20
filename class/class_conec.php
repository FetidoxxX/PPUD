<?php
class Conectar
{
  public static function conec()
  {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db_name = "ppud_bd";

    // Conectarnos a la BD
    $link = mysqli_connect($host, $user, $pass)
      or die("ERROR Al conectar la BD: " . mysqli_connect_error());

    // Seleccionar la BD
    mysqli_select_db($link, $db_name)
      or die("ERROR Al seleccionar la BD: " . mysqli_error($link));

    // Establecer charset UTF-8 para caracteres especiales
    mysqli_set_charset($link, "utf8");

    return $link;
  }
}
?>