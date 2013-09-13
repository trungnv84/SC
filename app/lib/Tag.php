<?php
defined('ROOT_DIR') || exit;

class Tag
{
	private static $html_title = '';
	private static $html_meta = array();
	private static $html_css = array();
	private static $html_js = array();
	private static $html_footer_js = array();

	public static function setHtmlTitle($title)
	{
		self::$html_title = $title;
	}

	public function addAsset($asset, $type, $key = false, $overwrite = false)
	{
		$type = 'html_' . $type;
		if ($key) {
			if (isset(self::$$type[$key]) && !$overwrite) return;
			self::$$type[$key] = $asset;
		} elseif (!in_array($asset, self::$type)) {
			self::$$type[] = $asset;
		}
	}
	
	public function addMetaTag($tag = '', $key = false, $overwrite = false)
	{
		self::addAsset($tag, 'meta', $key, $overwrite);
	}

	public function setMetaKeywords($keywords = '')
	{
		self::addMetaTag("<meta name=\"keywords\" content=\"$keywords\">", 'MetaKeywords', true);
	}

	public function setMetaDescription($description = '')
	{
		self::addMetaTag("<meta name=\"description\" content=\"$description\">", 'MetaDescription', true);
	}

	public function addCSS($css, $key = false, $overwrite = false)
	{
		$this->addAsset($css, 'css', $key, $overwrite);
	}

	public function addJS($js, $key = false, $overwrite = false)
	{
		$this->addAsset($js, 'js', $key, $overwrite);
	}

	public function addFooterJS($js, $key = false, $overwrite = false)
	{
		$this->addAsset($js, 'footer_js', $key, $overwrite);
	}

	public function unShiftCSS($css, $key = false, $overwrite = false)
	{
		if ($key && isset(self::$html_css[$key]) && !$overwrite) return;
		if ($key) $css = array($key => $css);
		else $css = array($css);
		self::$html_css = array_merge($css, self::$html_css);
	}

	public function unShiftJS($js, $key = false, $overwrite = false)
	{
		if ($key && isset(self::$html_js[$key]) && !$overwrite) return;
		if ($key) $js = array($key => $js);
		else $js = array($js);
		self::$html_js = array_merge($js, self::$html_js);
	}

	public function unShiftFooterJS($js, $key = false, $overwrite = false)
	{
		if ($key && isset(self::$html_footer_js[$key]) && !$overwrite) return;
		if ($key) $js = array($key => $js);
		else $js = array($js);
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
			if (ASSETS_OPTIMIZATION & 3) {
				$nameMd5 = $cache = '';
				foreach (self::$html_css as $css) {
					if (strrpos($css, '{') === false) {
						$nameMd5 .= $css;
						if (strrpos($css, '/') === false) {
							$css = APPPATH . 'views/site/' . $this->name . '/css/' . $css;
							if (file_exists($css)) {
								if(ASSETS_OPTIMIZATION & 2) $cache .= $this->minAsset($css) . "\n";
								else $cache .= @file_get_contents($css) . "\n";
							}
						} elseif (preg_match('/https?:\/\//', $css)) {
							$tmp = @file_get_contents($css);
							if (preg_match('/:\s*url\s*\(/i', $tmp)) {
								$html .= "<link href=\"$css?v=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
							} else {
								$cache .= $tmp;
							}
						} elseif (file_exists($css)) {
							if(ASSETS_OPTIMIZATION & 2) $tmp = $this->minAsset($css);
							else $tmp = @file_get_contents($css);
							$cache .= preg_replace('/:\s*url\s*\(\s*([\'"])/i', ': url($1../../../../../' . dirname($css) . '/', $tmp);
						}/* else
							$html .= "<link href=\"$css?v=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";*/
					} else {
						$nameMd5 .= $css;
						$cache .= $css . "\n";
					}
				}
				$cache = str_replace('"../', '"../../', $cache);
				$nameMd5 = md5($nameMd5);
				$cacheMd5 = md5($cache);
				$file = APPPATH . 'views/site/' . $this->name . '/css/cache/' . $nameMd5 . '.css';
				if (file_exists($file)) {
					if (ENVIRONMENT != 'production' && $cacheMd5 != md5_file($file))
						file_put_contents($file, $cache);
				} else {
					$folder = APPPATH . 'views/site/' . $this->name . '/css/cache/';
					if (!is_dir($folder)) mkdir($folder, 0755, true);
					file_put_contents($file, $cache);
				}
				$file = APPFOLDER . "/views/site/$this->name/css/cache/$nameMd5.css?v=$cacheMd5";
				$html .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" />\n";
			} else {
				foreach ($this->_css as $css) {
					if (strrpos($css, '{') === false) {
						if (strrpos($css, '/') === false)
							$css = APPFOLDER . "/views/site/$this->name/css/$css";
						$css .= '?v=' . ASSETS_VERSION;
						$html .= "<link href=\"$css\" rel=\"stylesheet\" type=\"text/css\" />\n";
					} else {
						$html .= "<style type=\"text/css\">\n{$css}\n</style>\n";
					}
				}
			}
		}
		if (isset($this->_js) && count($this->_js)) {
			if (ASSETS_OPTIMIZATION & 12) {
				$nameMd5 = $cache = '';
				foreach ($this->_js as $js) {
					if (preg_match('/[;\(]/', $js)) {
						$nameMd5 .= $js;
						$cache .= $js . "\n";
					} else {
						$nameMd5 .= $js;
						if (strrpos($js, '/') === false)
							$js = APPPATH . 'views/site/' . $this->name . '/js/' . $js;
						if (preg_match('/https?:\/\//', $js))
							$cache .= @file_get_contents($js) . "\n";
						elseif(file_exists($js))
							if(ASSETS_OPTIMIZATION & 8) $cache .= $this->minAsset($js) . "\n";
							else $cache .= @file_get_contents($js) . "\n";
						/*else
							$html .= "<script src=\"$js?v=" . ASSETS_VERSION . "\" type=\"text/javascript\" language=\"javascript\"></script>\n";*/
					}
				}
				$nameMd5 = md5($nameMd5);
				$cacheMd5 = md5($cache);
				$file = APPPATH . 'views/site/' . $this->name . '/js/cache/' . $nameMd5 . '.js';
				if (file_exists($file)) {
					if (ENVIRONMENT != 'production' && $cacheMd5 != md5_file($file))
						file_put_contents($file, $cache);
				} else {
					$folder = APPPATH . 'views/site/' . $this->name . '/js/cache/';
					if (!is_dir($folder)) mkdir($folder, 0755, true);
					file_put_contents($file, $cache);
				}
				$file = APPFOLDER . "/views/site/$this->name/js/cache/$nameMd5.js?v=$cacheMd5";
				$html .= "<script src=\"$file\" type=\"text/javascript\" language=\"javascript\"></script>\n";
			} else {
				foreach ($this->_js as $js) {
					if (preg_match('/[;\(]/', $js)) {
						$html .= "<script type=\"text/javascript\" language=\"javascript\">\n{$js}\n</script>\n";
					} else {
						if (strrpos($js, '/') === false)
							$js = APPFOLDER . "/views/site/$this->name/js/$js";
						$js .= '?v=' . ASSETS_VERSION;
						$html .= "<script src=\"$js\" type=\"text/javascript\" language=\"javascript\"></script>\n";
					}
				}
			}
		}
		return $html;
	}
}