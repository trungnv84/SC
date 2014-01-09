<?php
defined('ROOT_DIR') || exit;

class App
{
	public static $config;
	public static $module = DEFAULT_MODULE;
	public static $controllerRunning;
	public static $notFound = false;
	public static $actionRunning;
	public static $phpCacheFile;

	private static $vars = array();
	private static $params = array();
	private static $template = DEFAULT_TEMPLATE;
	private static $view_type = DEFAULT_VIEW_TYPE;
	private static $layout = DEFAULT_LAYOUT;
	private static $endEvents = array();

	/**
	 * App::run //ccc
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $module
	 * @param string $template
	 *
	 * @since   1.0
	 */
	public static function run($controller = null, $action = null, $module = null, $template = null)
	{
		if (self::showError((int)self::GET('_show_error', 0))) self::end();

		if (isset($_GET['_url'])) {
			if (strpos($_GET['_url'], '/404' . REWRITE_SUFFIX) !== false) self::end(404);
			self::parseUrl($_GET['_url']);
		}

		if (is_null($controller))
			$controller = strtolower(self::getVarName('controller', self::$config->defaultController));
		if (is_null($action))
			$action = strtolower(self::getVarName('action', self::$config->defaultAction));

		self::$controllerRunning = $controller;
		self::$actionRunning = $action;

		if (!is_null($module))
			self::$module = $module;

		if ($template)
			self::$template = $template;
		elseif (is_null($template))
			self::autoSetTemplate();

		if (PHP_CACHE) {
			self::$phpCacheFile = PHP_CACHE_DIR . self::$module . ".$controller.$action.php";
			if (file_exists(self::$phpCacheFile)) require_once self::$phpCacheFile;
		}

		$ctrl = ucfirst($controller) . 'Controller';
		if (class_exists($ctrl)) {
			$ctrl = new $ctrl;
			$act = $action . 'Action';
			if (method_exists($ctrl, $act)) {
				call_user_func_array(array($ctrl, $act), self::$params);
			}
			self::assign(get_object_vars($ctrl));
			unset($ctrl, $act);
			if (false !== $template) self::view($action, $controller);
		} elseif (self::view_exists($action, $controller) && false !== $template) {
			self::view($action, $controller);
		} else {
			self::$notFound = true;
			self::end(404, "$controller controller not found.");
		}
	}

	/**
	 * App::exec //ccc
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $module
	 *
	 * @since   1.0
	 */
	public static function exec($controller = null, $action = null, $module = null)
	{
		self::run($controller, $action, $module);
	}

	public static function parseUrl($url)
	{
		static $parsed;
		if (isset($parsed)) return;
		$parsed = true;

		if (isset(self::$config->modules) && is_array(self::$config->modules)) {
			foreach (self::$config->modules as $name) {
				if (strpos($url, "/$name/") === 0 || $url == "/$name") {
					$url = substr($url, strlen($name) + 1);
					self::$module = $name;
					break;
				}
			}
		}

		if ($url && $url != '/') {
			$routed = false;

			if (isset(self::$config->router) && is_array(self::$config->router)) {
				foreach (self::$config->router as $router) {
					if ($routed = preg_match('#^' . $router[0] . '$#', $url, $matches)) {
						foreach ($router[1] as $name => $index) {
							if (isset($matches[$index]) && $matches[$index]) {
								if (!isset($_GET[$name])) {
									$_GET[$name] = $matches[$index];
									if (!isset($_POST[$name]))
										$_REQUEST[$name] = $matches[$index];
								}
							}
						}
						break;
					}
				}
			}

			if (!$routed) {
				if ($routed = preg_match('#^/([^\/.]+)/([^\/.]+)/([^.]+)(\\' . REWRITE_SUFFIX . ')?$#', $url, $matches)) {
					$_GET['controller'] = $matches[1];
					$_GET['action'] = $matches[2];
					self::$params = explode('/', $matches[3]);
				}
			}

			if (!$routed) {
				self::end('none controller -> 404//zzz');
			}
		}
	}

	public static function autoSetTemplate()
	{
		if (isset(self::$config->moduleTemplates[self::$module])) {
			self::$template = self::$config->moduleTemplates[self::$module];
		}
	}

	/*################################################*/
	public static function getCurrentUrl()
	{
		static $url;
		if (!isset($url))
			$url = defined('CURRENT_URL') ? CURRENT_URL : BASE_URL . CURRENT_URI;
		return $url;
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

	public static function GET($name, $default = null)
	{
		if (isset($_GET[$name]))
			return $_GET[$name];
		else
			return $default;
	}

	public static function POST($name, $default = null)
	{
		if (isset($_POST[$name]))
			return $_POST[$name];
		else
			return $default;
	}

	public static function POST_GET($name, $default = null)
	{
		if (isset($_POST[$name]))
			return $_POST[$name];
		elseif (isset($_GET[$name]))
			return $_GET[$name];
		else
			return $default;
	}

	//Using $_REQUEST is strongly discouraged.
	//This super global is not recommended since it includes not only POST and GET data, but also the cookies sent by the request.
	//This can lead to confusion and makes your code prone to mistakes, which could lead to security problems.
	public static function REQUEST($name, $default = null)
	{
		if (isset($_REQUEST[$name]))
			return $_REQUEST[$name];
		else
			return $default;
	}

	public static function getVar($name, $default = null, $type = null, $hash = null)
	{
		if ('METHOD' === strtoupper($hash))
			$hash = $_SERVER['REQUEST_METHOD'];
		switch (strtoupper($hash)) {
			case 'GET':
				$var = self::GET($name, $default);
				break;
			case 'POST':
				$var = self::POST($name, $default);
				break;
			case 'REQUEST':
				$var = self::REQUEST($name, $default);
				break;
			default:
				$var = self::POST_GET($name, $default);
		}
		if ('HTML' === strtoupper($type))
			$filter = Joomla\JFilterInput::getInstance(null, null, 1, 1);
		else
			$filter = Joomla\JFilterInput::getInstance();
		$var = $filter->clean($var, $type);
		return $var;
	}

	public static function getVarString($name, $default = null, $hash = null)
	{
		$var = self::getVar($name, $default, 'STRING', $hash);
		return $var;
	}

	public static function getVarInt($name, $default = 0, $hash = null)
	{
		$var = self::getVar($name, $default, 'INT', $hash);
		return $var;
	}

	public static function getVarUInt($name, $default = 0, $hash = null)
	{
		$var = self::getVar($name, $default, 'UINT', $hash);
		return $var;
	}

	public static function getVarFloat($name, $default = 0.0, $hash = null)
	{
		$var = self::getVar($name, $default, 'FLOAT', $hash);
		return $var;
	}

	public static function getVarBool($name, $default = false, $hash = null)
	{
		$var = self::getVar($name, $default, 'BOOL', $hash);
		return $var;
	}

	public static function getVarWord($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'WORD', $hash);
		return $var;
	}

	public static function getVarCmd($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'CMD', $hash);
		return $var;
	}

	public static function getVarAlNum($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'ALNUM', $hash);
		return $var;
	}

	public static function getVarBase64($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'BASE64', $hash);
		return $var;
	}

	public static function getVarHTML($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'HTML', $hash);
		return $var;
	}

	public static function getVarArray($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'ARRAY', $hash);
		return $var;
	}

	public static function getVarPath($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'PATH', $hash);
		return $var;
	}

	public static function getVarUsername($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'USERNAME', $hash);
		return $var;
	}

	public static function getVarRaw($name, $default = '', $hash = null)
	{
		$var = self::getVar($name, $default, 'RAW', $hash);
		return $var;
	}

	public static function getVarName($name, $default = null)
	{
		$var = self::POST_GET($name);
		if (is_string($var)) $var = preg_replace('/\W+/i', '', $var);
		else $var = $default;
		return $var;
	}

	public static function getAllVar($hash = null, $default = array())
	{
		if ($hash === 'METHOD')
			$hash = self::getMethod();
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

	/*################################################*/
	public static function is_ajax_request()
	{
		static $result;
		if (!isset($result))
			$result = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
		return $result;
	}

	public static function contentType($default = null)
	{
		$headers = headers_list();
		foreach ($headers as $header) {
			if (is_string($header) && preg_match('/^\s*Content-Type\s*:\s*([\w\/]+)/i', $header, $header)) {
				$default = strtolower($header[1]);
				break;
			}
		}
		return $default;
	}

	/*################################################*/
	public static function assign($key, $value = null)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v)
				self::$vars[$k] = $v;
		} else self::$vars[$key] = $value;
	}

	public static function view_exists($action, $controller, $template = null)
	{
		if (is_null($template)) $template =& self::$template;
		return file_exists(TEMPLATE_DIR . $template . DS . $controller . DS . $action . '.php');
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

	public static function view($__action, $__controller, $__template = null, $__layout = null, $__type = null)
	{
		if (is_null($__template)) $__template =& self::$template;
		if (is_null($__layout)) $__layout =& self::$layout;
		if (is_null($__type)) $__type =& self::$view_type;

		if (self::view_exists($__action, $__controller, $__template)) {
			if (isset(self::$vars) && is_array(self::$vars))
				foreach (self::$vars as $__key => &$__val) $$__key =& $__val;

			require TEMPLATE_DIR . $__template . DS . $__controller . DS . $__action . '.php';
		}

		if (self::layout_exists($__layout, $__template)) {
			$__main_html = ob_get_contents();
			ob_clean();
			require TEMPLATE_DIR . $__template . DS . 'layout' . DS . $__layout . '.php';
		}

		if (self::response_type_exists($__type, $__template)) {
			$__main_html = ob_get_contents();
			ob_clean();
			require TEMPLATE_DIR . $__template . DS . $__type . '.php';
		}

		self::end();
	}

	public static function &db($instance = DB_INSTANCE, $driver = DB_DRIVER_NAME)
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
		//zzz xem lai ham nay co cach nao tot hon khong???
		$dbs[$instance][$driver]->init();
		return $dbs[$instance][$driver];
	}

	public static function &getModel($name, $target = null, $driver = DB_DRIVER_NAME, $pk = DB_OBJECT_KEY)
	{
		static $models;
		$key = "$name.$target.$driver.$pk";
		if (!isset($models[$key])) {
			$class_name = $name . 'Model';
			$models[$key] = new $class_name($target, $driver, $pk);
		}
		return $models[$key];
	}

	/*################################################*/
	/**
	 * Attempt to load undefined class
	 *
	 * @param string $class_name Undefined class name
	 */
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
				if (!isset($_file) && file_exists($adapter_path)) {
					require_once $adapter_path;
					$adapter_namespace = $adapter_name . '\\';
					$_file = $adapter_namespace . 'getFileNameAutoLoad';

					if (function_exists($_file))
						$_file = $_file($class_name);
					else
						unset($_file);
				}

				if (!isset($_file)) $_file = self::getFileNameAutoLoad($class_name);

				$file = $path . $_file . '.php';
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
				if (($slat = ($slat > 0)) && isset($adapter_namespace)) {
					$adapter_namespace = $adapter_namespace . 'getClassNameAutoLoad';
					if (function_exists($adapter_namespace))
						$_class_name = $adapter_namespace($class_name);
				} else $_class_name =& $class_name;

				if (class_exists($_class_name)) {
					if (method_exists($_class_name, '__init')) $_class_name::__init();
					if (PHP_CACHE) self::phpCache($file, (!$slat ? $_class_name : false));
					unset($_class_name);
					if (!self::$notFound && ACTION_LIB_LOG) Log::lib(array(
						self::$module,
						self::$controllerRunning,
						self::$actionRunning
					), $file);
					break;
				}
			}
		}
	}

	private static function getFileNameAutoLoad($class_name)
	{
		static $names;
		if (strpos($class_name, '\\') === 0) $class_name = substr($class_name, 1);
		if (!isset($names[$class_name])) {
			$names[$class_name] = str_replace('\\', DS, $class_name);
		}
		return $names[$class_name];
	}

	/*################################################*/
	private static function phpCache($file, $global_space_class = false)
	{
		if (isset(self::$phpCacheFile)) {
			$file = file_get_contents($file);
			if (file_exists(self::$phpCacheFile)) {
				$file = preg_replace('/^\s*<\?php/', '', $file, 1);
				$file = preg_replace('/defined\(\'\w+\'\)\s*(\|\||or)\s*(exit|die)\s*(\(\s*\))?\s*;/', '', $file);
				if ($global_space_class) $file = "\nnamespace {" . $file . "\nif (method_exists('$global_space_class', '__init')) $global_space_class::__init();\n}\n";
			} elseif ($global_space_class) {
				$file = preg_replace('/^\s*<\?php/', "<?php\nnamespace {", $file, 1) . "\nif (method_exists('$global_space_class', '__init')) $global_space_class::__init();\n}\n";
			}
			file_put_contents(self::$phpCacheFile, $file, FILE_APPEND);
		}
	}

	/*################################################*/
	private static function showError($type)
	{
		if (!$type) return false;

		if (ENVIRONMENT == 'Development' || ('POST' === self::getMethod() && ERROR_LOG_PASS === md5(self::POST('pass')))) {
			$time = self::GET('time', '');
			$file = substr($time, 0, 10);
			$file = ERROR_LOG_DIR . "error-$file.txt";

			if (file_exists($file)) {
				$time = "[[$time]]";
				$file = file_get_contents($file);
				$file = explode($time, $file);
				if (isset($file[1])) $file = $file[1];
				else $file = '';
			}

			switch ($type) {
				case 1:
					echo $file;
					break;
				case 2:
					echo '<pre>', htmlspecialchars($file, ENT_COMPAT, 'UTF-8'), '</pre>';
			}

			self::end();
		}

		require APP_LOG_DIR . 'form.html';

		self::end();
	}

	/*################################################*/
	public static function redirect($uri = '', $http_response_code = 302, $method = 'location')
	{
		self::afterEnd();

		if ($uri) {
			if (!preg_match('#^[a-z]+\://#i', $uri)) {
				if (substr($uri, 0, 1) == '/') $uri = substr($uri, 1);
				$uri = BASE_URL . $uri;
			}
		} else $uri = BASE_URL;

		if (headers_sent()) {
			echo "<script>document.location.href='" . str_replace("'", "&apos;", $uri) . "';</script>\n";
		} else {
			switch ($method) {
				case 'refresh':
					header("Refresh:0;url=" . $uri);
					break;
				default:
					http_response_code($http_response_code);
					header("Location: " . $uri, true, $http_response_code);
					break;
			}
			//header('Content-Type: text/html; charset=utf-8'); //$this->charSet
		}
		exit;
	}

	/*################################################*/
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

	public static function end($status = 0, $message = null)
	{
		static $ended;
		if (isset($ended)) return;
		$ended = true;

		$error = error_get_last();
		if (is_null($error))
			self::afterEnd();
		else {
			require_once LIBRARY_DIR . 'Log.php';
			$error = Log::error($error);
			$error = (self::$module != DEFAULT_MODULE ? self::$module . '/' : '') . '404' . REWRITE_SUFFIX . '?time=' . $error;
			self::redirect($error);
		}

		if ($status) {
			if ($error = self::GET('time', false)) {
				$__error_header = (ENVIRONMENT == 'Development' ? '<div>' : '<div id="__error_link" style="display: none;">') .
					'<a href="' . BASE_URL . '">Home</a> | <a href="javascript:history.back();">Back</a> |
					<a target="_blank" href="' . BASE_URL . '?_show_error=1&time=' . $error . '">Show html error</a> |
					<a target="_blank" href="' . BASE_URL . '?_show_error=2&time=' . $error . '">Show raw error</a></div>';
			}
			$error = TEMPLATE_DIR . self::$template . DS . 'error.php';
			if (file_exists($error)) {
				http_response_code(404);
				require $error;
			} else {
				http_response_code($status);
				echo $__error_header;
			}
		} elseif (ACTION_URL_LOG) {
			File::mkDir(ACTION_LOG_DIR);
			Log::updateLog(ACTION_LOG_DIR . self::$module . '.' . self::$controllerRunning . '.' . self::$actionRunning . '.urls.txt', CURRENT_URI ? CURRENT_URI : '/');
		}

		if (ENVIRONMENT != 'Production' && !self::is_ajax_request() && self::contentType('text/html') == 'text/html') {
			$hidden_debug = ENVIRONMENT != 'Development';
			if ($hidden_debug) echo '<div id="__debug" style="display: none">';

			$time[] = explode(' ', microtime());
			$time[] = explode(' ', MICRO_TIME_NOW);
			$time = ($time[0][0] - $time[1][0]) + ($time[0][1] - $time[1][1]);
			echo '<hr/><div>Run time: ', $time, '</div>';
			echo '<div>Memory Usage: ', Format::byte(memory_get_usage()), ' | ', Format::byte(memory_get_usage(true)), '</div>';
			echo '<div>Memory Peak Usage: ', Format::byte(memory_get_peak_usage()), ' | ', Format::byte(memory_get_peak_usage(true)), '</div>';

			if ($hidden_debug) echo '<script>
				document.__dbclick = 0;
				function __show_debug(){
					document.__dbclick++;
					if (3 == document.__dbclick) {
						document.getElementById("__debug").style.display = "block";
						var __error_link = document.getElementById("__error_link");
						if(__error_link) __error_link.style.display = "block";
					} else if(1 == document.__dbclick) {
						setTimeout(function(){
							if(document.__dbclick < 3)
								document.__dbclick = 0;
						}, 5000);
					}
				}

				if(document.addEventListener)
					document.addEventListener("dblclick", __show_debug);
				else if(document.attachEvent)
					document.attachEvent("ondblclick", __show_debug);
				</script></div>';
		}

		exit;
	}
}

/*################################################*/

App::$config = require(APP_DIR . 'config.php');

/*################################################*/

spl_autoload_register('App::autoLoad');

/*################################################*/

register_shutdown_function('App::end');
