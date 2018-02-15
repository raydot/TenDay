<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">

<HEAD>
<TITLE>Ten Day Weather Watch</TITLE>
</HEAD>

<body bgcolor="#FFFFFF" text="#000000" link="#00CC33" alink="#00CC33" vlink="#00CC33">
<?php

//include headers to work with DB scripts
	include 'config.php';
	include 'opendb.php';
	
	//today!  THIS DOES NOT WORK PROPERLY BEFORE 7 AM!!!
	$dateStr = time();
	
	echo "<h3>10 Day Weather Forecasts for 10011 (New York, NY)</h3>";
	$query = "SELECT * FROM temps WHERE date >= '".date("Y-m-d", ($dateStr - (86400 * 9)))."' AND CITY = 1";
	$result = mysql_query($query);
	echo "<font size='-1'><em>Ten day forecasts for each date shown. \"Day 0\" is the weather for the date shown (\"today's weather\" for that date), \"Day 9\" is the prediction 9 days from the date shown.<BR>Updated daily at 7AM, Pacific Time.</em></font>";
	echo "<TABLE>";
	echo "<TR><TD>Date</TD><TD>Day 0</TD><TD>Day 1</TD><TD>Day 2</TD><TD>Day 3</TD><TD>Day 4</TD><TD>Day 5</TD><TD>Day 6</TD><TD>Day 7</TD><TD>Day 8</TD><TD>Day 9</TD></TR>";
	
	$highlighter = 0;
	$hrow = 9;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		echo "<TR><TD>".$row['date']."</TD>";
		for ($i = 0; $i <= 9; $i++) {
			$next = 'day'.$i.'hi';
			//echo "<TD>".$next."</TD>";
			if ((($highlighter++) % 10) == $hrow) { //highlight the diagonal
				echo "<TD align='center' bgcolor='#FFFFCC'>".$row[$next]."</TD>";
			} else {
				echo "<TD align='center'>".$row[$next]."</TD>";
			}
			//$highlighter++;
		}
		echo "</TR>";
		$hrow--;
	}
	echo "</TABLE>";
	mysql_free_result($result);
	
	//DOES IT MAKE MORE SENSE TO PULL ALL OF THIS DATA 
	//INTO ARRAYS AND THEN PARSE IT ALL OUT?!
	
	//DECLARE ARRAYS
	
	$temphilist = array();
	$templolist = array();
	
	echo "<P>Last Ten Days' forecast for date: ".date("Y-m-d", $dateStr)."</P>";
	echo "<TABLE><TR><TD>Days ago</TD>";
	for ($i = 9; $i >= 0; $i--) {
		//multiply $i by 86400, or the number of seconds in a day

		if ($i > 0)
			echo "<TD align='center'>".$i." days ago</TD>";
		else
			echo "<TD align='center'>today</TD>";
	}
	echo "</TR>";
	
	//HIGH TEMPS
	echo "<TR><TD>Predicted High</TD>";
	for ($i = 9; $i >= 0; $i--) {
		$next = 'day'.$i.'hi';
		$query = "SELECT ".$next." FROM temps WHERE DATE = '".date("Y-m-d", $dateStr - ($i*86400))."' AND CITY = 1";
		//echo $query;
		$result = mysql_query($query);
		//build in DB error checking
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$temptocons = $row[$next];
			echo "<TD align='center'>".$temptocons."</TD>";
			$temphilist[$i] = $temptocons;
		}
	}
	echo "</TR>";
	
	//LOW TEMPS
	echo "<TR><TD>Predicted Low</TD>";
	for ($i = 9; $i >= 0; $i--) {
		$next = 'day'.$i.'lo';
		$query = "SELECT ".$next." FROM temps WHERE DATE = '".date("Y-m-d", $dateStr - ($i*86400))."' AND CITY = 1";
		//echo $query;
		$result = mysql_query($query);
		//build in DB error checking
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$temptocons = $row[$next];
			echo "<TD align='center'>".$temptocons."</TD>";
			$templolist[$i] = $temptocons;
		}
	}
	echo "</TR></TABLE>";
	
	$lo = 200;
	$hi = 0;
	$sum = 0;
	$bigdif = 0;
	$dif = 0;
	//echo "<P>Test:".$temphilist[1]."</P>";
	for ($j = 1; $j < 10; $j++) {
		$compare = $temphilist[$j];
		if ($compare > $hi) {
			$hi = $compare;
		}
		if ($compare < $lo) {
			$lo = $compare;
		}
		$dif = ABS($temphilist[0] - $temphilist[$j]);
		//echo $dif. " ";
		if ($dif > $bigdif) {
			$bigdif = $dif;
		}
		$sum += $compare;
	}
	$avg = $sum / 9;
	$avgdif = ABS($temphilist[0] - $avg);
	
	//echo "<P>Size = ".count($temphilist)."</P>";
	//echo "<P>Sum = ".$sum."</P>";
	echo "<P>Stats (for high temps only):<BR>";
	echo "<P>Highest 10 day prediction = ".$hi."</P>";
	echo "<P>Lowest 10 day prediction = ".$lo."</P>";
	echo "<P>Average prediction over last 10 days = ";
	printf("%2.2f", ($sum/9));
	echo "</P>";
	echo "<P>Difference between average and today = ";
	printf("%2.2f", $avgdif);
	echo "&deg;</P>";
	echo "<P>Biggest difference between prediction and today = ".$bigdif."&deg;</P>";
	
	//select an interval of 7 days
	//SELECT CURDATE( ) , DATE_SUB( CURDATE( ) , INTERVAL 7 DAY ) ;
	//CHECK OUT THE MYSQL COOKBOOK!
	
	//NEED TO ADD CLOSE DB STUFF HERE?!
	mysql_free_result($result);
	mysql_close;
	
?>



</body>
</html>

