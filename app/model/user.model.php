<?php
defined('ROOT_DIR') || exit;

class UserModel extends Model
{
	public $username;
	public $password;

	public function __construct($target = 'user', $driver = DB_DRIVER, $pk = null)
	{
		parent::__construct($target, $driver, $pk);
	}

	public static function __init($target = 'user', $driver = DB_DRIVER, $pk = null)
	{
		parent::init($target, $driver, $pk);
	}
}