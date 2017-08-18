<?php
/**
 * Dev
 * 
 * Development tools
 * @author Joelson Batista <joelsonb@msn.com>
 * @since 2015-11-03
 * @copyright Copyright &copy; 2015
 * 
 * @package \jLib\lib
 */
namespace vendor\ldm;

class Dev
{
	public static function e ($s = 'exited')
	{
		echo $s;
		exit;
	}
	public static function pr ($a, $e = false)
	{
		echo '<pre>';print_r($a);
	
		if ($e)
		{
			exit;
		}
		else
		{
			echo '</pre>';
		}
	}
	public static function pre ($a)
	{
		self::pr($a, true);
	}
	public static function vd ($a, $e = false)
	{
		echo '<pre>';var_dump($a);
	
		if ($e)
		{
			exit;
		}
		else
		{
			echo '</pre>';
		}
	}
	public static function vde ($a)
	{
		self::vd($a, true);
	}
}