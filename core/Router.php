<?php

namespace Core;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class Router
{
    private RouteCollection $routes;
    private array $middlewareGroups = [];
    private string $currentPrefix = '';
    private array $currentMiddleware = [];

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function get(string $path, $controller, string $name = null): void
    {
        $this->addRoute('GET', $path, $controller, $name);
    }

    public function post(string $path, $controller, string $name = null): void
    {
        $this->addRoute('POST', $path, $controller, $name);
    }

    public function put(string $path, $controller, string $name = null): void
    {
        $this->addRoute('PUT', $path, $controller, $name);
    }

    public function delete(string $path, $controller, string $name = null): void
    {
        $this->addRoute('DELETE', $path, $controller, $name);
    }

    public function patch(string $path, $controller, string $name = null): void
    {
        $this->addRoute('PATCH', $path, $controller, $name);
    }

    public function options(string $path, $controller, string $name = null): void
    {
        $this->addRoute('OPTIONS', $path, $controller, $name);
    }

    public function any(string $path, $controller, string $name = null): void
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $path, $controller, $name);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->currentPrefix;
        $previousMiddleware = $this->currentMiddleware;

        if (isset($attributes['prefix'])) {
            $this->currentPrefix = rtrim($previousPrefix . '/' . ltrim($attributes['prefix'], '/'), '/');
        }

        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->currentMiddleware = array_merge($this->currentMiddleware, $middleware);
        }

        $callback($this);

        $this->currentPrefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    public function middleware(array $middleware): self
    {
        $this->currentMiddleware = array_merge($this->currentMiddleware, $middleware);
        return $this;
    }

    private function addRoute($methods, string $path, $controller, string $name = null): void
    {
        $methods = is_array($methods) ? $methods : [$methods];
        $fullPath = $this->currentPrefix . $path;

        // Ensure path starts with /
        if (!str_starts_with($fullPath, '/')) {
            $fullPath = '/' . $fullPath;
        }

        // Handle different controller formats
        $controllerAction = $this->normalizeController($controller);

        $route = new Route($fullPath, ['_controller' => $controllerAction], [], [], '', [], $methods);

        // Add middleware to route defaults
        if (!empty($this->currentMiddleware)) {
            $route->setDefault('_middleware', $this->currentMiddleware);
        }

        $routeName = $name ?: $this->generateRouteName($methods[0], $fullPath);
        $this->routes->add($routeName, $route);
    }

    private function generateRouteName(string $method, string $path): string
    {
        return strtolower($method) . '.' . str_replace(['/', '{', '}'], ['.', '', ''], trim($path, '/'));
    }

    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    public function resource(string $name, $controller): void
    {
        // Handle both string and array controller formats
        if (is_array($controller)) {
            $controllerClass = $controller[0];
            $this->get($name, [$controllerClass, 'index'], "{$name}.index");
            $this->post($name, [$controllerClass, 'store'], "{$name}.store");
            $this->get("{$name}/{{$name}}", [$controllerClass, 'show'], "{$name}.show");
            $this->put("{$name}/{{$name}}", [$controllerClass, 'update'], "{$name}.update");
            $this->delete("{$name}/{{$name}}", [$controllerClass, 'destroy'], "{$name}.destroy");
        } else {
            // Legacy string format
            $this->get($name, "{$controller}::index", "{$name}.index");
            $this->post($name, "{$controller}::store", "{$name}.store");
            $this->get("{$name}/{{$name}}", "{$controller}::show", "{$name}.show");
            $this->put("{$name}/{{$name}}", "{$controller}::update", "{$name}.update");
            $this->delete("{$name}/{{$name}}", "{$controller}::destroy", "{$name}.destroy");
        }
    }

    public function apiResource(string $name, $controller): void
    {
        $this->resource($name, $controller);
    }

    /**
     * Normalize controller to a consistent format
     */
    private function normalizeController($controller)
    {
        // If it's already a callable (closure), return as-is
        if (is_callable($controller) && !is_array($controller)) {
            return $controller;
        }

        // If it's an array [ControllerClass::class, 'method']
        if (is_array($controller) && count($controller) === 2) {
            [$class, $method] = $controller;
            return "{$class}::{$method}";
        }

        // If it's already a string format 'ControllerClass::method'
        if (is_string($controller)) {
            return $controller;
        }

        throw new \InvalidArgumentException('Invalid controller format');
    }
}
