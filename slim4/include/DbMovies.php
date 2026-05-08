<?php

include_once __DIR__.'/Config.php';       // Configuration Rest Api
include_once __DIR__.'/Function.php';     // General Function

/**
 *
 * @About:      Gestión de Películas
 * @File:       DbMovies.php
 * @Date:       $Date:$ jul 2025
 * @Version:    $Rev:$ 1.0
 * @Developer:  fernando humanes
 **/

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

class DbMovies
{

    private $db;

    function __construct()
    {
        $dbHost = DB_HOST;
        $dbName = DB_NAME;

        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            $this->db = $pdo;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
         
    }
    // ---------------------------------------------------------------------------
    // --- Obtener todas las películas ---
    public function getAllMovies(Request $request, Response $response, array $args): Response
    {
        /* Verify Token Authenticate Security
        $verify = authenticate($request, $response);
        if (is_array($verify)) { // Si es una array, es que hay error
            $response->getBody()->write(json_encode($verify));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(404);
        }
        */
        $stmt = $this->db->query("
            SELECT 
                m.id_movie, 
                m.name, 
                m.price, 
                m.startDate, 
                m.rating, 
                t.theme, 
                m.theme_id,
                s.support,
                m.support_id
            FROM movie m
            JOIN theme t ON m.theme_id = t.id_theme
            JOIN support s ON m.support_id = s.id_support
            ORDER BY m.id_movie ASC
        ");
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($movies, JSON_UNESCAPED_UNICODE));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        
    }

    // --- Obtener una película por ID ---
    public function getMovieById(Request $request, Response $response, array $args): Response
    {
        global $errorMessages;
        $id = $args['id'];
        $stmt = $this->db->prepare("
            SELECT 
                m.id_movie, 
                m.name, 
                m.price, 
                m.startDate, 
                m.rating, 
                t.theme, 
                m.theme_id,
                s.support,
                m.support_id
            FROM movie m
            JOIN theme t ON m.theme_id = t.id_theme
            JOIN support s ON m.support_id = s.id_support
            WHERE m.id_movie = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$movie) {
            $response->getBody()->write(json_encode(['message' => $errorMessages['005']], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
        }

        $response->getBody()->write(json_encode($movie, JSON_UNESCAPED_UNICODE));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
    }

    // --- Crear una nueva película ---
    public function createMovie(Request $request, Response $response, array $args): Response
    {
        global $errorMessages;
        $data = $request->getParsedBody();
        // Validar datos de entrada (simple)
        $verify = verifyRequiredParams(array('name','price','startDate','rating','theme_id','support_id'), $data);
        if (is_array($verify)) {                                // Si es una array, es que hay error
            $response->getBody()->write(json_encode($verify), JSON_UNESCAPED_UNICODE);
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
        try {
            $stmt = $this->db->prepare("
                INSERT INTO movie (name, price, startDate, rating, theme_id, support_id)
                VALUES (:name, :price, :startDate, :rating, :theme_id, :support_id)
            ");

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':startDate', $data['startDate']);
            $stmt->bindParam(':rating', $data['rating'], PDO::PARAM_INT);
            $stmt->bindParam(':theme_id', $data['theme_id'], PDO::PARAM_INT);
            $stmt->bindParam(':support_id', $data['support_id'], PDO::PARAM_INT);

            $stmt->execute();

            $newId = $this->db->lastInsertId();
            // Obtén la película completa recién creada si es necesario, o simplemente devuelve el nombre.
            // Para simplificar, devolvemos el nombre que se envió, y el nuevo ID.
            $response->getBody()->write(json_encode([
                'message' => $errorMessages['006'],
                'id' => $newId,
                'name' => $data['name'] 
            ], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
            
        } catch (PDOException $e) {
            // Error La Película ya existe (ej. columna 'tname' es UNIQUE)
            if ($e->getCode() === '23000') { // Código de error SQL para violación de unicidad
                $response->getBody()->write(json_encode(['message' => $errorMessages['020']], JSON_UNESCAPED_UNICODE));
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(409); // 409 Conflict
            }
            $response->getBody()->write(json_encode(['message' => $errorMessages['014'], 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
        }
    }

    // --- Actualizar una película existente ---
    public function updateMovie(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        global $errorMessages;
        $data = $request->getParsedBody();
        // Validar datos de entrada (simple)
        $verify = verifyRequiredParams(array('name','price','startDate','rating','theme_id','support_id'), $data);
        if (is_array($verify)) {                                // Si es una array, es que hay error
            $response->getBody()->write(json_encode($verify), JSON_UNESCAPED_UNICODE);
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        $stmt = $this->db->prepare("
            UPDATE movie
            SET name = :name, price = :price, startDate = :startDate, rating = :rating, theme_id = :theme_id, support_id = :support_id
            WHERE id_movie = :id
        ");

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':startDate', $data['startDate']);
        $stmt->bindParam(':rating', $data['rating'], PDO::PARAM_INT);
        $stmt->bindParam(':theme_id', $data['theme_id'], PDO::PARAM_INT);
        $stmt->bindParam(':support_id', $data['support_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $response->getBody()->write(json_encode(['message' => $errorMessages['007']], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'message' => $errorMessages['008'],
            'name' => $data['name'] 
        ], JSON_UNESCAPED_UNICODE));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
    }

    // --- Eliminar una película ---
    public function deleteMovie(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        global $errorMessages;
        $stmt = $this->db->prepare("DELETE FROM movie WHERE id_movie = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $response->getBody()->write(json_encode(['message' => $errorMessages['009']], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
        }

        $response->getBody()->write(json_encode(['message' => $errorMessages['010']], JSON_UNESCAPED_UNICODE));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
    }
    // --- CRUD para Temas ---
    // --- Obtener todas las películas ---
    public function getAllThemes(Request $request, Response $response, array $args): Response
    {
        $stmt = $this->db->query("SELECT id_theme, theme FROM theme ORDER BY theme");
        $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($themes, JSON_UNESCAPED_UNICODE));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);    
    }

    public function createTheme(Request $request, Response $response, array $args): Response
    {
        global $errorMessages;
        $data = $request->getParsedBody();
        // Validar datos de entrada (simple)
        $verify = verifyRequiredParams(array('theme'), $data);
        if (is_array($verify)) {                                // Si es una array, es que hay error
            // $response->getBody()->write(json_encode($verify), JSON_UNESCAPED_UNICODE);
            $response->getBody()->write(json_encode(['message' => $errorMessages['011']], JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO theme (theme) VALUES (:theme)");
            $stmt->bindParam(':theme', $data['theme']);
            $stmt->execute();

            $newId = $this->db->lastInsertId();
            $response->getBody()->write(json_encode([
                'message' => $errorMessages['012'],
                'id' => $newId,
                'theme' => $data['theme']
            ], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        } catch (PDOException $e) {
            // Error si el tema ya existe (ej. columna 'theme' es UNIQUE)
            if ($e->getCode() === '23000') { // Código de error SQL para violación de unicidad
                $response->getBody()->write(json_encode(['message' => $errorMessages['013']], JSON_UNESCAPED_UNICODE));
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(409); // 409 Conflict
            }
            $response->getBody()->write(json_encode(['message' => $errorMessages['014'], 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
        }
    }

    public function updateTheme(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        global $errorMessages;
        $data = $request->getParsedBody();
        // Validar datos de entrada (simple)
        $verify = verifyRequiredParams(array('theme'), $data);
        if (is_array($verify)) {                                // Si es una array, es que hay error
            // $response->getBody()->write(json_encode($verify), JSON_UNESCAPED_UNICODE);
            $response->getBody()->write(json_encode(['message' => $errorMessages['011']], JSON_UNESCAPED_UNICODE));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        try {
            $stmt = $this->db->prepare("UPDATE theme SET theme = :theme WHERE id_theme = :id");
            $stmt->bindParam(':theme', $data['theme']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $response->getBody()->write(json_encode(['message' => $errorMessages['015']], JSON_UNESCAPED_UNICODE));
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
            }

            $response->getBody()->write(json_encode([
                'message' => $errorMessages['016'],
                'id' => $id,
                'theme' => $data['theme']
            ], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
        } catch (PDOException $e) {
             if ($e->getCode() === '23000') {  // Código de error SQL para violación de unicidad
                $response->getBody()->write(json_encode(['message' => $errorMessages['013']], JSON_UNESCAPED_UNICODE));
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(409);
            }
            $response->getBody()->write(json_encode(['message' => $errorMessages['014'], 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
        }
    }

    public function deleteTheme(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // --- Manejo de Integridad Referencial (FASE POSTERIOR, AHORA SOLO ELIMINA) ---
        // Para la fase 5, aquí iría la verificación de si hay películas usando este tema.
        // Por ahora, simplemente intentamos eliminar. Si hay una restricción FOREIGN KEY,
        // la base de datos lanzará una excepción PDO.
        
        global $errorMessages;
        try {
            $stmt = $this->db->prepare("DELETE FROM theme WHERE id_theme = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $response->getBody()->write(json_encode(['message' => $errorMessages['017']], JSON_UNESCAPED_UNICODE));
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(404);
            }

            $response->getBody()->write(json_encode(['message' => $errorMessages['018']], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
        } catch (PDOException $e) {
            // Código de error 23000 es para violación de integridad (Foreign Key Constraint)
            if ($e->getCode() === '23000') {
                $response->getBody()->write(json_encode(['message' => $errorMessages['019']], JSON_UNESCAPED_UNICODE));
                return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(409); // 409 Conflict
            }
            $response->getBody()->write(json_encode(['message' => $errorMessages['014'], 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(500);
        }
    }


    // --- CRUD para Soportes ---
    // --- Obtener todos los Soportes---
    public function getAllSupports(Request $request, Response $response, array $args): Response
    {
        $stmt = $this->db->query("SELECT id_support, support FROM support ORDER BY support");
        $supports = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($supports, JSON_UNESCAPED_UNICODE));
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);    
    }

    public function createSupport(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        if (!isset($data['support']) || empty($data['support'])) {
            $response->getBody()->write(json_encode(['message' => 'El nombre del soporte es obligatorio.'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO support (support) VALUES (:support)");
            $stmt->bindParam(':support', $data['support']);
            $stmt->execute();

            $newId = $this->db->lastInsertId();
            $response->getBody()->write(json_encode([
                'message' => 'Soporte creado con éxito.',
                'id' => $newId,
                'support' => $data['support']
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $response->getBody()->write(json_encode(['message' => 'El soporte ya existe.'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            $response->getBody()->write(json_encode(['message' => 'Error interno del servidor al crear el soporte.', 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function updateSupport(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        if (!isset($data['support']) || empty($data['support'])) {
            $response->getBody()->write(json_encode(['message' => 'El nombre del soporte es obligatorio para la actualización.'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $stmt = $this->db->prepare("UPDATE support SET support = :support WHERE id_support = :id");
            $stmt->bindParam(':support', $data['support']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $response->getBody()->write(json_encode(['message' => 'Soporte no encontrado o sin cambios.'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $response->getBody()->write(json_encode([
                'message' => 'Soporte actualizado con éxito.',
                'id' => $id,
                'support' => $data['support']
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $response->getBody()->write(json_encode(['message' => 'El soporte ya existe.'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            $response->getBody()->write(json_encode(['message' => 'Error interno del servidor al actualizar el soporte.', 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function deleteSupport(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // --- Manejo de Integridad Referencial (FASE POSTERIOR, AHORA SOLO ELIMINA) ---
        // Para la fase 5, aquí iría la verificación de si hay películas usando este soporte.
        // Por ahora, simplemente intentamos eliminar.

        try {
            $stmt = $this->db->prepare("DELETE FROM support WHERE id_support = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $response->getBody()->write(json_encode(['message' => 'Soporte no encontrado.'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            $response->getBody()->write(json_encode(['message' => 'Soporte eliminado con éxito.'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $response->getBody()->write(json_encode(['message' => 'No se puede eliminar el soporte porque está asignado a una o más películas.'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            $response->getBody()->write(json_encode(['message' => 'Error interno del servidor al eliminar el soporte.', 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
