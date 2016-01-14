<?php
function load($email) {
   $email = str_replace('.','．',$email);
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);
	
	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('email' => $email);

	$res = $col->find($query);	
	
	$m->close();
	
	foreach ($res as $doc) {
 	   return json_encode($doc);
	}
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

?>
