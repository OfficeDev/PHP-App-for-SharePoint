<?php

require_once 'vendor/autoload.php';

use Guzzle\Http\Client;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException;
use Guzzle\Http\Exception\BadResponseException;

if($_SERVER['REQUEST_METHOD'] === 'GET'){
	$spsite = 'https://patsoldemo.sharepoint.com/_layouts/15/start.aspx#/_layouts/15/viewlsts.aspx';
	header("HTTP/1.1 302 Found");
	header("Location: " . $spsite);
	exit;

}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$spsite = urldecode($_POST['SPSiteUrl']);
	$token = $_POST['SPAppToken'];
	$apiUri = $spsite . "/_api/Web/CurrentUser/Title";

	try {
		$client = new Client();
		
		$bearerAuth = new BearerAuth($token);
		$client->addSubscriber($bearerAuth);
		$response = $client->get($apiUri)->send();
		echo $response->getBody();
	} catch (BearerErrorResponseException $e) {
		echo $e->getMessage() . PHP_EOL;
	} catch (BadResponseException $e) {
		echo $e->getMessage() . PHP_EOL;
	}
	
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
<title>Insert title here</title>
</head>
<body>
<p>Hello</p>

</body>
</html>