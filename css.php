<?php

	include("./settings.php");

	include($mysqlDetailsFileName);
	$db_link = start_mysql();

	include('./getcss.php');
	
	$css = $_GET['css'];
	getCSS($css);

	end_mysql($db_link);
?>