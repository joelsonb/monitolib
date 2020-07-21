<?php
namespace MonitoLib\Mcl\Cli;

use \MonitoLib\App;
use \MonitoLib\Functions;
use \MonitoLib\Exception\BadRequest;
use \MonitoLib\Exception\NotFound;

class Connection extends \MonitoLib\Mcl\Controller
{
    public function add()
    {
        $name     = $this->request->getParam('name')->getValue();
        $env      = $this->request->getOption('env')->getValue() ?? 'prod';
        $dbms     = $this->request->getOption('dbms')->getValue();
        $server   = $this->request->getOption('server')->getValue();
        $user     = $this->request->getOption('user')->getValue();
        $password = $this->request->getOption('password')->getValue();
        // $db       = $this->request->getOption('db')->getValue();

        if (is_null($name)) {
            $name = $this->question('Informe o nome da conexao: ');
        }

        // \MonitoLib\Dev::pre($name);

        $file = App::getConfigPath() . 'database.json';

        $connections = [];

        // Verifica se o arquivo de configuração de banco de dados já existe
        if (file_exists($file)) {
            $connections = json_decode(file_get_contents($file), true);
        }

        if (!empty($connections)) {

        }

        $password = Functions::encrypt($password, $name . $env);

        $new = [
            'dbms'     => $dbms,
            'server'   => $server,
            'user'     => $user,
            'password' => $password,
        ];

        $connections[$name][$env] = $new;

        // "winthor":{
        //     "prod":{
        //       "dbms":"Oracle",
        //       "server":"//152.67.52.251/WINT_gru1rs.subnetferimport.lanferimport.oraclevcn.com",
        //       "user":"FERIMPORTS",
        //       "password":"F3R1MP0RTS"
        //     }
        // },

        if (!file_put_contents($file, json_encode($connections, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            \MonitoLib\Dev::pre($connections);
        }

        echo "cun\n";
    }

    public function list()
    {
        $file = App::getConfigPath() . 'database.json';

        if (!file_exists($file)) {
            throw new NotFound("O arquivo $file não foi encontrado!");
        }

        if (file_exists($file)) {
            $connections = json_decode(file_get_contents($file));

            $connections = json_decode(file_get_contents($file));

            if (is_null($connections)) {
                throw new InternalError("O arquivo $file é inválido!");
            }

            foreach ($connections as $name => $p) {
                echo $name . ' ' . $p->dbms . "\n";
            }
        }
    }
}
