<?php

namespace FMT;

class Configuracion implements \ArrayAccess{
	private $container = [];
	
	/**@var Configuracion*/
	static private $_instancia = NULL;

	static public function instancia(){
		if(!static::$_instancia){
			static::$_instancia = new Configuracion();
		}
		return static::$_instancia;
	}
	
    private function __construct() {}

	public function cargar($ruta){
		if(is_file($ruta)){
			$datos = require $ruta;
			$this->container += $datos;
		}elseif(is_dir($ruta)){
			$archivos = glob(rtrim($ruta,'/').'/*');
			foreach($archivos as $archivo){
				$this->cargar($archivo);
			}
		}
	}
	
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}