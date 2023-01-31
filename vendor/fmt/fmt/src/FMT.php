<?php
namespace FMT;

use FMT\Helper\Arr;

/**
 * Class FMT
 * @package FMT
 * Se debe instanciar en el inicio de  la aplicacion, para su implementacion (en bootstrap o index)
 */
class FMT {
	/**@var \FMT\Roles */
	public static $roles;
	/**@var string */
	public static $id_modulo;


	/**
	 * @param array $parametros parametros necesarios para configurar las diferentes funcionalidades.
	 */
	public static function init($parametros) {
		static::$roles = Arr::get($parametros, 'roles', false);
		static::$id_modulo = Arr::get($parametros, 'id_modulo', null);
	}
}