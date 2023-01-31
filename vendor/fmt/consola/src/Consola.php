<?php
namespace FMT;

class ColaTarea extends \FMT\Consola\Modelo\ColaTarea {
	
}
class Consola extends \FMT\Consola\Controlador\ConsolaCore {
/**
 * Este proceso se encarga de lanzar los procesos pendientes en base datos con los parametros correspondientes.
 * Se ejecuta con `sudo -u www-data php cron.php cola_tareas  2> /dev/null &`
 *
 * @return void
 */
	final public function accion_cola_tareas(){
		if(static::ClonadoProcessAlive('cola_tareas')){
			exit;
		}
		$run	= true;
		while ($run) {
			$tareas	= ColaTarea::listarPendientes();
			if(count($tareas) == 0){
				$run	= false;
				continue;
			}
			foreach ($tareas as $tarea) {
				static::EjecutarAccion($tarea->accion, $tarea->parametros);
			}
			sleep(2);
		}
	}
}
