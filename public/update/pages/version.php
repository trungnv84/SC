<?php
$_versions = versions();
if (isset($_versions)) {
	$has_data = true;
}

if (!isset($has_data)) {
	/*$git_log = launch(GIT_PATH . ' log --all');
	file_put_contents('data/git_log.txt', $git_log, LOCK_EX);
	$_version = logToRevision($git_log);*/

    $versions = launch(GIT_PATH . ' show origin/' . GIT_MAIN_BRANCH . ':' . GIT_VERSION_PATH);
    if (preg_match_all('/(\d+.\d+.\d+)\//i', $versions, $matches)) {
        $versions = array();
        foreach($matches[1] as $k => $name) {
	        $revision = launch(GIT_PATH . ' show origin/' . GIT_MAIN_BRANCH . ':' . GIT_VERSION_PATH . $matches[0][$k] . 'revision.txt');
	        if(preg_match('/\w{40}/', $revision, $revision)) {
		        $revision = $revision[0];
	        } else {
		        //zzz
	        }
            $versions[$name] = array(
                'dir' => $matches[0][$k],
	            'revision' => $revision
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
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" href="assets/css/common.css">
</head>
<body>
	<?php require 'pages/common/navbar.php';?>
	<pre>
		<?php print_r($versions);?>
	</pre>

	<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="assets/bootstrap3/js/bootstrap.min.js"></script>
	<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
</body>
</html>