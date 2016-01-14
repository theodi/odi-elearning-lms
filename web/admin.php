<?php
	$location = "/admin.php";
	include('_includes/header.php');
?>
<script src="js/jquery-2.1.4.min.js"></script>
<h1>Admin actions</h1>

<h2>Archive empty eLearning profiles</h2>

<section id="archive_elearning">
	Click the button below to archive empty eLearning profiles.
	<button id="archive_elearning_button">Archive empty profiles</button>
</section>
<script>

function archive_elearning() {
	$('#archive_elearning').html('Please wait');
	$.get('/api/archive.php',function(data) {
		$('#archive_elearning').html(data);
	}
}

function addListeners() {
	$('#archive_elearning_button').on('click',function() {
		archive_elearning();
	}
}

$(document).ready(function() {
	addListeners();
});

</script>

<?php
	include('_includes/footer.html');
?>
