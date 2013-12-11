<?php
defined('ROOT_DIR') || exit;

abstract class Model
{
	protected static $_driver = DB_DRIVER_NAME;
	protected static $_target = null;
	protected static $_pk = DB_OBJECT_KEY;

	protected $_reflect;
	protected $_properties = array();

	public $id;

	/*###################################################*/
	public function __construct($target = null, $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		$this->_reflect = new ReflectionClass($this);
		$this->_driver = $driver;
		$this->_target = (is_null($target) ? static::getSource() : $target);
		$this->_pk = $pk;
	}

	private function &_reflect()
	{
		if (!isset($this->_reflect))
			$this->_reflect = new ReflectionClass($this);
		return $this->_reflect;
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

	public function __call($name, $arguments = array())
	{
		$db =& App::db($this->_target, $this->_driver, $this->_pk);
		return call_user_func_array(array($db, $name), $arguments);
	}

	public function __set($name, $value)
	{
		$properties = $this->_reflect()->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				$this->_properties[$name] = $value;
				return;
			}
		$this->$name = $value;
	}

	public function __get($name)
	{
		$properties = $this->_reflect()->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return $this->_properties[$name];
			}
		return $this->$name;
	}

	public function __isset($name)
	{
		$properties = $this->_reflect()->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return isset($this->_properties[$name]);
			}
		return isset($this->$name);
	}

	public function getData($result = 'array')
	{
		$fields = $this->_reflect()->getProperties(ReflectionProperty::IS_PUBLIC);
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

	public function toArray()
	{
		return $this->getData();
	}

	//Can tao 1 function call non static and static
	public function setFetchMode($mode, $class = null)
	{
		if (isset($this) && method_exists($this, '_reflect')) {
			if (is_null($class)) $class = $this->_reflect()->name;
			$this->__call('setFetchMode', array($mode, $class));
		} else {
			self::__callStatic('setFetchMode', array($mode, $class));
		}
	}

	/*###################################################*/
	protected static function init($target = null, $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		static::$_driver = $driver;
		static::$_target = (is_null($target) ? static::getSource() : $target);
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
		$db =& App::db(self::$_target, self::$_driver, self::$_pk);
		return call_user_func_array(array($db, $name), $arguments);
	}
}