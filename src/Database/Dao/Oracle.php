<?php
namespace MonitoLib\Database\Dao;

class Oracle
{
	protected $dto;
	protected $dtoName;
	protected $conn;
	protected $model;
	private $executeMode = 32;
	/*
	* Modes:
	* OCI_COMMIT_ON_SUCCESS: 32
	* OCI_DESCRIBE_ONLY: 16
	* OCI_NO_AUTO_COMMIT: 0
	*/

	private $namespace = '\\';

	public function __construct ()
	{
		if (is_null($this->conn)) {
			$connector  = \MonitoLib\Database\Connector::getInstance();
			$this->conn = $connector->getConnection();
		}

		$this->dtoName = str_replace('dao\\','dto\\', get_class($this));
		$model = str_replace('dao\\','model\\', get_class($this));

		if (class_exists($this->dtoName)) {
			$this->dto   = new $this->dtoName;
		}
		if (class_exists($model)) {
			$this->model = new $model;
		}
		
		$class = get_class($this);

		$this->namespace .= str_replace('\\dao', '', substr($class, 0, strrpos($class, '\\'))) . '\\';

		// echo $this->namespace . "\n";
		// exit;
	}
	public function delete ($mix)
	{
		$keys = [];

		if (is_array($mix)) {
			$keys[] = $mix;
		} else {
			$keys[][] = $mix;
		}

		$sql = 'DELETE FROM ' . $this->model->getTableName() . ' WHERE ';

		// List all payload keys
		foreach ($keys as $pks) {
			// Checks if model keys and payload keys have the same length
			if (count($pks) != count($this->model->getPrimaryKey(true))) {
				throw new \Exception('Parâmetro incompatível!', 1);
			}

			// \MonitoLib\Dev::pre($this->model->getPrimaryKey(true));

			// Prepares query with parameters
			foreach ($this->model->getPrimaryKey(true) as $pkk => $pkv) {
				$sql .= "$pkv = :$pkv AND";
			}

			$sql = substr($sql, 0, -4);

			// echo $sql;exit;

			$stt = oci_parse($this->conn, $sql);

			// \MonitoLib\Dev::pre($pks);

			foreach ($this->model->getPrimaryKey(true) as $pkk => $pkv) {
				$var = $pks[$pkk];

				// \MonitoLib\Dev::vde($var);

				oci_bind_by_name($stt, ":$pkv", $var);
			}


			$exe = @oci_execute($stt);
		}

		if (!$exe) {
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}
	}
	public function insert ($dto)
	{
		if (!$dto instanceof $this->dtoName)
		{
			throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
		}
		
		//\jLib\Dev::vde($this->conn);

		$sql = 'INSERT INTO ' . $this->model->getTableName() . ' ('
			 . implode(',', $this->model->getFields()) . ') '
			 . 'VALUES (:' . implode(',:', $this->model->getFields()) . ')';
		$stt = oci_parse($this->conn, $sql);
		
		//echo "$sql\n";

		foreach ($this->model->getFields() as $f)
		{
			$var = \MonitoLib\Functions::toLowerCamelCase($f);
			$get = 'get' . ucfirst($var);

			$$var = $dto->$get();

			// Checks if ins_date and upd_date are null and set current date as its values
			//if (in_array($f, array('ins_date', 'upd_date')))
			//{
			//	if (is_null($$var))
			//	{
			//		$$var = date('Y-m-d H:i:s');
			//	}
			//}
			
			//echo ":$var = {$$var}\n";

			if (!@oci_bind_by_name($stt, ':' . $f, $$var))
			{
				throw new \Exception("Error on :$f bind!");
			}
		}

		$exe = @oci_execute($stt, $this->executeMode);

		if (!$exe)
		{
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}
	}
	public function getByFilter ($filter)
	{
		$sql = 'SELECT ' . implode(',', $this->model->getFields())
			 . ' FROM ' . $this->model->getTableName() . $filter;

		// echo "$sql--\n";
		$stt = oci_parse($this->conn, $sql);
		$exe = @oci_execute($stt);

		if (!$exe)
		{
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}


		//$i = 1;
		//
		//foreach ($this->model->getFields() as $f)
		//{
		//	$var = \MonitoLib\Functions::toLowerCamelCase($f);
		//	$stmt->bindColumn($i, $$var);
		//	$i++;
		//}

		//$dto = NULL;
		//
		$result = oci_fetch_array($stt, OCI_NUM | OCI_RETURN_NULLS);
		//
		//if ($stt->fetch())
		//{
		//	$dto = $this->dto;
		//
		//	foreach ($this->model->getFields() as $f)
		//	{
		//		$var = \MonitoLib\Functions::toLowerCamelCase($f);
		//		$set = 'set' . ucfirst($var);
		//		$dto->$set($$var);
		//	}
		//}
		
		

		$dto = NULL;

		if ($result) {
			$dto = $this->dto;

			$i = 0;

			foreach ($this->model->getFields() as $f) {
				$var = \MonitoLib\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($result[$i]);
				$i++;
			}
		}
		
		//\jLib\Dev::vde($dto);

		//$stmt = NULL;

		return $dto;
	}
	public function getBySql ($sql)
	{
		$stt = oci_parse($this->conn, $sql);
		$exe = @oci_execute($stt);

		if (!$exe) {
			$e = @oci_error($stt);
			throw new \Exception($e['message']);
		}

		$result = oci_fetch_array($stt, OCI_NUM | OCI_RETURN_NULLS);

		$dto = NULL;

		if ($result) {
			$dto = $this->dto;

			$i = 0;

			foreach ($this->model->getFields() as $f) {
				$var = \MonitoLib\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($result[$i]);
				$i++;
			}
		}

		return $dto;
	}
	public function getById ($id)
	{
		$sql = 'SELECT ' . implode(',', $this->model->getFields())
			 . ' FROM ' . $this->model->getTableName() . ' WHERE ' . $this->model->getPrimaryKey() . ' = :id';

		// echo "$sql\n";
		$stt = oci_parse($this->conn, $sql);
		oci_bind_by_name($stt, ':id', $id);
		$exe = @oci_execute($stt);

		if (!$exe)
		{
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}


		//$i = 1;
		//
		//foreach ($this->model->getFields() as $f)
		//{
		//	$var = \MonitoLib\Functions::toLowerCamelCase($f);
		//	$stmt->bindColumn($i, $$var);
		//	$i++;
		//}

		//$dto = NULL;
		//
		$result = oci_fetch_array($stt, OCI_NUM | OCI_RETURN_NULLS);
		//
		//if ($stt->fetch())
		//{
		//	$dto = $this->dto;
		//
		//	foreach ($this->model->getFields() as $f)
		//	{
		//		$var = \MonitoLib\Functions::toLowerCamelCase($f);
		//		$set = 'set' . ucfirst($var);
		//		$dto->$set($$var);
		//	}
		//}
		
		

		$dto = NULL;

		if ($result)
		{
			$dto = $this->dto;

			$i = 0;

			foreach ($this->model->getFields() as $f)
			{
				$var = \MonitoLib\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($result[$i]);
				$i++;
			}
		}
		
		//\jLib\Dev::vde($dto);

		//$stmt = NULL;

		return $dto;
	}
	public function getList ($filter = NULL)
	{
		$data   = array();
		$fields = [
				   $this->model->getName() => [
											   'fields'    => $this->model->getFields(),
											   'model'     => $this->model->getName(),
											   'namespace' => $this->namespace,
											   ]
				   ];

		$sql = 'SELECT ';

		foreach ($fields as $fk => $fv) {
			foreach ($fv['fields'] as $f) {
				$sql .= "$f,";
			}
		}
		
		$sql  = substr($sql, 0, -1) . ' FROM ' . $this->model->getTableName() . $filter;

		//echo "$sql\n";
		
		$stt = oci_parse($this->conn, $sql);
		$exe = @oci_execute($stt);

		if (!$exe) {
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}

		while ($r = oci_fetch_row($stt)) {
			$class = $this->namespace . 'dto\\' . $fv['model'];

			$dto = new $class;
				
			$i = 0;

			foreach ($fields as $fk => $fv) {
				foreach ($fv['fields'] as $f) {
					$var = \MonitoLib\Functions::toLowerCamelCase($fk.$f);
					$set = 'set' . ucfirst(\MonitoLib\Functions::toLowerCamelCase($f));
					$dto->$set($r[$i]);
					$i++;
				}

			}

			$data[] = $dto;
		}

		return $data;
	}
	public function limitedSql ($sql, $filter)
	{
		$rowStart = (($filter->getPage() - 1) * $filter->getLimitOffset()) + 1;
		$rowEnd   = $filter->getLimitOffset() * $filter->getPage();

		$limitedSql = 'SELECT * FROM ('
					. 'SELECT a.*, ROWNUM rnum FROM ('
					. $sql
					. ') a WHERE rownum <= ' . $rowEnd
					. ') WHERE rnum >= ' . $rowStart
					;

		return $limitedSql;
	}
	public function execute ($sql)
	{
		$stt = oci_parse($this->conn, $sql);
		$exe = @oci_execute($stt);

		if (!$exe) {
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}
	}
	public function read ($filter = NULL)
	{
		//\jLib\Dev::e($this->namespace);
		
		$dataSet = new \jLib\DataSet;
		$total   = 0;
		$count   = 0;
		$data    = array();
		$sqlJoin = NULL;

		$fields = [
				   $this->model->getName() => [
											   'fields'    => $this->model->getFields(),
											   'model'     => $this->model->getName(),
											   'namespace' => $this->namespace,
											   ]
				   ];

		$joins  = $this->model->getJoins();

		//\jLib\Dev::pre($joins);
		$sqlJoin .= 'FROM ' . $this->model->getTableName() . ' ';

		if (count($joins))
		{
			$sqlJoin .= $this->model->getName() . ' ';

			foreach ($joins as $jk => $jv)
			{
				$model  = new $jv['model'];
				$fields[$jk] = [
								'fields'    => $model->getFields(),
								'model'     => $model->getName(),
								'namespace' => str_replace('model', '', substr($jv['model'], 0, strrpos($jv['model'], '\\'))),
								];

				$sqlJoin .= $jv['type'] . ' ' . $model->getTableName() . ' ' . $jk . ' ON ';

				foreach ($jv['relations'] as $k => $v)
				{
					$sqlJoin .= $this->model->getName() . '.' . $k . " = $jk.$v AND";
				}

				$sqlJoin = substr($sqlJoin, 0, -3);
			}

			//$sqlJoin = substr($sqlJoin, 0, -3);
		}
		
		$sql  = 'SELECT COUNT(*) ' . $sqlJoin;
		
		//if (!is_null($sqlJoin))
		//{
		//	$sql .= ' ' . $sqlJoin;
		//}

		//\jLib\Dev::pre($fields);
		//\jLib\Dev::e($sql);
		
		//\jLib\Dev::vde($this->conn);
		

		// Get total records in table
		//$stmt = $this->conn->prepare($sql . $filter->getFixedCriteria());
		$stt = oci_parse($this->conn, $sql . $filter->getFixedCriteria());
		$exe = @oci_execute($stt);

		if (!$exe)
		{
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}

		$r     = oci_fetch_row($stt);
		$total = $r[0];

		
		//\jLib\Dev::vde($total);

		// If has records in table, get filtered count
		if ($total > 0)
		{
			// Get total filtered records
			$stt = oci_parse($this->conn, $sql . $filter->getCountCriteria());
			$exe = @oci_execute($stt);

			if (!$exe)
			{
				$e = oci_error($stt);
				throw new \Exception($e['message']);
			}

			$r     = oci_fetch_row($stt);
			$count = $r[0];
		}

		if ($count > 0)
		{
			$sql = 'SELECT ';
			
			foreach ($fields as $fk => $fv)
			{
				foreach ($fv['fields'] as $f)
				{
					$sql .= "$fk.$f,";
				}
			}
			
			$filter->setDbms(4);

			$sql = substr($sql, 0, -1) . ' ' . $sqlJoin . $filter;
			$sql = $this->limitedSql($sql, $filter);
			
			//\jLib\Dev::e($sql);

			$stt = oci_parse($this->conn, $sql);
			$exe = @oci_execute($stt);
	
			if (!$exe)
			{
				$e = oci_error($stt);
				throw new \Exception($e['message']);
			}

			// bind result variables
			//$i = 1;
			//
			//foreach ($fields as $fk => $fv)
			//{
			//	foreach ($fv['fields'] as $f)
			//	{
			//		$var = \MonitoLib\Functions::toLowerCamelCase($fk.$f);
			//		$stmt->bindColumn($i, $$var);
			//		$i++;
			//	}
			//}
			
			//\jLib\Dev::pre($stmt->fetch(\PDO::FETCH_ASSOC));

			//$stmt->bindColumn(1, $ufId);
			//$stmt->bindColumn(2, $ufNome);
			//$stmt->bindColumn(3, $ufActive);

			while ($r = oci_fetch_row($stt))
			{
				$dtos = new \stdClass;

				$i = 0;

				foreach ($fields as $fk => $fv)
				{
					//$model = new
					$class = $this->namespace . 'dto\\' . $fv['model'];
					$dto = new $class;

					foreach ($fv['fields'] as $f)
					{
						$var = \MonitoLib\Functions::toLowerCamelCase($fk.$f);
						$set = 'set' . ucfirst(\MonitoLib\Functions::toLowerCamelCase($f));
						$dto->$set($r[$i]);
						$i++;
					}

					$dtos->$fk = $dto;
					
				}

				$data[] = $dtos;
			}

			//$stmt->fetch();
			//$stmt = NULL;
		}
		
		//\jLib\Dev::pre($data);

		$dataSet->setTotal($total);
		$dataSet->setCount($count);
		$dataSet->setData($data);
		
		//\jLib\Dev::pre($dataSet);

		return $dataSet;
	}
	public function update ($dto)
	{
		if (!$dto instanceof $this->dtoName)
		{
			throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
		}
		
		$update = NULL;
		$keys   = NULL;
		
		// TODO: isso é só pra funcionar, depois é para fazer corretamente
		$aKeys  = array();
		$aFields = array();

		// \MonitoLib\Dev::pre($this->model->getPrimaryKey());

		$key = null;
		
		foreach ($this->model->getFieldsList() as $fk => $f) {
			if ($fk == $this->model->getPrimaryKey()) {
				$aKeys[] = $fk;
				$key    .= "$fk = :$fk AND";
			} else {
				if (isset($f['type']) && $f['type'] == 'datetime') {
					$update .= "$fk = TO_DATE(:$fk, 'YYYY-MM-DD HH24:MI:SS'), ";
				} else {
					$update .= "$fk = :$fk, ";
				}
				$aFields[] = $fk;
			}
		}
		
		$update = substr($update, 0, -2);
		$key    = substr($key, 0, -3);

		$sql  = 'UPDATE ' . $this->model->getTableName() . " SET $update WHERE $key";
		
		
		// \MonitoLib\Dev::e($sql);

		echo "$sql\n";
		
		
		$stt = oci_parse($this->conn, $sql);

		$i = 1;

		$aFields = array_merge($aFields, $aKeys);
		
		foreach ($aFields as $f) {
			$var = \MonitoLib\Functions::toLowerCamelCase($f);
			$get = 'get' . ucfirst($var);

			$$var = $dto->$get();
			oci_bind_by_name($stt, ":$var", $$var);
			
			echo ":$var, $$var \n";

			// $stmt->bindParam($i, $$var);
			$i++;
		}

		echo 'ooo';
		// exit;

		$exe = @oci_execute($stt);

		if (!$exe)
		{
			$e = oci_error($stt);
			throw new \Exception($e['message']);
		}
		// $stmt->execute();
		// $stmt = NULL;
	}
	public function toDataTransferObject ($data, $dtoClass = null)
	{
		if (is_null($dtoClass))
		{
			$dto = new \MonitoLib\Database\Dto($data);
			return $dto->getData();
		}
		else
		{
			$dto = new $dtoClass;
		}
	}
}