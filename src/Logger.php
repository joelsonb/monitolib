<?php
namespace MonitoLib;

use \MonitoLib\App;

class Logger
{
	const VERSION = '1.0.0';

	private $breakLine = true;
	private $echoLog = false;
	private $outputFile;
	private $timeStamp = true;

	public function __construct ($outputFile = null)
	{
		if (is_null($outputFile)) {
			$this->outputFile = App::getLogPath() . 'general.log';
		} else {
			if (file_exists($outputFile)) {
				if (is_dir($outputFile)) {
					throw new \Exception("Arquivo de log inválido: $outputFile!", 1);
				} else {
					$this->outputFile = $outputFile;
				}
			} else {
				if (preg_match('/[a-zA-Z0-9.-_]/', $outputFile)) {
					$this->outputFile = App::getLogPath() . $outputFile;
				} else {
					throw new \Exception("Arquivo de log inválido: $outputFile!", 1);
				}
			}
		}
	}
	public function log ($text, $echo = false, $breakLine = true, $timeStamp = true)
	{
		if ($timeStamp) {
			$text = date('Y-m-d H:i:s') . ': ' . $text;
		}

		if ($breakLine) {
			$text .= "\r\n";
		}

		if ($this->echoLog || $echo) {
			echo $text;
		}
		
		if (!error_log($text, 3, $this->outputFile)) {
			throw new \Exception('Erro ao gravar log em: ' . $this->outputFile . "!\r\n");
		}
	}
	public function setOutputFile ($outputFile)
	{
		$this->outputFile = $outputFile;
		return $this;
	}
}