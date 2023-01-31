<?php
/**
 * Se puede ejecutar como `php /directorio/absoluto/consola.php nombre_accion '{"foo":"baz"}'`
 * O como `php /directorio/absoluto/consola.php Nombre_controlador nombre_accion '{"foo":"baz"}'`
 */
require_once __DIR__ . "/../bootstrap.php";

// Ejecutar por consola unicamente, no por navegador web
$denegar	= ['REMOTE_ADDR', 'REQUEST_METHOD', 'HTTP_HOST', 'HTTP_CONNECTION'];
foreach ($denegar as $value) {
	if(isset($_SERVER[$value])){
		header('HTTP/1.0 403 Forbidden ');
		exit;
	}
}
define('IS_CONSOLA', true);
/**
 * REEMPLAZAR `Consola_simple` y `ConsolaEjemplos` por lo que corresponda
 */
$controller		= FMT\Helper\Arr::path($_SERVER, 'argv.1', 'Consola_simple');
$ctrl			= 'ConsolaEjemplos\\Consola\\'.ucfirst(strtolower($controller));

if(!(class_exists($ctrl) && (bool)preg_match('/Consola/', implode('-', class_parents($ctrl))) )){
	$accion		= $controller;
	$controller	= 'Consola_simple';
} else {
	$accion		= FMT\Helper\Arr::path($_SERVER, 'argv.2', 'index');
}
$class		= 'ConsolaEjemplos\\Consola\\' . ucfirst(strtolower($controller));

if (!class_exists($class, 1) || !method_exists($class, 'accion_'.$accion)) {
	$accion = 'help';
}

$control	= new $class(strtolower($accion));
$control->procesar();