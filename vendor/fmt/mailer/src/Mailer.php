<?php
namespace FMT;

use FMT\Helper\Curl;

class Mailer {
	public static $endpoint;
	public static $ssl_verifypeer;

	public $servidor;
	public $puerto;
	public $usuario;
	public $clave;

	public $SMTPAuth;
	public $SMTPAutoTLS;
	public $CharSet;
	protected $isSMTP;
	protected $isHTML;
	public $SMTPSecure;
	public $SMTPOptions = [];

	protected $remitente;
	protected $destinatario = [];
	protected $conCopia = [];
	protected $conCopiaOculta = [];
	protected $adjuntos = [];
	public $titulo;
	public $cuerpo;
	public $cuerpoAlt;

	public $ErrorInfo;

	public static function init($endpoint = null,$ssl_verifypeer = true) {
		if ($endpoint !== null) {
			static::$endpoint = $endpoint;
		} else {
			static::$endpoint = 'http://localhost/MailerServidor/endpoint.php';
		}
		static::$ssl_verifypeer = $ssl_verifypeer;
	}

	public function setearRemitente($correo, $nombre = '') {
		$this->remitente = ['correo' => $correo, 'nombre' => $nombre];
	}

	public function agregarDestinatario($correo, $nombre = '') {
		$this->destinatario[] = ['correo' => $correo, 'nombre' => $nombre];
	}

	public function agregarCopia($correo, $nombre = '') {
		$this->conCopia[] = ['correo' => $correo, 'nombre' => $nombre];
	}

	public function agregarCopiaOculta($correo, $nombre = '') {
		$this->conCopiaOculta[] = ['correo' => $correo, 'nombre' => $nombre];
	}

	public function agregarAdjunto($url, $nombre = '') {
		$archivo = file_get_contents($url);
		$base64 = base64_encode($archivo);

		if (!$nombre) {
			$nombre = basename($url);
		}

		$this->adjuntos[] = ['archivo' => $base64, 'nombre' => $nombre];
	}

	public function isHTML($isHTML = true) {
		$this->isHTML = $isHTML;
	}

	public function isSMTP() {
		$this->isSMTP = true;
	}

	public function limpiarDestinatarios() {
		$this->destinatario = [];
	}

	public function enviar() {
		$datos = [
			'servidor' => $this->servidor,
			'puerto' => $this->puerto,
			'usuario' => $this->usuario,
			'clave' => $this->clave,

			'isSMTP' => $this->isSMTP,
			'SMTPAuth' => $this->SMTPAuth,
			'SMTPAutoTLS' => $this->SMTPAutoTLS,
			'SMTPSecure' => $this->SMTPSecure,
			'SMTPOptions' => $this->SMTPOptions,

			'remitente' => $this->remitente,
			'destinatario' => $this->destinatario,
			'conCopia' => $this->conCopia,
			'conCopiaOculta' => $this->conCopiaOculta,
			'titulo' => $this->titulo,
			'cuerpo' => $this->cuerpo,
			'cuerpoAlt' => ($this->cuerpoAlt) ? $this->cuerpoAlt : '',
			'CharSet' => $this->CharSet,
			'isHTML' => $this->isHTML,
			'adjuntos' => $this->adjuntos,
		];

		$curl = new Curl(static::$endpoint,static::$ssl_verifypeer);
		$respuesta = $curl->send($datos);

		if (!empty($respuesta)) {
			$ret = json_decode($respuesta, true);
			if (is_array($ret) && array_key_exists('estado', $ret)) {
				if ($ret['estado'] == 400) {
					$this->ErrorInfo = $ret['error'];
					return false;
				} elseif ($ret['estado'] == 200) {
					return true;
				}
			}
		}

		$this->ErrorInfo = 'Error de aplicacion';
		return false;
	}

}