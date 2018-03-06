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
	private $queryString = [];
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
		// 	if (isset($this->json[$key])) {
		// 		return $this->json[$key];
		// 	} else {
		// 		return null;
		// 	}
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
			$f = explode('=', $field);

			if (substr($f[0], -2) == '[]') {
				$this->queryString[substr($f[0], 0, -2)][] = $f[1];
			} else {
				$this->queryString[$f[0]] = $f[1];
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