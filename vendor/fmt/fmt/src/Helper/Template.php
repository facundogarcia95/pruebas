<?php
namespace FMT\Helper;

class Template {

	/**
	 * Genera un array formateado para que FMT\Template genere un campo select seleccionado.
	 * 
	 * @param array $content lista de los elementos del select
	 *
	 * El parametro content tiene dos formatos validos.
	 * Para selects simples
	 * 			['id' => 'nombre'];
	 * Para selects con elementos historicos, es decir muestra elementos borrados siempre que esten seleccionados.
	 * 			['id' => ['nombre' => 'nombre elemento', 'borrado' =>'0|1']];
	 * @param string|array $selected  id de uno o varios elementos seleccionados
	 * @return array
	 */
	static public function select_block( $content, $selected = '' ) {
		$comb = [];
		if (!is_array($selected)){
			$selected = [$selected];
		}
		foreach ($content as $id => $value) {
			$marca = '';
			$borrado = '';
			if (in_array($id,$selected)) {
				$marca = 'selected';

				if (isset($value['borrado']) && $value['borrado'] == 1) {
					$borrado = ' disabled';
					$value['borrado'] = 0;
				}
			}

			if (isset($value['borrado'])) {
				if ($value['borrado'] == 0) {
					$comb[] = ['TEXT' => $value['nombre'], 'VALUE' => $id, 'SELECTED' => $marca, 'BORRADO' => $borrado];
				}
			} else {
				$comb[] = ['TEXT' => $value, 'VALUE' => $id,'SELECTED' => $marca];
			}
		}
		return $comb;
	}
}