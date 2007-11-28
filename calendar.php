<?php
	//include('../admin/db/mysql.inc');

	function printCalendar($pageName, $cm="", $cy="")
	{
		//$link = start_mysql();

		$today = getdate();

		/*if(!isset($cm)) $cm = $today['mon'];	//3;	//
		if(!isset($cy)) $cy = $today['year'];	//2006;	//
		*/
		if(!isset($cm) || !isset($cy))
		{
			//echo para("one of them is empty!");
			$pnvd = getValidDate($pageName);
			if($pnvd)
			{
				$vd = getdate($pnvd);
				if(!isset($cm)) $cm = $vd['mon'];	//3;	//
				if(!isset($cy)) $cy = $vd['year'];	//2006;	//
			}
			else
			{
				if(!isset($cm)) $cm = $today['mon'];	//3;	//
				if(!isset($cy)) $cy = $today['year'];	//2006;	//
			}
		}

		$dt = mktime(0,0,0, $cm, 1, $cy);	// create date from given month and year


		$dim = date("t", $dt);			// days in month


		//echo "<table border=1 width=100%>\n";		// in table
		echo "<table border=0 id='calendar'>\n";		// in table

		echo "<tr><td colspan=2 class='calendarMonth' id='previousMonth'><a href='".$PHP_SELF."?pageName=$pageName&cm=".($cm-1)."&cy=$cy'>&lt; ".date("M", mktime(0,0,0, $cm-1, 1, $cy))."</a> </td>";	// was F
		echo "<td colspan=3 class='calendarMonth' id='currentMonth'>".date("F Y", $dt)."</td>";			// print Month and Year
		echo "<td colspan=2 class='calendarMonth' id='nextMonth'> <a href='".$PHP_SELF."?pageName=$pageName&cm=".($cm+1)."&cy=$cy'>".date("M", mktime(0,0,0, $cm+1, 1, $cy))." &gt;</a></td></tr>";	// was F

		//echo "<br>\n";

		echo "<tr>";


		$cdt = $dt;
		$o = 1;					// offset to change start of week day
		$dow = $o;

		for($j=0; $j<7; $j++)			// print each day, Sunday -> Saturday
		{
			//$day = date("l", ($j+3+$o)*(60*60*24));	// long names
			$day = date("D", ($j+3+$o)*(60*60*24));		// short names
			echo "<th class='calendarDays' id='day$day' width=".(100/7)."%>$day</th>";
		}

		echo "</tr><tr>";

		//echo "<br>Day of week: $dow compared to: ".date("w", $cdt);

		// if the start day-of-week is after first-of-month, must modify the date by 7 days so the loop works properly!
		$modifier = ($dow > (date("w", $cdt))) ? 7 : 0;
		while($dow < (date("w", $cdt))+$modifier)		// indenting first line so date lines up with day of week
		{
			echo "<td></td>\n";
			//echo "Indenting a day";
			$dow++;
		}

		for($i=0; $i<$dim; $i++)
		{
			//echo "<br>";
			//echo date("D j M Y", $cdt);

			//echo "<td>".date("j", $cdt)."<br>";
			if(isToday($cdt) && isCurrentDatePage($cdt, $pageName))
				$cssID = "dayTodayBeingViewed";
			else if(isToday($cdt))
				$cssID = "dayToday";
			else if(isCurrentDatePage($cdt, $pageName))
				$cssID = "dayBeingViewed";
			else
				$cssID = "";

			// wrong, could be being viewed and has an entry
			/*if(isCurrentDatePage($cdt))
				$cssClass = " calendarBeingViewed";		// I'm not happy with this being a class, but it would be awkward if I made it an id...
			else */
			if(hasEntry($cdt))
				$cssClass = " calendarHasEntry";
			else
				$cssClass = " calendarEmptyDay";

			//echo "<td class='' id='$cssID'>";
			
			echo td(ahref($PHP_SELF."?".date("Ymd", $cdt), date("j", $cdt)), $cssID, "calendarDay$cssClass");

			// print out stuff happening on this day
			//printDaysEvents($cdt, $link);

			//echo "</td>\n";

			$dow++;
			if($dow > 6+$o)			// move to next line if end of week
			{
				//echo "<hr>";
				echo "</tr><tr>";
				$dow = $o;
			}

			$cdt += (60*60*24);	// add one day by (secs x mins x hrs)
		}
		echo "</tr>";

		echo "<tr><td colspan=3><a href='$PHP_SELF?pageName=$pageName&cm=".date("n")."&cy=".date("Y")."'>This Month</a></td><td colspan=1></td><td align=right colspan=3><a href='$PHP_SELF?".date("Ymd")."'>Today</a></td></tr>";

		echo "</table>\n";


		//end_mysql($link);
	}



	function isToday($cdt)
	{
		return date("Ymd", $cdt) == date("Ymd");
	}
	function isYesterday($cdt)
	{
		$nd = getdate();
		return date("Ymd", $cdt) == date("Ymd", mktime(0,0,0, $nd["mon"], $nd["mday"]-1, $nd["year"]));
	}
	function isTomorrow($cdt)
	{
		$nd = getdate();
		return date("Ymd", $cdt) == date("Ymd", mktime(0,0,0, $nd["mon"], $nd["mday"]+1, $nd["year"]));
	}

	function getRelativeDay($cdt)
	{
		if(isToday($cdt))
			return "Today";
		else if(isYesterday($cdt))
			return "Yesterday";
		else if(isTomorrow($cdt))
			return "Tomorrow";
		else
			return null;

		// add some more, like 'Next Monday', done by checking if within 7 days, and adding string for day of week
	}


	function printDaysEvents($cdt, $link)
	{
		//echo "Hello";
		$sql = "SELECT title, dt, TIME(dt) AS t FROM kendocal WHERE DATE(dt) = '".date("Y-m-d", $cdt)."';";
		//echo "<br>$sql";
		$r = mysql_query($sql, $link);
		if($r)
		{
			//echo "<br>r: "+$r;
			for($i=0; $i<mysql_num_rows($r); $i++)
			{
				$a = mysql_fetch_assoc($r);
				echo "<br>".date("g:ia: ", strtotime($a['t'])).$a['title'];
			}
		}
	}

	function isCurrentDatePage($cdt, $pageName)
	{
		return false;
	}

	function hasEntry($cdt)
	{
		return false;
	}
?>
