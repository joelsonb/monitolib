<?php

namespace MonitoLib;

class Command
{
	const VERSION = '1.0.0';

	protected $options = [];
	private $used = [];

	// public function __construct ()
	// {
	// 	$option = new \MonitoLib\Command\Option;
	// 	$option->addShortName('h')
	// 		->addLongName('help')
	// 		->setDescription('Display this help and exit');
	// 	$this->options[] = $option;
	// }
	public function addOption ($option)
	{
		if (!$option instanceof \MonitoLib\Command\Option) {
			throw new \Exception("Invalid option!", 1);
		}

		if (in_array($option->getName(), $this->used)) {
			throw new \Exception("Already used option!", 1);
		}

		$this->used[] = $option->getName();

		$this->options[$option->getName()] = $option;

		if (!is_null($option->getAlias())) {
			if (in_array($option->getAlias(), $this->used)) {
				throw new \Exception("Already used option!", 1);
			}
			$this->options[$option->getAlias()] = $option;
		}

	}
	private function showHelp ()
	{
		echo 'MonitoLib\Command v' . self::VERSION . "\n";
		echo "Usage:\n";
		echo "\n";

		foreach ($this->options as $option) {
			echo $option->getName();

			if (!is_null($option->getAlias())) {
				echo ',' . $option->getAlias();
			}

			echo ' ' . $option->getDescription() . "\n";
		}

		exit;
	}
	public function process ()
	{
		if (!in_array('h', $this->options)) {
			$option = new \MonitoLib\Command\Option;
			$option->setName('h', 'help')
				->setDescription('Display this help and exit');
			$this->options[] = $option;
		}

		$request = \MonitoLib\Request::getInstance();

		if ($request->getParam(0) == '-h') {
			$this->showHelp();
		}

		// \MonitoLib\Dev::pre($request);
	}
}