<?php

error_reporting(E_ALL ^ E_NOTICE);
$location = "/api/view_data.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once 'config.inc.php';
include_once('_includes/api-header.php');
include_once('_includes/functions.php');

$courses = getCoursesData();
if ($theme && $theme != "default") {
  $filter = get_client_mapping($theme);
  $courses = filterCourses($courses,$filter);
}

if (!$userData["isAdmin"]) {
  if (!$userData["externalAccess"]["courses"]) {
    header('Location: /401.php');
    exit();
  }
} 

$tracking = get_course_identifiers();

$users = "";
$users = getUsers("elearning",$users);
$users = getUsers("externalBadges",$users);
$users = getUsers("courseAttendance",$users);
$users = getUserBadgeTotals($users);

if (!$userData["isAdmin"]) {
    $users = removeNullProfiles($users);
    $users = filterUsers($users,$userData["externalAccess"]["courses"]);
}

foreach ($users as $email => $data) {
  $data["Email"] = $email;
  $output[] = $data;
}
$out["data"] = $output;

echo json_encode($out);

function removeNullProfiles($users) {
  $ret = "";
  foreach ($users as $email => $data) {
    if ($data["courses"]["complete"] != null || $data["eLearning"]["complete"] !=null || $data["eLearning"]["in_progress"] !=null) {
      $ret[$email] = $data;
    }
  }
  return $ret;
}
function filterUsers($users,$filter) {
  foreach($users as $email => $data) {
    $data["courses"]["complete"] = filterCourseUser($data["courses"]["complete"],$filter,$email);
    $data["eLearning"]["complete"] = filterCourseUser($data["eLearning"]["complete"],$filter,$email);
    $data["eLearning"]["in_progress"] = filterCourseUser($data["eLearning"]["in_progress"],$filter,$email);
    $users[$email] = $data;
  }
  return removeNullProfiles($users);
}
function filterCourseUser($courses,$filter,$email) {
  $ret = "";
  for($i=0;$i<count($courses);$i++) {
    $id = $courses[$i];
    if ($filter[$id][0] == "ALL" || in_array($email, $filter[$id])) {
      $ret[] = $id;  
    }
  }
  return $ret;
}
/*
function filterUsers($users,$emails) {
  $ret = "";
  foreach($users as $email => $data) {
    if (in_array($email,$emails)) {
      $ret[$email] = $data;
    }
  }
  return $ret;
}
*/
function getUserBadgeTotals($users) {
  global $courses;
  foreach($users as $email => $data) {
    $total = 0;
    $complete = "";
    if (!is_array($data["eLearning"]["complete"])) {
      $data["eLearning"]["complete"] = [];
    }
    if (!is_array($data["courses"]["complete"])) {
      $data["courses"]["complete"] = [];
    }
    $complete = array_merge($data["eLearning"]["complete"],$data["courses"]["complete"]);
    for($i=0;$i<count($complete);$i++) {
      $total += $courses[$complete[$i]]["totalCredits"];
      if (!is_array($users[$email]["credits"])) {
        $users[$email]["credits"] = array();
      }
      $a1 = $users[$email]["credits"];
      $a2 = $courses[$complete[$i]]["credits"];
      if (!is_array($a2)) {
        $a2 = array();
      }
      $sums = array();
      foreach (array_keys($a1 + $a2) as $key) {
        $sums[$key] = @($a1[$key] + $a2[$key]);
      }
      $users[$email]["credits"] = $sums;
    }
    $users[$email]["totalCredits"] = $total;
  }
  return $users;
}

function processUser($collection,$users,$doc,$email) {
  global $courses,$tracking;
  $users[$email]["First Name"] = $doc["First Name"];
  $users[$email]["Surname"] = $doc["Surname"];
  if ($collection = "eLearning") {
    $users[$email]["eLearning"] = geteLearningCompletion($doc,$courses,$users[$email]["eLearning"]);
  }
  if ($collection = "courseAttendance") {
    $id = $doc["Course"];
    if ($tracking[$id]) { $id = $tracking[$id]; }
    if ($courses[$id])  { $users[$email]["courses"]["complete"][] = $id; }
    $users[$email]["courses"]["complete"] = @array_unique($users[$email]["courses"]["complete"]);
  }
  if ($collection = "externalBadges") {
    $badge = "";
    if ($doc["badge"]) {
      $badge["id"] = $doc["badge"];
      $badge["url"] = $doc["badge_url"];
      $users[$email]["badges"]["complete"][] = $badge;
      $users[$email]["badges"]["complete"] = @array_unique($users[$email]["badges"]["complete"]);
    }
  }
  return $users;
}

function getUsers($collection,$users) {
  global $connection_url, $db_name;
  try {
  // create the mongo connection object
    $m = new MongoClient($connection_url);
    // use the database we connected to
    $col = $m->selectDB($db_name)->selectCollection($collection);
    $query = array('email' => array('$ne' => null));
    $cursor = $col->find($query);
    foreach ($cursor as $doc) {
      if ($doc["email"]) {
        $email = $doc["email"];
        $email = str_replace("．",".",$email);
        if (strpos($email,"@") > 0) {
          $users = processUser($collection,$users,$doc,$email);
        }
      }
    }
    $query = array('Email' => array('$ne' => null));
    $cursor = $col->find($query);
    foreach ($cursor as $doc) {
      if ($doc["Email"]) {
        $email = $doc["Email"];
        $email = str_replace("．",".",$email);
        if (strpos($email,"@") > 0) {
          $users = processUser($collection,$users,$doc,$email);
        }
      }
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
  return $users;
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
      echo "Imported record for " . $data[$i]["First Name"] . " " . $data[$i]["Surname"] . " (" . $data[$i]["Email"] . ")<br/>";
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
