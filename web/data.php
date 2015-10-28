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
outputCSV($summary);

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
	$output = false;
	$search = "ODI_" . $module . "_";
	foreach ($doc as $key => $value) {
		if (substr($key,0,strlen($search)) == $search) {
			$outkey = str_replace($search,"",$key);
			$outkey = str_replace("．",".",$outkey);
			$output[$outkey] = $doc[$key];
		}
	}
	if ($output) {
		$output["lang"] = $doc["lang"];
		$output["theme"] = $doc["theme"];
		if ($doc["email"]) {
			$output["email"] = "true";
		} else {
			$output["email"] = "false";
		}
	} else {
		return;
	}
	return processOutput($output);
}

function processOutput($output) {
	print_r($output);
	$line = [];
	$line["email"] = $output["email"];
	$line["theme"] = $output["theme"];
	$line["lang"] = $output["lang"];

	$progress = $output["cmi.suspend_data"];
	$data = json_decode($progress,"true");
	$completion = $data["spoor"]["completion"];
	$total = strlen($completion);
	$done = substr_count($completion,"1");
	$line["completion"] = $done / $total;
	$line["complete"] = "true";
	$line["passed"] = "false";
	if ($data["spoor"]["_isCourseComplete"] == 1) {
		$line["complete"] = "true";
	}
	if $data["spoor"]["_isAssessmentPassed"] == 1) {
		$line["passed"] = "true";
	}
	return $line;
}


function outputCSV($summary) {
	
	$handle = fopen("php://output","w");
	$first = $summary[0];

//	header('Content-Type: text/csv');
//	header('Content-Disposition: attachment; filename="data.csv"');

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
