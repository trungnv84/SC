<?php
date_default_timezone_set('Asia/Bangkok');
define('MICRO_TIME_NOW', microtime());
define('TIME_NOW', time());
define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', __DIR__);
define('ROOT_DIR', substr(__DIR__, 0, strrpos(__DIR__, DS)));

class App
{
	var $config;

	function __construct()
	{
		$this->config = require(ROOT_DIR . DS . 'app' . DS . 'config.php');
	}

	function run()
	{
		if (isset($_GET['_url'])) $this->parseUrl($_GET['_url']);
		$controller = $this->getVarName('controller', $this->config->defaultController);
		$action = $this->getVarName('action', 'default');
		$ctrl = $controller . 'Controller';
		if (class_exists($ctrl)) {
			$ctrl = new $ctrl;
			$act = $action . 'Action';
			if (method_exists($ctrl, $act))
				$ctrl->$act();
			else {
				$ctrl->view($action, $controller);
			}
		} else $this->end('none controller -> 404//zzz');
	}

	function parseUrl(&$url)
	{
		if (isset($this->config->router) && is_array($this->config->router)) {
			foreach ($this->config->router as $router) {
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

	function GET($key, $default = NULL)
	{
		if (isset($_GET[$key]))
			return $_GET[$key];
		else
			return $default;
	}

	function POST($key, $default = NULL)
	{
		if (isset($_POST[$key]))
			return $_POST[$key];
		else
			return $default;
	}

	function REQUEST($key, $default = NULL)
	{
		if (isset($_REQUEST[$key]))
			return $_REQUEST[$key];
		else
			return $default;
	}

	function getVarName($key, $default = NULL)
	{
		$varName = $this->REQUEST($key);
		if (!is_string($varName) || preg_match('/^[^a-zA-z]|[^a-zA-Z0-9_]/', $varName))
			$varName = $default;
		return $varName;
	}

	function &getView($type = DEFAULT_VIEW_TYPE)
	{
		static $views = [];
		if (!isset($views[$type])) {
			$view_name = $type . 'View';
			$views[$type] = new $view_name;
		}
		return $views[$type];
	}

	function &getModel($name)
	{
		static $models = [];
		if (!isset($models[$name])) {
			$model_name = $name . 'Model';
			$models[$name] = new $model_name;
		}
		return $models[$name];
	}

	function end($status = 0)
	{
		exit($status);
	}
}

function & app()
{
	global $app;
	if (!isset($app)) {
		$app = new App;
	}
	return $app;
}

function __autoload($class_name)
{
	foreach (app()->config->loadPath as $type => & $path) {
		if (!is_numeric($type)) {
			$len = strlen($type);
			if (substr($class_name, -$len) == $type) {
				$file = substr($class_name, 0, strlen($class_name) - $len);
				$type = strtolower($type);
				$file = $path . DS . $file . '.' . $type . '.php';
			} else continue;
		} else $file = $path . DS . $class_name . '.php';
		if (file_exists($file)) {
			require_once $file;
			if (class_exists($class_name)) break;
		}
	}
}

app()->run();

echo '<div>Run time: ', microtime() - MICRO_TIME_NOW, '</div>';
echo '<div>Memory Usage:', Format::byte(memory_get_usage()), ' | ', Format::byte(memory_get_usage(true)), '</div>';
echo '<div>Memory Peak Usage:', Format::byte(memory_get_peak_usage()), ' | ', Format::byte(memory_get_peak_usage(true)), '</div>';