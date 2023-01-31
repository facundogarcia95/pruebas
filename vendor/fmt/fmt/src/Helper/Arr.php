<?php
namespace FMT\Helper;

class Arr {

	public static $delimiter = '.';

	/**
	 * Obtener un valor de un array
	 * @param   array $array array to extract from
	 * @param   string $key key name
	 * @param   mixed $default default value
	 * @return  mixed
	 */
	public static function get($array, $key, $default = null) {
		if ($array instanceof \ArrayObject) {
			// This is a workaround for inconsistent implementation of isset between PHP and HHVM
			// See https://github.com/facebook/hhvm/issues/3437
			return $array->offsetExists($key) ? $array->offsetGet($key) : $default;
		} else {
			return isset($array[$key]) ? $array[$key] : $default;
		}
	}


	/**
	 * Revisa si la variable es un array o funciona como tal
	 * @param   mixed $value value to check
	 * @return  boolean
	 */
	public static function is_array($value) {
		if (is_array($value)) {
			// Definitely an array
			return true;
		} else {
			// Possibly a Traversable object, functionally the same as an array
			return (is_object($value) AND $value instanceof \Traversable);
		}
	}


	/**
	 * Obtiene un valor de un array, usando . como separador niveles.
	 * Es el equivalente a Arr::get() para arrays multidimensionales
	 *
	 *     // Get the value of $array['foo']['bar']
	 *     $value = Arr::path($array, 'foo.bar');
	 *
	 * Using a wildcard "*" will search intermediate arrays and return an array.
	 *
	 *     // Get the values of "color" in theme
	 *     $colors = Arr::path($array, 'theme.*.color');
	 *
	 *     // Using an array of keys
	 *     $colors = Arr::path($array, array('theme', '*', 'color'));
	 *
	 * @param   array $array array to search
	 * @param   mixed $path key path string (delimiter separated) or array of keys
	 * @param   mixed $default default value if the path is not set
	 * @param   string $delimiter key path delimiter
	 * @return  mixed
	 */
	public static function path($array, $path, $default = null, $delimiter = null) {
		if (!Arr::is_array($array)) {
			// This is not an array!
			return $default;
		}
		if (is_array($path)) {
			// The path has already been separated into keys
			$keys = $path;
		} else {
			if (array_key_exists($path, $array)) {
				// No need to do extra processing
				return $array[$path];
			}
			if ($delimiter === null) {
				// Use the default delimiter
				$delimiter = Arr::$delimiter;
			}
			// Remove starting delimiters and spaces
			$path = ltrim($path, "{$delimiter} ");
			// Remove ending delimiters, spaces, and wildcards
			$path = rtrim($path, "{$delimiter} *");
			// Split the keys by delimiter
			$keys = explode($delimiter, $path);
		}
		do {
			$key = array_shift($keys);
			if (ctype_digit($key)) {
				// Make the key an integer
				$key = (int)$key;
			}
			if (isset($array[$key])) {
				if ($keys) {
					if (Arr::is_array($array[$key])) {
						// Dig down into the next part of the path
						$array = $array[$key];
					} else {
						// Unable to dig deeper
						break;
					}
				} else {
					// Found the path requested
					return $array[$key];
				}
			} elseif ($key === '*') {
				// Handle wildcards
				$values = [];
				foreach ($array as $arr) {
					if ($value = Arr::path($arr, implode('.', $keys))) {
						$values[] = $value;
					}
				}
				if ($values) {
					// Found the values requested
					return $values;
				} else {
					// Unable to dig deeper
					break;
				}
			} else {
				// Unable to dig deeper
				break;
			}
		} while ($keys);
		// Unable to find the value requested
		return $default;
	}

	/**
	 * Set a value on an array by path.
	 *
	 * @see Arr::path()
	 * @param array $array Array to update
	 * @param string $path Path
	 * @param mixed $value Value to set
	 * @param string $delimiter Path delimiter
	 */
	public static function set_path(& $array, $path, $value, $delimiter = null) {
		if (!$delimiter) {
			// Use the default delimiter
			$delimiter = Arr::$delimiter;
		}
		// The path has already been separated into keys
		$keys = $path;
		if (!is_array($path)) {
			// Split the keys by delimiter
			$keys = explode($delimiter, $path);
		}
		// Set current $array to inner-most array path
		while (count($keys) > 1) {
			$key = array_shift($keys);
			if (ctype_digit($key)) {
				// Make the key an integer
				$key = (int)$key;
			}
			if (!isset($array[$key])) {
				$array[$key] = [];
			}
			$array = &$array[$key];
		}
		// Set key on inner-most array
		$array[array_shift($keys)] = $value;
	}


}