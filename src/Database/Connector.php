<?php 
/**
 * Database connector
 * @author Joelson B <joelsonb@msn.com>
 * @since 2013-12-10
 * @copyright Copyright &copy; 2013 - 2018
 *  
 * @package MonitoLib
 */
namespace MonitoLib\Database;

class Connector
{
	const VERSION = '1.0.0';

	private static $instance;

	private $connection;
	private $connections = [];
	private $dbms;

	private function __construct()
	{
		$file = MONITO_CONFIG_DIR . 'database.json';

		// TODO: validar arquivos
		if (!is_readable($file)) {
			throw new \Exception("File $file not found or permition error!");
		}

		$db = json_decode(file_get_contents($file));
		
		if (is_null($db)) {
			throw new \Exception("File $file can not be parsed!");
		}
		
		$this->connections = new \stdClass;

		if (count($db) > 0) {
			foreach ($db as $dk => $dv) {
				//self::$connections->$dk->$dv;
				$this->connections->$dk = $dv;
				$this->connections->$dk->name = $dk;
				$this->connections->$dk->instance = NULL;
			}
		}
	}
	/**
	 * getInstance
	 *
	 * @return returns instance of \jLib\Connector;
	 */
	public static function getInstance ()
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Database\Connector;
		}

		return self::$instance;
	}
	public static function closeConnection ($conn = NULL)
	{
		if (is_null($conn)) {
			foreach (self::$connections as $c) {
				_vd($c);
				$c->instance->close();
			}

			self::$connections = NULL;
		} else {
			if (key_exists($conn, self::$connections)) {
				self::$connections->$conn->instance = NULL;
				//unset(self::$connections->$conn);
			}
		}
	}
	public function getConfig ($conn = NULL)
	{
		if (count($this->connections) == 0) {
			throw new \Exception('There is no connections!');
		}

		if (is_null($this->connection) and is_null($conn)) {
			throw new \Exception('There is no default connection!');
		}

		if (is_null($conn)) {
			$conn = $this->connection;
		}

		if (!isset($this->connections->$conn)) {
			throw new \Exception("Connection $conn is not configured!");
		}

		return $this->connections->$conn;
	}
	public function getConnection ($conn = null)
	{
		if (count($this->connections) == 0) {
			throw new \Exception('There is no connections!');
		}

		if (is_null($this->connection) and is_null($conn)) {
			throw new \Exception('There is no default connection!');
		}

		if (is_null($conn)) {
			$conn = $this->connection;
		}

		if (!isset($this->connections->$conn)) {
			throw new \Exception("Connection $conn is not configured!");
		}

		if (is_null($this->connections->$conn->instance)) {
			switch ($this->connections->$conn->dbms) {
				case 'mysql':
					$obj = new \mysqli($this->connections->$conn->server, $this->connections->$conn->user, $this->connections->$conn->password, $this->connections->$conn->database);
					break;
				case 'mysql-pdo':
					return \MonitoLib\Database\Connector\MySQL::connect($this->connections->$conn);
				case 'oracle':
					return \MonitoLib\Database\Connector\Oracle::connect($this->connections->$conn);
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
		if (!key_exists($conn, $this->connections)) {
			throw new \Exception("There is no connection \"$conn\"!");
		}

		$this->connection = $conn;
	}
}