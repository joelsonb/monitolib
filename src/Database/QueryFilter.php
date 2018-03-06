<?php
namespace MonitoLib\Database;

class QueryFilter
{
	// 1 - MySql
	// 2 - SQLite
	// 3 - MSSQ
	// 4 - Oracle

	private $criteria;
	private $countCriteria;
	private $fixedCriteria;
	private $fields;

	private $dbms        = 1;
	private $page        = 1;
	private $limitStart  = 0;
	private $limitOffset = 0;
	private $sort        = array();
	private $sql;

	public function __construct ($sqlQuery = null)
	{
		$this->sql = $sqlQuery;
	}
	public function __toString ()
	{
		return $this->render();
	}

	public function addAndWhere ($field, $operator, $value, $fixed = false)
	{
		// ->addAndCriteria('adapters.mac_address', 'like', '58:3f');
		
		//starts|s
		//ends|e
		//like|lk
		//equal|eq|=
		//lt
		//le
		//rt
		//re

		switch ($operator)
		{
			case '=':
				break;
			case '<>':
				break;
			case '<':
				break;
			case '>':
				break;
			case 'like':
				break;
			default:
				throw new \Exception('Operador inválido!');
				break;
		}

		$sql = "$field $operator $value AND ";

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}
	}
	public function andIn ($field, $values, $fixed = false)
	{
		if (count($values) == 0)
		{
			throw new \Exception('Valores inválidos!');
		}

		$value = '';

		foreach ($values as $v)
		{
			if (is_numeric($v))
			{
				$value .= $v;
			}
			else
			{
				$value .= "'$v'";
			}

			$value .= ',';
		}

		$value = substr($value, 0, -1);

		$sql = "$field IN ($value) AND ";

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function startGroup ($fixed = false)
	{
		$sql = '(';

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function endGroup ($fixed = false)
	{

		// echo $this->criteria;
		// exit;


		// if ($p = preg_match('/(AND|OR)$/', $this->criteria)) {
		// 	\MonitoLib\Dev::pre($p);
		// }


		$sql = ')';

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andEqual ($field, $value, $fixed = false)
	{
		$sql = "$field = '$value' AND ";

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andEqualOrNull ($field, $value, $fixed = false)
	{
		if (is_null($value)) {
			return $this->andIsNull($field, $fixed);
		}

		$sql = "$field = '$value' AND ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andEqualOrNullRaw ($field, $value, $fixed = false)
	{
		if (is_null($value)) {
			return $this->andIsNull($field, $fixed);
		}

		$sql = "$field = $value AND ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andEqualRaw ($field, $value, $fixed = false)
	{
		$sql = "$field = $value AND ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andGreaterEqual ($field, $value, $fixed = false)
	{
		$sql = "$field >= '$value'";

		$sql .= ' AND ';

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andGreaterThan ($field, $value, $fixed = false)
	{
		$sql = "$field > '$value'";

		$sql .= ' AND ';

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andGroup ()
	{
		
	}
	public function andIsNull ($field, $fixed = false)
	{
		$sql = "$field IS NULL AND ";

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andLessEqual ($field, $value, $fixed = false)
	{
		$sql = "$field <= '$value'";

		$sql .= ' AND ';

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andLessEqualRaw ($field, $value, $fixed = false)
	{
		$sql = "$field <= $value";

		$sql .= ' AND ';

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function orEqual ($field, $value, $fixed = false)
	{
		$sql = "$field = '$value' OR ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function orNotEqual ($field, $value, $fixed = false)
	{
		$sql = "$field <> '$value' OR ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function orNotEqualRaw ($field, $value, $fixed = false)
	{
		$sql = "$field <> $value OR ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function orLessEqual ($field, $value, $fixed = false, $group = NULL)
	{
		$sql = "$field <= '$value'";

		$sql .= ' OR ';

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andLessThan ($field, $value, $fixed = false)
	{
		$sql = "$field < '$value'";

		$sql .= ' AND ';

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andNotEqual ($field, $value, $fixed = false)
	{
		$sql = "$field <> '$value' AND ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andNotEqualRaw ($field, $value, $fixed = false)
	{
		$sql = "$field <> $value AND ";

		$this->criteria .= $sql;

		if ($fixed) {
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andIsNotNull ($field, $fixed = false)
	{
		$sql = "$field IS NOT NULL AND ";

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function andLike ($field, $value, $fixed = false)
	{
		$sql = "$field LIKE '%$value%' AND ";

		$this->criteria .= $sql;

		if ($fixed)
		{
			$this->fixedCriteria .= $sql;
		}

		return $this;
	}
	public function addCriteria ($field, $criteria, $fixed = false)
	{
		// Divide a string com espaços
		$strings = explode(' ', $criteria);

		foreach ($strings as $s)
		{
			//_vde($criteria);

			$not = false;
			$sql = '';

			if (substr($s, 0, 1) == '!')
			{
				$not = true;
				$s = substr($s, 1);
			}

			if (preg_match('/^\*([[:alnum:]]{1,})\*$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' LIKE \'%' . $p[1] . '%\'';
			}
			if (preg_match('/^([[:alnum:]]{1,})\*$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' LIKE \'' . $p[1] . '%\'';
			}
			if (preg_match('/^\*([.-\/[:alnum:]\:]{1,})$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' LIKE \'%' . $p[1] . '\'';
			}
			if (preg_match('/^=([[:alnum:]]{1,})$/', $s, $p))
			{
				$sNot = $not ? ' <>' : ' =';
				$sql = $field . $sNot . ' \'' . $p[1] . '\'';

				//$sNot = $not ? ' NOT LIKE ' : ' LIKE ';
				//$sql = $field . $sNot . ' \'%' . $p[1] . '%\'';
			}
			//if (preg_match('/^([[:alnum:]]{1,})$/', $s, $p))
			if (preg_match('/^(.*)$/', $s, $p))
			{
				//$sNot = $not ? ' <>' : ' =';
				//$sql = $field . $sNot . ' \'' . $p[1] . '\'';

				$sNot = $not ? ' NOT LIKE ' : ' LIKE ';
				$sql = $field . $sNot . ' \'%' . $p[1] . '%\'';
			}
			//if (preg_match('/^(\d{1,})$/', $criteria, $p))
			//{
			//	$sNot = $not ? '<>' : ' = ';
			//	$sql = $field . $sNot . $p[1];
			//}
			if (preg_match('/^>(\d{1,})$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' > ' . $p[1];
			}
			if (preg_match('/^<(\d{1,})$/', $criteria, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' < ' . $p[1];
			}
			if ($criteria === true)
			{
				$sql = $field . ' IS NOT NULL ';
			}
			if ($criteria === false or is_null($criteria))
			{
				$sql = $field . ' IS NULL ';
			}
	
			if ($sql != '')
			{
				$sql .= ' AND ';
	
				$this->criteria .= $sql;
		
				if ($fixed)
				{
					$this->fixedCriteria .= $sql;
				}
			}
		}

		return $this;
	}
	public function addIntegerCriteria ($field, $criteria, $fixed = false)
	{
		// Divide a string com espaços
		//$strings = explode(' ', $criteria);

		$s = $criteria;
		
		//foreach ($strings as $s)
		//{
			//_vde($criteria);

			$not = false;
			$sql = '';

			if (substr($s, 0, 1) == '!')
			{
				$not = true;
				$s = substr($s, 1);
			}

			if (preg_match('/^>(\d{1,})$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' > ' . $p[1];
			}
			if (preg_match('/^<(\d{1,})$/', $criteria, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' < ' . $p[1];
			}
			if (is_numeric($criteria))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' = ' . $s;
			}

	
			if ($sql != '')
			{
				$sql .= ' AND ';
	
				$this->criteria .= $sql;
		
				if ($fixed)
				{
					$this->fixedCriteria .= $sql;
				}
			}
		//}

		return $this;
	}
	public function addCriteriaNEW ($field, $criteria, $fixed = false)
	{
		// Divide a string com espaços
		$strings = explode(' ', $criteria);

		foreach ($strings as $s)
		{
			//_vde($criteria);

			$not = false;
			$sql = '';

			if (substr($s, 0, 1) == '!')
			{
				$not = true;
				$s = substr($s, 1);
			}

			if (preg_match('/^\*([[:alnum:]]{1,})\*$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' LIKE \'%' . $p[1] . '%\'';
			}
			if (preg_match('/^([[:alnum:]]{1,})\*$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' LIKE \'' . $p[1] . '%\'';
			}
			if (preg_match('/^\*([.-\/[:alnum:]]{1,})$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' LIKE \'%' . $p[1] . '\'';
			}
			if (preg_match('/^=([[:alnum:]]{1,})$/', $s, $p))
			{
				$sNot = $not ? ' <>' : ' =';
				$sql = $field . $sNot . ' \'' . $p[1] . '\'';

				//$sNot = $not ? ' NOT LIKE ' : ' LIKE ';
				//$sql = $field . $sNot . ' \'%' . $p[1] . '%\'';
			}
			if (preg_match('/^([[:alnum:]]{1,})$/', $s, $p))
			{
				//$sNot = $not ? ' <>' : ' =';
				//$sql = $field . $sNot . ' \'' . $p[1] . '\'';

				$sNot = $not ? ' NOT LIKE ' : ' LIKE ';
				$sql = $field . $sNot . ' \'%' . $p[1] . '%\'';
			}
			//if (preg_match('/^(\d{1,})$/', $criteria, $p))
			//{
			//	$sNot = $not ? '<>' : ' = ';
			//	$sql = $field . $sNot . $p[1];
			//}
			if (preg_match('/^>(\d{1,})$/', $s, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' > ' . $p[1];
			}
			if (preg_match('/^<(\d{1,})$/', $criteria, $p))
			{
				$sNot = $not ? ' NOT' : '';
				$sql = $field . $sNot . ' < ' . $p[1];
			}
			if ($criteria === true)
			{
				$sql = $field . ' IS NOT NULL ';
			}
			if ($criteria === false or is_null($criteria))
			{
				$sql = $field . ' IS NULL ';
			}
	
			if ($sql != '')
			{
				$sql .= ' AND ';
	
				$this->criteria .= $sql;
		
				if ($fixed)
				{
					$this->fixedCriteria .= $sql;
				}
			}
		}

		return $this;
	}
	public function addCriteriaOLD ($field, $criteria, $fixed = false)
	{
		//_vde($criteria);
		
		$not = false;
		$sql = '';

		if (substr($criteria, 0, 1) == '!')
		{
			$not = true;
			$criteria = substr($criteria, 1);
		}

		if (preg_match('/^\*([[:alnum:]]{1,})\*$/', $criteria, $p))
		{
			$sNot = $not ? ' NOT' : '';
			$sql = $field . $sNot . ' LIKE \'%' . $p[1] . '%\'';
		}
		if (preg_match('/^([[:alnum:]]{1,})\*$/', $criteria, $p))
		{
			$sNot = $not ? ' NOT' : '';
			$sql = $field . $sNot . ' LIKE \'' . $p[1] . '%\'';
		}
		if (preg_match('/^\*([.-\/[:alnum:]]{1,})$/', $criteria, $p))
		{
			$sNot = $not ? ' NOT' : '';
			$sql = $field . $sNot . ' LIKE \'%' . $p[1] . '\'';
		}
		if (preg_match('/^([[:alnum:]]{1,})$/', $criteria, $p))
		{
			$sNot = $not ? ' <>' : ' =';
			$sql = $field . $sNot . ' \'' . $p[1] . '\'';
		}
		//if (preg_match('/^(\d{1,})$/', $criteria, $p))
		//{
		//	$sNot = $not ? '<>' : ' = ';
		//	$sql = $field . $sNot . $p[1];
		//}
		if (preg_match('/^>(\d{1,})$/', $criteria, $p))
		{
			$sNot = $not ? ' NOT' : '';
			$sql = $field . $sNot . ' > ' . $p[1];
		}
		if (preg_match('/^<(\d{1,})$/', $criteria, $p))
		{
			$sNot = $not ? ' NOT' : '';
			$sql = $field . $sNot . ' < ' . $p[1];
		}
		if ($criteria === true)
		{
			$sql = $field . ' IS NOT NULL ';
		}
		if ($criteria === false or is_null($criteria))
		{
			$sql = $field . ' IS NULL ';
		}

		if ($sql != '')
		{
			$sql .= ' AND ';

			$this->criteria .= $sql;
	
			if ($fixed)
			{
				$this->fixedCriteria .= $sql;
			}
		}
		
		return $this;
	}
	public function addSort ($field, $direction = 'asc')
	{
		$direction = $direction == 0 ? 'ASC' : 'DESC';
		$this->sort[$field] = $direction;
		return $this;
	}
	public function render ()
	{
		$sql = '';

		if (!is_null($this->criteria)) {
			$sql .= ' WHERE ' . $this->criteria;//substr($this->criteria, 0, -4);

			if (preg_match('/\s(AND|OR)\s\)?$/', $sql, $m)) {
				$matched = $m[0];
				$length = strlen($matched);
				$sql = substr($sql, 0, -$length);

				if (substr($matched, -1, 1) == ')') {
					$sql .= ')';
				}

				// \MonitoLib\Dev::pre($m);
			}
		}
		if (count($this->sort) > 0) {
			$sql .=  ' ORDER BY ';

			foreach ($this->sort as $sk => $sv) {
				$sql .= $sk . ' ' . $sv . ', ';
			}

			$sql = substr($sql, 0, -2);
		}
	
		if ($this->dbms == 1) {
			if ($this->limitOffset > 0) {
				$this->limitStart = ($this->page - 1) * $this->limitOffset;
				$sql .= ' LIMIT ' . $this->limitStart . ',' . $this->limitOffset;
			}
		}

		return $sql;
	}
	public function getCountCriteria ()
	{
		$sql = '';

		if (!is_null($this->criteria))
		{
			$sql .= ' WHERE ' . substr($this->criteria, 0, -4);
		}

		return $sql;
	}
	public function getFixedCriteria ()
	{
		$sql = '';

		if (!is_null($this->fixedCriteria))
		{
			$sql .= ' WHERE ' . substr($this->fixedCriteria, 0, -4);
		}

		return $sql;
	}
	public function setDbms ($dbms)
	{
		$this->dbms = $dbms;
	}
	public function setLimit ($start, $offset)
	{
		$this->limitStart  = $start;
		$this->limitOffSet = $offset;
		return $this;
	}
	public function setLimitOffset ($limitOffset)
	{
		$this->limitOffset = $limitOffset;
		return $this;
	}
	public function setPage ($page)
	{
		$this->page = $page;
		
		

		
		//if ()
		//{
			//$this->limitStart = ($page - 1) * $this->limitOffset;
		//}
		
		return $this;
	}
	public function getCriteria ()
	{
		return substr($this->criteria, 0, -4);
	}
	public function getField ()
	{
		return $this->field;
	}
	public function getLimitOffset ()
	{
		return $this->limitOffset;
	}
	public function getLimitStart ()
	{
		return $this->limitStart;
	}
	public function getPage ()
	{
		return $this->page;
	}
	public function getName ()
	{
		return $this->name;
	}
	//public function getFilter ()
	//{
	//	return $this->filter;
	//}
	public function getValue ()
	{
		return $this->value;
		$v = $this->value;
		$r = '';
		$o= '=';

		if (preg_match('/[[:alnum:]]\*$/', $v))
		{
			$v  = substr($v, 0, -1) . '%';
			//$r .= $v . '%';
			$o = 'LIKE';
		}
		if (preg_match('/^\*[[:alnum:]]/', $v))
		{
			$v  = '%' . substr($v, 1);
			//$v = '%' . $v;
			$o = 'LIKE';
		}
		
		
		/*
		if (is_numeric($v))
		{
			$s .= $k . ' = ' . $v . ' AND ';
		}
		if (preg_match('/^\>[0-9]{1,}$/', $v))
		{
			$s .= $k . ' >= ' . substr($v, 1) . ' AND ';
		}
		if (preg_match('/^\<[0-9]{1,}$/', $v))
		{
			$s .= $k . ' <= ' . substr($v, 1) . ' AND ';
		}
		if (preg_match('/^[0-9]{1,}-[0-9]{1,}$/', $v))
		{
			$x  = explode('-', $v);
			$s .= $k . ' BETWEEN ' . $x[0] . ' AND ' . $x[1] . ' AND ';
		}
		if (preg_match('/^[a-z]{1,}\*$/', $v))
		{
			$s .= $k . ' LIKE \'' . substr($v, 0, (strlen($v) - 1)) . '%\' AND ';
		}
		if (preg_match('/^[a-z]{1,}$/', $v))
		{
			$s .= $k . ' LIKE \'%' . $v . '%\' AND ';
		}
		//if (preg_match('/[0-9a-z]/', $v))
		//{
			//$v  = is_numeric($v) ? $v : "'$v'";
			//$s .= $f . ' = ' . $v . ' AND ';
		//}
		
		//echo count($x) . '<br />';
			

		if($s != '')
		{
			$s = preg_replace('/AND $/', '', $s);
			$s = ' ' . ($append ? $type : 'WHERE') . ' ' . $s;
		}
		
		//echo $s;exit;*/

		return "$o '$v'";
	}
	public function like ($value)
	{
		$this->value  = $value;
		$this->filter = "LIKE '%$value%'";
	}
	public function setValue ($value)
	{
		$this->value = $value;
	}
	public function setFields ($fields) {
		$this->fields = $fields;
	}
	public function getFields () {
		return $this->fields;
	}
}