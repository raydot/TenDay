
<?php
$to = "raydot@gmail.com";
$from = 'From: Dave Kanter'."\r\n";
$subject = "Test from CRON!";
$body = "This is a test.\n Cool! \n Ok then!";
if (mail($to, $subject, $body, $from)) {
	echo("<P>Message sent!</p>");
} else {
	echo("<P>No dice!</p>");
}
?>
