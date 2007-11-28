<?php

	$str = 'hello "there "[nowiki]how are[/nowiki]" you?';

	$re = "/(?<=\[nowiki\])((.)*)(?=\[\/nowiki\])/";

	echo "<p>".preg_replace($re, "<u>$1</u>", $str)."</p>";

	echo "<p>".preg_replace("/(\[\/?nowiki\])/", "", $str)."</p>";

	echo "<p>".preg_replace("/\"(.*)\"/U", "$1", $str)."</p>";
?>