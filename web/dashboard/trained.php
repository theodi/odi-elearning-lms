<?php
	$location = "/dashboard/index.php?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	include('_includes/functions.php');
	$data = get_data_from_collection($collection);
	$courses = getCoursesData();
	foreach ($data as $user) {
		$complete_modules = getCompleteModuleCount($user,$courses);
		if ($complete_modules > 0) {
			$people_trained++;
			$complete[$complete_modules]++;
		}
	}
	echo $people_trained;
	print_r($complete);

function getCompleteModuleCount($user,$courses) {
	$complete = 0;
	foreach($user as $key => $data) {
                $key = str_replace("．",".",$key);
                if (strpos($key,"_cmi.suspend_data") !== false) {
                        $course = substr($key,0,strpos($key,"_cmi"));
                        $progress = $data;
                        if ($courses[$course]) {
                                $courses[$course]["progress"] = getProgress($courses[$course],$progress);
                                if ($courses[$course]["progress"] > 99) {
                                        $complete++;
                                }
                        }
                }
        }
	return $complete;
}

function getProgress($course,$progress) {
        $spoor = json_decode($progress,true);
        $progress = $spoor["spoor"];
        if ($progress["_isAssessmentPassed"] > 0 || $progress["_isCourseComplete"] > 0) {
                $progress["completion"] = str_replace("0","1",$progress["completion"]);
                return 100;
        }
        $total = strlen($progress["completion"]);
        $sub = substr_count($progress["completion"],0);
        $complete = round(($sub / $total) * 100);
        return $complete;       
}



	include('_includes/footer.html');
?>
