<?php
class controller extends object
{
	var $template;

	function __construct()
	{
		parent::__construct();
		$this->template = DEFAULT_TEMPLATE;
	}

	function indexAction()
	{
		if (method_exists($this, 'defaultAction'))
			$this->defaultAction();
		else $this->end('none action -> 404//zzz');
	}

	function view($controller, $name)
	{
		if (isset($this->vars) && is_array($this->vars))
			foreach ($this->vars as $key => &$value) $$key =& $value;
		$file = ROOT_DIR . DS . 'app' . DS . 'template' . DS . $this->template . DS . strtolower($controller) . DS . strtolower($name) . '.php';
		if (file_exists($file)) {
			ob_start();
			require $file;
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		} else $this->end('none view -> 404//zzz');
	}

	function assign($key, &$value)
	{
		$this->vars[$key] =& $value;
	}
}