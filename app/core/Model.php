<?php
defined('ROOT_DIR') || exit;

class Model
{
	private $driver = 'mySql';
	private $target = '';

	public function __construct($target, $driver = 'mySql')
	{
		$this->driver = $driver;
		$this->target = $target;
	}

	public function __call($name, $arguments = array())
	{
		call_user_func(array($this->driver, $name), $arguments);
	}

	public static function init($target, $driver = 'mySql')
	{
		self::$driver = $driver;
		self::$target = $target;
	}

	public static function __callStatic($name, $arguments = array())
	{
		call_user_func(array(self::$driver, $name), $arguments);
	}
}