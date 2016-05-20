<?php
	$location = "/dashboard/trained.php";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	include_once('_includes/functions.php');
	$data = get_data_from_collection($collection);
	$courses = getCoursesData();
	foreach ($data as $user) {
		$complete_modules = getCompleteModuleCount($user,$courses);
		if ($complete_modules > 0) {
			$people_trained++;
			$complete[$complete_modules]++;
			$module_completions+=$complete_modules;
		}
	}
	ksort($complete);

function getCompleteModuleCount($user,$courses) {
	$complete = 0;
	foreach($user as $key => $data) {
                $key = str_replace("．",".",$key);
                if (strpos($key,"_cmi.suspend_data") !== false) {
                        $course = substr($key,0,strpos($key,"_cmi"));
                        $progress = $data;
                        if ($courses[$course] && $courses[$course]["format"] == "eLearning") {
				$course_id = $courses[$course]["id"];
				$course_id = substr($course_id,4);
				if (is_numeric($course_id) && $course_id < 14) {
                                	$courses[$course]["progress"] = getProgress($courses[$course],$progress);
                                	if ($courses[$course]["progress"] > 99) {
                                        	$complete++;
                                	}
				}
                        }
                }
        }
	return $complete;
}
/*
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
*/
?>
<style>
	body {line-height: 1;}
        .box {font-family: Arial, sans-serif;background-color: #F1F1F1;border:0;width:340px;webkit-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);margin: 0 auto 25px;text-align:center;padding:10px 0px; display: inline-block; height: 11em; font-size: 15px;}
        .box img{padding: 10px 0px;}
        .box a{color: #427fed;cursor: pointer;text-decoration: none;}
        .number {font-size: 8em;}
        .sub {display: block; font-size: 2em;}
        .subsub {display: block; font-size: 1em;}
</style>
<div align="center">
<div class="box">
  <div>
        <span class="number"><?php echo $people_trained; ?></span>
        <span class="sub">people trained</span>
        <span class="subsub">(have completed at least 1 eLearning module)</span>
  </div>
</div>
&nbsp;
&nbsp;
&nbsp;
&nbsp;
<div class="box">
  <div>
        <span class="number"><?php echo $module_completions;?></span>
        <span class="sub">Module completions</span>
        <span class="subsub">(1 person can complete multiple modules)</span>
  </div>
</div>
</div>
<h2>Completed modules breakdown</h2>
<p>The table below shows how many people have completed at least X modules. All those who have completed 2 will have completed 1 but are not included in this count. Everyone in this table has completed at least 1 module. It is not possible to tell from this data which modules have been completed, just the count.</p>
<div align="center">
<table style="width: 400px; text-align: center; line-height:20px;">
<tr><th>Number of modules completed</th><th>Number of people</th></tr>
<?php
	for($i=1;$i<=count($complete);$i++) {
		echo '<tr><td>' . $i . '</td><td>' . $complete[$i] . '</td></tr>';
	}
?>
</table>
</div>

<?php
	include('_includes/footer.html');
?>
