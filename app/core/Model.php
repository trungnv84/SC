<?php
defined('ROOT_DIR') || exit;

abstract class Model
{
	protected static $_driver = DB_DRIVER;
	protected static $_target = null;
	protected static $_pk = DB_OBJECT_KEY;

	protected $_reflect;
	protected $_properties = array();

	public $id;

	/*###################################################*/
	public function __construct($target = null, $driver = DB_DRIVER, $pk = DB_OBJECT_KEY)
	{
		$this->_reflect = new ReflectionClass($this);
		$this->_driver = $driver;
		$this->_target = (is_null($target)?static::getSource():$target);
		$this->_pk = $pk;
	}

	public function &setTarget($target)
	{
		$this->_target = $target;
		return $this;
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
		call_user_func(array($this->_driver, 'setInstanceName'), $this->_target);
		return call_user_func_array(array($this->_driver, $name), $arguments);
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
	protected static function init($target = null, $driver = DB_DRIVER, $pk = DB_OBJECT_KEY)
	{
		static::$_driver = $driver;
		static::$_target = (is_null($target)?static::getSource():$target);
		static::$_pk = $pk;
	}

	public abstract function getSource();

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
		call_user_func(array(static::$_driver, 'setInstanceName'), self::$_target);
		return call_user_func_array(array(static::$_driver, $name), $arguments);
	}
}