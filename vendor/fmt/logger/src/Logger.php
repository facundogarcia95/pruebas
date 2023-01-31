<?php
namespace FMT;
class Logger {

	protected static $id_usuario;
	protected static $id_modulo;
	protected static $end_point_event;
	protected static $end_point_debug;
	protected static $debug;
	protected static $curl_setopt;

	public static function init($id_usuario,$id_modulo,$end_point_event,$end_point_debug,$debug, $curl_setopt = []){
			static::$id_usuario			=	 $id_usuario;
			static::$id_modulo			=	 $id_modulo;
			static::$end_point_event 	=	 $end_point_event;
			static::$end_point_debug 	=	 $end_point_debug;
			static::$debug 				=	 $debug;
			static::$curl_setopt		=	 $curl_setopt;	
	}

	protected static function call_api($data,$end_point){
		$ch = curl_init($end_point);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		foreach(static::$curl_setopt as $name_option => $value) {
			curl_setopt($ch, constant($name_option), $value);
		}
		$result = curl_exec($ch);
		curl_close($ch);
	}

	public static function event($event,$content){
		$conf = array(
			'id_usuario' 	=> static::$id_usuario,
			'id_modulo' 	=> static::$id_modulo,
			'fecha' 			=> round(microtime(true) * 1000),
			'event'				=> $event,
			'data'				=> $content
		);	
		$rta_json = json_encode($conf, JSON_UNESCAPED_UNICODE);
		static::call_api($rta_json,static::$end_point_event);
	}

	public static function debug($event, $content){
		if (static::$debug) {
			$conf = array(
				'id_usuario' 	=> static::$id_usuario,
				'id_modulo' 	=> static::$id_modulo,
				'fecha' 			=> round(microtime(true) * 1000),
				'event'				=> $event,
				'data'				=> $content
			);
			$rta_json = json_encode($conf, JSON_UNESCAPED_UNICODE);
			static::call_api($rta_json,static::$end_point_debug);
		}
	}
}
