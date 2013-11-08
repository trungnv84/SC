<?php
defined('ROOT_DIR') || exit;

class MySql extends DBDriver
{
	private static $connections = array();
	private static $currentDatabase = '';

	private static function db_set_charset($instance, $charset, $collation)
	{
		$use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>=') && version_compare(mysql_get_server_info(self::$connections[$instance]), '5.0.7', '>=')) ? FALSE : TRUE;
		if ($use_set_names === TRUE) {
			return @mysql_query("SET NAMES '" . $charset . "' COLLATE '" . $collation . "'", self::$connections[$instance]);
		} else {
			return @mysql_set_charset($charset, self::$connections[$instance]);
		}
	}

	private static function &collect($instance = DB_INSTANCE)
	{
		if (!$instance) $instance = 'default';
		$config = self::getDbConfig($instance, MYSQL_DRIVER_NAME);
		if (!isset(self::$connections[$instance])) {
			$key = self::getDbKey($instance, MYSQL_DRIVER_NAME);
			if (!isset(self::$connections[$key])) {
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
				}
				if (!self::db_set_charset($key, $config['char_set'], $config['dbcollat'])) {
					App::end("DB Set charset error: " . mysql_error() . " -> ???//zzz");
				}
			}
			self::$connections[$instance] =& self::$connections[$key];
		}
		if (self::$currentDatabase != $config['database']) {
			if (!mysql_select_db($config['database'], self::$connections[$instance])) {
				App::end("Database [$config[database]] not exists -> ???//zzz");
			}
			self::$currentDatabase = $config['database'];
		}
		return self::$connections[$instance];
	}

	public static function close($instance = DB_INSTANCE)
	{
		if (isset(self::$connections[$instance])) {
			if (is_resource(self::$connections[$instance])) {
				mysql_close(self::$connections[$instance]);
			}
			unset(self::$connections[$instance]);
		}
	}

	public static function closeAll()
	{
		foreach (self::$connections as $key => &$instance) {
			if (is_resource($instance)) {
				mysql_close(self::$connections[$key]);
			}
			unset(self::$connections[$key]);
		}
		unset($instance);
		self::$connections = array();
	}

	public function query($sql)
	{
		$connection =& self::collect($this->instance);
		return mysql_query($sql, $connection);
	}

	/*public function find()
	{
		$connection =& self::collect($this->instance);
	}*/

}