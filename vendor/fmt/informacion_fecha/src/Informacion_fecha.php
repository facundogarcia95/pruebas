<?php
	namespace FMT;

	use DateTime;
	use Exception;

	class Informacion_fecha {

		protected static $curl;
		protected static $endpoint;
		const ERROR_SERVIDOR = 1;
		const ERROR_FECHA = 2;

		public static function init($endpoint = null, $curl_setopt = []) {
			static::$curl = \curl_init();

			curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, 1);

			if ($endpoint == true) {
				static::$endpoint = $endpoint;
			}
			else {
				static::$endpoint = 'http://localhost/informacion_fecha/index.php/consulta/';
			}

			foreach($curl_setopt as $name_option => $value) {
				curl_setopt(static::$curl, constant($name_option), $value);
			}
		}

		protected static function consulta($value) {
			curl_setopt(static::$curl, CURLOPT_URL, static::$endpoint.$value);
			$response = curl_exec(static::$curl);

			if ($response) {
				$data = json_decode($response);
				if ($data->estado == 400){
					if ($data->error == self::ERROR_SERVIDOR){
						throw new Exception('Se produjo un error en el servidor.');
					} elseif($data->error == self::ERROR_FECHA) {
						throw new Exception('Se produjo un error en el formato de fecha esperado.');
					}
				} else {
					$data = $data->resultado;
				}

			} else {
				throw new Exception('No se pudo hacer la peticion. Revise su endpoint:'.static::$endpoint);
			}
			return $data;
		}

		/**
		 * Retorna si la fecha es un dia Habil
		 * @param DateTime
		 * @return Boolean
		 * @throws Exception
		 */
		public static function es_habil($fecha) {
			if (static::es_fecha($fecha) === true){
				return static::consulta('habil/'.$fecha->format('Y-m-d'));
			}
		}

		/**
		 * Evalua si la fecha es un feriado
		 * @param DateTime
		 * @return Boolean
		 * @throws Exception
		 */
		public static function es_feriado($fecha) {
			if (static::es_fecha($fecha) === true){
				return static::consulta('feriado/'.$fecha->format('Y-m-d'));
			}
		}

		/**
		 * Retorna todos los feriados del mes
		 * @param DateTime
		 * @return array|mixed
		 * @throws Exception
		 *
		 */
		public static function ver_feriados_mes($fecha) {
			if (static::es_fecha($fecha) === true){
				return static::consulta('feriados_mes/'.$fecha->format('Y-m'));
			}
		}

		/**
		 * Retorna la fecha y si hay precipitaciones
		 * @param  DateTime
		 * @return Boolean
		 * @throws Exception
		 */
		public static function hay_precipitaciones($fecha) {
			if (static::es_fecha($fecha) === true) {
				return static::consulta('precipito/' . $fecha->format('Y-m-d'));
			}
		}

		/**
		 * Retorna cantidad de dias habiles
		 * @param  DateTime
		 * @return Boolean
		 * @throws Exception
		 */
		public static function cantidad_dias_habiles($fecha_desde, $fecha_hasta=null) {

			if (static::es_fecha($fecha_desde) === true){
				$fecha_desde = $fecha_desde->format('Y-m-d');
				
				if($fecha_hasta && static::es_fecha($fecha_hasta) === true){
					$fecha_hasta = $fecha_hasta->format('Y-m-d');
				}else{
					$fecha_hasta = "";
				}

				return  static::consulta('cantidad_dias_habiles/' . $fecha_desde .'/'. $fecha_hasta);
			}
		}

		/**
		 * Evalua si es un objeto DateTime
		 * @param  DateTime
		 * @return Boolean
		 * @throws Exception
		 */
		protected static function es_fecha($fecha){

			if ($fecha instanceof DateTime){
				return true;
			} else {
				throw new Exception('Formato de fecha inválido. Se espera');
			}
		}

		/**
		 * Retorna cantidad de dias habiles
		 * @param  DateTime
		 * @return String
		 * @throws Exception
		 */
		public static function dias_habiles_hasta_fecha($fecha_desde, $cant_dias=0) {
			if (static::es_fecha($fecha_desde) === true){
				$fecha_desde = $fecha_desde->format('Y-m-d');
				return  static::consulta('dias_habiles_hasta_fecha/' . $fecha_desde .'/'. $cant_dias);
			}
		}
	}
