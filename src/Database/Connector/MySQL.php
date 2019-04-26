<?php 
/**
 * Database connector
 * @author Joelson B <joelsonb@msn.com>
 * @copyright Copyright &copy; 2013 - 2018
 *  
 * @package MonitoLib
 */
namespace MonitoLib\Database\Connector;

use \MonitoLib\Exception\DatabaseError;
use \MonitoLib\Exception\InternalError;

class MySQL
{
    const VERSION = '1.0.0';
    /**
    * 1.0.0 - 2019-04-17
    * first versioned
    */

	private $conn;
	private static $instance;

	private function __construct ($parameters)
	{
		try {
			$this->conn = new \PDO('mysql:host=' . $parameters->server 
				. ';dbname=' . $parameters->database 
				. ';charset=UTF8', $parameters->user, 
				$parameters->password);
			$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			$error = [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			];

			throw new DatabaseError('Erro ao conectar no banco de dados!', $error);
		}
	}
	public function beginTransaction ()
	{
		$this->conn->beginTransaction();
	}
	public function commit ()
	{
		$this->conn->commit();
	}
	public static function connect ($parameters)
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Database\Connector\MySQL($parameters);
		}

		return self::$instance;
	}
	public function execute ($stt)
	{
		try {
			$stt->execute();
			return $stt;
		} catch (\PDOException $e) {
			$error = [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			];

			throw new DatabaseError('Erro ao conectar no banco de dados!', $error);
		}
	}
	public function fetchArrayAssoc ($stt)
	{
        return $stt->fetch(\PDO::FETCH_ASSOC);
	}
	public function fetchArrayNum ($stt)
	{
        return $stt->fetch(\PDO::FETCH_NUM);
	}
	public function parse ($sql)
	{
        return $this->conn->prepare($sql);
	}
	public function rollback ()
	{
		$this->conn->rollback();
	}
}