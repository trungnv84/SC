<?php
defined('ROOT_DIR') || exit;

class App
{
	public static $config;
	public static $module = DEFAULT_MODULE;
	public static $phpCacheFile;
	private static $vars = array();
	private static $template = DEFAULT_TEMPLATE;
	private static $view_type = DEFAULT_VIEW_TYPE;
	private static $layout = DEFAULT_LAYOUT;
	private static $endEvents = array();

	public static function autoLoad($class_name)
	{
		$slat = strrpos($class_name, '\\');
		if ($slat > 0) {
			$pos = strpos($class_name, '\\', 1);
			$adapter_name = substr($class_name, 0, $pos);
			$adapter = strtolower(str_replace('\\', '', $adapter_name)) . DS . 'adapter.php';
		}

		foreach (self::$config->autoLoadPath as $type => $path) {
			if ($slat > 0) {
				$adapter_path = $path . $adapter;
				if (file_exists($adapter_path)) {
					$adapter_namespace = $adapter_name . '\\';
					require_once $adapter_path;
				} else {
					$adapter_namespace = '';
				}
				$file = $adapter_namespace . 'getFileNameAutoLoad';
				$file = $path . $file($class_name) . '.php';
			} else {
				if (is_numeric($type)) {
					$file = $path . $class_name . '.php';
				} else {
					$len = strlen($type);
					if (substr($class_name, -$len) == $type) {
						if (self::$module && isset(self::$config->modulePaths[self::$module][$type])) {
							$path = self::$config->modulePaths[self::$module][$type];
						}
						$file = substr($class_name, 0, strlen($class_name) - $len);
						if ($slat === 0) $file = substr($file, 1);
						$file = $path . strtolower($file) . '.' . strtolower($type) . '.php';
					} else continue;
				}
			}

			if (file_exists($file)) {
				require_once $file;
				if (($slat = $slat > 0) && $adapter_namespace) {
					$adapter_namespace = $adapter_namespace . 'getClassNameAutoLoad';
					$class_name = $adapter_namespace($class_name);
				}

				if (class_exists($class_name)) {
					if (method_exists($class_name, '__init')) $class_name::__init();
					if (PHP_CACHE) self::phpCache($file, !$slat);
					break;
				}
			}
		}
	}

	public static function phpCache($file, $globalSpace = true)
	{
		if (isset(self::$phpCacheFile)) {
			$file = file_get_contents($file);
			if (file_exists(self::$phpCacheFile)) {
				$file = preg_replace('/^\s*<\?php/', '', $file, 1);
				$file = preg_replace('/defined\(\'ROOT_DIR\'\)\s*\|\|\s*(exit|die)\s*(\(\s*\))?\s*;/', '', $file);
				if ($globalSpace) $file = "\nnamespace {" . $file . "\n}\n";
			} elseif ($globalSpace) {
				$file = preg_replace('/^\s*<\?php/', "<?php\nnamespace {", $file, 1) . "\n}\n";
			}
			file_put_contents(self::$phpCacheFile, $file, FILE_APPEND);
		}
	}

	public static function delCache($type)
	{
		switch ($type) {
			case 'php':
				$folders = array(PHP_CACHE_DIR);
				break;
			case 'css':
				$folders = array(CSS_CACHE_DIR);
				break;
			case 'js':
				$folders = array(JS_CACHE_DIR);
				break;
			case 'all':
				$folders = array(
					PHP_CACHE_DIR,
					CSS_CACHE_DIR,
					JS_CACHE_DIR
				);
				break;
			default:
				return;
		}

		foreach ($folders as $folder) {
			if (is_dir($folder)) {
				File::delete($folder);
			}
		}
	}

	public static function run($controller = null, $action = null)
	{
		if (isset($_GET['_url'])) self::parseUrl($_GET['_url']);

		self::autoSetTemplate();

		if (is_null($controller))
			$controller = strtolower(self::getVarName('controller', self::$config->defaultController));
		if (is_null($action))
			$action = strtolower(self::getVarName('action', self::$config->defaultAction));

		$ctrl = ucfirst($controller) . 'Controller';
		if (class_exists($ctrl)) {
			define('CURRENT_CONTROLLER', $controller);
			$ctrl = new $ctrl;
			$act = $action . 'Action';
			if (method_exists($ctrl, $act))
				$ctrl->$act();
			unset($ctrl);
			self::view($action);
		} elseif (self::view_exists($action, $controller)) {
			self::view($action, $controller);
		} else {
			self::end('none controller -> 404//zzz');
		}
	}

	public static function parseUrl(&$url)
	{
		static $parsed;
		if (isset($parsed)) return;
		$parsed = true;

		if (isset(self::$config->modules) && is_array(self::$config->modules)) {
			foreach (self::$config->modules as $name) {
				if (strpos($url, "/$name/") === 0 || $url == "/$name") {
					self::$module = $name;
					break;
				}
			}
		}

		$routed = false;
		if (isset(self::$config->router) && is_array(self::$config->router)) {
			foreach (self::$config->router as $router) {
				if ($routed = preg_match('#^' . $router[0] . '$#', $url, $matches)) {
					foreach ($router[1] as $name => $index) {
						if (isset($matches[$index]) && $matches[$index]) {
							$_GET[$name] = $matches[$index];
							if (!isset($_POST[$name]))
								$_REQUEST[$name] = $matches[$index];
						}
					}
					break;
				}
			}
		}

		if (!$routed) {
			self::end('none controller -> 404//zzz');
		}
	}

	public static function autoSetTemplate()
	{
		if (isset(self::$config->moduleTemplates[self::$module])) {
			self::$template = self::$config->moduleTemplates[self::$module];
		}
	}

	public static function getMethod()
	{
		static $method;
		if (!isset($method)) {
			if (isset($_SERVER['REQUEST_METHOD']))
				$method = strtoupper($_SERVER['REQUEST_METHOD']);
			else
				$method = null;
		}
		return $method;
	}

	public static function GET($key, $default = null)
	{
		if (isset($_GET[$key]))
			return $_GET[$key];
		else
			return $default;
	}

	public static function POST($key, $default = null)
	{
		if (isset($_POST[$key]))
			return $_POST[$key];
		else
			return $default;
	}

	public static function POST_GET($key, $default = null)
	{
		if (isset($_POST[$key]))
			return $_POST[$key];
		elseif (isset($_GET[$key]))
			return $_GET[$key];
		else
			return $default;
	}

	//Using $_REQUEST is strongly discouraged.
	//This super global is not recommended since it includes not only POST and GET data, but also the cookies sent by the request.
	//This can lead to confusion and makes your code prone to mistakes, which could lead to security problems.
	public static function REQUEST($key, $default = null)
	{
		if (isset($_REQUEST[$key]))
			return $_REQUEST[$key];
		else
			return $default;
	}

	public static function getVar($key, $default = null, $type = null)
	{
		$var = self::POST_GET($key, $default);
		if ($type == 'html')
			$filter = Joomla\JFilterInput::getInstance(null, null, 1, 1);
		else
			$filter = Joomla\JFilterInput::getInstance();
		$var = $filter->clean($var, $type);
		return $var;
	}

	public static function getVarString($key, $default = null)
	{
		$var = self::getVar($key, $default, 'string');
		return $var;
	}

	public static function getVarInt($key, $default = 0)
	{
		$var = self::getVar($key, $default, 'int');
		return $var;
	}

	public static function getVarUInt($key, $default = 0)
	{
		$var = self::getVar($key, $default, 'uint');
		return $var;
	}

	public static function getVarFloat($key, $default = 0.0)
	{
		$var = self::getVar($key, $default, 'float');
		return $var;
	}

	public static function getVarBool($key, $default = false)
	{
		$var = self::getVar($key, $default, 'bool');
		return $var;
	}

	public static function getVarWord($key, $default = '')
	{
		$var = self::getVar($key, $default, 'word');
		return $var;
	}

	public static function getVarCmd($key, $default = '')
	{
		$var = self::getVar($key, $default, 'cmd');
		return $var;
	}

	public static function getAllVar($hash = null, $default = array())
	{
		if ($hash === 'METHOD')
			$hash = $_SERVER['REQUEST_METHOD'];
		switch (strtoupper($hash)) {
			case 'GET':
				if (isset($_GET)) $var = $_GET;
				else $var = $default;
				break;
			case 'POST':
				if (isset($_POST)) $var = $_POST;
				else $var = $default;
				break;
			default:
				if (isset($_REQUEST)) $var = $_REQUEST;
				else $var = $default;
		}
		$filter = Joomla\JFilterInput::getInstance();
		$var = $filter->clean($var, null);
		return $var;
	}

	public static function getVarName($key, $default = null)
	{
		$var = self::getVar($key);
		if (!is_string($var) || preg_match('/^[^a-zA-z]|[^a-zA-Z0-9_]/', $var))
			$var = $default;
		return $var;
	}

	public static function is_ajax_request()
	{
		static $result;
		if (!isset($result))
			$result = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
		return $result;
	}

	public static function assign($key, $value = null)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v)
				self::$vars[$k] = $v;
		} else self::$vars[$key] = $value;
	}

	public static function view_exists($action, $controller = CURRENT_CONTROLLER, $template = null, $layout = null, $type = null)
	{
		static $results = array();
		if (is_null($template)) $template =& self::$template;
		if (is_null($type)) $type =& self::$view_type;
		if (is_null($layout)) $layout =& self::$layout;
		$key = "$template.$type.$layout.$controller.$action";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . $template . DS . $controller . DS . $action . '.php');
		}
		return $results[$key];
	}

	public static function layout_exists($layout, $template = null)
	{
		static $results = array();
		if (is_null($template)) $template =& self::$template;
		$key = "$template.$layout";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . $template . DS . 'layout' . DS . $layout . '.php');
		}
		return $results[$key];
	}

	public static function response_type_exists($type, $template = null)
	{
		static $results = array();
		if (is_null($template)) $template =& self::$template;
		$key = "$template.$type";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . $template . DS . $type . '.php');
		}
		return $results[$key];
	}

	public static function view($__action, $__controller = CURRENT_CONTROLLER, $__template = null, $__layout = null, $__type = null)
	{
		if (is_null($__template)) $__template =& self::$template;
		if (is_null($__layout)) $__layout =& self::$layout;
		if (is_null($__type)) $__type =& self::$view_type;

		if (self::view_exists($__action, $__controller, $__template, $__layout, $__type)) {
			if (isset(self::$vars) && is_array(self::$vars))
				foreach (self::$vars as $__key => &$__val) $$__key =& $__val;
			ob_start();
			require(TEMPLATE_DIR . $__template . DS . $__controller . DS . $__action . '.php');
			if (App::layout_exists($__layout, $__template)) {
				$__html__main = ob_get_clean();
				require(TEMPLATE_DIR . $__template . DS . 'layout' . DS . $__layout . '.php');
			}
			if (App::response_type_exists($__type, $__template)) {
				$__html_layout = ob_get_clean();
				require(TEMPLATE_DIR . $__template . DS . $__type . '.php');
			}
			//ob_end_flush();
			self::end();
		} else {
			self::end('none view -> 404//zzz');
		}
	}

	public static function &db($instance = DB_INSTANCE, $driver = DB_DRIVER)
	{
		static $dbs;
		if (!$instance) $instance = 'default';
		if (!isset($dbs[$instance][$driver])) {
			if (!isset($dbs[$instance])) $dbs[$instance] = array();
			$key = call_user_func(array($driver, 'getDbKey'), $instance, $driver);
			if (!isset($dbs[$key])) {
				$dbs[$key] = new $driver($instance);
			}
			$dbs[$instance][$driver] =& $dbs[$key];
		}
		return $dbs[$instance][$driver];
	}

	public static function &getModel($name, $target = null, $driver = DB_DRIVER, $pk = DB_OBJECT_KEY)
	{
		static $models;
		$key = "$name.$target.$driver.$pk";
		if (!isset($models[$key])) {
			$class_name = $name . 'Model';
			$models[$key] = new $class_name($target, $driver, $pk);
		}
		return $models[$key];
	}

	public static function addEndEvents($event)
	{
		self::$endEvents[] = $event;
	}

	private static function afterEnd()
	{
		foreach (self::$endEvents as $event) {
			if (!isset($event['arguments']) || !is_array($event['arguments'])) $event['arguments'] = array();
			call_user_func_array($event['function'], $event['arguments']);
		}
	}

	private static function errorLog($errors)
	{

	}

	public static function end($status = 0)
	{
		static $ended;
		if (isset($ended)) return;
		$ended = true;

		$lastError = error_get_last();
		if (is_null($lastError))
			self::afterEnd();
		else
			self::errorLog($lastError);

		if (ENVIRONMENT == 'Development' && !self::is_ajax_request()) {
			echo '<div>Run time: ', microtime() - MICRO_TIME_NOW, '</div>';
			echo '<div>Memory Usage: ', Format::byte(memory_get_usage()), ' | ', Format::byte(memory_get_usage(true)), '</div>';
			echo '<div>Memory Peak Usage: ', Format::byte(memory_get_peak_usage()), ' | ', Format::byte(memory_get_peak_usage(true)), '</div>';
		}

		die($status);
	}
}

/*################################################*/

App::$config = require APP_DIR . 'config.php';

/*################################################*/

function getFileNameAutoLoad($class_name)
{
	static $names;
	if (strpos($class_name, '\\') === 0) $class_name = substr($class_name, 1);
	if (!isset($names[$class_name])) {
		$names[$class_name] = str_replace('\\', DS, $class_name);
	}
	return $names[$class_name];
}

function __autoload($class_name)
{
	App::autoLoad($class_name);
}

/*################################################*/

register_shutdown_function('App::end');



