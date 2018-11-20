<?php
namespace MonitoLib\Database\Dao;

class Filter
{
    const FIXED_FILTER = 1;
    const CHECK_NULL = 2;
    const RAW_FILTER = 4;
    // 1 - MySql
    // 2 - SQLite
    // 3 - MSSQ
    // 4 - Oracle

    private $criteria;
    private $countCriteria;
    private $fixedCriteria;
    private $fields;


    private $tableName;

    private $dbms        = 1;
    private $page        = 1;
    private $limitStart  = 0;
    private $limitOffset = 0;
    private $perPage     = 0;
    private $sort        = [];
    private $sql;
    private $sqlCount;

    protected $complete = false;

    public function __construct ($sqlQuery = null)
    {
        $this->sql = $sqlQuery;
    }
    public function __toString ()
    {
        return $this->render();
    }

    public function andIn ($field, $values, $fixed = false)
    {
        if (count($values) == 0) {
            throw new \Exception('Valores inválidos!');
        }

        $value = '';

        foreach ($values as $v) {
            if (is_numeric($v)) {
                $value .= $v;
            } else {
                $value .= "'" . $this->escape($v) . "'";
            }

            $value .= ',';
        }

        $value = substr($value, 0, -1);

        $sql = "$field IN ($value) AND ";

        $this->criteria .= $sql;

        if ($fixed) {
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
    public function startAndGroup ($fixed = false)
    {
        if (preg_match('/ (AND|OR) $/', $this->criteria, $m)) {
            $this->criteria = substr($this->criteria, 0, strlen($m[0]) * -1);
        }

        $sql = ' AND (';

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function startOrGroup ($fixed = false)
    {
        if (preg_match('/ (AND|OR) $/', $this->criteria, $m)) {
            $this->criteria = substr($this->criteria, 0, strlen($m[0]) * -1);
        }

        $sql = ' OR (';

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function endGroup ($fixed = false)
    {
        $sql = ')';

        if (preg_match('/ (AND|OR) $/', $this->criteria, $m)) {
            $this->criteria = substr($this->criteria, 0, strlen($m[0]) * -1);
        }

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    private function escape ($value) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
    }
    private function addCriteriaParser ($seila, $operator, $field, $value, $fixed = false, $null = false, $raw = false) {
        if (($null === true || $fixed & self::CHECK_NULL) && is_null($value)) {
            return $this->andIsNull($field, $fixed);
        }

        if ($raw === true || $fixed & self::RAW_FILTER) {
            $q = '';
        } else {
            $q = '\'';
        }

        $sql = "$field $operator $q" . ($raw ? $value : $this->escape($value)) . "$q $seila ";

        if (substr($this->criteria, -1) === ')') {
            $this->criteria .= " $seila ";
        }

        $this->criteria .= $sql;

        if ($fixed === true || $fixed & self::FIXED_FILTER) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function andEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', '=', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andGreaterEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', '>=', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andGreaterThan ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('OR', '>', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andIsNull ($field, $fixed = false)
    {
        $sql = "$field IS NULL AND ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function orIsNull ($field, $fixed = false)
    {
        $sql = "$field IS NULL OR ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function andBitAnd ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', '&', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function orBitAnd ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('OR', '&', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andLessEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', '<=', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function orEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('OR', '=', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function orNotEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('OR', '<>', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function orLessEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('OR', '<=', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function orLessThan ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('OR', '<', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andLessThan ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', '<', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andNotEqual ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', '<>', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andIsNotNull ($field, $fixed = false)
    {
        $sql = "$field IS NOT NULL AND ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function orIsNotNull ($field, $fixed = false)
    {
        $sql = "$field IS NOT NULL OR ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function andLike ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', 'LIKE', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andNotLike ($field, $value, $fixed = false, $null = false, $raw = false)
    {
        $this->addCriteriaParser('AND', 'NOT LIKE', $field, $value, $fixed, $null, $raw);
        return $this;
    }
    public function andFilter ($field, $value, $type = 'string')
    {
        $value = urldecode($value);

        switch (strtolower($type)) {
            case 'number':
                // Verifica se é intervalo
                if (preg_match('/^([0-9.]+)-([0-9.]+)$/', $value, $m)) {
                    $this->criteria .= "$field BETWEEN $m[1] AND $m[2] AND ";
                    break;
                }

                // Verifica se tem algum modificador
                if (preg_match('/^([><=!]{1,2})?([0-9]+)$/', $value, $m)) {
                    switch ($m[1]) {
                        case '>':
                            $method = 'andGreaterThan';
                            break;
                        case '<':
                            $method = 'andLessThan';
                            break;
                        case '>=':
                            $method = 'andGreaterEqual';
                            break;
                        case '<=':
                            $method = 'andLessEqual';
                            break;
                        case '<>':
                        case '!':
                            $method = 'andNotEqual';
                            break;
                        default:
                            $method = 'andEqual';
                            break;
                    }

                    $this->$method($field, $m[2]);
                    break;
                }

                // Verifica se é lista
                if (preg_match('/^[0-9.,\s]+$/', $value, $m)) {
                    $this->andIn($field, explode(',', $m[0]));
                }

                break;
            case 'string':
                if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
                    $this->andEqual($field, substr($value, 1, -1));
                } else {
                    $strings = explode(' ', $value);

                    foreach ($strings as $s) {
                        $a = '%';
                        $b = '%';
                        $m = 'andLike';
                        $f = substr($s, 0, 1);
                        $l = substr($s, -1);

                        if ($f === '"' && $l === '"') {
                            $s = substr($s, 1, -1);
                        }

                        if ($f === '!') {
                            $m = 'andNotLike';
                            $s = substr($s, 1);
                            $f = substr($s, 0, 1);
                        }

                        if ($f === '%') {
                            $f = substr($s, 0, 1);
                        } else {
                            $a = '';
                        }

                        if ($l === '%') {
                            $s = substr($s, 0, -1);
                        } else {
                            $b = '';
                        }

                        if ($a === $b) {
                            $a = $b = '%';
                        }

                        $this->$m($field, "{$a}{$s}{$b}");
                    }
                }
                break;
        }

        return $this;
    }
    public function orderBy ($field, $direction = 'ASC')
    {
        $this->sort[$field] = strtoupper($direction);
        return $this;
    }
    public function renderSql ($command = 'SELECT')
    {
        if (is_null($this->sql)) {
            switch ($command) {
                case 'SELECT':
                    $sql = $command;

                    if ($this->dbms === 1) {
                        $sql .= ' `' . implode('`,`', $this->fields) . '`';
                    } else {
                        $sql .= ' ' . implode(',', $this->fields);
                    }
                    $this->complete = true;
                    break;
                case 'COUNT':
                    // TODO: mudar completamente essa bizarrice
                    if (is_null($this->sqlCount)) {
                        $sql = 'SELECT COUNT(*) AS count';
                    } else {
                        $sql = $this->sqlCount;
                    }
                    break;
                default:
                    $sql = $command;
                    break;
            }

            if (is_null($this->sqlCount)) {
                $sql .= ' FROM ' . $this->tableName;
            }

        } else {
            $sql = $this->sql;
        }

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

        if ($this->perPage > 0) {
            if ($this->dbms == 1) {
                $sql .= ' LIMIT ' . (($this->page - 1) * $this->perPage) . ',' . $this->perPage;
            }
        }

        return $sql;
    }
    protected function reset ()
    {
        $this->criteria      = null;
        $this->countCriteria = null;
        $this->fixedCriteria = null;
        $this->command       = 'SELECT';
        $this->page          = 1;
        $this->limitStart    = 0;
        $this->limitOffset   = 0;
        $this->sort          = [];
        $this->sql           = null;
        $this->complete      = false;
    }
    public function renderCountAllSql ()
    {
        $request = \MonitoLib\Request::getInstance();

        if (!is_null($page = $request->getQueryString('page'))) {
            if (preg_match('/^[0-9]{1,}$/', $page)) {
                $this->page = $page;
            }
        }

        if (!is_null($perPage = $request->getQueryString('perPage'))) {
            if (preg_match('/^[0-9]{1,}$/', $perPage)) {
                $this->perPage = $perPage;
            }
        }

        if (is_null($sql = $this->sqlCount)) {
            $sql = 'SELECT COUNT(*) FROM ' . $this->tableName;
        }

        return $sql . $this->getFixedCriteria();
    }
    public function renderCountSql ()
    {
        if (is_null($sql = $this->sqlCount)) {
            $sql = 'SELECT COUNT(*) FROM ' . $this->tableName;
        }

        return $sql . $this->getCountCriteria();
    }
    public function getCountCriteria ()
    {
        $sql = '';

        if (!is_null($this->criteria)) {
            $sql .= ' WHERE ' . substr($this->criteria, 0, -4);
        }

        return $sql;
    }
    public function getFixedCriteria ()
    {
        $sql = '';

        if (!is_null($this->fixedCriteria)) {
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
            $this->limitStart = ($page - 1) * $this->limitOffset;
        //}

        return $this;
    }
    public function setPerPage ($perPage)
    {
        $this->perPage = $perPage;
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
    public function getPerPage ()
    {
        return $this->perPage;
    }
    public function getName ()
    {
        return $this->name;
    }
    public function setSql ($sql)
    {
        $this->sql = $sql;
        return $this;
    }
    public function setSqlCount ($sqlCount)
    {
        $this->sqlCount = $sqlCount;
        return $this;
    }
    public function setFields ($fields) {
        $this->fields = $fields;
        return $this;
    }
    public function setTableName ($tableName) {
        $this->tableName = $tableName;
        return $this;
    }
    public function getFields () {
        return $this->fields;
    }
}