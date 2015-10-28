<?php

header("Access-Control-Allow-Origin: *");

include_once 'config.inc.php';

error_reporting(E_ALL ^ E_NOTICE);

$summary = [];

$module = $_GET["module"];
if (!$module) {
	exit(0);
}

query();
//outputCSV($summary);

function query() {
   global $connection_url, $db_name, $collection, $summary;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();

	foreach ($cursor as $doc) {
		$summary[] = processRecord($doc);
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
   }
}

function processRecord($doc) {
	global $module;
	$search = "ODI_" + $module;
	foreach ($doc as $key => $value) {
		if (substr($key,0,strlen($search) == $search)) {
			$output[$key] = $doc[$key];
		}
	}
	if ($output) {
		$output["lang"] = $doc["lang"];
		$output["theme"] = $doc["theme"];
		if ($doc["email"]) {
			$output["email"] = "yes";
		} else {
			$output["email"] = "no";
		}
	} else {
		return;
	}
	processOutput($output);
}

function processOutput($output) {
	print_r($output);
}


function outputCSV($summary) {
	
	$handle = fopen("php://output","w");
	$first = $summary[0];

	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="data.csv"');

	foreach ($first as $key => $value) {
		$keys[] = $key;
		$values[] = $value;
	}
	fputcsv($handle,$keys);
	fputcsv($handle,$values);
	for($i=1;$i<count($summary);$i++) {
		$values = "";
		$line = $summary[$i];
		foreach ($line as $key => $value) {
			$values[] = $value;
		}
		fputcsv($handle,$values);
	}

	fclose($handle);
	
}

?>
