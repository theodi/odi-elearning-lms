<?php
	$location = "/dashboard/index.php?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	include('_includes/functions.php');
	$data = get_data_from_collection($collection);
	$courses = getCoursesData();
	foreach ($data as $user) {
		print_r($user);
		exit(1);
	}
	
	include('_includes/footer.html');
?>
