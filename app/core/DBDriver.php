<?php
defined('ROOT_DIR') || exit;

abstract class DBDriver
{
	protected static $instance;

	public static function setInstanceName($instance) //yyy
	{
		static::$instance = $instance;
	}

	protected static function dbConfig($instance, $driver)
	{
		static $configs;
		if (!isset($configs[$instance][$driver])) {
			if (!isset($configs[$instance])) {
				$configs[$instance] = array();
			}
			if (DB_INSTANCE) {
				if (isset(App::$config->db[$instance][$driver])) {
					$config = App::$config->db[$instance][$driver];
					if (is_array($config)) {
						$configs[$instance][$driver] = $config;
					} else {
						$config = explode('.', $config);
						if (isset(App::$config->db[$config[0]][$driver])) {
							$configs[$instance][$driver] = App::$config->db[$config[0]][$driver];
						} else {
							$configs[$instance][$driver] = App::$config->db[DB_INSTANCE][$driver];
						}
						if (isset($config[1])) {
							$configs[$instance][$driver]['database'] = $config[1];
						}
					}
				} else {
					$configs[$instance][$driver] =& App::$config->db[DB_INSTANCE][$driver];
				}
			} else {
				$configs[$instance][$driver] =& App::$config->db[$driver];
			}
		}
		return $configs[$instance][$driver];
	}
}