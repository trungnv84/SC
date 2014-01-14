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

	protected $nameQuote;

	protected $fetch_mode = null;
	protected $active_class = null;
	protected $active_object = null;

	public function __construct($instance)
	{
		$this->instance = $instance;
	}

	public static function getDriverKey($instance = DB_INSTANCE, $driver = DB_DRIVER_NAME)
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
				$config = array_diff_key($config, array_flip(App::$config->driverKeyIgnores[$driver]));
			}
			$keys[$instance] = implode('.', $config);
		}
		return $keys[$instance];
	}

	protected static function &getDbConfig($instance, $driver, $name = false)
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

		if ($name) return $configs[$instance][$driver][$name];
		else return $configs[$instance][$driver];
	}

	public function &getConfig($name = false)
	{
		$driver = get_class($this);
		return self::getDbConfig($this->instance, $driver, $name);
	}

	public function setFetchMode($mode)
	{
		$this->fetch_mode = $mode;
	}

	public function active_class($class)
	{
		$this->active_object = null;
		if (class_exists($class)) $this->active_class = $class;
		else $this->active_class = null;
	}

	public function active_object(&$object)
	{
		$this->active_object = $object;
		$this->active_class = get_class($object);
	}

	protected function getModelParams()
	{
		if (!is_null($this->active_object)) {
			$params = array(@$this->active_object->_target, @$this->active_object->_driver, @$this->active_object->_pk);
		} elseif (!is_null($this->active_class)) {
			$params = call_user_func(array($this->active_class, 'getParamsOfInit'));
		} else $params = null;
		return $params;
	}

	/**
	 * Get the current query object or a new JDatabaseQuery object.
	 *
	 * @return  JDatabaseQuery  The current query object or a new object extending the JDatabaseQuery class.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function getQuery()
	{
		$driver = get_class($this);

		// Derive the class name from the driver.
		$class = 'Joomla\JDatabaseQuery' . ucfirst(strtolower($driver));

		// Make sure we have a query class for this driver.
		if (!class_exists($class)) {
			// If it doesn't exist we are at an impasse so throw an exception.
			App::end(500, 'Database Query Class not found.');
		}

		$driver = 'Joomla\JDatabaseDriver';

		Fake::import($driver);

		$config =& self::getDbConfig($this->instance, $driver);

		return new $class(new $driver($config, $this->nameQuote));
	}

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * @param   mixed $name   The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
	 *                        Each type supports dot-notation name.
	 * @param   mixed $as     The AS query part associated to $name. It can be string or array, in latter case it has to be
	 *                        same length of $name; if is null there will not be any AS part for string or array element.
	 *
	 * @return  mixed  The quote wrapped name, same type of $name.
	 *
	 * @since   11.1
	 */
	public function quoteName($name, $as = null)
	{
		if (is_string($name)) {
			$quotedName = $this->quoteNameStr(explode('.', $name));

			$quotedAs = '';

			if (!is_null($as)) {
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameStr($as);
			}

			return $quotedName . $quotedAs;
		} else {
			$fin = array();

			if (is_null($as)) {
				foreach ($name as $str) {
					$fin[] = $this->quoteName($str);
				}
			} elseif (is_array($name) && (count($name) == count($as))) {
				$count = count($name);

				for ($i = 0; $i < $count; $i++) {
					$fin[] = $this->quoteName($name[$i], $as[$i]);
				}
			}

			return $fin;
		}
	}

	/**
	 * Quote strings coming from quoteName call.
	 *
	 * @param   array $strArr Array of strings coming from quoteName dot-explosion.
	 *
	 * @return  string  Dot-imploded string of quoted parts.
	 *
	 * @since 11.3
	 */
	protected function quoteNameStr($strArr)
	{
		$parts = array();
		$q = $this->nameQuote;

		foreach ($strArr as $part) {
			if (is_null($part)) {
				continue;
			}

			if (strlen($q) == 1) {
				$parts[] = $q . $part . $q;
			} else {
				$parts[] = $q{0} . $part . $q{1};
			}
		}

		return implode('.', $parts);
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
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string $sql         The SQL statement to prepare.
	 * @param   string $prefix      The common table prefix.
	 * @param   string $tablePrefix The table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   11.1
	 */
	public function replacePrefix($sql, $prefix = '#__', $tablePrefix = '')
	{
		$startPos = 0;
		$literal = '';

		$sql = trim($sql);
		$n = strlen($sql);

		while ($startPos < $n) {
			$ip = strpos($sql, $prefix, $startPos);

			if ($ip === false) {
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);

			if (($k !== false) && (($k < $j) || ($j === false))) {
				$quoteChar = '"';
				$j = $k;
			} else {
				$quoteChar = "'";
			}

			if ($j === false) {
				$j = $n;
			}

			$literal .= str_replace($prefix, $tablePrefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n) {
				break;
			}

			// Quote comes first, find end of quote
			while (true) {
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;

				if ($k === false) {
					break;
				}
				$l = $k - 1;

				while ($l >= 0 && $sql{$l} == '\\') {
					$l--;
					$escaped = !$escaped;
				}

				if ($escaped) {
					$j = $k + 1;
					continue;
				}

				break;
			}

			if ($k === false) {
				// Error in the query - no end quote; ignore it
				break;
			}

			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}

		if ($startPos < $n) {
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Method to fetch a row from the result type
	 *
	 * @param   mixed $mode The optional result type from which to fetch the row.
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public abstract function fetch($mode = false);

	public abstract function load($key);
}