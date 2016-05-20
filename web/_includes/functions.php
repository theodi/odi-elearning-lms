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
		$los = $courses[$id]["_learningOutcomes"];
		$badge = "";
		$total = 0;
		for ($i=0;$i<count($los);$i++) {
			$lo = $los[$i];
			$badge[$lo["badge"]] += $lo["credits"];
			$total += $lo["credits"];
		}
		$courses[$id]["credits"] = $badge;
		$courses[$id]["totalCredits"] = $total;
	}
	return $courses;
}


function filterCourses($courses,$userCourses) {
  $ret = "";
  foreach ($courses as $id => $data) {
    for($i=0;$i<count($userCourses);$i++) {
      if ($userCourses[$i] == $id) {
        $ret[$id] = $data;
      }
    }
  }
  return $ret;
}

function geteLearningCompletion($user,$courses,$ret) {
	foreach($user as $key => $data) {
		$key = str_replace("．",".",$key);
		if (strpos($key,"_cmi.suspend_data") !== false) {
			$course = substr($key,0,strpos($key,"_cmi"));
			$progress = $data;
			if ($courses[$course]) {
				$courses[$course]["progress"] = getProgress($courses[$course],$progress);
				if ($courses[$course]["progress"] > 99) {
					$ret["complete"][] = $courses[$course]["id"];
				} else {
					$ret["in_progress"][] = $courses[$course]["id"];
				}
			}
		}
	}
	$ret["complete"] = @array_unique($ret["complete"]);
	$ret["in_progress"] = @array_unique($ret["in_progress"]);
	$ret["in_progress"] = @array_diff($ret["in_progress"],$ret["complete"]);
	return $ret;
}

function getExternalAccess($email,$theme) {
	global $connection_url, $db_name;
	$collection = "externalAccess";
	try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$query = array('email' => $email, 'theme' => $theme);
		$res = $col->find($query);	
		foreach ($res as $doc) {
			return $doc;
		}
		$m->close();
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
   	return false;
}

function getExternalBadgeData($userid) {
   global $connection_url, $db_name;
   $attendance = false;
   $collection = "externalBadges";
   try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$query = array('Email' => $userid);
		$res = $col->find($query);	
		foreach ($res as $doc) {
			$badges[] = $doc;
		}

		$m->close();
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
   	return $badges;
}


function getF2FAttendance($userid) {
   global $connection_url, $db_name;
   $attendance = false;
   $collection = "courseAttendance";
   try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$query = array('Email' => $userid, "Attended" => "Yes");
		$res = $col->find($query);	
	
		foreach ($res as $doc) {
			$attendance[] = $doc;
		}

		$query = array('Email' => $userid, "Attended" => "yes");
		$res = $col->find($query);	
	
		foreach ($res as $doc) {
			$attendance[] = $doc;
		}
	
		$m->close();
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
   	return $attendance;
}

function getProgress($course,$progress) {
	$spoor = json_decode($progress,true);
	$progress = $spoor["spoor"];
	if ($progress["_isAssessmentPassed"] > 0 || $progress["_isCourseComplete"] > 0) {
		$progress["completion"] = str_replace("0","1",$progress["completion"]);
		$badgeData = getModuleBadgeData($course);
		return 100;
	}
	$total = strlen($progress["completion"]);
	$sub = substr_count($progress["completion"],0);
	$complete = round(($sub / $total) * 100);
	return $complete;	
}

function getModuleBadgeData($course) {
	global $userBadgeCredits;
	$los = $course["_learningOutcomes"];
	for ($i=0;$i<count($los);$i++) {
		$lo = $los[$i];
		$badge[$lo["badge"]] += $lo["credits"];
		$userBadgeCredits[$lo["badge"]] += $lo["credits"];
	}
	return $badge;
}

function getTheme($host) {
	$host = str_replace(".","_",$host);
    $courseIdentifiers = get_data_from_collection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["hosts"];
   		foreach ($doc as $key => $value) {
   			if ($key == $host) {
   				return $value[0];
   			}
   		}
   	}
   	return false;
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

function get_client_mapping($theme) {
    $courseIdentifiers = get_data_from_collection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["mapping"];
   		foreach ($doc as $key => $value) {
   			if ($key == $theme) {
   				return $value;
   			}
    	}
   	}
   	return false;
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

function getSources($doc_id) {
	$content = exec('php ~/getFile.php ' . $doc_id,$lines);
  	$headers = str_getcsv($lines[0]);
	for($i=1;$i<count($lines);$i++) {
		$line = str_getcsv($lines[$i]);
		$record["key"] = $line[0];
		$record["id"] = $line[1];
		$ret[] = $record;
	}
	return($ret);
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
   global $connection_url, $db_name;
   $collection = "elearning";
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
   global $connection_url, $db_name;
   $collection = "elearning";
   if ($email == "" || !$email) {
	return null;
   }
   $email = str_replace('.','．',$email);
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
		$output .= '<tr><td id="course_name"><a target="_blank" href="'.$doc["web_url"].'">' . $doc["title"] . '</a></td>';
	} else {
		$output .= '<tr><td id="course_name">' . $doc["title"] . '</td>';
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
	} elseif ($progress == 100) {
		$output .= '<span id="tick">&#10004;</span>';
	} elseif (is_numeric($progress)) {
		$output .= '<progress max="100" value="'.$progress.'"></progress>';
	}
	$output .= '</td>';
	$output .= '</tr>';
        return $output;
}
function outputUserCredits($data) {
	$box = '<div align="right"><table id="user_credits"><tr>';
	foreach ($data as $key => $value) {
		$total += $value;
		$box .= '<td id="user_badge_cell"><svg id="user_badge" width="80" height="60">
  					<image xlink:href="images/badges/'.$key.'.svg" src="images/badges'.$key.'.png" width="80" height="60" />
				</svg><br/>'.ucwords($key).'</td>';
		$box .= '<td id="user_credits_score">' . $value . '</td>';
	}
	$box .= '</tr></table></div>';
	return $box;
}

function outputCredits($courseId) {
	$data = get_course_credits_by_badge($courseId);
	return outputCreditsTable($data);
}
function outputCreditsTable($data) {
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
