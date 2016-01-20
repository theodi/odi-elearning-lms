<?php
	$location = "/courses/index.php";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	include('_includes/functions.php');
	echo '<table style="width: 100%;">';
	echo '<tr><th>Course name</th><th style="width:150px;">Credits</th><th>Type</th><th>Dashboard</th></tr>';
	echo coursesTable();
	echo '</table>';
	include('_includes/footer.html');

function coursesTable() {
   // global $courses_collection;

   $courses = getCoursesData();
   
   // Put in google spreadsheet
   //$tracking["open-data-day"] = "InADay";
   //$tracking["open-data-science"] = "ODS";

   foreach ($courses as $doc) {
   		$courseId = $doc["id"];
   		/*
	   	if ($doc["slug"]) {
   			$courseId = $doc["slug"];
   		}
   		if ($tracking[$courseId]) {
   			$courseId = $tracking[$courseId];
   		}
   		*/
   		if ($doc["web_url"]) {
			$output .= '<tr><td><a target="_blank" href="'.$doc["web_url"].'">' . $doc["title"] . '</a></td>';
		} else {
			$output .= '<tr><td>' . $doc["title"] . '</td>';
		}
     	$output .= '<td style="text-align: center;">';
		$output .= outputCredits($courseId);
		$output .= '</td>';
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
		$output .= '</tr>';
   }
   return $output;
}

function outputCredits($courseId) {
	$data = get_course_credits_by_badge($courseId);
	$rows = "";
	foreach ($data as $key => $value) {
		$total += $value;
		$rows .= "<tr><td>" . $key . "</td><td>" . $value . '</td></tr>';
	}
	$box = '<div id="course_credits_box"><score>' . $total .' </score><table id="course_credits_table">';
	$box .= $rows;
	$box .= '</table></div>';
	return $box;
}

?>
