<html lang="en">
<head>
	<title>Ten Day</title>
</head>
<body>
<?
	//start at the beginning, can I pull an entire page over as text?
	/*$target_url="http://xoap.weather.com/weather/local/10011?dayf=10";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$html = curl_exec($ch);
	if (!$html) {
		echo "<br />cURL error number:" .curl_errno($ch);
		echo "<br />cURL error:" . curl_error($ch);
		exit;
	} else {
		echo "<B>Working! C</B><BR>";
		//echo $html;
	}*/
	echo "hello!<BR>";
	$html="http://xoap.weather.com/weather/local/10011?dayf=10";
	//echo $html;
	
	/*$dom = new DOMDocument();
	@$dom->loadHTML($html);
	
	$xpath = new DOMXPath($dom);
	//echo $xpath . "<BR>";
	$hrefs = $xpath->evaluate("//*");
	
	echo $hrefs->length;
	
	for ($i = 0; $i < $hrefs->length; $i++) {
		$href = $hrefs->item($i);
		//echo $i.$href."<BR>";
		$url = $href->getAttribute("*");
		//echo $url . " " . $target_url . "<BR>";
		echo $url . "<BR>";
	}*/
	
  // DOMElement->getElementsByTagName() -- Gets elements by tagname
  // nodeValue : The value of this node, depending on its type.
  // Load XML File. You can use loadXML if you wish to load XML data from a 			string

  $objDOM = new DOMDocument();
  $objDOM->loadHTML($html); //make sure path is correct


  $note = $objDOM->getElementsByTagName('day');
  // for each note tag, parse the document and get values for
  // tasks and details tag.
	$i = 0;
  foreach( $note as $value )
  {
    $tasks = $value->getElementsByTagName('hi');
    $task  = $tasks->item(0)->nodeValue;


    $details = $value->getElementsByTagName('lo');
    $detail  = $details->item(0)->nodeValue;

    echo $i++."$task :: $detail <br>";
  }


 echo $i."<BR>";
	
?>
</body>
</html>
