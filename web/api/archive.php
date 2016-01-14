<?php
$location = "api/archive.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include('_includes/api-header.php');
echo "DONE";
exit();
include('_includes/functions.php');

$count = archive_empty_profiles();

if ($count === false) {
	echo "FAILED";
} else {
	echo "Complete<br/>" . $count . " archived.";
}

?>
