<?php
namespace MonitoLib\Database\Model;

use \MonitoLib\Functions;

class Model
{
    public function getDefaults ($option = null)
    {
        if (is_null($option)) {
            return $this->defaults;
        } else {
            if (isset($this->defaults[$option])) {
                return $this->defaults[$option];
            }

            throw new \Exception("There's no '$option' option in defaults options");
        }
    }
    public function getField ($field)
    {
        if (isset($this->fields[$field])) {
            return $this->fields[$field];
        }
    }
    public function getFields ()
    {
        return $this->fields;
    }
    // Retorna string com campos da tabela separados por vírgula, ignorando campos de auto incremento
    public function getFieldsInsert ()
    {
        $fields = array();

        if (isset($this->fields[0])) {
            return $this->fields;
        } else {
            foreach ($this->fields as $fk => $fv) {
                $fv = Functions::ArrayMergeRecursive($this->defaults, $fv);
    
                if (!$fv['auto']) {
                    $fields[] = $fk;
                }
            }
        }

        return $fields;
    }
    // Retorna array com lista dos campos da tabela
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
    // Retorna string com campos da tabela separados por vírgula
    public function getFieldsSerialized ($fields = null)
    {
        // \MonitoLib\Dev::pr($fields);
        $list = ' ';

        if (is_null($fields) || count($fields) === 0) {
            $fields = $this->getFieldsList();
        }

        foreach ($fields as $f) {
            if (isset($this->fields[$f])) {
                $list .= '`';

                if (isset($this->fields['name'])) {
                    $list .= $this->fields['name'];
                } else {
                    $list .= $f;
                }

                $list .= '`,';
            } else {
                throw new \MonitoLib\Exception\BadRequest("O campo $f não existe na tabela {$this->getTableName()}!");
            }
        }

        return substr($list, 0, -1);
    }
    public function getPrimaryKeys ()
    {
        return $this->keys;
    }
    public function getPrimaryKey ()
    {
        $keys = 'id';

        if (!is_null($this->keys)) {
            $keys = NULL;

            foreach ($this->keys as $k) {
                $keys .= "$k,";
            }

            $keys = substr($keys, 0, -1);
        }

        return $keys;
    }
    public function listFieldsNames ()
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
}