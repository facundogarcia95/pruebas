<?php
namespace FMT;

use FMT\Helper\Arr;

class Request {
	
	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	
	protected $_requested_with;
	protected $_method;
	private $_body;
	/**
	 * @var array    query parameters
	 */
	protected $_get = [];
	/**
	 * @var array    post parameters
	 */
	protected $_post = [];


	public function __construct() {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			// Use the server request method
			$method = $_SERVER['REQUEST_METHOD'];
		} else {
			// Default to GET requests
			$method = static::METHOD_GET;
		}
		if ($method !== static::METHOD_GET) {
			// Ensure the raw body is saved for future use
			$body = file_get_contents('php://input');
		}

		$this->query($_GET)
			->post($_POST)
			->requested_with(Arr::get($_SERVER, 'HTTP_X_REQUESTED_WITH'));
		
		if (isset($method)) {
			// Set the request method
			$this->method($method);
		}
		if (isset($body)) {
			// Set the request body (probably a PUT type)
			$this->body($body);
		}
	}

	/**
	 * Returns whether this is an ajax request (as used by JS frameworks)
	 *
	 * @return  boolean
	 */
	public function is_ajax() {
		return ($this->requested_with() === 'xmlhttprequest');
	}
	
	/**
	 * Returns if the current request was POSTed
	 * @return boolean
	 */
	public function is_post() {
		return ($this->method() === static::METHOD_POST);
	}
	
	/**
	 * Returns if the current request was GETted
	 * @return boolean
	 */
	public function is_get() {
		return ($this->method() === static::METHOD_GET);
	}

	/**
	 * Gets and sets the requested with property, which should
	 * be relative to the x-requested-with pseudo header.
	 *
	 * @param   string $requested_with Requested with value
	 * @return  mixed
	 */
	public function requested_with($requested_with = null) {
		if ($requested_with === null) {
			// Act as a getter
			return $this->_requested_with;
		}
		// Act as a setter
		$this->_requested_with = strtolower($requested_with);
		return $this;
	}


	/**
	 * Gets or sets the HTTP method. Usually GET, POST, PUT or DELETE in
	 * traditional CRUD applications.
	 *
	 * @param   string $method Method to use for this request
	 * @return  mixed
	 */
	public function method($method = null) {
		if ($method === null) {
			// Act as a getter
			return $this->_method;
		}
		// Act as a setter
		$this->_method = strtoupper($method);
		return $this;
	}

	/**
	 * Gets or sets the HTTP body of the request. The body is
	 * included after the header, separated by a single empty new line.
	 *
	 * @param   string $content Content to set to the object
	 * @return  mixed
	 */
	public function body($content = null) {
		if ($content === null) {
			// Act as a getter
			return $this->_body;
		}
		// Act as a setter
		$this->_body = $content;
		return $this;
	}

	/**
	 * Gets or sets HTTP query string.
	 *
	 * @param   mixed $key Key or key value pairs to set
	 * @param   string $value Value to set to a key
	 * @return  mixed
	 * @uses    Arr::path
	 */
	public function query($key = null, $value = null) {
		if (is_array($key)) {
			// Act as a setter, replace all query strings
			$this->_get = $key;
			return $this;
		}
		if ($key === null) {
			// Act as a getter, all query strings
			return $this->_get;
		} elseif ($value === null) {
			// Act as a getter, single query string
			return Arr::path($this->_get, $key);
		}
		// Act as a setter, single query string
		$this->_get[$key] = $value;
		return $this;
	}

	/**
	 * Gets or sets HTTP POST parameters to the request.
	 *
	 * @param   mixed $key Key or key value pairs to set
	 * @param   string $value Value to set to a key
	 * @return  mixed
	 * @uses    Arr::path
	 */
	public function post($key = null, $value = null) {
		if (is_array($key)) {
			// Act as a setter, replace all fields
			$this->_post = $key;
			return $this;
		}
		if ($key === null) {
			// Act as a getter, all fields
			return $this->_post;
		} elseif ($value === null) {
			// Act as a getter, single field
			return Arr::path($this->_post, $key);
		}
		// Act as a setter, single field
		$this->_post[$key] = $value;
		return $this;
	}


}