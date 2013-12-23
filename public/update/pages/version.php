<?php
if (file_exists('data/versions.php')) {
	require_once 'data/versions.php';
	if (isset($_versions)) {
		$has_data = true;
	}
}

if (!isset($has_data)) {
	/*$git_log = launch(GIT_PATH . ' log --all');
	file_put_contents('data/git_log.txt', $git_log, LOCK_EX);
	$_version = logToRevision($git_log);*/

    $versions = launch(GIT_PATH . ' show origin/' . GIT_MAIN_BRANCH . ':' . GIT_VERSION_PATH);
    if (preg_match_all('/(\d+.\d+.\d+)\//i', $versions, $matches)) {
        $versions = array();
        foreach($matches[1] as $k => $name) {
            $versions[$name] = array(
                'dir' => $matches[0][$k]
            );
        }
    }

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
	} else $branch = array();

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update versions</title>
</head>
<body>
	<pre>
		<?php print_r($versions);?>
	</pre>
</body>
</html>