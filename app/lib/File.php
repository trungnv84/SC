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

	/*
	 *  foreach (new DirectoryIterator($folder) as $fileInfo) {
			if ($fileInfo->isFile()) {
				unlink($fileInfo->getPathname());
			} elseif (!$fileInfo->isDot() && $fileInfo->isDir()) {
				rmdir($fileInfo->getPathname());
			}
		}
	 * */

	public static function delete($path)
	{
		if (is_dir($path)) {
			$dir_perms = fileperms($path);
			if (DIR_WRITE_MODE != $dir_perms) chmod($path, DIR_WRITE_MODE);

			$dir_handle = opendir($path);
			if (false === $dir_handle) return false;

			if (substr($path, -1) != DS) $path .= DS;
			while ($file = readdir($dir_handle)) {
				if ($file != "." && $file != "..") {
					if (!self::destroy($path . $file)) return false;
				}
			}

			closedir($dir_handle);
			if (DIR_WRITE_MODE != $dir_perms) chmod($path, $dir_perms);
			return true;
		} elseif (file_exists($path)) {
			chmod($path, FILE_WRITE_MODE);
			return unlink($path);
		}
	}

	public static function destroy($path)
	{
		if (is_dir($path)) {
			chmod($path, DIR_WRITE_MODE);
			if (self::delete($path)) return rmdir($path);
		} elseif (file_exists($path)) {
			chmod($path, FILE_WRITE_MODE);
			return unlink($path);
		}
		return false;
	}
}