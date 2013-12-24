<?php
function get($name, $default = null)
{
	if (isset($_POST[$name])) return $_POST[$name];
	if (isset($_GET[$name])) return $_GET[$name];
	return $default;
}

function session($name, $value = null)
{
	if (isset($_SESSION['_UPDATE_TOOL'][$name])) $old = $_SESSION['_UPDATE_TOOL'][$name];
	else $old = $value;
	if (isset($value))
		$_SESSION['_UPDATE_TOOL'][$name] = $value;
	return $old;
}

function versions($versions = null) {
	if (!is_null($versions)) {
		file_put_contents('data/versions.php', 'return ' . var_export($versions)) . ';';
	} elseif (file_exists('data/versions.php')) {
		$versions = require 'data/versions.php';
	}
	return $versions;
}

function updated($updated = null)
{
	if (!is_null($updated)) {
		file_put_contents('data/updated.php', 'return ' . var_export($updated)) . ';';
	} elseif (file_exists('data/updated.php')) {
		$updated = require 'data/updated.php';
	}
	return $updated;
}

function launch($command, $background = false)
{
	if (function_exists('exec')) {
		$last_line = exec($command, $output, $return_var);
		$output = implode("\n", $output);
	} elseif (function_exists('system')) {
		ob_start();
		$last_line = system($command, $return_var);
		$output = ob_get_contents();
		ob_end_clean();
	} elseif (function_exists('passthru')) {
		ob_start();
		passthru($command, $return_var);
		$output = ob_get_contents();
		ob_end_clean();
	}
	//echo '<pre>';var_dump($output);echo '</pre>';
	return $output;

	/*
	// Windows
	if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
		if ($background) {
			pclose(popen('start "background_exec" ' . $call, 'r'));
		} else {
			pclose(popen('start ' . $call, 'r'));
		}
	} // Some sort of UNIX
	else {
		if ($background) {
			exec($call . ' > /dev/null &');
		} else {
			exec($call);
		}
	}
	return true;
	*/
}

function logToRevision($log)
{
	$log = $log;
	$revisions = array();
	if ($log != "" && preg_match_all('/commit\s+(\w{40})\n/i', $log, $matches)) {
		$data = preg_split('/commit\s+\w{40}\n/i', $log);
		foreach($matches[1] as $k => $match) {
			preg_match('/Author:\s+([^\n]+)\n/i', $data[$k+1], $author);
			if (isset($author[1])) {
				$author = htmlentities($author[1]);
				$data[$k+1] = preg_replace('/Author:\s+[^\n]+\n/i', '', $data[$k+1]);
			} else $author = '';

			preg_match('/Date:\s+([^\n]+)\n/i', $data[$k+1], $date);
			if (isset($date[1])) {
				$date = $date[1];
				$data[$k+1] = preg_replace('/Date:\s+[^\n]+\n/i', '', $data[$k+1]);
			} else $date = '';

			$comment = trim($data[$k+1]);

			$revisions[$match] = array(
				'hash' => $match,
				'author' => $author,
				'date' => $date, //date('Y-m-d H:i:s', strtotime($date))
				'comment' => $comment
			);
		}
	}
	return $revisions;
}