<?php 
/**
 * Database connector
 * @author Joelson B <joelsonb@msn.com>
 * @copyright Copyright &copy; 2013 - 2018
 *  
 * @package MonitoLib
 */
namespace MonitoLib\Database;

use \MonitoLib\App;
use \MonitoLib\Exception\InternalError;

class Connector
{
    const VERSION = '1.0.0';
    /**
    * 1.0.0 - 2019-04-17
    * first versioned
    */
	private static $instance;

	private $active     = [];
	private $configured = [];
	private $default;

	private function __construct()
	{
		$file = App::getConfigPath() . 'database.json';

		if (!is_readable($file)) {
			throw new InternalError("Arquivo $file não encontrado ou usuário sem permissão!");
		}

		$db = json_decode(file_get_contents($file), true);
		
		if (empty($db)) {
			throw new InternalError("Não existem conexões configuradas ou o arquivo $file é inválido!");
		}

		$this->configured = $db;
	}
	/**
	 * getInstance
	 *
	 * @return returns instance of \jLib\Connector;
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Database\Connector();
		}

		return self::$instance;
	}
	public function getConnection($connectionName = null)
	{
		$connectionName = $connectionName ?? $this->default;
		
		if (is_null($connectionName)) {
			throw new InternalError('Não existe uma conexão padrão e nenhuma conexão foi informada!');
		}

		$p = explode('.', $connectionName);

		$connection = $p[0];
		$enviroment = $p[1] ?? App::getEnv();
		$name = $connection . '.' . $enviroment;

		// Retorna a conexão, se configurada
		if (isset($this->active[$name])) {
			return $this->active[$name];
		}

		if (!isset($this->configured[$connection])) {
			throw new InternalError("A conexão $connection não existe!");
		}

		if (!isset($this->configured[$connection][$enviroment])) {
			throw new InternalError("O ambiente $enviroment não está configurado na conexão $connection!");
		}

		$params = $this->configured[$connection][$enviroment];
		$params['name'] = $connection;
		$params['env']  = $enviroment;

		$dbms = $this->configured[$connection][$enviroment]['dbms'];

		if ($dbms === 'Rest') {
			return $params;
		} else {
			$class = '\MonitoLib\Database\Connector\\' . $dbms;

			if (!class_exists($class)) {
				throw new InternalError("Dbms $dbms inválido!");
			}

			return $this->active[$name] = new $class($params);
		}
	}
	/**
	 * getConnectionsList
	 *
	 * @return array Connections list
	 */
	public static function getConnectionsList()
	{
		return self::$connections;
	}
	/**
	 * setConnectionName
	 * 
	 * @param string $connectionName Connection name
	 */
	public function setConnectionName($connectionName)
	{
		$this->default = $connectionName;

		// $p = explode('.', $connectionName);

		// $connectionName = $p[0];
		// $enviroment     = $p[1] ?? App::getEnv();

		// if (!isset($this->connections->$connectionName->$enviroment)) {
		// 	throw new InternalError("A conexão $connectionName não existe no ambiente $enviroment!");
		// }

		// // $this->dbms = $this->connections->$connectionName->dbms;

		// $this->connectionName = $connectionName . '.' .  $enviroment;
	}
}