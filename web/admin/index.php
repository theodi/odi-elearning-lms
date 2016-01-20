<?php
	$location = "/admin/index.php";
        $path = "../";
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);

	include('_includes/header.php');
?>
<script src="../js/jquery-2.1.4.min.js"></script>
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

<h2>Add adapt course/module</h2>
<section id="add_adapt_course">
	Please enter the url of the course homepage in the box below, it must be publically accessible. (e.g. http://accelerate.theodi.org/en/module1 or http://training.theodi.org/inaday).<br/>
	<input type="text" id="course_url"></input></br>
	<button id="import_adapt_course">Import adapt course</button>
</section>

<h2>Update course identifiers</h2>
<section id="update_identifiers">
	Click the button below to update the mapping of all the different course identifiers. <br/>
	<button id="update_identifiers_button">Update course identifiers</button>
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

function update_identifiers() {
	$('#update_identifiers').html('Please wait');
	$.get('/api/update_course_identifiers.php',function(data) {
		$('#update_identifiers').html(data);
	});
}

function import_adapt() {
	url = $('#course_url').val();
	if (url == "") {
		alert("please enter a course url!");
		return;
	} 
	$('#add_adapt_course').html('Please wait');
	$.get('/api/import_adapt.php?url=' + encodeURI(url), function(data) {
		$('#add_adapt_course').html(data);
	});
}

function addListeners() {
	$('#archive_elearning_button').on('click',function() {
		archive_elearning();
	});
	$('#update_courses_button').on('click',function() {
		update_courses();
	});
	$('#update_identifiers_button').on('click',function() {
		update_identifiers();
	});
	$('#import_adapt_course').on('click',function() {
		import_adapt();
	});
}

$(document).ready(function() {
	addListeners();
});

</script>

<?php
	include('_includes/footer.html');
?>
