<?php
header("Access-Control-Allow-Origin: *");
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include_once '_includes/functions.php';

$cursor = get_data_from_collection($courses_collection);
$output = '{ "results": [';
foreach ($cursor as $doc) {
	$output .= json_encode($doc);
	$output .= ",";
}
$output = substr($output,0,-1) . "]}";
echo $output;

?>
