<?php
	$connection_url = getenv("MONGOLAB_URI");
	$url = parse_url($connection_url);
	$db_name = preg_replace('/\/(.*)/', '$1', $url['path']);
	$collection = getenv("MONGOLAB_COLLECTION");
?>
