<?php



	function printLogin($pageName, $un="", $pw="", $action="")
	{
		global $PHP_SELF;
		global $login, $enterLogin;

		if($login || $enterLogin)
		{
			$userID = passwordIsCorrect($un, $pw);
			//echo "<p>".$userID;
			if($userID)
			{
				$_SESSION['userID'] = $userID;
				echo para("Logged in!");

				if($action == "")
					echo para(ahref("rwl.php?$pageName","Return"));
				else
					echo para(ahref("rwl.php?action=$action&pageName=$pageName","Return"));

				echo para("page name=$pageName, previous action=$action");
				redirectTo($pageName, $action);
			}
			else
			{
				echo "<p>Username or password is incorrect!</p>";
				printLoginForm($pageName, $un, $prevAction);
			}
		}
		else
		{
			$u = getUser();
			if($u->id > -1)
			{
				echo para("Logged in as: $u->un");
			}
			else
			{
				//echo para("Not logged in. ".ahref("./login.php?pageName=$pageName", "Login"));
				//echo para("Not logged in. ".ahref("$PHP_SELF?action=login&pageName=$pageName", "Login"));
				echo para("Not logged in.");
				printLoginform($pageName, $un, $action);
			}
		}
	}


	function printLoginForm($pageName, $un="", $action="")
	{
		global $PHP_SELF;

		if($action == "")
			echo "<form method='POST' action='$PHP_SELF?$pageName'>\n";
		else
		{
			echo "<form method='POST' action='$PHP_SELF?action=$action&pageName=$pageName'>\n";
			echo "<input type='HIDDEN' name='action' value='$action'>\n";
			echo "<input type='HIDDEN' name='enterLogin' value='pressedEnter'>\n";
		}
		echo "<p>Username: <input type='TEXT' name='un' value='$un'>\n";
		echo "<br>Password: <input type='PASSWORD' name='pw'>\n";
		echo "<br><input type='submit' name='login' value='Login'></form>\n";
	}


	function loggedIn()
	{
		if(isset($_SESSION['userID']))
		{
			return $_SESSION['userID'];
		}
		else
			false;
	}

	function passwordIsCorrect($un, $pw)
	{
		$checkUserPasswordSQL = "SELECT id FROM rwlUser WHERE rwlUser.un = '$un' AND ENCRYPT('$pw','rp') = rwlUser.epw;";
		//echo "<p>$checkUserPasswordSQL</p>";
		$checkUserPasswordResult = mysql_query($checkUserPasswordSQL);
		if(mysql_num_rows($checkUserPasswordResult)>0)
		{
			$user = mysql_fetch_object($checkUserPasswordResult);
			return $user->id;
		}
		else
			return false;
	}

	function checkUser($userID)
	{
		$getUserSQL = "SELECT * FROM rwlUser WHERE rwlUser.id = $userID";
		$getUserResult = mysql_query($getUserSQL);
		if(mysql_num_rows($getUserResult)<1)
			return false;
		else
			return mysql_fetch_object($getUserResult);
	}

	function getUser()
	{
		$nullUser->id = -1;
		$nullUser->un = "Anonymous";
		$nullUser->level = 0;

		$userID = loggedIn();
		if($userID)
		{
			//echo "<p>UserID: $userID";
			$user = checkUser($userID);
			if($user)
			{
				return $user;
			}
		}

		return $nullUser;
	}

?>