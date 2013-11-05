<?php
defined('ROOT_DIR') || exit;

class MySql
{
	private static function collect()
	{
		static $connect_id;
		if(!isset($connect_id)) {
			$config =& App::$config->db['MySql'];
			$connect_id = @mssql_connect($config['hostname'], $config['username'], $config['password']);
		}
		return $connect_id;
	}

	private static function db_select()
	{
		static $db_select;
		if(!isset($db_select)) {
			$config =& App::$config->db['MySql'];
			$db_select = @mssql_select_db('['.$config['database'].']', self::collect());
		}
		return $db_select;
	}

	public static function find()
	{

	}
}