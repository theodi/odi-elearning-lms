<?php
header("Access-Control-Allow-Origin: *");

include_once 'config.inc.php';

function query() {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();

	$col2 = $m->selectDB($db_name)->selectCollection("elearning-deleted");
	foreach ($cursor as $doc) {
		$id = $doc["_id"];
		$query = array('_id' => $id);
		if (!$doc["ODI_lastSave"] || !$doc["theme"]) {
	        	$count = $col2->count($query);
	        	if ($count > 0) {
				$newdata = array('$set' => $doc);
				$col2->update($query,$newdata);
			} else {
				$col2->save($doc);
			}
			$col->remove($doc);
		}
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

query();

?>
