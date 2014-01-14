<?php
defined('ROOT_DIR') || exit;

class Fake
{
	public static function import($class)
	{
		static $fakes = array();
		if (!isset($fakes[$class])) {
			$fakes[$class] = true;
			$class = LIBRARY_DIR . 'fake' . DS . str_replace('\\', DS, $class) . '.php';
			if (file_exists($class)) require_once $class;
		}
	}
}