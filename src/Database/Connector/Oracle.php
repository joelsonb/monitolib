<?php 
/**
 * Database connector
 * @author Joelson B <joelsonb@msn.com>
 * @since 2013-12-10
 * @copyright Copyright &copy; 2013 - 2018
 *  
 * @package MonitoLib
 */
namespace MonitoLib\Database\Connector;

class Oracle
{
	const VERSION = '1.0.0';

	private $conn;

	private static $instance;

	private $connection;
	private $connections = [];
	private $dbms;
    private $executeMode = 32;
    /*
    * Modes:
    * OCI_COMMIT_ON_SUCCESS: 32
    * OCI_DESCRIBE_ONLY: 16
    * OCI_NO_AUTO_COMMIT: 0
    */

	private function __construct ($parameters)
	{
		// \MonitoLib\Dev::ee('came here');
		$this->conn = oci_connect($parameters->user, $parameters->password, $parameters->server);

		if (!$this->conn) {
			$m = oci_error();
			throw new \Exception('Error connecting to Oracle database: ' . $m['message']);
		}
	}
	/**
	 * getInstance
	 *
	 * @return returns instance of \MonitoLib\Database\Connector\MySQL;
	 */
	public static function connect ($parameters)
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Database\Connector\Oracle($parameters);
		}

		return self::$instance;
	}
	public function beginTransaction ()
	{
		$this->executeMode = OCI_NO_AUTO_COMMIT;
	}
	public function commit ()
	{
		@oci_commit($this->conn);
		$this->executeMode = OCI_COMMIT_ON_SUCCESS;
	}
	public function rollback ()
	{
		@oci_rollback($this->conn);
		$this->executeMode = OCI_COMMIT_ON_SUCCESS;
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
	public function execute ($stt)
	{
		return @oci_execute($stt, $this->executeMode);
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