<?php
	include('../admin/db/mysql.inc');
	$db_link = start_mysql();

	include('./getcss.php');
	getCSS($css);

	end_mysql($db_link);
?>