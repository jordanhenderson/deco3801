<?php
	$submissions = $asg_obj->getSubmissions();
	
	$reviewnum = count($submissions) - 1;
	
	if ($reviewnum > $asg['ReviewsNeeded']) {
		$reviewnum = $asg['ReviewsNeeded'];
	}
	
	// for each submission
	for ($i = 0; $i < count($submissions); ++$i) {
		// the student who made the submission must mark 'reviewnum' submissions.
		for ($j = 0; $j < $reviewnum; ++$j) {
			// Make review row for student/submission
			$index = ($i + $j) % count($submissions);
			$reviewerID = $submissions[$index]->getOwner();
			$submissions[$i]->addReview("i=$i, j=$j", $reviewerID, 0, 0, "", "");
		}
	}
?>