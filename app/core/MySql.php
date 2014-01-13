<?php
defined('ROOT_DIR') || exit;

class MySql extends DBDriver
{
	private static $connections = array();
	private static $databases = array();

	private $resource = null;
	private $last_query = null;

	protected $nameQuote = '`';

	public $bind_marker = '?';
	public $bind_prefix_marker = ':';
	public $bind_suffix_marker = ':';

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
		$config =& self::getDbConfig($instance, MYSQL_DRIVER_NAME);
		if (!isset(self::$connections[$instance])) {
			$key = self::getDriverKey($instance, MYSQL_DRIVER_NAME);
			if (!isset(self::$connections[$key])) {
				if ($config['pconnect']) {
					self::$connections[$key] = mysql_pconnect($config['hostname'], $config['username'], $config['password']);
				} else {
					self::$connections[$key] = mysql_connect($config['hostname'], $config['username'], $config['password'], true);
				}
				if (false === self::$connections[$key]) {
					App::end(500, 'Could not connect: ' . mysql_error());
				} else {
					App::addEndEvents(array(
						'function' => array(MYSQL_DRIVER_NAME, 'closeAll')
					));
				}
				if (!self::db_set_charset($key, $config['char_set'], $config['dbcollat'])) {
					App::end(500, 'DB Set charset error: ' . mysql_error());
				}
			}
			self::$connections[$instance] =& self::$connections[$key];
		}
		if (!isset(self::$databases[$instance]) || self::$databases[$instance] != $config['database']) {
			if (!mysql_select_db($config['database'], self::$connections[$instance])) {
				App::end(500, "Database [$config[database]] not exists");
			}
			self::$databases[$instance] = $config['database'];
		}
		return self::$connections[$instance];
	}

	public static function select_db($database, $instance = DB_INSTANCE)
	{
		if (!$instance) $instance = 'default';
		if (self::$databases[$instance] != $database) {
			if (!mysql_select_db($database, self::$connections[$instance])) {
				App::end(500, "Database [$database] not exists");
			}
			self::$databases[$instance] = $database;
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

	/**
	 * Close all connect to data base
	 *
	 */
	public static function closeAll()
	{
		foreach (self::$connections as $key => &$instance) {
			if (is_resource($instance)) {
				mysql_close(self::$connections[$key]);
			}
		}
		self::$connections = array();
	}

	public function getQuery($new = false, $driver = MYSQL_DRIVER_NAME)
	{
		return parent::getQuery($new, $driver);
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
			//$text = mysql_real_escape_string($text, self::collect($this->instance));
			$connection =& self::collect($this->instance);
			if (function_exists('mysql_real_escape_string') AND is_resource($connection)) {
				$text = mysql_real_escape_string($text, $connection);
			} elseif (function_exists('mysql_escape_string')) {
				$text = mysql_real_escape_string($text);
			} else {
				$text = addslashes($text);
			}

			if ($extra) {
				$text = addcslashes($text, '%_');
			}
		} elseif (is_bool($text)) {
			$text = ($text === FALSE) ? 0 : 1;
		} elseif (is_null($text)) {
			$text = 'NULL';
		}

		return $text;
	}

	/**
	 * Get the common table prefix for the database driver.
	 *
	 * @param   string $driver ccc
	 *
	 * @return  string  The common database table prefix.
	 *
	 * @since   11.1
	 */
	public function getPrefix($driver = MYSQL_DRIVER_NAME)
	{
		return parent::getPrefix($driver);
	}

	protected function compile_bind($sql, $bind)
	{
		krsort($bind);
		$search = $replace = array();
		foreach ($bind as $key => $value) {
			if (is_numeric($key))
				$search[] = $this->bind_marker . $key;
			else
				$search[] = $this->bind_prefix_marker . $key . $this->bind_suffix_marker;
			$replace[] = $this->quote($value);
		}
		return str_replace($search, $replace, $sql);
	}

	public function query($sql)
	{
		$config =& self::getDbConfig($this->instance, MYSQL_DRIVER_NAME);
		if ($config['swap_pre']) $sql = $this->replacePrefix($sql, $config['swap_pre'], $config['dbprefix']);
		$this->last_query = $sql;
		$connection =& self::collect($this->instance);
		$this->resource = mysql_query($sql, $connection);
		return ($this->resource ? true : false);
	}

	public function fetch($mode = false)
	{
		if (is_null($this->resource) || $this->resource === false) return false;

		if (!$mode) $mode =& $this->fetch_mode;

		switch ($mode) {
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
				if (is_array($result)) $result = new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
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

	public function fetchAll($query = null, $k = false)
	{
		$params = $this->getModelParams();

		if ($query instanceof Joomla\JDatabaseQuery) {
			if (is_null($query->from) && !is_null($params)) {
				$config =& self::getDbConfig($this->instance, MYSQL_DRIVER_NAME);
				$query->from($config['dbprefix'] . $params[0]);
			}
			$query = $query->__toString();
		}
		//zzz elseif(get_object_vars)


		if (is_string($query)) $this->query($query);

		if ($k === true && isset($params)) $k = $params[2];

		$results = array();
		while ($result = $this->fetch()) {
			$key = $k ? (is_array($result) && isset($result[$k]) ? $result[$k] :
				(is_object($result) && isset($result->$k) ? $result->$k : null)) : null;
			if (is_null($key)) $results[] = $result; else $results[$key] = $result;
		}
		return $results;
	}

	public function load($key)
	{
		if (is_null($this->active_class)) return false;

		$params = $this->getModelParams();

		$config =& self::getDbConfig($this->instance, MYSQL_DRIVER_NAME);
		if (is_scalar($key)) {
			$sql = 'SELECT * FROM ' . $config['dbprefix'] . $params[0] . ' WHERE ' . $this->quoteName($params[2]) . ' = ' . $this->quote($key) . ' LIMIT 1';
			unset($key);
			$this->query($sql);
		} else {
			$sql = 'SELECT * FROM ' . $config['dbprefix'] . $params[0];
			if (isset($key['condition'])) {
				if (is_object($key['condition'])) $key['condition'] = get_object_vars($key['condition']);
				if (is_array($key['condition'])) $key['condition'] = implode(' AND ', $key['condition']);
				if (is_string($key['condition'])) $sql .= ' WHERE ' . $key['condition'];
			}
			if (isset($key['order'])) $sql .= ' ORDER BY ' . $key['order'];
			if (isset($key['bind'])) $sql = $this->compile_bind($sql, $key['bind']);

			$sql .= ' LIMIT 1';

			unset($key);
			$this->query($sql);
		}

		return mysql_fetch_object($this->resource, $this->active_class, $params);
	}

	/*public function find()
	{
		$connection =& self::collect($this->instance);
	}*/

}