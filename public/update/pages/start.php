<?php
defined('GIT_PATH') || exit;

if ('POST' == $_SERVER['REQUEST_METHOD']) {
	$username = get('username');
	$password = get('password');
	if ($username && $password) {
		$id = base64_encode(strtolower($username));
		$security = md5(MICRO_TIME_NOW);
		$_users = array(
			$id => array(
				'username' => $username,
				'password' => md5($password . $security) . ':' . $security,
				'root' => true
			)
		);
		users($_users);
		header("Location: " . BASE_URL, false, 302);
	}
	$msg = 'The Username and/or Password is invalid.';
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Update tools start</title>
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap.min.css">
	<!--<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">-->
	<link rel="stylesheet" href="assets/css/jquery-ui-1.10.3.css">
	<link rel="stylesheet" href="assets/bootstrap3/css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="assets/css/common.css">
</head>
<body>

<div class="container">

	<div class="login-template">
		<h1>Update tools start</h1>
		<br />

		<div class="msg-show">
			(Create root user)
		</div>

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
						       autocomplete="off" placeholder="Username" value="<?php echo $username; ?>">
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
						<button type="submit" class="btn btn-default">Create</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

</div>

<!--<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>-->
<script src="assets/js/jquery-1.10.2.min.js"></script>
<script src="assets/js/jquery-ui-1.10.3.js"></script>
<script src="assets/bootstrap3/js/bootstrap.min.js"></script>
</body>
</html>