<?php
/**
 * 1.0.0. - 2017-06-26
 * Inicial release
 */
namespace vendor\ldm;

class Request
{
	static private $instance;

	private $queryString = [];
	private $requestUri;
	private $post;

	private function __construct ()
	{
		$this->post = $_POST;
	}
	public static function getInstance ()
	{
		if (is_null(self::$instance))
		{
			return new \vendor\ldm\Request;
		}
		else
		{
			return self::$instance;
		}
	}
	public function getQueryString ($key = null)
	{
		if (is_null($key))
		{
			return $this->queryString;
		}
		else
		{
			if (isset($this->queryString[$key]))
			{
				return $this->queryString[$key];
			}
			else
			{
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

		foreach ($fields as $field)
		{
			$f = explode('=', $field);

			if (substr($f[0], -2) == '[]')
			{
				$this->queryString[substr($f[0], 0, -2)][] = $f[1];
			}
			else
			{
				$this->queryString[$f[0]] = $f[1];
			}
		}		
	}
	public function setRequestUri ($requestUri)
	{
		$this->requestUri = '/' . $requestUri;
	}
}