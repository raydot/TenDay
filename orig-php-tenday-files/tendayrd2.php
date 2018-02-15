<HTML xml:lang="en" lang="en">
<HEAD>
<TITLE>10 Day</TITLE>
</HEAD>
<BODY>
<?php
$date = date("Y-m-d");
echo "Date: ".$date."<BR>";
//10011
if(!$xml = simplexml_load_file('http://xoap.weather.com/weather/local/10011?dayf=10&prod=xoap&par=1060864033&key=15847b56b7a9eb6e')){
	echo "Something failed!";
	}

$i = 1;
echo "New York, NY, ".$xml->dayf->lsup."<BR>";
foreach ($xml->dayf->day as $day){
$output = "Day ".$i++." Hi: ".$day->hi." Lo: ".$day->low."<BR>";
echo $output;
}

//60302
if(!$xml = simplexml_load_file('http://xoap.weather.com/weather/local/60302?dayf=10&prod=xoap&par=1060864033&key=15847b56b7a9eb6e')){
	echo "Something failed!";
	}

$i = 1;
echo "<P>Oak Park, IL, ".$xml->dayf->lsup."<BR>";
foreach ($xml->dayf->day as $day){
$output = "Day ".$i++." Hi: ".$day->hi." Lo: ".$day->low."<BR>";
echo $output;
}

//94116
if(!$xml = simplexml_load_file('http://xoap.weather.com/weather/local/94116?dayf=10&prod=xoap&par=1060864033&key=15847b56b7a9eb6e')){
	echo "Something failed!";
	}
$i = 1;
echo "<P>San Francisco, CA, ".$xml->dayf->lsup."<BR>";
foreach ($xml->dayf->day as $day){
$output = "Day ".$i++." Hi: ".$day->hi." Lo: ".$day->low."<BR>";
echo $output;
}

?>
</BODY>
</HTML>
