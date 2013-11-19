<?php
defined('ROOT_DIR') || exit;

class File
{
	public static function mkDir($path, $mode = DIR_WRITE_MODE)
	{
		if (!is_dir($path)) {
			return mkdir($path, $mode, true);
		} else {
			return chmod($path, $mode);
		}
	}

	public static function delete()
	{
		//zzz
	}

	public static function destroy($path)
	{
		if (file_exists($path)) {
			chmod($path, FILE_WRITE_MODE);
			return unlink($path);
		} elseif (is_dir($path)) {
			chmod($path, DIR_WRITE_MODE);
			if (self::delete($path)) return rmdir($path);
		}
		return false;
	}
}