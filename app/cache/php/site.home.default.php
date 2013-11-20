<?php
namespace {
defined('ROOT_DIR') || exit;

class Controller
{
	public function indexAction()
	{
		if (method_exists($this, 'defaultAction'))
			$this->defaultAction();
		else App::end('none action -> 404//zzz');
	}

	protected function assign($key, $value = NULL)
	{
		App::assign($key, $value);
	}

	protected function view($view, $controller = CURRENT_CONTROLLER, $template = null, $layout = null, $type = null)
	{
		App::view($view, $controller, $template, $layout, $type);
	}
}
}

namespace {


class HomeController extends Controller
{
	function defaultAction()
	{
		echo '<pre>';
		/*$user = new UserModel;
		$user->id = 99;
		$user->a = 5;
		var_dump(json_encode($user));
		$reflect = new ReflectionClass($user);
		var_dump($reflect->getProperties(ReflectionProperty::IS_STATIC));
		var_dump(get_object_vars($user));
		var_dump(get_class_vars('UserModel'));
		$data = $user->getData('both');
		var_dump($data->id, $data['id'], json_encode($data));*/

		/*$rs = UserModel::query('SELECT * FROM users');
		var_dump(mysql_fetch_row ( $rs ));*/
		/*$rs = UserModel::query('SELECT * FROM users');
		var_dump($rs);*/

		$filterInput = Joomla\JFilterInput::getInstance();
		echo $filterInput->clean('<script>alert("abc");</script>');
		echo $filterInput->clean('alert');

		echo '</pre>';
	}
}
}

namespace Joomla {
/**
 * @package     Joomla.Platform
 * @subpackage  Filter
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */



/**
 * JFilterInput is a class for filtering input from any data source
 *
 * Forked from the php input filter library by: Daniel Morris <dan@rootcube.com>
 * Original Contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
 *
 * @package     Joomla.Platform
 * @subpackage  Filter
 * @since       11.1
 */
class JFilterInput
{
	/**
	 * A container for JFilterInput instances.
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * The array of permitted tags (white list).
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $tagsArray;

	/**
	 * The array of permitted tag attributes (white list).
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $attrArray;

	/**
	 * The method for sanitising tags: WhiteList method = 0 (default), BlackList method = 1
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $tagsMethod;

	/**
	 * The method for sanitising attributes: WhiteList method = 0 (default), BlackList method = 1
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $attrMethod;

	/**
	 * A flag for XSS checks. Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $xssAuto;

	/**
	 * The list of the default blacklisted tags.
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $tagBlacklist = array(
		'applet',
		'body',
		'bgsound',
		'base',
		'basefont',
		'embed',
		'frame',
		'frameset',
		'head',
		'html',
		'id',
		'iframe',
		'ilayer',
		'layer',
		'link',
		'meta',
		'name',
		'object',
		'script',
		'style',
		'title',
		'xml'
	);

	/**
	 * The list of the default blacklisted tag attributes. All event handlers implicit.
	 *
	 * @var    array
	 * @since   11.1
	 */
	public $attrBlacklist = array(
		'action',
		'background',
		'codebase',
		'dynsrc',
		'lowsrc'
	);

	/**
	 * Constructor for inputFilter class. Only first parameter is required.
	 *
	 * @param   array    $tagsArray   List of user-defined tags
	 * @param   array    $attrArray   List of user-defined attributes
	 * @param   integer  $tagsMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $attrMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $xssAuto     Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 *
	 * @since   11.1
	 */
	public function __construct($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1)
	{
		// Make sure user defined arrays are in lowercase
		$tagsArray = array_map('strtolower', (array) $tagsArray);
		$attrArray = array_map('strtolower', (array) $attrArray);

		// Assign member variables
		$this->tagsArray = $tagsArray;
		$this->attrArray = $attrArray;
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
	}

	/**
	 * Returns an input filter object, only creating it if it doesn't already exist.
	 *
	 * @param   array    $tagsArray   List of user-defined tags
	 * @param   array    $attrArray   List of user-defined attributes
	 * @param   integer  $tagsMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $attrMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $xssAuto     Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 *
	 * @return  JFilterInput  The JFilterInput object.
	 *
	 * @since   11.1
	 */
	public static function &getInstance($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1)
	{
		$sig = md5(serialize(array($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto)));

		if (empty(self::$instances[$sig]))
		{
			self::$instances[$sig] = new JFilterInput($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto);
		}

		return self::$instances[$sig];
	}

	/**
	 * Method to be called by another php script. Processes for XSS and
	 * specified bad code.
	 *
	 * @param   mixed   $source  Input string/array-of-string to be 'cleaned'
	 * @param   string  $type    The return type for the variable:
	 *                           INT:       An integer,
	 *                           UINT:      An unsigned integer,
	 *                           FLOAT:     A floating point number,
	 *                           BOOLEAN:   A boolean value,
	 *                           WORD:      A string containing A-Z or underscores only (not case sensitive),
	 *                           ALNUM:     A string containing A-Z or 0-9 only (not case sensitive),
	 *                           CMD:       A string containing A-Z, 0-9, underscores, periods or hyphens (not case sensitive),
	 *                           BASE64:    A string containing A-Z, 0-9, forward slashes, plus or equals (not case sensitive),
	 *                           STRING:    A fully decoded and sanitised string (default),
	 *                           HTML:      A sanitised string,
	 *                           ARRAY:     An array,
	 *                           PATH:      A sanitised file path,
	 *                           USERNAME:  Do not use (use an application specific filter),
	 *                           RAW:       The raw string is returned with no filtering,
	 *                           unknown:   An unknown filter will act like STRING. If the input is an array it will return an
	 *                                      array of fully decoded and sanitised strings.
	 *
	 * @return  mixed  'Cleaned' version of input parameter
	 *
	 * @since   11.1
	 */
	public function clean($source, $type = 'string')
	{
		// Handle the type constraint
		switch (strtoupper($type))
		{
			case 'INT':
			case 'INTEGER':
				// Only use the first integer value
				preg_match('/-?[0-9]+/', (string) $source, $matches);
				$result = @ (int) $matches[0];
				break;

			case 'UINT':
				// Only use the first integer value
				preg_match('/-?[0-9]+/', (string) $source, $matches);
				$result = @ abs((int) $matches[0]);
				break;

			case 'FLOAT':
			case 'DOUBLE':
				// Only use the first floating point value
				preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $source, $matches);
				$result = @ (float) $matches[0];
				break;

			case 'BOOL':
			case 'BOOLEAN':
				$result = (bool) $source;
				break;

			case 'WORD':
				$result = (string) preg_replace('/[^A-Z_]/i', '', $source);
				break;

			case 'ALNUM':
				$result = (string) preg_replace('/[^A-Z0-9]/i', '', $source);
				break;

			case 'CMD':
				$result = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $source);
				$result = ltrim($result, '.');
				break;

			case 'BASE64':
				$result = (string) preg_replace('/[^A-Z0-9\/+=]/i', '', $source);
				break;

			case 'STRING':
				$result = (string) $this->_remove($this->_decode((string) $source));
				break;

			case 'HTML':
				$result = (string) $this->_remove((string) $source);
				break;

			case 'ARRAY':
				$result = (array) $source;
				break;

			case 'PATH':
				$pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
				preg_match($pattern, (string) $source, $matches);
				$result = @ (string) $matches[0];
				break;

			case 'USERNAME':
				$result = (string) preg_replace('/[\x00-\x1F\x7F<>"\'%&]/', '', $source);
				break;

			case 'RAW':
				$result = $source;
				break;

			default:
				// Are we dealing with an array?
				if (is_array($source))
				{
					foreach ($source as $key => $value)
					{
						// Filter element for XSS and other 'bad' code etc.
						if (is_string($value))
						{
							$source[$key] = $this->_remove($this->_decode($value));
						}
					}
					$result = $source;
				}
				else
				{
					// Or a string?
					if (is_string($source) && !empty($source))
					{
						// Filter source for XSS and other 'bad' code etc.
						$result = $this->_remove($this->_decode($source));
					}
					else
					{
						// Not an array or string.. return the passed parameter
						$result = $source;
					}
				}
				break;
		}

		return $result;
	}

	/**
	 * Function to determine if contents of an attribute are safe
	 *
	 * @param   array  $attrSubSet  A 2 element array for attribute's name, value
	 *
	 * @return  boolean  True if bad code is detected
	 *
	 * @since   11.1
	 */
	public static function checkAttribute($attrSubSet)
	{
		$attrSubSet[0] = strtolower($attrSubSet[0]);
		$attrSubSet[1] = strtolower($attrSubSet[1]);

		return (((strpos($attrSubSet[1], 'expression') !== false) && ($attrSubSet[0]) == 'style') || (strpos($attrSubSet[1], 'javascript:') !== false) ||
			(strpos($attrSubSet[1], 'behaviour:') !== false) || (strpos($attrSubSet[1], 'vbscript:') !== false) ||
			(strpos($attrSubSet[1], 'mocha:') !== false) || (strpos($attrSubSet[1], 'livescript:') !== false));
	}

	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 *
	 * @return  string  'Cleaned' version of input parameter
	 *
	 * @since   11.1
	 */
	protected function _remove($source)
	{
		$loopCounter = 0;

		// Iteration provides nested tag protection
		while ($source != $this->_cleanTags($source))
		{
			$source = $this->_cleanTags($source);
			$loopCounter++;
		}

		return $source;
	}

	/**
	 * Internal method to strip a string of certain tags
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 *
	 * @return  string  'Cleaned' version of input parameter
	 *
	 * @since   11.1
	 */
	protected function _cleanTags($source)
	{
		// First, pre-process this for illegal characters inside attribute values
		$source = $this->_escapeAttributeValues($source);

		// In the beginning we don't really have a tag, so everything is postTag
		$preTag = null;
		$postTag = $source;
		$currentSpace = false;

		// Setting to null to deal with undefined variables
		$attr = '';

		// Is there a tag? If so it will certainly start with a '<'.
		$tagOpen_start = strpos($source, '<');

		while ($tagOpen_start !== false)
		{
			// Get some information about the tag we are processing
			$preTag .= substr($postTag, 0, $tagOpen_start);
			$postTag = substr($postTag, $tagOpen_start);
			$fromTagOpen = substr($postTag, 1);
			$tagOpen_end = strpos($fromTagOpen, '>');

			// Check for mal-formed tag where we have a second '<' before the first '>'
			$nextOpenTag = (strlen($postTag) > $tagOpen_start) ? strpos($postTag, '<', $tagOpen_start + 1) : false;

			if (($nextOpenTag !== false) && ($nextOpenTag < $tagOpen_end))
			{
				// At this point we have a mal-formed tag -- remove the offending open
				$postTag = substr($postTag, 0, $tagOpen_start) . substr($postTag, $tagOpen_start + 1);
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}

			// Let's catch any non-terminated tags and skip over them
			if ($tagOpen_end === false)
			{
				$postTag = substr($postTag, $tagOpen_start + 1);
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}

			// Do we have a nested tag?
			$tagOpen_nested = strpos($fromTagOpen, '<');

			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end))
			{
				$preTag .= substr($postTag, 0, ($tagOpen_nested + 1));
				$postTag = substr($postTag, ($tagOpen_nested + 1));
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}

			// Let's get some information about our tag and setup attribute pairs
			$tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
			$currentTag = substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength = strlen($currentTag);
			$tagLeft = $currentTag;
			$attrSet = array();
			$currentSpace = strpos($tagLeft, ' ');

			// Are we an open tag or a close tag?
			if (substr($currentTag, 0, 1) == '/')
			{
				// Close Tag
				$isCloseTag = true;
				list ($tagName) = explode(' ', $currentTag);
				$tagName = substr($tagName, 1);
			}
			else
			{
				// Open Tag
				$isCloseTag = false;
				list ($tagName) = explode(' ', $currentTag);
			}

			/*
			 * Exclude all "non-regular" tagnames
			 * OR no tagname
			 * OR remove if xssauto is on and tag is blacklisted
			 */
			if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->tagBlacklist)) && ($this->xssAuto)))
			{
				$postTag = substr($postTag, ($tagLength + 2));
				$tagOpen_start = strpos($postTag, '<');

				// Strip tag
				continue;
			}

			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($currentSpace !== false)
			{
				$attr = '';
				$fromSpace = substr($tagLeft, ($currentSpace + 1));
				$nextEqual = strpos($fromSpace, '=');
				$nextSpace = strpos($fromSpace, ' ');
				$openQuotes = strpos($fromSpace, '"');
				$closeQuotes = strpos(substr($fromSpace, ($openQuotes + 1)), '"') + $openQuotes + 1;

				$startAtt = '';
				$startAttPosition = 0;

				// Find position of equal and open quotes ignoring
				if (preg_match('#\s*=\s*\"#', $fromSpace, $matches, PREG_OFFSET_CAPTURE))
				{
					$startAtt = $matches[0][0];
					$startAttPosition = $matches[0][1];
					$closeQuotes = strpos(substr($fromSpace, ($startAttPosition + strlen($startAtt))), '"') + $startAttPosition + strlen($startAtt);
					$nextEqual = $startAttPosition + strpos($startAtt, '=');
					$openQuotes = $startAttPosition + strpos($startAtt, '"');
					$nextSpace = strpos(substr($fromSpace, $closeQuotes), ' ') + $closeQuotes;
				}

				// Do we have an attribute to process? [check for equal sign]
				if ($fromSpace != '/' && (($nextEqual && $nextSpace && $nextSpace < $nextEqual) || !$nextEqual))
				{
					if (!$nextEqual)
					{
						$attribEnd = strpos($fromSpace, '/') - 1;
					}
					else
					{
						$attribEnd = $nextSpace - 1;
					}
					// If there is an ending, use this, if not, do not worry.
					if ($attribEnd > 0)
					{
						$fromSpace = substr($fromSpace, $attribEnd + 1);
					}
				}
				if (strpos($fromSpace, '=') !== false)
				{
					// If the attribute value is wrapped in quotes we need to grab the substring from
					// the closing quote, otherwise grab until the next space.
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes + 1)), '"') !== false))
					{
						$attr = substr($fromSpace, 0, ($closeQuotes + 1));
					}
					else
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}
				// No more equal signs so add any extra text in the tag into the attribute array [eg. checked]
				else
				{
					if ($fromSpace != '/')
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}

				// Last Attribute Pair
				if (!$attr && $fromSpace != '/')
				{
					$attr = $fromSpace;
				}

				// Add attribute pair to the attribute array
				$attrSet[] = $attr;

				// Move search point and continue iteration
				$tagLeft = substr($fromSpace, strlen($attr));
				$currentSpace = strpos($tagLeft, ' ');
			}

			// Is our tag in the user input array?
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);

			// If the tag is allowed let's append it to the output string.
			if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod))
			{
				// Reconstruct tag with allowed attributes
				if (!$isCloseTag)
				{
					// Open or single tag
					$attrSet = $this->_cleanAttributes($attrSet);
					$preTag .= '<' . $tagName;

					for ($i = 0, $count = count($attrSet); $i < $count; $i++)
					{
						$preTag .= ' ' . $attrSet[$i];
					}

					// Reformat single tags to XHTML
					if (strpos($fromTagOpen, '</' . $tagName))
					{
						$preTag .= '>';
					}
					else
					{
						$preTag .= ' />';
					}
				}
				// Closing tag
				else
				{
					$preTag .= '</' . $tagName . '>';
				}
			}

			// Find next tag's start and continue iteration
			$postTag = substr($postTag, ($tagLength + 2));
			$tagOpen_start = strpos($postTag, '<');
		}

		// Append any code after the end of tags and return
		if ($postTag != '<')
		{
			$preTag .= $postTag;
		}

		return $preTag;
	}

	/**
	 * Internal method to strip a tag of certain attributes
	 *
	 * @param   array  $attrSet  Array of attribute pairs to filter
	 *
	 * @return  array  Filtered array of attribute pairs
	 *
	 * @since   11.1
	 */
	protected function _cleanAttributes($attrSet)
	{
		$newSet = array();

		$count = count($attrSet);

		// Iterate through attribute pairs
		for ($i = 0; $i < $count; $i++)
		{
			// Skip blank spaces
			if (!$attrSet[$i])
			{
				continue;
			}

			// Split into name/value pairs
			$attrSubSet = explode('=', trim($attrSet[$i]), 2);

			// Take the last attribute in case there is an attribute with no value
			$attrSubSet[0] = array_pop(explode(' ', trim($attrSubSet[0])));

			// Remove all "non-regular" attribute names
			// AND blacklisted attributes

			if ((!preg_match('/[a-z]*$/i', $attrSubSet[0]))
				|| (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist))
				|| (substr($attrSubSet[0], 0, 2) == 'on'))))
			{
				continue;
			}

			// XSS attribute value filtering
			if (isset($attrSubSet[1]))
			{
				// Trim leading and trailing spaces
				$attrSubSet[1] = trim($attrSubSet[1]);

				// Strips unicode, hex, etc
				$attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);

				// Strip normal newline within attr value
				$attrSubSet[1] = preg_replace('/[\n\r]/', '', $attrSubSet[1]);

				// Strip double quotes
				$attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);

				// Convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr values)
				if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
				{
					$attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
				}
				// Strip slashes
				$attrSubSet[1] = stripslashes($attrSubSet[1]);
			}
			else
			{
				continue;
			}

			// Autostrip script tags
			if (self::checkAttribute($attrSubSet))
			{
				continue;
			}

			// Is our attribute in the user input array?
			$attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);

			// If the tag is allowed lets keep it
			if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod))
			{
				// Does the attribute have a value?
				if (empty($attrSubSet[1]) === false)
				{
					$newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				}
				elseif ($attrSubSet[1] === "0")
				{
					// Special Case
					// Is the value 0?
					$newSet[] = $attrSubSet[0] . '="0"';
				}
				else
				{
					// Leave empty attributes alone
					$newSet[] = $attrSubSet[0] . '=""';
				}
			}
		}

		return $newSet;
	}

	/**
	 * Try to convert to plaintext
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Plaintext string
	 *
	 * @since   11.1
	 */
	protected function _decode($source)
	{
		static $ttr;

		if (!is_array($ttr))
		{
			// Entity decode
			if (version_compare(PHP_VERSION, '5.3.4', '>='))
			{
				$trans_tbl = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'ISO-8859-1');
			}
			else
			{
				$trans_tbl = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT);
			}

			foreach ($trans_tbl as $k => $v)
			{
				$ttr[$v] = utf8_encode($k);
			}
		}

		$source = strtr($source, $ttr);

		// Convert decimal
		$source = preg_replace_callback('/&#(\d+);/m', function($m)
		{
			return utf8_encode(chr($m[1]));
		}, $source
		);

		// Convert hex
		$source = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($m)
		{
			return utf8_encode(chr('0x' . $m[1]));
		}, $source
		);

		return $source;
	}

	/**
	 * Escape < > and " inside attribute values
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Filtered string
	 *
	 * @since    11.1
	 */
	protected function _escapeAttributeValues($source)
	{
		$alreadyFiltered = '';
		$remainder = $source;
		$badChars = array('<', '"', '>');
		$escapedChars = array('&lt;', '&quot;', '&gt;');

		// Process each portion based on presence of =" and "<space>, "/>, or ">
		// See if there are any more attributes to process
		while (preg_match('#<[^>]*?=\s*?(\"|\')#s', $remainder, $matches, PREG_OFFSET_CAPTURE))
		{
			// Get the portion before the attribute value
			$quotePosition = $matches[0][1];
			$nextBefore = $quotePosition + strlen($matches[0][0]);

			// Figure out if we have a single or double quote and look for the matching closing quote
			// Closing quote should be "/>, ">, "<space>, or " at the end of the string
			$quote = substr($matches[0][0], -1);
			$pregMatch = ($quote == '"') ? '#(\"\s*/\s*>|\"\s*>|\"\s+|\"$)#' : "#(\'\s*/\s*>|\'\s*>|\'\s+|\'$)#";

			// Get the portion after attribute value
			if (preg_match($pregMatch, substr($remainder, $nextBefore), $matches, PREG_OFFSET_CAPTURE))
			{
				// We have a closing quote
				$nextAfter = $nextBefore + $matches[0][1];
			}
			else
			{
				// No closing quote
				$nextAfter = strlen($remainder);
			}

			// Get the actual attribute value
			$attributeValue = substr($remainder, $nextBefore, $nextAfter - $nextBefore);

			// Escape bad chars
			$attributeValue = str_replace($badChars, $escapedChars, $attributeValue);
			$attributeValue = $this->_stripCSSExpressions($attributeValue);
			$alreadyFiltered .= substr($remainder, 0, $nextBefore) . $attributeValue . $quote;
			$remainder = substr($remainder, $nextAfter + 1);
		}

		// At this point, we just have to return the $alreadyFiltered and the $remainder
		return $alreadyFiltered . $remainder;
	}

	/**
	 * Remove CSS Expressions in the form of <property>:expression(...)
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Filtered string
	 *
	 * @since   11.1
	 */
	protected function _stripCSSExpressions($source)
	{
		// Strip any comments out (in the form of /*...*/)
		$test = preg_replace('#\/\*.*\*\/#U', '', $source);

		// Test for :expression
		if (!stripos($test, ':expression'))
		{
			// Not found, so we are done
			$return = $source;
		}
		else
		{
			// At this point, we have stripped out the comments and have found :expression
			// Test stripped string for :expression followed by a '('
			if (preg_match_all('#:expression\s*\(#', $test, $matches))
			{
				// If found, remove :expression
				$test = str_ireplace(':expression', '', $test);
				$return = $test;
			}
		}

		return $return;
	}
}

}

namespace {


class Tag
{
	private static $html_title = '';
	private static $type = array();
	private static $html_meta = array();
	private static $html_css = array();
	private static $html_js = array();
	private static $html_footer_js = array();

	public static function setHtmlTitle($title)
	{
		self::$html_title = $title;
	}

	public static function getHtmlTitle()
	{
		return self::$html_title;
	}

	public static function addAsset($asset, $type, $key = false, $overwrite = false)
	{
		$type = 'html_' . $type;
		if ($key) {
			if (isset(self::${$type}[$key]) && !$overwrite) return;
			self::${$type}[$key] = $asset;
		} elseif (!in_array($asset, self::$type)) {
			self::${$type}[] = $asset;
		}
	}

	public static function addMetaTag($tag = '', $key = false, $overwrite = false)
	{
		self::addAsset($tag, 'meta', $key, $overwrite);
	}

	public static function setMetaKeywords($keywords = '')
	{
		self::addMetaTag("<meta name=\"keywords\" content=\"$keywords\">", 'MetaKeywords', true);
	}

	public static function setMetaDescription($description = '')
	{
		self::addMetaTag("<meta name=\"description\" content=\"$description\">", 'MetaDescription', true);
	}

	public static function addCSS($css, $key = false, $overwrite = false)
	{
		self::addAsset($css, 'css', $key, $overwrite);
	}

	public static function addJS($js, $key = false, $overwrite = false)
	{
		self::addAsset($js, 'js', $key, $overwrite);
	}

	public static function addFooterJS($js, $key = false, $overwrite = false)
	{
		self::addAsset($js, 'footer_js', $key, $overwrite);
	}

	public static function unShiftCSS($css, $key = false, $overwrite = false)
	{
		if ($key) {
			if (isset(self::$html_css[$key])) {
				if ($overwrite)
					unset(self::$html_css[$key]);
				else return;
			}
			$css = array($key => $css);
		} else $css = array($css);
		self::$html_css = array_merge($css, self::$html_css);
	}

	public static function unShiftJS($js, $key = false, $overwrite = false)
	{
		if ($key) {
			if (isset(self::$html_js[$key])) {
				if ($overwrite)
					unset(self::$html_js[$key]);
				else return;
			}
			$js = array($key => $js);
		} else $js = array($js);
		self::$html_js = array_merge($js, self::$html_js);
	}

	public static function unShiftFooterJS($js, $key = false, $overwrite = false)
	{
		if ($key) {
			if (isset(self::$html_footer_js[$key])) {
				if ($overwrite)
					unset(self::$html_footer_js[$key]);
				else return;
			}
			$js = array($key => $js);
		} else $js = array($js);
		self::$html_footer_js = array_merge($js, self::$html_footer_js);
	}

	public static function getHtmlHeader()
	{
		$html = '<base href="' . BASE_URL . "\">\n";
		if (sizeof(self::$html_meta)) {
			foreach (self::$html_meta as $metaTag)
				$html .= $metaTag . "\n";
			unset($metaTag);
		}
		if (sizeof(self::$html_css)) {
			if (ASSETS_OPTIMIZATION & 1) {
				$maxTime = 0;
				$nameMd5 = '';
				foreach (self::$html_css as $css) {
					$nameMd5 .= $css;
					if (strrpos($css, '{') === false) {
						if (strrpos($css, '/') === false) {
							$css = DEFAULT_CSS_DIR . $css;
							if (file_exists($css)) {
								$mTime = filemtime($css);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						} elseif (preg_match('/^https?:\/\/|\/\/([\da-z\.-]+)\.([a-z\.]{2,6})/i', $css)) {
							$file = CSS_CACHE_DIR . preg_replace('/[^a-z0-9\.]+/i', '-', $css);
							if (file_exists($file)) {
								$tmp = file_get_contents($file);
							} else {
								if (!preg_match('/^http/i', $css)) $css = SCHEME . ':' . $css;
								$tmp = @file_get_contents($css);
								$tmp = CssMin::minify($tmp);
								//if (!is_dir(CSS_CACHE_DIR)) mkdir(CSS_CACHE_DIR, DIR_WRITE_MODE, true);
								File::mkDir(CSS_CACHE_DIR);
								file_put_contents($file, $tmp);
							}
							if (preg_match('/:\s*url\s*\(/i', $tmp)) {
								$html .= "<link href=\"$css?__av=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
							}
						} else {
							$css = PUBLIC_DIR . $css;
							if (file_exists($css)) {
								$mTime = filemtime($css);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						}
					}
				}

				$nameMd5 = md5($nameMd5);
				$file = CSS_CACHE_DIR . $nameMd5 . '.css';
				if (!file_exists($file) || (ENVIRONMENT != 'Production' && $maxTime > filemtime($file))) {
					$cache = '';
					foreach (self::$html_css as $css) {
						if (strrpos($css, '{') !== false) {
							$cache .= $css;
						} elseif (strrpos($css, '/') === false) {
							$css = DEFAULT_CSS_DIR . $css;
							if (file_exists($css)) {
								if (ASSETS_OPTIMIZATION & 2) $cache .= self::minAsset($css, true) . "\n";
								else $cache .= file_get_contents($css) . "\n";
							}
						} elseif (preg_match('/^https?:\/\/|\/\/([\da-z\.-]+)\.([a-z\.]{2,6})/i', $css)) {
							$file = CSS_CACHE_DIR . preg_replace('/[^a-z0-9\.]+/i', '-', $css);
							if (file_exists($file)) {
								$css = file_get_contents($file);
							} else {
								if (!preg_match('/^http/i', $css)) $css = SCHEME . ':' . $css;
								$css = @file_get_contents($css);
								if (ASSETS_OPTIMIZATION & 2) $css = CssMin::minify($css);
								//if (!is_dir(CSS_CACHE_DIR)) mkdir(CSS_CACHE_DIR, DIR_WRITE_MODE, true);
								File::mkDir(CSS_CACHE_DIR);
								file_put_contents($file, $css);
							}
							if (!preg_match('/:\s*url\s*\(/i', $css)) {
								$cache .= $css;
							}
						} elseif (file_exists($css)) {
							if (ASSETS_OPTIMIZATION & 2) $tmp = self::minAsset($css, true);
							else $tmp = file_get_contents($css);
							$cache .= preg_replace('/url\s*\(\s*([\'"])/i', 'url($1../' . dirname($css) . '/', $tmp);
						}
					}
					$cache = str_replace(array('"../', '\'../'), array('"../../', '\'../../'), $cache);
					//if (!is_dir(CSS_CACHE_DIR)) mkdir(CSS_CACHE_DIR, DIR_WRITE_MODE, true);
					File::mkDir(CSS_CACHE_DIR);
					$file = CSS_CACHE_DIR . $nameMd5 . '.css';
					file_put_contents($file, $cache);
				}

				$file = "css/cache/$nameMd5.css?__av=" . ASSETS_VERSION;
				$html .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" />\n";
			} else {
				foreach (self::$html_css as $css) {
					if (strrpos($css, '{') === false) {
						if (strrpos($css, '/') === false) $css = "css/$css";
						if (ASSETS_OPTIMIZATION & 2 && !preg_match('/^https?:\/\/|\/\/([\da-z\.-]+)\.([a-z\.]{2,6})/i', $css)) $css = self::minAsset($css);
						$css .= '?__av=' . ASSETS_VERSION;
						$html .= "<link href=\"$css\" rel=\"stylesheet\" type=\"text/css\" />\n";
					} else {
						$html .= "<style type=\"text/css\">\n{$css}\n</style>\n";
					}
				}
			}
		}
		if (sizeof(self::$html_js)) {
			$html .= self::getJSHtml(self::$html_js);
		}
		return $html;
	}

	public static function getHtmlFooter()
	{
		return self::getJSHtml(self::$html_footer_js);
	}

	private static function getJSHtml(&$jss)
	{
		$html = '';
		if (sizeof($jss)) {
			if (ASSETS_OPTIMIZATION & 4) {
				$maxTime = 0;
				$nameMd5 = '';
				foreach ($jss as $js) {
					$nameMd5 .= $js;
					if (!preg_match('/[;\(]/', $js)) {
						if (strrpos($js, '/') === false) {
							$js = DEFAULT_JS_DIR . $js;
							if (file_exists($js)) {
								$mTime = filemtime($js);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						} elseif (preg_match('/^https?:\/\/|\/\/([\da-z\.-]+)\.([a-z\.]{2,6})/i', $js)) {
							$file = JS_CACHE_DIR . preg_replace('/[^a-z0-9\.]+/i', '-', $js);
							if (!file_exists($file)) {
								if (!preg_match('/^http/i', $js)) $js = SCHEME . ':' . $js;
								$js = @file_get_contents($js);
								$js = JSMin::minify($js);
								//if (!is_dir(JS_CACHE_DIR)) mkdir(JS_CACHE_DIR, DIR_WRITE_MODE, true);
								File::mkDir(JS_CACHE_DIR);
								file_put_contents($file, $js);
							}
						} else {
							$js = PUBLIC_DIR . $js;
							if (file_exists($js)) {
								$mTime = filemtime($js);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						}
					}
				}

				$nameMd5 = md5($nameMd5);
				$file = JS_CACHE_DIR . $nameMd5 . '.js';
				if (!file_exists($file) || (ENVIRONMENT != 'Production' && $maxTime > filemtime($file))) {
					$cache = '';
					foreach ($jss as $js) {
						if (preg_match('/[;\(]/', $js)) {
							$cache .= $js . "\n";
						} elseif (strrpos($js, '/') === false) {
							$js = DEFAULT_JS_DIR . $js;
							if (file_exists($js)) {
								if (ASSETS_OPTIMIZATION & 8) $cache .= self::minAsset($js, true) . "\n";
								else $cache .= file_get_contents($js) . "\n";
							}
						} elseif (preg_match('/^https?:\/\/|\/\/([\da-z\.-]+)\.([a-z\.]{2,6})/i', $js)) {
							$file = JS_CACHE_DIR . preg_replace('/[^a-z0-9\.]+/i', '-', $js);
							if (file_exists($file)) {
								$js = file_get_contents($file);
							} else {
								if (!preg_match('/^http/i', $js)) $js = SCHEME . ':' . $js;
								$js = @file_get_contents($js);
								$js = JSMin::minify($js);
								//if (!is_dir(JS_CACHE_DIR)) mkdir(JS_CACHE_DIR, DIR_WRITE_MODE, true);
								File::mkDir(JS_CACHE_DIR);
								file_put_contents($file, $js);
							}
							$cache .= $js . "\n";
						} elseif (file_exists($js)) {
							if (ASSETS_OPTIMIZATION & 8) $cache .= self::minAsset($js, true) . "\n";
							else $cache .= file_get_contents($js) . "\n";
						}
					}

					//if (!is_dir(JS_CACHE_DIR)) mkdir(JS_CACHE_DIR, DIR_WRITE_MODE, true);
					File::mkDir(JS_CACHE_DIR);
					$file = JS_CACHE_DIR . $nameMd5 . '.js';
					file_put_contents($file, $cache);
				}

				$file = "js/cache/$nameMd5.js?__av=" . ASSETS_VERSION;
				$html .= "<script src=\"$file\" type=\"text/javascript\" language=\"javascript\"></script>\n";
			} else {
				foreach ($jss as $js) {
					if (preg_match('/[;\(]/', $js)) {
						$html .= "<script type=\"text/javascript\" language=\"javascript\">\n{$js}\n</script>\n";
					} else {
						if (strrpos($js, '/') === false) $js = "js/$js";
						if (ASSETS_OPTIMIZATION & 2 && !preg_match('/^https?:\/\/|\/\/([\da-z\.-]+)\.([a-z\.]{2,6})/i', $js)) $js = self::minAsset($js);
						$js .= '?v=' . ASSETS_VERSION;
						$html .= "<script src=\"$js\" type=\"text/javascript\" language=\"javascript\"></script>\n";
					}
				}
			}
		}
		return $html;
	}

	private static function minAsset($file, $returnContent = false)
	{
		$pathInfo = pathinfo($file);
		if (substr($pathInfo['filename'], -4) == '.min')
			return ($returnContent ? file_get_contents($file) : $file);
		$minFile = "$pathInfo[dirname]/$pathInfo[filename].min.$pathInfo[extension]";
		if (file_exists($minFile) && (ENVIRONMENT == 'Product' || filemtime($minFile) > filemtime($file)))
			return ($returnContent ? file_get_contents($minFile) : $minFile);
		switch (strtolower($pathInfo['extension'])) {
			case 'css':
				$minContent = CssMin::minify(file_get_contents($file));
				break;
			case 'js':
				$minContent = JSMin::minify(file_get_contents($file));
				break;
			default:
				return ($returnContent ? file_get_contents($file) : $file);
		}
		file_put_contents($minFile, $minContent);
		return ($returnContent ? $minContent : $minFile);
	}
}
}

namespace {


class Format
{
	public static function byte($byte, $format = '%01.2lf %s')
	{
		if (($b = round($byte / 1024 / 1024, 2)) > 1) {
			$units = 'MB';
		} elseif (($b = round($byte / 1024, 2)) > 1) {
			$units = 'KB';
		} else {
			$b = round($byte, 2);
			$units = 'B';
		}
		if (strlen($format) == 0)
			$format = '%01d %s';

		return sprintf($format, $b, $units);
	}
}
}
