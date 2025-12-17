<?php
// Este archivo se comporta como una hoja de estilos CSS
header("Content-Type: text/css");

// Cargar acceso a la base y al helper de configuración
require_once __DIR__ . '/../../config/config.php';

// Listado de claves de color
$claves = [
  'color_primario',
  'color_header',
  'color_boton',
  'color_fondo',
  'color_texto',
  'color_acento',
  'color_titulo',
  'color_subtitulo',
];

// Generar variables CSS
echo ":root {\n";
foreach ($claves as $clave) {
  $valor = Config::get($clave) ?: '#000'; // valor por defecto si no existe
  echo "  --$clave: $valor;\n";
}
echo "}\n";
?>
