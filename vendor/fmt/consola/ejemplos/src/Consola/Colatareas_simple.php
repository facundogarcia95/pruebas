<?php
namespace ConsolaEjemplos\Consola;
use \FMT\Consola;
use \FMT\ColaTarea;

/**
 * Acciones para procesar por cron del servidor.
*/
class Colatareas_simple extends Consola {

/**
 * Test de proceso principal simple.
 * Se ejecuta con `sudo -u www-data php cron.php test_colatareas  2> /dev/null &`
 *
 * Luego para probar la cola de tareas ejecutar `sudo -u www-data php cron.php cola_tareas  2> /dev/null &`
 *
 * @return void
 */
	public function accion_test_colatareas(){
		// Agregar una tarea
		ColaTarea::agregar('Colatareas_simple test_proceso', ['test_id' => 4]);
		// Al duplicar una tarea con los mismo parametros devuelve `false`
		if(!ColaTarea::agregar('Colatareas_simple test_proceso', ['test_id' => 4])){
			$this->debug('accion_test_cola_tareas', false);
			$this->debug('Ejemplo tarea que no se agrega en Lista de Tareas', false);
		}
		ColaTarea::agregar('Colatareas_simple test_proceso', ['test_id' => 6]);
		ColaTarea::agregar('Colatareas_simple test_proceso', ['test_id' => 8]);
	}

	public function accion_test_proceso(){
		if (static::ClonadoProcessAlive('test_proceso')) {
			exit;
		}
		$params				= $this->getParams();
		// Obtener el registro de la cola de tareas
		$tarea	= ColaTarea::obtenerPorAccion('Colatareas_simple test_proceso', $params);
		// Indicar que el proceso se inicio
		ColaTarea::tareaEjecutando($tarea);


		$this->debug('accion_test_proceso', false); // /tmp/cron-debug.log por defecto
		$this->debug(time(), false);
		$test_data	= [
			'hola'		=> 'gervasio',
			'params'	=> $params,
		];

// Ejemplo de matar el proceso ante una condicion dada y de mantaner viva la misma.
		$i	= 0;
		do{
			if($i == 3){
				ColaTarea::tareaFinalizar($tarea);
				$this->matarProceso();
				exit;
			}
			// Si la existen N (static::HIJOS) en ejecucion,  espera a que el servidor se libere para que se ejecute el nuevo proceso.
			// Manteniendo el proceso principal vivo hasta que ser terminen las tareas pedidas.
			static::EjecutarAccion('Colatareas_simple test_proceso_secundario', $test_data);
			$i++;
			// Darle tiempo para que se realicen los procesos y no se sature el servidor.
			usleep(100);
		} while($i < 10);

		ColaTarea::tareaFinalizar($tarea);
	}

/**
 * Test de proceso secundario. Posee control de tiempo de vida para evitar que se ejecute infinitamente.
 *
 * @return void
*/
	public function accion_test_proceso_secundario(){
		$test_data	= $this->getParams();

		$this->debug('accion_test_proceso_secundario', false);
		$this->debug($test_data, false);
		$this->debug(time(), false);

		$process_start	= time();
		while(true) {
			sleep(2);
			/** Realizar Harakiri luego de 12hs abs(12*60*60) */
			if(abs(time() - $process_start) >= abs(10)){ // 10 Segundos
				$datos		= [
					'controlador'		=> 'Colatareas_simple',
					'ejecucion'			=> 'sistema',
					'parametros'		=> $test_data,
					'segundos_activo'	=> abs(time() - $process_start),
				];
				$this->debug($datos, false, 'test_proceso_secundario-harakiri');
				$this->matarProceso();
			}

		}
	}
}
