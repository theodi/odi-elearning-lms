<?php
	$location = "/courses/index.php";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	echo '<table style="width: 100%;">';
	echo '<tr><th>Course name</th><th>Type</th><th>Dashboard</th></tr>';
	echo coursesTable();
	echo '</table>';
	include('_includes/footer.html');


function coursesTable() {
   global $connection_url, $db_name, $courses_collection;

   $tracking["open-data-day"] = "InADay";
   $tracking["open-data-science"] = "ODS";

   try {
         // create the mongo connection object
        $m = new MongoClient($connection_url);

        // use the database we connected to
        $col = $m->selectDB($db_name)->selectCollection($courses_collection);

        $query = array('format' => 'course');
        $cursor = $col->find($query);
        $output = '';
        foreach ($cursor as $doc) {
		$output .= '<tr><td>' . $doc["title"] . '</td>';
		$output .= '<td style="text-align: center;"><img src="/images/';
			if ($doc["type"]) { $output .= $doc["type"]; } else { $output .= "f2f"; };
		$output .= '.png"></img></td>';
		$output .= '<td style="text-align: center;">';
		if ($doc["_moduleId"]) {
			$output .= '<a href="/dashboard/index.php?module=' . $doc["_moduleId"] . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} elseif ($tracking[$doc["slug"]]) {
			$output .= '<a href="/dashboard/index.php?module=' . $tracking[$doc["slug"]] . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} 
		$output .= '</td>';
		$output .= '</tr>';
        }
        $m->close();
        return $output;

   } catch ( MongoConnectionException $e ) {
//      return false;
        syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//      return false;
        syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//      return false;
        syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}
?>
