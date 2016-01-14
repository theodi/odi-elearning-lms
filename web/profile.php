<?php
	$location = "profile.php";
	include('_includes/header.php');
?>
<h1>Your profile</h1>
<?php
	require_once('_includes/functions.php');
	$doc = load($userData["email"]);
	echo $doc;
	include('_includes/footer.html');
?>
