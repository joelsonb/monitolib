<?php
/**
 * 1.0.0. - 2017-06-26
 * Inicial release
 */
namespace MonitoLib;

class Request
{
    const VERSION = '2.0.1';
    /**
    * 2.0.1 - 2019-06-05
    * fix: getPage and getPerPage to return only valid numbers
    *
    * 2.0.0 - 2019-05-02
    * new: new gets
    *
    * 1.0.0 - 2019-04-17
    * first versioned
    */

    static private $instance;

    private $json = [];
    private $queryString = [];
    private $requestUri;
    private $post;
    private $params;

    private $fields;
    private $orderBy;
    private $page;
    private $perPage;
    private $query;

    private function __construct ()
    {
        $this->post = $_POST;
    }
    public static function getInstance ()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Request;
        }

        return self::$instance;
    }
    public function getFields ()
    {
        if (is_null($this->fields)) {
            if (isset($this->queryString['fields'])) {
                $this->fields = explode(',', $this->queryString['fields']);
            }
        }

        return $this->fields;
    }
    public function getJson ($asArray = false)
    {
        $this->json = json_decode(file_get_contents('php://input'), $asArray);
        return $this->json;
    }
    public function getOrderBy ()
    {
        if (is_null($this->orderBy) && (isset($this->queryString['orderBy']))) {
            foreach ($this->queryString['orderBy'] as $value) {
                $p = explode(',', $value);
                $this->orderBy[$p[0]] = $p[1] ?? '';
            }
        }

        return $this->orderBy;
    }
    public function getPage ()
    {
        return (is_numeric($this->page) && $this->page > 0) ? $this->page : 1;
    }
    public function getParam ($key = null)
    {
        if (is_null($key)) {
            return $this->params;
        } else {
            if (isset($this->params[$key])) {
                return $this->params[$key];
            } else {
                return null;
            }
        }
    }
    public function getPerPage ()
    {
        return (is_numeric($this->perPage) && $this->perPage > 0) ? $this->perPage : 0;
    }
    public function getPost ($key = null)
    {
        if (is_null($key)) {
            return $this->post;
        } else {
            if (isset($this->post[$key])) {
                return $this->post[$key];
            } else {
                return null;
            }
        }
    }
    public function getQuery ()
    {
        return $this->query;
    }
    public function getQueryString ($key = null)
    {
        if (is_null($key)) {
            return $this->queryString;
        } else {
            if (isset($this->queryString[$key])) {
                return $this->queryString[$key];
            } else {
                return null;
            }
        }
    }
    public function getRequestUri ()
    {
        return $this->requestUri;
    }
    public function setQueryString ($queryString)
    {
        $fields = explode('&', $queryString);

        foreach ($fields as $field) {
            $p = strpos($field, '=');
            $f = substr($field, 0, $p);
            $v = substr($field, $p + 1);

            if (strcasecmp($f, 'fields') === 0) {
                $this->queryString['fields'] = $v;
            } elseif (strcasecmp($f, 'page') === 0) {
                $this->page = $v;
            } elseif (strcasecmp($f, 'perpage') === 0) {
                $this->perPage = $v;
            } elseif (strcasecmp($f, 'orderby') === 0) {
                $this->queryString['orderBy'][] = $v;
            } else {
                $this->query[] = [$f => $v];
            }
        }
    }
    public function setParams ($params)
    {
        $this->params = $params;
    }
    public function setRequestUri ($requestUri)
    {
        $this->requestUri = '/' . $requestUri;
    }
}