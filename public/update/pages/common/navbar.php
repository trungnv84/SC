<nav class="navbar navbar-default" role="navigation" style="visibility: hidden">
</nav>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse"
			        data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo BASE_URL; ?>">Update tools</a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li<?php if (CURRENT_URI == '') echo ' class="active"'; ?>><a
						href="<?php echo BASE_URL; ?>">Versions</a></li>
				<!--<li><a href="#">Link</a></li>-->
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="500"
					   data-close-others="true">Management <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#">Commit Log</a></li>
						<li><a href="#">Update Log</a></li>
						<li><a href="#">Access Log</a></li>
						<?php if ($_user['root']): ?>
							<li class="divider"></li>
							<li><a href="?_p=users">List Account</a></li>
							<li><a href="?_p=user">Create Account</a></li>
						<?php endif; ?>
					</ul>
				</li>
			</ul>
			<form class="navbar-form navbar-left" role="search">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="Search">
				</div>
				<button type="submit" class="btn btn-default">Submit</button>
			</form>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="<?php echo str_replace('update/', '', BASE_URL); ?>" target="_blank">Website</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle"
					   data-toggle="dropdown" data-hover="dropdown" data-delay="500"
					   data-close-others="true"><?php echo $_username = $_user['username']; ?> <b
							class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#?_p=logs&username=<?php echo $_username; ?>">My Access Log</a></li>
						<li><a href="?_p=user&username=<?php echo $_username; ?>">Change Password</a></li>
						<li class="divider"></li>
						<li><a href="?_p=logout">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
		<!-- /.navbar-collapse -->
	</div>
	<!-- /.container -->
</nav>
