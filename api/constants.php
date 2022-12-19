<?php
//Habilita as mensagens de erro do PHP
ini_set('display_errors','1');
ini_set('display_startup_errors', '1');
error_reporting(E_ERROR);

//Define as constantes do Servidor Dev
define("HOST", 'convicti-mysql-1');
define("BD", 'api_convicti');
define("USER", 'root');
define("PASS", 'root');

//Seta o caminho principal para acessar a api
define("DS", DIRECTORY_SEPARATOR);
define("DIR_APP", __DIR__);
define("DIR_PROJETO", '/api');


//Faz a inclusão do autoload
if(file_exists('autoload.php')){
    include 'autoload.php';
} else {
    echo 'Erro ao incluir as constantes';exit;
}