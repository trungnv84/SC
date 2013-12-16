<?php
defined('ROOT_DIR') || exit;

class UserModel extends Model
{
	public $id;
	public $username;
	public $password;
	public $status = 1;
	public $role;

	public function __construct($target = 'users', $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		parent::__construct($target, $driver, $pk);
	}

	public static function __init($target = 'users', $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		parent::init($target, $driver, $pk);
	}

	public function getSource()
	{
		return 'users';
	}
}