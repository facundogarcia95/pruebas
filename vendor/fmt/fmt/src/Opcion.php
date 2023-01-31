<?php
namespace FMT;
/**
*Clase complementaria de Menu, construye las opciones que constituyen a un menu.
*/
class Opcion {
	/** @var string $nombre Nombre de la opcion que se mostrará en el menu.  */
	protected $nombre;
  
	/**@var  array $columnas Array que contiene los elementos de cada una de las columnas que  forman a la opcion */
	protected $columnas;
	
	/**@var string $html Contiene el código html de la estructura de la opcion que varia segun la cantidad de columnas o si es solo un link.   */
  	protected $html;
  
  	/**@var string  $titulo_template Ruta al template de titulo . Es el separador de  categorias. */
	protected $titulo_template;
	
	/**@var string $link_template Ruta al template de link. Es el elemento que linkea a la funcionalidad, puede agruparse bajo las categorias. */
  	protected $link_template;
  
	/**@var string $extras_template Ruta del template que agrega toolstips a los links */
	protected $extras_template;
  
	/**@var string $divisor Ruta al template del divisor entre categorias. */
	protected $divisor;
  
	/**@var  int $columna_actual  Define la columna sobre la que se agregan elementos. */
	protected $columna_actual = 0;

	/**@var  int $index  Indice para el reemplazo de los elementos internos. */
	public $index = 0;
	
	/**
	*Constantes de clase que definen las columnas serteables para el menu.
	*/
	const COLUMNA1 = 1;
	const COLUMNA2 = 2;
	const COLUMNA3 = 3;

	/**
	* Constructor de la clase
	* @param string $nombre Nombre de la opcion.
	* @return void
	*/
	public function __construct($nombre) {
		$raiz = __DIR__;
		$this->nombre = $nombre;
		$this->titulo_template =  $raiz . '/vistas/templates/titulo.html';
		$this->link_template    = $raiz . '/vistas/templates/link.html';
		$this->extras_template = file_get_contents($raiz . '/vistas/templates/extras.html');
		$this->divisor = file_get_contents($raiz . '/vistas/templates/divisor.html');
	}

	/**
	*Agrega una categoria a la columna definida.
	*@param string  $nombre Nombre de la categoria que se agrega.
	*@param int        $columna Id de la columna a la que se agrega la categoria.
	*@return object  Retorna la instancia actual. 
	*/
	public function agregar_titulo($nombre, $columna = self::COLUMNA1) {
    $this->columnas[$columna]['LINKS_COL_'.$columna] = (!isset($this->columnas[$columna])) ? '' : $this->columnas[$columna]['LINKS_COL_'.$columna] ;
		$this->columnas[$columna]['LINKS_COL_'.$columna]  .= ($this->columnas[$columna]['LINKS_COL_'.$columna]  != '') ? $this->divisor : '';
    $this->columnas[$columna]['LINKS_COL_'.$columna]  .= (string) new Template($this->titulo_template,['TITULO_NOMBRE' => $nombre], ['CLEAN' =>false]);
		return $this;
	}

	/**
	*Agrega una link a la columna definida.
	*@param string  $nombre Nombre del link que se agrega.
	*@param string  $link Ruta a la que apunta el link.
	*@param int       $columna Id de la columna a la que se agrega la categoria.
	*@return object  Retorna la instancia actual. 
	*/
	public function agregar_link($nombre, $link, $columna = self::COLUMNA1) {
    	$this->index++; 
 		$this->columna_actual = $columna;
		$this->columnas[$columna]["LINKS_COL_$columna"] = (!isset($this->columnas[$columna])) ? '' : $this->columnas[$columna]['LINKS_COL_'.$columna];
    	$this->columnas[$columna]["LINKS_COL_$columna"] .= (string) new Template($this->link_template,['LINK_NOMBRE' => $nombre,'LINK' => $link, 'NUM' => $this->index],['CLEAN' =>false]);
    	$this->columnas[$columna]["LINK_NOMBRE_{$this->index}"] = $nombre;
    	$this->columnas[$columna]["LINK_{$this->index}"] = $link; 
		return $this;
	}

	/**
	*Agrega un tooltip al  último link agregado.
	*@param string $text Texto del tooltip que se agrega.
	*@return object  Retorna la instancia actual. 
	*/
	public function agregar_tooltip($text) {
    	$this->columnas[$this->columna_actual]["EXTRAS_{$this->index}"] = preg_replace('/\{\{\{TOOLTIPTEXT\}\}\}/', $text, $this->extras_template );
    	return $this;
	}

	/**
	*Agrega un icono al  último link agregado.
	*@param string $icon Define las clases necesarias para mostrar un icono. Ej. "fa fa-plane" .
	*@return object  Retorna la instancia actual. 
	*/
	public function agregar_icono($icon) {
    	$this->columnas[$this->columna_actual]["ICON_{$this->index}"] = $icon;
		return $this;
	}

	/**
	*Agrega un link único a una opción.
	*@param string $link Ruta a la que apunta el link.
	*@return object  Retorna la instancia actual. 
	*/
	public function agregar_opcion_link($link) {
		$this->index++;
    	$this->columnas[0]['LINKS_COL_0'] = $link;
		return $this;
	}

	/**
	*Método mágico que renderisa la opcion y genera un bloque de código html. 
	*@param void
	*@return string Bloque de código html.
	*/
	public function __toString() {
		$raiz = __DIR__;
		$vars = [];
		$cant_col = (isset($this->columnas[0])) ? 0 : count($this->columnas);

		switch ($cant_col) {
			case 0:
        		$vars = $this->columnas[0];    
				$this->html = new Template($raiz . '/vistas/templates/menu_opcion_link.html', $vars,['CLEAN' =>false]);
  				$this->html->set_tag('NUM' ,$this->index);
				break;
			case 1:
        		$vars = $this->columnas[1];
				$this->html = new Template($raiz . '/vistas/templates/menu_columna_simple.html', $vars,['CLEAN' =>false]);
				break;
			default:
				$vars['NUM_COL'] = $cant_col;
				for ($i = 1; $i <= $cant_col; $i++) {
					$vars['COL'][$i] = ['COLN' => $i, '12DIV_NUM_COL' => 12 / $cant_col];
          			$vars += $this->columnas[$i];
      			}
				$this->html = new Template($raiz . '/vistas/templates/menu_columna_multiple.html', $vars,['CLEAN' =>false]);
				break;
		}
		$this->html->set_tag('OPCION_NOMBRE', $this->nombre);
		return "{$this->html}";
	}
}