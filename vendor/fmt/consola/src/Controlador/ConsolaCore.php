<?php
namespace FMT\Consola\Controlador;
use \FMT\Controlador;

/**
 * Clase para manejar procesos cron.
 * Si al intentar ejecutar un proceso con ::EjecutarProceso(); el servidor esta ocupado, queda a la espera que se liberen intancias de cron.php.
 * Para controlar este comportamiento y evitar la espera de ser necesario se pueden usar los metodos ::LimiteMaximoAlcanzado() o ::ProcessAlive().
 *
 * - ->debug() 				- sirve para debuguear en procesos hijos.
 * - ::SDebug() 			- sirve para debuguear en cualquier parte del proceso del lado servidor.
 * - ->getParams()			- Obtiene parametros pasados por enviromentes a procesos hijos
 * - ->ejecutar_accion()	- Ejecuta procesos hijos
 * - ::ClonadoProcessAlive() - Uso interno de los procesos, evita que se ejecuten 2 instacias del mismo proceso, ideal para usar con ColaTarea:: 
 * - ::ProcessAlive()		- Sirve para controlar si el proceso que se quiere ejecutar ya esta en uso. Ideal para mezclar con ColaTarea::
 * - ::LimiteMaximoAlcanzado() - Sirve para saber si el proyecto tiene mas x ::HIJOS  ejecutandose, (basicamente hace con 'ps ax' con varios 'grep')
*/

class ConsolaCore extends  Controlador {
/**
 * Cantidad de proceso en simultaneo
 * @var        integer
 */
	const HIJOS							= 4;
/**
 * @var        array
 */
	protected static $PROCESOS_VIVOS	= [];

/**
 * Devuelve el directorio temporal para guardar archivos temporales.
 * @return string
 */
	static public function getDirectorioTMP(){
		$return	= BASE_PATH.'/uploads/temporal_consola';
		if(!is_dir($return)){
			mkdir($return, 0777, true);
		}
		return $return;
	}

/**
 * Antes de ejecutar una accion, comprueba que sea llamado desde entorno servidor.
*/
	final public function procesar(){
		if(!defined('BASE_PATH') || !defined('CONSOLA_FILE')){
			throw new \Exception('No se encuentran definidas las constantes "CONSOLA_FILE" o "BASE_PATH"', 1);
		}
		static::check_server_enviroment();
		proc_nice(19);
		parent::procesar();
	}
/**
 * Funcion para debugear los procesos que se ejecutan por sub-rutinas.
 * El resultado del debug se guarda en "/tmp/cron-debug".
 *
 * @param      mixing	$data   		- La informacion a debugear.
 * @param      boolean	$clean_debug	- Si es "true" limpia el archivo de registros anteriores. Default: false
 * @param	   string	$name			- Permite cambiar el nombre del archivo donde se guarda el archivo de error. Default: "cron-debug"
 *
 * @return void
 */
	final public function debug($data=null, $clean_debug=false, $name='cron-debug'){
		$trace	= debug_backtrace();
		$_debug	= print_r($data, true);

		$html	= <<<HTML

{$trace[0]['file']} | Linea: {$trace[0]['line']}
--------
{$_debug}
_______________________
HTML;
		$dir	= static::getDirectorioTMP();
		if($clean_debug){
			$resource	= fopen("{$dir}/{$name}.log", 'w+');
		} else {
			$resource	= fopen("{$dir}/{$name}.log", 'a+');
		}
		fwrite($resource, $html);
		fclose($resource);
	}

/**
 * Modo statico para debuguear.
 * Muy util para usar en modelos que interactuan con los procesos cron.
 *
 * @param      mixing	$data   		- La informacion a debugear.
 * @param      boolean	$clean_debug	- Si es "true" limpia el archivo de registros anteriores. Default: false
 * @param	   string	$name			- Permite cambiar el nombre del archivo donde se guarda el archivo de error. Default: "cron-debug"
 *
 * @return void
 */
	final static public function SDebug($data=null, $clean_debug=false, $name='cron-debug'){
		return (new static(null))->debug($data, $clean_debug, $name);
	}

/**
 * Comprueba que el metodo no se este llamando desde el navegador web. Solo proceso del sistema.
 */
	static final protected function check_server_enviroment(){
		$denegar	= ['REMOTE_ADDR', 'REQUEST_METHOD', 'HTTP_HOST', 'HTTP_CONNECTION'];
		if(!defined('CONSOLA_FILE') || empty(constant('CONSOLA_FILE'))){
			throw new \Exception("La constante 'CONSOLA_FILE' no esta definida correctamente.", 1);
			exit;
		}
		if(!defined('PHP_INTERPRETE') || empty(constant('PHP_INTERPRETE'))){
			throw new \Exception("La constante 'PHP_INTERPRETE' no esta definida correctamente.", 1);
			exit;
		}

		foreach ($denegar as $value) {
			if(isset($_SERVER[$value])){
				header('HTTP/1.0 403 Forbidden ');
				exit;
			}
		}
	}

/**
 * Obtener parametros pasado como enviroments. Los parametros que recibe fueron pasados por "$this->ejecutar_accion()"
 * Se usa en procesos hijos.
 *
 * TODO: Contemplar el uso de  getopt()
 *
 * @return     Object|null
 */
	final protected function getParams(){
		if(!empty($_SERVER['argv'][2]) && !empty($_SERVER['argv'][3]) && ($_SERVER['argv'][2] == '--params' || $_SERVER['argv'][2] == '-p')){
			$argv_param_indice	= 3;
		} else if (!empty($_SERVER['argv'][3]) && !empty($_SERVER['argv'][4]) && ($_SERVER['argv'][3] == '--params' || $_SERVER['argv'][3] == '-p')){
			$argv_param_indice	= 4;
		} else {
			$argv_param_indice	= false;
		}
		if($argv_param_indice !== false){
			$fork_name	= $_SERVER['argv'][$argv_param_indice];
			return unserialize(getenv($fork_name));
		}
		return null;
	}
/**
 * Controla que no se ejecuten mas procesos en simultaneo de los permitidos.
 * Antes de ejecutar un proceso, checkea que los procesos actuales en ejecucion esten vivos, caso contrario espera, y vuelve a comprobar.
 *
 * @return void
*/
	private function preEjecucion(){
		usleep(100);
		while(count(static::$PROCESOS_VIVOS) >= static::HIJOS){
			foreach (static::$PROCESOS_VIVOS as $indice => &$resource) {
				if(is_resource($resource)){
					$data	= proc_get_status($resource);
					if(empty($data['running']) ){ // Quito por problemas en el servidor: && empty(posix_getpgid($data['pid'] +1 ))
						proc_close($resource);
						$resource	= null;
						unset(static::$PROCESOS_VIVOS[$indice]);
					}
				}
				usleep(100);
			}
			usleep(100);
		}
	}

/**
 * Agrega un proceso abierto por "proc_open" a la lista de procesos activos.
 *
 * @param Resource $resource
 * @return boolean
*/
	private function postEjecucion(&$resource=null){
		if(is_resource($resource)){
			static::$PROCESOS_VIVOS[]	= $resource;
			return true;
		}
		return false;
	}
/**
 * Ejecuta un proceso hijo. Los parametros son pasados como variables de entorno serializados.
 * Los errores de los hijos se muestran en "/tmp/cron-error-output.txt".
 * Si el servidor esta ocupado y no se pude ejecutar, espera hasta que se libera.
 * Para no ejecutar la accion se puede usar ::LimiteMaximoAlcanzado() o ::ProcessAlive() para controlar este comportamiento.
 *
 * @param      string	$accion     - Accion a ejecutar. E.j: 'test_proceso' o 'Consola_ejemplo test_proceso'
 * @param      mixing	$params		- Parametros  cualquiera.
 *
 * @return     boolean
 */
	final protected function ejecutar_accion($accion=null,$params=null){
		if(empty($accion)){
			return false;
		}
		while(static::LimiteMaximoAlcanzado()){
			usleep(100);
		}

		$config	= \FMT\Configuracion::instancia();
		$dir	= static::getDirectorioTMP();
		if(!empty($config['app']['dev'])){
			$salida_stderr	= "{$dir}/cron-error-output.log";
		} else {
			$salida_stderr	= '/dev/null';
		}
		$endpoint	= constant('BASE_PATH').'/'. constant('CONSOLA_FILE');
		$bin_php	= constant('PHP_INTERPRETE');
		$fork_name	= getmypid() . '_' . time();
		$enviroment	= [
			"{$fork_name}"	=> serialize($params)
		];
		$descriptorspec	= array(
			0	=> array("pipe", "w"),  // stdin es una tubería usada por el hijo para lectura
			1	=> array("pipe", "r"),  // stdout es una tubería usada por el hijo para escritura
			2	=> array("file", "{$dir}/cron-error-output.log", "a") // stderr es un fichero para escritura
		);

		$this->preEjecucion();
		proc_nice(19);
		$child_process	= proc_open("{$bin_php} {$endpoint} {$accion} --params {$fork_name} > {$salida_stderr} 2>&1 &", $descriptorspec, $pipes, constant('BASE_PATH'), $enviroment);
		return $this->postEjecucion($child_process);
	}

/**
 * Metodo utilizado para ejecutar procesos desde controladores comunes y silvestres.
 *
 * @param      string	$accion     - Accion a ejecutar.
 * @param      array	$params		- Parametros  cualquiera.
 *
 * @return     boolean
*/
	final static public function EjecutarAccion($accion=null,$params=null){
		if(empty($accion)){
			return false;
		}
		return (new static($accion))->ejecutar_accion($accion, $params);
	}

/**
 * Evalua si es posible ejecutar el proceso o si excede la cantidad de procesos permitido.
 * Sirve para que no se sature el servidor.
 * Solo filtra por proyecto.
 *
 * @return bool - Devuelve true si el limite fue alcanzado, falso si hay cupo disponible.
 */
	final static public function LimiteMaximoAlcanzado(){
		$filtro_x_proyecto	= '';
		if(defined('BASE_PATH')){
			$path				= constant('BASE_PATH');
			$filtro_x_proyecto	= "grep '{$path}' |";
		}
		$script	= constant('CONSOLA_FILE');
		exec("ps ax | grep php | {$filtro_x_proyecto} grep {$script}", $listado_procesos);
		if(count($listado_procesos) !== '0'){
			$i	= count($listado_procesos);
			unset($listado_procesos[--$i]);
		}
		return (count($listado_procesos) >= static::HIJOS);
	}

/**
 * Evalua si un proceso especifico ya se esta ejecutando.
 * Devuelve `true`, en caso de estar corriendo, `false` en caso contrario.
 *
 * @param string	$accion	- nombre de la accion que se desea evaluar.
 * @return bool
 */
	final static public function ProcessAlive($accion=null){
		if(!is_string($accion) || empty($accion)){
			throw new \Exception("El nombre de la accion debe ser un string.", 1);
		}
		$filtro_x_proyecto	= '';
		if(defined('BASE_PATH')){
			$path				= constant('BASE_PATH');
			$filtro_x_proyecto	= "grep '{$path}' |";
		}
		$script	= constant('CONSOLA_FILE');
		exec("ps ax | grep php | {$filtro_x_proyecto} grep {$script} | grep '{$accion}'", $listado_procesos);
		if(count($listado_procesos) !== '0'){
			$i	= count($listado_procesos);
			unset($listado_procesos[--$i]);
		}
		return !empty($listado_procesos);
	}

/**
 * Se usa para evaluar si existe una instancia del mismo proceso que se esta intentando ejecutar.
 * Devuelve `true`, en caso existir un proceso previo corriendo, `false` en caso contrario.
 *
 * @param string	$accion	- nombre de la accion que se desea evaluar.
 * @return bool
 */
	final static public function ClonadoProcessAlive($accion=null){
		$listado_procesos	= [];
		if(!is_string($accion) || empty($accion)){
			throw new \Exception("El nombre de la accion debe ser un string.", 1);
		}
		$filtro_x_proyecto	= '';
		if(defined('BASE_PATH')){
			$path				= constant('BASE_PATH');
			$filtro_x_proyecto	= "grep '{$path}' |";
		}
		$script	= constant('CONSOLA_FILE');
		exec("ps ax | grep php | {$filtro_x_proyecto} grep {$script} | grep '{$accion}'", $listado_procesos);
		if(count($listado_procesos) != '0'){
			$i	= count($listado_procesos);
			if(preg_match('/sudo/', $listado_procesos[0])){
				array_shift($listado_procesos);
			}
			array_shift($listado_procesos);
			array_shift($listado_procesos);
		}
		return !empty($listado_procesos);
	}

/**
 * Mata el proceso actual que lo llame.
*/
	final public function matarProceso(){
		posix_kill(posix_getpid(), SIGKILL);
		exit;
	}
}