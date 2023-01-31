<?php
namespace FMT;

/**
 * Class ControladorJSON. En vez de vista usa la variable resultado, y emite json, con el Content-Type adecuado
 * @package FMT
 */
abstract class ControladorJSON extends Controlador {

	protected $resultado = [];

	public function mostrar_vista() {

		// Cabecera JSON
		header('Content-Type: application/json;charset=utf-8');

		// Devuelvo Resultado JSON
		echo json_encode($this->resultado, JSON_UNESCAPED_UNICODE);
	}

}