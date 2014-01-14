<?php
namespace Joomla {
	class JDatabaseDriver
	{
		private $config;
		private $nullDate;
		private $nameQuote;

		public function __construct($config = array(), $nameQuote = null)
		{
			$this->config = $config;
			$this->nameQuote = $nameQuote;
		}

		/**
		 * Returns a PHP date() function compliant date format for the database driver.
		 *
		 * @return  string  The format string.
		 *
		 * @since   11.1
		 */
		public function getDateFormat()
		{
			return 'Y-m-d H:i:s';
		}

		/**
		 * Get the common table prefix for the database driver.
		 *
		 * @return  string The common database table prefix.
		 *
		 * @since   11.1
		 */
		public function getPrefix()
		{
			return isset($this->config['dbprefix']) ? $this->config['dbprefix'] : '';
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
				if (function_exists('mysql_escape_string')) {
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
		 * Get the null or zero representation of a timestamp for the database driver.
		 *
		 * @return  string  Null or zero representation of a timestamp.
		 *
		 * @since   11.1
		 */
		public function getNullDate()
		{
			return $this->nullDate;
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
	}
}