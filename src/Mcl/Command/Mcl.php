<?php
namespace MonitoMkr\Command;

use \MonitoLib\Mcl\Command;
use \MonitoLib\Mcl\Module;
use \MonitoLib\Mcl\Option;
use \MonitoLib\Mcl\Param;

class Mkr extends Module
{
    const VERSION = '1.0.0';

    protected $name = 'lib';
    protected $help = 'Cria aplicações da MonitoLib';

    public function setup()
    {
        // Adiciona um comando
        $this->addCommand(
            new class extends Command
            {
                protected $name   = 'list-connections';
                protected $class  = '\MonitoMkr\cli\Connection';
                protected $method = 'list';
                protected $help   = 'Lista as conexões configuradas';
            }
        );

        $command = $this->addCommand(
            new class extends Command
            {
                protected $name   = 'import-table';
                protected $class  = '\MonitoMkr\cli\Mkr';
                protected $method = 'importTables';
                protected $help   = 'Lista as conexões configuradas';
            }
        );

        // Adiciona um parâmetro ao comando
        $command->addParam(
            new class extends Param
            {
                protected $name     = 'connectionName';
                protected $help     = 'Nome da conexão com o banco de dados';
                protected $required = true;
            }
        );

        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'tables';
                protected $alias    = 't';
                protected $help     = 'Tabelas que serão importadas. Se não informada, todas as tabelas da conexão serão importadas.';
                protected $required = true;
                protected $type     = 'string';
            }
        );

        $command->addOption(
            new class extends Option
            {
                protected $name  = 'columns';
                protected $alias = 'c';
                protected $help  = 'Colunas que serão importadas';
            }
        );

        /*
        * generate
        */
        $command = $this->addCommand(
            new class extends Command
            {
                protected $name   = 'generate';
                protected $class  = '\MonitoMkr\cli\Mkr';
                protected $method = 'generate';
                protected $help   = 'Gera os objetos';
            }
        );

        // Adiciona um parâmetro ao comando
        $command->addParam(
            new class extends Param
            {
                protected $name     = 'connectionName';
                protected $help     = 'Nome da conexão com o banco de dados';
                protected $required = true;
            }
        );

        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'tables';
                protected $alias    = 't';
                protected $help     = 'Tabelas que serão importadas. Se não informada, todas as tabelas da conexão serão importadas.';
                protected $required = true;
                protected $type     = 'string';
            }
        );
    }
}