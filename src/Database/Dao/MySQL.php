<?php
namespace MonitoLib\Database\Dao;

use \MonitoLib\Functions;

class MySQL extends \MonitoLib\Database\Dao\Filter implements \MonitoLib\Database\Dao
{
	protected $dto;
	protected $dtoName;
	protected $conn;
	protected $connection;
	protected $model;
	private $namespace = '';

	public function __construct ()
	{
		if (is_null($this->conn)) {
			$connector  = \MonitoLib\Database\Connector::getInstance();
			$this->conn = $connector->getConnection()->getConnection();
		}

		$this->dtoName = str_replace('dao\\','dto\\', get_class($this));
		$model = str_replace('dao\\','model\\', get_class($this));

		if (class_exists($this->dtoName)) {
			$this->dto   = new $this->dtoName;
		}

		if (class_exists($model)) {
			$this->model = new $model;

			$this->setFields($this->model->getFields());
			$this->setTableName($this->model->getTableName());
		}

		$class = get_class($this);

		$this->namespace .= str_replace('dao\\', '', substr($class, 0, strrpos($class, '\\')));
	}
	// TODO: to implement
	/**
	* count
	*/
	public function count ()
	{

	}
	/**
	* dataset
	*/
	public function dataset ()
	{
		$data = [];

		$sql = $this->renderCountAllSql();
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
		$res = $stmt->fetch(\PDO::FETCH_NUM);

		$total = $res[0];

		if ($total > 0) {
			$sql = $this->renderCountSql();
			$stmt = $this->conn->prepare($sql);
			$stmt->execute();
			$res = $stmt->fetch(\PDO::FETCH_NUM);

			$count = $res[0];

			if ($count > 0) {

				// \MonitoLib\Dev::e($this->renderSql());

				$data = $this->list();
			}
		}

		// Reset filter
		$this->reset();

		return [
			'count' => $count,
			'data'  => $data,
			'total' => $total,
			'page'  => $this->getPage(),
			'pages' => ceil($count / $this->getLimitOffset())
		];
	}
	/**
	* delete
	* @todo allow delete using dtoObject
	* @todo validate deleting without parameters
	* @todo validate deleting without all key parameters
	*/
	public function delete (...$params)
	{
		$this->setCommand('DELETE');

		if ($this->model->getTableType() == 'view') {
			throw new \Exception('A view object is readonly!');
		}

		if (count($params) > 0) {
			$keys = $this->model->getPrimaryKeys();

			if (count($params) !== count($keys)) {
				throw new \Exception("Invalid parameters number!", 1);
			}

			if (count($params) > 1) {
				foreach ($params as $p) {
					foreach ($keys as $k) {
						$this->andEqual($k, $p);
					}
				}
			} else {
				$this->andEqual($keys[0], $params[0]);
			}
		}

		$stmt = $this->conn->prepare($this->renderSql());
		$stmt->execute();

		// Reset filter
		$this->reset();

		return $stmt->rowCount();
	}
	/**
	* get
	*/
	public function get ()
	{
		$sql = $this->renderSql();

		$stmt = $this->conn->prepare($sql);
		$stmt->execute();

		if ($this->complete && !is_null($this->model)) {
			$i = 1;

			foreach ($this->model->getFields() as $f) {
				$var = Functions::toLowerCamelCase($f);
				$stmt->bindColumn($i, $$var);
				$i++;
			}

			$dto = NULL;

			if ($stmt->fetch()) {
				$dto = $this->dto;

				foreach ($this->model->getFields() as $f) {
					$var = Functions::toLowerCamelCase($f);
					$set = 'set' . ucfirst($var);
					$dto->$set($$var);
				}
			}

			$stmt = NULL;
		} else {
			$dto = $this->toDataTransferObject($stmt->fetch(\PDO::FETCH_ASSOC));
		}	

		// Reset filter
		$this->reset();
		return $dto;
	}
	/**
	* getById
	*/
	public function getById (...$params)
	{
		if (count($params) > 0) {
			$keys = $this->model->getPrimaryKeys();

			if (count($params) !== count($keys)) {
				throw new \Exception("Invalid parameters number!", 1);
			}

			if (count($params) > 1) {
				foreach ($params as $p) {
					foreach ($keys as $k) {
						$this->andEqual($k, $p);
					}
				}
			} else {
				$this->andEqual($keys[0], $params[0]);
			}

			return $this->get();
		}
	}
	/**
	* getConnection
	*/
	public function getConnection ()
	{
		return $this->conn;
	}
	/**
	* getLastId
	*/
	public function getLastId ()
	{
		return $this->conn->lastInsertId();
	}
	/**
	* insert
	*/
	public function insert ($dto)
	{
		try {
			if ($this->model->getTableType() == 'view') {
				throw new \Exception('Não é possível inserir registros em uma view!');
			}

			if (!$dto instanceof $this->dtoName) {
				throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
			}

			if (method_exists($dto, 'setInsTime') && is_null($dto->getInsTime())) {
				$dto->setInsTime(date('Y-m-d H:i:s'));
			}
			if (method_exists($dto, 'setInsUserId') && is_null($dto->getInsUserId())) {
				// TODO: buscar o usuário atual
				$dto->setInsUserId(751129730);
			}
			
			$sql = 'INSERT INTO ' . $this->model->getTableName() . ' ('
				 . '`' . implode('`,`', $this->model->getFieldsInsert()) . '`) '
				 . 'VALUES (' . substr(str_repeat('?,', count($this->model->getFieldsInsert())), 0, -1) . ')';
			$stmt = $this->conn->prepare($sql);

			$i = 1;

			foreach ($this->model->getFieldsInsert() as $f) {
				$var = Functions::toLowerCamelCase($f);
				$get = 'get' . ucfirst($var);

				$$var = $dto->$get();

				$stmt->bindParam($i, $$var);
				$i++;
			}

			$stmt->execute();
			$stmt = NULL;

			if (method_exists($dto, 'setId') && is_null($dto->getId())) {
				$dto->setId($this->conn->lastInsertId());
				return $dto;
			}
		} catch (\PDOException $e) {
			\MonitoLib\Dev::pre($e);
		}
	}
	/**
	* list
	*/
	public function list ()
	{
		$sql = $this->renderSql();
		// \MonitoLib\Dev::e($sql);
		// echo "$sql\n";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute();

		if ($this->complete && !is_null($this->model)) {
			$i = 1;

			foreach ($this->model->getFields() as $f) {
				$var = Functions::toLowerCamelCase($f);
				$stmt->bindColumn($i, $$var);
				$i++;
			}

			$data = array();

			while ($stmt->fetch()) {
				$dto = new $this->dtoName;

				foreach ($this->model->getFields() as $f) {
					$var = Functions::toLowerCamelCase($f);
					$set = 'set' . ucfirst($var);
					$dto->$set($$var);
				}

				$data[] = $dto;
			}

			$stmt = NULL;
		} else {
			$data = $this->toDataTransferObject($stmt->fetchAll(\PDO::FETCH_ASSOC));
		}

		// Reset filter
		$this->reset();

		return $data;
	}
	/**
	* toDataTransferObject
	*/
	private function toDataTransferObject ($data, $dtoClass = null)
	{
		if (is_null($dtoClass)) {
			$dto = new \MonitoLib\Database\Dto($data);
			return $dto->getData();
		} else {
			return new $dtoClass;
		}
	}
	/**
	* truncate
	*/
	public function truncate ()
	{
		$sql = 'TRUNCATE TABLE ' . $this->model->getTableName();
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
	}
	/**
	* update
	*/
	public function update ($dto)
	{
		try {
			if ($this->model->getTableType() == 'view') {
				throw new \Exception('Não é possível atualizar registros de uma view!');
			}

			if (!$dto instanceof $this->dtoName) {
				throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
			}

			if (method_exists($dto, 'setUpdTime') && is_null($dto->getUpdTime())) {
				$dto->setUpdTime(date('Y-m-d H:i:s'));
			}

			if (method_exists($dto, 'setUpdUserId') && is_null($dto->getUpdUserId())) {
				// TODO: buscar o usuário atual
				$dto->setUpdUserId(751129730);
			}

			$update = NULL;
			$key    = NULL;
			
			// TODO: isso é só pra funcionar, depois é para fazer corretamente
			$aKeys  = array();
			$aFields = array();
			
			foreach ($this->model->getFields() as $f) {
				if ($f == $this->model->getPrimaryKey()) {
					$aKeys[] = $f;
					$key    .= "$f = ? AND";
				} else {
					$update .= "`$f` = ?,";
					$aFields[] = $f;
				}
			}
			
			$update = substr($update, 0, -1);
			$key    = substr($key, 0, -3);

			$sql  = 'UPDATE ' . $this->model->getTableName() . " SET $update WHERE $key";
			
			
			// \MonitoLib\Dev::e($sql);
			
			
			$stmt = $this->conn->prepare($sql);

			$i = 1;

			$aFields = array_merge($aFields, $aKeys);
			
			foreach ($aFields as $f) {
				$var = Functions::toLowerCamelCase($f);
				$get = 'get' . ucfirst($var);

				$$var = $dto->$get();
				$stmt->bindParam($i, $$var);
				$i++;
			}

			$stmt->execute();
			$updated = $stmt->rowCount();
			$stmt = NULL;

			return $updated;
		} catch (\PDOException $e) {
			\MonitoLib\Dev::pre($e);
		}
	}
}