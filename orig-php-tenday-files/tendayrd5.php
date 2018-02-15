
<?php
//Include headers to work with DB scripts
	include 'config.php';
	include 'opendb.php';

//SHOULD OPTIMIZE BY STORING CITY DATA IN ARRAY?

//GRAB ALL DATA FROM THE FEED
//TAKES CITY NAME FOR DISPLAY
//TAKES CITY ZIP TO PULL WEATHER DATA
//TAKES CITY ID TO PASS TO DB
function outCity($c_name, $c_zip, $c_id, $dbv1){
  
  //"link" parameter added 
  $wcpar='1060864033';
  $wckey='15847b56b7a9eb6e';
  $url = 'http://xoap.weather.com/weather/local/'.$c_zip.'?dayf=10&link=xoap&prod=xoap&par='.$wcpar.'&key='.$wckey;
  echo $url;
	if(!$xml = simplexml_load_file($url)){
		echo 'Something failed with'.$c_zip.'!';
	}

	$i = 0;
	//echo $c_name." ".$xml->dayf->lsup."<BR>";
	$hiarr = array();
	$lowarr= array();
	//echo "$xml: ".$xml."<BR>";
	foreach ($xml->dayf->day as $temp){
		$hiarr[] = $temp->hi;
		$lowarr[] = $temp->low;
		//echo "temp:".$temp->hi."<BR>";
	}
	
	//put all together in one array
	$grandarr = array($c_id, $xml->dayf->lsup, $hiarr, $lowarr); 
	
	//now send wherever for output
	//riptoHtml($grandarr);  //disable in the version for cron
	riptoSql($grandarr, $dbv1);

	
	//echo "<P>";
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
	//echo $sql."<BR>";
	//echo $dbv."<BR>";
	$result = mysql_query($sql, $dbv);
	if(!$result) die("Query failed!  So sorry.");

	
	//mysql_free_result($result);
	mysql_close;		
	//end function
}

//Have to pass along the DB variable -- must be a better way?
//There is!  http://www.phpit.net/article/using-globals-php/
outCity('New York, NY', '10011', 1, $db);
outCity('Oak Park, IL', '60301', 2, $db);
outCity('San Francisco, CA', '94116', 3, $db);
outCity('Washington, DC', '20015', 4, $db);


//Now I send myself an e-mail...
/*$to = "raydot@gmail.com";
$from = 'From: Dave Kanter'."\r\n";
$subject = "Weather Pulled!";
$outdate = date("F j, Y, g:i a");
$body = "Weather data pulled at: ".$outdate."\n";
if (mail($to, $subject, $body, $from)) {
	echo("Message sent!");
} else {
	echo("Message failed...");
}*/

//For the cron job e-mail
$outdate = date("F j, Y, g:i a");
echo "Weather data pulled at ".$outdate;
?>
