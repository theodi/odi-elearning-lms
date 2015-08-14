<?php
header("Access-Control-Allow-Origin: *");

include_once 'config.inc.php';

require_once('sendMail.php');

function store($data) {
   global $connection_url, $db_name, $collection, $mail_lock;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$id = $data["_id"];
	$query = array('_id' => $id);
        $count = $col->count($query);
        if ($count > 0) {
		$newdata = array('$set' => $data);
		$col->update($query,$newdata);
	} else {
		$col->save($data);
	}
	if ($mail_lock == "false") {
		findEmails();
	}

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
