<?php
defined('ROOT_DIR') || exit;

class UserModel extends Model
{
	/*private $driver = 'MySql';
	private $target = 'user';*/

	public function __construct()
	{
		parent::__construct('user', 'MySql');
	}

	public static function __init()
	{
		parent::init('user', 'MySql');
	}
}