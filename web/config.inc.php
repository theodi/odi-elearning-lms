<?php
	$connection_url = parse_url(getenv("MONGOLAB_URI"));
	$dbuser = getenv("MONGOLAB_USER");
	$dbuser = getenv("MONGOLAB_PASS");
	$collection = getenv("MONGOLAB_COLLECTION");
?>
