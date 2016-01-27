<?php

function getCoursesData() {
	global $courses_collection;
	$cursor = get_data_from_collection($courses_collection);
	$tracking = get_course_identifiers();
	$courses = "";
	foreach ($cursor as $doc) {
   		if ($doc["slug"]) {
			$id = $doc["slug"];
		} else {
			$id = $doc["id"];
		}
		if ($tracking[$id]) {
			$id = $tracking[$id];
		}
		if ($courses[$id] != "") {
			$courses[$id] = array_merge($courses[$id],$doc);
			$courses[$id]["id"] = $id;
		} else {
			$courses[$id] = $doc;
		}
	}
	return $courses;
}

function get_course_identifiers() {
    $courseIdentifiers = get_data_from_collection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["identifiers"];
   		foreach ($doc as $key => $value) {
   			for($i=0;$i<count($value);$i++) {
	 	  		$tracking[$value[$i]] = $key;
   			}
   		}
   	}
   	return $tracking;
}

function get_course_credits_by_badge($id) {
	$badge["explorer"] = 0;
	$badge["strategist"] = 0;
	$badge["practitioner"] = 0;
	$badge["pioneer"] = 0;
	$course = get_course_by_id($id);
	$los = $course["_learningOutcomes"];
	for ($i=0;$i<count($los);$i++) {
		$lo = $los[$i];
		$badge[$lo["badge"]] += $lo["credits"];
	}
	return $badge;
}

function get_course_by_id($id) {
	$courses = getCoursesData();
	return $courses[$id];
}

function get_data_from_collection($collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();
	
	return $cursor;

	$m->close();

	return $doneCount;
   } catch ( MongoConnectionException $e ) {
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
	return false;
   } catch ( MongoException $e ) {
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
	return false;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
	return false;
   }
}

function archive_empty_profiles() {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();
	
	$doneCount = 0;

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
			$doneCount++;
		}
	}
		
	$m->close();

	return $doneCount;
   } catch ( MongoConnectionException $e ) {
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
	return false;
   } catch ( MongoException $e ) {
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
	return false;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
	return false;
   }
}
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

function outputCourse($doc,$progress) {
	$output = "";
   	if ($doc["web_url"]) {
		$output .= '<tr><td><a target="_blank" href="'.$doc["web_url"].'">' . $doc["title"] . '</a></td>';
	} else {
		$output .= '<tr><td>' . $doc["title"] . '</td>';
	}
     	$output .= '<td style="text-align: center;">';
	$output .= outputCredits($doc["id"]);
	$output .= '</td>';
	$output .= '<td style="text-align: center;"><img style="max-height: 40px;" src="/images/';
	$output .= $doc["format"]; 
	$output .= '.png"></img></td>';
	$output .= '<td style="text-align: center;">';
	if ($progress == "") {
		if (substr($doc["id"],0,4) == "ODI_") {
			$dashId = str_replace("ODI_","",$doc["id"]);
			$output .= '<a href="/dashboard/index.php?module=' . $dashId . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} elseif ($tracking[$doc["slug"]]) {
			$output .= '<a href="/dashboard/index.php?module=' . $tracking[$doc["slug"]] . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} 
	} elseif ($progress = 100) {
		$output .= '<span id="tick">&#10004;</span>';
	} elseif (is_numeric($progress)) {
		$output .= '<progress max="100" value="'.$progress.'"></progress>';
	}
	$output .= '</td>';
	$output .= '</tr>';
        return $output;
}

function outputCredits($courseId) {
	$data = get_course_credits_by_badge($courseId);
	$rows = "";
	foreach ($data as $key => $value) {
		$total += $value;
		$rows .= "<tr><td>" . $key . "</td><td>" . $value . '</td></tr>';
	}
	$box = '<div id="course_credits_box"><score>' . $total .' </score><table id="course_credits_table">';
	$box .= $rows;
	$box .= '</table></div>';
	return $box;
}

?>
