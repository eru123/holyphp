<?php

namespace eru123\router;

use Exception;
use Error;
use Throwable;
use eru123\fs\File;

class Router
{
    protected $childs = [];
    protected $parent = null;
    protected $routes = [];
    protected $bootstraps = [];
    protected $base = '';
    protected $fallback = null;
    protected $error = null;
    protected $response = null;

    public function __construct(?Router $parent = null)
    {
        $this->parent = $parent;
    }

    public function fallback(callable $callback): static
    {
        $this->fallback = $callback;
        return $this;
    }

    public function error(callable $callback): static
    {
        $this->error = $callback;
        return $this;
    }

    public function response(callable $callback): static
    {
        $this->response = $callback;
        return $this;
    }

    public function request($method, $url, ...$callbacks): static
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $url,
            'callbacks' => $callbacks
        ];
        return $this;
    }

    public function route(...$args): static
    {
        return $this->request(...$args);
    }

    public function get($url, ...$callbacks): static
    {
        return $this->request('GET', $url, ...$callbacks);
    }

    public function post($url, ...$callbacks): static
    {
        return $this->request('POST', $url, ...$callbacks);
    }

    public function bootstrap(array|callable $callbacks): static
    {
        if (is_array($callbacks)) {
            $this->bootstraps = array_merge($this->bootstraps, $callbacks);
        } else {
            $this->bootstraps[] = $callbacks;
        }

        return $this;
    }

    public function put($url, ...$callbacks): static
    {
        return $this->request('PUT', $url, ...$callbacks);
    }

    public function delete($url, ...$callbacks): static
    {
        return $this->request('DELETE', $url, ...$callbacks);
    }

    public function patch($url, ...$callbacks): static
    {
        return $this->request('PATCH', $url, ...$callbacks);
    }

    public function options($url, ...$callbacks): static
    {
        return $this->request('OPTIONS', $url, ...$callbacks);
    }

    public function head($url, ...$callbacks): static
    {
        return $this->request('HEAD', $url, ...$callbacks);
    }

    public function any($url, ...$callbacks): static
    {
        return $this->request('ANY', $url, ...$callbacks);
    }

    public function static($url, string|array $dir, string|array $index = [], ...$callbacks): static
    {
        if (is_string($dir)) {
            $dir = [$dir];
        }

        if (is_string($index)) {
            $index = [$index];
        }

        $callbacks[] = function (Context $context) use ($dir, $index) {
            if (!isset($context->route['file']) || empty($context->route['file'])) {
                return null;
            }

            $fp = ltrim(urldecode($context->route['file']), '/');

            foreach ($dir as $i => $d) {
                $d = realpath($d);
                $f = null;

                if (!$d) {
                    continue;
                }

                if (!empty($fp)) {
                    $f = realpath($d . '/' . $fp);
                    if (!$f) {
                        continue;
                    }
                }

                if (!$f) {
                    foreach ($index as $j => $f) {
                        $f = realpath($d . '/' . $f);
                        if ($f) {
                            break;
                        }
                    }
                }

                if (!$f || !file_exists($f) || strpos($f, $d) !== 0) {
                    continue;
                }

                return (new File($f))->stream();
            }
        };

        return $this->request('STATIC', $url, ...$callbacks);
    }

    public function child(Router $router): static
    {
        $router->parent($this);
        $this->childs[] = $router;
        return $this;
    }

    public function parent(?Router $router): ?Router
    {
        return is_null($router) ? $this->parent : $this->parent = $router;
    }

    public function base(?string $base = null): string
    {
        return is_null($base) ? $this->base : $this->base = $base;
    }

    public function map(string $parent_base = '', array $parent_callbacks = []): array
    {
        $map = [];
        $stack = [[$this, $parent_base, $parent_callbacks]];

        while (!empty($stack)) {
            [$router, $prefix, $callbacks] = array_pop($stack);

            foreach ($router->routes as $route) {
                $map[] = [
                    'method' => strtoupper(trim($route['method'])),
                    'path' => $prefix . $router->base() . $route['path'],
                    'callbacks' => array_merge($callbacks, $router->bootstraps, $route['callbacks'])
                ];
            }

            foreach ($router->childs as $child) {
                $stack[] = [$child, $prefix . $router->base(), array_merge($callbacks, $router->bootstraps)];
            }
        }

        return array_map(function ($route) {
            $route['match'] = Helper::match($route['path']);
            $route['matchdir'] = Helper::matchdir($route['path']);
            $route['params'] = Helper::params($route['path']);
            $route['file'] = Helper::file($route['path']);
            return $route;
        }, $map);
    }

    public static function status_page(int $code, string $title, string $message)
    {
        http_response_code($code);
        $title = htmlspecialchars($title);
        $message = htmlspecialchars($message);
        echo "<!DOCTYPE html> <html lang=\"en\"> <head> <meta charset=\"UTF-8\"> <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"> <title>$title</title> <style> body { font-family: sans-serif; background-color: #f1f1f1; } h1 { text-align: center; margin-top: 100px; } p { text-align: center; font-size: 18px; } </style></head><body><h1>$title</h1><p>$message</p></body></html>";
        exit;
    }

    public function run(?string $base = null): void
    {
        if (!is_null($base)) {
            $this->base($base);
        }

        $map = $this->map();

        $fallback_handler = !empty($this->fallback) ? $this->fallback : function () {
            return self::status_page(404, '404 Not Found', 'The requested URL was not found on this server.');
        };

        $error_handler = !empty($this->error) ? $this->error : function () {
            return self::status_page(500, '500 Internal Server Error', 'The server encountered an internal error and was unable to complete your request. Either the server is overloaded or there is an error in the application.');
        };

        $response_handler = !empty($this->response) ? $this->response : function ($response) {
            if (is_array($response) || is_object($response)) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            } else if (is_string($response) || is_numeric($response)) {
                echo $response;
                exit;
            }
        };

        try {
            $callback_response = null;
            foreach ($map as $route) {
                if ($route['match'] || $route['matchdir']) {
                    $context = new Context;
                    $context->route = $route;
                    $context->routes = $map;
                    $callbacks = $route['callbacks'];
                    if ((($route['method'] == 'ANY' || $route['method'] == Helper::method()) && $route['match']) || ($route['method'] == 'STATIC' && $route['matchdir'])) {
                        $callback_response = null;
                        while (!empty($callbacks) && is_null($callback_response)) {
                            $callback = array_shift($callbacks);
                            if (is_callable($callback)) {
                                $callback_response = call_user_func_array($callback, [&$context]);
                            }
                        }

                        if (!is_null($callback_response)) {
                            $response_handler($callback_response);
                        }
                    }
                }
            }

            if (is_null($callback_response)) {
                $response_handler($fallback_handler($context));
            }
        } catch (Exception $e) {
            $response_handler($error_handler($e, $context));
        } catch (Error $e) {
            $response_handler($error_handler($e, $context));
        } catch (Throwable $e) {
            $response_handler($error_handler($e, $context));
        }
    }
}
