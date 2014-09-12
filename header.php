<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">Peer Code Review: <?php echo $_SESSION['course_code']; ?></a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Home Page</a></li>
					<?php
						// Display the admin bar only for administrators.
						if (isset($_SESSION['admin']) && $_SESSION['admin']) {
							echo '<li><a href="admin.php">Admin Panel</a></li>';
						}
						// Display the help center bar only if help is enaled.
						if (isset($_SESSION['helpenabled']) && $_SESSION['helpenabled']) {
							echo '<li><a href="help.php">Help Centre</a></li>';
						}
					?>

					<li><a href="reviewhub.php">Review Hub</a></li>
				</ul>
			</div>
		</div>
	</nav>