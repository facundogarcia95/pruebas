<?php
define('BASE_PATH', realpath(__DIR__));
define('CONSOLA_FILE', 'public/sample_consola.php');
if(!empty($_SERVER['_'])){
	define('PHP_INTERPRETE', $_SERVER['_']);
} else {
	define('PHP_INTERPRETE', 'php71');
}