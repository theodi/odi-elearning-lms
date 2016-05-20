<?php

$location = "/api/update_courses.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include('_includes/api-header.php');

$id = $_GET['id'];
if (!$id || $id == "") {
  return "GO AWAY";
}

$data = getCourseAttendanceData($id);

$all = prepareData($data,$id);
remove($id,"courseAttendance");
store($all,"courseAttendance");

function prepareData($lines,$id) {
  $headers = str_getcsv($lines[0]);
  for($i=1;$i<count($lines);$i++) {
    $line = str_getcsv($lines[$i]);
	  $record = "";
    $record["source"] = $id;
	  for($j=0;$j<count($headers);$j++) {
		  $record[$headers[$j]] = $line[$j];
	  }
	  if ($record["Email"] == "" && $record["First Name"] == "") {
    } else {
      if ($record["Email"]) {
          $record["Email"] = strtolower($record["Email"]);
      }
		  $out[] = $record;
    }
  }
  return $out;
}

function getCourseAttendanceData($id) {
  $content = exec('php ~/getFile.php ' . $id,$output);
  return $output;
}

function remove($id,$collection) {
  global $connection_url, $db_name;
  try {
  // create the mongo connection object
    $m = new MongoClient($connection_url);
    // use the database we connected to
    $col = $m->selectDB($db_name)->selectCollection($collection);
    $query = array('source' => $id);
    $cursor = $col->find($query);
    foreach ($cursor as $doc) {
      $col->remove($doc);
    }
  } catch ( MongoConnectionException $e ) {
    //  return false;
   echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
   syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
  } catch ( MongoException $e ) {
    //  return false;
    echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
    print_r($data);
    echo "<br/><br/>\n\n";
    syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
  } catch ( Exception $e ) {
    echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
    print_r($data);
    echo "<br/><br/>\n\n";
    //  return false;
    syslog(LOG_ERR,'Error: ' . $e->getMessage());
  }
}

function store($data,$collection) {
  global $connection_url, $db_name;
  try {
  // create the mongo connection object
    $m = new MongoClient($connection_url);
    // use the database we connected to
    $col = $m->selectDB($db_name)->selectCollection($collection);
    for($i=0;$i<count($data);$i++) {
      $record = $data[$i];
      $col->save($data[$i]);
      echo "Imported record for " . $data[$i]["First Name"] . " " . $data[$i]["Surname"] . " (" . $data[$i]["Course"] . ")<br/>";
    }
    $m->close();
    return true;
  } catch ( MongoConnectionException $e ) {
    //  return false;
   echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
   syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
  } catch ( MongoException $e ) {
    //  return false;
    echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
    print_r($data);
    echo "<br/><br/>\n\n";
    syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
  } catch ( Exception $e ) {
    echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
    print_r($data);
    echo "<br/><br/>\n\n";
    //  return false;
    syslog(LOG_ERR,'Error: ' . $e->getMessage());
  }
}

?>
