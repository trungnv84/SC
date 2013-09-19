<?php
defined('ROOT_DIR') || exit;

abstract class Model
{
	protected $id;

	/*###################################################*/
	public function __construct($target, $driver = 'MySql')
	{
		$this->properties('driver', $driver);
		$this->properties('target', $target);
		$this->properties('_pk', 'id');
	}

	public function properties($name, $value = null)
	{
		static $props = array();
		if(is_null($value)) {
			if(isset($props[$name])) {
				return $props[$name];
			} else return null;
		} else {
			return $props[$name] = $value;
		}
	}

	public function __call($name, $arguments = array())
	{
		return call_user_func(array($this->properties('driver'), $name), $arguments);
	}
/*
	public function __set($name, $value)
	{
		if(!$this->properties || in_array($name, $this->properties))
			$this->attributes[$name] = $value;
		else $this->$name = $value;
	}

	public function __get($name)
	{
		if(isset($this->attributes[$name]))
			return $this->attributes[$name];
		else return $this->$name;
	}

	public function __isset($name)
	{
		if(!$this->properties || in_array($name, $this->properties))
			return isset($this->attributes[$name]);
		else return isset($this->$name);
	}
*/
	/*###################################################*/
	protected static function init($target, $driver = 'MySql')
	{
		self::attributes('driver', $driver);
		self::attributes('target', $target);
	}

	public static function attributes($name, $value = null)
	{
		static $attrs = array();
		if(is_null($value)) {
			if(isset($attrs[$name])) {
				return $attrs[$name];
			} else return null;
		} else {
			return $attrs[$name] = $value;
		}
	}

	public static function __callStatic($name, $arguments = array())
	{
		return call_user_func(array(self::attributes('driver'), $name), $arguments);
	}
}