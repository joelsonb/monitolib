<?php
namespace MonitoLib;

class Controller
{
    const VERSION = '1.0.0';
    /**
    * 1.0.0 - 2019-04-17
    * first versioned
    */

	protected $request;
	protected $response;

	public function __construct ()
	{
		$this->request  = \MonitoLib\Request::getInstance();
		$this->response = \MonitoLib\Response::getInstance();
	}

	public function jsonToDto ($dto, $json)
	{
		if (!is_null($json)) {
			foreach ($json as $k => $v) {
				$method = 'set' . \MonitoLib\Functions::toUpperCamelCase($k);
				if (method_exists($dto, $method)) {
					$dto->$method($v);
				}
			}
		}

		return $dto;
	}
	public function toNull ($value)
	{
		return $value === '' ? null : $value;
	}
}