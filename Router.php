<?php
/**
 * core/Router.php
 * Router sederhana berbasis array: cocokkan method + path ke
 * [Controller::class, 'method']. Mendukung parameter dinamis {id}.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        // Buang base path aplikasi jika di-deploy di subfolder
        $basePath = parse_url(config('app.url'), PHP_URL_PATH) ?? '';
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', trim($route['path'], '/'));
            $pattern = '#^/' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$class, $methodName] = $route['handler'];
                $controller = new $class();
                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
