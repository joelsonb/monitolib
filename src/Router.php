<?php
/**
 * 1.0.0. - 2017-03-16
 * Inicial release
 */
namespace vendor\ldm;

class Router
{
	static private $routes = [];

	private static function add ($method, $url, $action, $secure = true)
	{
		$parts = explode('/', $url);

		$routes = [
			$method => [],
		];

		$ca = $routes[$method];

		$len = count($parts) - 1;

		$cf = null;
		$af = null;

		for ($i = $len; $i > 0; $i--)
		{ 
			if (in_array(substr($parts[$i], 0, 1), [':','?']))
			{
				if (preg_match('/\{(.*)\}/', $parts[$i], $matchs))
				{
					$index = $matchs[1];
				}
				else
				{
					if (substr($parts[$i], 0, 1) == ':')
					{
						$index = '.*';
					}
					else
					{
						$index = '[.*]?';
					}
				}
			}
			else
			{
				$index = $parts[$i];
			}

			if ($i == $len)
			{
				$af = [$index => $action . ($secure ? '+' : '')];
			}
			else
			{
				$af[$index] = $cf;
			}

			$cf = $af;
			$af = [];
		}

		$routes[$method] = $cf;

		self::$routes = \vendor\ldm\Functions::ArrayMergeRecursive(self::$routes, $routes);
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
		// \vendor\ldm\Dev::pr(self::$routes);

		$requestMethod = $_SERVER['REQUEST_METHOD'];

		$up = explode('/', $request->getRequestUri());
		array_shift($up);
		$uc = count($up);
		$ri = self::$routes[$requestMethod];
		$ip = true;
		$i = 0;
		$action = 'erro';
		$params = [];
		$secure = false;

		while ($ip)
		{
			if (isset($ri[$up[$i]]))
			{
				if (!is_array(current($ri)))
				{
					if (!isset($up[$i + 1]))
					{
						$action = current($ri);

						if (substr($action, -1, 1) === '+')
						{
							$secure = true;
							$action = substr($action, 0, -1);
						}

						$ip = false;
					}
				}

				$ri = $ri[$up[$i]];
			}
			else
			{
				foreach ($ri as $rk => $rv)
				{
					if (preg_match("/$rk/", $up[$i]))
					{
						$params[] = $up[$i];

						if (!is_array(current($ri)))
						{
							$action = current($ri);

							if (substr($action, -1, 1) === '+')
							{
								$secure = true;
								$action = substr($action, 0, -1);
							}

							$ip = false;
						}

						$ri = $ri[$rk];

						break;
					}
					else
					{
						$ip = false;
					}
				}
			}

			$i++;

			if (!isset($i))
			{
				$ip = false;
			}
		}

		if ($action == 'erro')
		{
			return [
				'code'    => 2,
				'message' => 'Route not configured!',
				'debug'   => [
					'method' => $requestMethod,
					'url'    => $request->getRequestUri(),
					],
				];
		}

		$parts  = explode('@', $action);
		$class  = $parts[0];
		$method = $parts[1];

		if (class_exists($class))
		{
			$class = new $class;

			if (is_callable([$class, $method]))
			{
				// Verifica se permite acesso sem credenciais
				if ($secure)
				{
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

				$return = $class->$method(...$params);
			}
			else
			{
				$return = [
					'code'    => 5, 
					'message' => 'Controller method not found!',
					];
			}
		}
		else
		{
			$return = [
				'code'    => 3, 
				'message' => 'Controller not found!',
				];
		}

		$return['version'] = LDM_APP_VERSION;

		return $return;
	}
}