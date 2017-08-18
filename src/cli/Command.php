<?php
namespace MonitoFeo\cli;

class Command
{
	const VERSION = '1.0.0';
	private $argv;
	private $command;

	public function __construct ($command, $argv)
	{
		if (is_null($command) || !method_exists($this, $command))
		{
			$this->listCommands();
		}

		$this->command = $command;
		$this->argv    = $argv;
	}
	private function listCommands () {
		echo 'Command v' . self::VERSION . PHP_EOL;
		echo 'Available commands:' . PHP_EOL;
		echo 'create: creates classes' . PHP_EOL;
		exit;
	}
	// private function create ($argv)
	private function create ($argv)
	{
		$create = new \MonitoFeo\cli\Commands\Create($argv);
		$create->run();
		exit;


		$argv = getopt("f:hp:");
		\vendor\ldm\Dev::pre($argv);

		$arg1 = isset($argv[0]) ? $argv[0] : null;

		\vendor\ldm\Dev::pre($argv);

		$connector  = \vendor\ldm\Connector::getInstance();
		$connection = $connector->getConnection('tms');

		if (is_null($connection))
		{
			throw new \Exception("Connection not configured!");
		}

		$dbms  = $connector->getDbms();
		$class = '\vendor\ldm\cli\\' . $dbms;

		$class = new $class($connector);
		$tables = $class->listTablesAndColumns();

		$r = "{\r\n";
		$t = null;

		foreach ($tables as $table)
		{
			$c = '      "' . $table['COLUMN_NAME'] . "\": \"" . \vendor\ldm\Functions::toLowerCamelCase($table['COLUMN_NAME']) . "\",\n";

			if ($t != $table['TABLE_NAME'])
			{
				if (!is_null($t))
				{
					$r = substr($r, 0, -2) . "\n    }\n  },\n";
				}

				$c = "  \"" . $table['TABLE_NAME'] . "\": {\n"
					. "    \"className\": \"" . \vendor\ldm\Functions::toUpperCamelCase(\vendor\ldm\Functions::toSingular($table['TABLE_NAME'])) . "\",\n"
					. "    \"fields\": {"
					. "\n" . $c;
			}

			$t = $table['TABLE_NAME'];
			$r .= $c;
		}

		$r = substr($r, 0, -2) . "\n    }\n  }\n}";

		file_put_contents(LDM_CONFIG_PATH . 'tms.json', $r);

		// $dao = new \vendor\ldm\cli\Dao;
		// $dao->run();

		// if (in_array($arg1, ['all', 'model']))
		// {
		// 	// lib\dm\cli\Model;
		// }
	}
	private function createController ()
	{
		$tables = json_decode(file_get_contents(LDM_CONFIG_PATH . 'tms.json'));

		foreach ($tables as $tk => $tv)
		{
			$c = '_' . $tv->className;

			$f = "<?php\n"
				. "namespace app\\controller;\n"
				. "\n"
				. "class $c extends \\vendor\\ldm\\Controller\n"
				. "{\n"
				. "\tconst VERSION = '1.0.0';\n"
				. "\t/**\n"
				. "\t * 1.0.0 - " . date('Y-m-d') . "\n"
				. "\t * initial release\n"
				. "\t */\n"
				. "\tpublic function get (\$request, \$keys = null)\n"
				. "\t{\n"
				. "\t\t\n"
				. "\t}\n"
				. "\tpublic function create (\$request)\n"
				. "\t{\n"
				. "\t\t\n"
				. "\t}\n"
				. "\tpublic function delete (\$request)\n"
				. "\t{\n"
				. "\t\t\n"
				. "\t}\n"
				. "\tpublic function update (\$request)\n"
				. "\t{\n"
				. "\t\t\n"
				. "\t}\n"
				. '}';

			file_put_contents(LDM_CONTROLLER_PATH . $c . '.php', $f);
		}
	}
	private function createDao ()
	{
		$connector  = \vendor\ldm\Connector::getInstance();
		$connection = $connector->getConnection('tms');

		if (is_null($connection))
		{
			throw new \Exception("Connection not configured!");
		}

		$dbms  = $connector->getDbms();
		$class = '\vendor\ldm\cli\\' . $dbms;

		$class = new $class($connector);
		$tables = $class->listTablesAndColumns();

		$t = null;

		foreach ($tables as $table)
		{
			if ($t != $table['TABLE_NAME'])
			{
				$c = '_' . \vendor\ldm\Functions::toUpperCamelCase($table['TABLE_NAME']);

				$f = "<?php\n"
					. "namespace app\\dao;\n"
					. "\n"
					. "class $c extends \\vendor\\ldm\\Database\\MySQL\\Dao\n"
					. "{\n"
					. "\tconst VERSION = '1.0.0';\n"
					. "\t/**\n"
					. "\t * 1.0.0 - " . date('Y-m-d') . "\n"
					. "\t * initial release\n"
					. "\t */\n"
					. '}';

				file_put_contents(LDM_DAO_PATH . $c . '.php', $f);
			}
		}
	}
	private function createDto ()
	{
		// $connector  = \vendor\ldm\Connector::getInstance();
		// $connection = $connector->getConnection('tms');

		// if (is_null($connection))
		// {
		// 	throw new \Exception("Connection not configured!");
		// }

		// $dbms  = $connector->getDbms();
		// $class = '\vendor\ldm\cli\\' . $dbms;

		// $class = new $class($connector);
		// $tables = $class->listTablesAndColumns();

		$tables = json_decode(file_get_contents(LDM_CONFIG_PATH . 'tms.json'));
		// \vendor\ldm\Dev::pre($tables);

		foreach ($tables as $tk => $tv)
		{
			$c = '_' . $tv->className;
			$p = '';
			$g = '';
			$s = '';

			foreach ($tv->fields as $field)
			{
				$cou = \vendor\ldm\Functions::toUpperCamelCase($field);
				$col = \vendor\ldm\Functions::toLowerCamelCase($field);
				$get = 'get' . $cou;
				$set = 'set' . $cou;

				$p .= "\tprivate \$$col;\n";
				$g .= "\t/**\n"
					. "\t* $get()\n"
					. "\t*\n"
					. "\t* @return \$$col\n"
					. "\t*/\n"
					. "\tpublic function $get ()\n"
					. "\t{\n"
					. "\t\treturn \$this->$col;\n"
					. "\t}\n";
				$s .= "\t/**\n"
					. "\t* $set()\n"
					. "\t*\n"
					. "\t* @return \$this\n"
					. "\t*/\n"
					. "\tpublic function $set (\$$col)\n"
					. "\t{\n"
					. "\t\t\$this->$col = \$$col;\n"
					. "\t\treturn \$this;\n"
					. "\t}\n";
			}

			$f = "<?php\n"
				. "namespace app\\dto;\n"
				. "\n"
				. "class $c\n"
				. "{\n"
				. "\tconst VERSION = '1.0.0';\n"
				. "\t/**\n"
				. "\t * 1.0.0 - " . date('Y-m-d') . "\n"
				. "\t * initial release\n"
				. "\t */\n"
				. $p
				. $g
				. $s
				. '}';

			file_put_contents(LDM_DTO_PATH . $c . '.php', $f);
		}
	}
	private function createModel ()
	{
		$connector  = \vendor\ldm\Connector::getInstance();
		$connection = $connector->getConnection('tms');

		if (is_null($connection))
		{
			throw new \Exception("Connection not configured!");
		}

		$dbms  = $connector->getDbms();
		$class = '\vendor\ldm\cli\\' . $dbms;

		$class = new $class($connector);
		$tables = $class->listTables();

		$modelDefault = new \vendor\ldm\Database\Mysql\Model;

		foreach ($tables as $t)
		{
			$fields = $class->listColumns($t['TABLE_NAME']);

			$output = '';
			$keys = '';

			foreach ($fields as $field)
			{
				$cl = strlen($field->name);
				$ci = $cl;//$bi + $cl;
				$it = floor($ci / 4);
				$is = $ci % 4;
				$li = "\t\t\t";//$util->indent($it, $is);

				$output .= "\t\t'" . $field->name . "' => [\n";
				
				if ($field->isAuto)
				{
					$output .= "$li'auto' => true,\n";
				}

				if ($field->type == 'char')
				{
					if ($field->charset != $modelDefault->getDefaults('charset'))
					{
						$output .= "$li'charset'   => '{$field->charset}',\n";
					}
					if ($field->collation != $modelDefault->getDefaults('collation'))
					{
						$output .= "$li'collation' => '{$field->collation}',\n";
					}
				}
				if (!is_null($field->defaultValue))
				{
					//if ()
					//{
					//	
					//}

					$output .= "$li'defaultValue' => '{$field->defaultValue}',\n";
				}
				if (!is_null($field->label))
				{
					$output .= "$li'label' => '{$field->label}',\n";
				}
				if (!is_null($field->maxLength) && $field->maxLength > 0)
				{
					$output .= "$li'maxLength' => {$field->maxLength},\n";
				}
				if ($field->isPrimary)
				{
					$keys .= "'" . $field->name . "',";
					$output .= "$li'primary' => true,\n";
				}
				if ($modelDefault->getDefaults('required') != $field->isRequired)
				{
					$output .= "$li'required' => true,\n";
				}
				if ($modelDefault->getDefaults('type') != $field->type)
				{
					$output .= "$li'type' => '{$field->type}',\n";
				}
				if ($modelDefault->getDefaults('unique') != $field->isUnique)
				{
					$output .= "$li'unique' => {$field->isUnique},\n";
				}
				if ($modelDefault->getDefaults('unsigned') != $field->isUnsigned)
				{
					$output .= "$li'unsigned' => {$field->isUnsigned},\n";
				}
				





				//'maxValue'         => 0,
				//'minValue'         => 0,
				//'numericPrecision' => null,
				//'numericScale'     => null,

				$output .= "\t\t],\n";
			}

			$keys = substr($keys, 0, -1);

			$c = '_' . \vendor\ldm\Functions::toUpperCamelCase($t['TABLE_NAME']);
			$f = "<?php\n"
				// . $this->renderComments()
				. "\n"
				. "namespace app\\model;\n"
				. "\n"
				// TODO: checks dbms to extends to right class
				. "class $c extends \\vendor\\ldm\\Database\\MySQL\\Model\n"
				. "{\n"
				. "\tconst VERSION = '1.0.0';\n"
				. "\n"
				. "\tprotected \$tableName = '" . $t['TABLE_NAME'] . "';\n"
				. "\n"
				. "\tprotected \$fields = [\n"
				. $output
				. "\t];\n"
				. "\n"
				. "\tprotected \$keys = [$keys];\n"
				. "}"
				;
			file_put_contents(LDM_MODEL_PATH . $c . '.php', $f);
		}
	}
	public function run ()
	{
		$this->{$this->command}($this->argv);
	}
}