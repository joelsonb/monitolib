<?php
namespace MonitoLib\Database\Model;

class MySQL
{
	protected $tableType = 'table';
	protected $joins;
	private $defaults = array(
							  'auto'             => false,
							  'charset'          => 'utf8',
							  'collation'        => 'utf8_general_ci',
							  'defaultValue'     => null,
							  'label'            => '',
							  'maxValue'         => 0,
							  'minValue'         => 0,
							  'numericPrecision' => null,
							  'numericScale'     => null,
							  'primary'          => false,
							  'required'         => false,
							  'type'             => 'varchar',
							  'unique'           => false,
							  'unsigned'         => false,
							  );

	public function addValidation ($field)
	{
		if (in_array($field, $this->fields))
		{
			//$v = new \jLib\ValidationRule;
		}
	}
	public function getDefaults ($option = null)
	{
		if (is_null($option))
		{
			return $this->defaults;
		}
		else
		{
			if (isset($this->defaults[$option]))
			{
				return $this->defaults[$option];
			}

			throw new \Exception("There's no '$option' option in defaults options");
		}
	}
	public function getFields ()
	{
		if (isset($this->fields[0])) {
			//\jLib\Dev::pre($this->fields);
			return $this->fields;
		} else {
			return array_keys($this->fields);
		}
	}
	public function getFieldsInsert ()
	{
		$fields = array();

		if (isset($this->fields[0])) {
			return $this->fields;
		} else {
			foreach ($this->fields as $fk => $fv) {
				$fv = \MonitoLib\Functions::ArrayMergeRecursive($this->defaults, $fv);
	
				if (!$fv['auto']) {
					$fields[] = $fk;
				}
			}
		}

		return $fields;
	}
	public function getFieldList ()
	{
		return implode(',', $this->getFields());
	}
	public function getFieldsSerialized ($alias = null, $prefix = true)
	{
		$fields = '';

		foreach ($this->fields as $f => $v) {
			if (!is_null($alias)) {
				$fields .= $alias . '.';
			}

			$fields .= $f;

			if ($prefix === true || $prefix != '') {
				$fields .= ' AS ' . $alias . '_' . $f;
			}

			$fields .= ', ';
		}

		$fields = substr($fields, 0, -2);

		return $fields;
	}
	public function getJoins ()
	{
		return $this->joins;
	}
	public function getName ()
	{
		$class = get_class($this);
		return substr($class, strrpos($class, '\\') + 1);
	}
	public function getPrimaryKeys ()
	{
		return $this->keys;
	}
	public function getPrimaryKey ()
	{
		$keys = 'id';

		if (!is_null($this->keys))
		{
			$keys = NULL;

			foreach ($this->keys as $k)
			{
				$keys .= "$k,";
			}

			$keys = substr($keys, 0, -1);
		}

		return $keys;
	}
	//public function getFields ()
	//{
	//	$class = get_class($this);
	//	
	//	$reflect = new \ReflectionClass($class);
	//	$props   = $reflect->getProperties();
	//	
	//	\jLib\Dev::pre($props);
	//	
	//	\jLib\Dev::vde(get_object_vars(new $class));
	//}
	public function getTableName ()
	{
		return $this->tableName;
	}
	public function getTableType ()
	{
		return $this->tableType;
	}
	public function getValidations ($field)
	{
		return $this->$$field;
	}
	public function validate (&$dtoObject)
	{
		$exception = null;

		foreach ($this->fields as $fk => $fv)
		{
			// echo "\$fk: $fk\n";

			$fv = \MonitoLib\Functions::ArrayMergeRecursive($this->defaults, $fv);

			$getObject = 'get' . ucfirst(\MonitoLib\Functions::toLowerCamelCase($fk));
			$setObject = 'set' . ucfirst(\MonitoLib\Functions::toLowerCamelCase($fk));

			$value = $dtoObject->$getObject();

			// \MonitoLib\Dev::pre($fv);

			// Search for default values
			if (is_null($value) || $value === false)
			{
				// Checks if ins_date and upd_date are null and set current date as its values
				if (in_array($fk, array('ins_time', 'date_upd')))
				{
					$value = date('Y-m-d H:i:s');
				}

				if (!is_null($fv['defaultValue']))
				{
					$value = $fv['defaultValue'];
					$dtoObject->$setObject($value);
					// echo "\$dtoObject->$setObject($value);\n";
				}

				if (defined('JLIB_IS_SECURE') && JLIB_IS_SECURE && in_array($fk, array('user_id_ins', 'user_id_upd')))
				{
					$user = $_SESSION[JLIB_SID]['user'];
					$value = $user->getId();
				}
				// if (!is_null($value))
				// {
					
				// }
			}

			if (in_array($fv['type'], ['int','date','time','tinyint']) && $value == '') {
				$dtoObject->$setObject(null);
			}

			if ($fv['auto'] == false && $fv['required'] == true && ($value == '' || is_null($value)))
			{
				$label = $fv['label'] == '' ? $fk : $fv['label'];
				$exception .= "Informe um valor para o campo <b>$label</b>!<br />";
			}
			
			if (isset($fv['maxLength']))
			{
				if (strlen($value) > $fv['maxLength'])
				{
					$vl = strlen($value);
					$ml = $fv['maxLength'];
	
					$exception .= "Valor informado ($vl) maior que o permitido o campo ($ml) <b>{$fv['label']}</b>!<br />";
				}
			}
			


		}

		return $exception;
	}
}