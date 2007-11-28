<?php

//	header("Location: /~rfp102/rwl/rwl.php?".preg_replace("/\/~rfp102\/rwl\//", "", $_SERVER['REQUEST_URI']));
//	// would this be safe? header("Location: ".preg_replace("/\/rwl\//", "/rwl/rwl.php?", $_SERVER['REQUEST_URI']));
//	exit;


	$pageName = urldecode(preg_replace("/\/~rfp102\/rwl\//", "", $_SERVER['REQUEST_URI']));

	include('rwl.php');
?>
