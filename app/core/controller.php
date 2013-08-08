<?php
class Controller
{
	var $template = DEFAULT_TEMPLATE;
	var $view_type = DEFAULT_VIEW_TYPE;

	/*function __construct()
	{
	}*/

	function indexAction()
	{
		if (method_exists($this, 'defaultAction'))
			$this->defaultAction();
		else app()->end('none action -> 404//zzz');
	}

	function view($controller, $name)
	{
		if (isset($this->vars) && is_array($this->vars))
			foreach ($this->vars as $key => &$value) $$key =& $value;
		$file = ROOT_DIR . DS . 'app' . DS . 'template' . DS . $this->template . DS . strtolower($controller) . DS . strtolower($name) . '.php';
		if (file_exists($file)) {
			ob_start();
			require $file;
			$main = ob_get_contents();
			ob_end_clean();
			$view = app()->getView($this->view_type);
			$view->generate($main);
		} else app()->end('none view -> 404//zzz');
	}

	function assign(&$key, &$value = NULL)
	{
		if (is_array($key)) {
			foreach ($key as $k => &$v)
				$this->vars[$k] = & $v;
		} else $this->vars[$key] =& $value;
	}

	function setViewType($view_type)
	{
		$this->view_type = $view_type;
	}
}