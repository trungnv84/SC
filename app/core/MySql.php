<?php
defined('ROOT_DIR') || exit;

class MySql extends DBDriver
{
	private static $connections = array();
	private static $currentDatabase = array();

	private $resource = null;
	private $last_query = null;
	private $current_query = null;

	private static function db_set_charset($instance, $charset, $collation)
	{
		$use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>=') && version_compare(mysql_get_server_info(self::$connections[$instance]), '5.0.7', '>=')) ? FALSE : TRUE;
		if ($use_set_names === TRUE) {
			return mysql_query("SET NAMES '" . $charset . "' COLLATE '" . $collation . "'", self::$connections[$instance]);
		} else {
			return mysql_set_charset($charset, self::$connections[$instance]);
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
		if (!isset(self::$currentDatabase[$instance]) || self::$currentDatabase[$instance] != $config['database']) {
			if (!mysql_select_db($config['database'], self::$connections[$instance])) {
				App::end("Database [$config[database]] not exists -> ???//zzz");
			}
			self::$currentDatabase[$instance] = $config['database'];
		}
		return self::$connections[$instance];
	}

	public static function select_db($database, $instance = DB_INSTANCE)
	{
		if (!$instance) $instance = 'default';
		if (self::$currentDatabase[$instance] != $database) {
			if (!mysql_select_db($database, self::$connections[$instance])) {
				App::end("Database [$database] not exists -> ???//zzz");
			}
			self::$currentDatabase[$instance] = $database;
		}
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

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string  $text  The string to be escaped.
	 * @param   boolean $extra Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   12.1
	 */
	public function escape($text, $extra = false)
	{
		if (is_string($text)) {
			$result = mysql_real_escape_string($text, self::collect($this->instance));

			if ($extra) {
				$result = addcslashes($result, '%_');
			}
		} elseif (is_bool($text)) {
			$result = ($text === FALSE) ? 0 : 1;
		} elseif (is_null($text)) {
			$result = 'NULL';
		} else $result = $text;

		return $result;
	}

	public function query($sql)
	{
		$this->last_query = $sql;
		$connection =& self::collect($this->instance);
		$this->resource = mysql_query($sql, $connection);
		return ($this->resource ? true : false);
	}

	public function fetch($cursor = null)
	{
		if (is_null($this->resource) || $this->resource === false) return false;

		switch ($this->fetch_mode) {
			case self::FETCH_ASSOC:
			default:
				$result = mysql_fetch_assoc($this->resource);
				break;
			case self::FETCH_OBJ:
				$result = mysql_fetch_object($this->resource);
				break;
			case self::FETCH_NUM:
				$result = mysql_fetch_row($this->resource);
				break;
			case self::FETCH_BOTH:
				$result = mysql_fetch_array($this->resource, MYSQL_BOTH);
				break;
			case self::FETCH_ARR_OBJ:
				$result = mysql_fetch_assoc($this->resource);
				$result = new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
				break;
			case self::FETCH_ACT_OBJ:
				if (is_null($this->active_class))
					$result = mysql_fetch_object($this->resource);
				else {
					if (!is_null($this->active_object)) {
						$params = array(@$this->active_object->_target, @$this->active_object->_driver, @$this->active_object->_pk);
					} else {
						$params = call_user_func(array($this->active_class, 'getParamsOfInit'));
					}
					$result = mysql_fetch_object($this->resource, $this->active_class, $params);
				}
				break;
		}
		return $result;
	}

	public function load($key)
	{
		if (is_null($this->active_class)) return false;
		if (!is_null($this->active_object)) {
			$params = array(@$this->active_object->_target, @$this->active_object->_driver, @$this->active_object->_pk);
		} else {
			$params = call_user_func(array($this->active_class, 'getParamsOfInit'));
		}
		$this->query('SELECT * FROM ' . $params[0] . ' WHERE ' . $params[2] . ' = ' . $this->quote($key) . ' LIMIT 1');

		return mysql_fetch_object($this->resource, $this->active_class, $params);
	}

	/*public function find()
	{
		$connection =& self::collect($this->instance);
	}*/

}