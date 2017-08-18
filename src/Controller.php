<?php
namespace vendor\ldm;

class Controller
{
	protected $request;

	public function __construct ()
	{
		$this->request = \vendor\ldm\Request::getInstance();
	}

	public function toArray ($objectList)
	{
		$results = array();

		foreach ($objectList as $object)
		{
			$result = array();
		    $class = new \ReflectionClass(get_class($object));
		    
		    foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
		    {
		        $methodName = $method->name;

		        if (strpos($methodName, 'get') === 0 && strlen($methodName) > 3)
		        {
		            $propertyName = lcfirst(substr($methodName, 3));
		            $value = $method->invoke($object);

		            if (is_object($value))
		            {
	                    $result[$propertyName] = $this->toArray($value);
		            }
		            else
		            {
		                $result[$propertyName] = $value;
		            }
		        }
		    }
		    $results[] = $result;
		}

    	return $results;

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
}