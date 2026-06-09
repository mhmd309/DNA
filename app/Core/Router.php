<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
  private array $routes = [];

  public function get(string $path, string $controller, string $method, ?string $permission = null): void
  {
    $this->addRoute('GET', $path, $controller, $method, $permission);
  }

  public function post(string $path, string $controller, string $method, ?string $permission = null): void
  {
    $this->addRoute('POST', $path, $controller, $method, $permission);
  }

  private function addRoute(string $httpMethod, string $path, string $controller, string $method, ?string $permission): void
  {
    $this->routes[] = [
      'method'     => $httpMethod,
      'path'       => $path,
      'controller' => $controller,
      'action'     => $method,
      'permission' => $permission,
    ];
  }

  public function dispatch(string $url): void
  {
    $url = trim($url, '/');
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    foreach ($this->routes as $route) {
      $pattern = $this->convertToRegex($route['path']);
      if ($route['method'] === $requestMethod && preg_match($pattern, $url, $matches)) {
        array_shift($matches);
        $params = $matches;

        $controllerClass = 'App\\Controllers\\' . $route['controller'];
        if (!class_exists($controllerClass)) {
          $this->notFound();
          return;
        }

        $controller = new $controllerClass();

        if ($route['permission'] !== null) {
          Auth::requirePermission($route['permission']);
        }

        $action = $route['action'];
        if (!method_exists($controller, $action)) {
          $this->notFound();
          return;
        }

        call_user_func_array([$controller, $action], $params);
        return;
      }
    }

    $this->notFound();
  }

  private function convertToRegex(string $path): string
  {
    $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $path);
    return '#^' . $pattern . '$#';
  }

  private function notFound(): void
  {
    http_response_code(404);
    View::render('errors/404', ['title' => 'الصفحة غير موجودة'], 'guest');
  }
}
