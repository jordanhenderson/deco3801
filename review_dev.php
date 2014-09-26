<?php
//Include the main handler backend
require_once 'includes/handlers.php';
//Initialise the PCRHandler
$crs = new PCRHandler();
// hardcoding 2 for the time being
$reviews = $crs->getReviews('2');
$annotationText = array();
foreach ($reviews as $review) {
    array_push($annotationText, $review->getRow());
}
echo "<pre>"; print_r($annotationText); echo "</pre>";
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
        var testRetrieve = <?php echo json_encode($annotationText); ?>;
        var selected;
        var edit = -1;
        jQuery(function ($) {
            $('#innercontainer').annotator();
		});
        
        $(function getComments() {
            var innerContents = $('#assignment_code').html();
            var wordArray = innerContents.split('\n');
            for(var i=0; i < testRetrieve.length; i++) {
                if (testRetrieve[i].fileName == $( "#file_heading" ).html()) {
                    var index = parseInt(testRetrieve[i].startIndex);
                    var line = parseInt(testRetrieve[i].startLine);
                    var text = testRetrieve[i].text;
                    var numLines = (text.match(/\n/g) || []).length;
                    var endLine = line + numLines;
                    var spanString = '<span class="annotator-hl span' + testRetrieve[i].reviewNum + '">';
                    var endIndex = index + text.length + spanString.length;
                    if (numLines > 0) {
                        var textArr = text.split('\n');
                        endIndex = textArr[textArr.length-1].length;
                    }
                    alert(endLine + "::" + wordArray[endLine]);
                    wordArray[line] = wordArray[line].slice(0,index) + spanString + wordArray[line].slice(index,wordArray[line].length);
                    wordArray[endLine] = wordArray[endLine].slice(0,endIndex) + "</span>" + wordArray[endLine].slice(endIndex, wordArray[endLine].length);
                }
            }
            $('#assignment_code').html(wordArray.join('\n'));
        });
        
        /*
        Get the users comment/review and store it and the position of the review in
        the database
        
        TODO: remove alert once testing is complete and need to store submission
        ID and permissions
        */
        function getHighlighted() {
            selected = window.getSelection().toString();
            selected = selected.replace(/</g, "&lt;");
            selected = selected.replace(/>/g, "&gt;");
            edit = -1;
        }
        
        function getContents() {
            var comment = $('#annotator-field-0').val();
            // add code to make sure the comment is unique
            for(var i=0; i < annotationText.length; i++) {
                if (comment == annotationText[i].comment) {
                    alert('Your comment matches another comment, please dont take other peoples comments');
                    $('annotator-delete').trigger("click");
                    return;
                }
            }
            if (edit >= 0) {
                if (annotationText[edit].prevComment === undefined) {
                    annotationText[edit].prevComment = annotationText[edit].comment;
                }
                annotationText[edit].comment = comment;
                if (annotationText[edit].reviewNum !== undefined) {
                    annotationText[edit].status = 'e';
                }
                edit = -1;
                return;
            }
            annotationText.push({"comment":comment, "text":selected, "status":'n'});
        }
        
        function saveReviews() {
            var startIndex;
            var size = 0;
            $('#assignment_code span').each(function( index, element ) {
                if ($(element).hasClass('annotator-hl')) {
                    $(element).addClass( 'span' + index );
                    size++;
                    for (var i=0; i < annotationText.length; i++) {
                        if (annotationText[i].text === $(element).html() && annotationText[i].reviewNum === undefined) {
                            annotationText[i].reviewNum = index;
                            break;
                        }
                    }
                }                   
            });
            var innerContents = $('#assignment_code').html();
            var wordArray = innerContents.split('\n');
            for (var i=0; i < size; i++) {
                for (var j=0; j < wordArray.length; j++) {
                    startIndex = wordArray[j].indexOf('<span class="annotator-hl span' + i + '">');
                    if (startIndex >= 0) {
                        for (var k=0; k < annotationText.length; k++) {
                            if (annotationText[k].reviewNum === i) {
                                annotationText[k].startIndex = startIndex;
                                annotationText[k].startLine = j;
                                annotationText[k].fileName = $( "#file_heading" ).html();
                                break;
                            }
                        }                        
                        break;
                    }  

                }
            }
            alert(JSON.stringify(annotationText));
            //AJAX call to store the review in the database
            $.ajax({
			  url: "storeData_dev.php?reviews="+JSON.stringify(annotationText),
			  type: "POST"
			})
			  .done(function( retval ) {
                alert("Your comments have been saved! Woohoo!");
                alert(retval);
                for (var i=0; i < annotationText.length; i++) {
                    if(annotationText[i].status == 'd') {
                        annotationText.splice(i, 1);
                    } else {
                        annotationText[i].status = '';
                        annotationText[i].prevComment = undefined;
                    }
                }
            });
            
        }
        
        /*
        * Run when the user clicks the x button in the annotation window
        * Finds the corresponding annotation and deletes it from the array
        * using the array splice method
        */
        function deleteAnnotation() {
            // get the comment and compare against ones in annotationText
            // Comments must be unique
            var comment = $('.annotator-item').children('div').html();
            alert(comment);
            for(var i=0; i < annotationText.length; i++) {
                if(annotationText[i].comment == comment) {
                    // Check if the review hasn't been saved to the database
                    if (annotationText[i].reviewNum === undefined) {
                        annotationText.splice(i, 1);
                        return;
                    }
                    // if it has then mark it for deletion
                    annotationText[i].status = 'd';
                    return;
                }
            }
        }
        
        /*
         * Function to mark a comment as being modified.
         * Code is nearly identical to delete, so will integrate
         */
        function editAnnotation() {
            var comment = $('.annotator-item').children('div').html();
            for(var i=0; i < annotationText.length; i++) {
                if(annotationText[i].comment == comment) {
                    edit = i;
                    break;
                }
            }
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
