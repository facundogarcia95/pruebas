<?php
namespace FMT;

use FMT\Helper\Arr;

/**
 * Clase para manejo y composicion de templates.
 *
 */
class Template {
	/** 
	 * Contiene el código html en el que se reemplazan los tags.
	 * @var string $html
	 */
	protected	$html;

	/** 
	 * Contiene las etiquetas que se tienen que buscar para reemplazar ('tags') y 
	 * los valores de reemplazo ('values').
	 * @var array $replace
	 */
	protected	$replace;

	/** 
	 * Array que contiene la información que recibe el constructor para procesar y obtener tags y valores.
	 * @var array $vars
	 */
	protected	$vars;

	/** 
	 * Es un flag que le indica a la clase se debe eliminar todos los tags y bloques no reemplazados.
	 * @var bool $clean
	 */
	protected	$clean;

	/** 
	 * Es la ruta de acceso al template base sobre el que se hara el reemplazo.
	 * @var string $base_path
	 */
	protected	$base_path;
	
	/**
	 * Constructor de la clase
	 * @param string $base_path Ruta de la ubicacion del archivo base para realizar el templating,
	 * 	puede ser html o php.
	 * @param array $vars Arreglo que contiene los tags y valor que se reemplazarán en el template.
	 * @param array $options Arreglo que contiene las configuraciones extras
	 * @return void.
	 */ 
	public function __construct($base_path, $vars =null, $options=[]) {
		$this->base_path = $base_path;
		$this->vars = $vars;
		$this->clean = Arr::get($options, 'CLEAN', true);
	}

	/**
	 * Guarda una nueva etiqueta de busqueda con su correspondiente valor de reemplazo. 
	 * @param string $tag nombre de la etiqueta de busqueda.
	 * @param string $value contenido con el que se reemplazará a la etiqueta.
	 * @return void
	 * 
	 */
	public function set_tag($tag,$value) {
		preg_match('/^[A-Z_]*\./', $tag, $aux);
		$tag = (isset($aux[0])) ? str_replace($aux[0], strtoupper($aux[0]), $tag) : strtoupper($tag);
		$this->replace['tags'][] = '{{{' . $tag . '}}}';
		$this->replace['values'][] = $value;
	}

	/**
	 * Ejecuta el reemplazo de las etiquetas por los valores guardados sobre el template base.
	 * Tambien elimina todas las etiquetas simples y bloques no reemplazadas.  
	 * @param void
	 * @return void 
	 */
	protected function render() {
		if (isset($this->replace['tags'])){
			$this->html = str_replace($this->replace['tags'], $this->replace['values'], $this->html);
		}
		if ($this->clean) {
			preg_match_all('/\{\{\{[A-Z0-9_]*?\}\}\}/', $this->html, $tags);
			foreach ($tags[0] as $tag) {
				$tag = str_replace(['{', '}'], '', $tag);
				if($this->is_block($tag, $this->html)){
					$this->html = preg_replace('/\{\{\{' . $tag . '\}\}\}[\w\W]*?\{\{\{\/' . $tag . '\}\}\}/', '', $this->html);
				}
			}
			$this->html = preg_replace('/\{\{\{[A-Za-z0-9_\.]*?\}\}\}/', '', $this->html);
		}
	}

	/**
	 * Verifica si el tag suministrado es un bloque dentro de la porsion de código analizada.
	 * @param  string $tag Nombre de la etiqueta buscada.
	 * @param  string $block Bloque de codigo donde se hace la busqueda.
	 * @return bool   
	 */ 
	protected function is_block($tag, $block) {
		$tag = strtoupper($tag);
		return preg_match('/\{\{\{\/' . $tag . '\}\}\}/', $block);
	}

	/**
	 * Busca recursivamente bloques dentro de bloques para reemplazar los valores suministrados 
	 * y sustituirlos en el código base.    
	 * @param  string $tag   Nombre de la etiqueta buscada.
	 * @param  array  $rows  Valores para reemplazo en el bloque. 
	 * @param  string $block Bloque de codigo donde se hace la busqueda.
	 * @return void
	 */
	protected function serialize_sub_block($tag, $rows, &$block) {
		preg_match('/^[A-Z_]*\./', $tag, $aux);
		$tag = (isset($aux[0])) ? str_replace([$aux[0], '.'], [strtoupper($aux[0]), '\.'], $tag) : strtoupper($tag);

		if ($this->is_block($tag, $block)) {

			if (preg_match('/\{\{\{' . $tag . '\}\}\}[\w\W]*?\{\{\{\/' . $tag . '\}\}\}/', $block, $arr)) {

				$block = preg_replace('/\{\{\{' . $tag . '\}\}\}[\w\W]*?\{\{\{\/' . $tag . '\}\}\}/', '{{{SERIALICE_' . $tag . '}}}', $block);
				$sub_block = preg_replace(['/\{\{\{' . $tag . '\}\}\}/', '/\{\{\{\/' . $tag . '\}\}\}/'], '', $arr[0]);
				$code = '';
				foreach ($rows as $row) {
					$aux = $sub_block;

			    	foreach ($row as $ss_tag => $value) {
						if (is_array($value)) {
							$this->serialize_sub_block($ss_tag, $value, $aux);
						} else {
							$aux = preg_replace('/\{\{\{' . $ss_tag . '\}\}\}/', $value, $aux);
						}
					}
					$code .= $aux;
				}

				$block = preg_replace('/\{\{\{SERIALICE_' . $tag . '\}\}\}/', $code, $block);
			}
		} else {
		  foreach ($rows as $sub_tag => $value) {
		  	 $sub_tag = strtoupper($sub_tag);
		  	 if (is_array($value)) {
				$this->serialize_sub_block($sub_tag, $value, $block);
		  	 } else {
				$block = preg_replace('/\{\{\{' . $tag . '\}\}\}/', $value, $block);
		  	 }
		  }	
		}	
	}

	/**
	 * Reemplaza antes que el el resto de las etiquetas los componentes que completan la estructura
	 * del html base. 
	 * @param string $tag nombre de la etiqueta a reemplazar.
	 * @param object $value Contiene un objeto Template que entrega un string por su metodo __toString.
	 * @return void
	 */
	protected function replace_component($tag, $value) {
		$tag = strtoupper($tag);
		$this->html = preg_replace('/\{\{\{' . $tag . '\}\}\}/', $value, $this->html);
	}

	/**
	 * Genera la composición y reemplazo de todos los elementos suministrados.
	 * @param void
	 * @return string
	 */ 
	public function render_output() {
		ob_start();
		/** @noinspection PhpIncludeInspection */
		require $this->base_path;
		$this->html = ob_get_clean();

		if (is_array($this->vars)) {
			//Se pre procesan todos los valores que son de tipo objeto y se eliminan de "vars".
			foreach ($this->vars as $key => $objeto) {
				if (is_object($objeto)) {
					//Si el objeto es una instancia "Template" significa que 
					//es un bloque para componer con template base.
					if(get_class($objeto) == static::CLASS) {
						$this->replace_component($key, $objeto);
					} else {

						$clase = strtoupper($key);
						$campos = get_object_vars($objeto);
						foreach ($campos as $campo => $valor) {
							$this->vars[$clase . '.' . $campo] = $valor;
						}
					}
					unset($this->vars[$key]);
				}
			}

			foreach ($this->vars as $tag => $value) {
					if (is_array($value)) {
						$this->serialize_sub_block($tag, $value, $this->html);
					} else {
						$this->set_tag($tag, $value);
					}
			}
			$this->render();
		}
		return $this->html;
	}

	/**
	 * Genera la composición y reemplazo de todos los elementos suministrados.
	 * @param void
	 * @return string
	 */
	public function __toString() {
		return $this->render_output();
	}

}