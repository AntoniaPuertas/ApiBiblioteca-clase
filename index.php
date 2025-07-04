<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prueba</title>
</head>
<body>
  <h1>Hola!!!</h1>


<?php
  $host_name = 'db5018152581.hosting-data.io';
  $database = 'dbs14399127';
  $user_name = 'dbu2106078';
  $password = 'segurA3.!33';
  echo '<p>Antes de la conexion</p>';
  $link = new mysqli($host_name, $user_name, $password, $database);
echo '<p>Después de la conexion</p>';
  if ($link->connect_error) {
    die('<p>Error al conectar con servidor MySQL: '. $link->connect_error .'</p>');
  } else {
    echo '<p>Se ha establecido la conexión al servidor MySQL con éxito.</p>';
  }
?>
</body>
</html>