<?php

$location = "/api/update_courses.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include('_includes/api-header.php');

$data = getCourseIdentifierData();

$all = prepareData($data);
$all["id"] = "CourseIdentifiers";
$all["title"] = "Course Identifiers";
//print_r($all);
store($all,"courseIdentifiers");

function prepareData($data) {
  $master = "";
  for($i=1;$i<count($data);$i++) {
    $bits = str_getcsv($data[$i]);
    $master[$bits[0]][] = $bits[1];  
  }
  return $master;
}

function getCourseIdentifierData() {
  $content = exec('php ~/getFile.php 17gtugoN05aYnWN07Exf6_RpdknlpCfi6a1WSTyJ_z7c',$output);
  return $output;
}

function store($data,$collection) {
   global $connection_url, $db_name;
   try {
   // create the mongo connection object
  $m = new MongoClient($connection_url);

  // use the database we connected to
  $col = $m->selectDB($db_name)->selectCollection($collection);
  
  $id = $data["id"];
  $query = array('id' => $id);
        $count = $col->count($query);
        if ($count > 0) {
    $newdata = array('$set' => $data);
    $col->update($query,$newdata);
  } else {
    $col->save($data);
  }

  $m->close();
  echo "Updated " . $data["title"] . "<br/>";
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
