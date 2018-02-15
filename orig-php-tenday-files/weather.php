<?php

// CONFIGURATION 
$db_host = "";
$db_user = "";
$db_pwd = "";
$db_name = "";
$partner_id = ;
$key = ;
$unit = "s";	// s=standard, m=metric

$input = urlencode($_REQUEST['q']);
$length = $_REQUEST['d'];
$remember = $_REQUEST['remember'];
$input_type = ((strlen($input) == 5 And preg_match("/[0-9][0-9][0-9][0-9][0-9]/",$input)) Or (strlen($input) == 8 And preg_match("/[A-Z][A-Z][A-Z][A-Z][0-9][0-9][0-9][0-9]/",$input))) ? "code":"city"; // code can be a zip code or weather.com city code
$url_search = "http://xoap.weather.com/search/search?where=$input";
$url_forecast = "http://xoap.weather.com/weather/local/$input?cc=*&dayf=$length&unit=$unit&prod=xoap&par=$partner_id&key=$key";

$url = ($input_type==city) ? $url_search:$url_forecast;

$timestamp = time(); // current timestamp
$xml_url = md5($url);
$interval = 2;	// Hours to keep data in db before being considered old
$expires = $interval*60*60;
$expired_timestamp = $timestamp - $expires;

$connection = mysql_connect($db_host, $db_user, $db_pwd) or die("Could not connect");
mysql_select_db($db_name) or die("Could not select database");

// Delete expired records
$query = "DELETE FROM weather_xml WHERE last_updated <= '$expired_timestamp'";
$result = mysql_query($query) or die('Invalid query: ' . mysql_error());

$query = "SELECT * FROM weather_xml WHERE xml_url = '$xml_url'"; 
$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
$row = mysql_fetch_array($result);

if (mysql_num_rows($result) < 1) // Data not in table - Add from weather.com.
{
	// Get XML Query Results from Weather.com
	$fp = @fopen($url,"r");
	if(is_resource($fp))
	{
		while (!feof ($fp)) $xml .= fgets($fp, 4096);
		fclose ($fp);
	}
	else
	{
		// error - unable to fopen url
		exit("Unable to connect to weather.com");
	}

	$query = "INSERT INTO weather_xml VALUES ('$xml_url', '$xml', '$timestamp')";
	$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
}
else // Data in table, and it is within expiration period - do not load from weather.com, use cached copy instead.
{
	$xml = $row['xml_data'];
}

$parser = xml_parser_create(); 
xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
xml_parse_into_struct($parser,$xml,$values,$index); 
xml_parser_free($parser);

//echo $xml; // outputs the xml from weather.com
//print_r($values);
//print_r($index);

function getWeatherData($index,$values)
{
	global $length;
	// possible values are 'weather','search, and 'error'
	switch ($values[0]['tag'])
	{
		case "weather":		
			$return_array['type'] = "weather";

			$return_array[unit]['temp'] = $values[$index['ut'][0]]['value'];
			$return_array[unit]['dist'] = $values[$index['ud'][0]]['value'];
			$return_array[unit]['speed'] = $values[$index['us'][0]]['value'];
			$return_array[unit]['precip'] = $values[$index['up'][0]]['value'];
			$return_array[unit]['pressure'] = $values[$index['ur'][0]]['value'];

			$return_array[loc_info]['name'] = $values[$index['dnam'][0]]['value'];
			$return_array[loc_info]['time'] = $values[$index['tm'][0]]['value'];
			$return_array[loc_info]['lat'] = $values[$index['lat'][0]]['value'];
			$return_array[loc_info]['long'] = $values[$index['lon'][0]]['value'];
			$return_array[loc_info]['time_zone'] = $values[$index['zone'][0]]['value'];
		
			$return_array[cc]['feels_like'] = $values[$index['flik'][0]]['value'];
			$return_array[cc]['last_update'] = $values[$index['lsup'][0]]['value'];
			$return_array[cc]['temp'] = $values[$index['tmp'][0]]['value'];
			$return_array[cc]['text'] = $values[$index['t'][0]]['value'];
			$return_array[cc]['observation_station'] = $values[$index['obst'][0]]['value'];
			$return_array[cc]['icon'] = $values[$index['icon'][0]]['value'];
			$return_array[cc]['humidity'] = $values[$index['hmid'][0]]['value'];
			$return_array[cc]['visibility'] = $values[$index['vis'][0]]['value'];
			$return_array[cc]['dew_point'] = $values[$index['dewp'][0]]['value'];
			$return_array[cc]['uv_index'] = $values[$index['i'][0]]['value'];
			$return_array[cc]['uv_text'] = $values[$index['t'][2]]['value'];
			$return_array[cc]['moon_icon'] = $values[$index['icon'][1]]['value'];
			$return_array[cc]['moon_text'] = $values[$index['t'][3]]['value'];
			$return_array[cc]['uv_index'] = $values[$index['i'][0]]['value'];
			$return_array[cc]['wind_speed'] = $values[$index['s'][0]]['value'];
			$return_array[cc]['wind_gust'] = $values[$index['gust'][0]]['value'];
			$return_array[cc]['wind_direction'] = $values[$index['d'][1]]['value'];
			$return_array[cc]['wind_text'] = $values[$index['t'][1]]['value'];
			$return_array[cc]['barometer'] = $values[$index['r'][0]]['value'];
			$return_array[cc]['barometer_dir'] = $values[$index['d'][0]]['value'];

			$counter = 0;
			if (array_key_exists("day",$index))
			{
				foreach ($index['day'] as $day)
				{
					if ($values[$day]['attributes']['t'] != "")
					{
						$day_text = (($counter + 1) * 3) + $counter + 1;  // 4,8,12,...
						$day_wind = ((($counter + 1) * 3) + $counter) + 2; // 5,9,13,...
						$day_windspeed = (($counter + 1) * 2) - 1; // 1,3,5,...
						$day_windgust = (($counter + 1) * 2) - 1; // 1,3,5,...
						$day_winddir = ($counter + 1) * 2; // 2,4,6,...
						$day_humidity = (($counter + 1) * 2) - 1; // 1,3,5,...
						$day_precip = $counter * 2; // 0,2,4,...
						$day_icon = ($counter + 1) * 2; // 2,4,6,...

						$night_text = ((($counter + 1) * 3) + $counter) + 3; // 6,10,14,...
						$night_wind = ((($counter + 1) * 3) + $counter) + 4; // 7,11,15,...
						$night_windspeed = ($counter + 1) * 2; // 2,4,6,...
						$night_windgust = ($counter + 1) * 2; // 2,4,6,...
						$night_winddir = (($counter + 1) * 2) + 1; // 3,5,7,...
						$night_humidity = ($counter + 1) * 2; // 2,4,6,...
						$night_precip = ($counter * 2) + 1; // 1,3,5,...
						$night_icon = (($counter + 1) * 2) + 1; // 3,5,7,...

						$return_array['day'][$counter]['date'] = $values[$day]['attributes']['dt'];
						$return_array['day'][$counter]['day'] = $values[$day]['attributes']['t'];
						$return_array['day'][$counter]['hi'] = $values[$index['hi'][$counter]]['value'];
						$return_array['day'][$counter]['low'] = $values[$index['low'][$counter]]['value'];
						$return_array['day'][$counter]['sunrise'] = $values[$index['sunr'][$counter+1]]['value'];
						$return_array['day'][$counter]['sunset'] = $values[$index['suns'][$counter+1]]['value'];
						$return_array['day'][$counter]['day_text'] = $values[$index[t][$day_text]][value];
						$return_array['day'][$counter]['day_wind'] = $values[$index[t][$day_wind]][value];
						$return_array['day'][$counter]['day_windspeed'] = $values[$index[s][$day_windspeed]][value];
						$return_array['day'][$counter]['day_windgust'] = $values[$index[gust][$day_windgust]][value];
						$return_array['day'][$counter]['day_winddir'] = $values[$index[d][$day_winddir]][value];
						$return_array['day'][$counter]['day_humid'] = $values[$index[hmid][$day_humidity]][value];
						$return_array['day'][$counter]['day_pct_precip'] = $values[$index[ppcp][$day_precip]][value];
						$return_array['day'][$counter]['day_icon'] = $values[$index[icon][$day_icon]][value];
						$return_array['day'][$counter]['night_text'] = $values[$index[t][$night_text]][value];
						$return_array['day'][$counter]['night_wind'] = $values[$index[t][$night_wind]][value];
						$return_array['day'][$counter]['night_windspeed'] = $values[$index[s][$night_windspeed]][value];
						$return_array['day'][$counter]['night_windgust'] = $values[$index[gust][$night_windgust]][value];
						$return_array['day'][$counter]['night_winddir'] = $values[$index[d][$night_winddir]][value];
						$return_array['day'][$counter]['night_humid'] = $values[$index[hmid][$night_humidity]][value];
						$return_array['day'][$counter]['night_pct_precip'] = $values[$index[ppcp][$night_precip]][value];
						$return_array['day'][$counter]['night_icon'] = $values[$index[icon][$night_icon]][value];
						$counter++;
					}
				}
			}
			break;
		case "search":
			if(array_key_exists('loc',$index))
			{
				if (count($index[loc]) == 1) header("Location:".$_SERVER[PHP_SELF]."?q=".$values[$index['loc'][0]]['attributes']['id']."&d=".$length); // if one city is returned for a search, don't draw select drop down menu, get weather data
				$return_array['type'] = "search";
				$search_count = 0;
				foreach($index['loc'] as $valkey)
				{
					$return_array[$search_count]['city'] = $values[$valkey]['value'];
					$return_array[$search_count]['locid'] = $values[$valkey]['attributes']['id'];
					$search_count++;
				}
			}
			else
			{
				$return_array['type'] = "error";
				$return_array[]['error'] = "no locations found";
			}
			break;
		case "error":
			$return_array['type'] = "error";
			$return_array[]['error'] = $values[1]['value'];;
			break;
	}
	//print_r($return_array);
	return $return_array;
}

if ($values[0]['tag'] == "weather" And $_REQUEST['remember']) setcookie("wdc", $input, strtotime("+1 week")); // if we have a valid weather forecast call (AND THE USER CHECKED REMEMBER) set a cookie
header("Content-Type:text/xml"); // uncomment when returning xml

$weather_array = getWeatherData($index,$values);

if($weather_array[type] == "error")
{
	echo $weather_array[0]['error'];
}

if($weather_array[type] == "search")
{
	echo "<select name=\"cities\" onchange=\"getModule(this.options[this.selectedIndex].value,0);theForm.elements['zip'].value=this.options[this.selectedIndex].value\">\n";
	echo "<option value=\"00000\">Select a city</option>\n";
	for($i=0;$i<=count($weather_array)-2;$i++)
	{
		echo "<option value=\"" . $weather_array[$i][locid] . "\">" . $weather_array[$i][city] . "</option>\n";
	}
	echo "</select>\n";
}

if($weather_array[type] == "weather")
{
	$system = $weather_array['unit']['temp'];
	$barometer = $weather_array['unit']['pressure'];
	$response = '';

	$response = "<table>\n";
	$response .= "<tr><td colspan=\"2\" class=\"loc_name\"> Currently in " . $weather_array['loc_info']['name'] . "</td></tr>\n";
	$response .= "<tr><td class=\"loc_text\"><img src=\"/images/weather/128x128/" .$weather_array['cc']['icon'] . ".png\" /><br />" . $weather_array['cc']['text'] . "</td><td valign=\"top\">";
	$response .= "<strong>" .$weather_array['cc']['temp'] . "&#176;$system</strong> (Feels like " . $weather_array['cc']['feels_like'] . "&#176;$system)<br />\n";
	$response .= "<span class=\"details\">Last update: " . $weather_array['cc']['last_update'] . "<br />\n";
	$response .= "Data from: " . $weather_array['cc']['observation_station'] . "<br />\n";
	$response .= "Humidity: " . $weather_array['cc']['humidity'] . "%<br />\n";
	$response .= "Visibility: " . $weather_array['cc']['visibility'] . " " .$weather_array['unit']['dist'] . "<br />\n";
	$response .= "Dew Point: " . $weather_array['cc']['dew_point'] . "&#176;$system<br />\n";
	$response .= "UV Index: " . $weather_array['cc']['uv_index'] . " (" . $weather_array['cc']['uv_text'] . ")<br />\n";
	$response .= "Barometer: " . $weather_array['cc']['barometer'] . "$barometer " . $weather_array['cc']['barometer_dir'] . "<br />\n";
	$response .= "Moon: " . $weather_array['cc']['moon_text'] . "<br />\n";
	$response .= "Wind: " . $weather_array['cc']['wind_speed'];
	if(is_numeric($weather_array['cc']['wind_speed'])) $response .= $weather_array['unit']['speed'];
	$response .= " From: " . $weather_array['cc']['wind_text'] . "</span>\n";
	$response .= "</td></tr></table>\n";

	if(array_key_exists("day",$weather_array))
	{
		$day_counter = 1;
		foreach($weather_array['day'] as $forecast_day)
		{
//			print_r($forecast_day);

			if($day_counter == 1)
			{
				$forecast_date = ($forecast_day[hi] == "N/A") ? "Tonight":"Today";
				$forecast_icon = ($forecast_day[hi] == "N/A") ? $forecast_day['night_icon']:$forecast_day['day_icon'];
				$forecast_data = ($forecast_day[hi] == "N/A") ? "$forecast_day[night_text]<br />":"$forecast_day[day_text]<br />";
				if($forecast_day[hi] != "N/A") $forecast_data .= "High: <strong>$forecast_day[hi]&#176;</strong>$system<br />";
				$forecast_data .= "Low: <strong>$forecast_day[low]&#176;</strong>$system<br />";
			}
			else
			{
				$forecast_date = "$forecast_day[day] <span class=\"for_date\">[$forecast_day[date]]</span>";
				$forecast_icon = $forecast_day['day_icon'];
				$forecast_data = $forecast_day['day_text']."<br />High: <strong>".$forecast_day['hi']."&#176;</strong>$system<br />Low: <strong>".$forecast_day['low']."&#176;</strong>$system<br />";
			}
			$forecast_data .= "Sunrise: $forecast_day[sunrise]<br />Sunset: $forecast_day[sunset]";
			if($day_counter == 1 And $forecast_day[hi] == "N/A") $forecast_data .= "<br />&nbsp;";
			$response .= "<div id=\"day$day_counter\" class=\"forecast ".applyforecaststyle($forecast_day['hi'])."\">\n";
			$response .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$response .= "	<tr><td colspan=\"2\" align=\"center\" class=\"for_border\">$forecast_date</td></tr>\n";
			$response .= "	<tr><td><img src=\"/images/weather/64x64/$forecast_icon.png\" /></td><td><span class=\"details\">$forecast_data</span></td></tr>\n";
			$response .= "</table></div>\n";

			$day_counter++;
		}
	}

	$response .= "<div style=\"clear:left;\">\n";
	$response .= ($_REQUEST['remember']) ? "":"<input type=\"button\" value=\"Remember Location\" name=\"remember\" onclick=\"getModule(theForm.elements['zip'].value,1)\" />";
	$response .= "</div>\n";
	echo "<weather>$response</weather>";
}

function applyforecaststyle($temp)
{
	// returns the class name to apply based on high temp
	if ($temp >= 0) $class = "temp_0";
	if ($temp >= 10) $class = "temp_10";
	if ($temp >= 20) $class = "temp_20";
	if ($temp >= 30) $class = "temp_30";
	if ($temp >= 40) $class = "temp_40";
	if ($temp >= 50) $class = "temp_50";
	if ($temp >= 60) $class = "temp_60";
	if ($temp >= 70) $class = "temp_70";
	if ($temp >= 80) $class = "temp_80";
	if ($temp >= 90) $class = "temp_90";
	if ($temp >= 100) $class = "temp_100";
	if ($temp == "N/A") $class = "temp_u";
	return $class;
}
?>
