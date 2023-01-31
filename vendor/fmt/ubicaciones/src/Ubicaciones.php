<?php
	namespace FMT;

	class Ubicaciones {

		protected static $curl;
		protected static $endpoint;

		public static function init($endpoint = null, $curl_setopt = []) {

			static::$curl = \curl_init();
			curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, 1);

			foreach($curl_setopt as $name_option => $value) {
				curl_setopt(static::$curl, constant($name_option), $value);
			}

			if ($endpoint !== null) {
				static::$endpoint = $endpoint;
			}
			else {
				static::$endpoint = 'http://localhost/ubicaciones/index.php/';
			}
		}


		protected static function consulta($value) {
			curl_setopt(static::$curl, CURLOPT_URL, static::$endpoint.$value);
			$response = curl_exec(static::$curl);

			if ($response) {
				$data = json_decode($response);

			} else {
				$data = null;
			}
			return $data;
		}


		/**
		 * Devuelve en formato JSON el listado de paises		 
		 * @return string
		 */
		public static function get_paises() {
			return static::consulta('paises');
		}

		/**
		 * Devuelve en formato JSON el listado de las provincias o regiones de
		 * del país
		 * @param  string $id_pais Por ejemplo: br	
		 * @return string
		 */
		public static function get_regiones($id_pais) {
			return static::consulta('regiones/'.strtoupper(strval($id_pais)));
		}

		/**
		 * Devuelve en formato JSON el listado de las localidades de
		 * la región o provincia
		 * @param  integer $id_region		 
		 * @return string
		 */
		public static function get_localidades($id_region) {
			return static::consulta('localidades/'.(intval($id_region)));
		}

		
		/**
		 * Devuelve en formato JSON el detalle de la localidad		 
		 * @param  integer $id_localidad		 
		 * @return string
		 */
		public static function get_localidad($id_localidad) {
			return static::consulta('localidad/'.(intval($id_localidad)));
		}

		/**
		 * Devuelve en formato JSON el detalle del pais	
		 * @param  string $id_pais Por ejemplo: ar
		 * @return string
		 */
		public static function get_pais($id_pais){
			return static::consulta('pais/'.strtoupper(strval($id_pais)));
		}

		/**
		 * Devuelve en formato JSON el detalle de la región/provincia	
		 * @param  integer $id_pais
		 * @return string
		 */
		public static function get_region($id_region){
			return static::consulta('region/'.(intval($id_region)));
		}

		/**
		 * Devuelve en formato JSON el listado de gentilicios. EL id corresponde al del pais.		 
		 * @return string
		 */
		public static function get_gentilicios() {
			return static::consulta('gentilicios');
		}		
	}