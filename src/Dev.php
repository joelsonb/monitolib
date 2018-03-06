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
	public static function e ($s = 'exited')
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
	public static function vd ($a, $e = false)
	{
		echo self::isCli() ? '' : '<pre>';
		var_dump($a);
	
		if ($e) {
			exit;
		} else {
			echo self::isCli() ? "\n" : '</pre>';
		}
	}
	public static function vde ($a)
	{
		self::vd($a, true);
	}
}