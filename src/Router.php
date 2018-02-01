<?php
/**
 * 1.0.0. - 2017-03-16
 * Inicial release
 */
namespace MonitoLib;

class Router
{
	static private $routes = [];

	private static function addNEW ($method, $url, $action, $secure = true)
	{
		echo $url . '<br />';
		$parts = explode('/', trim($url, '/'));
		\MonitoLib\Dev::pr($parts);

		$routes = [
			$method => [],
		];

		// $ca = $routes[$method];

		$maxIndex = count($parts) - 1;

		echo "\$maxIndex: $maxIndex<br />"; 

		$cf = null;
		$af = null;

		for ($i = 0; $i <= $maxIndex; $i++) {
			// Verifica se o index é uma string ou padrão
			if (in_array(substr($parts[$i], 0, 1), [':','?'])) {
				if (preg_match('/\{(.*)\}/', $parts[$i], $matchs)) {
					$index = $matchs[1];
				} else {
					if (substr($parts[$i], 0, 1) == ':') {
						$index = '.*';
					} else {
						$index = '[.*]?';
					}
				}
			} else {
				$index = $parts[$i];
			}

			// Verifica se existe array para o índice atual

			if ($i == $maxIndex) {
				$af[$index] = ['@' => $action . ($secure ? '+' : '')];
			} else {
				$af[$index] = $cf;
			}

			$cf = $af;
			$af = [];
			\MonitoLib\Dev::pr($cf);
		}

		$routes[$method] = $cf;

		self::$routes = \MonitoLib\Functions::ArrayMergeRecursive(self::$routes, $routes);
	}
	private static function add ($method, $url, $action, $secure = true)
	{
		$parts = explode('/', $url);
		// \MonitoLib\Dev::pr($parts);

		$routes = [
			$method => [],
		];

		// $ca = $routes[$method];

		$len = count($parts) - 1;

		$cf = null;
		$af = null;

		for ($i = $len; $i > 0; $i--) { 
			if (in_array(substr($parts[$i], 0, 1), [':','?'])) {
				if (preg_match('/\{(.*)\}/', $parts[$i], $matchs)) {
					$index = $matchs[1];
				} else {
					if (substr($parts[$i], 0, 1) == ':') {
						$index = '.*';
					} else {
						$index = '[.*]?';
					}
				}
			} else {
				$index = $parts[$i];
			}

			if ($i == $len) {
				// $af = [$index => $action . ($secure ? '+' : '')];
				$af[$index] = [
					'@' => $action . ($secure ? '+' : ''),
					'*' => [$method]
				];
			} else {
				$af[$index] = $cf;
			}

			$cf = $af;
			$af = [];
			// \MonitoLib\Dev::pr($cf);
		}

		// $routes[$method] = $cf;
		$routes = $cf;


		// \MonitoLib\Dev::pr(self::$routes);
		// \MonitoLib\Dev::pr($routes);



		self::$routes = \MonitoLib\Functions::ArrayMergeRecursive(self::$routes, $routes);
	}
	public static function cliOLD ($url, $action, $secure = false)
	{
		$method = 'cli';
		$parts = explode('/', $url);
		\MonitoLib\Dev::pre($parts);

		$routes = [
			$method => [],
		];

		$ca = $routes[$method];

		$len = count($parts) - 1;

		$cf = null;
		$af = null;

		for ($i = $len; $i > 0; $i--) { 
			if (in_array(substr($parts[$i], 0, 1), [':','?'])) {
				if (preg_match('/\{(.*)\}/', $parts[$i], $matchs)) {
					$index = $matchs[1];
				} else {
					if (substr($parts[$i], 0, 1) == ':') {
						$index = '.*';
					} else {
						$index = '[.*]?';
					}
				}
			} else {
				$index = $parts[$i];
			}

			if ($i == $len) {
				$af = [$index => $action . ($secure ? '+' : '')];
			} else {
				$af[$index] = $cf;
			}

			$cf = $af;
			$af = [];
		}


		$routes[$method] = $cf;
		\MonitoLib\Dev::pre($routes);

		self::$routes = \MonitoLib\Functions::ArrayMergeRecursive(self::$routes, $routes);
	}
	public static function cli ($url, $action, $secure = false)
	{

		$method = 'cli';
		$parts = explode('/', $url);
		// \MonitoLib\Dev::pre($parts);

		$routes = [
			$method => [],
		];

		$ca = $routes[$method];

		$len = count($parts) - 1;

		$cf = null;
		$af = null;

		for ($i = $len; $i >= 0; $i--) { 
			if (in_array(substr($parts[$i], 0, 1), [':','?'])) {
				if (preg_match('/\{(.*)\}/', $parts[$i], $matchs)) {
					$index = $matchs[1];
				} else {
					if (substr($parts[$i], 0, 1) == ':') {
						$index = '.*';
					} else {
						$index = '[.*]?';
					}
				}
			} else {
				$index = $parts[$i];
			}

			if ($i == $len) {
				$af = [$index => ['@' => $action]];
			} else {
				$af[$index] = $cf;
			}

			$cf = $af;
			$af = [];
		}


		$routes[$method] = $cf;
		// \MonitoLib\Dev::pre($routes);

		self::$routes = \MonitoLib\Functions::ArrayMergeRecursive(self::$routes, $routes);
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
		// TODO: usar try...catch
		// echo 'ru: ' . $request->getRequestUri() . '<br />';
		// \MonitoLib\Dev::pr(self::$routes);

		$requestMethod = $_SERVER['REQUEST_METHOD'];

		$uriParts = explode('/', trim($request->getRequestUri(), '/'));

		// \MonitoLib\Dev::pre($uriParts);

		$currentArray = [];

		$action = true;
		$params = [];


		if (isset(self::$routes)) {
			$ri = self::$routes;

			// Percorre as partes da uri para identificar a rota
			// TODO: casar regexp
			// \MonitoLib\Dev::pr($ri);

			foreach ($uriParts as $uriPart) {
				if (isset($ri[$uriPart])) {
					echo "existe $uriPart<br />";
					// $ri = self::$routes[$uriPart];
				} else {
					// self::$routes[$uriPart] = [];
					echo "nao existe $uriPart<br />";
				}
				


				// $ri = self::$routes[$uriPart];
			}

			\MonitoLib\Dev::pre($ri);

			foreach ($uriParts as $uriPart) {
				$rk = key($ri);
				// if (isset($ri[$uriPart])) {
				if (isset($ri[$uriPart])) {
					$ri = $ri[$uriPart];

					// \MonitoLib\Dev::pre($ri);
					if (isset($ri['*']) && !in_array($requestMethod, $ri['*'])) {
						return [
							'code'    => 405,
							'message' => 'Method Not Allowed!',
							'debug'   => [
								'method' => $requestMethod,
								'url'    => $request->getRequestUri(),
								],
							];
					}

					if (isset($ri['@'])) {
						$action = $ri['@'];
					}
				} elseif (preg_match("/$rk/", $uriPart)) {
					$params[] = $uriPart;
					$ri = $ri[$rk];

					// \MonitoLib\Dev::pre($ri);
					if (isset($ri['*']) && !in_array($requestMethod, $ri['*'])) {
						return [
							'code'    => 405,
							'message' => 'Method Not Allowed!',
							'debug'   => [
								'method' => $requestMethod,
								'url'    => $request->getRequestUri(),
								],
							];
					}
					if (isset($ri['@'])) {
						$action = $ri['@'];
					}
				} else {
					$action = false;
					break;
				}
			}
		}
		
		$secure = false;

		if (!$action) {
			return [
				'code'    => 404,
				'message' => 'Route not configured in the server!',
				'debug'   => [
					'method' => $requestMethod,
					'url'    => $request->getRequestUri(),
					],
				];
		}

		$parts  = explode('@', $action);
		$class  = $parts[0];
		$method = $parts[1];

		// $classCudo = new $class;

		if (class_exists($class)) {
			$class = new $class;

			if (is_callable([$class, $method])) {
				// Verifica se permite acesso sem credenciais
				if ($secure) {
					// Verifica se o usuário tem as credenciais necessárias
					// if (!$hasPower)
					// {
						$return = [
							'version' => LDM_APP_VERSION,
							'code'    => 4, 
							'message' => 'You don not have right privileges!',
							];
						return $return;
					// }
				}

				try {
					$return = $class->$method(...$params);
				} catch (\Exception $e) {
					$return = [
						'code'    => $e->getCode(), 
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

		$return['version'] = LDM_APP_VERSION;

		return $return;
	}
	static public function checkOLD ($request)
	{
		\MonitoLib\Dev::pr(self::$routes);

		$requestMethod = $_SERVER['REQUEST_METHOD'];

		$up = explode('/', trim($request->getRequestUri(), '/'));
		array_shift($up);
		$uc = count($up);
		$action = false;

		if (isset(self::$routes[$requestMethod])) {
			$ri = self::$routes[$requestMethod];
			$ip = true;
			$i = 0;
			$params = [];
			$secure = false;

			while ($ip) {
				if (isset($ri[$up[$i]])) {
					if (!is_array(current($ri))) {
						if (!isset($up[$i + 1])) {
							$action = current($ri);

							if (substr($action, -1, 1) === '+') {
								$secure = true;
								$action = substr($action, 0, -1);
							}

							$ip = false;
						}
					}

					$ri = $ri[$up[$i]];
				} else {
					foreach ($ri as $rk => $rv) {
						if (preg_match("/$rk/", $up[$i])) {
							$params[] = $up[$i];

							if (!is_array(current($ri))) {
								$action = current($ri);

								if (substr($action, -1, 1) === '+') {
									$secure = true;
									$action = substr($action, 0, -1);
								}

								$ip = false;
							}

							$ri = $ri[$rk];

							break;
						} else {
							$ip = false;
						}
					}
				}

				$i++;

				if (!isset($i)) {
					$ip = false;
				}
			}
		}

		if (!$action) {
			return [
				'code'    => 2,
				'message' => 'Route not configured in the server!',
				'debug'   => [
					'method' => $requestMethod,
					'url'    => $request->getRequestUri(),
					],
				];
		}

		$parts  = explode('@', $action);
		$class  = $parts[0];
		$method = $parts[1];

		$classCudo = new $class;

		if (class_exists($class)) {
			$class = new $class;

			if (is_callable([$class, $method])) {
				// Verifica se permite acesso sem credenciais
				if ($secure) {
					// Verifica se o usuário tem as credenciais necessárias
					// if (!$hasPower)
					// {
						$return = [
							'version' => LDM_APP_VERSION,
							'code'    => 4, 
							'message' => 'You don not have right privileges!',
							];
						return $return;
					// }
				}

				try {
					$return = $class->$method(...$params);
				} catch (\Exception $e) {
					$return = [
						'code'    => $e->getCode(), 
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

		$return['version'] = LDM_APP_VERSION;

		return $return;
	}
	static public function run ($request)
	{
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
					$return = $class->$method(...$params);
				} catch (\Exception $e) {
					$return = [
						'code'    => $e->getCode(), 
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