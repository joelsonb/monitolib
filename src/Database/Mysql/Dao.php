<?php
namespace vendor\ldm\Database\MySQL;

class Dao
{
	protected $dto;
	protected $dtoName;
	protected $conn;
	protected $connection;
	protected $model;

	private $namespace = '';

	public function __construct ()
	{
		if (is_null($this->connection))
		{
			$connector  = \vendor\ldm\Connector::getInstance();
			$this->conn = $connector->getConnection();
		}

		$this->dtoName = str_replace('dao\\','dto\\', get_class($this));
		$model = str_replace('dao\\','model\\', get_class($this));

		$this->dto   = new $this->dtoName;
		$this->model = new $model;
		
		$class = get_class($this);

		$this->namespace .= str_replace('dao\\', '', substr($class, 0, strrpos($class, '\\')));
	}
	public function delete ($mix)
	{
		if ($this->model->getTableType() == 'view')
		{
			throw new \Exception('Não é possível deletar registros de uma view!');
		}

		$sql  = 'DELETE FROM ' . $this->model->getTableName() . ' WHERE ';

		if (is_array($mix))
		{
			if (!is_array($this->model->getPrimaryKey()))
			{
				throw new \Exception('Parâmetro incompatível!');
			}

			foreach ($this->model->getPrimaryKey() as $k => $v)
			{
				$sql .= "$k = ? AND";
			}

			$sql = substr($sql, 0, -3);
		}
		else
		{
			$sql .= $this->model->getPrimaryKey() . ' = ?';
			$mix = array($mix);
		}

		$stmt = $this->conn->prepare($sql);

		$i = 1;

		foreach ($mix as $m)
		{
			$stmt->bindParam($i, $m);
		}

		$stmt->execute();
		$stmt = NULL;
	}
	public function insert ($dto)
	{
		if ($this->model->getTableType() == 'view')
		{
			throw new \Exception('Não é possível inserir registros em uma view!');
		}

		if (!$dto instanceof $this->dtoName)
		{
			throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
		}
		
		
		//\jLib\Dev::pre($this->model->getPrimaryKey());
		//\jLib\Dev::pre($this->model->getFields());

		$sql = 'INSERT INTO ' . $this->model->getTableName() . ' ('
			 . '`' . implode('`,`', $this->model->getFieldsInsert()) . '`) '
			 . 'VALUES (' . substr(str_repeat('?,', count($this->model->getFieldsInsert())), 0, -1) . ')';
		$stmt = $this->conn->prepare($sql);

		$i = 1;

		foreach ($this->model->getFieldsInsert() as $f)
		{
			$var = \vendor\ldm\Functions::toLowerCamelCase($f);
			$get = 'get' . ucfirst($var);

			$$var = $dto->$get();

			$stmt->bindParam($i, $$var);
			$i++;
		}

		$stmt->execute();
		$stmt = NULL;
	}
	public function getByFilter ($filter)
	{
		$sql = 'SELECT ' . implode(',', $this->model->getFields())
			 . ' FROM ' . $this->model->getTableName()
			 . $filter;

		$stmt = $this->conn->prepare($sql);
		$stmt->execute();

		$i = 1;

		foreach ($this->model->getFields() as $f)
		{
			$var = \vendor\ldm\Functions::toLowerCamelCase($f);
			$stmt->bindColumn($i, $$var);
			$i++;
		}

		$dto = NULL;

		if ($stmt->fetch())
		{
			$dto = $this->dto;

			foreach ($this->model->getFields() as $f)
			{
				$var = \vendor\ldm\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($$var);
			}
		}

		$stmt = NULL;

		return $dto;
	}
	public function getById ($id)
	{
		$sql = 'SELECT `' . implode('`,`', $this->model->getFields())
			 . '` FROM ' . $this->model->getTableName() . ' WHERE ' . $this->model->getPrimaryKey() . ' = ?';

		// \vendor\ldm\Dev::e($sql . $id);

		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam(1, $id);
		$stmt->execute();

		$i = 1;

		foreach ($this->model->getFields() as $f)
		{
			$var = \vendor\ldm\Functions::toLowerCamelCase($f);
			$stmt->bindColumn($i, $$var);
			$i++;
		}

		$dto = NULL;

		if ($stmt->fetch())
		{
			$dto = $this->dto;

			foreach ($this->model->getFields() as $f)
			{
				$var = \vendor\ldm\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($$var);
			}
		}

		$stmt = NULL;

		return $dto;
	}
	public function getLastId ()
	{
		return $this->conn->lastInsertId();
	}
	public function getList ($filter = null)
	{
		$sql = 'SELECT `' . implode('`,`', $this->model->getFields())
			 . '` FROM ' . $this->model->getTableName() . $filter;

		
		// \vendor\ldm\Dev::e($sql);
		
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();

		$i = 1;

		foreach ($this->model->getFields() as $f)
		{
			$var = \vendor\ldm\Functions::toLowerCamelCase($f);
			$stmt->bindColumn($i, $$var);
			$i++;
		}

		$data = array();

		while ($stmt->fetch())
		{
			$dto = new $this->dtoName;

			foreach ($this->model->getFields() as $f)
			{
				$var = \vendor\ldm\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($$var);
			}

			$data[] = $dto;
		}

		$stmt = NULL;

		return $data;
	}
	public function getListByObject ($stmt)
	{
		$stmt->execute();

		$i = 1;

		foreach ($this->model->getFields() as $f)
		{
			$var = \vendor\ldm\Functions::toLowerCamelCase($f);
			$stmt->bindColumn($i, $$var);
			$i++;
		}

		$data = array();

		while ($stmt->fetch())
		{
			$dto = new $this->dtoName;

			foreach ($this->model->getFields() as $f)
			{
				$var = \vendor\ldm\Functions::toLowerCamelCase($f);
				$set = 'set' . ucfirst($var);
				$dto->$set($$var);
			}

			$data[] = $dto;
		}

		$stmt = NULL;

		return $data;
	}
	public function read ($filter = null)
	{
		if (is_null($filter))
		{
			$filter = new \jLib\Filter;
		}
		//\jLib\Dev::e($this->namespace);
		
		$dataSet = new \jLib\DataSet;
		$total   = 0;
		$count   = 0;
		$data    = array();
		$sqlJoin = NULL;

		$fields = [
				   $this->model->getName() => [
											   'fields'    => $this->model->getFields(),
											   //'model'     => $this->model->getName(),
											   'model'     => '\\'  . $this->namespace . 'model\\' . $this->model->getName(),
											   'namespace' => $this->namespace,
											   ]
				   ];

		$joins  = $this->model->getJoins();

		//\jLib\Dev::pre($joins);
		//\jLib\Dev::pre($fields);
		$sqlJoin .= 'FROM ' . $this->model->getTableName() . ' ';
		
		$sqlJoin .= 'tab' . $this->model->getName() . ' ';

		if (count($joins))
		{
			// TODO: quando não tiver join, não botar rótulo
			//$sqlJoin .= 'tab' . $this->model->getName() . ' ';

			foreach ($joins as $jk => $jv)
			{
				//\jLib\Dev::pr($jv);
				
				$model  = new $jv['model'];
				$fields[$jk] = [
								'fields'    => $model->getFields(),
								'model'     => $jv['model'],//$model->getName(),
								'namespace' => str_replace('model', '', substr($jv['model'], 0, strrpos($jv['model'], '\\'))),
								];

				$sqlJoin .= $jv['type'] . ' ' . $model->getTableName() . ' tab' . $jk . ' ON ';

				foreach ($jv['relations'] as $k => $v)
				{
					$sqlJoin .= 'tab' . $this->model->getName() . '.' . $k . " = tab$jk.$v AND";
				}

				$sqlJoin = substr($sqlJoin, 0, -3);
			}

			//$sqlJoin = substr($sqlJoin, 0, -3);
		}
		
		//\jLib\Dev::pre($fields);
		
		$sql  = 'SELECT COUNT(*) ' . $sqlJoin;
		
		//if (!is_null($sqlJoin))
		//{
		//	$sql .= ' ' . $sqlJoin;
		//}

		//\jLib\Dev::pre($fields);
		//\jLib\Dev::e($sql);
		//\jLib\Dev::e($sql . $filter->getFixedCriteria());

		// Get total records in table
		$stmt = $this->conn->prepare($sql . $filter->getFixedCriteria());
		$stmt->execute();
		$stmt->bindColumn(1, $total);
		$stmt->fetch();

		// If has records in table, get filtered count
		if ($total > 0)
		{
			//\jLib\Dev::e($sql . $filter->getCountCriteria());
			// Get total filtered records
			$stmt = $this->conn->prepare($sql . $filter->getCountCriteria());
			$stmt->execute();
			$stmt->bindColumn(1, $count);
			$stmt->fetch();
		}

		if ($count > 0)
		{
			$sql = 'SELECT ';
			
			foreach ($fields as $fk => $fv)
			{
				foreach ($fv['fields'] as $f)
				{
					$sql .= "tab$fk.$f,";
				}
			}

			$sql = substr($sql, 0, -1) . ' ' . $sqlJoin . $filter;
			//\jLib\Dev::e($sql);

			$stmt = $this->conn->prepare($sql);
			$stmt->execute();

			// bind result variables
			$i = 1;
	
			foreach ($fields as $fk => $fv)
			{
				foreach ($fv['fields'] as $f)
				{
					$var = \vendor\ldm\Functions::toLowerCamelCase($fk.$f);
					$stmt->bindColumn($i, $$var);
					$i++;
				}
			}
			
			//\jLib\Dev::pre($stmt->fetch(\PDO::FETCH_ASSOC));

			//$stmt->bindColumn(1, $ufId);
			//$stmt->bindColumn(2, $ufNome);
			//$stmt->bindColumn(3, $ufActive);

			while ($stmt->fetch())
			{
				$dtos = new \stdClass;

				foreach ($fields as $fk => $fv)
				{
					//\jLib\Dev::pr($fv);
					//$model = new
					//$class = $this->namespace . 'dto\\' . $fv['model'];
					$class = str_replace('\\model', '\\dto' , $fv['model']);
					$dto = new $class;
					
					//\jLib\Dev::pr($dto);

					foreach ($fv['fields'] as $f)
					{
						$var = \vendor\ldm\Functions::toLowerCamelCase($fk.$f);
						$set = 'set' . ucfirst(\vendor\ldm\Functions::toLowerCamelCase($f));
						$dto->$set($$var);
					}

					$dtos->{lcfirst($fk)} = $dto;
				}

				$data[] = $dtos;
			}

			$stmt->fetch();
			$stmt = NULL;
		}
		
		//\jLib\Dev::pre($data);

		$dataSet->setTotal($total);
		$dataSet->setCount($count);
		$dataSet->setData($data);

		return $dataSet;
	}
	public function toDataTransferObject ($data, $dtoClass = null)
	{
		if (is_null($dtoClass))
		{
			$dto = new \vendor\ldm\Database\Dto($data);
			return $dto->getData();
		}
		else
		{
			$dto = new $dtoClass;
		}
	}
	public function truncate ()
	{
		$sql = 'TRUNCATE TABLE ' . $this->model->getTableName();
		$stmt = $this->conn->prepare($sql);
		$stmt->execute();
	}
	public function update ($dto)
	{
		if ($this->model->getTableType() == 'view')
		{
			throw new \Exception('Não é possível atualizar registros de uma view!');
		}

		if (!$dto instanceof $this->dtoName)
		{
			throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
		}
		
		$update = NULL;
		$key    = NULL;
		
		// TODO: isso é só pra funcionar, depois é para fazer corretamente
		$aKeys  = array();
		$aFields = array();
		
		foreach ($this->model->getFields() as $f)
		{
			if ($f == $this->model->getPrimaryKey())
			{
				$aKeys[] = $f;
				$key    .= "$f = ? AND";
			}
			else
			{
				$update .= "`$f` = ?,";
				$aFields[] = $f;
			}
		}
		
		$update = substr($update, 0, -1);
		$key    = substr($key, 0, -3);

		$sql  = 'UPDATE ' . $this->model->getTableName() . " SET $update WHERE $key";
		
		
		//\jLib\Dev::e($sql);
		
		
		$stmt = $this->conn->prepare($sql);

		$i = 1;

		$aFields = array_merge($aFields, $aKeys);
		
		foreach ($aFields as $f)
		{
			$var = \vendor\ldm\Functions::toLowerCamelCase($f);
			$get = 'get' . ucfirst($var);

			$$var = $dto->$get();
			$stmt->bindParam($i, $$var);
			$i++;
		}

		$stmt->execute();
		$stmt = NULL;
	}
}