<?php
// The Team
// Kevin Systrom (CEO, co-founder)

// Kevin (@kevin) graduated from Stanford University in 2006 with a BS in Management Science & Engineering. He got his first taste of the startup world when he was an intern at Odeo that later became Twitter. He spent two years at Google - the first of which was working on Gmail, Google Reader, and other products and the latter where he worked on the Corporate Development team. Kevin has always had a passion for social products that enable people to communicate more easily, and combined with his passion for photography Instagram is a natural fit.

// Mike Krieger (co-founder)

// Mike (@mikeyk) also graduated from Stanford University where he studied Symbolic Systems with a focus in Human-Computer Interaction. During his undergrad, he interned at Microsoft's PowerPoint team as a PM and at Foxmarks (now Xmarks) as a software developer. He wrote his Master's thesis on how user interfaces can better support collaboration on a large scale. After graduating, he worked at Meebo for a year and a half as a user experience designer and as a front-end engineer before joining the Instagram team doing design & development.

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Dashboard - PCR</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">

	<style>
		tbody > tr {
			cursor: pointer;
		}
	</style>
</head>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">Peer Code Review </a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Get Started!</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<div class="container">
		<div style="position:relative; left:25%; width: 50%;"><h2><i>"For lecturers wanting to better support students, the Peer Code Review system is a must have learning tool!"</i></h2></div>
		<h2>What is PCR?</h2>
		<div>
The Peer Code Review system facilitates a collaborative learning environment to which students can give and receive feedback about assignments. PCR easily integrates with Online Learning Tools, providing a secure environment compatible with any existing learning infrastructure, to make managing any amount of students easy. The system also better facilitates assignment submission and marking, making the system an absolute must for any programming course. 

Using Peer Code Review, teachers are able to define assignments and upload unit tests in any language to be conveniently run as students make submissions. Consequently, students are able to see their results as soon as a submission has been run against the unit tests provided by the administrator.

Peer Code Review has been designed with collaboration in mind, extending the learning process to more than just a student teacher interaction. Our intuitive assignment feedback approach makes it possible for students to mark their peers work, giving teachers a truly effective method to encourage participation and learning within programming.

In addition, collaboration between students and lecturers are also facilitated via our inbuilt help centre. The help centre is reminiscent of stack overflow, providing additional support to students.
		</div>
		<div>
		<h2>The Team</h2>
		<ul type="circle">
		<li>&#8226 Addison Gourluck</li>
		<li>&#8226 Kieran Shannon</li>
		<li>&#8226 Jordan Henderson</li>
		<li>&#8226 Carlie Smits</li>
		<li>&#8226 Sean Winters</li>
		<li>&#8226 Morgan Haig</li>
		</ul>
		</div>
	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>

</body>
</html>