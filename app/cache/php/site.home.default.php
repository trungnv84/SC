<?php
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
		$rs = UserModel::query('SELECT * FROM users');
		var_dump($rs);

		echo '</pre>';
	}
}


abstract class Model
{
	protected static $_driver = DB_DRIVER;
	protected static $_target = null;
	protected static $_pk = DB_OBJECT_KEY;

	protected $_reflect;
	protected $_properties = array();

	public $id;

	/*###################################################*/
	public function __construct($target = null, $driver = DB_DRIVER, $pk = DB_OBJECT_KEY)
	{
		$this->_reflect = new ReflectionClass($this);
		$this->_driver = $driver;
		$this->_target = (is_null($target)?static::getSource():$target);
		$this->_pk = $pk;
	}

	public function &setTarget($target)
	{
		$this->_target = $target;
		return $this;
	}

	public function properties($name, $value = null)
	{
		static $props = array();
		if (is_null($value)) {
			if (isset($props[$name])) {
				return $props[$name];
			} else return null;
		} else {
			return $props[$name] = $value;
		}
	}

	public function __call($name, $arguments = array())
	{
		$db =& App::db($this->_target, $this->_driver);
		return call_user_func_array(array($db, $name), $arguments);
	}

	public function __set($name, $value)
	{
		$properties = $this->_reflect->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				$this->_properties[$name] = $value;
				return;
			}
		$this->$name = $value;
	}

	public function __get($name)
	{
		$properties = $this->_reflect->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return $this->_properties[$name];
			}
		return $this->$name;
	}

	public function __isset($name)
	{
		$properties = $this->_reflect->getProperties(ReflectionProperty::IS_STATIC);
		foreach ($properties as &$property)
			if ($property->getName() == $name) {
				return isset($this->_properties[$name]);
			}
		return isset($this->$name);
	}

	public function getData($result = 'array')
	{
		$fields = $this->_reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		if ($isArray = ($result == 'array'))
			$data = array();
		else
			$data = new stdClass();
		foreach ($fields as $field) {
			$name = $field->getName();
			if ($isArray)
				$data[$name] = $this->$name;
			else
				$data->$name = $this->$name;
		}
		if (in_array($result, array('both', 'ArrayObject')))
			$data = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
		return $data;
	}

	/*###################################################*/
	protected static function init($target = null, $driver = DB_DRIVER, $pk = DB_OBJECT_KEY)
	{
		static::$_driver = $driver;
		static::$_target = (is_null($target)?static::getSource():$target);
		static::$_pk = $pk;
	}

	public abstract function getSource();

	public static function attributes($name, $value = null)
	{
		static $attrs = array();
		if (is_null($value)) {
			if (isset($attrs[$name])) {
				return $attrs[$name];
			} else return null;
		} else {
			return $attrs[$name] = $value;
		}
	}

	public static function __callStatic($name, $arguments = array())
	{
		$db =& App::db(self::$_target, static::$_driver);
		return call_user_func_array(array($db, $name), $arguments);
	}
}


class UserModel extends Model
{
	public $username;
	public $password;
	public $status = 1;
	public $role;

	public function __construct($target = 'user')
	{
		parent::__construct($target);
	}

	public static function __init($target = 'user')
	{
		parent::init($target);
	}

	public function getSource()
	{
		return 'user';
	}
}


abstract class DBDriver
{
	protected $instance;

	public function __construct($instance = DB_INSTANCE)
	{
		$this->instance = $instance;
	}

	public static function getDbKey($instance = DB_INSTANCE, $driver = DB_DRIVER)
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
}


class MySql extends DBDriver
{
	private static $connections = array();
	private static $currentDatabase = '';

	private $resource = null;
	private $last_query = null;

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
		$this->last_query = $sql;
		$connection =& self::collect($this->instance);
		$this->resource = mysql_query($sql, $connection);
		return ($this->resource ? true : false);
	}

	/*public function find()
	{
		$connection =& self::collect($this->instance);
	}*/

}


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
							$css = PUBLIC_DIR . DS . 'css' . DS . $css;
							if (file_exists($css)) {
								$mTime = filemtime($css);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						} elseif (preg_match('/https?:\/\//i', $css)) {
							$folder = PUBLIC_DIR . DS . 'css' . DS . 'cache' . DS;
							$file = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $css);
							if (file_exists($file)) {
								$tmp = @file_get_contents($file);
							} else {
								$tmp = @file_get_contents($css);
								$tmp = CssMin::minify($tmp);
								if (!is_dir($folder)) mkdir($folder, DIR_WRITE_MODE, true);
								@file_put_contents($file, $tmp);
							}
							if (preg_match('/:\s*url\s*\(/i', $tmp)) {
								$html .= "<link href=\"$css?__av=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
							}
						} else {
							$css = PUBLIC_DIR . DS . $css;
							if (file_exists($css)) {
								$mTime = filemtime($css);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						}
					}
				}

				$nameMd5 = md5($nameMd5);
				$file = PUBLIC_DIR . DS . 'css' . DS . 'cache' . DS . $nameMd5 . '.css';
				if (!file_exists($file) || (ENVIRONMENT != 'Production' && $maxTime > filemtime($file))) {
					$cache = '';
					foreach (self::$html_css as $css) {
						if (strrpos($css, '{') !== false) {
							$cache .= $css;
						} elseif (strrpos($css, '/') === false) {
							$css = PUBLIC_DIR . DS . 'css' . DS . $css;
							if (file_exists($css)) {
								if (ASSETS_OPTIMIZATION & 2) $cache .= self::minAsset($css, true) . "\n";
								else $cache .= @file_get_contents($css) . "\n";
							}
						} elseif (preg_match('/https?:\/\//i', $css)) {
							$folder = PUBLIC_DIR . DS . 'css' . DS . 'cache' . DS;
							$file = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $css);
							if (file_exists($file)) {
								$css = @file_get_contents($file);
							} else {
								$css = @file_get_contents($css);
								if (ASSETS_OPTIMIZATION & 2) $css = CssMin::minify($css);
								if (!is_dir($folder)) mkdir($folder, DIR_WRITE_MODE, true);
								@file_put_contents($file, $css);
							}
							if (!preg_match('/:\s*url\s*\(/i', $css)) {
								$cache .= $css;
							}
						} elseif (file_exists($css)) {
							if (ASSETS_OPTIMIZATION & 2) $tmp = self::minAsset($css, true);
							else $tmp = @file_get_contents($css);
							$cache .= preg_replace('/url\s*\(\s*([\'"])/i', 'url($1../' . dirname($css) . '/', $tmp);
						}
					}
					$cache = str_replace(array('"../', '\'../'), array('"../../', '\'../../'), $cache);
					$folder = PUBLIC_DIR . DS . 'css' . DS . 'cache' . DS;
					if (!is_dir($folder)) mkdir($folder, DIR_WRITE_MODE, true);
					$file = $folder . $nameMd5 . '.css';
					@file_put_contents($file, $cache);
				}

				$file = "css/cache/$nameMd5.css?__av=" . ASSETS_VERSION;
				$html .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" />\n";
			} else {
				foreach (self::$html_css as $css) {
					if (strrpos($css, '{') === false) {
						if (strrpos($css, '/') === false) $css = "css/$css";
						if (ASSETS_OPTIMIZATION & 2 && !preg_match('/https?:\/\//i', $css)) $css = self::minAsset($css);
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
							$js = PUBLIC_DIR . DS . 'js' . DS . $js;
							if (file_exists($js)) {
								$mTime = filemtime($js);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						} elseif (preg_match('/https?:\/\//i', $js)) {
							$folder = PUBLIC_DIR . DS . 'js' . DS . 'cache' . DS;
							$file = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $js);
							if (!file_exists($file)) {
								$js = @file_get_contents($js);
								$js = JSMin::minify($js);
								if (!is_dir($folder)) mkdir($folder, DIR_WRITE_MODE, true);
								@file_put_contents($file, $js);
							}
						} else {
							$js = PUBLIC_DIR . DS . $js;
							if (file_exists($js)) {
								$mTime = filemtime($js);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						}
					}
				}

				$nameMd5 = md5($nameMd5);
				$file = PUBLIC_DIR . DS . 'js' . DS . 'cache' . DS . $nameMd5 . '.js';
				if (!file_exists($file) || (ENVIRONMENT != 'Production' && $maxTime > filemtime($file))) {
					$cache = '';
					foreach ($jss as $js) {
						if (preg_match('/[;\(]/', $js)) {
							$cache .= $js . "\n";
						} elseif (strrpos($js, '/') === false) {
							$js = PUBLIC_DIR . DS . 'js' . DS . $js;
							if (file_exists($js)) {
								if (ASSETS_OPTIMIZATION & 8) $cache .= self::minAsset($js, true) . "\n";
								else $cache .= @file_get_contents($js) . "\n";
							}
						} elseif (preg_match('/https?:\/\//i', $js)) {
							$folder = PUBLIC_DIR . DS . 'js' . DS . 'cache' . DS;
							$file = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $js);
							if (file_exists($file)) {
								$js = @file_get_contents($file);
							} else {
								$js = @file_get_contents($js);
								$js = JSMin::minify($js);
								if (!is_dir($folder)) mkdir($folder, DIR_WRITE_MODE, true);
								@file_put_contents($file, $js);
							}
							$cache .= $js . "\n";
						} elseif (file_exists($js)) {
							if (ASSETS_OPTIMIZATION & 8) $cache .= self::minAsset($js, true) . "\n";
							else $cache .= @file_get_contents($js) . "\n";
						}
					}

					$folder = PUBLIC_DIR . DS . 'js' . DS . 'cache' . DS;
					if (!is_dir($folder)) mkdir($folder, DIR_WRITE_MODE, true);
					$file = $folder . $nameMd5 . '.js';
					@file_put_contents($file, $cache);
				}

				$file = "js/cache/$nameMd5.js?__av=" . ASSETS_VERSION;
				$html .= "<script src=\"$file\" type=\"text/javascript\" language=\"javascript\"></script>\n";
			} else {
				foreach ($jss as $js) {
					if (preg_match('/[;\(]/', $js)) {
						$html .= "<script type=\"text/javascript\" language=\"javascript\">\n{$js}\n</script>\n";
					} else {
						if (strrpos($js, '/') === false) $js = "js/$js";
						if (ASSETS_OPTIMIZATION & 2 && !preg_match('/https?:\/\//i', $js)) $js = self::minAsset($js);
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
			return ($returnContent ? @file_get_contents($file) : $file);
		$minFile = "$pathInfo[dirname]/$pathInfo[filename].min.$pathInfo[extension]";
		if (file_exists($minFile) && (ENVIRONMENT == 'Product' || filemtime($minFile) > @filemtime($file)))
			return ($returnContent ? @file_get_contents($minFile) : $minFile);
		switch (strtolower($pathInfo['extension'])) {
			case 'css':
				$minContent = CssMin::minify(@file_get_contents($file));
				break;
			case 'js':
				$minContent = JSMin::minify(@file_get_contents($file));
				break;
			default:
				return ($returnContent ? @file_get_contents($file) : $file);
		}
		@file_put_contents($minFile, $minContent);
		return ($returnContent ? $minContent : $minFile);
	}
}


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