<?php
//Include the main handler backend
require_once 'includes/handlers.php';
//Initialise the PCRHandler
$crs = new PCRHandler();
// hardcoding 2 for the time being
$reviews = $crs->getReviews('2');
$annotations = array();
foreach ($reviews as &$review) {
    array_push($annotations, $review->getRow());
}
echo "<pre>"; print_r($annotations); echo "</pre>";
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
	<!-- JQuery text highlighter library -->
	<script type="text/javascript" src="js/jquery.textHighlighter.js"></script>
	

	<script>
		var annotations = [];
        var annotations = <?php echo json_encode($annotations); ?>;
        var edit = -1;
		var selected;
		var prevReview = [];
		var count = 0;

		/**
		 *
		 *
		 */
		function createReview() {
			$('#reviews').append('<div id="review' + count + '" class="reviewContainer"><div id="reviewControls' + count + '" style="display:none"><a class="delete_btn" href="#" onclick="clearReview(' + count + ')" role="button" id="delete' + count + '"></a><a class="edit_btn" href="#" onclick="editAnnotation(' + count + ')" role="button" id="edit' + count + '"></a></div><br><textarea class="reviewContent" rows="2" cols="32" id="textarea' + count + '"></textarea></br><a class="cancel_btn" href="#" onclick="clearReview(' + count + ')" role="button" id="cancel' + count + '">Cancel</a><a class="save_btn" href="#" onclick="getContents(' + count + ')" role="button" id="save' + count + '">Save</a></div>');
			$('#assignment_code').getHighlighter().destroy();
		}
		
		/**
        * Run when the user clicks the x button in the annotation window
        * Finds the corresponding annotation and deletes it from the array
        * using the array splice method
        */
        function clearReview(id) {
            //fix when reviewnum sorted
            var comment = $('#textarea' + id).val();
            for(var i=0; i < annotations.length; i++) {
                if(annotations[i].Comments == comment) {
                    // Check if the review hasn't been saved to the database
                    if (annotations[i].status != 'o') {
                        annotations.splice(i, 1);
                        break;
                    }
                    // if it has then mark it for deletion
                    annotations[i].status = 'd';
                    break;
                }
            }
			// This is the last review created, so decrement count
            if (id == count-1) {
                count = count - 1;
            }
			// want to destroy and recreate to update the id (count)
            if ($('#assignment_code').getHighlighter() !== undefined) {
                $('#assignment_code').getHighlighter().destroy();
            }
            setupHighlighter();
            $('#review' + id).remove();
			// remove the highlight
            $('#assignment_code').getHighlighter().removeHighlights($('#span' + id));
        }
        
		/**
		 *
		 *
		 */
        function cancelEdit(id) {
            $('#textarea' + id).val(prevReview[id]);
            // show/hide things
            reviewContainerOriginalDisplay(id);
			edit = -1;
        }
        
		/**
		 *
		 *
		 */
        function getComments() {
            var innerContents = $('#assignment_code').html();
            var wordArray = innerContents.split('\n');
            for(var i=0; i < annotations.length; i++) {
				// add status to mark as already in database
				annotations[i].status = 'o';
				// hard coding ReviewerID to be 2 for the time being
				// && annotations[i].ReviewerID == '2'
                if (annotations[i].fileName == $( "#file_heading" ).html() ) {
                    var index = parseInt(annotations[i].startIndex);
                    var line = parseInt(annotations[i].startLine);
                    var text = annotations[i].text;
                    var numLines = (text.match(/\n/g) || []).length;
                    var endLine = line + numLines;
                    var spanString = '<span style="background-color:#ffff7b" class="highlighted" id="span' + count + '">';
                    var endIndex = index + text.length + spanString.length;
                    if (numLines > 0) {
                        var textArr = text.split('\n');
                        endIndex = textArr[textArr.length-1].length;
                    }
                    alert(endLine + "::" + wordArray[endLine] + "::" + annotations[i].Comments);
                    wordArray[line] = wordArray[line].slice(0,index) + spanString + wordArray[line].slice(index,wordArray[line].length);
                    wordArray[endLine] = wordArray[endLine].slice(0,endIndex) + "</span>" + wordArray[endLine].slice(endIndex, wordArray[endLine].length); 
                    $('#reviews').append('<div id="review' + count + '" class="reviewContainer"><div id="reviewControls' + count + '"><a class="delete_btn" href="#" onclick="clearReview(' + count + ')" role="button" id="delete' + count + '"></a><a class="edit_btn" href="#" onclick="editAnnotation(' + count + ')" role="button" id="edit' + count + '"></a></div><br><textarea class="reviewContent" rows="2" cols="32" id="textarea' + count + '" readonly="true">'+ annotations[i].Comments + '</textarea></br><a class="cancel_btn" href="#" onclick="cancelEdit(' + count + ')" role="button" id="cancel' + count + '" style="display:none;">Cancel</a><a class="save_btn" href="#" onclick="getContents(' + count + ')" role="button" id="save' + count + '" style="display:none">Save</a></div>');
                    count = count + 1;
                }
            }
            $('#assignment_code').html(wordArray.join('\n'));
        }
        
		/**
		 * Gets the contents of the review when they click
		 * the save button in the review box and stores it in an array
		 * to push into the database later
		 */
        function getContents(id) {
            var comment = $('#textarea'+id).val();
            // add code to make sure the comment is unique
            for(var i=0; i < annotations.length; i++) {
                if (comment == annotations[i].comment) {
                    alert("Your comment matches another comment, please don't take other peoples comments");
                    return;
                }
            }
			$('#cancel' + id).attr('onclick', 'cancelEdit(' + id + ')');
			reviewContainerOriginalDisplay(id);
            if (edit >= 0) {
                if (annotations[edit].prevComment === undefined) {
                    annotations[edit].prevComment = annotations[edit].Comments;
                }
                annotations[edit].Comments = comment;
                if (annotations[edit].status == 'o') {
                    annotations[edit].status = 'e';
                }
                edit = -1;
                return;
            }
			var innerContents = $('#assignment_code').html();
			var wordArray = innerContents.split('\n');
			var startIndex;
			var startLine;
			for (var i = 0; i < wordArray.length; i++) {
				// Find the line the comment starts on
				startIndex = wordArray[i].indexOf('id="span' + id + '"');
				if (startIndex >= 0) {
					startLine = i;
					break;
				}
			}
            annotations.push({"Comments":comment, "text":selected, "status":'n', "startLine":startLine, "startIndex":startIndex, "fileName":$( "#file_heading" ).html()});
            count = count + 1;
            setupHighlighter();
        }
        
		/**
		 *
		 *
		 */
		function reviewContainerOriginalDisplay(id) {
			$('#textarea'+id).attr('readonly', true);
            $('#save' + id).hide();
            
            $('#cancel' + id).hide();
            $('#reviewControls' + id).show();
		}
		
        /**
		 * Gets the reviews from the array and loops through to get
		 * the positions. These arrays are then sent via POST
		 * to get saved in the database.
		 *
		 * This will definitely change to become simpler
		 */
		function saveReviews() {
            alert(JSON.stringify(annotations));
            //AJAX call to store the review in the database
            $.ajax({
			  url: "storeData_dev.php?reviews="+JSON.stringify(annotations),
			  type: "POST"
			})
			  .done(function( retval ) {
                alert("Your comments have been saved! Woohoo!");
                alert(retval);
                for (var i=0; i < annotations.length; i++) {
                    if(annotations[i].status == 'd') {
                        annotations.splice(i, 1);
                    } else {
                        annotations[i].status = '';
                        annotations[i].prevComment = undefined;
                    }
                }
            });
            
        }
        
        /**
         * Function to mark a comment as being modified.
         * Code is nearly identical to delete, so will integrate
         */
        function editAnnotation(id) {
            var comment = $('#textarea' + id).val();
            prevReview[id] = comment;
            $('#textarea' + id).attr('readonly', false);
            $('#reviewControls' + id).hide();
            $('#save' + id).show();
            $('#cancel' + id).show();
            for(var i=0; i < annotations.length; i++) {
                if(annotations[i].Comments == comment) {
                    edit = i;
                    break;
                }
            }
        }
		
		/**
		 * Handles when someone clicks on the file tree
		 *
		 */
		function handleSwap(id) {
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
				// remove previous annotations and add the new ones
				$('#reviews').html('');
				getContents();
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
			<h1>Assignment <span>99</span> Submission</h1>
			<div class="col-md-12">
				<h2 id="file_heading">assign1_additional.cpp</h2>
				<div id="innercontainer">
                    <pre id='assignment_code' style="float:left"><?php
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
				<div id="reviews" style="float:right"></div>
                </div>	
				<p style="float:left;clear:left;">
					<a class="btn btn-primary" href="reviewhub.php" role="button">Submit</a>
					<a class="btn btn-info" href="#" onclick="saveReviews()" role="button">Save</a>
					<a class="btn btn-warning" href="#" role="button">Reset</a>
					<a class="btn btn-warning" href="reviewhub.php" role="button">Close</a>
				</p>
			</div>
		</div>
	</div>
	
	<!-- JQuery text highlighter library setup code -->
	<script type="text/javascript" id="snippet-source">
		function setupHighlighter() {
			$('#assignment_code').textHighlighter({
				onAfterHighlight: function(highlights, range) {
					createReview();
					highlights.id = 'span' + count;
					selected = range.toString();
				},
				id: count
			});
		}
		
		$(document).ready(function() {
			getComments();
			setupHighlighter();
		});
    </script>
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>
