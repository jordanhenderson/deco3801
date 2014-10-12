<?php
	$subs = $asg_obj->getSubmissions();
	
	$reviewnum = count($subs) - 1;
	
	if ($reviewnum > $asg['ReviewsNeeded']) {
		$reviewnum = $asg['ReviewsNeeded'];
	}
	
	// for each submission
	for ($i = 0; $i < count($subs); ++$i) {
		// the student who made the submission must mark 'reviewnum' submissions.
		for ($j = 0; $j < $reviewnum; ++$j) {
			// Make review row for student/submission
		}
	}
?>