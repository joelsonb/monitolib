<?php
namespace MonitoLib;

class Logger
{
	const VERSION = '1.0.0';

	private $breakLine = true;
	private $echoLog = false;
	private $outputFile;
	private $timeStamp = true;

	public function __construct ()
	{
		$logDir = MONITO_SITE_PATH . 'log' . DIRECTORY_SEPARATOR;

		if (!file_exists($logDir)) {
			mkdir($logDir);
		}

		$this->outputFile = $logDir . 'general.log';
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