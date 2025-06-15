<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Core\Router;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Initialize database
require_once __DIR__ . '/database.php';

class Application
{
    private Router $router;
    private Request $request;
    private array $middleware = [];

    public function __construct()
    {
        $this->router = new Router();
        $this->request = Request::createFromGlobals();

        // Set default headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Handle preflight requests
        if ($this->request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    public function loadRoutes(): void
    {
        // Load web routes
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            require_once __DIR__ . '/../routes/web.php';
        }

        // Load API routes
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            require_once __DIR__ . '/../routes/api.php';
        }
    }

    public function addMiddleware(string $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function run(): void
    {
        try {
            $this->loadRoutes();

            $context = new RequestContext();
            $context->fromRequest($this->request);

            $matcher = new UrlMatcher($this->router->getRoutes(), $context);
            $parameters = $matcher->match($this->request->getPathInfo());

            // Extract controller and action
            $controller = $parameters['_controller'];
            unset($parameters['_controller']);

            // Apply middleware based on route middleware
            $routeMiddleware = $parameters['_middleware'] ?? [];
            foreach ($routeMiddleware as $middlewareName) {
                if ($middlewareName === 'jwt') {
                    $middlewareInstance = new \Core\JwtMiddleware();
                    if (method_exists($middlewareInstance, 'handle')) {
                        $result = $middlewareInstance->handle($this->request);
                        if ($result instanceof Response) {
                            $result->send();
                            return;
                        }
                    }
                }
            }

            // Execute controller
            if (is_callable($controller)) {
                $response = call_user_func_array($controller, array_values($parameters));
            } else {
                [$class, $method] = explode('::', $controller);
                $controllerInstance = new $class();
                $response = call_user_func_array([$controllerInstance, $method], array_values($parameters));
            }

            if ($response instanceof Response) {
                $response->send();
            } else {
                $this->jsonResponse($response);
            }
        } catch (ResourceNotFoundException $e) {
            $this->jsonResponse(['error' => 'Route not found'], 404);
        } catch (MethodNotAllowedException $e) {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        } catch (Exception $e) {
            if ($_ENV['APP_DEBUG'] === 'true') {
                $this->jsonResponse([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            } else {
                $this->jsonResponse(['error' => 'Internal server error'], 500);
            }
        }
    }

    private function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}

// Create global application instance
$app = new Application();

// Helper function to get the app instance
function app(): Application
{
    global $app;
    return $app;
}
