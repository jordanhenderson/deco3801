<?php
//Include the main handler backend
require_once 'includes/handlers.php';
//Initialise the PCRHandler
$crs = new PCRHandler();
$initialFile = '';
// Get the submissionID from the url
$subID = $_GET['subid'];
$courseid = $_SESSION['course_id'];

$submission = $crs->getSubmissionForReviewing($subID);
if(!$submission->isValid()) {
	header("Location: reviewhub.php");
	die();
}
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
} elseif(isset($_SESSION['admin'])) {
	$reviews = $submission->getReviews();
} else {
	//Redirect the user to overview.php if access is denied.
	$review = new Review(array("SubmissionID"=>$submission->getID(), "ReviewerID"=>$_SESSION['user_id']), false);
	if(!$review->isValid()) {
		header("Location: reviewhub.php");
		die();
	}
	// Check to see if the user has already submitted their reviews
	if (!$submission->checkAccess()) {
		header("Location: reviewhub.php");
		die();
	}
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
	if ($row["SubmissionID"] == $subID && $row["FileID"] !== null) {
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
			$('#saveButton').hide();
			$('#submitButton').hide();
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
				$('#studentReviews').append('<button class="reviewedBy" onclick="changeReviewer(' + key + ')">Student: ' + key + '</button>');
			}
		}
		
		/**
		 *
		 *
		 */
		function changeReviewer(id) {
			$('#student_heading_span').html(id);
			/*setupHighlighter();
			$('#assignment_code').getHighlighter().removeHighlights();
			$('#assignment_code').getHighlighter().destroy();*/
			$(".highlighted").each(function() {
				$(this).contents().unwrap();
			});
			count = 0;
			$('#reviews').html('');
			// Load the comments for the next review in
			toggleSyntaxHighlightingOff();
			getComments();
			toggleSyntaxHighlightingOn();
			setupHover();
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
					if (annotations[i].FileID == $( "#file_heading" ).data("fid") ) {
						wordArray = reviewPopulate(wordArray, i);
					}
				} else {
					if (annotations[i].FileID == $("#file_heading").data("fid") && annotations[i].ReviewerID == $("#student_heading_span").html()) {
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
			var spanString = '<span id="span' + count + '" style="background-color:#20afcd" class="highlighted">';
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
			// Push the new comment into the array
			var fileHeading = $("#file_heading");
			annotations.push({"Comments":comment, "text":selected, "status":'n', "FileID":fileHeading.data("fid"), "SubmissionID":"<?php echo $subID;?>"});
			count = count + 1;
			setupHighlighter();
			setupHover();
		}
		
		/**
		 *
		 *
		 */
		function updatePositions() {
			toggleSyntaxHighlightingOff();
			var innerContents = $('#assignment_code').html();
			var wordArray = innerContents.split('\n');
			var numInOtherFile = 0;
			for (var j = 0; j < annotations.length; j++) {
				if (annotations[j].status == 'd' || annotations[j].FileID != $( "#file_heading" ).data("fid")) {
					numInOtherFile++;
					continue;
				}
				var startIndex;
				var startLine;
				for (var i = 0; i < wordArray.length; i++) {
					// Find the line the comment starts on and allow for the 6 characters ('<span  ')
					var filtered = wordArray[i].replace(/ rgb\(32, 175, 205\);/g, "#20afcd");
					startIndex = filtered.indexOf('id="span' + (j-numInOtherFile) + '"') - 6;
					if (startIndex >= 0) {
						startLine = i;
						break;
					}
				}
				annotations[j].startLine = startLine;
				annotations[j].startIndex = startIndex;
			}
			toggleSyntaxHighlightingOn();
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
			// Update the startIndex and startLine of the comments (Might be effected by the deleted value)
			updatePositions();
			//AJAX call to store the review in the database
			var request = {f: 'saveReviews', params: [annotations]};
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
				count = annotations.length;
			});
		}
		
		/**
		 * Saves and submits reviews, by setting each row's "Submitted" to 1.
		 * Though only a single row needs it to be set to 1, I'm doing them all.
		 */
		function submitReviews() {
			if (!confirm("Are you sure you want to submit?\nYou will no longer have access to this page if you submit.")) {
				return;
			}
			// Update the startIndex and startLine of the comments (Might be effected by the deleted value)
			updatePositions();
			var request = {f: 'submitReviews', params: [annotations]};
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
				count = annotations.length;
				window.location="reviewhub.php";
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
		function handleSwap() {
			var file_link = $(this);
			// Don't update for the owner, since they can't change them anyway
			if (isOwner == 0) {
				updatePositions();
			}
			$('a.active').removeClass('active');
			file_link.addClass('active');
			//Loads the selected file into the main content area using AJAX
			var request = {f: 'loadFile', params: [file_link.data("fid")]};
			$.post("api.php", JSON.stringify(request), function( filecode ) {
				var contentObj = $.parseJSON(filecode);
				// reset count
				// want to destroy and recreate to update the id (count)
				count = 0;
				$( "#assignment_code" ).html(contentObj.r.trim());
				$( "#file_heading" ).html( file_link.data("fname") );
				$( "#file_heading" ).data("fid", file_link.data("fid"));
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
				setupHover();
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
				<ul class="filetree">
			<?php
				$files = $crs->getFiles($subID);
				$initialFile = $files[0];
				$lastdir = "";
				$close = "";
				foreach($files as $file) {
					$id = $file->getID();
					$name = $file->getFileName();
					$path = $file->getPath();

					if($path === $lastdir) {
						//Same subdirectory.
					} elseif($lastdir === "" || strpos($lastdir, $path) !== FALSE) {
						//Deeper directory.
						$close .= "</ul></li>";
						echo "<li class='dir'>" . $file->getLastPath() . "<ul class='filetree'>";
					} else {
						//pull back.
						echo $close;
						$close = "";
					}
					$lastdir = $path;
				      
					$active = $file == $files[0] ? "active" : "";
					echo "<li><a href='#' class='file-link $active' data-fid='$id' data-fname='$name'>$name</a></li>";
				}
			?>
			 </ul>
			</div>
		</div>
		<div class="col-md-9">
			<h1>Reviewing: <?php echo $crs->getAssignment($assignid)->getAssignmentName(); ?></h1>
			<div class="col-md-12">
				<h2 id="file_heading">Reviews</h2>
				<h3 id="student_heading" style="display:none">Student <span id="student_heading_span"></span></h3>
				<div id="studentReviews" class="list-group" style="float:right"></div>
				<div id="innercontainer">
					<pre id='assignment_code' style="float: left; min-width: 450px; max-width: 550px"><?php
					//Loads the first file in the file tree if its not empty
					if ($initialFile !== '') {
						echo trim($crs->loadFile($initialFile->getID()));
					}?></pre>
				<div id="reviews" style="clear:right; float:right;"></div>
				</div>	
				<p style="float:left;clear:left;">
					<a class="btn btn-info" href="#" onclick="saveReviews()" id = "saveButton" role="button">Save</a>
					<a class="btn btn-primary" href="reviewhub.php" onclick="submitReviews()" id="submitButton" role="button">Submit</a>
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
					$('.highlighted').each( function() {
						if ($(this).attr('id') == id.replace("review", "span")) {
							$(this).css("background-color", "#ec971f");
						}
					});
				}, function() {
					// Reset colour when mouseout
					var id = $(this).attr("id");
					$('.highlighted').each( function() {
						if ($(this).attr('id') == id.replace("review", "span")) {
							$(this).css("background-color", "#20afcd");
						}
					});
				}
			);
		}
		
		$(document).ready(function() {
			$("#breadcrumbs").rcrumbs();
			$(".file-tree-container").on("click", ".file-link", handleSwap);
			$(".file-link").first().trigger("click");
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
