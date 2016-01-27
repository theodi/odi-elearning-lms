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
   
   $courses = getCoursesData();
   
   $output = "";
	
   foreach ($courses as $doc) {
	$output .= outputCourse($doc,"");
   }
   return $output;
}

?>
