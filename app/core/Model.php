<?php
defined('ROOT_DIR') || exit;

abstract class Model
{
	protected static $_driver;
	protected static $_target;
	protected static $_pk = DB_OBJECT_KEY;

	private $_reflect;
	private $_properties = array();

	public $id;

	/*###################################################*/
	public function __construct($target, $driver = DB_DRIVER, $pk = null)
	{
		$this->_reflect = new ReflectionClass($this);
		$this->_driver = $driver;
		$this->_target = $target;
		if(is_null($pk))
			$this->_pk = DB_OBJECT_KEY;
		else
			$this->_pk = $pk;
	}

	public function properties($name, $value = null)
	{
		static $props = array();
		if (is_null($value)) {
			if (isset($props[$name])) {
				return $props[$name];
			} else return null;
		} else {
			return $props[$name] = $value;
		}
	}

	public function getData($result = 'array')
	{
		$fields = $this->_reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		if ($isArray = ($result == 'array'))
			$data = array();
		else
			$data = new stdClass();
		foreach ($fields as $field) {
			$name = $field->getName();
			if ($isArray)
				$data[$name] = $this->$name;
			else
				$data->$name = $this->$name;
		}
		if (in_array($result, array('both', 'ArrayObject')))
			$data = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
		return $data;
	}

	public function __call($name, $arguments = array())
	{
		return call_user_func(array($this->_driver, $name), $arguments);
	}

	public function __set($name, $value)
	{
		$properties = $this->_reflect->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				$this->_properties[$name] = $value;
				return;
			}
		$this->$name = $value;
	}

	public function __get($name)
	{
		$properties = $this->_reflect->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return $this->_properties[$name];
			}
		return $this->$name;
	}

	public function __isset($name)
	{
		$properties = $this->_reflect->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return isset($this->_properties[$name]);
			}
		return isset($this->$name);
	}

	/*###################################################*/
	protected static function init($target, $driver = DB_DRIVER, $pk = null)
	{
		self::$_driver = $driver;
		self::$_target = $target;
		if(!is_null($pk)) self::$_pk = $pk;
	}

	public static function attributes($name, $value = null)
	{
		static $attrs = array();
		if (is_null($value)) {
			if (isset($attrs[$name])) {
				return $attrs[$name];
			} else return null;
		} else {
			return $attrs[$name] = $value;
		}
	}

	public static function __callStatic($name, $arguments = array())
	{
		return call_user_func(array(self::$_driver, $name), $arguments);
	}
}