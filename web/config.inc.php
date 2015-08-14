<?php
	$connection_url = getenv("MONGOLAB_URI");
	$url = parse_url($connection_url);
	$db_name = preg_replace('/\/(.*)/', '$1', $url['path']);
	$collection = getenv("MONGOLAB_COLLECTION");
	$mandrill_key = getenv("MANDRILL_KEY");
	$eLearning_prefix = getenv("ELEARNING_PREFIX");
	$mail_lock = getenv("MAIL_LOCK");
?>
