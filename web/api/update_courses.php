<?php

$location = "/api/update_courses.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include('_includes/api-header.php');

getInstances();
getCourses();

function getInstances() {
	global $instances_collection;
	$instances_url = "http://contentapi.theodi.org/with_tag.json?type=course_instance";
	$data = file_get_contents($instances_url);
	$data = str_replace("+00:00","Z",$data);
	$data = str_replace("+01:00","Z",$data);
	$data = str_replace('"date"','"$date"',$data);
	$json = json_decode($data,true);
	$results = $json["results"];
	for ($i=0;$i<count($results);$i++) {
		store($results[$i],$instances_collection);
	}
}

function getCourses() {
	global $courses_collection; 
	$courses_url = "http://contentapi.theodi.org/with_tag.json?type=course";
	$data = file_get_contents($courses_url);
	$json = json_decode($data,true);
	$results = $json["results"];
	for ($i=0;$i<count($results);$i++) {
                store($results[$i],$courses_collection);
        }
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
	} else {
		$col->save($data);
	}

	$m->close();
	echo "Updated " . $data["title"] . "<br/>";
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
