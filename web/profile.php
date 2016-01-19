<?php
	$location = "profile.php";
	include('_includes/header.php');


function getCoursesData() {
   global $connection_url, $db_name, $courses_collection;
   try {
         // create the mongo connection object
        $m = new MongoClient($connection_url);

        // use the database we connected to
        $col = $m->selectDB($db_name)->selectCollection($courses_collection);

        $cursor = $col->find();
	$courses = "";
        foreach ($cursor as $doc) {
		if ($doc["slug"]) {
			$id = $doc["slug"];
		} else {
			$id = $doc["id"];
		}
		$courses[$id] = $doc;
        }
        $m->close();
        return $courses;

   } catch ( Exception $e ) {
   	return [];	
   }
}

function getProfileData($email) {
	$doc = load($userData["email"]);
	$doc = str_replace("．",".",$doc);
	$data = json_decode($doc,true);
	return $data;
}

function drawProfile($user) {
	$courses = getCoursesData();
	foreach($user as $key => $data) {
		$key = str_replace("．",".",$key);
		if (strpos($key,"_cmi.suspend_data") !== false) {
			$course = substr($key,0,strpos($key,"_cmi"));
			$progress = $data;
			if ($courses[$course]) {
				renderProgress($courses[$course],$progress);
			}
		}
	}	
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

function renderProgress($course,$progress) {
	echo $course["title"] . "<br/>";
	$spoor = json_decode($progress,true);
	$progress = $spoor["spoor"];
	if ($progress["_isAssessmentPassed"] > 0 || $progress["_isCourseComplete"] > 0) {
		$progress["completion"] = str_replace("0","1",$progress["completion"]);
	}
	echo $progress["completion"] . "<br/>";
	if (substr_count($progress["completion"],0) < 1 && $progress["completion"] != "") {
		$badgeData = getModuleBadgeData($course);
		foreach ($badgeData as $badge => $credit) {
			echo $badge . " : " . $credit . "<br/>";
		}
	}
}

?>
<h1>Your profile</h1>
<?php
	require_once('_includes/functions.php');
	$user = getProfileData($email);
	drawProfile($user);
	include('_includes/footer.html');

?>
