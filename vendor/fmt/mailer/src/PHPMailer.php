<?php
namespace PHPMailer\PHPMailer;

use FMT\Mailer;

class PHPMailer extends Mailer {
	public $Host;
	public $Port;
	public $Username;
	public $Password;

	public $Subject;
	public $Body;
	public $AltBody;

	protected $exceptions = false;

	public function __construct($e = null) {
		$this->exceptions = $e;
	}

	/**
	 * @return bool
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	public function send() {
		$this->servidor = $this->Host;
		$this->puerto = $this->Port;
		$this->usuario = $this->Username;
		$this->clave = $this->Password;

		$this->titulo = $this->Subject;
		$this->cuerpo = $this->Body;
		$this->cuerpoAlt = $this->AltBody;

		$ret = $this->enviar();
		if (!$ret && $this->exceptions) {
			throw new Exception($this->ErrorInfo);
		}
		return $ret;
	}

	public function setFrom($correo, $nombre = '') {
		$this->setearRemitente($correo, $nombre);
	}

	public function addAddress($correo, $nombre = '') {
		$this->agregarDestinatario($correo, $nombre);
	}

	public function addCC($correo, $nombre = '') {
		$this->agregarCopia($correo, $nombre);
	}

	public function addBCC($correo, $nombre = '') {
		$this->agregarCopiaOculta($correo, $nombre);
	}

	public function addAttachment($url, $nombre = '') {
		$this->agregarAdjunto($url, $nombre);
	}

	public function clearAddresses() {
		$this->limpiarDestinatarios();
	}

}
