<?php
	$connection_url = getenv("MONGOLAB_URI");
	$url = parse_url($connection_url);
	$db_name = preg_replace('/\/(.*)/', '$1', $url['path']);
	$collection = getenv("MONGOLAB_COLLECTION");
	$mandrill_key = getenv("MANDRILL_KEY");
	$eLearning_prefix = getenv("ELEARNING_PREFIX");
    $client_id = getenv("GOOGLE_OAUTH_ID");
 	$client_secret = getenv("GOOGLE_OAUTH_SECRET");
 	$redirect_uri = getenv("GOOGLE_REDIRECT_URI");
	$courses_collection = getenv("MONGOLAB_COURSES");
	$instances_collection = getenv("MONGOLAB_INSTANCES");
?>
