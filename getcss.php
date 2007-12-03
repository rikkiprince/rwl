<?php

	$cssDBRoot = "^RWL\\Settings\\CSS\\";
	$cssMinLevel = 1;

	function getStyleSheets($default)
	{
		global $cssDBRoot, $cssMinLevel;
		global $rwlEntry;

		// should it bring together all levels, or just do the top one?
		//$getCSSSQL = "SELECT * FROM $rwlEntry WHERE $rwlEntry.page RLIKE '".addslashes(addslashes($cssDBRoot))."' ORDER BY $rwlEntry.level DESC;";
		$getCSSSQL = "SELECT * FROM $rwlEntry WHERE $rwlEntry.page RLIKE '".addslashes(addslashes($cssDBRoot))."' AND $rwlEntry.level>=$cssMinLevel ORDER BY $rwlEntry.level DESC;";
		$getCSSResult = mysql_query($getCSSSQL);
		if(mysql_num_rows($getCSSResult) < 1)
		{
			//echo ("No entries on this page yet!");
		}
		else
		{
			while($css = mysql_fetch_object($getCSSResult))		// each page can have numerous entries, hence var names
			{
				$tl = getTopLevel($css->page);
				if($tl == $default)
					$type = "stylesheet";
				else
					$type = "alternate stylesheet";
				echo "\t<link rel='$type' href='css.php?css=$tl' type='text/css' title='$tl'>\n";
			}
		}
	}

	function getCSSLinks($default="", $howManyAsLinks=0)
	{
		global $PHP_SELF, $pageName;
		global $cssDBRoot, $cssMinLevel;
		global $rwlEntry;

		$out = "Change style: ";

		$getCSSSQL = "SELECT * FROM $rwlEntry WHERE $rwlEntry.page RLIKE '".addslashes(addslashes($cssDBRoot))."' AND $rwlEntry.level>=$cssMinLevel ORDER BY $rwlEntry.level DESC;";
		$getCSSResult = mysql_query($getCSSSQL);
		if(mysql_num_rows($getCSSResult) < 1)
		{
			//echo ("No entries on this page yet!");
			return "";
		}
		else if(mysql_num_rows($getCSSResult) < $howManyAsLinks)
		{
			$i = 0;
			while($css = mysql_fetch_object($getCSSResult))		// each page can have numerous entries, hence var names
			{
				if($i != 0)
					$out = $out." | ";
				$tl = getTopLevel($css->page);
				if($tl == $default)
					$id = "defaultCSS";
				else
					$id = "";
				$out = $out.ahref("$PHP_SELF?action=setcss&css=$tl&pageName=$pageName", $tl, $id);

				$i++;
			}
		}
		else
		{
			$out = $out."<form action='$PHP_SELF?action=setcss&pageName=$pageName' method='get'>";
			$out = $out."<select name='css'>";
			while($css = mysql_fetch_object($getCSSResult))		// each page can have numerous entries, hence var names
			{
				$tl = getTopLevel($css->page);
				if($tl == $default)
					$selected = " selected='selected'";
				else
					$selected = "";
				$out = $out."<option$selected value='$tl'>$tl</option>";
			}
			$out = $out."<input type='hidden' name='action' value='setcss'><input type='hidden' name='pageName' value='$pageName'>";
			$out = $out."<input type='submit'></form>";
		}

		return $out;
	}


	function getCSS($cssToGet)
	{
		global $rwlEntry;
	
		$cssDBRoot = "RWL\\Settings\\CSS\\";

		$getCSSSQL = "SELECT * FROM $rwlEntry WHERE $rwlEntry.page RLIKE '".addslashes(addslashes("^".$cssDBRoot.$cssToGet."$"))."' AND $rwlEntry.level=1 ORDER BY $rwlEntry.level DESC;";
		$getCSSResult = mysql_query($getCSSSQL);
		if(mysql_num_rows($getCSSResult) < 1)
		{
			echo ("No entries on this page yet!");
		}
		else
		{
			while($css = mysql_fetch_object($getCSSResult))		// each page can have numerous entries, hence var names
			{
				//echo getTopLevel($css->page)."\n";
				echo $css->content;
			}
		}
	}

?>