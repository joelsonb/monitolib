<?php
namespace MonitoLib\Database\Dao;

use \MonitoLib\Functions;

class Oracle extends \MonitoLib\Database\Dao\Filter implements \MonitoLib\Database\Dao
{
    protected $dto;
    protected $dtoName;
    protected $conn;
    protected $model;

    private $namespace = '\\';

    public function __construct ()
    {
        if (is_null($this->conn)) {
            $connector  = \MonitoLib\Database\Connector::getInstance();
            $this->connection = $connector->getConnection();
            $this->conn = $connector->getConnection()->getConnection();

            if (is_null($this->conn)) {
                throw new \Exception('Database not connected!');
            }
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

        $this->namespace .= str_replace('\\dao', '', substr($class, 0, strrpos($class, '\\'))) . '\\';

        $this->setDbms(4);

        // echo $this->namespace . "\n";
        // exit;
    }
    public function count ()
    {
        $sql = $this->renderSql('COUNT');
        $stt = oci_parse($this->conn, $sql);
        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }

        $res = oci_fetch_row($stt);

        return $res[0];
    }
    public function dataset ()
    {
        $data = [];

        $sql = $this->renderCountAllSql();
        $stt = oci_parse($this->conn, $sql);
        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }

        $res = oci_fetch_row($stt);

        $total = $res[0];
        $count = 0;

        if ($total > 0) {
            $sql = $this->renderCountSql();
            $stt = oci_parse($this->conn, $sql);
            $exe = $this->connection->execute($stt);

            if (!$exe) {
                $e = oci_error($stt);
                throw new \Exception($e['message']);
            }

            $res = oci_fetch_row($stt);

            $count = $res[0];

            if ($count > 0) {
                $startRow = (($this->getPage() - 1) * $this->getPerPage()) + 1;
                $endRow   = $this->getPerPage() * $this->getPage();

                $sql = "SELECT * FROM (SELECT a.*, ROWNUM as rown FROM ({$this->renderSql()}) a) WHERE rown BETWEEN $startRow AND $endRow";
                // \MonitoLib\Dev::e($sql);
                // Reseta para evitar que list() use os parâmetros do busca
                $this->reset();
                // // \MonitoLib\Dev::e($this->renderSql());

                $data = $this->setSql($sql)->list();

                // Reseta novamente para limpar $sql
                $this->reset();
            }
        }

        return [
            'count' => $count,
            'data'  => $data,
            'total' => $total,
            'page'  => $this->getPerPage(),
            'pages' => ceil($count / $this->getPerPage())
        ];
    }
    public function delete (...$params)
    {
        // if ($this->model->getTableType() == 'view') {
        //     throw new \Exception('A view object is readonly!');
        // }

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

        $stt = oci_parse($this->conn, $this->renderSql('DELETE'));
        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }

        // Reset filter
        $this->reset();

        return oci_num_rows($stt);
    }
    public function get ()
    {
        $sql = $this->renderSql();

        // echo "$sql\n";

        $stt = oci_parse($this->conn, $sql);
        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }

        if ($this->complete && !is_null($this->model)) {
            $result = oci_fetch_array($stt, OCI_NUM | OCI_RETURN_NULLS);
            $dto = NULL;

            if ($result) {
                // $class = $this->namespace . 'dto\\' . $fv['model'];
                $class = $this->dtoName;

                $dto = new $class;

                $i = 0;

                foreach ($this->model->getFields() as $f) {
                    // $var = Functions::toLowerCamelCase($fk.$f);
                    $set = 'set' . Functions::toUpperCamelCase($f);
                    $dto->$set($result[$i]);
                    $i++;
                }
            }
        } else {
            $dto = $this->toDataTransferObject($stt, true);
        }

        $this->reset();
        return $dto;
    }
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
    public function getLastId ()
    {

    }
    public function insert ($dto)
    {
        if (!$dto instanceof $this->dtoName) {
            throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
        }

        //\jLib\Dev::vde($this->conn);

        $sql = 'INSERT INTO ' . $this->model->getTableName() . ' ('
             . implode(',', $this->model->getFields()) . ') '
             . 'VALUES (:' . implode(',:', $this->model->getFields()) . ')';
        $stt = oci_parse($this->conn, $sql);

        //echo "$sql\n";

        foreach ($this->model->getFields() as $f) {
            $var = \MonitoLib\Functions::toLowerCamelCase($f);
            $get = 'get' . ucfirst($var);

            $$var = $dto->$get();

            // Checks if ins_date and upd_date are null and set current date as its values
            //if (in_array($f, array('ins_date', 'upd_date')))
            //{
            //  if (is_null($$var))
            //  {
            //      $$var = date('Y-m-d H:i:s');
            //  }
            //}

            //echo ":$var = {$$var}\n";

            if (!@oci_bind_by_name($stt, ':' . $f, $$var)) {
                throw new \Exception("Error on :$f bind!");
            }
        }

        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }
    }
    public function list ()
    {
        $data = [];

        $sql = $this->renderSql();

        $stt = oci_parse($this->conn, $sql);
        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }
        if ($this->complete && !is_null($this->model)) {
            while ($r = oci_fetch_row($stt)) {
                // $class = $this->namespace . 'dto\\' . $fv['model'];
                $class = $this->dtoName;

                $dto = new $class;

                $i = 0;

                foreach ($this->model->getFields() as $f) {
                    // $var = Functions::toLowerCamelCase($fk.$f);
                    $set = 'set' . Functions::toUpperCamelCase($f);
                    $dto->$set($r[$i]);
                    $i++;
                }

                $data[] = $dto;
            }
        } else {
            $data = $this->toDataTransferObject($stt);
        }

        $stt = null;

        return $data;
    }



    public function truncate ()
    {

    }
    public function update ($dto)
    {
        if (!$dto instanceof $this->dtoName) {
            throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
        }

        $update = NULL;
        $keys   = NULL;

        // TODO: isso é só pra funcionar, depois é para fazer corretamente
        $aKeys  = array();
        $aFields = array();

        // \MonitoLib\Dev::pre($this->model->getPrimaryKey());

        $key = null;





            // $key    = NULL;

            // // TODO: isso é só pra funcionar, depois é para fazer corretamente
            // $aKeys  = array();
            // $aFields = array();

            // foreach ($this->model->getFields() as $f) {
            //  if (in_array($f, $this->model->getPrimaryKeys())) {
            //      $aKeys[] = $f;
            //      $key    .= "$f = ? AND ";
            //  } else {
            //      $update .= "`$f` = ?,";
            //      $aFields[] = $f;
            //  }
            // }



        foreach ($this->model->getFieldsList() as $fk => $f) {
            // if ($fk == $this->model->getPrimaryKey()) {



            if (in_array($fk, $this->model->getPrimaryKeys())) {
                $aKeys[] = $fk;
                $key    .= "$fk = :$fk AND ";
            } else {
                if (isset($f['type']) && $f['type'] == 'datetime') {
                    $update .= "$fk = TO_DATE(:$fk, 'YYYY-MM-DD HH24:MI:SS'), ";
                } elseif (isset($f['type']) && $f['type'] == 'date') {
                    $update .= "$fk = TO_DATE(:$fk, 'YYYY-MM-DD'), ";
                } else {
                    $update .= "$fk = :$fk, ";
                }
                $aFields[] = $fk;
            }
        }

        $update = substr($update, 0, -2);
        $key    = substr($key, 0, -4);

        $sql  = 'UPDATE ' . $this->model->getTableName() . " SET $update WHERE $key";


        // \MonitoLib\Dev::e($sql);

        // echo "$sql\n";


        $stt = oci_parse($this->conn, $sql);

        $i = 1;

        $aFields = array_merge($aFields, $aKeys);

        foreach ($aFields as $f) {
            $var = \MonitoLib\Functions::toLowerCamelCase($f);
            $get = 'get' . ucfirst($var);

            $$var = $dto->$get();
            oci_bind_by_name($stt, ":$var", $$var);

            // echo ":$var, $$var \n";

            // $stmt->bindParam($i, $$var);
            $i++;
        }

        // echo 'ooo';
        // exit;

        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            \MonitoLib\Dev::pre($dto);
            throw new \Exception($e['message']);
        }
        // $stmt->execute();
        // $stmt = NULL;
        return $dto;
    }

    public function OLDlimitedSql ($sql, $filter)
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
        $exe = $this->connection->execute($stt);

        if (!$exe) {
            $e = oci_error($stt);
            throw new \Exception($e['message']);
        }
    }


    public function toDataTransferObject ($stt, $first = false)
    {
        $data = [];

        while ($r = oci_fetch_array($stt, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $dto = new \MonitoLib\Database\Dto($r);

            if ($first) {
                return $dto->getData();
            }

            $data[] = $dto->getData();
        }

        if ($first) {
            return null;
        }

        return $data;
    }
}