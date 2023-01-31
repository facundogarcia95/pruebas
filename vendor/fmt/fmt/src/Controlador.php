<?php
namespace FMT;

abstract class Controlador
{
	/**@var string|\FMT\Vista*/
	protected $vista;
	/**@var string*/
	protected $clase;
	/**@var string*/
	protected $accion;
	/**@var Request*/
	protected $request;
	/**@var Mensajeria*/
	protected $mensajeria;

	public function __construct($accion)
	{
		$this->clase = preg_replace('/.*\\\\/', '', static::CLASS);
        $this->accion = $accion;
		$this->request = new Request();
		$this->mensajeria = new Mensajeria(FMT::$id_modulo, $this->clase, $this->accion);
	}

    protected function antes(){
        if(FMT::$roles) {
            $app_roles = FMT::$roles;
            if($app_roles::puede($this->clase,$this->accion)) {
                return true;
            } else {
                $this->vista = $app_roles::sin_permisos($this->accion);
                return  false;
            }
        } else {
            return true;
        }
    }

	/**
	 * @throws \Exception
	 */
	public function procesar()
    {
        $this->existe_accion();
        if($this->antes()){
            $this->{'accion_'.$this->accion}();
            $this->despues();
        }
        $this->mostrar_vista();
    }

	protected function despues(){}

	/**
	 * @throws \Exception
	 */
	protected function existe_accion()
	{
        $metodos = get_class_methods($this);
        if(!in_array('accion_'.$this->accion, $metodos))
        {
            throw new \Exception('ERROR: No existe la accion '. $this->accion);
        }
	}

	public function mostrar_vista(){
		echo $this->vista;
	}


	/**
	 * @param string $url
	 */
	public function redirect($url){
		header('Location: '.$url);
		exit;
	}

	public function set_query($key,$value) {
		$this->request->query($key,$value);
	}

/**
 * Un gran poder conlleva una gran responsabilidad.
 * Use parent::__get($key) para reemplazar este metodo.
 *
 * @return any
*/
	public function __get($key){
		if($key === 'json'){
			$this->json = new ApiJSON();
		}
		return $this->{$key};
	}
}