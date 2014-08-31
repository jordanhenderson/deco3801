<?php
echo '
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">Peer Code Review</a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Home Page</a></li>'; 


					if(isset($_SESSION['helpenabled']) && $_SESSION['helpenabled'] == 1) {
					echo '
					<li><a href="help.php">Help Centre</a></li>
					<li><a href="reviewhub.php">Review Hub</a></li>
					<li><a href="#">Feedback</a></li>
				</ul>
			</div>
		</div>
	</nav>';
}
else
{
	echo '
					
					<li><a href="reviewhub.php">Review Hub</a></li>
					<li><a href="#">Feedback</a></li>
				</ul>
			</div>
		</div>
	</nav>';
}
?>