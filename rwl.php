<?php

	ob_start();
	session_start();

	/**************************************
	 * Editable settings
	 **************************************/

	include('../admin/db/rwl_dev_mysql.inc');

	$rwlMain = "Home";
	$rwlTitle = "AIBO Internship";
	$rwlTableNamePrefix = "";

	/*******/


	$db_link = start_mysql();

	$QS = htmlDecode4($_SERVER['QUERY_STRING']);

	//echo "func session id: ".session_id();


	include('./diff_patch.php');
	
	include("./render.php");

	include("./user.php");
	include("./calendar.php");

	function registerGlobalPOST($name)
	{
		global ${$name};
		
		if(isset($_POST[$name]))
			${$name} = $_POST[$name];
	}

	function registerGlobalGET($name)
	{
		global ${$name};
		
		if(isset($_GET[$name]))
			${$name} = $_GET[$name];
	}


	/********************
	REGISTER_GLOBALS
	*********************/

	registerGlobalGET('PHP_SELF');

	registerGlobalGET('pageName');
	
	//$cm = $_GET['cm'];
	//$cy = $_GET['cy'];
	registerGlobalGET('cm');
	registerGlobalGET('cy');
	
	registerGlobalGET('action');
	
	registerGlobalPOST('submit');
	if(!isset($submit))
		registerGlobalGET('submit');
	
	registerGlobalGET('login');
	registerGlobalGET('enterLogin');
	
	registerGlobalGET('un');
	registerGlobalGET('pw');
	
	registerGlobalGET('for');
	
	
	/*registerGlobalGET('');
	registerGlobalGET('');
	registerGlobalGET('');
	registerGlobalGET('');
	registerGlobalGET('');
	registerGlobalGET('');
	registerGlobalGET('');*/
	/********************/


	if(!isset($pageName))
	{
		if(strlen($QS)==0)
		{
			redirectToMain();
		}
		$pageName = ucsmart($QS);
		//echo para("Page Name: $pageName");
	}
	if(!validPageName($pageName))
	{
		redirectToMain();
	}




	//$userID = -1;

	// userID must be above zero - if does not exist, set to -1...
	//$userLevel = 2;	// get from db here!
	
	/*{
		echo para("User does not exist!");
		$userID = -1;
		$userName = "Anonymous";
		$userLevel = 0;
	}
	else
	{
		$user = mysql_fetch_object($getUserResult);
		$userID = $user->id;
		$userName = $user->un;
		$userLevel = $user->level;
	}*/

	$user = getUser();
	$userID = $user->id;
	$userName = $user->un;
	$userLevel = $user->level;

	//echo para("you are user #$userID, $userName, level $userLevel");
	
	for($i=$userLevel; $i>=0; $i--)
		registerGlobalPOST('newEntry'.$i);
	//for($i=$userLevel; $i>=0; $i--)
		//registerGlobalGET('');
		

	
	$dh = getValidDate($pageName);
	if($dh)
	{
		/*if(isToday($dh))
			$pageTitle = "Today (".date("jS F", $dh).")";*/
		$rd = getRelativeDay($dh);
		if($rd)
			$pageTitle = "$rd (".date("jS F", $dh).")";
		else
			$pageTitle = date("l jS F Y", $dh);
	}
	else
		$pageTitle = $pageName;



	//echo h($pageTitle,1);
	$dh = getValidDate($pageName);
	if($dh)
	{
		$getPreviousEntrySQL = "SELECT page FROM rwlEntry WHERE page=DATE_FORMAT(STR_TO_DATE(page, '%Y%m%d'), '%Y%m%d') AND page < '$pageName' ORDER BY page DESC LIMIT 1;";
		$getPreviousEntryResult = mysql_query($getPreviousEntrySQL);
		if(mysql_num_rows($getPreviousEntryResult) == 1)
		{
			$pe = mysql_fetch_object($getPreviousEntryResult);
			$pd = getValidDate($pe->page);
			if($pd)
			{
				$prd = getRelativeDay($pd);
				if($prd)
					$previousEntry = $prd;
				else
					$previousEntry = date("j M Y", $pd);

				$previousEntryDate = $pe->page;

				$previousEntryLink = ahref("$PHP_SELF?$previousEntryDate", "< ".$previousEntry, "", "", "prev");
			}
		}

		$getNextEntrySQL = "SELECT page FROM rwlEntry WHERE page=DATE_FORMAT(STR_TO_DATE(page, '%Y%m%d'), '%Y%m%d') AND page > '$pageName' ORDER BY page ASC LIMIT 1;";
		$getNextEntryResult = mysql_query($getNextEntrySQL);
		if(mysql_num_rows($getNextEntryResult) == 1)
		{
			$ne = mysql_fetch_object($getNextEntryResult);
			$nd = getValidDate($ne->page);
			if($nd)
			{
				$nrd = getRelativeDay($nd);
				if($nrd)
					$nextEntry = $nrd;
				else
					$nextEntry = date("j M Y", $nd);

				$nextEntryDate = $ne->page;

				$nextEntryLink = ahref("$PHP_SELF?$nextEntryDate", $nextEntry." >", "", "", "next");
			}
		}
	}




?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<head>
	<title><?php echo $pageTitle; ?> - <?php echo $rwlTitle; ?> - RWL</title>
	<link rel="stylesheet" href="test.css" type="text/css">
<?php 
	include('./getcss.php');
	getStyleSheets($_SESSION['defaultCSS']);

	echo "<link rel='prev' href='$PHP_SELF?$previousEntryDate' title='$previousEntry'>";
	echo "<link rel='next' href='$PHP_SELF?$nextEntryDate' title='$nextEntry'>";
?>
</head>
<body>

<?php




	echo "<div id='header'>";
	echo "<h1><span>$rwlTitle</span></h1>";	// should this have <h1></h1> DISCUSS!
	echo "</div>";


	echo "<div id='pageTitle'><h2>$previousEntryLink</h2> <h1>$pageTitle</h1> <h2>$nextEntryLink</h2></div>";


	echo "<div id='links'>";
	getLinks();
	echo "</div>";
	

	echo "<div id='body'>";

	if(isset($action))
	{
		//echo para("Must perform action: $action");

		switch($action)
		{
			case 'edit':	// output edit form
							echo para("Edit page ".ahref("$PHP_SELF?$pageName",$pageName));

							$editPageSQL = "SELECT * FROM rwlEntry WHERE rwlEntry.page = '".addslashes($pageName)."' AND rwlEntry.level<=$userLevel;";
							$editPageResult = mysql_query($editPageSQL);
							
							if(!isset($submit))
							{
							/*
							* IF SOMETHING HAS NOT BEEN SUBMITTED, FOR ALL OF THE LEVELS FROM THE USER LEVEL DOWN TO ZERO,
							* OUTPUT A TEXTAREA WITH THE CONTENT FOR THAT LEVEL ON THIS PAGE
							*/
								while($entry = mysql_fetch_object($editPageResult))
								{
									//echo "<textarea name='entry$entry->id' ROWS=10 COLS=50>$entry->content</textarea>\n";
									${"newEntry$entry->level"} = $entry->content;
								}
								echo "<form method='POST' action='$PHP_SELF'>\n";
								echo "<input type='HIDDEN' name='action' value='edit'>\n";
								echo "<input type='HIDDEN' name='pageName' value='$pageName'>\n";
								echo "<p><input type='submit' name='submit' value='Save All'></p>";
								//while($entry = mysql_fetch_object($editPageResult))
								for($i=$userLevel; $i>=0; $i--)
								{
									echo para("Level $i");
									//echo "<textarea name='entry$entry->id' ROWS=10 COLS=50>$entry->content</textarea>\n";
									echo "<textarea name='newEntry$i' rows=19 cols=100 style='width:100%'>".${"newEntry$i"}."</textarea>\n";
									echo "<p><input type='submit' name='submit' value='Save All'></p>";
								}
								echo "</form>\n";

								/*echo " <input type='reset' name='reset'>";
								echo "</form>\n";
								echo "<form action='$PHP_SELF?$pageName' method='post'><input type='submit' value='Cancel'></form>";*/
							}
							else
							{
							/*
							* IF SOMETHING HAS BEEN SUBMITTED, THEN IT NEEDS TO BE PROCESSED AND STORED IN THE DATABASE.
							*/
								echo para("Store in database!");
								/*	Should this load all entries into memory to do this check, or should it
									check the database for each possible value?  Maybe check how many are in
									the database, and it it is over half, leave them in the database? */
							
								while($entry = mysql_fetch_object($editPageResult))
								{
									//echo "<textarea name='entry$entry->id' ROWS=10 COLS=50>$entry->content</textarea>\n";
									${"oldEntry$entry->level"} = $entry->content;
								}
								for($i=$userLevel; $i>=0; $i--)
								{
									$newContent = ${"newEntry$i"};
									
									if(isset(${"oldEntry$i"}))
									{
										echo para("Exists in DB...");
										$oldContent = ${"oldEntry$i"};
										
										$diffArray = diff($oldContent, $newContent);
										$diffString = var_export($diffArray, TRUE);

										/*echo para("$entry->id");
										echo para("Old content: $oldContent");
										echo para("New content: $newContent");
										echo para("Differences: $diffString");*/
										
										//if(count($diffArray) < 1)
										if(empty($diffArray))
										{
											echo para("No changes!  Nothing to do :)");
										}
										else
										{
											$getEntrySQL = "SELECT * FROM rwlEntry WHERE page='".addslashes($pageName)."' AND level='$i';";
											$getEntryResult = mysql_query($getEntrySQL);
											if(mysql_num_rows($getEntryResult) < 1)
											{
												echo para("Entry does not exist!");
												// call insert entry function?
											}
											else
											{
												$entry = mysql_fetch_object($getEntryResult);
												$entryID = $entry->id;
												echo para("Entry ID: $entryID");

												$insertDifferenceSQL = "INSERT INTO rwlHistory(entry, diff, editor, edited, eip) VALUES('$entryID','".addslashes($diffString)."','$userID',NOW(),'".$_SERVER['REMOTE_ADDR']."');";
												$updateEntrySQL = "UPDATE rwlEntry SET content='".addslashes($newContent)."', editor='$userID', edited=NOW() WHERE id='$entryID';";
												echo para($insertDifferenceSQL);
												echo para($updateEntrySQL);
											
												$result = mysql_query("begin");
												if($result) $result = mysql_query($insertDifferenceSQL);
												if($result) $result = mysql_query($updateEntrySQL);
												if($result) $result = mysql_query("commit");
												if($result)
												{
													echo para("Edit saved!");
												}
												else
												{
													$result = mysql_query("rollback");
													echo para("Edit saving failed :( Error No: ".mysql_errno()."  Error: ".mysql_error());
												}
											}	
										}
									}
									elseif(!empty($newContent))
									{
										echo para("Does not exist in DB.");
										$insertEntrySQL = "INSERT INTO rwlEntry(page,content,level,creator,created,cip,editor,edited,eip) VALUES('".addslashes($pageName)."','".addslashes($newContent)."','$i','$userID',NOW(),'".$_SERVER['REMOTE_ADDR']."','$userID',NOW(),'".$_SERVER['REMOTE_ADDR']."');";
										//$insertEntrySQL = "INSERT INTO rwlEntry(page) VALUES('".addslashes($pageName)."';";
										echo para($insertEntrySQL);
										
										$result = mysql_query("begin");
										if($result) $result = mysql_query($insertEntrySQL);
										if($result) $result = mysql_query("commit");
										if($result)
										{
											echo para("New entry added!");
										}
										else
										{
											$result = mysql_query("rollback");
											echo para("Adding new entry failed :( Error No: ".mysql_errno()."  Error: ".mysql_error());
										}
									}
									else
									{
										echo para("No change made to this.  Leave it alone!");
									}
								}
								echo para(ahref("$PHP_SELF?$pageName"));
								redirectTo($pageName);
							}
							break;
			case 'history':	echo para("History for page ".ahref("$PHP_SELF?$pageName",$pageName));
							$getPageSQL = "SELECT * FROM rwlEntry WHERE rwlEntry.page = '".addslashes($pageName)."' ORDER BY rwlEntry.level DESC;";
							$getPageResult = mysql_query($getPageSQL);
							if(mysql_num_rows($getPageResult) < 1)
							{
								echo para("No entries on this page yet!");
							}
							else
							{
								while($entry = mysql_fetch_object($getPageResult))		// each page can have numerous entries, hence var names
								{
									//echo para("Level $entry->level");
									//echo para(htmlentities($entry->content));
									echo hr();
									render($entry);
									echo hr();

									$getHistorySQL = "SELECT * FROM rwlHistory WHERE rwlHistory.entry = '$entry->id' ORDER BY edited DESC;";
									//echo $getHistorySQL;
									$temp = $entry;
									$getHistoryResult = mysql_query($getHistorySQL);
									//$i = 0;
									while($history = mysql_fetch_object($getHistoryResult))
									{
										//$i++;
										//echo para($i);
										eval("\$diff_arr = $history->diff;");
										//echo para(print_r($diff_arr));
										$temp->content = unpatch($temp->content, $diff_arr);
										render($temp);
									}

									echo hr();
								}
							}
							break;
/*			case 'login':	echo para("Login");
							if($submit)
							{
								$userID = passwordIsCorrect($un, $pw);
								//echo "<p>".$userID;
								if($userID)
								{
									$_SESSION['userID'] = $userID;
									echo para("Logged in!");

									echo para(ahref("rwl.php?$pageName","Return"));
									echo para("page name=$pageName, previous action=$prevAction");
									//redirectTo($pageName, $prevAction);
								}
								else
								{
									echo "<p>Username or password is incorrect!</p>";
									printLoginForm($pageName, $prevAction);
								}
							}
							else
							{
								printLoginForm($pageName, $prevAction);
							}*/
			case 'search':	echo para("Search!");
						$getSearchSQL = "SELECT page FROM rwlEntry WHERE content RLIKE '".addslashes($for)."';";
						$getSearchResult = mysql_query($getSearchSQL);
						if(mysql_num_rows($getSearchResult) < 1)
						{
							echo para("No entries match your search of ".addslashes($for));
						}
						else
						{
							while($entry = mysql_fetch_object($getSearchResult))
							{
								echo para(ahref("$PHP_SELF?$entry->page", $entry->page));
							}
						}
						break;
			case 'setcss':	$_SESSION['defaultCSS'] = $css;
							redirectTo($pageName);
			default:		echo para("Action unknown.");
		}
	}
	else
	{
		echo "<div id='content'>";

		switch($QS)
		{
			/* Handle special case pages here */
			case $rwlMain:	echo para("This is the Main page.  Welcome!");
						redirectTo(date("Ymd"));
						break;
			default:	//echo para("Default operation: $QS");
						//if(validPageName($QS))
						{
							//echo para("Valid page name!");
							//$pageName = ucsmart($QS);
							//echo para("Page Name: $pageName");

							// now do database access...

							$getPageSQL = "SELECT * FROM rwlEntry WHERE rwlEntry.page = '".addslashes($pageName)."' ORDER BY rwlEntry.level DESC;";
							$getPageResult = mysql_query($getPageSQL);
							if(mysql_num_rows($getPageResult) < 1)
							{
								echo para("No entries on this page yet!");
							}
							else
							{
								while($entry = mysql_fetch_object($getPageResult))		// each page can have numerous entries, hence var names
								{
									//echo para("Level $entry->level");
									//echo para(htmlentities($entry->content));
									render($entry);
								}
							}

							printParentPage($pageName);
							printSubPages($pageName);

							echo para(ahref("$PHP_SELF?action=edit&pageName=$pageName", "Edit this page"));
							echo para(ahref("$PHP_SELF?action=history&pageName=$pageName", "View history"));
						}
						//else
						//{
						//	echo para("Invalid page name.");
						//}
				
		}

		echo "</div>";
	}

	echo "</div>";

	//echo para($QS);


	/*
	echo "<div id='links'>";
	getLinks();
	echo "</div>";
	*/

	
	echo "<div id='utilities'>";

	printLogin($pageName, $un, $pw, $action);
	
	printCalendar($pageName, $cm, $cy);

	echo para("<form action='$PHP_SELF' method='get' id='formGoto'>Go to: <input type='text' name='pageName' value='$pageName'></form>");
	echo para("<form action='$PHP_SELF' method='get' id='formSearch'><input type='hidden' name='pageName' value='$pageName'><input type='hidden' name='action' value='search'>Search: <input type='text' name='for' value='$for'></form>");

	echo getCSSLinks($_SESSION['defaultCSS'], 5);

	echo "</div>";






	include("./footer.php");


	/*if($pageName != "ATest")
	{
		echo "<hr>";
		echo para("some include here!");
		readfile("http://www.ecs.soton.ac.uk/~rfp102/rwl/rwl.php?ATest");
	}*/



	end_mysql($db_link);
	
	ob_end_flush();






	// some useful functions

	function validPageName($pn)
	{
		return TRUE;
	}

	

	// html decode as PHP4 and other suggestions do not work!
	function htmlDecode4($str)
	{
		return preg_replace("/%([\dA-Fa-f][\dA-Fa-f])/e","chr(hexdec('$1'))", $str);
	}

	// http://uk.php.net/manual/en/function.ucwords.php#65712 - lev at phpfox dot com
	function ucsmart($text)
	{
		//return preg_replace('/([^a-z\']|^)([a-z])/e', '"$1".strtoupper("$2")', strtolower($text));
		//return preg_replace('/([^a-z\']|^)([a-z])/e', '"$1".strtoupper("$2")', $text);
		return preg_replace('/([^A-Za-z\']|^)([a-z])/e', '"$1".strtoupper("$2")', $text);
	}

	function getValidDate($str)
	{
		if(preg_match("/^[1-9][0-9]*[0-9]{2}[0-9]{2}$/", $str)>0)
		{
			$year = substr($str, 0, -4);
			$month = substr($str, -4, 2);
			$day = substr($str, -2, 2);
			if(checkdate($month, $day, $year))
			{
				return mktime(0,0,0, $month, $day, $year);
			}
		}

		return false;
	}

	function redirectToMain()
	{
		global $rwlMain;
		redirectTo($rwlMain);
	}

	function redirectTo($str, $action="")
	{
		global $PHP_SELF;

		if($action != "")
		{
			header("Location: $PHP_SELF?action=$action&pageName=$str");
		}
		else
		{
			header("Location: $PHP_SELF?$str");
		}
		echo para("Redirect!");
		exit;
	}

	function printParentPage($page)
	{
		$s = strrpos($page, "\\");
		//echo para($s);
		if($s != "")
		{
			$parent = substr($page, 0, $s);
			echo para("Parent: ".ahref("$PHP_SELF?$parent", $parent));
		}
	}

	function printSubPages($page)
	{
		global $PHP_SELF;

		//echo para("Subpages:");
		//$getSubPagesSQL = "SELECT * FROM rwlEntry WHERE rwlEntry.page RLIKE '".addslashes(addslashes($page))."\\\\\\\[^\\\]*$';";
		//$getSubPagesSQL = "SELECT * FROM rwlEntry WHERE rwlEntry.page RLIKE '".addslashes(addslashes("^$page\\[^\]*$"))."';";
		$getSubPagesSQL = "SELECT * FROM rwlEntry WHERE rwlEntry.page RLIKE '".addslashes(addslashes("^$page\\.*$"))."';";
		$getSubPagesResult = mysql_query($getSubPagesSQL);

		//echo para($getSubPagesSQL);

		if(mysql_num_rows($getSubPagesResult) < 1)
		{
			//echo para("No subpages");
		}
		else
		{
			echo "Subpages: ";
			while($subPage = mysql_fetch_object($getSubPagesResult))
			//for($i=0; $subPage = mysql_fetch_object($getSubPagesResult); $i++)
			{
				//echo para("Level $entry->level");
				//echo para(htmlentities($entry->content));
				//render($entry);
				//echo ahref("$PHP_SELF?$subPage->page", substr($subPage->page, strrpos($subPage->page, "\")));
				//echo ahref("$PHP_SELF?$subPage->page", substr($subPage->page, strrpos($subPage->page, "\\") + 1));
				//$subPageArray[preg_replace("/^($page\\\)([^\\\]+)(\\\.*)$/U", "$1$2", $subPage->page)] = "";
				$subPageArray[preg_replace(addslashes("/^($page\)([^\]+)((\.*)*)$/U"), "$1$2", $subPage->page)] = "";
			}
			$i = 0;
			foreach($subPageArray as $sp => $rubbish)
			{
				if($i != 0)
					echo " | ";
				echo ahref("$PHP_SELF?$sp", getTopLevel($sp));
				$i++;
			}
		}
	}

	function getTopLevel($str)
	{
		return substr($str, strrpos($str, "\\") + 1);
	}

	function getLinks()
	{
		global $rwlMain;

		$getLinksSQL = "SELECT * FROM rwlEntry WHERE page='".addslashes("$rwlMain\Links")."' AND level=1;";
		//echo para($getLinksSQL);
		$getLinksResult = mysql_query($getLinksSQL);
		if(mysql_num_rows($getLinksResult) < 1)
		{
			echo para("No subpages");
		}
		else
		{
			while($entry = mysql_fetch_object($getLinksResult))
			{
				render($entry);
			}
		}
	}


?>

</body>
