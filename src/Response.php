<?php
/**
 * 1.0.0. - 2018-04-27
 * Inicial release
 */
namespace MonitoLib;

use \MonitoLib\Functions;

class Response
{
	static private $instance;

	private $json = [];
	private $httpResponseCode = 200;

	private function __construct ()
	{
	}
	public function __toString ()
	{
		return $this->render();
	}
	public static function getInstance ()
	{
		if (!isset(self::$instance)) {
			self::$instance = new \MonitoLib\Response;
		}

		return self::$instance;
	}
	private function render ()
	{
		http_response_code($this->httpResponseCode);

		if (count($this->json) > 0) {
			return json_encode($this->json);
		}
	}
	public function setData ($data)
	{
		$this->json['data'] = $data;
		return $this;
	}
	public function setDataset ($dataset)
	{
		$this->json = Functions::arrayMergeRecursive($this->json, $dataset);
		return $this;
	}
	public function setHttpResponseCode ($httpResponseCode)
	{
		$this->httpResponseCode = $httpResponseCode;
		return $this;
	}
	public function setJson ($json)
	{
		$this->json = $json;
		return $this;
	}
	public function setMessage ($message)
	{
		$this->json['message'] = $message;
		return $this;
	}
	public function setProperty ($property, $value)
	{
		$this->json[$property] = is_null($value) ? '' : $value;
		return $this;
	}
}