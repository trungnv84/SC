<?php
defined('ROOT_DIR') || exit;
class App
{
	public static $config;
	public static $module = DEFAULT_MODULE;
	private static $vars = array();
	private static $template = DEFAULT_TEMPLATE;
	private static $view_type = DEFAULT_VIEW_TYPE;
	private static $layout = DEFAULT_LAYOUT;
	private static $endEvents = array();

	public static function run()
	{
		if (isset($_GET['_url'])) self::parseUrl($_GET['_url']);
		else self::autoSetTemplate();
		$controller = strtolower(self::getVarName('controller', self::$config->defaultController));
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

	private static function parseUrl(&$url)
	{
		if (isset(self::$config->modules) && is_array(self::$config->modules)) {
			foreach (self::$config->modules as $name) {
				if (strpos($url, "/$name/") === 0 || $url == "/$name") {
					self::$module = $name;
					break;
				}
			}
		}

		$routed = false;
		self::autoSetTemplate();
		if (isset(self::$config->router) && is_array(self::$config->router)) {
			foreach (self::$config->router as $router) {
				if ($routed = preg_match('#^' . $router[0] . '$#', $url, $matches)) {
					foreach ($router[1] as $name => $index) {
						if (isset($matches[$index])) {
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

	private static function autoSetTemplate()
	{
		if (isset(self::$config->moduleTemplates[self::$module])) {
			self::$template = self::$config->moduleTemplates[self::$module];
		}
	}

	public static function GET($key, $default = NULL)
	{
		if (isset($_GET[$key]))
			return $_GET[$key];
		else
			return $default;
	}

	public static function POST($key, $default = NULL)
	{
		if (isset($_POST[$key]))
			return $_POST[$key];
		else
			return $default;
	}

	//Using $_REQUEST is strongly discouraged.
	//s inputThis super global is not recommended since it includes not only POST and GET data, but also the cookies sent by the request.
	//This can lead to confusion and makes your code prone to mistakes, which could lead to security problems.
	public static function REQUEST($key, $default = NULL)
	{
		if (isset($_REQUEST[$key]))
			return $_REQUEST[$key];
		else
			return $default;
	}

	public static function getVarName($key, $default = NULL)
	{
		$varName = self::REQUEST($key);
		if (!is_string($varName) || preg_match('/^[^a-zA-z]|[^a-zA-Z0-9_]/', $varName))
			$varName = $default;
		return $varName;
	}

	public static function is_ajax_request()
	{
		static $result;
		if (!isset($result))
			$result = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
		return $result;
	}

	public static function assign($key, $value = NULL)
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
			$results[$key] = file_exists(TEMPLATE_DIR . DS . $template . DS . $controller . DS . $action . '.php');
		}
		return $results[$key];
	}

	public static function layout_exists($layout, $template = null)
	{
		static $results = array();
		if (is_null($template)) $template =& self::$template;
		$key = "$template.$layout";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . DS . $template . DS . 'layout' . DS . $layout . '.php');
		}
		return $results[$key];
	}

	public static function response_type_exists($type, $template = null)
	{
		static $results = array();
		if (is_null($template)) $template =& self::$template;
		$key = "$template.$type";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . DS . $template . DS . $type . '.php');
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
			require(TEMPLATE_DIR . DS . $__template . DS . $__controller . DS . $__action . '.php');
			if (App::layout_exists($__layout, $__template)) {
				$__html__main = ob_get_clean();
				require(TEMPLATE_DIR . DS . $__template . DS . 'layout' . DS . $__layout . '.php');
			}
			if (App::response_type_exists($__type, $__template)) {
				$__html_layout = ob_get_clean();
				require(TEMPLATE_DIR . DS . $__template . DS . $__type . '.php');
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
		if(!$instance) $instance = 'default';
		if (!isset($dbs[$instance][$driver])) {
			if (!isset($dbs[$instance])) $dbs[$instance] = array();
			$key = call_user_func(array($driver, 'getDbKey'), $instance, $driver);
			if(!isset($dbs[$key])) {
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

	public static function end($status = 0)
	{
		static $ended;
		if (!isset($ended)) {
			$ended = true;
			self::afterEnd();

			if (ENVIRONMENT == 'Development' && !self::is_ajax_request()) {
				echo '<div>Run time: ', microtime() - MICRO_TIME_NOW, '</div>';
				echo '<div>Memory Usage: ', Format::byte(memory_get_usage()), ' | ', Format::byte(memory_get_usage(true)), '</div>';
				echo '<div>Memory Peak Usage: ', Format::byte(memory_get_peak_usage()), ' | ', Format::byte(memory_get_peak_usage(true)), '</div>';
			}

			die($status);
		}
	}
}

/*################################################*/

App::$config = require(APP_DIR . DS . 'config.php');

register_shutdown_function('App::end');

/*################################################*/

function __autoload($class_name)
{
	foreach (App::$config->autoLoadPath as $type => & $path) {
		if (!is_numeric($type)) {
			$len = strlen($type);
			if (substr($class_name, -$len) == $type) {
				if (App::$module && isset(App::$config->modulePaths[App::$module][$type])) {
					$path = App::$config->modulePaths[App::$module][$type];
				}
				$file = substr($class_name, 0, strlen($class_name) - $len);
				$file = $path . DS . strtolower($file) . '.' . strtolower($type) . '.php';
			} else continue;
		} else $file = $path . DS . $class_name . '.php';
		if (file_exists($file)) {
			require_once($file);
			if (class_exists($class_name)) {
				if (method_exists($class_name, '__init'))
					$class_name::__init();
				break;
			}
		}
	}
}