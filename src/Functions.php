<?php
namespace MonitoLib;

class Functions
{
	function jLibErrorHandler ()
	{
		$error = error_get_last();
	
		if ($error['type'] == 1)
		{
			throw new \Exception($error['message']);
		}
	}
	/**
	 * Merges an array recursively
	 * Mescla um array recursivamente
	 *
	 * @param array $a1 Default array
	 * @param array $a2 Input array
	 *
	 * @return array
	 */
	public static function ArrayMergeRecursive ($a1, $a2)
	{
		$newArray = $a1;
		if (is_array($a2))
		{
			foreach ($a2 as $k => $v)
			{
				if (isset($a1[$k]))
				{
					if (is_array($a1[$k]))
					{
						$newArray[$k] = self::ArrayMergeRecursive($a1[$k], $a2[$k]);
					}
					else
					{
						$newArray[$k] = $a2[$k];
					}
				}
				else
				{
					$newArray[$k] = $a2[$k];
				}
			}
		}
		return $newArray;
	}
	/*
	 * Obtém informações do certificado
	 * 
	 * @param string $file Caminho completo para o arquivo .pem do certificado
	 * 
	 * @return array
	 */	
	function jLibcertificate_expiration ($file)
	{
		$content = openssl_x509_read(file_get_contents($file));
		$parsed  = openssl_x509_parse($content);
	
		// Extrai a data do certificado
		$year  = substr($parsed['validTo'], 0, 2);
		$month = substr($parsed['validTo'], 2, 2);
		$day   = substr($parsed['validTo'], 4, 2);
	
		$date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
		$date = date('Y-m-d', $parsed['validTo_time_t']);
		$date = $parsed['validTo_time_t'];
	
		return $date;
	}
	/**
	 * jLibchange_index
	 *
	 * @param &$e
	 */
	function jLibchange_index (array $array, array $new_index)
	{
		$r = array();
	
		foreach ($array as $k => $v)
		{
			if (isset($new_index[$k]))
			{
				$r[$new_index[$k]] = $v;
			}
			else
			{
				$r[$k] = $v;
			}
		}
	
		return $r;
	}
	/**
	 * jLibhex_to_float
	 *
	 * @param string $hex
	 */
	public static function hexToFloat ($hex)
	{
		$hex   = str_replace('#', '', $hex);
		$color = array();
	
		if (strlen($hex) == 3)
		{
			$color[] = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1)) / 255;
			$color[] = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1)) / 255;
			$color[] = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1)) / 255;
		}
		else if (strlen($hex) == 6)
		{
			$color[] = hexdec(substr($hex, 0, 2)) / 255;
			$color[] = hexdec(substr($hex, 2, 2)) / 255;
			$color[] = hexdec(substr($hex, 4, 2)) / 255;
		}
	
		return $color;
	}
	/**
	 * jLibisset
	 *
	 * @param &$e
	 */
	public static function isset(&$variable, $default = '')
	{
		return isset($variable) ? $variable : $default;
	}
	public static function postValue ($variable, $default = null)
	{
		if (!isset($_POST[$variable]))
		{
			throw new \Exception("Post field '$variable' not found!");
		}

		$value = $_POST[$variable];

		if ($value == '')
		{
			return $default;	
		}

		return $value;
	}	
	/**
	 * jLibisset
	 *
	 * @param &$e
	 */
	function jLibpost(&$element, $compareTo = '', $defaulValue = NULL)
	{
		return isset($element) ? $element == $compareTo ? $defaulValue : utf8_encode($element) : $defaulValue;
	}
	/**
	 * jLibisset
	 *
	 * @param $v
	 * @param $n
	 */
	static function sqlWrapper ($value, $nullify = true)
	{
		if ($value == '')
		{
			if (!$nullify)
			{
				$r = "''";
			}
			else
			{
				$r = 'NULL';
			}
		}
		else
		{
			$value = str_replace("'", "''", $value);
			$r = "'$value'";
		}

		return $r;
	}
	function jLibstrtolower_utf8 ($string)
	{
		return utf8_encode(strtolower(utf8_decode($string)));
	}
	function jLibstrtoupper_utf8 ($string)
	{
		//return utf8_encode(strtoupper(utf8_decode($string)));
		return mb_strtoupper($string, 'UTF-8');
	}
	/**
	* Função de redirecionamento de página
	*
	* @param $url Url para onde a página será redirecionada
	*
	* @return void
	*/
	static function urlRedirect ($url)
	{
		if (!headers_sent())
		{
			header("Location: $url");
		}
		else
		{
			echo "<meta HTTP-EQUIV='Refresh' CONTENT='0;URL=$url'>";
			echo "<script>top.document.location='$url';</script>";
		}
	
		exit;
	}
	/**
	* Valida string xml
	*
	* @param $xml XML string
	*
	* @return boolean
	*/
	function jLibvalidate_xml ($xml)
	{
		libxml_use_internal_errors(1);
	
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->loadXML($xml);
	
		$errors = libxml_get_errors();
	
		if (empty($errors))
		{
			return true;
		}
	
		$error = $errors[0];
	
		if ($error->level < 3)
		{
			return true;
		}
	
		return false;
	}
	function jLibFormatBytes($v)
	{
		$s = 'B';
		if($v > 1024)
		{
			$s = 'KB';
			$v = $v / 1024;
		}
		if($v > 1024)
		{
			$s = 'MB';
			$v = $v / 1024;
		}
		if($v > 1024)
		{
			$s = 'GB';
			$v = $v / 1024;
		}
	
		return jLibFormatDecimal($v) . $s;
	}
	/**
	 * Formata CEP
	 * @param string $value
	 * @return string
	 */
	function jLibFormatCep ($value)
	{
		$pattern = '/(\d{5})(\d{3})/';
		return preg_replace($pattern, '$1-$2', $value); 
	}
	/**
	 * Formata Chave de Acesso
	 * @param string $value
	 * @return string
	 */
	function jLibFormatchave_acesso($value)
	{
		$pattern = '/(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})/';
		return preg_replace($pattern, '$1 $2 $3 $4 $5 $6 $7 $8 $9 $10 $11', $value); 
	
		$pattern = '/(\d{2})(\d{2})(\d{2})(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})(\d{2})(\d{3})(\d{3})(\d{3})(\d{3})(\d{3})(\d{3})(\d{3})(\d{1})/';
		return preg_replace($pattern, '$1-$2.$3-$4.$5.$6/$7-$8-$9-$10-$11.$12.$13-$14.$15.$16-$17', $value); 
	}
	/**
	 * Formata CNPJ
	 * @param string $n
	 * @return string
	 */
	function jLibFormatCnpj($n)
	{
		$n = jLibRemoveNonNumbers($n);
	
		if ($n != '')
		{
			return substr($n, 0, 2) . '.' . substr($n, 2, 3) . '.' . substr($n, 5, 3) . '/' . substr($n, 8, 4) . '-' . substr($n, 12, 2);
		}
	}
	/**
	 * Formata CPF
	 * @param string $n
	 * @return string
	 */
	function jLibFormatCpf($n)
	{
		$n = jLibRemoveNonNumbers($n);
		
		if ($n != '')
		{
			return substr($n, 0, 3) . '.' . substr($n, 3, 3) . '.' . substr($n, 6, 3) . '-' . substr($n, 9, 2);
		}
	}
	/**
	 * Formata CPF/CNPJ
	 * @param string $n
	 * @return string
	 */
	function jLibFormatCpfCnpj ($n)
	{
		$n = jLibRemoveNonNumbers($n);
		$r = NULL;
		if (strlen($n) > 0)
		{
			if (strlen($n) <= 11)
			{
				$r = jLibFormatCpf($n);
			}
			else
			{
				$r = jLibFormatCnpj($n);
			}
		}
		return $r;
	}
	
	
	/**
	 * Formata Data
	 * @param string $value
	 * @return string
	 */
	function date ($value)
	{
		// TODO: validar data
		return $value != '' ? date('d/m/Y', strtotime($value)) : '';
	}
	function jLibLogger ($logFilePath, $message, $newLine = true)
	{
		if ($newLine)
		{
			$message = date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL;
		}
	
		error_log($message, 3, $logFilePath);
	}
	/**
	 * Remove não números de uma string
	 * @param string $n
	 * @return string
	 */
	public static function removeNonNumbers ($str)
	{
		$er = '/[^\d]/';
		return preg_replace($er, '', $str);
	}
	/**
	 * Formata telefone
	 * @param string $value
	 * @return string
	 */
	function jLibFormatTelefone ($value)
	{
		$value = jLibRemoveNonNumbers($value);
		
	
		if (substr($value, 0, 4) == '0800')
		{
			$len     = strlen($value);
			$pattern = '/(\d{4})(\d*)(\d{4})/';
			$value   = preg_replace($pattern, '$1-$2-$3', $value);
		}
		else
		{
			$value = +$value;
			$len   = strlen($value);
			//
			//if ($len >= 7 and $len <= 9)
			//{
			//	$pattern = '/(\d*)(\d{4})/';
			//	$value   = preg_replace($pattern, '$1-$2', $value);
			//}
			//elseif ($len >= 10 and $len <= 11)
			//{
			//	$pattern = '/(\d{2})(\d*)(\d{4})/';
			//	$value   = preg_replace($pattern, '($1) $2-$3', $value);
			//}
	
			switch ($len)
			{
				case 7:
				case 8:
				case 9:
					$pattern = '/(\d*)(\d{4})/';
					$value   = preg_replace($pattern, '$1-$2', $value);
					break;
				case 10:
				case 11:
					$pattern = '/(\d{2})(\d*)(\d{4})/';
					$value   = preg_replace($pattern, '($1) $2-$3', $value);
					break;
			}
		}
	
		return $value;
	}
	/**
	 * Formata value
	 * @param float $value
	 * @param int $decimals Default: 2
	 * @return string
	 */
	function jLibFormatDecimal($value, $decimals = 2)
	{
		if (is_numeric($value))
		{
			$value = number_format($value, $decimals, ',', '.');
		}
	
		return $value;
	}
	static function convertToUrl ($url)
	{
		$url = strtolower($url);
		$url = self::removeAccents($url);
		$url = preg_replace('/[^a-z0-9\-]/', '-', $url);
		$url = preg_replace('/-{2,}/', '-', $url);
		$url = preg_replace('/-$/', '', $url);
		
		return $url;
	}
	static function friendlyDate ($time)
	{
		$diff = time() - $time;

		$r = 'há ';

		if ($diff <= 60)
		{
			$r = $diff . ' segundo' . ($diff > 1 ? 's' : '');
		}
		elseif ($diff > 60 && $diff <= 3600)
		{
			$diff = round($diff / 60);
			$r = $diff . ' minuto' . ($diff > 1 ? 's' : '');
		}
		elseif ($diff > 3600 && $diff <= 86400)
		{
			$diff = round($diff / 3600);
			$r = $diff . ' hora' . ($diff > 1 ? 's' : '');
		}
		elseif ($diff > 86400 && $diff <= 31536000)
		{
			$diff = round($diff / 86400);
			$r = $diff . ' dia' . ($diff > 1 ? 's' : '');
		}
		else
		{
			$diff = floor($diff / 31536000);
			$r = 'mais de ' . $diff . ' ano' . ($diff > 1 ? 's' : '');
		}

		return $r;
	}
	static function post ($fieldName)
	{
		$value = NULL;

		if (isset($_POST[$fieldName]))
		{
			$value = $_POST[$fieldName];
		}

		return $value;
	}
	static function removeAccents ($str)
	{
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'ss', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
		return str_replace($a, $b, $str);
	}
	static function spaceRightPad ($string, $size)
	{
		return str_pad($string, $size, ' ', STR_PAD_RIGHT);
	}
	static function stringToFloat ($value)
	{
		$value = str_replace('.', '', $value);
		$value = str_replace(',', '.', $value);

		return $value;
	}
	static function toInt ($number)
	{
		if ($number != '')
		{
			return +$number;
		}
	}
	static public function toLowerCamelCase ($string)
	{
		$frag  = explode('_', $string);
		$count = count($frag);
		$newString = $frag[0];
		
		for ($i = 1; $i < $count; $i++)
		{
			$newString .= ucfirst($frag[$i]);
		}
		
		return $newString;
	}
	static public function toUpperCamelCase ($string)
	{
		return ucfirst(self::toLowerCamelCase($string));
	}
	static public function toSingular ($string)
	{
		if (strtolower($string) == 'status')
		{
			return $string;
		}
		if (preg_match('/ens$/', $string))
		{
			$string = substr($string, 0, -3) . 'em';
		}
		if (preg_match('/ies$/', $string))
		{
			$string = substr($string, 0, -3) . 'y';
		}
		if (preg_match('/oes$/', $string))
		{
			$string = substr($string, 0, -3) . 'ao';
		}
		//if (preg_match('/res$/', $string))
		//{
		//	$string = substr($string, 0, -2);
		//}
		if (preg_match('/tchs$/', $string))
		{
			$string = substr($string, 0, -1);
		}
		if (preg_match('/[acdeiouglmnprt]s$/', $string))
		{
			$string = substr($string, 0, -1);
		}

		return $string;
	}
	static function upperSeparator ($string, $separator)
	{
		$len = strlen($string);
		$res = '';

		for ($i = 0; $i < $len; $i++)
		{
			$crt = $string[$i];
			$lwr = strtolower($crt);

			if ($crt === $lwr)
			{
				$res .= $crt;
			}
			else
			{
				$res .= $separator . $lwr;
			}
		}

		return $res;
	}
	public static function urlToMethod ($url)
	{
		$af = explode('-', $url);
		$ra = NULL;

		if (count($af) > 0)
		{
			$ra = $af[0];
			array_shift($af);

			foreach ($af as $f)
			{
				$ra .= ucfirst($f);
			}
		}

		return $ra;
	}
	/**
	 * Preenche um número com zeros à esquerda
	 * @param int $n Número que será preenchido 
	 * @param int $t Tamanho do preenchimento
	 * @return string
	 */
	static function zeroLeftPad ($number, $size)
	{
		if (is_numeric($number))
		{
			return str_pad($number, $size, '0', STR_PAD_LEFT);
		}
		else
		{
			return $number;
		}
	}
}