<?php
defined('ROOT_DIR') || exit;
class App
{
	static $config;
	private static $vars = array();

	public static function run()
	{
		if (isset($_GET['_url'])) self::parseUrl($_GET['_url']);
		$controller = strtolower(self::getVarName('controller', self::$config->defaultController));
		$action = strtolower(self::getVarName('action', 'default'));
		$ctrl = $controller . 'Controller';
		if (class_exists($ctrl)) {
			$ctrl = new $ctrl;
			$act = $action . 'Action';
			define('CURRENT_CONTROLLER', $controller);
			if (method_exists($ctrl, $act))
				$ctrl->$act();
			unset($ctrl);
			self::view($action, $controller);
		} elseif (self::view_exists($action, $controller)) {
			self::view($action, $controller);
		} else {
			self::end('none controller -> 404//zzz');
		}
	}

	private static function parseUrl(&$url)
	{
		if (isset(self::$config->router) && is_array(self::$config->router)) {
			foreach (self::$config->router as $router) {
				if (preg_match('#^' . $router[0] . '$#', $url, $matches)) {
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

	public static function assign($key, $value = NULL)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v)
				self::$vars[$k] = $v;
		} else self::$vars[$key] = $value;
	}

	public static function view_exists($action, $controller = CURRENT_CONTROLLER, $template = DEFAULT_TEMPLATE, $layout = DEFAULT_LAYOUT, $type = DEFAULT_VIEW_TYPE)
	{
		static $results = array();
		$key = "$template.$type.$layout.$controller.$action";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . DS . $template . DS . $controller . DS . $action . '.php');
		}
		return $results[$key];
	}

	public static function layout_exists($layout, $template = DEFAULT_TEMPLATE)
	{
		static $results = array();
		$key = "$template.$layout";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . DS . $template . DS . 'layout' . DS . $layout . '.php');
		}
		return $results[$key];
	}

	public static function response_type_exists($type, $template = DEFAULT_TEMPLATE)
	{
		static $results = array();
		$key = "$template.$type";
		if (!isset($results[$key])) {
			$results[$key] = file_exists(TEMPLATE_DIR . DS . $template . DS . $type . '.php');
		}
		return $results[$key];
	}

	public static function view($__action, $__controller = CURRENT_CONTROLLER, $__template = DEFAULT_TEMPLATE, $__layout = DEFAULT_LAYOUT, $__type = DEFAULT_VIEW_TYPE)
	{
		if (self::view_exists($__action, $__controller, $__template, $__layout, $__type)) {
			if (isset(self::$vars) && is_array(self::$vars))
				foreach (self::$vars as $__key => &$__val) $$__key =& $__val;
			ob_start();
			require(TEMPLATE_DIR . DS . $__template . DS . $__controller . DS . $__action . '.php');
			if(App::layout_exists($__template, $__layout)) {
				$__main = ob_get_clean();
				require(TEMPLATE_DIR . DS . $__template . DS . 'layout' . DS . $__layout . '.php');
			}
			if(App::response_type_exists($__type, $__template)) {
				$__html_layout = ob_get_clean();
				require(TEMPLATE_DIR . DS . $__template . DS . $__type . '.php');
			}
			ob_end_flush();
			self::end();
		} else {
			self::end('none view -> 404//zzz');
		}
	}

	public static function &getModel($name)
	{
		static $models = [];
		if (!isset($models[$name])) {
			$model_name = $name . 'Model';
			$models[$name] = new $model_name;
		}
		return $models[$name];
	}

	public static function end($status = 0)
	{
		exit($status);
	}
}

App::$config = require(APP_DIR . DS . 'config.php');

function __autoload($class_name)
{
	foreach (App::$config->autoLoadPath as $type => & $path) {
		if (!is_numeric($type)) {
			$len = strlen($type);
			if (substr($class_name, -$len) == $type) {
				$file = substr($class_name, 0, strlen($class_name) - $len);
				$file = $path . DS . $file . '.' . strtolower($type) . '.php';
			} else continue;
		} else $file = $path . DS . $class_name . '.php';
		if (file_exists($file)) {
			require_once($file);
			if (class_exists($class_name)) break;
		}
	}
}