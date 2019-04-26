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

class Oracle
{
    const VERSION = '1.0.0';
    /**
    * 1.0.0 - 2019-04-17
    * first versioned
    */

	private $conn;
    private $executeMode;
    private static $instance;

	private function __construct ($parameters)
	{
		$this->executeMode = OCI_COMMIT_ON_SUCCESS;
		$this->conn = @oci_connect($parameters->user, $parameters->password, $parameters->server, 'AL32UTF8');

		if (!$this->conn) {

			$db = debug_backtrace();
			$e  = oci_error();

			$error = [
				'message' => $e['message'],
				'file' => $db[1]['file'],
				'line' => $db[1]['line'],
			];

			throw new DatabaseError('Erro ao conectar no banco de dados!', $error);
		}
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
	public static function connect ($parameters)
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Database\Connector\Oracle($parameters);
		}

		return self::$instance;
	}
	public function execute ($stt)
	{
		$exe = @oci_execute($stt, $this->executeMode);

        if (!$exe) {
            $e = oci_error($stt);
            throw new DatabaseError('Ocorreu um erro no banco de dados!', $e);
        }

        return $stt;
	}
	public function fetchArrayAssoc ($stt)
	{
        return oci_fetch_array($stt, OCI_ASSOC | OCI_RETURN_NULLS);
	}
	public function fetchArrayNum ($stt)
	{
        return oci_fetch_array($stt, OCI_NUM | OCI_RETURN_NULLS);
	}
	public function parse ($sql)
	{
		$stt = oci_parse($this->conn, $sql);
        return $stt;
	}
	public function rollback ()
	{
		@oci_rollback($this->conn);
		$this->executeMode = OCI_COMMIT_ON_SUCCESS;
	}
	public function transform ($function)
	{
		switch ($function) {
			case 'UPPERCASE':
				return 'UPPER';
			default:
				return $function;
		}
	}
}