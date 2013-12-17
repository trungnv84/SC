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

	protected $fetch_mode = null;
	protected $active_class = null;
	protected $active_object = null;

	public function __construct($instance = DB_INSTANCE)
	{
		$this->instance = $instance;
	}

	public static function getDbKey($instance = DB_INSTANCE, $driver = DB_DRIVER_NAME)
	{
		static $keys;
		if (!$instance) $instance = 'default';
		if (!isset($keys[$instance])) {
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
			if (isset(App::$config->dbKeyIgnores[$driver])) {
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

	public function init()
	{
		$this->active_class = null;
		$this->active_object = null;
	}

	public function setFetchMode($mode)
	{
		$this->fetch_mode = $mode;
	}

	public function active_class($class)
	{
		$this->active_object = null;
		$this->active_class = $class;
	}

	public function active_object(&$object)
	{
		$this->active_object = $object;
		$this->active_class = get_class($object);
	}

	/**
	 * Quotes and optionally escapes a string to database requirements for use in database queries.
	 *
	 * @param   mixed   $text   A string or an array of strings to quote.
	 * @param   boolean $escape True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @note    Accepting an array of strings was added in 12.3.
	 * @since   11.1
	 */
	public function quote($text, $escape = true)
	{
		if (is_array($text)) {
			foreach ($text as $k => $v) {
				$text[$k] = $this->quote($v, $escape);
			}

			return $text;
		} elseif (is_string($text)) {
			return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
		} else {
			return $escape ? $this->escape($text) : $text;
		}
	}

	/**
	 * Escapes a string for usage in an SQL statement.
	 *
	 * @param   string  $text  The string to be escaped.
	 * @param   boolean $extra Optional parameter to provide extra escaping.
	 *
	 * @return  string   The escaped string.
	 *
	 * @since   11.1
	 */
	public abstract function escape($text, $extra = false);

	/**
	 * Method to fetch a row from the result set cursor
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public abstract function fetch($cursor = null);

	public abstract function load($key);
}