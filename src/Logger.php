<?php
namespace MonitoLib;

class Logger
{
	private $breakLine = true;
	private $echoLog = false;
	private $outputFile;
	private $timeStamp = true;

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