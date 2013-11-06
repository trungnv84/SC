<?php
defined('ROOT_DIR') || exit;

class UserModel extends Model
{
	public $username;
	public $password;
	public $status = 1;
	public $role;

	public function __construct($target = 'user')
	{
		parent::__construct($target);
	}

	public static function __init($target = 'user')
	{
		parent::init($target);
	}

	public function getSource()
	{
		return 'user';
	}
}