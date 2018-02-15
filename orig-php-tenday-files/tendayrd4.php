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

//GRAB ALL DATA FROM THE FEED
//TAKES CITY NAME FOR DISPLAY
//TAKES CITY ZIP TO PULL WEATHER DATA
//TAKES CITY ID TO PASS TO DB
function outCity($c_name, $c_zip, $c_id, $dbv1){
	$url = 'http://xoap.weather.com/weather/local/'.$c_zip.'?dayf=10&prod=xoap&par=1060864033&key=15847b56b7a9eb6e';
	if(!$xml = simplexml_load_file($url)){
		echo 'Something failed with'.$c_zip.'!';
	}

	$i = 0;
	echo $c_name." ".$xml->dayf->lsup."<BR>";
	$hiarr = array();
	$lowarr= array();
	foreach ($xml->dayf->day as $temp){
		$hiarr[] = $temp->hi;
		$lowarr[] = $temp->low;
	}
	
	//put all together in one array
	$grandarr = array($c_id, $xml->dayf->lsup, $hiarr, $lowarr); 
	
	//now send wherever for output
	riptoHtml($grandarr);
	riptoSql($grandarr, $dbv1);

	
	echo "<P>";
} //function end

//DISPLAY DATA TO HTML PAGE
function ripToHtml($masterArray) {
	echo "city id: ".$masterArray[0]."<BR>";
  	echo "date info: ".$masterArray[1]."<BR>";
  	echo "hi temps: <BR>";
  	//I have to offset to get the dates to work, which I don't get.
  	$i = -1;
  	foreach ($masterArray[2] as $hi){
  		
  		$datedist = mktime(0, 0, 0, 0, date("d") + $i++, 0);
  		if ($i == 0) {
  			$outstr = "Today";
  		}else if ($i == 1) {
  			$outstr = "Tomorrow";
  		}else{
  			$outstr = date("D", $datedist);
  		}
  		echo $outstr." ".$hi."<br>";
  		//$i++;
  	}
  	
  	echo "low temps: <BR>";
  	foreach ($masterArray[3] as $lo)
  		echo $lo."<br>";
} //function end

function riptoSql($masterArray, $dbv){
	//generate sql request
	$sql = 'INSERT INTO `kanterro_tendaydb`.`temps` (`id`, `city`, `date`, `day0hi`, `day1hi`, `day2hi`, `day3hi`, `day4hi`, `day5hi`, `day6hi`, `day7hi`, `day8hi`, `day9hi`, `day0lo`, `day1lo`, `day2lo`, `day3lo`, `day4lo`, `day5lo`, `day6lo`, `day7lo`, `day8lo`, `day9lo`) VALUES (NULL';

	$sql .= ','.$masterArray[0];
	$sql .= ',\''.date("Y-m-d").'\'';
	
	foreach($masterArray[2] as $hi) 
		$sql .= ','.$hi;
		
	foreach($masterArray[3] as $lo)
		$sql .= ','.$lo;
		
	$sql .= ");";
	
	//rip to db
	//$sql = "Select * from `temps`;";
	//echo "<P>".$sql."</P>";
	$result = mysql_query($sql, $dbv);
	if(!$result) die("Query failed!  So sorry.");

	
		
	//end function
}

//Have to pass along the DB variable -- must be a better way?
outCity('New York, NY', '10011', 1, $db);
outCity('Oak Park, IL', '60301', 2, $db);
outCity('San Francisco, CA', '94116', 3, $db);

	//$tomorrow = mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
	//echo "<BR>Tomorrow is ".date("D,Y/m/d", $tomorrow);

?>
<P>
$sql = 'INSERT INTO `kanterro_tendaydb`.`temps` (`id`, `city`, `date`, `day0hi`, `day1hi`, `day2hi`, `day3hi`, `day4hi`, `day5hi`, `day6hi`, `day7hi`, `day8hi`, `day9hi`, `day0lo`, `day1lo`, `day2lo`, `day3lo`, `day4lo`, `day5lo`, `day6lo`, `day7lo`, `day8lo`, `day9lo`) VALUES (NULL, \'1\', \'2008-04-09\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\', \'65\');';

</BODY>
</HTML>
