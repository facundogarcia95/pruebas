<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . "/constantes.php";
require_once BASE_PATH . '/vendor/autoload.php';

/**
 * La inicializacion de los modelos va aca
 */
$config = FMT\Configuracion::instancia();
$config->cargar(BASE_PATH . '/config');

if(!defined('PHP_INTERPRETE')){
    define('PHP_INTERPRETE', \FMT\Helper\Arr::get($config['app'], 'php_interprete', 'php74'));
}