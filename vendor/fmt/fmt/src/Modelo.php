<?php
namespace FMT;
abstract class Modelo{

	public $errores;

	/**
	 * Controlar si los datos en las propiedad son validos segun las reglas de negocio
	 * @return bool
	 */
	abstract public function validar();

	/**
	 * Guardar los datos, usar cuando se arranca desde una instancia en blanco
	 * @return bool
	 */
	abstract public function alta();

	/**
	 * Eliminar el objeto
	 * @return bool
	 */
	abstract public function baja();

	/**
	 * Actualizar información de un objeto existente
	 * @return bool
	 */
	abstract public function modificacion();

	//TODO: en PHP7 definir estos como abstracto
	/** @noinspection PhpDocSignatureInspection */
	/**
	 * devuelve una lista de objetos
	 * @return array
	 */
	static public function listar(){trigger_error("Abstract method not defined", E_USER_ERROR);exit();}

	//TODO: en PHP7 definir estos como abstracto
	/** @noinspection PhpDocSignatureInspection */
	/**
	 * devuelve una instancia del objeto
	 * Si se pasa un valor por defecto (implementation dependant, se sugiere null), devuelve una instancia en blanco con valores por defecto
	 * Si se pasa un $id valido, devuelve los datos que le corresponde
	 * Si el $id no se encuentra, devuelve null
	 * @param $id
	 * @return static|null
	 */

	static public function obtener($id){trigger_error("Abstract method not defined", E_USER_ERROR);exit();}

	/**
	 * El constructor se define como protected a propósito para que no se creen instancias manuales. Usar ::obtener() para obtener una instancia nueva.
	 */
	protected function __construct(){}

	/**
	 * @param $key
	 * @param $value
	 * @throws \ErrorException
	 */
	public function __set($key,$value)
	{
		throw new \ErrorException('ERROR: Variable asignada "'.$key.'" no existe.');
	}

	/**
	 * @param $key
	 * @throws \ErrorException
	 */
	public function __get($key)
	{
		throw new \ErrorException('ERROR: Variable consultada "'.$key.'" no existe.');
	}
}