<?php
namespace FMT;
/**
 * Estandariza las respuesta para manejo con JSON. Es una aproximacion a la metodologia REST API.
 *
 * En caso de consultas provenientes por JS se estandariza la respuesta con codigos de error, mensajes y data.
 * En caso de consultas provenientes por CURL se devuelve la informacion tal cual fue cargada en `$this->data`.
 *
 * Para setear :
 * - Informacion se usa `$this->setData($data = array())`.
 * - Mensajes de error `$this->setMensajes()`
 * - Activar flag de errores `$this->setÈrror()` o automaticamente `$this->data` vacio.
 * - Callbacks para JavaScript `$this->setCallback('funcion_cliente')` o automaticamente si es pasado por get "dominio.com/url_consulta?callback=funcion_cliente".
 * Para renderizar la respuesta se usa `$this->render()`. *
 */
class ApiJSON {
/** @var	boolean */
	private	$error			= false;
/** @var	boolean */
	private	$success		= true;
/** @var	array */
	private	$mensajes		= [];
/** @var	array */
	private	$data			= [];
/** @var	false|string */
	private $callback		= false;
/** @var	Request:: */
	private $request		= null;

/**
 * Restaura los atributos a su valor original.
 * @return void
*/
	final protected function reset(){
		$this->error	= false;
		$this->success	= true;
		$this->mensajes	= [];
		$this->data		= [];
		$this->callback	= false;
	}

	final public function __construct(){
		$this->reset();
		$this->request	= new Request();
	}

/**
 * Formatea la respuesta en JSON con parametros estandar.
 * Luego de imprimir corta la ejecucion con exit.
 * En caso de error devuelve STATUS_CODE 404 o 200 caso contrario.
 *
 * @return void
*/
	public function render(){
		$this->checkDataEmpty();
		$response	= [
			'success'	=> !((bool)$this->error),
			'error'		=> (bool)$this->error,
			'callback'	=> $this->setCallback(),
			'mensajes'	=> $this->mensajes,
		];
		if(isset($this->data['data']) && is_array($this->data['data'])){
			$response			= array_merge($response, $this->data);
		} else {
			$response['data']	= $this->data;
		}

		header("Content-Type: application/json;charset=utf-8");
		http_response_code($this->error ? 404 : 200);
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
		$this->reset();
		exit;
	}

/**
 * Recibe la informacion que se desea responder en formato JSON.
 * Si se desea pasar informacion adicional al mismo nivel que "{data: []}" se puede pasar "data" como key de este metodo.
 * e.j.:
 * ['data'	=> array(), 'draw'	=> '4']
 *
 * @param array	$data
 * @return void
*/
	public function setData($data=null){
		if($data === null){
			return;
		}
		if(is_object($data)){
			$data	= json_decode(json_encode($data), true);
		}
		if(!is_array($data)){
			$data	= [$data];
		}
		$this->data	= $data;
	}

/**
 * Comprueba si `$this->data` tiene informacion, caso contrario activa el flag de error.
 * @return void
*/
	private function checkDataEmpty(){
		if(empty($this->data)){
			$this->setError();
			if(empty($this->mensajes)){
				$this->setMensajes('No existe la información solicitada.');
			}
		}
	}
/**
 * Setea `$this->error` en true.
 * @return void
*/
	public function setError(){
		$this->error	= true;
	}

/**
 * El parametro "callback" en la respuesta sirve para que JavaScript invoque una funcion de su contexto en conjunto con la respuesta.
 *
 * En caso de recibir por parametro "GET" el parametro "callback", se setea en el atributo.
 * Si se especifica manual, se superpone al recibido por "GET".
 *
 * Si no se pasa nada como parametro, se devuelve el contenido de `$this->callback`
 * @param string	$callback
 * @return void
*/
	public function setCallback($callback=null){
		if(is_string($callback)){
			return $this->callback	= $callback;
		}
		if(is_string($this->request->query('callback'))){
			return $this->callback	= $this->request->query('callback');
		}
		return $this->callback;
	}

/**
 * Setea lo mensaje de error que se mostraran en la respuesta.
 *
 * @param string|array	$text	- Mensaje o array con mensajes.
 * @return void
 */
	public function setMensajes($text=null){
		if(!empty($this->mensajes) && is_array($text)){
			$this->mensajes	= array_merge($this->mensajes, $text);
		}
		if(empty($this->mensajes) && is_array($text)){
			$this->mensajes	= $text;
		}
		if(is_string($text)){
			$this->mensajes[]	= $text;
		}
		return $this->mensajes;
	}
}