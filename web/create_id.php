<?php
header("Access-Control-Allow-Origin: *");

include 'config.inc.php';

function existsID($id) {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);

	$query = array('_id' => $id);
	$count = $col->count($query);
	if ($count > 0) {
		$m->close();
		return true;
	}
	$m->close();
	return false;
   } catch ( MongoConnectionException $e ) {
//	return false;
	die('Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	die('Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
	die('Error: ' . $e->getMessage());
   }
}

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

$guid = GUID();
while (existsID($guid)) {
	$guid = GUID();
}

echo $guid;
?>
