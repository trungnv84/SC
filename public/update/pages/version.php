<?php
if (file_exists('data/versions.php')) {
	require_once 'data/versions.php';
	if (isset($_versions)) {
		$has_data = true;
	}
}

if (!isset($has_data)) {
	$git_log = launch(GIT_PATH . ' log');
	file_put_contents('data/git_log.txt', $git_log, LOCK_EX);
	$_version = logToRevision($git_log);
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update versions</title>
</head>
<body>
	<pre>
		<?php print_r($_version);?>
	</pre>
</body>
</html>