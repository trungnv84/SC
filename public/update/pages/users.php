<?php
defined('GIT_PATH') || exit;

if (!$_user['root']) header('Location: ' . BASE_URL, false, 302);

$_users = users();
?>
<!DOCTYPE html>
<html>
<head>
	<title>User Management</title>
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap.min.css">
	<!--<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">-->
	<link rel="stylesheet" href="assets/css/jquery-ui-1.10.3.css">
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="assets/css/common.css">
</head>
<body>
<?php require 'pages/common/navbar.php'; ?>
<div class="container">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">User Management</h3>
		</div>
		<!--<div class="panel-body">
			List user.
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-primary">Fetch all</button>
			</div>
		</div>-->
		<table class="table table-striped table-hover">
			<thead>
			<tr>
				<th class="start">Root</th>
				<th>Username</th>
				<th><a class="btn btn-xs btn-success pull-right" href="?_p=user">Add</a></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($_users as $user): ?>
				<?php $cur = $user['username'] == $_user['username']; ?>
				<tr<?php if ($cur) echo ' class="success"'; ?>>
					<td class="cur bold"><?php echo $user['root'] ? '√' : ''; ?></td>
					<td class="nodes"><?php echo $user['username']; ?></td>
					<td>
						<div class="btn-group btn-group-xs pull-right">
							<a class="btn btn-success" href="?_p=user">Add</a>
							<a class="btn btn-info" href="?_p=user&username=<?php echo $user['username']; ?>">Edit</a>
							<?php if (!$user['root']): ?>
								<a class="btn btn-warning" href="?_p=delete&username=<?php echo $user['username']; ?>"
								   onclick="return confirm('Are you sure to delete this account?')">
									Delete
								</a>
							<?php endif; ?>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<!--<pre>
		<?php /*print_r($_versions); */?>
	</pre>-->
</div>

<!--<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>-->
<script src="assets/js/jquery-1.10.2.min.js"></script>
<script src="assets/js/jquery-ui-1.10.3.js"></script>
<script src="assets/bootstrap3/js/bootstrap.min.js"></script>
<script src="assets/js/bootstrap-hover-dropdown.min.js"></script>
</body>
</html>