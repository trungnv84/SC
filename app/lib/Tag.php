<?php
defined('ROOT_DIR') || exit;

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
			if (ASSETS_OPTIMIZATION & 3) {
				$maxTime = 0;
				$nameMd5 = '';
				foreach (self::$html_css as $css) {
					if (strrpos($css, '{') === false) {
						$nameMd5 .= $css;
						if (strrpos($css, '/') === false) {
							$css = PUBLIC_DIR . DS . 'css' . DS . $css;
							if (file_exists($css)) {
								$mTime = filemtime($css);
								if ($maxTime < $mTime) $maxTime = $mTime;
							}
						} elseif (!preg_match('/https?:\/\//', $css)) {
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
						if (strrpos($css, '/') === false) {
							$css = PUBLIC_DIR . DS . 'css' . DS . $css;
							if (file_exists($css)) {
								if (ASSETS_OPTIMIZATION & 2) $cache .= self::minAsset($css) . "\n";
								else $cache .= @file_get_contents($css) . "\n";
							}
						} elseif (preg_match('/https?:\/\//', $css)) {
							$folder = PUBLIC_DIR . DS . 'css' . DS . 'cache' . DS;
							$file = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $css);
							if (file_exists($file)) {
								$tmp = @file_get_contents($file);
							} else {
								$tmp = @file_get_contents($css);
								if (!is_dir($folder)) mkdir($folder, 0755, true);
								file_put_contents($file, $tmp);
							}
							if (preg_match('/:\s*url\s*\(/i', $tmp)) {
								$html .= "<link href=\"$css?__av=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
							} else {
								$cache .= $tmp;
							}
						} elseif (file_exists($css)) {
							if (ASSETS_OPTIMIZATION & 2) $tmp = self::minAsset($css);
							else $tmp = @file_get_contents($css);
							$cache .= preg_replace('/url\s*\(\s*([\'"])/i', 'url($1../' . dirname($css) . '/', $tmp);
						}
					}
					$cache = str_replace(array('"../', '\'../'), array('"../../', '\'../../'), $cache);
					$folder = PUBLIC_DIR . DS . 'css' . DS . 'cache' . DS;
					if (!is_dir($folder)) mkdir($folder, 0755, true);
					$file = $folder . $nameMd5 . '.css';
					file_put_contents($file, $cache);
				}

				$file = "css/cache/$nameMd5.css?__av=" . ASSETS_VERSION; //BASE_URL
				$html .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" />\n";

				/*#############################
				$nameMd5 = $cache = '';
				foreach (self::$html_css as $css) {
					if (strrpos($css, '{') === false) {
						if (strrpos($css, '/') === false) {
							$nameMd5 .= $css;
							$css = PUBLIC_DIR . '/css/' . $css;
							if (file_exists($css)) {
								if (ASSETS_OPTIMIZATION & 2) $cache .= self::minAsset($css) . "\n";
								else $cache .= @file_get_contents($css) . "\n";
							}
						} elseif (preg_match('/https?:\/\//', $css)) {
							$folder = PUBLIC_DIR . '/css/cache/';
							$cf = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $css);
							if (file_exists($cf)) {
								$tmp = @file_get_contents($cf);
							} else {
								$tmp = @file_get_contents($css);
								if (!is_dir($folder)) mkdir($folder, 0755, true);
								file_put_contents($cf, $tmp);
							}
							if (preg_match('/:\s*url\s*\(/i', $tmp)) {
								$html .= "<link href=\"$css?__av=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
							} else {
								$nameMd5 .= $css;
								$cache .= $tmp;
							}
						} elseif (file_exists($css)) {
							$nameMd5 .= $css;
							if (ASSETS_OPTIMIZATION & 2) $tmp = self::minAsset($css);
							else $tmp = @file_get_contents($css);
							$cache .= preg_replace('/url\s*\(\s*([\'"])/i', 'url($1../' . dirname($css) . '/', $tmp);
						}
						//else $html .= "<link href=\"$css?__av=" . ASSETS_VERSION . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
					} else {
						$nameMd5 .= $css;
						$cache .= $css . "\n";
					}
				}
				$cache = str_replace(array('"../', '\'../'), array('"../../', '\'../../'), $cache);
				$nameMd5 = md5($nameMd5);
				$cacheMd5 = md5($cache);
				$file = PUBLIC_DIR . '/css/cache/' . $nameMd5 . '.css';
				if (file_exists($file)) {
					if (ENVIRONMENT != 'Production' && $cacheMd5 != md5_file($file))
						file_put_contents($file, $cache);
				} else {
					$folder = PUBLIC_DIR . '/css/cache/';
					if (!is_dir($folder)) mkdir($folder, 0755, true);
					file_put_contents($file, $cache);
				}
				$file = "css/cache/$nameMd5.css?__av=$cacheMd5"; //BASE_URL
				$html .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" />\n";
				#############################*/
			} else {
				foreach (self::$html_css as $css) {
					if (strrpos($css, '{') === false) {
						if (strrpos($css, '/') === false)
							$css = "css/$css"; //BASE_URL
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
			if (ASSETS_OPTIMIZATION & 12) {
				$nameMd5 = $cache = '';
				foreach ($jss as $js) {
					if (preg_match('/[;\(]/', $js)) {
						$nameMd5 .= $js;
						$cache .= $js . "\n";
					} else {
						$nameMd5 .= $js;
						if (strrpos($js, '/') === false)
							$js = PUBLIC_DIR . '/js/' . $js;
						if (preg_match('/https?:\/\//', $js)) {
							$folder = PUBLIC_DIR . '/js/cache/';
							$cf = $folder . preg_replace('/[^a-z0-9\.]+/i', '-', $js);
							if (file_exists($cf)) {
								$tmp = @file_get_contents($cf);
							} else {
								$tmp = @file_get_contents($js);
								if (!is_dir($folder)) mkdir($folder, 0755, true);
								file_put_contents($cf, $tmp);
							}
							$cache .= $tmp . "\n";
						} elseif (file_exists($js))
							if (ASSETS_OPTIMIZATION & 8) $cache .= self::minAsset($js) . "\n";
							else $cache .= @file_get_contents($js) . "\n";
						/*else
							$html .= "<script src=\"$js?v=" . ASSETS_VERSION . "\" type=\"text/javascript\" language=\"javascript\"></script>\n";*/
					}
				}
				$nameMd5 = md5($nameMd5);
				$cacheMd5 = md5($cache);
				$file = PUBLIC_DIR . '/js/cache/' . $nameMd5 . '.js';
				if (file_exists($file)) {
					if (ENVIRONMENT != 'Production' && $cacheMd5 != md5_file($file))
						file_put_contents($file, $cache);
				} else {
					$folder = PUBLIC_DIR . '/js/cache/';
					if (!is_dir($folder)) mkdir($folder, 0755, true);
					file_put_contents($file, $cache);
				}
				$file = "js/cache/$nameMd5.js?v=$cacheMd5"; //BASE_URL
				$html .= "<script src=\"$file\" type=\"text/javascript\" language=\"javascript\"></script>\n";
			} else {
				foreach ($jss as $js) {
					if (preg_match('/[;\(]/', $js)) {
						$html .= "<script type=\"text/javascript\" language=\"javascript\">\n{$js}\n</script>\n";
					} else {
						if (strrpos($js, '/') === false)
							$js = "js/$js"; //BASE_URL
						$js .= '?v=' . ASSETS_VERSION;
						$html .= "<script src=\"$js\" type=\"text/javascript\" language=\"javascript\"></script>\n";
					}
				}
			}
		}
		return $html;
	}

	private static function minAsset($file)
	{
		$pathInfo = pathinfo($file);
		if (substr($pathInfo['filename'], -4) == '.min')
			return @file_get_contents($file);
		$minFile = "$pathInfo[dirname]/$pathInfo[filename].min.$pathInfo[extension]";
		if (file_exists($minFile) && (ENVIRONMENT == 'Product' || filemtime($minFile) > @filemtime($file)))
			return @file_get_contents($minFile);
		switch (strtolower($pathInfo['extension'])) {
			case 'css':
				$minContent = CssMin::minify(@file_get_contents($file));
				break;
			case 'js':
				$minContent = JSMin::minify(@file_get_contents($file));
				break;
			default:
				return @file_get_contents($file);
		}
		file_put_contents($minFile, $minContent);
		return $minContent;
	}
}