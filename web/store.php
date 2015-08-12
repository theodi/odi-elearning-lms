<?php
header("Access-Control-Allow-Origin: *");

include 'config.inc.php';

function store($data) {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);

	$col->save($data);

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

$data = $_POST["data"]; //Fetching all posts
$data = str_replace(".","\uff0e",$data);
$json = json_decode($data,true);

store($json);

?>
