<?php
defined('ROOT_DIR') || exit;

class CacheController extends Controller
{
	function deleteAction($type = null)
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
			case 'asset':
				$folders = array(
					CSS_CACHE_DIR,
					JS_CACHE_DIR
				);
				break;
			case 'all':
				$folders = array(
					PHP_CACHE_DIR,
					CSS_CACHE_DIR,
					JS_CACHE_DIR
				);
			case 'adt':
				$files = File::find(ROOT_DIR, '*' . ADAPTER_FILE_EXT . '.php');
				if (isset($folders)) {
					if ($files) $folders = array_merge($folders, $files);
				} else $folders = $files;
				break;
			default:
				return false;
				break;
		}

		foreach ($folders as $folder) File::delete($folder);

		App::redirect('_tools/cache');
	}
}