<?php
namespace FMT\Consola\Modelo;

use FMT\Consola\Helper\Conexiones;
use FMT\Logger;
use FMT\Consola\Controlador\ConsolaCore;

class ColaTarea extends \FMT\Modelo {
/** @var int */
	public $id;
/** @var int */
	public $id_usuario;
/** @var string */
	public $accion;
/** @var json */
	public $parametros;
/** @var int */
	public $pendiente;
/** @var int */
	public $en_ejecucion;
/** @var string */
	public $md5_sum;
/** @var timestamp */
	public $time_start;
/** @var timestamp */
	public $time_finish;
/** @var int */
	public $borrado;

/**
 * Obtiene y finaliza una tarea.
 *
 * @param string|int $accion	- El string con el nombre de la accion o el ID en tabla `cola_tareas`
 * @param mixed $parametros		- Opcional
 * @return bool
 */
	static public function tareaFinalizar($accion=null, $parametros=null){
		if(!(is_object($accion) && $accion instanceof ColaTarea)){
			if(is_numeric($accion)){
				$tarea	= static::obtener($accion);
			} else {
				$tarea	= static::obtenerPorAccion($accion, $parametros);
			}
		} else {
			$tarea	= $accion;
		}
		$tarea->en_ejecucion	= '0';
		$tarea->pendiente		= '0';
		$tarea->time_finish		= \DateTime::createFromFormat('U', time());
		return $tarea->modificacion();
	}

/**
 * Obtiene y marca como Ejecutando una tarea. 
 * Le quita el estado de pendiente.
 *
 * @param string|int|ColaTarea:: $accion	- El string con el nombre de la accion o el ID en tabla `cola_tareas` o la instancia traida por obtener.
 * @param mixed $parametros		- Opcional
 * @return bool
 */
	static public function tareaEjecutando($accion=null, $parametros=null){
		if(!(is_object($accion) && $accion instanceof ColaTarea)){
			if(is_numeric($accion)){
				$tarea	= static::obtener($accion);
			} else {
				$tarea	= static::obtenerPorAccion($accion, $parametros);
			}
		} else {
			$tarea	= $accion;
		}
		$tarea->en_ejecucion	= '1';
		$tarea->pendiente		= '0';
		$tarea->time_start		= \DateTime::createFromFormat('U', time());
		return $tarea->modificacion();
	}

/**
 * Devuelve un array de objetos con las tareas pendientes.
 *
 * @return array
 */
	static public function listarPendientes(){
		$sql	= <<<SQL
			SELECT 
				id,
				id_usuario,
				accion,
				parametros,
				pendiente,
				en_ejecucion,
				md5_sum,
				time_start,
				time_finish,
				borrado
			FROM cola_tareas
			WHERE borrado = 0 AND pendiente = 1 AND en_ejecucion = 0 AND time_finish IS NULL
			ORDER BY id DESC
SQL;
		$resp	= (new Conexiones())->consulta(Conexiones::SELECT, $sql);
		if(empty($resp)) { 
			return [];
		}
		foreach ($resp as &$value) {
			$value	= static::arrayToObject($value);
		}
		return $resp;
	}

/**
 * Busca y devuelve una tarea por ID sin importar el estado.
 *
 * @param int $id
 * @return ColaTarea::
 */
	static public function obtener($id=null){
		$obj	= new static;
		if($id===null){
			return	static::arrayToObject();
		}
		$sql_params	= [
			':id'	=> $id,
		];
		$sql	= <<<SQL
			SELECT 
				id,
				id_usuario,
				accion,
				parametros,
				pendiente,
				en_ejecucion,
				md5_sum,
				time_start,
				time_finish,
				borrado
			FROM cola_tareas
			WHERE id = :id
SQL;
		$cnx	= new Conexiones();
		$res	= $cnx->consulta(Conexiones::SELECT, $sql, $sql_params);
		if(!empty($res)){
			return	static::arrayToObject($res[0]);
		}
		return	static::arrayToObject();
	}

/**
 * Obtiene una tarea en base al nombre de la accion y los parametros.
 * Los parametros se hashean como MD5 para usar como filtro.
 * Solo busca tareas que fueron finalizadas por `::tareaFinalizar()`
 *
 * @param string $accion
 * @param mixed $parametros
 * @return ColaTarea::
 */
	static public function obtenerPorAccion($accion=null, $parametros=null){
		if($accion===null){
			return	static::arrayToObject();
		}
		if(!is_string($parametros)){
			$parametros	= json_encode($parametros);
		}
		$sql_params	= [
			':accion'	=> $accion,
			':md5_sum'	=> md5($parametros),
		];
		$sql	= <<<SQL
			SELECT 
				id,
				id_usuario,
				accion,
				parametros,
				pendiente,
				en_ejecucion,
				md5_sum,
				time_start,
				time_finish,
				borrado
			FROM cola_tareas
			WHERE accion = :accion AND md5_sum = :md5_sum AND borrado = 0 AND time_finish IS NULL
SQL;
		$cnx	= new Conexiones();
		$res	= $cnx->consulta(Conexiones::SELECT, $sql, $sql_params);
		if(!empty($res)){
			return	static::arrayToObject($res[0]);
		}
		return	static::arrayToObject();
	}

/**
 * Agrega una tarea y lanza el proceso `cola_tareas` de ser posible.
 * Si la tarea ya existe y esta ejecutandose o pendiente, devuelve `false`.
 * Si es un proceso agregado desde un controlador (lanzado por un usuario) se agrega el `id_usuario` .
 *
 * @param string $accion		- Nombre del metodo del controlador sin 'accion_'. E.j: si metodo es 'accion_test' queda como 'test'.
 * @param mixed $parametros		- Opcional. Parametros que se le pasan a la accion.
 * @return bool
 */
	static public function agregar($accion=null, $parametros=null){
		$tarea					= static::obtenerPorAccion($accion, $parametros);
		if(!empty($tarea->en_ejecucion)){
			return false;
		}
		if(!empty($tarea->pendiente)){
			return false;
		}
		$tarea->id				= null;
		$tarea->accion			= $accion;
		if(!is_string($parametros)){
			$tarea->parametros	= json_encode($parametros);
		}
		$tarea->md5_sum			= md5($tarea->parametros);
		$tarea->pendiente		= '1';
		$tarea->en_ejecucion	= '0';
		$tarea->time_start		= null;
		$tarea->time_finish		= null;
		if(isset($_SESSION) && isset($_SESSION['iu']) && is_numeric($_SESSION['iu'])){
			$tarea->id_usuario		= $_SESSION['iu'];
		}
		if($tarea->alta()){
			//
			// Evaluar esta sentencia. Se corre el riesgo de que se ejecute la cola de tareas 2 o mas veces al mismo tiempo por cada servidor, en el balanceo de carga.
			//
			// if(!ConsolaCore::ProcessAlive('cola_tareas') && !ConsolaCore::LimiteMaximoAlcanzado()){
			// 	ConsolaCore::EjecutarAccion('cola_tareas');
			// }
			return true;
		}
		return false;
	}

	public function alta(){
		$campos		= [
			'id_usuario'		=> ':id_usuario',
			'accion'			=> ':accion',
			'parametros'		=> ':parametros',
			'pendiente'			=> ':pendiente',
			'en_ejecucion'		=> ':en_ejecucion',
			'md5_sum'			=> ':md5_sum',
			'time_start'		=> ':time_start',
			'time_finish'		=> ':time_finish',
		];
		$sql_params	= [
			':id_usuario'		=> $this->id_usuario,
			':accion'			=> $this->accion,
			':parametros'		=> $this->parametros,
			':pendiente'		=> $this->pendiente,
			':en_ejecucion'		=> $this->en_ejecucion,
			':md5_sum'			=> $this->md5_sum,
			':time_start'		=> $this->time_start,
			':time_finish'		=> $this->time_finish,
		];
		if(!is_string($this->parametros)){
			$sql_params[':parametros']	= json_encode($this->parametros);
		}
		if($this->time_start instanceof \DateTime){
			$sql_params[':time_start']	= $this->time_start->format('Y-m-d H:i:s');
		}
		if($this->time_finish instanceof \DateTime){
			$sql_params[':time_finish']	= $this->time_finish->format('Y-m-d H:i:s');
		}
		$sql	= 'INSERT INTO cola_tareas('.implode(',', array_keys($campos)).') VALUES ('.implode(',', array_values($campos)).')';
		$cnx	= new Conexiones();
		$res	= $cnx->consulta(Conexiones::INSERT, $sql, $sql_params);
		if($res !== false){
			$this->id	= $res;
			$datos = (array) $this;
			$datos['modelo'] = 'ColaTarea';
			Logger::event('alta', $datos);
			return true;
		}
		return false;
	}
	static public function listar() {
		return false;
	}
	public function baja(){
		return false;
	}
	public function modificacion(){
		if(empty($this->id)){
			return false;
		}
		$campos		= [
			'pendiente'			=> 'pendiente = :pendiente',
			'en_ejecucion'		=> 'en_ejecucion = :en_ejecucion',
			'time_start'		=> 'time_start = :time_start',
			'time_finish'		=> 'time_finish = :time_finish',
		];
		$sql_params	= [
			':id'				=> $this->id,
			':pendiente'		=> $this->pendiente,
			':en_ejecucion'		=> $this->en_ejecucion,
			':time_start'		=> $this->time_start,
			':time_finish'		=> $this->time_finish,
		];
		if($this->time_start instanceof \DateTime){
			$sql_params[':time_start']	= $this->time_start->format('Y-m-d H:i:s');
		}
		if($this->time_finish instanceof \DateTime){
			$sql_params[':time_finish']	= $this->time_finish->format('Y-m-d H:i:s');
		}
		$sql	= 'UPDATE cola_tareas SET '.implode(',', $campos).' WHERE id = :id';
		$res	= (new Conexiones())->consulta(Conexiones::UPDATE, $sql, $sql_params);
		if($res !== false){
			$datos = (array) $this;
			$datos['modelo'] = 'ColaTarea';
			Logger::event('modificacion', $datos);
			return true;
		}
		return false;
	}
	public function validar() {
		return false;
	}
	static public function arrayToObject($res = []) {
		$campos	= [
			'id'				=> 'int',
			'id_usuario'		=> 'int',
			'accion'			=> 'string',
			'parametros'		=> 'json',
			'pendiente'			=> 'int',
			'en_ejecucion'		=> 'int',
			'md5_sum'			=> 'string',
			'time_start'		=> 'datetime',
			'time_finish'		=> 'datetime',
			'borrado'			=> 'int',
		];
		$obj = new self();
		foreach ($campos as $campo => $type) {
			switch ($type) {
				case 'int':
					$obj->{$campo}	= isset($res[$campo]) ? (int)$res[$campo] : null;
					break;
				case 'json':
					$obj->{$campo}	= isset($res[$campo]) ? json_decode($res[$campo], true) : null;
					break;
				case 'datetime':
					$obj->{$campo}	= isset($res[$campo]) ? \DateTime::createFromFormat('Y-m-d H:i:s', $res[$campo]) : null;
					break;
				case 'date':
					$obj->{$campo}	= isset($res[$campo]) ? \DateTime::createFromFormat('Y-m-d H:i:s', $res[$campo].' 0:00:00') : null;
					break;
				default:
					$obj->{$campo}	= isset($res[$campo]) ? $res[$campo] : null;
					break;
			}
		}
		return $obj;
	}
}