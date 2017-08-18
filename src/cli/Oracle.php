<?php
namespace vendor\ldm\cli;

class Oracle
{
	private $conn;
	private $connection;
	private $dbName;
	private $projectId;
	private $tables = array();
	private $util;

	public function __construct ($connector)
	{
		$this->connection = $connector->getConnection('tms');
		$this->dbName     = 'tms';//$connector->getDbName();
	}

	public function addTable ($table)
	{
		$this->tables[] = $table;
	}
	private function labelIt ($label)
	{
		if ($label ==  'id')
		{
			$label = '#';
		}
		else
		{
			$frag = NULL;

			if (preg_match('/_id$/', $label))
			{
				//$frag  = '# ';
				$label = substr($label, 0, -3);
			}

			$parts = explode('_', $label);
			$label = '';

			foreach ($parts as $p)
			{
				$label .= ucfirst($p) . ' ';
			}

			$label = substr($label, 0, -1);
			
			//if (!is_null($frag))
			//{
			//	$label .= ' ' . $frag;
			//}
		}

		return $label;
	}
	public function listColumns ($tableName = NULL)
	{
		$sql = 'SELECT * FROM user_tab_columns';

		if (!is_null($tableName))
		{
			$sql .= ' AND TABLE_NAME ';

			if (is_array($tableName))
			{
				$tableName  = "'" . implode("','", $tableName) . "'";
				$sql       .= "IN ($tableName)";
				$tableName  = NULL;
			}
			else
			{
				$sql .= '= ?';
			}
		}

		$sth = $this->connection->prepare($sql);
		$sth->bindParam(1, $this->dbName);

		if (!is_null($tableName))
		{
			$sth->bindParam(2, $tableName);
		}

		$sth->execute();

		$columns = $sth->fetchAll(\PDO::FETCH_ASSOC);
	
		$data = array();
		
		foreach ($columns as $c)
		{
			switch ($c['DATA_TYPE'])
			{
				case 'char':
				case 'varchar':
				case 'text':
					$type = 'char';
					break;
				case 'int':
				case 'bigint':
				case 'smallint':
				case 'tinyint':
					$type = 'int';
					break;
				case 'decimal';
				case 'double';
				case 'float';
					$type = 'float';
					break;
				default:
					$type = $c['DATA_TYPE'];
			}
			
			$columnName          = $c['COLUMN_NAME'];
			$columnType          = $type;
			$columnLabel         = $this->labelIt($c['COLUMN_NAME']);
			$columnDataType      = $c['DATA_TYPE'];
			$columnDefault       = $c['COLUMN_DEFAULT'] == '' ? NULL : $c['COLUMN_DEFAULT'];
			$columnMaxLength     = is_null($c['CHARACTER_MAXIMUM_LENGTH']) ? $c['NUMERIC_PRECISION'] : $c['CHARACTER_MAXIMUM_LENGTH'];
			$columnPrecisionSize = $c['NUMERIC_PRECISION'];
			$columnScale         = $c['NUMERIC_SCALE'];
			$columnCollation     = $c['COLLATION_NAME'];
			$columnCharset       = $c['CHARACTER_SET_NAME'];
			$columnIsPrimary     = $c['COLUMN_KEY'] == 'PRI' ? 1 : 0;
			$columnIsRequired    = $c['IS_NULLABLE'] == 'YES' ? 0 : 1;
			$columnIsBinary      = strpos($c['COLLATION_NAME'], '_bin') !== FALSE ? 0 : 1;
			$columnIsUnsigned    = strpos($c['COLUMN_TYPE'], 'unsigned') !== FALSE ? 0 : 1;
			$columnIsUnique      = $c['COLUMN_KEY'] == 'UNI' ? 1 : 0;
			$columnIsZerofilled  = strpos($c['COLUMN_TYPE'], 'zerofill') !== FALSE ? 1 : 0;
			$columnIsAuto        = $c['EXTRA'] == 'auto_increment' ? 1 : 0;
			$columnIsForeign     = $c['COLUMN_KEY'] == 'MUL' ? 1 : 0;
			$columnActive        = 1;
			$tableName           = $c['TABLE_NAME'];

			//$tableDao    = \dao\Factory::createTable();
			//$tableObject = $tableDao->getByName($tableName);
	
			//if (is_null($tableObject))
			//{
			//	throw new \Exception("Table $tableName not found!");
			//}

			// $columnDao   = \dao\Factory::createColumn();
			// $columnDto = new \dto\Column;
			//$columnDto->setTableId($tableObject->getId());
			// $columnDto->setName($columnName);
			// $columnDto->setType($columnType);
			// $columnDto->setLabel($columnLabel);
			// $columnDto->setDataType($columnDataType);
			// $columnDto->setDefaultValue($columnDefault);
			// $columnDto->setMaxLength($columnMaxLength);
			// $columnDto->setNumericPrecision($columnPrecisionSize);
			// $columnDto->setNumericScale($columnScale);
			// $columnDto->setCollation($columnCollation);
			// $columnDto->setCharset($columnCharset);
			// $columnDto->setIsPrimary($columnIsPrimary);
			// $columnDto->setIsRequired($columnIsRequired);
			// $columnDto->setIsBinary($columnIsBinary);
			// $columnDto->setIsUnsigned($columnIsUnsigned);
			// $columnDto->setIsUnique($columnIsUnique);
			// $columnDto->setIsZerofilled($columnIsZerofilled);
			// $columnDto->setIsAuto($columnIsAuto);
			// $columnDto->setIsForeign($columnIsForeign);
			// $columnDto->setActive($columnActive);
			$columnDto = new \stdClass;
			$columnDto->name = $columnName;
			$columnDto->type = $columnType;
			$columnDto->label = $columnLabel;
			$columnDto->dataType = $columnDataType;
			$columnDto->defaultValue = $columnDefault;
			$columnDto->maxLength = $columnMaxLength;
			$columnDto->numericPrecision = $columnPrecisionSize;
			$columnDto->numericScale = $columnScale;
			$columnDto->collation = $columnCollation;
			$columnDto->charset = $columnCharset;
			$columnDto->isPrimary = $columnIsPrimary;
			$columnDto->isRequired = $columnIsRequired;
			$columnDto->isBinary = $columnIsBinary;
			$columnDto->isUnsigned = $columnIsUnsigned;
			$columnDto->isUnique = $columnIsUnique;
			$columnDto->isZerofilled = $columnIsZerofilled;
			$columnDto->isAuto = $columnIsAuto;
			$columnDto->isForeign = $columnIsForeign;
			$columnDto->active = $columnActive;

			//$columnDao    = \dao\Factory::createColumn();
			//$columnObject = $columnDao->getByName($tableObject->getId(), $columnName);
			//
			//if (is_null($columnObject))
			//{
			//	$columnDao->insert($columnModel);
			//}
			//else
			//{
			//	$columnModel->setId($columnObject->getId());
			//	$columnDao->update($columnModel);
			//}
			$data[] = $columnDto;
		}
		
		return $data;
	}
	public function listRelations ($database, $tableName = NULL)
	{
		$sql = 'SELECT * FROM user_tables';

		if (!is_null($tableName))
		{
			$sql .= ' WHERE TABLE_NAME ';

			if (is_array($tableName))
			{
				$tableName  = "'" . implode("','", $tableName) . "'";
				$sql       .= "IN ($tableName)";
				$tableName  = NULL;
			}
			else
			{
				$sql .= '= ?';
			}
		}

		$sth = $this->conn->prepare($sql);
		$sth->bindParam(1, $this->dbName);

		if (!is_null($tableName))
		{
			$sth->bindParam(2, $tableName);
		}

		$sth->execute();

		$relations = $sth->fetchAll(\PDO::FETCH_ASSOC);

		$data = array();

		foreach ($relations as $r)
		{
			if (!is_null($r['REFERENCED_TABLE_NAME']))
			{
				$data[] = array(
								'tableNameSource'       => $r['TABLE_NAME'],
								'columnNameSource'      => $r['COLUMN_NAME'],
								'tableNameDestination'  => $r['REFERENCED_TABLE_NAME'],
								'columnNameDestination' => $r['REFERENCED_COLUMN_NAME'],
								'sequence'              => $r['ORDINAL_POSITION'],
								);
			}
		}

		return $data;
	}
	public function listTables ($tableName = NULL)
	{
		$sql = 'SELECT * FROM user_tables';

		if (!is_null($tableName))
		{
			$sql .= ' WHERE TABLE_NAME ';

			if (is_array($tableName))
			{
				$tableName  = "'" . implode("','", $tableName) . "'";
				$sql       .= "IN ($tableName)";
				$tableName  = NULL;
			}
			else
			{
				$sql .= '= ?';
			}
		}

		//\jLib\Dev::e($sql);

		$sth = $this->connection->prepare($sql);
		$sth->bindParam(1, $this->dbName);

		if (!is_null($tableName))
		{
			$sth->bindParam(2, $tableName);
		}

		$sth->execute();

		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	public function listTablesAndColumns ()
	{
		$sql = 'SELECT * FROM user_tables t '
			. 'INNER JOIN user_tab_columns c ON t.table_name = c.table_name '
			. 'ORDER BY t.TABLE_NAME, c.column_id';

		$sth = $this->connection->prepare($sql);
		$sth->bindParam(1, $this->dbName);
		$sth->execute();

		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	public function load ()
	{
		// Loads tables
		$this->loadTables();

		// Loads columns
		$this->loadColumns();

		// Loads relations
		$this->loadRelations();
	}
	private function loadColumns ()
	{
		$columns = $this->listColumns($this->connection->getDbName(), $this->tables);

		$data = array();
		
		foreach ($columns as $c)
		{
			$columnName          = $c['COLUMN_NAME'];
			$columnType          = NULL;
			$columnLabel         = $this->labelIt($c['COLUMN_NAME']);
			$columnDataType      = $c['DATA_TYPE'];
			$columnDefault       = $c['COLUMN_DEFAULT'] == '' ? NULL : $c['COLUMN_DEFAULT'];
			$columnMaxLength     = is_null($c['CHARACTER_MAXIMUM_LENGTH']) ? $c['NUMERIC_PRECISION'] : $c['CHARACTER_MAXIMUM_LENGTH'];
			$columnPrecisionSize = $c['NUMERIC_PRECISION'];
			$columnScale         = $c['NUMERIC_SCALE'];
			$columnCollation     = $c['COLLATION_NAME'];
			$columnCharset       = $c['CHARACTER_SET_NAME'];
			$columnIsPrimary     = $c['COLUMN_KEY'] == 'PRI' ? 1 : 0;
			$columnIsRequired    = $c['IS_NULLABLE'] == 'YES' ? 0 : 1;
			$columnIsBinary      = strpos($c['COLLATION_NAME'], '_bin') !== FALSE ? 0 : 1;
			$columnIsUnsigned    = strpos($c['COLUMN_TYPE'], 'unsigned') !== FALSE ? 0 : 1;
			$columnIsUnique      = $c['COLUMN_KEY'] == 'UNI' ? 1 : 0;
			$columnIsZerofilled  = strpos($c['COLUMN_TYPE'], 'zerofill') !== FALSE ? 0 : 1;
			$columnIsAuto        = $c['EXTRA'] == 'auto_increment' ? 1 : 0;
			$columnIsForeign     = $c['COLUMN_KEY'] == 'MUL' ? 1 : 0;
			$columnActive        = 1;
			$tableName           = $c['TABLE_NAME'];

			//$tableDao    = \dao\Factory::createTable();
			//$tableObject = $tableDao->getByName($tableName);
	
			//if (is_null($tableObject))
			//{
			//	throw new \Exception("Table $tableName not found!");
			//}

			$columnDao   = \dao\Factory::createColumn();
			$columnDto = new \model\Column;
			//$columnDto->setTableId($tableObject->getId());
			$columnDto->setName($columnName);
			$columnDto->setType($columnType);
			$columnDto->setLabel($columnLabel);
			$columnDto->setDataType($columnDataType);
			$columnDto->setDefaultValue($columnDefault);
			$columnDto->setMaxLength($columnMaxLength);
			$columnDto->setNumericPrecision($columnPrecisionSize);
			$columnDto->setNumericScale($columnScale);
			$columnDto->setCollation($columnCollation);
			$columnDto->setCharset($columnCharset);
			$columnDto->setIsPrimary($columnIsPrimary);
			$columnDto->setIsRequired($columnIsRequired);
			$columnDto->setIsBinary($columnIsBinary);
			$columnDto->setIsUnsigned($columnIsUnsigned);
			$columnDto->setIsUnique($columnIsUnique);
			$columnDto->setIsZerofilled($columnIsZerofilled);
			$columnDto->setIsAuto($columnIsAuto);
			$columnDto->setIsForeign($columnIsForeign);
			$columnDto->setActive($columnActive);

			//$columnDao    = \dao\Factory::createColumn();
			//$columnObject = $columnDao->getByName($tableObject->getId(), $columnName);
			//
			//if (is_null($columnObject))
			//{
			//	$columnDao->insert($columnModel);
			//}
			//else
			//{
			//	$columnModel->setId($columnObject->getId());
			//	$columnDao->update($columnModel);
			//}
			$data[] = $columnDto;
		}
		
		return $data;
	}
	private function loadRelations ()
	{
		$columnDao   = \dao\Factory::createColumn();
		$relationDao = \dao\Factory::createRelation();
		$tableDao    = \dao\Factory::createTable();

		$relations = $this->listRelations($this->connection->getDbName(), $this->tables);

		//\jLib\Dev::pre($relations);

		foreach ($relations as $r)
		{
			$referencedTableName = $r['REFERENCED_TABLE_NAME'];

			if (!is_null($referencedTableName))
			{
				$sourceTableName      = $r['TABLE_NAME'];
				$sourceColumnName     = $r['COLUMN_NAME'];
				$referencedColumnName = $r['REFERENCED_COLUMN_NAME'];
				$sequence             = $r['ORDINAL_POSITION'];
				$active               = 1;

				$sourceTable  = $tableDao->getByName($sourceTableName);
				$sourceColumn = $columnDao->getByName($sourceTable->getId(), $sourceColumnName);

				$referencedTable  = $tableDao->getByName($referencedTableName);
				
				if (!is_null($referencedTable))
				{
					$referencedColumn = $columnDao->getByName($referencedTable->getId(), $referencedColumnName);
	
					//$relationModel->setId($id);
					$relationModel = new \model\Relation;
					$relationModel->setColumnIdSource($sourceColumn->getId());
					$relationModel->setColumnIdDestination($referencedColumn->getId());
					$relationModel->setSequence($sequence);
					$relationModel->setActive($active);
	
					$relation = $relationDao->getByColumnsIds($sourceColumn->getId(), $referencedColumn->getId());
	
					if (is_null($relation))
					{
						$relationDao->insert($relationModel);
					}
					else
					{
						$relationModel->setId($relation->getId());
						$relationDao->update($relationModel);
					}
				}
				//\jLib\Dev::pre($relationModel);
			}
		}
	}
	private function OLDloadTables ()
	{
		$tables = $this->listTables($this->connection->getDbName(), $this->tables);
		
		foreach ($tables as $t)
		{
			$className    = '';
			$tableName    = $t['TABLE_NAME']; 	
			$tableAlias   = $tableName;
			$className    = '';
			$objectName   = '';
			$viewName     = '';
			$singularName = '';
			$pluralName   = '';
			$active       = 1;
			$frag         = explode('_', $tableName);
	
			foreach ($frag as $f)
			{
				$className .= $this->util->toSingular(ucfirst($f));
			}
	
			$objectName = strtolower(substr($className, 0, 1)) . substr($className, 1);
			$viewName   = str_replace('_', '-', strtolower($tableName));
	
			foreach ($frag as $f)
			{
				$singularName .= $this->util->toSingular(ucfirst($f)) . ' ';
			}
	
			foreach ($frag as $f)
			{
				$pluralName .= $this->util->toPlural(ucfirst($f)) . ' ';
			}
			
			$singularName = substr($singularName, 0, -1);
			$pluralName   = substr($pluralName, 0, -1);
	
			$tableModel = new \model\Table;
			$tableModel->setProjectId($this->projectId);
			$tableModel->setConnectionId($this->connection->getId());
			$tableModel->setTableName($tableName);
			$tableModel->setTableAlias($tableAlias);
			$tableModel->setClassName($className);
			$tableModel->setObjectName($objectName);
			$tableModel->setViewName($viewName);
			$tableModel->setSingularName($singularName);
			$tableModel->setPluralName($pluralName);
			$tableModel->setActive($active);
			//\jLib\Dev::pre($tableModel);
	
			$tableDao    = \dao\Factory::createTable();
			$tableObject = $tableDao->getByName($tableName);
	
			if (is_null($tableObject))
			{
				$tableDao->insert($tableModel);
			}
			else
			{
				$tableModel->setId($tableObject->getId());
				$tableDao->update($tableModel);
			}
		}
	}
}