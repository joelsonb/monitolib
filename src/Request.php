<?php
/**
 * 1.0.0. - 2017-06-26
 * Inicial release
 */
namespace MonitoLib;

class Request
{
    static private $instance;

    private $json = [];
    private $queryString = ['orderBy' => []];
    private $requestUri;
    private $post;
    private $params;

    private function __construct ()
    {
        $this->post = $_POST;
    }
    public static function getInstance ()
    {
        if (!isset(self::$instance)) {
            self::$instance = new \MonitoLib\Request;
        }

        return self::$instance;
    }
    public function getJson ($asArray = false)
    {
        $this->json = json_decode(file_get_contents('php://input'), $asArray);

        // if (is_null($key)) {
            return $this->json;
        // } else {
        //  if (isset($this->json[$key])) {
        //      return $this->json[$key];
        //  } else {
        //      return null;
        //  }
        // }
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

            if (strtoupper($f) === 'ORDERBY') {
                $this->queryString['orderBy'][] = $v;
            } else {
                if (substr($f, -2) == '[]') {
                    $this->queryString['query'][][substr($f, 0, -2)][] = $v;
                } else {
                    $this->queryString['query'][][$f] = $v;
                }
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