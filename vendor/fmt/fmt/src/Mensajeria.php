<?php
namespace FMT;

class Mensajeria {

	private $id_modulo;
	private $controlador;
	private $accion;

	const TIPO_ERROR = 1;
	const TIPO_AVISO = 2;

	const KEY_CONTENIDO = '_contenido';
	const KEY_SESSION = 'mensajeria';

	public function __construct($id_modulo, $controlador, $accion) {
		$this->id_modulo = $id_modulo;
		$this->controlador = $controlador;
		$this->accion = $accion;
	}

	/**
	 * Agrega un mensaje
	 * @param string $mensaje mensaje a mostrar al usuario
	 * @param string $tipo tipo de mensaje a mostrar al usuario
	 * @param string $controlador para que controlador ejecutar el mensaje
	 * @param string $accion para que accion ejecutar el mensaje
	 * @throws \Exception
	 */
	public function agregar($mensaje, $tipo, $controlador = null, $accion = null) {
		$this->controlar_datos();

		switch (true) {
			case (!is_null($controlador) && !is_null($accion)):
				$_SESSION[static::KEY_SESSION][$this->id_modulo][$controlador][$accion][] = ['mensaje' => $mensaje, 'tipo' => $tipo];
				break;

			case(!is_null($controlador)):
				$_SESSION[static::KEY_SESSION][$this->id_modulo][$controlador][static::KEY_CONTENIDO][] = ['mensaje' => $mensaje, 'tipo' => $tipo];
				break;
			default:
				$_SESSION[static::KEY_SESSION][$this->id_modulo][static::KEY_CONTENIDO][] = ['mensaje' => $mensaje, 'tipo' => $tipo];
				break;
		}
	}

	/**
	 * Obtiene todos los Mensajes guardados y los borra
	 * @return array
	 * @throws \Exception
	 */
	public function obtener() {
		$this->controlar_datos();

		$mensajes = [];

		if (isset($_SESSION[static::KEY_SESSION][$this->id_modulo][$this->controlador][$this->accion])) {
			$mensajes = array_merge_recursive($mensajes, $_SESSION[static::KEY_SESSION][$this->id_modulo][$this->controlador][$this->accion]);
			unset($_SESSION[static::KEY_SESSION][$this->id_modulo][$this->controlador][$this->accion]);
		}

		if (isset($_SESSION[static::KEY_SESSION][$this->id_modulo][$this->controlador][static::KEY_CONTENIDO])) {
			$mensajes = array_merge_recursive($mensajes, $_SESSION[static::KEY_SESSION][$this->id_modulo][$this->controlador][static::KEY_CONTENIDO]);
			unset($_SESSION[static::KEY_SESSION][$this->id_modulo][$this->controlador][static::KEY_CONTENIDO]);
		}

		if (isset($_SESSION[static::KEY_SESSION][$this->id_modulo][static::KEY_CONTENIDO])) {
			$mensajes = array_merge_recursive($mensajes, $_SESSION[static::KEY_SESSION][$this->id_modulo][static::KEY_CONTENIDO]);
			unset($_SESSION[static::KEY_SESSION][$this->id_modulo][static::KEY_CONTENIDO]);
		}
		return $mensajes;
	}

	/**
	 * @throws \Exception
	 */
	protected function controlar_datos() {
		if (is_null($this->id_modulo)) {
			throw new \Exception('Mensajería tiene mal configurado su contexto actual.');
		}
	}
}