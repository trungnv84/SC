<?php
defined('GIT_PATH') || exit;

$_versions = versions();
if (isset($_versions) && is_array($_versions)) {
	$has_data = true;
}

if (isset($has_data)) {
	if (get('fetch_all')) {
		versions(false);
		header('Location: ' . BASE_URL, false, 302);
	}
} else {
	launch(GIT_PATH . ' fetch --all');
	launch(GIT_PATH . ' log --all > data/git_log.txt');
	//file_put_contents('data/git_log.txt', $git_log, LOCK_EX);
	//$_version = logAllToRevision($git_log);

	$_nodes = array();
    $_start_revision = false;

	$tags = launch(GIT_PATH . ' tag -l');
	if (trim($tags)) {
		$tagNames = explode("\n", $tags);
		$tags = array();
		foreach ($tagNames as $name) {
			$tags[$name] = logTagToTag($name, launch(GIT_PATH . ' show --pretty=medium ' . $name));
			$_nodes[] = $tags[$name];
		}
	} else $tags = array();

	$branch = launch(GIT_PATH . ' branch -av --no-abbrev');
	if (preg_match_all('/(\*\s+)?(([\w\/\-\_]+)|(\([^\)]+\)))\s+(\w{40})\s+([^\n]+)/i', $branch, $matches)) {
		$branch = array();
		foreach ($matches[2] as $k => $name) {
			if ((bool)$matches[1][$k]) {
				$_start_revision = start_revision($matches[5][$k]);
				if ((bool)$matches[3][$k]) $_almost_branch = almost_branch($matches[3][$k]);
				else $_almost_branch = almost_branch(GIT_MAIN_BRANCH);
			}
			$branch[$name] = array(
				'current' => (bool)$matches[1][$k],
				'object' => (bool)$matches[3][$k],
				'hash' => $matches[5][$k],
				'name' => $name,
				'comment' => htmlentities($matches[6][$k])
			);
			$_nodes[] = $branch[$name];
		}
	} else $branch = array();

	$_versions = versions(loadRevisionFromFile($_nodes, $_start_revision));

}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update versions</title>
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap.min.css">
	<!--<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">-->
	<link rel="stylesheet" href="assets/css/jquery-ui-1.10.3.css">
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="assets/css/common.css">
</head>
<body>
<?php require 'pages/common/navbar.php'; ?>
<div class="container">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">Version list</h3>
		</div>
		<div class="panel-body">
			Version and branch list of current source code.
			<div class="btn-group pull-right">
				<a class="btn btn-sm btn-primary" href="?_p=versions&fetch_all=1">Fetch all</a>
			</div>
		</div>
		<table class="table table-hover">
			<thead>
			<tr>
				<th class="start">&nbsp;</th>
				<th>Version</th>
				<th>&nbsp;</th>
				<th>Author</th>
				<th>Date</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($_versions as $version): ?>
				<?php
				$cur = '';
				$nodes = '';
				foreach ($version['nodes'] as $node) {
					if (isset($node['current']) && $node['current']) $cur = '▐█';
					if (!$node['object']) continue;
					if (true) {
						if (isset($node['author'])) {
							$node_class = 'info';
                            $menu_actions = '';
                        } elseif (false !== strpos($node['name'], '/')) {
							$node_class = 'primary';
						} else {
							$node_class = 'success';
                            $menu_actions = $cur?
                                "<li><a href=\"?_p=version&_a=pull&branch=$node[name]\">Git Pull</a></li>":
                                "<li><a href=\"?_p=version&_a=checkout&branch=$node[name]\">Switch/Checkout</a></li>";
						}

                        $node['name'] = str_replace('remotes/', '', $node['name'], $remote);

                        if ($remote) {
                            $nodes .= "<span class=\"label label-$node_class\">$node[name]</span> ";
                        } else {
                            $nodes .= '
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs dropdown-toggle btn-' . $node_class . '" data-toggle="dropdown" data-hover="dropdown" data-delay="500" data-close-others="true">' .
                                        (isset($node['current']) && $node['current'] ? '► ' : '') .
                                        $node['name'] . ' &nbsp;<span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        ' . $menu_actions . '
                                    </ul>
                                </div> ';
                        }
					} else {
						if (isset($node['author']))
							$nodes .= '<span class="label label-info">';
						elseif (false !== strpos($node['name'], '/')) {
							$nodes .= '<span class="label label-primary">';
						} else {
							$nodes .= '<span class="label label-success">';
						}
						$nodes .= (isset($node['current']) && $node['current'] ? '► ' : '') .
							str_replace('remotes/', '', $node['name']) . '</span> ';
					}
				}
				$nodes .= explode("\n", $version['comment'])[0];
				?>
				<tr<?php if ($cur) echo ' class="success"'; ?>>
					<td class="cur"><?php echo $cur; ?></td>
					<td class="nodes"><?php echo $nodes; ?></td>
					<td>
						<!--<div class="btn-group btn-group-sm pull-right">
							<button type="button" class="btn btn-success">Pull</button>
							<button type="button" class="btn btn-info">Checkout</button>
							<button type="button" class="btn btn-warning">Revert</button>
						</div>-->
					</td>
					<td><?php echo strstr($version['author'], htmlentities(' <'), true); ?></td>
					<td><?php echo date('H:i:s d/m/Y', strtotime(preg_replace('/(\+|\-)(\d+)/', '', $version['date']))); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<!--<pre>
		<?php /*print_r($_versions); */?>
	</pre>-->
</div>

<?php require 'pages/common/footer.php'; ?>

<!--<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>-->
<script src="assets/js/jquery-1.10.2.min.js"></script>
<script src="assets/js/jquery-ui-1.10.3.js"></script>
<script src="assets/bootstrap3/js/bootstrap.min.js"></script>
<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
</body>
</html>