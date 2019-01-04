<?php
namespace MonitoLib\Database\Model;

class Oracle extends \MonitoLib\Database\Model\Model
{
	protected $defaults = [
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
	];
	protected $joins;

	public function addValidation ($field)
	{
		if (in_array($field, $this->fields)) {
			//$v = new \jLib\ValidationRule;
		}
	}
	public function getFields ()
	{
		return $this->fields;
	}
	public function getFieldsList ()
	{
		$list = [];

		foreach ($this->fields as $key => $value) {
			if (isset($value['name'])) {
				$list[] = $value['name'];
			} else {
				$list[] = $key;
			}
		}

		return $list;
	}
	public function OLDgetFieldsList ()
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
	public function getPrimaryKey ($raw = false)
	{
		if ($raw) {
			return $this->keys;
		}

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
	public function getPrimaryKeys ()
	{
		return $this->keys;
	}

	// Retorna string com campos da tabela separados por vírgula
	public function getFieldsSerialized ($fields = null)
	{
		$list = ' ';

		if (is_null($fields) || count($fields) === 0) {
			$fields = $this->getFieldsList();
		}

		foreach ($fields as $f) {
			if (isset($this->fields[$f])) {
				if (isset($this->fields['name'])) {
					$list .= $this->fields['name'];
				} else {
					$list .= $f;
				}

				$list .= ',';
			} else {
				throw new \MonitoLib\Exception\BadRequest("O campo $f não existe na tabela {$this->getTableName()}!");
			}
		}

		return substr($list, 0, -1);
	}

	// select
	public function getSelectFields ($fields = null)
	{
		$list = ' ';

		// \MonitoLib\Dev::vd($fields);

		// $fields = is_null($fields) ? $this->fields : $fields;

		foreach ($this->fields as $key => $value) {
			// TODO: lançar exceção se o campo não existir
			if (is_null($fields) || (is_array($fields) && (count($fields) === 0 || in_array($key, $fields)))) {

				if (isset($value['name'])) {
					$list .= $value['name'];
				} else {
					$list .= $key;
				}

				$list .= ',';
			}

		}

		return substr($list, 0, -1);
	}
	public function getTableName ()
	{
		return $this->tableName;
	}
	public function getValidations ($field)
	{
		return $this->$$field;
	}
}