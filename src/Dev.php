<?php
/**
 * Dev
 * 
 * Development tools
 * @author Joelson B <joelsonb@msn.com>
 * @copyright Copyright &copy; 2015 - 2018
 * 
 * @package \MonitoLib
 */
namespace MonitoLib;

class Dev
{
	private static function isCli () {
		return PHP_SAPI == 'cli' ? true : false;
	}
	public static function e ($s, $breakLine = false)
	{
		echo $s;

		if ($breakLine) {
			if (PHP_SAPI === 'cli') {
				echo PHP_EOL;
			} else {
				echo '<br />';
			}
		}
	}
	public static function ee ($s = 'exited')
	{
		echo $s;
		exit;
	}
	public static function lme ($class)
	{
		$methods = get_class_methods($class);
		sort($methods);
		self::pre($methods);
	}
	public static function pr ($a, $e = false)
	{
		echo self::isCli() ? '' : '<pre>';
		print_r($a);
	
		if ($e) {
			exit;
		} else {
			echo self::isCli() ? "\n" : '</pre>';
		}
	}
	public static function pre ($a)
	{
		self::pr($a, true);
	}
	private static function sliceArrayDepth ($array, $depth = 0)
	{
	    foreach ($array as $key => $value) {
	        if (is_array($value)) {
	            if ($depth > 0) {
	                $array[$key] = self::sliceArrayDepth($value, $depth - 1);
	            } else {
	                unset($array[$key]);
	            }
	        }
	    }

	    return $array;
	}
	public static function vd ($a, $depth = 0, $e = false)
	{
		if ($depth > 0) {
			$a = self::sliceArrayDepth($a, $depth = 0);
		}

		echo self::isCli() ? '' : '<pre>';
		var_dump($a);
	
		if ($e) {
			exit;
		} else {
			echo self::isCli() ? "\n" : '</pre>';
		}
	}
	public static function vde ($a, $depth = 0)
	{
		self::vd($a, $depth, true);
	}
}