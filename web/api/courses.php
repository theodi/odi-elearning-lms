<?php
header("Access-Control-Allow-Origin: *");
$location = "/api/all_access.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include_once('_includes/api-header.php');
include_once '_includes/functions.php';

$courses = getCoursesData();

if ($theme && $theme != "default") {
	$filter = get_client_mapping($theme);
	$courses = filterCourses($courses,$filter);
}

$output = array();
foreach ($courses as $id => $course) {
  $course["ID"] = $id;
  $output[] = $course;
}

$out["data"] = $output;

echo json_encode($out);

?>
