<?php
	$location = "/dashboard/?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	$module = $_GET["module"];
?>
<form action="" method="get" style="text-align: right; position: relative; bottom: 6.5em;">
    <select name="module" style="height: 1.8em; font-size: 1.8em;">
<?php
	for ($i=1;$i<14;$i++) {
        	echo '<option ';
		if ($module == $i) {
			echo 'selected ';
		}
		echo 'value='.$i.'>Module ' . $i . '</option>';
	}
?>
    </select>
    <input type="submit" value="Go" style="padding: 0.2em 1em; font-size: 1.6em;"/>
</form>
<?php
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('dashboard/board.html');
	include('_includes/footer.html');
?>
