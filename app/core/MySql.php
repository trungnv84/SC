<?php
defined('ROOT_DIR') || exit;

class MySql extends DBDriver
{
	private static $connections = array();

	private static function &collect($instance = null)
	{
		if (!$instance) $instance = self::$instance; //yyy
		$config = self::dbConfig($instance, 'MySql');
		$instance = "$config[hostname].$config[username].$config[password]";
		if (!isset(self::$connections[$instance])) {
			if ($config['pconnect']) {
				self::$connections[$instance] = mysql_pconnect($config['hostname'], $config['username'], $config['password']);
			} else {
				self::$connections[$instance] = mysql_connect($config['hostname'], $config['username'], $config['password']);
			}
			if (false === self::$connections[$instance]) {
				App::end("Could not connect: " . mysql_error() . " -> ???//zzz");
			} else {
				App::addEndEvents(array(
					'function' => array('MySql', 'closeAll')
				));
				if (!mysql_select_db($config['database'], self::$connections[$instance])) {
					App::end("Database [$config[database]] not exists -> ???//zzz");
				}
			}
		}
		return self::$connections[$instance];
	}

	public static function close($instance = null)
	{
		if (!$instance) $instance = self::$instance; //yyy
		if (isset(self::$connections[$instance])) {
			mysql_close(self::$connections[$instance]);
			unset(self::$connections[$instance]);
		}
	}

	public static function closeAll()
	{
		foreach (self::$connections as &$instance) {
			mysql_close($instance);
		}
		self::$connections = array();
	}

	public static function query($sql)
	{
		$connection =& self::collect();
		return mysql_query($sql, $connection);
	}

	public static function find()
	{
		$connection =& self::collect();
	}

}