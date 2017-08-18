<?php 
/**
 * Database connector
 * @author Joelson Batista <joelsonb@msn.com>
 * @since 2013-12-10
 * @copyright Copyright &copy; 2009 - 2017
 *  
 * @package Lib
 */
namespace vendor\ldm;

class Connector
{
	private static $instance;

	// Array de conexões do sistema
	private $connection;
	private $connections = array();
	private $dbms;

	private function __construct()
	{
		$file = JL_SITE_PATH . 'config' . DIRECTORY_SEPARATOR . 'database.json';

		// TODO: validar arquivos
		if (!is_readable($file))
		{
			throw new \Exception("File $file not found or permition error!");
		}

		$db = json_decode(file_get_contents($file));
		
		if (is_null($db))
		{
			throw new \Exception("File $file can not be parsed!");
		}
		
		$this->connections = new \stdClass;

		if (count($db) > 0)
		{
			foreach ($db as $dk => $dv)
			{
				//self::$connections->$dk->$dv;
				$this->connections->$dk = $dv;
				$this->connections->$dk->name = $dk;
				$this->connections->$dk->instance = NULL;
			}
		}
		
		// TODO: Verificar se a conexão foi bem sucedida e atualizar o arquivo de conexões
		
		
		//_pre(self::$connections);
		
		/*
		_pre(self::$connections);
		


		if (isset($db[$conn]))
		{
			require_once JLIB_LIB_PATH . 'db_' . $db[$conn]['dbms'] . '.php';
			// Todo: mudar de constante para variável

			if (!defined('JLIB_DBMS'))
			{
				define('JLIB_DBMS', $db[$conn]['dbms']);
			}

			self::$connections[$conn] = new Db($db[$conn]);
		}
		else
		{
			throw new Exception('A conexão <b>' . $conn . '</b> não está configurada no servidor!');
		}
		*/
	}
	/**
	 * getInstance
	 *
	 * @return returns instance of \jLib\Connector;
	 */
	public static function getInstance ()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new \vendor\ldm\Connector;
		}

		return self::$instance;
	}
	public static function closeConnection ($conn = NULL)
	{
		if (is_null($conn))
		{
			foreach (self::$connections as $c)
			{
				_vd($c);
				$c->instance->close();
			}

			self::$connections = NULL;
		}
		else
		{
			if (key_exists($conn, self::$connections))
			{
				self::$connections->$conn->instance = NULL;
				//unset(self::$connections->$conn);
			}
		}
	}
	public function getConnection ($conn = NULL)
	{
		if (count($this->connections) == 0)
		{
			throw new \Exception('There are no connections!');
		}

		if (is_null($this->connection) and is_null($conn))
		{
			// $dbt = debug_backtrace();
			//\lib\dm\Dev::pre($dbt);
			
			throw new \Exception('There is no default connection!');
		}

		if (is_null($conn))
		{
			$conn = $this->connection;
		}

		if (!isset($this->connections->$conn))
		{
			throw new \Exception("Connection $conn is not configured!");
		}

		if (is_null($this->connections->$conn->instance))
		{
			switch ($this->connections->$conn->dbms)
			{
				case 'mysql':
				case 'mysql-pdo':
					$obj = new \PDO('mysql:host=' . $this->connections->$conn->server 
						. ';dbname=' . $this->connections->$conn->database 
						. ';charset=UTF8', $this->connections->$conn->user, 
						$this->connections->$conn->password);
					$obj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
					break;
				case 'oracle':
						$obj = oci_connect($this->connections->$conn->user, $this->connections->$conn->password, $this->connections->$conn->server);

						if (!$obj)
						{
							$m = oci_error();
							throw new \Exception($m['message']);
						}
					break;
			}

			$this->dbms = $this->connections->$conn->dbms;

			$this->connections->$conn->instance = $obj;
		}

		return $this->connections->$conn->instance;
	}
	/**
	 * getConnectionsList
	 *
	 * @return array Connections list
	 */
	public static function getConnectionsList ()
	{
		return self::$connections;
	}
	public function getDbms ()
	{
		return $this->dbms;
	}
	/**
	 * setConnection
	 * 
	 * @param string $conn Connection name
	 */
	public function setConnection ($conn)
	{
		if (!key_exists($conn, $this->connections))
		{
			throw new \Exception("There is no connection \"$conn\"!");
		}

		$this->connection = $conn;
	}
}