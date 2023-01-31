<?php
namespace FMT\Helper;

class Curl {
	protected $instance;
	protected $endpoint;

	public function __construct($endpoint,$ssl_verifypeer) {
		$this->instance = curl_init();
		$this->endpoint = $endpoint;
		curl_setopt($this->instance, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->instance, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
	}

	public function send($data = null) {
		curl_setopt($this->instance, CURLOPT_URL, $this->endpoint);

		if ($data) {
			curl_setopt($this->instance, CURLOPT_POST, 1);
			curl_setopt($this->instance, CURLOPT_POSTFIELDS, http_build_query($data));
		}

		$respuesta = curl_exec($this->instance);

		return $respuesta;
	}

}
