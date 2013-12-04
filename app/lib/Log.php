<?php
class Log
{
	public static function error($errors)
	{
		chmod(APP_LOG_DIR, DIR_WRITE_MODE);
		if (!is_dir(ERROR_LOG_DIR)) {
			mkdir(ERROR_LOG_DIR, DIR_WRITE_MODE, true);
		} else {
			chmod(ERROR_LOG_DIR, DIR_WRITE_MODE);
		}

		if (is_array($errors) && isset($errors['type']))
			$errors['type'] .= ' (' . self::friendlyErrorType($errors['type']) . ')';
		echo "\n<pre><hr/>Last error:\n";
		print_r($errors);
		echo '</pre>';

		$time = explode(' ', MICRO_TIME_NOW);
		$time = date('Y-m-d H:i:s', TIME_NOW) . ' ' . substr($time[0], 2, 6) . rand();
		$wrapper = "\n[[$time]]\n";
		$content = $wrapper . ob_get_contents() . $wrapper;
		ob_clean();

		$file = date(ERROR_LOG_FILE_SUFFIX, TIME_NOW);
		$file = ERROR_LOG_DIR . "error-$file.txt";
		if (file_exists($file)) chmod($file, FILE_WRITE_MODE);
		file_put_contents($file, $content, FILE_APPEND);

		return $time;

		/*echo(ENVIRONMENT == 'Development' ? '<div>' : '<div style="display: none;">'),
		'<a target="_blank" href="', BASE_URL, '?_show_error=1&time=', $time, '">Show html error</a> |
		<a target="_blank" href="', BASE_URL, '?_show_error=2&time=', $time, '">Show raw error</a></div>';*/
	}

	public static function friendlyErrorType($type)
	{
		switch ($type) {
			case E_ERROR: // 1 //
				return 'E_ERROR';
			case E_WARNING: // 2 //
				return 'E_WARNING';
			case E_PARSE: // 4 //
				return 'E_PARSE';
			case E_NOTICE: // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 //
				return 'E_CORE_WARNING';
			case E_CORE_ERROR: // 64 //
				return 'E_COMPILE_ERROR';
			case E_CORE_WARNING: // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return "";
	}

	public static function lib($action, $lib)
	{
		//$time = date('Y-m-d H:i:s', filemtime($lib));
		$lib = str_replace(ROOT_DIR, '', $lib);
		$fileLib = str_replace(array('\\', '/'), '-', $lib);
		if (is_array($action)) $action = implode('.', $action);

		File::mkDir(LIB_LOG_DIR);
		File::mkDir(ACTION_LOG_DIR);

		self::updateLog(LIB_LOG_DIR . $fileLib . '.txt', $action);
		self::updateLog(ACTION_LOG_DIR . $action . '.libs.txt', $lib);
	}

	public static function updateLog($file, $log)
	{
		if (file_exists($file)) {
			$logs = explode("\n", file_get_contents($file));
			if (in_array($log, $logs)) return true;
		}
		return (false !== file_put_contents($file, "$log\n", FILE_APPEND));
	}
}