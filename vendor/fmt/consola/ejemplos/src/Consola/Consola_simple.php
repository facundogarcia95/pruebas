<?php
namespace ConsolaEjemplos\Consola;
use \FMT\Consola;

/**
 * Acciones para procesar por cron del servidor.
*/
class Consola_simple extends Consola {

	public function accion_help(){
		if (static::ClonadoProcessAlive('help')) {
			exit;
		}
		$consola_file	= constant('CONSOLA_FILE');
		$interprete		= constant('PHP_INTERPRETE');
		$tmp_dir		= static::getDirectorioTMP();
		$ayuda	= <<<TXT
Directorio temporal: {$tmp_dir}

Este sistema posee los metodos :
 - {$interprete} {$consola_file} cola_tareas: Para usar en cron de sistema. Busca, administra y procesa las tareas pendientes almacenadas en base de datos.

 - {$interprete} {$consola_file} test_proceso: Test de proceso principal simple.
  
 - {$interprete} {$consola_file} test_proceso_secundario: Test de proceso secundario. Posee control de tiempo de vida para evitar que se ejecute infinitamente.
\n
TXT;
		echo $ayuda;		
		$this->matarProceso();
		exit;
	}

/**
 * Test de proceso principal simple.
 * Se ejecuta con `sudo -u www-data php cron.php test_proceso  2> /dev/null &`
 *
 * @return void
 */
	public function accion_test_proceso(){
		if (static::ClonadoProcessAlive('test_proceso')) {
			exit;
		}
		$params				= $this->getParams();

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
				$this->matarProceso();
				exit;
			}
			// Si la existen N (static::HIJOS) en ejecucion,  espera a que el servidor se libere para que se ejecute el nuevo proceso.
			// Manteniendo el proceso principal vivo hasta que ser terminen las tareas pedidas.
			static::EjecutarAccion('test_proceso_secundario', $test_data);
			$i++;
			// Darle tiempo para que se realicen los procesos y no se sature el servidor.
			usleep(100);
		} while($i < 10);
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
					'controlador'		=> 'Consola_simple',
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
