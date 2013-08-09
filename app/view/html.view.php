<?php
defined('ROOT_DIR') || exit;

class HtmlView extends View
{
	var $doc_title = '';

	function generate($_main, $layout = DEFAULT_LAYOUT, $template = DEFAULT_TEMPLATE)
	{
		$file =  ROOT_DIR . DS . 'app' . DS . 'template' . DS . $template . DS . 'layout' . DS . $layout . '.php';
		if(file_exists($file)) {
			ob_start();
			require $file;
			$_layout = ob_get_contents();
			ob_end_clean();
			$_doc_title = $this->doc_title;
			$_doc_header = "";
			$_doc_footer = "";
			$file =  ROOT_DIR . DS . 'app' . DS . 'template' . DS . $template . DS . 'html.php';
			if(file_exists($file)) {
				require $file;
			} else app()->end('none document -> 404//zzz');
		} else app()->end('none layout -> 404//zzz');
	}


}