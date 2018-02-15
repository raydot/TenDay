<HTML xml:lang="en" lang="en">
<HEAD>
<TITLE>10 Day</TITLE>
</HEAD>
<BODY>
<?php
//Include headers to work with DB scripts
	include 'config.php';
	include 'opendb.php';

//Request Date
//$date = date("Y-m-d");
//echo "Date: ".$date."<BR>";

//SHOULD OPTIMIZE BY STORING CITY DATA IN ARRAY?

function outCity($c_name, $c_zip, $c_id){
	$url = 'http://xoap.weather.com/weather/local/'.$c_zip.'?dayf=10&prod=xoap&par=1060864033&key=15847b56b7a9eb6e';
	if(!$xml = simplexml_load_file($url)){
		echo 'Something failed with'.$c_zip.'!';
	}

	$i = 0;
	echo $c_name." ".$xml->dayf->lsup."<BR>";
	$hiarr = array();
	//$lowarr = 0;
	foreach ($xml->dayf->day as $hi){
		$hiarr[] = $hi->hi;
		//$hiarr[i++] = ($hi->hi);
		//$arrMast = array($arr);
		//$output = "Day ".$i++." Hi: ".$day->hi." Lo: ".$day->low."<BR>";
		//echo $output;
		//echo $hiarr."<BR>";
		
	}
	foreach ($hiarr as $out){
		echo $out;
		//echo $low;
		echo "x<BR>";
	}
	echo "<P>";

} //function end

outCity('New York, NY', '10011', 1);
outCity('Oak Park, IL', '60301', 2);
outCity('San Francisco, CA', '94116', 3);

?>
</BODY>
</HTML>
