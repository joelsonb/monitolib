<?php
namespace jLib\Pattern\Oracle;

class Model
{
	protected $joins;

	public function addValidation ($field)
	{
		if (in_array($field, $this->fields))
		{
			//$v = new \jLib\ValidationRule;
		}
	}
	public function getFields ()
	{
		return $this->fields;
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
	public function getValidations ($field)
	{
		return $this->$$field;
	}
}