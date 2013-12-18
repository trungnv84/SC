<?php
defined('ROOT_DIR') || exit;

class MySqlQuery
{
	public function __construct($params = array())
	{
		foreach($params as $param => $value)
			$this->$param = is_object($value)?get_object_vars($value):$value;
	}

	public function toString()
	{

	}
}