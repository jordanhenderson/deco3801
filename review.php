<?php
//Initiates a session
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Review - PCR</title>
    
    <!-- jQuery -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">
    
    <!-- Highlighter JS -->
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/styles/default.min.css">
	<script src="http://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.1/highlight.min.js"></script>
	<script>hljs.initHighlightingOnLoad();</script>
	
	<!-- Annotator -->
	<script src="js/annotator-full.min.js"></script> 
	<link rel="stylesheet" type="text/css" href="css/annotator.min.css">

	<script>
		//Initialises Annotator for writing reviews on the page
		jQuery(function ann($) {
			$('#innercontainer').annotator().annotator('setupPlugins');
            /*, {
				tokenUrl: 'includes/token.php',
				Permissions: false,
				AnnotateItPermissions: {}
			});
			/*var innercontainer = $('#innercontainer').annotator()
			innercontainer.annotator('addPlugin', 'Store', {
				tokenUrl: 'includes/token.php',
				annotationData: {
					'uri': 'review.php'
				},
				loadFromSearch: {
					'limit':10,
					'uri':'review.php'
				}
			});*/
		});
		
        //Handles when someone clicks on the file tree
		function handleSwap(id) {
			$('a.active').removeClass('active');
			$('#' + id.split('.')[0]).addClass('active');
			//Loads the selected file into the main content area using AJAX
			$.ajax({
			  url: "loadFile.php?filename="+id,
			  type: "POST"
			})
			  .done(function( filecode ) {
				$( "#assignment_code" ).html( filecode );
				$( "#file_heading" ).html( id );
			});	  
		}
	</script>
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
			<div class="list-group">
            <?php
                /*
                Handles the retrieval of files from the server for the first load
                
                TODO: replace hard coding
                */
                $dir = "/var/www/upload/course_00001/assign_00001/submissions/s1234567/";
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
                // Loop through the directory contents to make the file tree
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
				<h2 id="file_heading"></h2>
				<div id="innercontainer">
                <?php
                    /*
                    Loads the first file in the file tree if its not empty
                    
                    TODO: remove hard coding
                    */
                    if (count($filesArray) > 0) {
                        $assignment = "/var/www/upload/course_00001/assign_00001/submissions/s1234567/" . $filesArray[0];
                        $handle = fopen($assignment, "r");
                        $contents = fread($handle, filesize($assignment));
                        echo "<pre id='assignment_code'><code>" . $contents . "</code></pre>";
                        fclose($handle);
                    }
                ?>			
                </div>	
				<p>
					<a class="btn btn-primary" href="reviewhub.php" role="button">Submit</a>
					<a class="btn btn-info" href="#" role="button">Save</a>
					<a class="btn btn-warning" href="#" role="button">Reset</a>
					<a class="btn btn-warning" href="reviewhub.php" role="button">Close</a>
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
