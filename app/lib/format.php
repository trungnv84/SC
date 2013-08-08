<?php
defined('ROOT_DIR') || exit;

class Format
{
	static function byte($byte, $format = '%01.2lf %s')
	{
		if (($b = round($byte / 1024 / 1024, 2)) > 1) {
			$units = 'MB';
		} elseif (($b = round($byte / 1024, 2)) > 1) {
			$units = 'KB';
		} else {
			$b = round($byte, 2);
			$units = 'B';
		}
		if (strlen($format) == 0)
			$format = '%01d %s';

		return sprintf($format, $b, $units);
	}
}