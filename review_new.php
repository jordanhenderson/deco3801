<?php
//Include the main handler backend
require_once 'includes/handlers.php';
//Initialise the PCRHandler
$crs = new PCRHandler();
// hardcoding 2 for the time being
$reviews = $crs->getReviews('2');
$annotations = array();
foreach ($reviews as $review) {
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
	
	<!-- Annotator -->
	<script src="js/annotator-full.min.js"></script> 
	<link rel="stylesheet" type="text/css" href="css/annotator.min.css">

	<script>
		var annotations = [];
        var annotations = <?php echo json_encode($annotations); ?>;
        var selected;
        var edit = -1;
		var prevReview = [];
		var count = 0;
        
        $(window).load(function() {            
            $( "#createComment" ).click(function() {
				$('#reviews').append('<div id="review' + count + '" class="reviewContainer"><div id="reviewControls' + count + '" style="display:none"><a class="delete_btn" href="#" onclick="clearReview(' + count + ')" role="button" id="delete' + count + '"></a><a class="edit_btn" href="#" onclick="editAnnotation(' + count + ')" role="button" id="edit' + count + '"></a></div><br><textarea class="reviewContent" rows="2" cols="32" id="textarea' + count + '"></textarea></br><a class="cancel_btn" href="#" onclick="clearReview(' + count + ')" role="button" id="cancel' + count + '">Cancel</a><a class="save_btn" href="#" onclick="getContents(' + count + ')" role="button" id="save' + count + '">Save</a></div>');
				$('#createComment').hide();
				$('#assignment_code').getHighlighter().destroy();
            });
        });
		
		/*
        * Run when the user clicks the x button in the annotation window
        * Finds the corresponding annotation and deletes it from the array
        * using the array splice method
        */
        function clearReview(num) {
            //fix when reviewnum sorted
            var comment = $('#textarea' + num).val();
            for(var i=0; i < annotations.length; i++) {
                if(annotations[i].comment == comment) {
                    // Check if the review hasn't been saved to the database
                    if (annotations[i].reviewNum === undefined) {
                        annotations.splice(i, 1);
                        break;
                    }
                    // if it has then mark it for deletion
                    annotations[i].status = 'd';
                    break;
                }
            }
            if (num == count-1) {
                count = count - 1;
            }
            if ($('#assignment_code').getHighlighter() !== undefined) {
                $('#assignment_code').getHighlighter().destroy();
            }
            setupHighlighter();
            $('#review' + num).remove();
            $('#assignment_code').getHighlighter().removeHighlights($('#span' + num));
            // remove the highlight
        }
        
        function cancelEdit(id) {
            $('#textarea' + id).val(prevReview[id]);
            // show/hide things
            $('#textarea' + id).attr('readonly', true);
            $('#save' + id).hide();
            $('#cancel' + id).hide();
            $('#reviewControls' + id).show();
			edit = -1;
        }
        
        $(function getComments() {
            var innerContents = $('#assignment_code').html();
            var wordArray = innerContents.split('\n');
            for(var i=0; i < annotations.length; i++) {
				// hard coding ReviewerID to be 2 for the time being
                if (annotations[i].fileName == $( "#file_heading" ).html() && annotations[i].ReviewerID == 2) {
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
        
		/**
		 * Gets the contents of the review when they click
		 * the save button in the review box and stores it in an array
		 * to push into the database later
		 */
        function getContents(id) {
            var comment = $('#textarea'+id).val();
            alert(comment);
            // add code to make sure the comment is unique
            for(var i=0; i < annotations.length; i++) {
                if (comment == annotations[i].comment) {
                    alert("Your comment matches another comment, please don't take other peoples comments");
                    return;
                }
            }
            if (edit >= 0) {
                if (annotations[edit].prevComment === undefined) {
                    annotations[edit].prevComment = annotations[edit].comment;
                }
                annotations[edit].comment = comment;
                if (annotations[edit].reviewNum !== undefined) {
                    annotations[edit].status = 'e';
                }
                edit = -1;
				$('#textarea'+id).attr('readonly', true);
				$('#save' + id).hide();
				$('#cancel' + id).attr('onclick', 'cancelEdit(' + id + ')');
				$('#cancel' + id).hide();
				$('#reviewControls' + id).show();
                return;
            }
            annotations.push({"comment":comment, "text":selected, "status":'n'});
            count = count + 1;
            $('#textarea'+id).attr('readonly', true);
            $('#save' + id).hide();
            $('#cancel' + id).attr('onclick', 'cancelEdit(' + id + ')');
            $('#cancel' + id).hide();
            $('#reviewControls' + id).show();
            setupHighlighter();
        }
        
        /**
		 * Gets the reviews from the array and loops through to get
		 * the positions. These arrays are then sent via POST
		 * to get saved in the database.
		 *
		 * This will likely change to become simpler
		 */
		function saveReviews() {
            var startIndex;
            var size = 0;
            $('#assignment_code span').each(function( index, element ) {
                if ($(element).hasClass('annotator-hl')) {
                    $(element).addClass( 'span' + index );
                    size++;
                    for (var i=0; i < annotations.length; i++) {
                        if (annotations[i].text === $(element).html() && annotations[i].reviewNum === undefined) {
                            annotations[i].reviewNum = index;
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
                        for (var k=0; k < annotations.length; k++) {
                            if (annotations[k].reviewNum === i) {
                                annotations[k].startIndex = startIndex;
                                annotations[k].startLine = j;
                                annotations[k].fileName = $( "#file_heading" ).html();
                                break;
                            }
                        }                        
                        break;
                    }  

                }
            }
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
        
        /*
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
		
		//Handles when someone clicks on the file tree
		function handleSwap(id) {
            annotations = [];
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
			<h1>Assignment <span>99</span> Submission</h1>
			<div class="col-md-12">
				<h2 id="file_heading">assign1_additional.cpp</h2>
				<div id="innercontainer">
					<button id="createComment" style="display:none;">Make Comment Dude!</button></br>
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
	
	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>
	
	<!-- JQuery text highlighter library setup code -->
	<script type="text/javascript" id="snippet-source">
		function setupHighlighter() {
			alert(count);
			$('#assignment_code').textHighlighter({
				onAfterHighlight: function(highlights, range) {
					$('#createComment').trigger("click");
					highlights.id = 'span' + count;
				},
				id: count
			});
		}
		
		  $(document).ready(function() {
			  setupHighlighter();
		  });
    </script>
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>