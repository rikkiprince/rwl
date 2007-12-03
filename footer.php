<?php

	echo "<div id='footer'>";

	echo "<hr>";
	//echo para("&copy; July 2006".(date("Y")>2006)?date("Y"):"");
	$copy = "&copy; July 2006";
	if(date("Y")>2006)
		$copy = $copy."-".date("F")." ".date("Y");
	$copy = $copy." Rikki Prince, ECS";
	echo para($copy);
	echo para(	"Disclaimer:  As part of this page is publically editable, 
				no guarantee can be given to the validity of the information on this page.  
				Also, it must be made clear that it does not represent the views of the website owner".
				(isset($rwlWebsiteOwner)?" ($rwlWebsiteOwner)":"") 
				." or the website host".
				(isset($rwlWebsiteHost)?" ($rwlWebsiteHost)":"")
				.".");

	echo "</div>";
?>