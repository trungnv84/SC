<?php
if (file_exists('data/versions.php')) {
	require_once 'data/versions.php';
	if (isset($_versions)) {
		$has_data = true;
	}
}

if (!isset($has_data)) {
	//$git_log = launch(GIT_PATH . ' log --all');
	//file_put_contents('data/git_log.txt', $git_log, LOCK_EX);
	//$_version = logToRevision($git_log);

	$branch = launch(GIT_PATH . ' branch -av --no-abbrev');
	if (preg_match_all('/(\*\s+)?([\w\/]+|(\([^\)]+\)))\s+(\w{40})\s+([^\n]+)/i', $branch, $matches)) {
		$branch = array();
		foreach($matches[2] as $k => $name) {
			$branch[$name] = array(
				'current' => (bool)$matches[1][$k],
				'name' => $name,
				'revision' => $matches[4][$k],
				'comment' => $matches[5][$k]
			);
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update versions</title>
</head>
<body>
	<pre>
		<?php print_r($branch);?>
	</pre>
</body>
</html>