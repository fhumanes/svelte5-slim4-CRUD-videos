<?php
/**
 *
 * @About:      API Interface
 * @File:       index.php
 * @Date:       $Date:$ Ene 2025
 * @Version:    $Rev:$ 1.0
 * @Developer:  Federico Guzman || Modificado por Fernando Humanes para PHP 8.1
 **/

/* Los headers permiten acceso desde otro dominio (CORS) a nuestro REST API o desde un cliente remoto via HTTP
 * Removiendo las lineas header() limitamos el acceso a nuestro RESTfull API a el mismo dominio
 * Nótese los métodos permitidos en Access-Control-Allow-Methods. Esto nos permite limitar los métodos de consulta a nuestro RESTfull API
 * Mas información: https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
 **/

// $dominioPermitido = "http://localhost:3000";

// header("Access-Control-Allow-Origin: $dominioPermitido"); // Para restringir desde dónde se pueden hacer peticines
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, authorization, Authorization, token-user ");
// header("Access-Control-Allow-Headers: *");

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
// header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('Content-Type: multipart/form-data');
header('Content-Type: application/x-www-form-urlencoded');
header('Content-Type: application/json');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

// session_cache_limiter(false);

include_once '../include/Config.php';  // Configuration Rest Api
//     require_once("../../include/dbcommon.php"); // DataBase PHPRunner

// Debug
// $debugCode = false;
// custom_error(1,"URL ejecutada: ".$_SERVER["REQUEST_URI"]); // To debug
// $debugCode = false;

// use App\Models\Db;  // Utilizamos la conexión de PHPRunner
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
// use DI\Container;
use Slim\Routing\RouteCollectorProxy;
use Slim\Middleware\BodyParsingMiddleware;


require_once __DIR__ . '/../libs/autoload.php';   // Library SLIM v4

$app = AppFactory::create();

$app->addRoutingMiddleware();
// $app->add(new BasePathMiddleware($app)); // No usar si se ejecuta en subdirectorio
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

$app->setBasePath(SCRIPTS_DIR);             // Indica el directorio desde donde está trabajando

require_once '../include/DbMovies.php';
$db = new DbMovies();

// --------------------------------------------------------------------------------------
// Grupo de Rutas para Películas
$app->group('/movies', function (RouteCollectorProxy $group) use ($db) {
    // Pasa la instancia de $db a tus controladores    
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // LIST
         return $db->getAllMovies($request, $response, $args);
    });
    $group->get('/{id}', function (Request $request, Response $response, $args ) use ($db) {    // VIEW
         return $db->getMovieById($request, $response, $args);
    });
    $group->post('', function (Request $request, Response $response, $args) use ($db) {         // ADD
         return $db->createMovie($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {     // UPDATE
         return $db->updateMovie($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {  // DELETE
         return $db->deleteMovie($request, $response, $args);
    });
});

// Grupo de Rutas para Temas
$app->group('/themes', function (RouteCollectorProxy $group)  use ($db) {
    // Pasa la instancia de $db a tus controladores    
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // LIST
         return $db->getAllThemes($request, $response, $args);
    });
    /*
    $group->get('/{id}', function (Request $request, Response $response, $args ) use ($db) {    // VIEW
         return $db->getThemeById($request, $response, $args);
    });
     */
    $group->post('', function (Request $request, Response $response, $args) use ($db) {         // ADD
         return $db->createTheme($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {     // UPDATE
         return $db->updateTheme($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {  // DELETE
         return $db->deleteTheme($request, $response, $args);
    });
});

// Grupo de Rutas para Soportes
$app->group('/supports', function (RouteCollectorProxy $group) use ($db) {
    // Pasa la instancia de $db a tus controladores    
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // LIST
         return $db->getAllSupports($request, $response, $args);
    });
    /*
    $group->get('/{id}', function (Request $request, Response $response, $args ) use ($db) {    // VIEW
         return $db->getSupportById($request, $response, $args);
    });
     */
    $group->post('', function (Request $request, Response $response, $args) use ($db) {         // ADD
         return $db->createSupport($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {     // UPDATE
         return $db->updateSupport($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {  // DELETE
         return $db->deleteSupport($request, $response, $args);
    });
});



/* Runner the aplication */
$app->run();


