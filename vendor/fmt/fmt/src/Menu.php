<?php
namespace FMT;

class Menu {
	protected $base;
	/**
	 * @var Opcion[]
	 */
	protected $opciones = [];
	protected $indice = 0;
	protected $manual;
	protected $logo;
	protected $salir;
	protected $dev;


	public function __construct() {
		$raiz = __DIR__;
		$this->base = $raiz . '/vistas/templates/menu_base.html';
	}

	public function activar_dev() {
		$this->dev = true;
	}

	public function agregar_opcion($nombre) {
		$indice = $this->indice;
		$this->opciones[$indice] = new Opcion($nombre);
		$this->indice++;
		return $this->opciones[$indice];
	}

	public function agregar_manual($link) {
		$this->manual = $link;
	}

	public function agregar_logo($link) {
		$this->logo = $link;
	}

	public function agregar_salir($link) {
		$this->salir = $link;
	}

	public function agregar_link($nombre, $link) {
		$indice = $this->indice;
		$this->opciones[$indice] = new Opcion($nombre);
		$this->opciones[$indice]->agregar_opcion_link($link);
		$this->indice++;
		return $this->opciones[$indice];
	}

	public function __toString() {
		$html_opciones = '';
		foreach ($this->opciones as $value) {
			$html_opciones .= $value;
		}
		$vars['OPCIONES'] = $html_opciones;
		if (!is_null($this->manual)) {
			$vars['MANUAL'][] = ['LINK_MANUAL' => $this->manual];
		}
		if (!is_null($this->salir)) {
			$vars['SALIR'][] = ['LINK_SALIR' => $this->salir];
		}

		if ($this->dev) {
			$vars['DEV'][] = ['TEXT_DEV' => 'VERSION DE DESARROLLO'];
		}

		$vars['LOGO_MINISTERIO'] = ($this->logo) ? $this->logo : 'https://www.transporte.gob.ar/_img/logo_ministerio_grande_blanco.png';

		$html = new Template($this->base, $vars);

		return "$html"; //lo pasamos a string para que renderice Template
	}
}