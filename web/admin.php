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

<h2>Update courses from publisher</h2>

<section id="update_courses">
	Click below to update the list of courses from the publisher (theodi.org) data.
	<button id="update_courses_button">Update courses from publisher</button>
</section>
<script>

function archive_elearning() {
	$('#archive_elearning').html('Please wait');
	$.get('/api/archive.php',function(data) {
		$('#archive_elearning').html(data);
	});
}

function update_courses() {
	$('#update_courses').html('Please wait');
	$.get('/api/update_courses.php',function(data) {
		$('#update_courses').html(data);
	});
}

function addListeners() {
	$('#archive_elearning_button').on('click',function() {
		archive_elearning();
	});
	$('#update_courses_button').on('click',function() {
		update_courses();
	});
}

$(document).ready(function() {
	addListeners();
});

</script>

<?php
	include('_includes/footer.html');
?>
