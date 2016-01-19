<?php
	$location = "profile.php";
	include('_includes/header.php');
?>
<h1>Your profile</h1>
<?php
	require_once('_includes/functions.php');
	$doc = load($userData["email"]);
	$doc = str_replace("．",".",$doc);
	$data = json_decode($doc,true);
	print_r($doc);
	include('_includes/footer.html');
?>
