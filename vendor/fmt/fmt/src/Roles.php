<?php

namespace FMT;

use \FMT\Helper\Arr;
/**
 * Class Roles
 *
 * Modo de Empleo
 * Para el funcionamiento correcto debera implementar el siguiente bloque en un archivo php dentro del Modelo.
 * A continuacion se deja un ejemplo de como implementarlo *
 *
use FMT\Roles;

class {{{Nombre_Clase}}} extends Roles
{
    static $permisos = [
        3 => [
            'nombre'=> 'Solo Lectura',
            'permisos'=> [
            'Test'=>['listar'=>1, 'ver'=>1 ]]
        ],
        2 => [
            'nombre'=> 'Usuario',
            'permisos'=> ['Test'=>['listar'=>1,'alta'=>1,'modificar'=>1,'borrar'=>1]]
        ],
        1 => [
            'nombre'=> 'Admin',
            'padre' => 2, 'permisos'=> ['Test'=>['alta'=>1,'modificar'=>1,'borrar'=>1]]
        ]
        ];
}
 * Se implementa dentro del atributo estatico

        static $permisos = [

            [rol] => [
                'nombre' => [nombre_de_rol],
                'permisos' => [aca se setean los permisos para cada seccion dentro del rol]
            ];

 * Si en el caso se seteara la clave 'padre', hace referencia a un rol que quiere ser hija de ese padre.
 * Ejemplo:
 *
    1 => [
        'nombre'=> 'Admin',
        'padre' => 2,
        ]
    ];

 * En este caso el rol 1 llamado 'Admin' tiene como padre el rol 2 (Hereda permisos).

 *
 * Caso que el Usuario no tenga permisos, a dicha seccion (por ejemplo), mostrara un error que debera ser sobreescrito
 * por el desarrollador, de acuerdo a los requerimientos del proyecto en el que esta trabajando.
 *
 * @package FMT
 */

    abstract class Roles
    {
        protected static $permisos = [];

        public static function puede($cont, $accion)
        {
			/** @noinspection PhpUndefinedClassInspection */
			$rol =  Usuarios::$usuarioLogueado['permiso'];
            while (true) {
                if (isset(static::$permisos[$rol]['permisos'][$cont][$accion])) {
                    return static::$permisos[$rol]['permisos'][$cont][$accion];
                }
                if (isset(static::$permisos[$rol]['padre'])) {
                    $rol = static::$permisos[$rol]['padre'];
                } else {
                    break;
                }
            }
            return false;
        }


        public static function sin_permisos($accion)
        {
            return "No tiene permisos para usar " . $accion;
        }

        /**
         * Obtiene el id del rol en funcion del nombre suministrado.
         * 
         * @param string $nombre
         * @return int
         * 
         */
        public static function obtener_id($nombre) {
            $id = false;
            foreach (static::$permisos as $key => $permiso){
                if ($permiso['nombre'] == $nombre){
                    $id = $key;
                    break;
                }
            }
            return $id;
        }

        /**
         * Obtiene la info del rol en funcion del id suministrado.
         * 
         * @param int $id
         * @return array
         * 
         */
        public static function obtener_info($id) {
            $info  = false;
            $rol   = Arr::get(static::$permisos, $id, false);
            if ($rol) {
                $info = ['nombre'   => $rol['nombre'], 
                         'id_padre' => Arr::get($rol,'padre', null)
                        ];
            }
            return $info;
        }


        /**
         * Lista todos los roles configurados.
         * 
         * @return array
         */
        public static function listar() {
            $lista = [];
            foreach (static::$permisos as $id_rol => $rol) {
                $lista[$id_rol] = $rol['nombre'];
            }
            asort($lista);
            return $lista;
        }
    }