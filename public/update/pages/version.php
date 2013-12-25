<?php
$_versions = versions();
if (isset($_versions)) {
	$has_data = true;
}

if (!isset($has_data)) {
	launch(GIT_PATH . ' fetch --all');
	launch(GIT_PATH . ' log --all > data/git_log.txt');
	//file_put_contents('data/git_log.txt', $git_log, LOCK_EX);
	//$_version = logAllToRevision($git_log);

	$nodes = array();

	$tags = launch(GIT_PATH . ' tag -l');
	if (trim($tags)) {
		$tagNames = explode("\n", $tags);
		$tags = array();
		foreach ($tagNames as $name) {
			$tags[$name] = logTagToTag($name, launch(GIT_PATH . ' show --pretty=medium ' . $name));
			$nodes[] = $tags[$name];
		}
	} else $tags = array();

	$branch = launch(GIT_PATH . ' branch -av --no-abbrev');
	if (preg_match_all('/(\*\s+)?([\w\/\-\_]+|(\([^\)]+\)))\s+(\w{40})\s+([^\n]+)/i', $branch, $matches)) {
		$branch = array();
		foreach ($matches[2] as $k => $name) {
			if ((bool)$matches[1][$k]) $_start_revision = start_revision($matches[4][$k]);
			$branch[$name] = array(
				'current' => (bool)$matches[1][$k],
				'hash' => $matches[4][$k],
				'name' => $name,
				'comment' => htmlentities($matches[5][$k])
			);
			$nodes[] = $branch[$name];
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
<?php require 'pages/common/navbar.php'; ?>
<pre>
		<?php print_r($nodes); ?>
	</pre>

<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="assets/bootstrap3/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
</body>
</html>