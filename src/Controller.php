<?php
namespace MonitoLib;

class Controller
{
	protected $request;
	protected $response;

	public function __construct ()
	{
		$this->request = \MonitoLib\Request::getInstance();
		$this->response = \MonitoLib\Response::getInstance();
	}

	public function jsonToDto ($dto, $json)
	{
		// $json = json_decode($json);

		foreach ($json as $k => $v) {
			$method = 'set' . \MonitoLib\Functions::toUpperCamelCase($k);
			if (method_exists($dto, $method)) {
				$dto->$method($v);
			}
		}

		return $dto;
	}
	public function toArray ($objectList)
	{
		// TODO: this is bizarre! do it better!
		$objectListOk = [];

		if (is_array($objectList)) {
			$objectListOk = $objectList;
		} else {
			$objectListOk[] = $objectList;
		}
		$results = array();

		foreach ($objectListOk as $object) {
			$result = array();
		    $class = new \ReflectionClass(get_class($object));
		    
		    foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
		        $methodName = $method->name;

		        if (strpos($methodName, 'get') === 0 && strlen($methodName) > 3) {
		            $propertyName = lcfirst(substr($methodName, 3));
		            $value = $method->invoke($object);

		            if (is_object($value)) {
	                    $result[$propertyName] = $this->toArray($value);
		            } else {
		                $result[$propertyName] = $value;
		            }
		        }
		    }
		    $results[] = $result;
		}
		if (is_array($objectList)) {
	    	return $results;
		} else {
			return $results[0];
		}

		// if (is_object($objectList))
		// {
		// 	return (array)$objectList;
		// }
		// else
		// {
		// 	$arrayList = [];

		// 	foreach ($objectList as $object)
		// 	{
		// 		$arrayList[] = (array)$object;
		// 	}

		// 	return $arrayList;
		// }
		// $a = array();

		foreach ($objectList as $k => $v)
		{
			$a[$k] = (is_array($v) || is_object($v)) ? $this->toArray($v): $v; 
			return $a;
		}
	}
	public function validateJson (&$json, $schemaPath, $coerce = true)
	{
		if (!file_exists($schemaPath)) {
			throw new \MonitoLib\Exception\InternalError("Schema $schemaPath not found!");
		}

		$validator = new \JsonSchema\Validator;
		$validator->validate($json, json_decode(file_get_contents($schemaPath)), \JsonSchema\Constraints\Constraint::CHECK_MODE_COERCE_TYPES | \JsonSchema\Constraints\Constraint::CHECK_MODE_APPLY_DEFAULTS);

		if (!$validator->isValid()) {
			$errors = [];

			foreach ($validator->getErrors() as $error) {
				\MonitoLib\Dev::pre($error);
				$errors[] = sprintf("[%s] %s\n", $error['property'], $error['message']);
			}

			return $errors;
		}
	}
}