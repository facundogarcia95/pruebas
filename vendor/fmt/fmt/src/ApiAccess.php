<?php
namespace FMT;
/**
 * Clase para controlar el acceso a aplicaciones via API HTTP.
*/
class ApiAccess {

/**
 * Valida que el token recibido por la cabecera **X-Authorization** sea valido. Caso contrario corta la ejecucion y lanza error 403.
 * **$whiteListToken** es un array con indice numerico cuyo valor es un array con formato `['id_modulo' => int|string, 'token' => string]`
 *
 * @param array $whiteListToken - Lista de tokens validos vinculados a Id de Aplicacion. `[0 => ['id_modulo' => int|string, 'token' => string]]`
 * @return void
 */
	static public function permitir($whiteListToken=array()){
		$tokenRequest	= !empty($_SERVER['HTTP_X_AUTHORIZATION']) ? $_SERVER['HTTP_X_AUTHORIZATION'] : null;
		if($tokenRequest == null){
			header('HTTP/1.0 403 Forbidden');
			echo '403 Forbidden - You shall not pass!';
			exit;
		}
		$tokenRequest	= json_decode(base64_decode($tokenRequest), true);
		if(empty($whiteListToken) || empty($tokenRequest['id_modulo']) || empty($tokenRequest['token'])){
			header('HTTP/1.0 403 Forbidden');
			echo '403 Forbidden - System Disabled';
			exit;
		}
		foreach ($whiteListToken as $list) {
			if($tokenRequest['id_modulo'] == $list['id_modulo'] && $tokenRequest['token'] == $list['token']){
				return true;
			}
		}
		header('HTTP/1.0 403 Forbidden');
		echo '403 Forbidden - You shall not pass!';
		exit;
	}
}
