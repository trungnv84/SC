<?php
defined('GIT_PATH') || exit;

$editing = isset($_GET['username']);
if (!($_user['root'] || ($editing && $_GET['username'] == $_user['username'])))
	header('Location: ' . BASE_URL . '?_p=user&username=' . $_user['username'], false, 302);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
	$password = get('password');
	$username = $editing ? $_GET['username'] : get('username');
	if ($username && $password) {
		$_users = users();
		$id = base64_encode(strtolower($username));
		if ($editing || !isset($_users[$id])) {
			$security = md5(MICRO_TIME_NOW);
			$root = $editing ? $_users[$id]['root'] : false;
			$_users[$id] = array(
				'username' => $username,
				'password' => md5($password . $security) . ':' . $security,
				'root' => $root
			);
			users($_users);
			header('Location: ' . ($_user['root'] ? BASE_URL . '?_p=users' : BASE_URL), false, 302);
		} else
			$msg = 'The Username has been in use.';
	} else
		$msg = 'The Username and/or Password is invalid.';
} else {
	$username = $editing ? $_GET['username'] : get('username');
	if ($username) {
		$_users = users();
		$id = base64_encode(strtolower($username));
		if (!isset($_users[$id]))
			header('Location: ' . BASE_URL . '?_p=users', false, 302);
	}
}

$title = $editing ? 'Change password' : 'Create user';
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title; ?></title>
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap.min.css">
	<!--<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">-->
	<link rel="stylesheet" href="assets/css/jquery-ui-1.10.3.css">
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="assets/css/common.css">
</head>
<body>
<?php require 'pages/common/navbar.php'; ?>
<div class="container">
	<div class="login-template">
		<h1><?php echo $title; ?></h1>
		<br />
		<?php if (isset($msg)): ?>
			<div class="alert alert-danger col-sm-offset-4 col-sm-5">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				<?php echo $msg; ?>
			</div>
		<?php endif; ?>
		<form class="form-horizontal" role="form" method="post">
			<fieldset class="col-sm-offset-4 col-sm-4">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="username">Username</label>

					<div class="col-sm-8">
						<input type="text" class="form-control" id="username" name="username"
						       autocomplete="off" placeholder="Username"
						       value="<?php echo $username; ?>"<?php if ($editing) echo ' readonly="readonly"'; ?>>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label" for="password">Password</label>

					<div class="col-sm-8">
						<input type="password" class="form-control" id="password" name="password"
						       autocomplete="off" placeholder="Password">
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-offset-4 col-sm-8">
						<button type="submit" class="btn btn-primary">Save</button>
						<a class="btn btn-default" href="<?php echo BASE_URL, $_user['root']?'?_p=users':'';?>">Cancel</a>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
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