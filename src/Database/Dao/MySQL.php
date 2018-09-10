<?php
namespace MonitoLib\Database\Dao;

use \MonitoLib\Functions;

class MySQL extends \MonitoLib\Database\Dao\Filter implements \MonitoLib\Database\Dao
{
    protected $dto;
    protected $dtoName;
    protected $conn;
    protected $connection;
    protected $model;
    private $namespace = '';

    public function __construct ()
    {
        if (is_null($this->conn)) {
            $connector  = \MonitoLib\Database\Connector::getInstance();
            $this->conn = $connector->getConnection()->getConnection();
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

        $this->namespace .= str_replace('dao\\', '', substr($class, 0, strrpos($class, '\\')));
    }
    // TODO: to implement
    /**
    * count
    */
    public function count ()
    {
        $sql = $this->renderSql('COUNT');
        $stm = $this->conn->prepare($sql);
        $stm->execute();
        $res = $stm->fetch(\PDO::FETCH_ASSOC);
        return $res['count'];
    }
    /**
    * dataset
    */
    public function dataset ()
    {
        $data = [];
        $page  = $this->getPage();
        $limit = $this->getPerPage();

        $sql = $this->renderCountAllSql();
        $stt = $this->conn->prepare($sql);
        $stt->execute();
        $res = $stt->fetch(\PDO::FETCH_NUM);

        $total = $res[0];
        $count = 0;

        if ($total > 0) {
            $sql = $this->renderCountSql();
            $stt = $this->conn->prepare($sql);
            $stt->execute();
            $res = $stt->fetch(\PDO::FETCH_NUM);

            $count = $res[0];

            if ($count > 0) {
                $data = $this->list();
            }
        }

        return [
            'count' => $count,
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'pages' => $limit > 0 ? ceil($count / $limit) : 1
        ];
    }
    /**
    * delete
    * @todo allow delete using dtoObject
    * @todo validate deleting without parameters
    * @todo validate deleting without all key parameters
    */
    public function delete (...$params)
    {
        if ($this->model->getTableType() == 'view') {
            throw new \Exception('A view object is readonly!');
        }

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

        $stt = $this->conn->prepare($this->renderSql('DELETE'));
        $stt->execute();

        // Reset filter
        $this->reset();

        return $stt->rowCount();
    }
    /**
    * get
    */
    public function get ()
    {
        $sql = $this->renderSql();
        $stt = $this->conn->prepare($sql);
        $stt->execute();

        $dto = NULL;

        if ($res = $stt->fetch(\PDO::FETCH_ASSOC)) {
            if (!is_null($this->model) && array_keys($res) === $this->model->getFields()) {
                $dto = new $this->dtoName;
            } else {
                $dto = \MonitoLib\Database\Dto::get($res);
            }

            foreach ($res as $f => $v) {
                $set = 'set' . Functions::toUpperCamelCase($f);
                $dto->$set($v);
            }
        }

        // Reset filter
        $this->reset();
        return $dto;
    }
    /**
    * getById
    */
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
    /**
    * getConnection
    */
    public function getConnection ()
    {
        return $this->conn;
    }
    /**
    * getLastId
    */
    public function getLastId ()
    {
        return $this->conn->lastInsertId();
    }
    /**
    * insert
    */
    public function insert ($dto)
    {
        try {
            if ($this->model->getTableType() == 'view') {
                throw new \Exception('Não é possível inserir registros em uma view!');
            }

            if (!$dto instanceof $this->dtoName) {
                throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
            }

            if (method_exists($dto, 'setInsTime') && is_null($dto->getInsTime())) {
                $dto->setInsTime(date('Y-m-d H:i:s'));
            }

            $sql = 'INSERT INTO ' . $this->model->getTableName() . ' ('
                 . '`' . implode('`,`', $this->model->getFieldsInsert()) . '`) '
                 . 'VALUES (' . substr(str_repeat('?,', count($this->model->getFieldsInsert())), 0, -1) . ')';
            $stt = $this->conn->prepare($sql);

            $i = 1;

            foreach ($this->model->getFieldsInsert() as $f) {
                $var = Functions::toLowerCamelCase($f);
                $get = 'get' . ucfirst($var);

                $$var = $dto->$get();

                $stt->bindParam($i, $$var);
                $i++;
            }

            $stt->execute();
            $stt = NULL;

            // TODO: tratar as chaves primárias
            if (method_exists($dto, 'setId') && is_null($dto->getId())) {
                $dto->setId($this->conn->lastInsertId());
                return $dto;
            }
        } catch (\PDOException $e) {
            \MonitoLib\Dev::pre($e);
        }
    }
    /**
    * list
    */
    public function list ()
    {
        $sql = $this->renderSql();
        $stt = $this->conn->prepare($sql);
        $stt->execute();

        $data = [];

        while ($res = $stt->fetch(\PDO::FETCH_ASSOC)) {
            if (!is_null($this->model) && array_keys($res) === $this->model->getFields()) {
                $dto = new $this->dtoName;
            } else {
                $dto = \MonitoLib\Database\Dto::get($res);
            }

            foreach ($res as $f => $v) {
                $set = 'set' . ucfirst(\MonitoLib\Functions::toLowerCamelCase($f));
                $dto->$set($v);
            }

            $data[] = $dto;
        }

        // Reset filter
        $this->reset();

        return $data;
    }
    /**
    * truncate
    */
    public function truncate ()
    {
        $sql = 'TRUNCATE TABLE ' . $this->model->getTableName();
        $stt = $this->conn->prepare($sql);
        $stt->execute();
    }
    /**
    * update
    */
    public function update ($dto)
    {
        try {
            if ($this->model->getTableType() == 'view') {
                throw new \Exception('Não é possível atualizar registros de uma view!');
            }

            if (!$dto instanceof $this->dtoName) {
                throw new \Exception('O parâmetro passado não é uma instância de ' . $this->dtoName . '!');
            }

            if (method_exists($dto, 'setUpdTime') && is_null($dto->getUpdTime())) {
                $dto->setUpdTime(date('Y-m-d H:i:s'));
            }

            $update = NULL;
            $key    = NULL;

            // TODO: isso é só pra funcionar, depois é para fazer corretamente
            $aKeys  = array();
            $aFields = array();

            foreach ($this->model->getFields() as $f) {
                if (in_array($f, $this->model->getPrimaryKeys())) {
                    $aKeys[] = $f;
                    $key    .= "$f = ? AND ";
                } else {
                    $update .= "`$f` = ?,";
                    $aFields[] = $f;
                }
            }

            $update = substr($update, 0, -1);
            $key    = substr($key, 0, -4);

            $sql  = 'UPDATE ' . $this->model->getTableName() . " SET $update WHERE $key";

            $stt = $this->conn->prepare($sql);

            $i = 1;

            $aFields = array_merge($aFields, $aKeys);

            foreach ($aFields as $f) {
                $var = Functions::toLowerCamelCase($f);
                $get = 'get' . ucfirst($var);

                $$var = $dto->$get();
                $stt->bindParam($i, $$var);
                $i++;
            }

            $stt->execute();
            $updated = $stt->rowCount();
            $stt = NULL;

            return $updated;
        } catch (\PDOException $e) {
            \MonitoLib\Dev::pre($e);
        }
    }
}