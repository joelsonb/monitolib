<?php
/**
 * 1.0.0. - 2017-03-16
 * Inicial release
 */
namespace MonitoLib;

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
						$method => $action
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

		try {
			if (isset(self::$routes)) {
				$ri = self::$routes;

				// \MonitoLib\Dev::pr($ri);

				$cParts = count($uriParts);
				$i = 1;

				$matched = false;
				$xPart = '';

				foreach ($uriParts as $uriPart) {
					// Verifica se a parte casa
					if (isset($ri[$uriPart])) {
						$matched = true;
						$xPart = $uriPart;
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
						$ri = $ri[$uriPart];
						$i++;
						continue;
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
								$class = new $class;

								if (is_callable([$class, $method])) {
									// Verifica se permite acesso sem credenciais
									if ($secure) {
										// Verifica se o usuário tem as credenciais necessárias
										// if ($isLoggedId) {
										// 	if (!$hasPower) {
										// 		throw new \Exception('You don not have right privileges!', 403);
										// 	}
										// } else {
											throw new \Exception('You must send credentials!', 401);
										// }
									}

									try {
										if (PHP_SAPI == 'cli' && is_callable([$class, 'process'])) {
											$class->process();
										}
										$return = $class->$method(...$params);
									} catch (\Exception $e) {
										throw new \Exception($e->getMessage(), $e->getCode());
									}
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
				}
				return $return;
			} else {
				throw new \Exception("There's not route configured in server", 7);
			}
		} catch (\Exception $e) {
			return [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
				'debug'   => [
					'method' => $requestMethod,
					'url'    => $request->getRequestUri(),
					],
				];
		}

		return $return;
	}

	static public function OLDrun ($request)
	{
		\MonitoLib\Dev::pre(self::$routes);
		// \MonitoLib\Dev::pre($request);
		// \MonitoLib\Dev::pre(self::$routes['cli']);

		$params = $request->params;
		// \MonitoLib\Dev::pre($params);

		$parts = explode('/', $request->command);
		$action = true;
		$ri = self::$routes['cli'];

		foreach ($parts as $uriPart) {
			$rk = key($ri);
			// if (isset($ri[$uriPart])) {
			if (isset($ri[$uriPart])) {
				$ri = $ri[$uriPart];

				// \MonitoLib\Dev::pre($ri);

				if (isset($ri['@'])) {
					$action = $ri['@'];
				}
			} else {
				$action = false;
				break;
			}
		}

		$parts  = explode('@', $action);
		$class  = $parts[0];
		$method = $parts[1];

		echo "$class->$method\n";

		// $classCudo = new $class;

		if (class_exists($class)) {
			$class = new $class($request);

			if (is_callable([$class, $method])) {
				try {
					if (PHP_SAPI == 'cli') {


						\MonitoLib\Dev::vde($class);


						$class->process();
					}
					$return = $class->$method(...$params);
				} catch (\Exception $e) {
					$return = [
						'code'    => $e->getCode(), 
						'file'    => $e->getFile(), 
						'line'    => $e->getLine(), 
						'message' => $e->getMessage(),
						];
				}
			} else {
				$return = [
					'code'    => 5, 
					'message' => 'Controller method not found!',
					];
			}
		} else {
			$return = [
				'code'    => 3, 
				'message' => 'Controller not found!',
				];
		}

		$return['version'] = 'LDM_APP_VERSION';

		\MonitoLib\Dev::pre($return);

		echo "$action";exit;

		$params = $request->params;

		echo $request->command . "\n";

		if (isset(self::$routes['cli'][$request->command])) {
			$action = self::$routes['cli'][$request->command];
			$parts  = explode('@', $action);
			$class  = $parts[0];
			$method = $parts[1];

			echo "$class-$method\n";

			$classCudo = new $class;

			if (class_exists($class)) {
				$class = new $class;

				// \MonitoLib\Dev::pre($class);

				if (is_callable([$class, $method])) {
					$return = $class->$method(...$params);
				} else {
					$return = [
						'code'    => 5, 
						'message' => 'Controller method not found!',
						];
				}
			} else {
				$return = [
					'code'    => 3, 
					'message' => 'Controller not found!',
					];
			}


			// return $return;
			echo "ran: " . $request->command . "\n";
		} else {
			echo "cnf: " . $request->command . "\n";
		}
	}
}