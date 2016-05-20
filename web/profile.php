<?php
	$location = "/profile.php";
	include('_includes/header.php');
	include_once('_includes/functions.php');

$userBadgeCredits["explorer"] = 0;
$userBadgeCredits["strategist"] = 0;
$userBadgeCredits["practitioner"] = 0;
$userBadgeCredits["pioneer"] = 0;

function getProfileData() {
	global $userData;
	if ($userData["sudo_user"]) {
		$userid = $userData["sudo_user"];
	} else {
		$userid = $userData["email"];
	}
	$doc = load($userid);
	$doc = str_replace("．",".",$doc);
	$data = json_decode($doc,true);
	$user = getProfile($data);
	$user = getF2FCompletion($userid,$user);
	$user = getExternalBadges($userid,$user);
	return $user;
}

function getExternalBadges($userid,$user) {
	$user["externalBadges"] = getExternalBadgeData($userid);
	return $user;
}

function getF2FCompletion($userid,$user) {
	$courses = getCoursesData();
	$data = getF2FAttendance($userid);
	$tracking = get_course_identifiers();
	for($i=0;$i<count($data);$i++) {
		$id = $data[$i]["Course"];
		if ($tracking[$id]) {
			$id = $tracking[$id];
		}
		if ($courses[$id]) {
			$courses[$id]["progress"] = 100;
			$badgeData = getModuleBadgeData($courses[$id]);
			$user["complete"][] = $courses[$id];
		}
	}
	return $user;
}

function getProfile($user) {
	$courses = getCoursesData();
	foreach($user as $key => $data) {
		$key = str_replace("．",".",$key);
		if (strpos($key,"_cmi.suspend_data") !== false) {
			$course = substr($key,0,strpos($key,"_cmi"));
			$progress = $data;
			if ($courses[$course]) {
				$courses[$course]["progress"] = getProgress($courses[$course],$progress);
				if ($courses[$course]["progress"] > 99) {
					$user["complete"][] = $courses[$course];
				} else {
					$user["in_progress"][] = $courses[$course];
				}
			}
		}
	}
	return $user;
}

function drawProfile($user) {
	global $userBadgeCredits;
	echo outputUserBadges($user["externalBadges"]);
	echo outputUserCredits($userBadgeCredits);
	$complete = $user["complete"];
	$in_progress = $user["in_progress"];
	if (count($complete)>0) {
		echo '<h2 class="profile_h2">Completed courses</h2>';
		outputCourses($complete,"Complete");
	}
	if (count($in_progress)>0) {
		echo '<h2 class="profile_h2">Courses in progress</h2>';
		outputCourses($in_progress,"Progress");
	}
}

function outputUserBadges($badges) {
	$output = '<div align="right" style="margin-bottom:10px;">';
	for($i=0;$i<count($badges);$i++) {
		$url = $badges[$i]["badge_url"];
		$name = $badges[$i]["badge"];
		$output .= '<img class="awardedBadge" src="'.$url.'" alt="'.$name.'"/>';
	}
	$output .= '</div>';
	return $output;
}

function outputCourses($courses,$heading) {
	echo '<table style="width: 100%;">';
        echo '<tr><th width="50%"></th><th style="width:150px;">Credits</th><th width="20%">Type</th><th width="20%">'.$heading.'</th></tr>';
	foreach ($courses as $course) {
	        echo outputCourse($course,$course["progress"]);
	}
	echo '</table>';
}

function enableSelectUser() {
	global $userData;
	if ($_GET["sudo_user"]) {
		$userData["sudo_user"] = $_GET["sudo_user"];
	}
	echo '<form action="" method="get" style="text-align: right; position: relative; bottom: 5em; margin-bottom: -60px;">';
    echo '<input name="sudo_user" type="text" value="'.$userData["sudo_user"].'"></input>';
    echo '<input type="submit" value="Go" style="padding: 0.2em 1em; position: relative; bottom: 5px;"/>';
	echo '</form>';
}

?>
<?php

	if ($userData["isAdmin"]) {
		enableSelectUser();
	}
	$user = getProfileData();
	drawProfile($user);
	include('_includes/footer.html');

?>
