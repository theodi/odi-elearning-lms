<?php

$location = "/api/update_courses.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include('_includes/api-header.php');

$url = $_GET["url"];

$data = getCourseData($url);
$data["id"] = getModuleId($url);
store($data,$courses_collection);

function getCourseData($url) {
	$dataUrl = $url . "/course/en/course.json";
	$data = file_get_contents($dataUrl);
	$data = json_decode($data,true);
	unset($data["_resources"]);
	unset($data["_buttons"]);
	return $data;
}

function getModuleId($url) {
	$dataUrl = $url . "/course/en/config.json";
	$data = file_get_contents($dataUrl);
	$data = json_decode($data,true);
	return $data["_moduleId"];
}

function store($data,$collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$id = $data["id"];
	$query = array('id' => $id);
        $count = $col->count($query);
        if ($count > 0) {
		$newdata = array('$set' => $data);
		$col->update($query,$newdata);
		echo "Updated " . $data["title"] . "<br/>";
	} else {
		$col->save($data);
		echo "Imported " . $data["title"] . "<br/>";
	}

	$m->close();
	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	print_r($data);
	echo "<br/><br/>\n\n";
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
	echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	print_r($data);
	echo "<br/><br/>\n\n";
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

?>
