<?php
defined('ROOT_DIR') || exit;

abstract class Model
{
	protected static $_driver = DB_DRIVER_NAME;
	protected static $_target = null;
	protected static $_pk = DB_OBJECT_KEY;

	protected $_reflect;
	protected $_props = array();
	protected $_options = array();

	public $id;

	/*###################################################*/
	public function __construct($target = null, $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		//var_dump($this); //kiểm tra việc update các field mới...
		$fields = $this->_reflect()->getProperties(ReflectionProperty::IS_PUBLIC);
		$defaultValues = array();
		foreach ($fields as $field) {
			$name = $field->getName();
			$defaultValues[$name] = $this->$name;
		}
		unset($fields);
		$this->properties('defaultValues', $defaultValues);

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
		//static $props = array();
		if (is_null($value)) {
			if (isset($this->_props[$name])) {
				return $this->_props[$name];
			} else return null;
		} else {
			return $this->_props[$name] = $value;
		}
	}

	public function __call($name, $arguments = array())
	{
		$db =& App::db($this->_target, $this->_driver);
		$db->active_object($this);
		return call_user_func_array(array($db, $name), $arguments);
	}

	public function __set($name, $value)
	{
		$properties = $this->_reflect()->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				$this->_options[$name] = $value;
				return;
			}
		$this->$name = $value;
	}

	public function __get($name)
	{
		$properties = $this->_reflect()->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return $this->_options[$name];
			}
		return $this->$name;
	}

	public function __isset($name)
	{
		$properties = $this->_reflect()->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return isset($this->_options[$name]);
			}
		return isset($this->$name);
	}

	public function renewData()
	{
		$fields = $this->_reflect()->getProperties(ReflectionProperty::IS_PUBLIC);
		$defaultValues = get_class_vars(get_class($this));
		foreach ($fields as $field) {
			$name = $field->getName();
			if (array_key_exists($name, $defaultValues))
				$this->$name = $defaultValues[$name];
			else unset($this->$name);
		}
	}

	// them get all public properties of object
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

	/*###################################################*/
	public static function __init($target = null, $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		static::$_driver = $driver;
		static::$_target = (is_null($target) ? static::getSource() : $target);
		static::$_pk = $pk;
	}

	public static function getSource()
	{
		return substr(get_called_class(), 0, -5);

	}

	public static function getParamsOfInit()
	{
		return array(static::$_target, static::$_driver, static::$_pk);
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
		$db =& App::db(static::$_target, static::$_driver);
		$db->active_class(get_called_class());
		return call_user_func_array(array($db, $name), $arguments);
	}
}