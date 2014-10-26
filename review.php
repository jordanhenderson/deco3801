<?php
//Include the main handler backend
require_once 'includes/handlers.php';
//Initialise the PCRHandler
$crs = new PCRHandler();
$initialFile = '';
// Get the submissionID from the url
$subID = ''.$_GET['subid'];
while (strlen($subID) < 5) {
	$subID = '0'.$subID;
}

$courseid = $_SESSION['course_id'];

// This currently returns an empty value (TODO does it still?)
$submission = $crs->getSubmissionForReviewing($subID);
$assignid = ''.$submission->getAssignmentID();
while (strlen($assignid) < 5) {
	$assignid = '0'.$assignid;
}

// Get the owner of the submission
$owner = $submission->getOwner();
$isOwner = 0;
// Check who is accessing the page (submission owner or reviewer)
if (intval($_SESSION['user_id']) == intval($owner)) {
	// Load all submitted reviews made for the submission for viewing
	$reviews = $submission->getConditionalReviews(1); // submitted == 1
	$isOwner = 1;
} else {
	// Load only the reviews for the current reviewer
	$reviews = $submission->getStudentsReviews($_SESSION['user_id']);
}

$annotations = array();
foreach ($reviews as $review) {
	/*
	 * push review into the array if the submission id of the 
	 * review matches the current submission. Also mark as a
	 * review that is already in the database.
	 */
	$row = $review->getRow();
	if ($row["SubmissionID"] == $subID) {
		$row["status"]='o';
		array_push($annotations, $row);
	}
}
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
	<link rel="stylesheet" href="css/monokai_sublime.css">
	<script src="js/highlight.pack.js"></script>
	
	<!-- JQuery text highlighter library -->
	<script type="text/javascript" src="js/jquery.textHighlighter.js"></script>
	
	<!-- Breadcrumbs -->
	<link rel="stylesheet" type="text/css" href="css/jquery.rcrumbs.css">
	<script src="js/jquery.rcrumbs.js"></script>
	

	<script>
		// GLOBALS
		var annotations = [];
		var annotations = <?php echo json_encode($annotations); ?>;
		var isOwner = <?php echo $isOwner; ?>;
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
			for (var i=0; i < annotations.length; i++) {
				if (annotations[i].Comments == comment) {
					// Check if the review hasn't been saved to the database
					if (annotations[i].status == 'n') {
						annotations.splice(i, 1);
						break;
					}
					// Check if the deleted comment has been edited
					if (annotations[i].status == 'e') {
						annotations[i].Comments = annotations[i].prevComment;
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
			// remove the highlight(s)
			$(".highlighted").each(function() {
				if ($(this).attr('id') == ('span' + id)) {
					$('#assignment_code').getHighlighter().removeHighlights($(this));
				}
			});
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
		function ownerSetup() {
			$('#student_heading').show();
			var counts = {};
			var first = 0;
			for (var i = 0; i < annotations.length; i++) {
				counts[annotations[i].ReviewerID] = 1 + (counts[annotations[i].ReviewerID] || 0);
			}
			for (var key in counts) {
				if (first == 0) {
					first = 1;
					$('#student_heading_span').html(key);
				}
				$('#studentReviews').append('<a href="#" class="reviewedBy" onclick="changeReviewer(' + key + ')">Student: ' + key + '</a>');
			}
		}
		
		/**
		 *
		 *
		 */
		function changeReviewer(id) {
			$('#student_heading_span').html(id);
			setupHighlighter();
			$('#assignment_code').getHighlighter().removeHighlights();
			$('#assignment_code').getHighlighter().destroy();
			count = 0;
			$('#reviews').html('');
			getComments();
		}

		/**
		 *
		 *
		 */
		function getComments() {
			var innerContents = $('#assignment_code').html();
			var wordArray = innerContents.split('\n');
			
			for (var i=0; i < annotations.length; i++) {
				// add status to mark as already in database
				//annotations[i].status = 'o';
				
				if (isOwner == 0) {
					if (annotations[i].fileName == $( "#file_heading" ).html() ) {
						wordArray = reviewPopulate(wordArray, i);
					}
				} else {
					if (annotations[i].fileName == $("#file_heading").html() && annotations[i].ReviewerID == $("#student_heading_span").html()) {
						wordArray = reviewPopulate(wordArray, i);
						$("#reviewControls" + (count-1)).hide();
					}
				}
			}
			$('#assignment_code').html(wordArray.join('\n'));
		}
		
		/**
		 *
		 *
		 */
		function reviewPopulate(wordArray, i) {
			var index = parseInt(annotations[i].startIndex);
			var line = parseInt(annotations[i].startLine);
			var text = annotations[i].text;
			var numLines = (text.match(/\n/g) || []).length;
			var endLine = line + numLines;
			var spanString = '<span style="background-color:#20afcd" class="highlighted" id="span' + count + '">';
			var endIndex = index + text.length + spanString.length;
			if (numLines > 0) {
				var textArr = text.split('\n');
				endIndex = textArr[textArr.length-1].length;
			}
			wordArray[line] = wordArray[line].slice(0,index) + spanString + wordArray[line].slice(index,wordArray[line].length);
			wordArray[endLine] = wordArray[endLine].slice(0,endIndex) + "</span>" + wordArray[endLine].slice(endIndex, wordArray[endLine].length); 
			$('#reviews').append('<div id="review' + count + '" class="reviewContainer"><div id="reviewControls' + count + '"><a class="delete_btn" href="#" onclick="clearReview(' + count + ')" role="button" id="delete' + count + '"></a><a class="edit_btn" href="#" onclick="editAnnotation(' + count + ')" role="button" id="edit' + count + '"></a></div><br><textarea class="reviewContent" rows="2" cols="32" id="textarea' + count + '" readonly="true">'+ annotations[i].Comments + '</textarea></br><a class="cancel_btn" href="#" onclick="cancelEdit(' + count + ')" role="button" id="cancel' + count + '" style="display:none;">Cancel</a><a class="save_btn" href="#" onclick="getContents(' + count + ')" role="button" id="save' + count + '" style="display:none">Save</a></div>');
			count = count + 1;
			return wordArray;
		}
		
		/**
		 *
		 *
		 */
		function toggleSyntaxHighlightingOff() {
			$('#assignment_code').removeClass();
			$('#assignment_code').toggleClass('nohighlight');
			$('#assignment_code').find("span").each( function() {
				if (! $(this).is('.highlighted')) {
					$(this).contents().unwrap();
				}
			});
			hljs.highlightBlock(document.getElementById("assignment_code"));
		}

		/**
		 *
		 *
		 */
		function toggleSyntaxHighlightingOn() {
			$('#assignment_code').removeClass('nohighlight');
			hljs.highlightBlock(document.getElementById("assignment_code"));
		}
		
		/**
		 * Gets the contents of the review when they click
		 * the save button in the review box and stores it in an array
		 * to push into the database later
		 */
		function getContents(id) {
			var comment = $('#textarea'+id).val();
			// Checking for an invalid comment
			if (comment.trim() == "") {
				alert("Please enter a valid comment");
				return;
			}
			// add code to make sure the comment is unique
			for (var i=0; i < annotations.length; i++) {
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
				// If the comment was already in the database, mark it as edited
				if (annotations[edit].status == 'o') {
					annotations[edit].status = 'e';
				}
				edit = -1;
				return;
			}
			toggleSyntaxHighlightingOff();
			var innerContents = $('#assignment_code').html();
			var wordArray = innerContents.split('\n');
			var startIndex;
			var startLine;
			for (var i = 0; i < wordArray.length; i++) {
				// Find the line the comment starts on and allow for the 6 characters ('<span  ')
				startIndex = wordArray[i].indexOf('id="span' + id + '"') - 6;
				if (startIndex >= 0) {
					startLine = i;
					break;
				}
			}
			// Push the new comment into the array
			annotations.push({"Comments":comment, "text":selected, "status":'n', "startLine":startLine, "startIndex":startIndex, "fileName":$( "#file_heading" ).html(), "SubmissionID":<?php echo $subID;?>});
			count = count + 1;
			toggleSyntaxHighlightingOn();
			setupHighlighter();
			setupHover();
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
			//AJAX call to store the review in the database
			var request = {f: 'saveReviews', params: [JSON.stringify(annotations)]};
			$.post("api.php", JSON.stringify(request), function(retval) {
				alert("Your comments have been saved!");
				for (var i=0; i < annotations.length; i++) {
					if (annotations[i].status == 'd') {
						annotations.splice(i, 1);
					} else {
						annotations[i].status = 'o';
						annotations[i].prevComment = undefined;
					}
				}
			});
		}
		
		/**
		 * Saves and submits reviews, by setting each row's "Submitted" to 1.
		 * Though only a single row needs it to be set to 1, I'm doing them all.
		 */
		function submitReviews() {
			var request = {f: 'submitReviews', params: [JSON.stringify(annotations)]};
			$.post("api.php", JSON.stringify(request), function(retval) {
				alert("Your comments have been submitted!");
				for (var i=0; i < annotations.length; i++) {
					if (annotations[i].status == 'd') {
						annotations.splice(i, 1);
					} else {
						annotations[i].status = 'o';
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
			for (var i=0; i < annotations.length; i++) {
				if (annotations[i].Comments == comment) {
					edit = i;
					break;
				}
			}
		}
		
		/**
		 * Handles when someone clicks on the file tree
		 */
		function handleSwap(id) {
			$('a.active').removeClass('active');
			var fileName = '';
			if (id.indexOf("/") != -1) {
				fileName = id.substring(id.lastIndexOf("/") + 1);
			}
			$('#' + fileName.split('.')[0]).addClass('active');
			//Loads the selected file into the main content area using AJAX
			var request = {f: 'loadFile', params:  ['' + id]};
			$.post("api.php", JSON.stringify(request), function( filecode ) {
				var contentObj = $.parseJSON(filecode);
				// reset count
				// want to destroy and recreate to update the id (count)
				count = 0;
				$( "#assignment_code" ).html(contentObj.r.trim());
				$( "#file_heading" ).html( fileName );
				// remove previous annotations and add the new ones
				$('#reviews').html('');
				getComments();
				if ($('#assignment_code').getHighlighter() !== undefined) {
					$('#assignment_code').getHighlighter().destroy();
				}
				if (!isOwner) {
					setupHighlighter();
				}
				hljs.highlightBlock(document.getElementById("assignment_code"));
			});	  
			
		}
	</script>
</head>

<body>
	<?php include 'header.php'; ?>
	
	<div class="container">
		<div class="rcrumbs" id="breadcrumbs">
			<ul>
				<li><a href="http://deco3801-14.uqcloud.net">Home</a><span class="divider">></span></li>
				<li><a href="http://deco3801-14.uqcloud.net/reviewhub.php">Review Hub</a><span class="divider">></span></li>
				<li><a href="#">Assignment Reviews</a><span class="divider"></span></li>
			</ul>
		</div>
		<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
		
			<div class="file-tree-container">
			<?php
				function setupFileTree ($dir) {
					// Open a directory, and read its contents
					if ($dh = opendir($dir)) {
						echo "<ul class='filetree'>";
						$filesArray = array();
						while (($file = readdir($dh)) !== false) {
							// Check if it's a directory (but not '.' or '..')
							if (is_dir($dir.$file) && $file != "." && $file != "..") {
								// Use recursion to go into subdirectories
								handleSubDir($file, $dir, $filesArray);
							} else if ($file != "." && $file != "..") {
								// add the file to the array
								array_push($filesArray, $file);
							}
						}
						// print the array as a list
						printPath ($filesArray, $dir);
						closedir($dh);
						echo "</ul>";
					}
				}
				
				function handleSubDir($subdir, $dir) {
					// print the name of the directory and then traverse into it
					echo "<li class='dir'>" . $subdir;
					setupFileTree($dir . $subdir . "/");
					echo "</li>";
				}
				
				function printPath ($filesArray, $dir) {
					// Loop through the directory contents to make the file tree
					foreach ($filesArray as $name) {
						global $initialFile;
						$includesDir = substr_replace($dir . $name, '/includes/..', strlen(''. __DIR__), 0);
						if ($name === $filesArray[0]) {
							$initialFile = $dir . $name;
						}
						echo "<li>";
						echo "<a href='#' id='" . explode('.', $name)[0] . "' class='file-link' onclick='handleSwap(\"" . $includesDir . "\");'>" . $name . "</a>";
						echo "</li>";
					}
				}
				
				setupFileTree(__DIR__ . "/storage/course_$courseid/assign_$assignid/submissions/$subID/");
			?>
			</div>
		</div>
		<div class="col-md-9">
			<h1><?php echo $crs->getAssignment($assignid)->getAssignmentName(); ?></h1>
			<div class="col-md-12">
				<h2 id="file_heading"><?php if ($initialFile !== '') echo substr($initialFile, strrpos($initialFile, "/") + 1); ?></h2>
				<h3 id="student_heading" style="display:none">Student <span id="student_heading_span"></span></h3>
				<div id="studentReviews" class="list-group" style="float:right"></div>
				<div id="innercontainer">
					<pre id='assignment_code' style="float: left; min-width: 450px; max-width: 550px"><?php
					//Loads the first file in the file tree if its not empty
					if ($initialFile !== '') {
						echo trim($crs->loadFile($initialFile));
					}?></pre>
				<div id="reviews" style="clear:right; float:right;"></div>
				</div>	
				<p style="float:left;clear:left;">
					<a class="btn btn-info" href="#" onclick="saveReviews()" role="button">Save</a>
					<a class="btn btn-primary" href="reviewhub.php" onclick="submitReviews()" role="button">Submit</a>
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
		
		function setupHover() {
			// change highlighting colour for text related to hovered comment
			$(".reviewContainer").hover(
				function() {
					// Set colour when mouseover
					var id = $(this).attr("id");
					$("#" + id.replace("review", "span")).css("background-color", "#ec971f");
				}, function() {
					// Reset colour when mouseout
					var id = $(this).attr("id");
					$("#" + id.replace("review", "span")).css("background-color", "#20afcd");
				}
			);
		}
		
		$(document).ready(function() {
			$("#breadcrumbs").rcrumbs();
			// Set the current file to be 'active'
			var fileName;
			var id = '<?php echo $initialFile; ?>';
			if (id.indexOf("/") != -1) {
				fileName = id.substring(id.lastIndexOf("/") + 1);
			}
			$('#' + fileName.split('.')[0]).addClass('active');
			// Turn the file list into a collapsible tree
			$('li.dir').each(function(i) {
				// temporarily disconnect the sub directory
				var subDir = $(this).children().remove();
				// show/hide the sub directory on click
				$(this).wrapInner('<a/>').find('a').click(function() {
					subDir.toggle();
				});
				// reconnect the sub directory
				$(this).append(subDir);
			});
			// Hide all sub directories
			$('ul ul').hide();
			
			if (isOwner == 1) {
				ownerSetup();
			}
			getComments();
			// don't setup the highlighter for the owner
			if (isOwner == 0) {
				setupHighlighter();
			}
			setupHover();
			hljs.highlightBlock(document.getElementById("assignment_code"));
		});
	</script>
	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>