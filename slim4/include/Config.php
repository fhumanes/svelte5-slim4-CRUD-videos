<?php

//
//  Database configuration
//

//
//  URI raíz de la aplicación
//

define('SCRIPTS_DIR', '/movie-server/v1'); 

$server = 'test';  // 'test' or 'production'

if ($server == 'test' ) {
// Path root of Directory files in File System or Disc
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'humanes'); 
define('DB_NAME', 'svelte-app1'); 
} else {
// In Server Linux
define('DB_HOST', 'localhost');
define('DB_USER', 'u637977917_factu');
define('DB_PASSWORD', 'humanes'); 
define('DB_NAME', 'linux-svelte-app1'); 
} 

//referencia generado con MD5(uniqueid(<some_string>, true))
define('API_KEY','3d524a53c110e4c22463b10ed32cef9d');

/**
 * API Response HTTP CODE
 * Used as reference for API REST Response Header
 *
 */
/*
200 	OK
201 	Created
304 	Not Modified
400 	Bad Request
401 	Unauthorized
403 	Forbidden
404 	Not Found
409     Conflict
422 	Unprocessable Entity
500 	Internal Server Error
*/

// Error messages to facilitate their translation

$errorMessages = array(
    "001" => "Authorization Token Missing",
    "002" => "The authorization token is incorrect",
    "003" => "Required field(s) or attribute(s) {1} is missing or empty",
    "004" => "Required Action (add,view,update,delete)",
    "005" => "Película no encontrada",
    "006" => "Película creada",
    "007" => "Película no encontrada o sin cambios",
    "008" => "Película actualizada",
    "009" => "Película no encontrada",
    "010" => "Película eliminada",
    "011" => "El nombre del tema es obligatorio",
    "012" => "Tema creado con éxito",
    "013" => "El tema ya existe",
    "014" => "Error interno del servidor al crear el tema",
    "015" => "Tema no encontrado o sin cambios",
    "016" => "Tema actualizado con éxito",
    "017" => "Tema no encontrado",
    "018" => "Tema eliminado con éxito",
    "019" => "No se puede eliminar el tema porque está asignado a una o más películas",
    "020" => "La Película ya existe",
    "021" => "",
    "022" => "",
    "023" => "",
    "024" => "",
    "025" => "",
    "026" => ""
);

