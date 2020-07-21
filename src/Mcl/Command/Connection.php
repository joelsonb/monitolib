<?php
namespace MonitoLib\Mcl\Command;

use \MonitoLib\Mcl\Command;
use \MonitoLib\Mcl\Module;
use \MonitoLib\Mcl\Option;
use \MonitoLib\Mcl\Param;

class Connection extends Module
{
    const VERSION = '1.0.0';

    protected $name = 'connection';
    protected $help = 'Configura as conexões da aplicação';

    public function setup()
    {
        // $command = [
        //     'name'   => 'add',
        //     'class'  => '\MonitoLib\Mcl\Cli\Connection',
        //     'method' => 'add',
        //     'help'   => 'Adiciona uma conexão',
        //     'params' => [
        //     ],
        //     'options' => [
        //     ]
        // ];


        // Adiciona um comando
        $command = $this->addCommand(
            new class extends Command
            {
                protected $name   = 'add';
                protected $class  = '\MonitoLib\Mcl\Cli\Connection';
                protected $method = 'add';
                protected $help   = 'Adiciona uma conexão';
            }
        );
        // Adiciona um parâmetro ao comando
        $command->addParam(
            new class extends Param
            {
                protected $name     = 'name';
                protected $help     = 'Nome da conexão com o banco de dados';
                // protected $required = true;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'env';
                protected $alias    = '';
                protected $help     = 'Ambiente da conexão. Default: prod';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'dbms';
                protected $alias    = '';
                protected $help     = 'Banco de dados da conexão';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'server';
                protected $alias    = '';
                protected $help     = 'Host do banco de dados da conexão';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'user';
                protected $alias    = '';
                protected $help     = 'Usuário do banco de dados da conexão';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'password';
                protected $alias    = '';
                protected $help     = 'Senha do banco de dados da conexão';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona um comando
        $this->addCommand(
            new class extends Command
            {
                protected $name   = 'list';
                protected $class  = '\MonitoLib\Mcl\Cli\Connection';
                protected $method = 'list';
                protected $help   = 'Lista as conexões disponíveis';
            }
        );

        // Adiciona um comando
        $this->addCommand(
            new class extends Command
            {
                protected $name   = 'edit';
                protected $class  = '\MonitoLib\Mcl\Cli\Lib';
                protected $method = 'install';
                protected $help   = 'Edita uma conexão';
            }
        );

        // Adiciona um comando
        $this->addCommand(
            new class extends Command
            {
                protected $name   = 'delete';
                protected $class  = '\MonitoLib\Mcl\Cli\Lib';
                protected $method = 'install';
                protected $help   = 'Deleta uma conexão';
            }
        );


        // $command = $this->addCommand(
        //     new class extends Command
        //     {
        //         protected $name   = 'import-table';
        //         protected $class  = '\MonitoMkr\cli\Mkr';
        //         protected $method = 'importTables';
        //         protected $help   = 'Lista as conexões configuradas';
        //     }
        // );

        // // Adiciona um parâmetro ao comando
        // $command->addParam(
        //     new class extends Param
        //     {
        //         protected $name     = 'connectionName';
        //         protected $help     = 'Nome da conexão com o banco de dados';
        //         protected $required = true;
        //     }
        // );

        // // Adiciona uma opção ao comando
        // $command->addOption(
        //     new class extends Option
        //     {
        //         protected $name     = 'tables';
        //         protected $alias    = 't';
        //         protected $help     = 'Tabelas que serão importadas. Se não informada, todas as tabelas da conexão serão importadas.';
        //         protected $required = true;
        //         protected $type     = 'string';
        //     }
        // );

        // $command->addOption(
        //     new class extends Option
        //     {
        //         protected $name  = 'columns';
        //         protected $alias = 'c';
        //         protected $help  = 'Colunas que serão importadas';
        //     }
        // );

        // /*
        // * generate
        // */
        // $command = $this->addCommand(
        //     new class extends Command
        //     {
        //         protected $name   = 'generate';
        //         protected $class  = '\MonitoMkr\cli\Mkr';
        //         protected $method = 'generate';
        //         protected $help   = 'Gera os objetos';
        //     }
        // );

        // // Adiciona um parâmetro ao comando
        // $command->addParam(
        //     new class extends Param
        //     {
        //         protected $name     = 'connectionName';
        //         protected $help     = 'Nome da conexão com o banco de dados';
        //         protected $required = true;
        //     }
        // );

        // // Adiciona uma opção ao comando
        // $command->addOption(
        //     new class extends Option
        //     {
        //         protected $name     = 'tables';
        //         protected $alias    = 't';
        //         protected $help     = 'Tabelas que serão importadas. Se não informada, todas as tabelas da conexão serão importadas.';
        //         protected $required = true;
        //         protected $type     = 'string';
        //     }
        // );
    }
}