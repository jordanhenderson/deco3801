<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Review - PCR</title>

	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">

	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/styles/default.min.css">
	<script src="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/highlight.min.js"></script>
	<script>hljs.initHighlightingOnLoad();</script>
	
	<!-- Annotator -->
	<script src="temp/annotator-full.min.js"></script> 
	<link rel="stylesheet" type="text/css" href="temp/annotator.min.css">

	<script>
		
		jQuery(function ann($) {
			$('#innercontainer').annotator();
		});
		/*Handles when someone clicks on the file tree*/
		function handleSwap(id) {
			$('a.active').removeClass('active');
			$('#' + id.split('.')[0]).addClass('active');
			
			$.ajax({
			  url: "loadFile.php?filename="+id,
			  type: "POST"
			})
			  .done(function( filecode ) {
				$( "#assignment_code" ).html( filecode );
			});	  
		}
	</script>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
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
				<a class="navbar-brand" href="#">Peer Code Review</a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Dashboard</a></li>
					<li><a href="#">Help Centre</a></li>
					<li><a href="admin.php">Admin</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="container">
		<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
			<div class="list-group">
			<?php
		$dir = "/var/www/upload/course_00001/assign_00001/submissions_00001/s1234567/";
		$filesArray = array();
		// Open a directory, and read its contents
		if (is_dir($dir)){
			if ($dh = opendir($dir)){
				while (($file = readdir($dh)) !== false){
					if ($file != "." && $file != ".."){
						array_push($filesArray, $file);
					}
				}
				closedir($dh);
			}
		}
		foreach ($filesArray as $name) {
			if ($name === $filesArray[0]) {
				echo "<a href='#' id='" . explode('.', $name)[0] . "' class='list-group-item active' onclick='handleSwap(\"" . $name . "\");'>" . $name . "</a>";
				continue;
			}
			echo "<a href='#' id='" . explode('.', $name)[0] . "' class='list-group-item' onclick='handleSwap(\"" . $name . "\");'>" . $name . "</a>";
		}
?>
			</div>
		</div>
		<div class="col-md-9">
			<h1>Assignment 99 Submission</h1>
			<div class="col-md-12">
				<h2>main.c</h2>
			
				<?php  
$str = "#include <stdio.h>
#include <sys/types.h> 
#include <sys/socket.h>
#include <netinet/in.h>

int main( int argc, char *argv[] )
{
    int sockfd, newsockfd, portno, clilen;
    char buffer[256];
    struct sockaddr_in serv_addr, cli_addr;
    int  n;

    /* First call to socket() function */
    sockfd = socket(AF_INET, SOCK_STREAM, 0);
    if (sockfd < 0) 
    {
        perror(\"ERROR opening socket\");
        exit(1);
    }
    /* Initialize socket structure */
    bzero((char *) &serv_addr, sizeof(serv_addr));
    portno = 5001;
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_addr.s_addr = INADDR_ANY;
    serv_addr.sin_port = htons(portno);
 
    /* Now bind the host address using bind() call.*/
    if (bind(sockfd, (struct sockaddr *) &serv_addr,
                          sizeof(serv_addr)) < 0)
    {
         perror(\"ERROR on binding\");
         exit(1);
    }

    /* Now start listening for the clients, here process will
    * go in sleep mode and will wait for the incoming connection
    */
    listen(sockfd,5);
    clilen = sizeof(cli_addr);

    /* Accept actual connection from the client */
    newsockfd = accept(sockfd, (struct sockaddr *)&cli_addr, 
                                &clilen);
    if (newsockfd < 0) 
    {
        perror(\"ERROR on accept\");
        exit(1);
    }
    /* If connection is established then start communicating */
    bzero(buffer,256);
    n = read( newsockfd,buffer,255 );
    if (n < 0)
    {
        perror(\"ERROR reading from socket\");
        exit(1);
    }
    printf(\"Here is the message: %s\\n\",buffer);

    /* Write a response to the client */
    n = write(newsockfd,\"I got your message\",18);
    if (n < 0)
    {
        perror(\"ERROR writing to socket\");
        exit(1);
    }
    return 0; 
}";
			//	$str = str_replace('<', '&lt;', $str);
				//$str = str_replace('>', '&gt;', $str);?>
				
				<?php //echo("<pre id='assignment_code'><code>".$str."</code></pre>") ?>
	<div id="innercontainer">
	<?php
		if (count($filesArray) > 0) {
			$assignment = "/var/www/upload/course_00001/assign_00001/submissions_00001/s1234567/" . $filesArray[0];
			$handle = fopen($assignment, "r");
			$contents = fread($handle, filesize($assignment));
			echo "<pre id='assignment_code'><code>" . $contents . "</code></pre>";
			fclose($handle);
		}
	?>			
</div>	
				<p>
					<a class="btn btn-primary" href="#" role="button">Submit</a>
					<a class="btn btn-info" href="#" role="button">Save</a>
					<a class="btn btn-warning" href="#" role="button">Reset</a>
				</p>
			</div>
		</div>
	</div>
	
	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>
