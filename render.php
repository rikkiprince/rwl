<?php

	function para($str, $id="", $class="")
	{
		$classString = ($class=="")?"":" class='$class'";
		$idString = ($id=="")?"":" id='$id'";
		return "<p".$classString.$idString.">$str</p>\n";
	}

	// should make external links open in new window...
	function ahref($uri, $name="", $id="", $class="", $rel="")
	{
		$classString = ($class=="")?"":" class='$class'";
		$idString = ($id=="")?"":" id='$id'";
		$relString = ($rel=="")?"":" rel='$rel'";
		$name = str_replace("\'", "'", $name);
		if($name==="")
			$name = $uri;
		return "<a".$classString.$idString.$relString." href='$uri'>".$name."</a>";
	}

	function h($str,$l)
	{
		return "<h$l>$str</h$l>";
	}

	function img($im, $snl = false)		// snl = shrink 'n' link - nice eh?
	{
		if($snl)
			return ahref($im, "<img class='shrinknlink' src='$im'>", "", "shrinknlink");
		else
			return "<img src='$im'>";
	}

	function bold($str)
	{
		return "<b>$str</b>";
	}

	function hr()
	{
		return "<hr>";
	}

	function td($str, $id="", $class="")
	{
		$classString = ($class=="")?"":" class='$class'";
		$idString = ($id=="")?"":" id='$id'";
		return "<td".$classString.$idString.">$str</td>\n";
	}

	function flv($url)
	{
		$out = "<object type=\"application/x-shockwave-flash\" width=\"320\" height=\"260\" wmode=\"transparent\" data=\"flvplayer.swf?file=$url\">";
		$out = $out."<param name=\"movie\" value=\"flvplayer.swf?file=$url\" />";
		$out = $out."<param name=\"wmode\" value=\"transparent\" />";
		$out = $out."<object/>";

		return $out;
	}


	/*
	 * Processes the entry (which arrive as an object from the database) and changes markup into HTML
	 * and finds CamelCase instances and converts them into links.
	 */
	function render($entry)
	{
		$reNL = getNewLineRegex();

		//echo h("Level $entry->level",3);

		if($entry->content == "")
			$entry->content = "This page has been left intentionally blank.";

		// GBP sign
		$entry->content = preg_replace("/Â£/", "£", $entry->content);	// GBP

		// Remove real HTML!
		$midRender = htmlentities($entry->content);
		//$midRender = $entry->content;

		//$reExclusions = "/(?<=\[nowiki\])((.|$reNL)*)(?=\[\/nowiki\])/";
		/*$reExclusions = "/(\[nowiki\](?!\[\/nowiki\])$reNL(?!\[\/nowiki\])\[\/nowiki\])/";


		//echo renderProcess($midRender);

		$arr = preg_split($reExclusions, $midRender, -1, PREG_SPLIT_DELIM_CAPTURE);
		//echo preg_replace($reExclusions, '<u>$1</u>', $midRender);

		//print_r($arr);

		for($i=0; $i<count($arr); $i++)
		{
			if(preg_match($reExclusions, $arr[$i])>0)
			{
				echo $arr[$i];
				$arr[$i] = preg_replace("/(\[(\/?)nowiki\])/", "<$2p>", $arr[$i]);
			}
			else
				$arr[$i] = renderBlocks($arr[$i]);
		}
		$midRender = implode($arr);*/

		//preg_replace("/<p><br></p>/"

		//$midRender = renderProcess($midRender);

		$midRender = renderBlocks($midRender);
		$midRender = renderFormat($midRender);


		$midRender = tidyHTMLSource($midRender);

		echo "<div class='level' id='level$entry->level'>".$midRender."</div>";

		// newline test
		//echo para(preg_replace("/$reNL/e", '"[NEWLINE]"', $entry->content));
		//echo para(preg_replace("/\n/",'[N]', preg_replace("/\r/",'[R]', $entry->content)));
	}

	function tidyHTMLSource($midRender)
	{
		$reNL = getNewLineRegex();

		$midRender = preg_replace("/((<p.*>)|(<ul.*>)|(<li.*>))/Ue", '"\r\n\r\n$1"', $midRender);

		return $midRender;
	}

	function renderBlocks($midProcess)
	{
		$reNL = getNewLineRegex();

		$midProcess = findTable($midProcess);

		// list
		//echo para(preg_replace("/(^|$reNL)((\*)(.*($reNL)\*.*)*)($|$reNL)/e", '"[LIST]".findList("$2",2)."[/LIST]"', $entry->content));
		$midProcess = findList($midProcess,"\*","u",1);
		$midProcess = findList($midProcess,"-","u",1);
		$midProcess = findList($midProcess,"#","o",1);

		$midProcess = findPre($midProcess);

		return $midProcess;
	}

	function renderFormat($midProcess)
	{
		$reNL = getNewLineRegex();
		$imageDir = ".";

		// Headings - need to FIX - how?
		$midProcess = preg_replace("/(=+) ([A-Za-z 0-9_\-\.\(\)]+) (=+)($reNL)?/e", 'h("$2", strlen("$1"))', $midProcess);			// change so it works for more text inside
		// 6 heading levels use ^ ... ^ as normal, use ====== ... ====== inversely?

		// Turn CamelCase into links
		$midProcess = preg_replace('/(?<= )(([A-Z][a-z0-9]+){2,})(?=[ ,.])/e', '"".ahref("$PHP_SELF?$1","$1")', $midProcess);		// must match at least 2 words or capital letters for it to be CamelCase
		// Do other link stuff here?
		//$midProcess = preg_replace('/http:\/\/
		//echo preg_replace("/(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/", "$1", $midProcess);
		//echo preg_replace("/(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)/", "<u>$1 $2 $3 $4 $5 $6 $7 $8</u>", $midProcess);
		//$midProcess = preg_replace("/(http:\/\/[A-Za-z0-9.\/_]*\.(gif|jpeg|jpg|png))/", "<img src='$1'>", $midProcess);
		$reURI = "([A-Za-z0-9]*:\/\/[A-Za-z0-9.\/_\-\#;~?=%&+:]*)";

		// LINKs in brackets are associated with the previous word	- good idea?
		//$midProcess = preg_replace("/(([[:word:]]|-)+) \(($reURI)\)/e", 'renderLink("$3", "$1")', $midProcess);
		$midProcess = preg_replace("/((^|[^ \t($reNL)>])+)([ \t]+)\(($reURI)\)/e", 'renderLink("$5", "$1")', $midProcess);

		// LINKs after colons are associated with previous sentence	- good idea?
//		$midProcess = preg_replace("/([[:punct:]] {1,2})(.*)([[:punct:]]*): ($reURI)/e", '"$1".renderLink("$4", "$2")."$3"', $midProcess);

		// Normal LINKs
		//$midProcess = preg_replace("/([($reNL) >]|^)$reURI([($reNL) $<])/e", '"$1".renderLink("$2")."$3"', $midProcess);
		$midProcess = preg_replace("/(^|($reNL)| |>)$reURI($|($reNL)| |<)/e", '"$1".renderLink("$3")."$4"', $midProcess);

		// Anything inside [[]] will be a link...
		$midProcess = preg_replace("/\[\[(.*)\]\]/Ue", 'ahref("$PHP_SELF?$1","$1")', $midProcess);


		// ACRONYM
		$midProcess = preg_replace("/([A-Z\-]{2,}) \((.*)\)/U", "<acronym title='$2'>$1</acronym>", $midProcess);

		// Horizontal rule
		$midProcess = preg_replace('/-{4,}/e', '"<hr>"', $midProcess);

		// emphasis - should this be bold or italic? *ABC* will it clash with lists?
		$midProcess = preg_replace('/\*(.*)\*/Ue', '"<b>$1</b>"', $midProcess);

		// italic - this is a good idea, I like this one :)
		$midProcess = preg_replace('/ \/([A-Za-z!]+)\/ /e', '"<i>$1</i>"', $midProcess);
		//$midProcess = preg_replace('/\'\'([A-Za-z!]+)\'\'/e', '"<i>$1</i>"', $midProcess);

		// underline
		$midProcess = preg_replace('/ _([A-Za-z]+)_ /Ue', '"<u>$1</u>"', $midProcess);

		// paragraph and new lines!
		$midProcess = preg_replace("/($reNL){2,}/e", '"</p><p>"', $midProcess);
		$midProcess = preg_replace("/$reNL/e", '"<br>"', $midProcess);

		// smilies!
		$midProcess = preg_replace("/( |^):-?\)( |$)/e", '"$1".img("$imageDir/smilie.png")."$2"', $midProcess);	// smile
		$midProcess = preg_replace("/( |^):-?\((<| |$|$reNL)/e", '"$1".img("$imageDir/sadie.png")."$2"', $midProcess);	// sadie
		$midProcess = preg_replace("/(>| |^|$reNL):'-?\((<| |$|$reNL)/e", '"$1".img("$imageDir/cryie.png")."$2"', $midProcess);	// cryie
		$midProcess = preg_replace("/( |^):-?(P|p)(<| |$|$reNL)/e", '"$1".img("$imageDir/toungie.png")."$3"', $midProcess);	// toungie
		$midProcess = preg_replace("/(>| |^|$reNL);-?\)(<| |$|$reNL)/e", '"$1".img("$imageDir/winkie.png")."$2"', $midProcess);	// winkie
		$midProcess = preg_replace("/(>| |^|$reNL):-?S(<| |$|$reNL)/e", '"$1".img("$imageDir/confusie.png")."$2"', $midProcess);	// confusie
		$midProcess = preg_replace("/(>| |^|$reNL):-?(o|O)(<| |$|$reNL)/e", '"$1".img("$imageDir/shockie.png")."$3"', $midProcess);	// shockie

		$midProcess = preg_replace("/(\(R\))/", "&reg;", $midProcess);		// registered trademark

		$midProcess = "<p>$midProcess</p>";

		return $midProcess;
	}

	function renderLink($link, $text="")
	{
		if(preg_match("/\.(gif|jpeg|jpg|png)$/",$link)>0)
			return img($link, true);
		else if(preg_match("/\.(flv)$/", $link)>0)
			return flv($link);
		else
			return ahref($link, $text);
	}

	function findList($str, $point="*", $type="u", $level=1)
	{
		if(empty($str))
			return;

		//$point = preg_quote($point);

		$reNL = getNewLineRegex();

		$reList = "/(^|$reNL)(".$point."{".$level.",} .*(($reNL)".$point."{".$level.",} .*)*)($|$reNL)/e";
		//$re2 = "/(\*{".($level-1)."})(.*)([^*][*][^*]|$|$reNL|\[)/e";
		//$re2 = "/(\*{".($level-1)."})(.*)($|$reNL)/";
		//$re2 = "/(\*{".($level-1)."})(.*)($reNL)/";
		//$re2 = "/(\*{".($level-1)."})((.*)($|$reNL))/";
		$reListItem = "/(^|$reNL)(".$point."{".($level-1)."})/e";

		/*echo para($re);
		echo para($re2);
		echo para($str);*/

		//return preg_replace('/(\n|^)((\*){2}(.*\n\*{2}.*)*)(\n|$)/e', '"[LIST $level]$2[/LIST]"', $str);
		//return preg_replace("/(^|$reNL)(\*{".$level."})(.*)($|$reNL)/e", '"[LIST $level]".findList("$3",$level+1)."[/LIST]"', $str);

		//$temp = preg_replace($re, '"[LIST $level]".findList("$2",$level+1)."[/LIST $level]\r\n"', $str);
		//backup $currList = preg_replace($reList, '"<".$type."l>".findList("$2", $point, $type, $level+1)."</".$type."l>\r\n"', $str);
		//echo para("'< ".$type."l>'.findList('$2', '$point', '$type', ".($level+1).").'< /".$type."l>\r\n'");

		// WHICH IS RIGHT?
		// THIS ONE PUTS A NEW LINE AFTER EACH LIST (THERE USED TO BE A GOOD REASON FOR THIS!)
		$currList = preg_replace($reList, "'<".$type."l>'.findList('$2', '$point', '$type', ".($level+1).").'</".$type."l>\r\n'", $str);
		// THIS ONE REMOVES THE NEW LINE
		//$currList = preg_replace($reList, "'<".$type."l>'.findList('$2', '$point', '$type', ".($level+1).").'</".$type."l>'", $str);
		// FIRST ONE ALWAYS WORKS, SECOND SOMETIMES BREAKS, BUT AVOIDS SPARE NEW LINE AT END...
		//echo para("ARGH: $temp level: $level");
		if($level>1)
		{
			//$temp = preg_replace($re2, "[ELEMENT $level]$2[/ELEMENT $level]\n", $temp);
			$arr = preg_split($reListItem, $currList, -1, PREG_SPLIT_NO_EMPTY);
			//$temp = "[ELEMENT ".($level-1)."]".implode("[/ELEMENT ".($level-1)."][ELEMENT ".($level-1)."]", $arr)."[/ELEMENT ".($level-1)."]";
			$currList = "<li>".implode("</li><li>", $arr)."</li>";
		}
		//$temp = preg_replace($re2, "[ELEMENT $level]$3[/ELEMENT $level]", $temp);
		return $currList;
	}

	function findTable($str)
	{
		$reNL = getNewLineRegex();

		//$reTable = "/$reNL((\|\|)+(.|$reNL)*(\|\|)+)$reNL/e";
		$reTable = "/(^|$reNL)((\|\|)+((.+$reNL.*))*(\|\|)+)($|$reNL)/e";

		return preg_replace($reTable, "'\r\n<table border=1>'.findTableRows('$2').'</table>'", $str);
	}

	function findTableRows($str)
	{
		$reNL = getNewLineRegex();

		$currRows = $str;

		/*$currRows = preg_replace("/(^|$reNL)((\|\|)+)/e",'"$1[d ".(strlen("$2")/2)."]"', $currRows);
		$currRows = preg_replace("/(\|\|)($|$reNL)/e",'"[/d]$2"', $currRows);
		$currRows = preg_replace("/((\|\|)+)/e",'"[/d][d ".(strlen("$1")/2)."]"', $currRows);
		$currRows = preg_replace("/$reNL/","[/r][r]", $currRows);*/

		$currRows = preg_replace("/(^|$reNL)((\|\|)+)/e","'$1<td colspan='.(strlen('$2')/2).'>'", $currRows);
		$currRows = preg_replace("/(\|\|)($|$reNL)/e","'</td>$2'", $currRows);
		$currRows = preg_replace("/((\|\|)+)/e","'</td><td colspan='.(strlen('$1')/2).'>'", $currRows);
		$currRows = preg_replace("/$reNL/",'</tr><tr>', $currRows);

		$currRows = "<tr>".$currRows."</tr>";

		return $currRows;
	}

	function findPre($str)
	{
		$reNL = getNewLineRegex();

		$str = preg_replace("/(((^|$reNL)( +)(.*))+)((($reNL)([^ ]))|$)/", "<pre>$1</pre>$9", $str);

		return $str;
	}

	function getNewLineRegex()
	{
		$reNL = '\r\n';		//|\n';	//'(\r\n|\n\r|\r|\n)';

		return $reNL;
	}

?>