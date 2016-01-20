<?php
	$location = "/courses/index.php";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	include('_includes/functions.php');
	echo '<table style="width: 100%;">';
	echo '<tr><th>Course name</th><th>Type</th><th>Dashboard</th></tr>';
	echo coursesTable();
	echo '</table>';
	include('_includes/footer.html');

function coursesTable() {
   global $courses_collection;

   $courses = get_data_from_collection($courses_collection);
   $courseIdentifiers = get_data_from_collection("courseIdentifiers");
   foreach ($courseIdentifiers as $doc) {
   	$doc = $doc["identifiers"];
   	foreach ($doc as $key => $value) {
   		for($i=0;$i<count($value);$i++) {
	   		$tracking[$value[$i]] = $key;
   		}
   	}
   }

   // Put in google spreadsheet
   //$tracking["open-data-day"] = "InADay";
   //$tracking["open-data-science"] = "ODS";

   foreach ($courses as $doc) {
   		$courseId = $doc["id"];
   		if ($doc["slug"]) {
   			$courseId = $doc["slug"]
   		}
   		if ($tracking[$courseId]) {
   			$courseId = $tracking[$courseId];
   		}
   		if ($doc["web_url"]) {
			$output .= '<tr><td><a target="_blank" href="'.$doc["web_url"].'">' . $doc["title"] . '</a></td>';
		} else {
			$output .= '<tr><td>' . $doc["title"] . '</td>';
		}
		$output .= '<td style="text-align: center;"><img style="max-height: 40px;" src="/images/';
		$output .= $doc["format"]; 
		$output .= '.png"></img></td>';
		$output .= '<td style="text-align: center;">';
		if (substr($doc["id"],0,4) == "ODI_") {
			$dashId = str_replace("ODI_","",$doc["id"]);
			$output .= '<a href="/dashboard/index.php?module=' . $dashId . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} elseif ($tracking[$doc["slug"]]) {
			$output .= '<a href="/dashboard/index.php?module=' . $tracking[$doc["slug"]] . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} 
		$output .= '</td>';
		outputCredits($courseId)
		$output .= '</tr>';
   }
   return $output;
}

function outputCredits($courseId) {
	$data = get_course_credits_by_badge($courseId);
	print_r($data);
}

?>
