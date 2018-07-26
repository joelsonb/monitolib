<?php
/**
 * 1.0.0. - 2017-03-16
 * Inicial release
 */
namespace MonitoLib;

use \MonitoLib\App;
use \MonitoLib\Functions;

class Router
{
    static private $routes = [];

    private static function add ($method, $url, $action, $secure = true)
    {
        $parts = explode('/', trim($url, '/'));

        $routes = [];
        $cf = null;
        $af = null;

        $len = count($parts) - 1;

        for ($i = $len; $i >= 0; $i--) { 
            $index = $parts[$i];

            if ($i == $len) {
                $af[$index] = [
                    '@' => [
                        $method => $action . ($secure ? '+' : '')
                    ]
                ];
            } else {
                $af[$index] = $cf;
            }

            $cf = $af;
            $af = [];
        }

        self::$routes = \MonitoLib\Functions::ArrayMergeRecursive(self::$routes, $cf);
    }
    public static function cli ($url, $action, $secure = true)
    {
        self::add('CLI', $url, $action, $secure);
    }
    public static function get ($url, $action, $secure = true)
    {
        self::add('GET', $url, $action, $secure);
    }   
    public static function patch ($url, $action, $secure = true)
    {
        self::add('PATCH', $url, $action, $secure);
    }
    public static function post ($url, $action, $secure = true)
    {
        self::add('POST', $url, $action, $secure);
    }
    public static function put ($url, $action, $secure = true)
    {
        self::add('PUT', $url, $action, $secure);
    }
    public static function delete ($url, $action, $secure = true)
    {
        self::add('DELETE', $url, $action, $secure);
    }
    static private function error ($message)
    {
        return $json = [
            'code'    => '1', 
            'message' => $message
            ];
    }
    static public function check ($request)
    {
        if (PHP_SAPI == 'cli') {
            $requestMethod = 'CLI';
            $params = $request->getParam();
        } else {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $params = [];
        }

        $uriParts = explode('/', trim($request->getRequestUri(), '/'));

        $currentArray = [];

        $action = true;

        if (isset(self::$routes)) {
            $ri = self::$routes;

            // \MonitoLib\Dev::pre($ri);

            $cParts = count($uriParts);
            $i = 1;

            $matched = false;
            $xPart = '';

            foreach ($uriParts as $uriPart) {
                $xPart = $uriPart;
                // Verifica se a parte casa
                if (isset($ri[$uriPart])) {
                    $matched = true;
                } else {
                    foreach ($ri as $key => $value) {
                        if (preg_match('/:\{.*\}/', $key)) {
                            $key1 = substr($key, 2, -1);

                            if (preg_match("/$key1/", $uriPart)) {
                                $matched = true;
                                $xPart = $key;
                                $params[] = $uriPart;
                                // exit;
                                break;
                            }
                        }
                    }
                }

                // Se a parte da URL não for a última, continua comparando
                if ($cParts !== $i) {
                    $matched = false;

                    if (!isset($ri[$xPart])) {
                        break;
                    }

                    $ri = $ri[$xPart];
                    $i++;
                    continue;
                }
            }
            // Se a url foi encontrada
            if ($matched) {
                // echo "existe $uriPart<br />";
                // $ri = self::$routes[$uriPart];

                // \MonitoLib\Dev::pr($ri[$xPart]);
                // \MonitoLib\Dev::e($ri[$xPart]['#'][$requestMethod]);

                $xM = $requestMethod;

                // if (!isset($ri[$xPart]['#'][$requestMethod]) || !isset($ri[$xPart]['#']['*'])) {
                if (!isset($ri[$xPart]['@'][$requestMethod])) {
                    if (isset($ri[$xPart]['@']['*'])) {
                        $xM = '*';
                    } else {
                        http_response_code(405);
                        throw new \Exception('Method Not Allowed!', 405);
                    }
                }

                if (isset($ri[$xPart]['@'][$xM])) {
                    $action = $ri[$xPart]['@'][$xM];
                    $parts  = explode('@', $action);
                    $class  = $parts[0];
                    $method = $parts[1];
                    $secure = false;

                    if (substr($method, -1) === '+') {
                        $secure = true;
                        $method = substr($method, 0, -1);
                    }

                    if (class_exists($class)) {
                        if (is_callable([$class, $method])) {
                            $router = new \StdClass;
                            $router->class    = $class;
                            $router->method   = $method;
                            $router->params   = $params;
                            $router->isSecure = $secure;
                            return $router;
                        } else {
                            throw new \Exception('Controller method not found!', 5);
                        }
                    } else {
                        throw new \Exception('Controller not found!', 3);
                    }
                } else {
                    throw new \Exception('Action not found!', 6);
                }
            } else {
                http_response_code(404);
                throw new \Exception('Route not configured in the server!', 404);
            }
        } else {
            throw new \Exception("There's not routes configured in server", 7);
        }
    }
}