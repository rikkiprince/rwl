<?php

	session_start();

	include("./render.php");
	include('../admin/db/mysql.inc');
	include('./user.php');

	$db_link = start_mysql();

	

	if($submit)
	{
		$userID = passwordIsCorrect($un, $pw);
		//echo "<p>".$userID;
		if($userID)
		{
			$_SESSION['userID'] = $userID;
			echo para("Logged in!");

			echo para(ahref("rwl.php?$pageName","Return"));
		}
		else
		{
			echo "<p>Username or password is incorrect!</p>";
			printLoginForm();
		}
	}
	else
	{
		printLoginForm();
	}

	
	end_mysql($db_link);

?>