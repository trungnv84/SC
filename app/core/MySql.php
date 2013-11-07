<?php
defined('ROOT_DIR') || exit;

define('MYSQL_DRIVER_NAME', 'MySql');

class MySql extends DBDriver
{
	private static $connections = array();

	private static function &collect($instance = DB_INSTANCE)
	{
		if (!$instance) $instance = 'default';
		if (!isset(self::$connections[$instance])) {
			$key = self::getDbKey($instance, MYSQL_DRIVER_NAME);
			if (!isset(self::$connections[$key])) {
				//zzz
				$config = self::getDbConfig($instance, MYSQL_DRIVER_NAME);
				if ($config['pconnect']) {
					self::$connections[$key] = mysql_pconnect($config['hostname'], $config['username'], $config['password']);
				} else {
					self::$connections[$key] = mysql_connect($config['hostname'], $config['username'], $config['password']);
				}
				if (false === self::$connections[$key]) {
					App::end("Could not connect: " . mysql_error() . " -> ???//zzz");
				} else {
					App::addEndEvents(array(
						'function' => array(MYSQL_DRIVER_NAME, 'closeAll')
					));
					if (!mysql_select_db($config['database'], self::$connections[$key])) {
						App::end("Database [$config[database]] not exists -> ???//zzz");
					}
				}

			}
			self::$connections[$instance] =& self::$connections[$key];
		}
		return self::$connections[$instance];
	}

	public static function close($instance = DB_INSTANCE)
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
			if (is_resource($instance))
				mysql_close($instance);
		}
		self::$connections = array();
	}

	public function query($sql)
	{
		$connection =& self::collect($this->instance);
		return mysql_query($sql, $connection);
	}

	public function find()
	{
		$connection =& self::collect($this->instance);
	}

}