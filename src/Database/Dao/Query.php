<?php
namespace MonitoLib\Database\Dao;

use \MonitoLib\Exception\BadRequest;
use \MonitoLib\Functions;
use \MonitoLib\Validator;

class Query
{
    const VERSION = '1.0.0';
    /**
    * 1.0.0 - 2019-04-07
    * First versioned
    */

    const FIXED_QUERY = 1;
    const CHECK_null = 2;
    const RAW_QUERY = 4;

    const DB_MYSQL = 1;
    const DB_ORACLE = 2;

    private $criteria;
    private $fixedCriteria;
    private $reseted = false;

    private $selectedFields;

    private $page        = 1;
    private $perPage     = 0;
    private $orderBY     = [];
    private $sql;
    private $sqlCount;

    private $selectSql;
    private $selectSqlReady = false;
    private $countSql;
    private $countSqlReady = false;
    private $orderBySql;
    private $orderBySqlReady = false;

    private $modelFields;

    public function __construct ($sqlQuery = null)
    {
        // $this->sql = $sqlQuery;
        // $this->fields = $this->model->getFields();
        $this->parseRequest();
    }
    public function __toString ()
    {
        return $this->render();
    }

    public function andIn ($field, $values, $modifiers = 0)
    {
        if (empty($values)) {
            throw new BadRequest('Valores inválidos!');
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
    public function startGroup ($modifiers = 0)
    {
        $sql = '(';

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function startAndGroup ($modifiers = 0)
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
    public function startOrGroup ($modifiers = 0)
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
    public function endGroup ($modifiers = 0)
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

    private function addCriteriaParser ($logicalOperator, $comparisonOperator, $field, $value, $modifiers = 0)
    {
        $fields = $this->getModelFields();
        $type = $fields[$field]['type'];
        $name = $fields[$field]['name'];

        $fixed = ($modifiers & self::FIXED_QUERY) === self::FIXED_QUERY;
        $null  = ($modifiers & self::CHECK_null) === self::CHECK_null;
        $raw   = ($modifiers & self::RAW_QUERY) === self::RAW_QUERY;

        if (is_null($value) && $null) {
            return $this->andIsNull($name, $fixed);
        }

        if ($raw || $type === 'int') {
            $q = '';
        } else {
            $q = '\'';
        }

        $sql = "$name $comparisonOperator $q" . ($raw ? $value : $this->escape($value)) . "$q $logicalOperator ";

        if (substr($this->criteria, -1) === ')') {
            $this->criteria .= " $logicalOperator ";
        }

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        // \MonitoLib\Dev::vd($this->criteria);


        return $this;
    }
    public function andEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '=', $field, $value, $modifiers);
        return $this;
    }
    public function andGreaterEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '>=', $field, $value, $modifiers);
        return $this;
    }
    public function andGreaterThan ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '>', $field, $value, $modifiers);
        return $this;
    }
    public function andIsNull ($field, $modifiers = 0)
    {
        $sql = "$field IS null AND ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function andBitAnd ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '&', $field, $value, $modifiers);
        return $this;
    }
    public function andLessEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '<=', $field, $value, $modifiers);
        return $this;
    }
    public function andLessThan ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '<', $field, $value, $modifiers);
        return $this;
    }
    public function andNotEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', '<>', $field, $value, $modifiers);
        return $this;
    }
    public function andIsNotNull ($field, $modifiers = 0)
    {
        $sql = "$field IS NOT null AND ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function andLike ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', 'LIKE', $field, $value, $modifiers);
        return $this;
    }
    public function andNotLike ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('AND', 'NOT LIKE', $field, $value, $modifiers);
        return $this;
    }
    public function andFilter ($field, $value, $type = 'string')
    {
        // \MonitoLib\Dev::ee("andFilter\n");
        // \MonitoLib\Dev::ee($type);
        // $value = urldecode($value);

        $type = strtolower($type);

        switch ($type) {
            case 'date':
            case 'double':
            case 'int':
                $value = urldecode($value);

                // Verifica se é intervalo
                if (preg_match('/^([0-9.]+)-([0-9.]+)$/', $value, $m)) {
                    $this->criteria .= "$field BETWEEN $m[1] AND $m[2] AND ";
                    break;
                }

                // Verifica se tem algum modificador
                if (preg_match('/^([><=!]{1,2})?([0-9.\-\s:]+)$/', $value, $m)) {
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

                    // if ($type === 'date') {
                    //     $this->$method($field, "TO_DATE('{$m[2]}', 'YYYY-MM-DD')", self::RAW_QUERY);
                    //     continue;
                    // }

                    if ($type === 'date') {
                        $v = $m[2];

                        if (!Validator::date($v, 'Y-m-d') && !Validator::date($v, 'Y-m-d H:i:s')) {
                            throw new BadRequest('Data inválida: ' . $v);
                        }

                        $f = 'YYYY-MM-DD HH24:MI:SS';

                        if ($this->fields[$field]['format'] === 'Y-m-d H:i:s' && Validator::date($v, 'Y-m-d')) {
                            $field = "TRUNC($field)";
                        }

                        $this->$method($field, "TO_DATE('$v', '$f')", self::RAW_QUERY);
                        continue;
                    }

                    $this->$method($field, $m[2]);
                    break;
                } else {
                    // $this->andEqual($field, $value);
                    throw new BadRequest('Valor inválido!');
                }

                // Verifica se é lista
                // if (preg_match('/^[0-9.,\s]+$/', $value, $m)) {
                //     $this->andIn($field, explode(',', $m[0]));
                // }

                break;
            case 'string':
            default:
                // foreach ($strings as $s) {
                    $m = 'andEqual';
                    $s = urldecode($value);
                    $a = '';
                    $b = '';

                    \MonitoLib\Dev::e($s);

                    if (substr($s, 0, 1) === '%') {
                        // $s = substr($s, 1);
                        $a = '%';
                        $m = 'andLike';
                    }


                    echo substr($s, -1) . '=== %' . PHP_EOL . PHP_EOL;

                    if (substr($s, -1) === '%') {
                        // $s = substr($s, 0, -1);
                        $b = '%';
                        $m = 'andLike';
                    }

                    // $value   = urldecode($value);
                    // $strings = explode(' ', $value);

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

                    // if ($a === $b) {
                    //     $a = $b = '%';
                    // }

                    $this->$m($field, "{$a}{$s}{$b}");
                // }

                // break;
            // default:
                // throw new BadRequest('Tipo de campo inválido!');
        }

        return $this;
    }
    private function checkIfFieldExists ($field)
    {
        $field = trim(urldecode($field));

        if (is_null($this->modelFields)) {
            $this->modelFields = $this->getModel()->getFields();
        }

        if (isset($this->modelFields[$field])) {
            return $this->modelFields[$field];
        } else {
            return "O campo $field não existe no modelo de dados!";
        }
    }
    public function orderBy ($field, $direction = 'ASC')
    {
        $this->orderBy[$field] = strtoupper($direction);
        return $this;
    }
    public function orBitAnd ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('OR', '&', $field, $value, $modifiers);
        return $this;
    }
    public function orIsNull ($field, $modifiers = 0)
    {
        $sql = "$field IS null OR ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    public function orEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('OR', '=', $field, $value, $modifiers);
        return $this;
    }
    public function orNotEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('OR', '<>', $field, $value, $modifiers);
        return $this;
    }
    public function orLessEqual ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('OR', '<=', $field, $value, $modifiers);
        return $this;
    }
    public function orLessThan ($field, $value, $modifiers = 0)
    {
        $this->addCriteriaParser('OR', '<', $field, $value, $modifiers);
        return $this;
    }
    public function orIsNotNull ($field, $modifiers = 0)
    {
        $sql = "$field IS NOT null OR ";

        $this->criteria .= $sql;

        if ($fixed) {
            $this->fixedCriteria .= $sql;
        }

        return $this;
    }
    private function escape ($value) {
        return str_replace(['\\', "\0", "\n", "\r", "'", '"', "\x1a"], ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'], $value);
    }
    public function getModelFields () {
        if (is_null($this->modelFields)) {
            $this->modelFields = $this->getModel()->getFields();
        }

        return $this->modelFields;
    }
    private function getLimitSql ()
    {
        $sql = '';

        if ($this->perPage > 0 && $this->dbms == 1) {
            $sql .= ' LIMIT ' . (($this->page - 1) * $this->perPage) . ',' . $this->perPage;
        }

        return $sql;
    }
    public function getOrderBySql ()
    {
        $sql = '';

        if (!empty($this->orderBy)) {
            $sql = ' ORDER BY ';

            foreach ($this->orderBy as $k => $v) {
                $sql .= $k . ' ' . ($v === '' ? '' : strtoupper($v)) . ', ';
            }
        }

        $sql = substr($sql, 0, -2);
        return $sql;
    }
    public function getPage ()
    {
        return $this->page;
    }
    public function getPerPage ()
    {
        return $this->perPage;
    }
    protected function getSelectFields ()
    {
        $list     = '';
        $selected = $this->selectedFields;

        if (empty($selected)) {
            $selected = $this->getModelFields();
        }

        foreach ($selected as $k => $v) {
            $field = $v['name'];

            if ($v['type'] === 'date' && $this->dbms === 2) {
                $mask  = 'YYYY-MM-DD' . ($v['format'] === 'Y-m-d H:i:s' ? ' HH24:MI:SS' : '');
                $field = "TO_CHAR($field, '$mask') AS $field";
            }

            $list .= "$field, ";
        }

        $list = substr($list, 0, -2);

        // \MonitoLib\Dev::ee($list);
        return $list;
    }
    public function getWhereSql ($fixed = false)
    {
        // \MonitoLib\Dev::e($this->criteria);
        $criteria = $fixed ? $this->fixedCriteria : $this->criteria;
        $sql = '';

        if (!is_null($criteria)) {
            $sql = ' WHERE '. $criteria;

            if (preg_match('/\s(AND|OR)\s\)?$/', $sql, $m)) {
                $matched = $m[0];
                $length = strlen($matched);
                $sql = substr($sql, 0, -$length);

                if (substr($matched, -1, 1) == ')') {
                    $sql .= ')';
                }
            }
        }

        return $sql;
    }
    private function parseRequest ()
    {
        $request     = \MonitoLib\Request::getInstance();
        $queryString = $request->getQueryString();

        $selectedFields = [];

        $errors = [];

        foreach ($queryString as $key => $value) {
            switch ($key) {
                case 'fields':
                    if ($value === '') {
                        throw new BadRequest('É preciso informar pelo menos um campo!');
                    }

                    $parts = explode(',', $value);

                    foreach ($parts as $v) {
                        $field = $this->checkIfFieldExists($v);

                        if (is_array($field)) {
                            $this->selectedFields[$v] = $field;
                        } else {
                            $errors = $field;
                        }
                    }
                    break;
                case 'page':
                    if (!is_numeric($value) && !is_integer(+$value)) {
                        throw new BadRequest('Número da página inválido!');
                    }
                    $this->page = $value;
                    break;
                case 'perPage':
                    if (!is_numeric($value) && !is_integer(+$value)) {
                        throw new BadRequest('Quantidade por página inválida!');
                    }
                    $this->perPage = $value;
                    break;
                case 'orderBy':
                    foreach ($value as $v) {
                        $parts = explode(',', $v);
                        $field = $this->checkIfFieldExists($parts[0]);

                        if (is_array($field)) {
                            $this->orderBy[$field['name']] = $parts[1];
                        } else {
                            $errors = $field;
                        }
                    }
                    break;
                case 'query':
                    foreach ($value as $k => $val) {
                        $field = $this->checkIfFieldExists($k);

                        if (is_array($field)) {
                            foreach ($val as $v) {
                                $this->andFilter($k, $v, $field['type']);
                            }
                        } else {
                            $errors = $field;
                        }
                    }
            }
        }

        if (!empty($errors)) {
            throw new BadRequest('Campo informado não existe no modelo de dados!', $errors);
        }
    }
    public function renderSelectSql ()
    {
        $sql = $this->sql;

        if (is_null($sql)) {
            if ($this->selectSqlReady) {
                return $this->getSelectSql();
            }

            $sql = 'SELECT ' . $this->getSelectFields() . ' FROM ' . $this->model->getTableName() . $this->getWhereSql() . $this->getOrderBySql() . $this->getLimitSql();
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
        $this->reseted       = true;
        return $this;
    }
    public function renderCountSql ($all = false)
    {
        return 'SELECT COUNT(*) FROM ' . $this->model->getTableName() . $this->getWhereSql($all);
    }
    public function renderDeleteSql ()
    {
        return 'DELETE FROM ' . $this->model->getTableName() . $this->getWhereSql();
    }
    public function setDbms ($dbms)
    {
        $this->dbms = $dbms;
    }
    public function setFields ($fields) {
        $this->fields = $fields;
        return $this;
    }
    public function setModel ($model) {
        $this->model = $model;
        return $this;
    }
    public function setPage ($page)
    {
        $this->page = $page;
        return $this;
    }
    public function setPerPage ($perPage)
    {
        $this->perPage = $perPage;
        return $this;
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
    public function setTableName ($tableName) {
        $this->tableName = $tableName;
        return $this;
    }
}