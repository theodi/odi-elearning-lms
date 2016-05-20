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

$data = getRawData($id);

$outer = [];
$profile = str_getcsv($data[0])[1];
$theme = str_getcsv($data[1])[1];
$source = $id;
$emails = getColumn($data,0);
$courses = getCoursesAccessData($data);

function getCoursesAccessData($lines) {
  $ret = "";
  $headers = str_getcsv($lines[3]);
  for($i=4;$i<count($lines);$i++) {
    $line = str_getcsv($lines[$i]);
    for($j=1;$j<count($headers);$j++) {
      if ($line[$j] != "") {
        $ret[$headers[$j]][] = $line[$j];
      }
    }
  }
  return $ret;
}

$all = "";
for($i=0;$i<count($emails);$i++) {
  $record = "";
  $record["source"] = $source;
  $record["profile"] = $profile;
  $record["theme"] = $theme;
  $record["email"] = $emails[$i];
  $record["courses"] = $courses;
  $all[] = $record;
}

remove($id,"externalAccess");
store($all,"externalAccess");

function getColumn($lines,$index) {
  $array = "";
  for($i=4;$i<count($lines);$i++) {
      $line = str_getcsv($lines[$i]);
      $value = $line[$index];
      if ($value) {
          $array[] = $value;
      }
  }
  return $array;
}

function getRawData($id) {
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
      echo "Imported record for " . $data[$i]["email"] . " (" . $data[$i]["theme"] . ")<br/>";
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
