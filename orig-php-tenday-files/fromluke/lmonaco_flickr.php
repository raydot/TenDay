<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>lmonaco on flickr</title>
	<LINK REL="stylesheet" TYPE="text/css" HREF="/style/feed.css">
</head>

<body>

<?PHP 
include($_SERVER['DOCUMENT_ROOT']."/tenday/fromluke/myatomparser.php");
 
# where is the feed located? 
$url = "http://xoap.weather.com/weather/local/10011?dayf=10"; 
# create object to hold data and display output 
$atom_parser = new myAtomParser($url); 
# returns string containing HTML 
$output = $atom_parser->getOutput(); 

echo $output; 
?>

</body>
</html> 