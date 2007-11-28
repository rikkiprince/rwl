<head><title>Changing...</title></head>
<body>

<?php
	include("./diff_patch.php");

	echo "\n<br>previously: <pre>".$previous."</pre>";
	echo "\n<br>became: <pre>".$text."</pre>";

	$diff_arr = diff($previous, $text);

	foreach($diff_arr as $d)
	{
		echo "\n<br>$d";
	}

	//echo "<br>patched: <pre>".patch($previous,$diff_arr)."</pre>";

	//echo "<br>unpatched: <pre>".unpatch($text,$diff_arr)."</pre>";

	echo "\n<br>";
	$a = var_export($diff_arr, TRUE);

	echo $a;

	eval("\$new_arr = $a;");

	print_r($new_arr);

	foreach($new_arr as $d)
	{
		echo "\n<br>$d";
	}
?>

<form method="post" action="<?php $PHP_SELF; ?>">
	<textarea name="text" rows="10" cols="50"><?php echo $text; ?></textarea>
	<input type="hidden" name="previous" value="<?php echo htmlspecialchars($text); ?>">
	<br>
	<input type="submit">
</form>

</body>
