<?php

	namespace FMT;

	class Usuarios {

		/* Atributos */

		private static $app_id;
		private static $curl;
		private static $endpoint;
		public static $usuarioLogueado;

		/* Metodos */

		// Init
		public static function init($app_id, $endpoint = null, $curl_setopt = []) {

			// Seteo App Id
			static::$app_id = $app_id;

			// Inicializo Curl
			static::$curl = \curl_init();

			// Seteo Opciones
			curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, 1);

			foreach($curl_setopt as $name_option => $value) {
				curl_setopt(static::$curl, constant($name_option), $value);
			}
			
			// Seteo Ruta Panel
			if ($endpoint !== null) {
				static::$endpoint = $endpoint;
			}
			else {
				static::$endpoint = 'http://localhost/panel/api.php';
			}

            if (!isset($_SESSION['iu'])){

                static::$usuarioLogueado = [
                    'idUsuario' => '',
                    'user' 		=> '',
                    'nombre' 	=> '',
                    'apellido' 	=> '',
                    'documento' => '',
                    'email' 	=> '',
                    'permiso' 	=> '',
                    'area' 		=> '',
                    'idOrganismo' => ''
                ];

            } else{
                static::$usuarioLogueado = static::getUsuario($_SESSION['iu']);
                static::$usuarioLogueado += static::getPermiso($_SESSION['iu']);
			}
		}

		// Consultar
		private static function consultar($query) {

			// Seteo Url
			curl_setopt(static::$curl, CURLOPT_URL, static::$endpoint.$query);
			
			// Ejecuto Servicio
			$respuesta = curl_exec(static::$curl);

			// Decodifico Respuesta
			if ($respuesta) {
				$datos = json_decode($respuesta); 

			} else {
				$datos = null;
			}

			// Retorno Datos
			return $datos;
		}

		// Get Rol
		public static function getPermiso($user_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=getPermiso&idUsuario='.$user_id.'&idModulo='.static::$app_id);

			// Asigno Resultado
			$dato = ['permiso' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		// Set Rol
		public static function setPermiso($user_id, $permiso_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=setPermiso&idUsuario='.$user_id.'&idModulo='.static::$app_id.'&permiso='.$permiso_id);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		// Eliminar Rol
		public static function eliminarPermiso($user_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=eliminarPermiso&idUsuario='.$user_id.'&idModulo='.static::$app_id);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		// Get Usuarios (Todos)
		public static function getUsuarios($id_modulo = null) {
			
			$moduloID = !empty($id_modulo) ? $id_modulo : static::$app_id;
			// Realizo Consulta
			$respuesta = static::consultar('?accion=getUsuariosPorModulo&idModulo='.$moduloID);

			// Asigno Resultado
			$dato = [];

			// Si Devolvio Usuarios
			if ($respuesta !== null) {
				foreach ($respuesta as $usuario) {
					// Guardo Array
					$dato[$usuario->idUsuario] = [
						'idUsuario' => $usuario->idUsuario,
						'user' 		=> $usuario->user,
						'nombre' 	=> $usuario->nombre,
						'apellido' 	=> $usuario->apellido,
						'documento' => $usuario->documento,
						'email' 	=> $usuario->email, 
						'permiso' 	=> $usuario->permiso,
						'area' 		=> $usuario->area,
						'idOrganismo' => $usuario->idOrganismo
						];
				}
			}

			// Devuelvo Resultado
			return $dato;
		}

		// Get Usuario (Uno)
		public static function getUsuario($user_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=getUsuario&idUsuario='.$user_id);

			// Inicializo Array
			$dato = [];

			// Si Tengo Respuesta
			if ($respuesta) {
				// Guardo Array
				$dato = [
					'idUsuario' => $respuesta->idUsuario,
					'user' 		=> $respuesta->user,
					'nombre' 	=> $respuesta->nombre,
					'apellido' 	=> $respuesta->apellido,
					'documento' => $respuesta->documento,
					'email' 	=> $respuesta->email,
					'area' 		=> $respuesta->idArea,
					'nombreArea'=> $respuesta->nombreArea,
					'idOrganismo'=>$respuesta->idOrganismo
				];
			}

			// Devuelvo Resultado
			return $dato;
		}
		
		//Set Document
		public static function setDocumento($user_id, $documento){
			$documento = rawurldecode($documento);
			$respuesta = static::consultar('?accion=setDocumento&idUsuario='.$user_id.'&documento='.$documento);
			$dato = ['resultado' => $respuesta];
			return $dato;
		}

		// Get Areas (Todas)
		public static function getAreas($idOrganismo = null) {

			// Realizo Consulta
			$query = '?accion=getAreas';
			if($idOrganismo){
				$query .= '&idOrganismo=' . $idOrganismo;
			}
			$respuesta = static::consultar($query);

			// Inicializo Array
			$dato = [];

			// Si Tengo Respuesta
			if ($respuesta !== null) {
				foreach ($respuesta as $area) {
					// Guardo Array
					$dato[] = [
						'idArea'	=> $area->idArea,
						'nombre' 	=> $area->nombre
					];
				}
			}

			// Devuelvo Resultado
			return $dato;
		}

		// Set Area
		public static function setArea($user_id, $area_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=setArea&idUsuario='.$user_id.'&idArea='.$area_id);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		public static function getOrganismos($index = null) {

            // Realizo Consulta
            $query = '?accion=getOrganismos';
            if($index)    $query.= '&idOrganismo='.$index ;

            $respuesta = static::consultar($query);
            // Inicializo Array
            $dato = [];

            // Si Tengo Respuesta
            if ($respuesta !== null) {
                foreach ($respuesta as $organismo) {
                    // Guardo Array
                   	$dato[] = [
                       'idOrganismo'    => $organismo->idOrganismo,
                       'nombre'     => $organismo->nombre
                    ];
                }
            }
            // Devuelvo Resultado
            return $dato;
        }
        
        // Get Metadata 
		public static function getMetadata($user_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=getMetadata&idUsuario='.$user_id.'&idModulo='.static::$app_id);

			// Asigno Resultado
			$dato = ['metadata' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		// Set Metadata
		public static function setMetadata($user_id, $metadata) {
            $metadata = rawurlencode($metadata);
			// Realizo Consulta
			$respuesta = static::consultar('?accion=setMetadata&idUsuario='.$user_id.'&idModulo='.static::$app_id.'&metadata='.$metadata);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		// Eliminar Metadata
		public static function eliminarMetadata($user_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=eliminarMetadata&idUsuario='.$user_id.'&idModulo='.static::$app_id);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		public static function getTodosModulos() {

            // Realizo Consulta
            $query = '?accion=getTodosModulos';
           

            $respuesta = static::consultar($query);
            // Inicializo Array
            $dato = [];

            // Si Tengo Respuesta
            if ($respuesta !== null) {
                foreach ($respuesta as $modulo) {
                    // Guardo Array
                   	$dato[] = [
						"idModulo"   =>	$modulo->idModulo,
						"nombre"	 => $modulo->nombre,
						"carpeta"	 => $modulo->carpeta,
						"estado"	 => $modulo->estado,
                    ];
                }
            }
            // Devuelvo Resultado
            return $dato;
        }

		/**
		 * Obtiene los modulos autorizados para el user buscado.
		 * 
		 * @param user_id int 
		 * @return array
		 */
		public static function getModulos($user_id) {

			// Realizo Consulta
			$respuesta = static::consultar('?accion=getModulos&idUsuario='.$user_id);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;
		}

		public static function getAutorizacionesDeUsuarios($modulos,$tipo=null){

			// Realizo Consulta
			$respuesta = static::consultar('?accion=getAutorizacionesDeUsuarios&modulos='.$modulos.'&tipo='.$tipo);

			// Asigno Resultado
			$dato = ['resultado' => $respuesta];

			// Devuelvo Resultado
			return $dato;

		}

	}