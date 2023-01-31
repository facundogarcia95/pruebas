<?php
namespace FMT;


class Vista{

	/**@var string*/
	protected $template;
	protected $vars;
	protected $binded_vars;

	/**
	 * Vista constructor.
	 * @param string $template ruta completa al archivo
	 * @param array|null $vars un array en el formato de set
	 */
	public function __construct($template, $vars = NULL) {
		$this->template = $template;
		if(is_array($vars)) {
			$this->vars = $vars;
		}
	}

	/**
	 * Asigna un valor de variable para el template. Puede ser un nombre,valor o un array de [nombre=>valor]
	 * @param string|array $var
	 * @param mixed|null $val
	 */
	public function set($var, $val = NULL){
		if(!is_array($this->vars)){
			$this->vars = [];
		}
		if(is_array($var)){
			foreach ($var as $item=>$value){
				$this->set($item, $value);
			}
		}else{
			$this->vars[$var] = $val;
		}
	}

	/**
	 * Pasa una variable por referencia al template
	 * @param string $var
	 * @param mixed $val
	 */
	public function bind($var, &$val){
		if(!is_array($this->binded_vars)){
			$this->binded_vars = [];
		}
		$this->binded_vars[$var] = &$val;
	}

	/**
	 * @return string
	 */
	public function render(){
		if(!is_file($this->template)){
			throw new \InvalidArgumentException('Plantilla no encontrada '.$this->template);
		}
		if(is_array($this->vars)){
			extract($this->vars,  EXTR_SKIP);
		}
		if(is_array($this->binded_vars)){
			extract($this->binded_vars,  EXTR_REFS);
		}
		ob_start();
		/** @noinspection PhpIncludeInspection */
		require $this->template;
		return ob_get_clean();
	}


	public function __toString() {
		return $this->render();
	}
}