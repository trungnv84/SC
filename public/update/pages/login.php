<?php
if ('POST' == $_SERVER['REQUEST_METHOD']) {
	$username = get('username');
	$password = get('password');
	if ($username && $password) {
		require_once 'data/users.php';
		$id = base64_encode($username);
		if (isset($_users[$id])) {
			$security = explode(':', $_users[$id]['password']);
			if ($security[1]) $password .= $security[1];
			if (md5($password) == $security[0]) {
				session('user', $_users[$id]);
				header("Location: " . BASE_URL, false, 302);
			}
		}
	}
	$msg = 'The Username and/or Password is invalid.';
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update tools login</title>
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	<link rel="stylesheet" href="assets/css/common.css">
</head>
<body>

<div class="container">

	<div class="login-template">
		<h1>Update tools login</h1>
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
						       placeholder="Username">
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label" for="password">Password</label>

					<div class="col-sm-8">
						<input type="password" class="form-control" id="password" name="password"
						       placeholder="Password">
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-offset-4 col-sm-8">
						<button type="submit" class="btn btn-default">Sign in</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

</div>

<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="assets/bootstrap3/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
</body>
</html>