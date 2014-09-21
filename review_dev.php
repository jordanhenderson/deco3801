<?php
//Include the main handler backend
require_once 'includes/handlers.php';
//Initialise the PCRHandler
$crs = new PCRHandler();
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
		var annotationText = [];
        jQuery(function ($) {
			$('#innercontainer').annotator();
		});
        $('#innercontainer').select(function() {
            alert("Selected");
        });
        
        /*
        Get the users comment/review and store it and the position of the review in
        the database
        
        TODO: remove alert once testing is complete and need to store submission
        ID and permissions
        */
        function getContents() {
            alert($('#annotator-field-0').val());
            annotationText.push($('#annotator-field-0').val());
        }
        
        function saveReviews() {
            var commentNum = 0;
            alert(annotationText);
            var innerContents = $('#assignment_code').html();
            var wordArray = innerContents.split('\n');
            //alert(wordArray.length);
            var indexPairs = [];
            var startIndex;
            var startIndexSet;
            var startLine;
            var endIndex;
            
            for (var i=0; i < wordArray.length; i++) {
          
                    startIndex = wordArray[i].indexOf('<span class="annotator-hl">');
                    endIndex = wordArray[i].indexOf('</span>');
                    if (startIndex >= 0) {
                        startIndexSet = startIndex;
                        startLine = i;
                        alert(wordArray[i]);
                        
                    }
                    if (endIndex >= 0) {
                        if($('annotator-h1')){
                            indexPairs.push([
                                startIndexSet, startLine, endIndex, i, annotationText[commentNum], $( "#file_heading" ).html()]);
                            alert(indexPairs);
                            commentNum++;
                        }
                    }  

            }
            //AJAX call to store the review in the database
            $.ajax({
			  url: "storeData_dev.php?reviews="+JSON.stringify(indexPairs),
			  type: "POST"
			})
			  .done(function( retval ) {
                alert("Your comments have been saved! Woohoo!");
                alert(retval);
                $('span.annotator-h1').addClass('submitted');
                alert(1);
            });
        }
        
		//Handles when someone clicks on the file tree
		function handleSwap(id) {
            annotationText = [];
			$('a.active').removeClass('active');
			$('#' + id.split('.')[0]).addClass('active');
			//Loads the selected file into the main content area using AJAX
			$.ajax({
			  url: "load_dev.php?filename="+id,
			  type: "POST"
			})
			  .done(function( filecode ) {
				$( "#assignment_code" ).html( filecode );
				$( "#file_heading" ).html( id );
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
				<h2 id="file_heading">assign1_additional.cpp</h2>
				<div id="innercontainer">
                    <pre id='assignment_code'><?php
                    /*
                    Loads the first file in the file tree if its not empty
                    TODO: remove hard coding
                    */
                    if (count($filesArray) > 0) {
                        $assignment = "/var/www/upload/course_00001/assign_00001/submissions/s1234567/" . $filesArray[0];
                        $handle = fopen($assignment, "r");
                        $contents = fread($handle, filesize($assignment));
                        $contents = str_replace('<', '&lt;', $contents);
                        $contents = str_replace('>', '&gt;', $contents);
                        echo $contents;
                        fclose($handle);
                    }
                ?></pre>
                </div>	
				<p>
					<a class="btn btn-primary" href="reviewhub.php" role="button">Submit</a>
					<a class="btn btn-info" href="#" onclick="saveReviews()" role="button">Save</a>
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
