<?php
	$location = "/learners/index.php";
        $path = "../";
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);

	include('_includes/header.php');
?>
<script src="../js/jquery-2.1.4.min.js"></script>

<h1>Learner profiles</h1>

<p>Page for browsing individual learner profiles</p>

<?php
	include('_includes/footer.html');
?>
