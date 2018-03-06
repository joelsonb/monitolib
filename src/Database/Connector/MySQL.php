<?php 
/**
 * Database connector
 * @author Joelson B <joelsonb@msn.com>
 * @copyright Copyright &copy; 2013 - 2018
 *  
 * @package MonitoLib
 */
namespace MonitoLib\Database\Connector;

class MySQL
{
	const VERSION = '1.0.0';

	private $conn;

	private static $instance;

	private $connection;
	private $connections = [];
	private $dbms;

	private function __construct($parameters)
	{
		$this->conn = new \PDO('mysql:host=' . $parameters->server 
			. ';dbname=' . $parameters->database 
			. ';charset=UTF8', $parameters->user, 
			$parameters->password);
		$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
	/**
	 * getInstance
	 *
	 * @return returns instance of \MonitoLib\Database\Connector\MySQL;
	 */
	public static function connect ($parameters)
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Database\Connector\MySQL($parameters);
		}

		return self::$instance;
	}
	public function beginTransaction ()
	{
		$this->conn->beginTransaction();
	}
	public function commit ()
	{
		$this->conn->commit();
	}
	public function rollback ()
	{
		$this->conn->rollback();
	}
	/**
	 * getInstance
	 *
	 * @return returns instance of \jLib\Connector;
	 */
	public static function getInstance ()
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Connector;
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
	public function getConnection ()
	{
		return $this->conn;
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