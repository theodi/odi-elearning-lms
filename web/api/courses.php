<?php
header("Access-Control-Allow-Origin: *");
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';

function query() {
   global $connection_url, $db_name, $courses_collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($courses_collection);
	
	$query = array('format' => 'course');
	$cursor = $col->find($query);
	$output = '{ "results": [';
	foreach ($cursor as $doc) {
		$output .= json_encode($doc);
		$output .= ",";
	}
	$output = substr($output,0,-1) . "]}";
	echo $output;
	$m->close();

	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

query();
?>
