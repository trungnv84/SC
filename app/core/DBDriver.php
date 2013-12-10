<?php
defined('ROOT_DIR') || exit;

abstract class DBDriver
{
	const FETCH_ASSOC = MYSQL_ASSOC;
	const FETCH_NUM = MYSQL_NUM;
	const FETCH_BOTH = MYSQL_BOTH;
	const FETCH_OBJ = 4; // MYSQL_OBJ
	const FETCH_ARR_OBJ = 5; // MYSQL_AOB_OBJ
	const FETCH_ACT_OBJ = 6; // MYSQL_ACT_OBJ

	protected $instance;
	protected $_pk = null;

	public function __construct($instance = DB_INSTANCE)
	{
		$this->instance = $instance;
	}

	public static function getDbKey($instance = DB_INSTANCE, $driver = DB_DRIVER_NAME)
	{
		static $keys;
		if(!$instance) $instance = 'default';
		if(!isset($keys[$instance])) {
			if (DB_INSTANCE) {
				if (isset(App::$config->db[$instance][$driver])) {
					$config = App::$config->db[$instance][$driver];
					if (!is_array($config)) {
						$config = explode('.', $config);
						if (isset(App::$config->db[$config[0]][$driver])) {
							$config = App::$config->db[$config[0]][$driver];
						} else {
							$config = App::$config->db[DB_INSTANCE][$driver];
						}
					}
				} else {
					$config = App::$config->db[DB_INSTANCE][$driver];
				}
			} else {
				$config = App::$config->db[$driver];
			}
			if(isset(App::$config->dbKeyIgnores[$driver])) {
				$config = array_diff_key($config, array_flip(App::$config->dbKeyIgnores[$driver]));
			}
			$keys[$instance] = implode('.', $config);
		}
		return $keys[$instance];
	}

	protected static function getDbConfig($instance, $driver)
	{
		static $configs;
		if (!isset($configs[$instance][$driver])) {
			if (!isset($configs[$instance])) $configs[$instance] = array();
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

	public function set_pk($pk)
	{
		$this->_pk = $pk;
	}

	public abstract function fetch();
}