<?php
 require_once 'TokenHelper.php';

 if($_SERVER['REQUEST_METHOD'] == 'GET'){
 	echo 'You must launch this web app from the contents page of the SharePoint site where you deployed the app for SharePoint.';
 	exit;
 }
 if($_SERVER['REQUEST_METHOD'] == 'POST'){
 	$tokenHelper = new TokenHelper($_POST['SPSiteUrl'], $_POST['SPAppToken']);
 	$accessToken = $tokenHelper->GetAccessToken();
 	
 	//Using curl to post the information to STS and get back the authentication response
 	$ch = curl_init();
 	// set url
 	$apiUrl = $_POST['SPSiteUrl'] . '/_api/web/title';
 	curl_setopt($ch, CURLOPT_URL, $apiUrl);
 	// Get the response back as a string
 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 	// By default, HTTPS does not work with curl.
 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 	//Add accept and authorization headers
 	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$accessToken->access_token, 'Accept:application/json;odata=verbose'));
 	
 	// read the output from the post request
 	$output = curl_exec($ch);
 	// close curl resource to free up system resources
 	curl_close($ch);
 	// decode the response from sts using json decoder
 	$jsonResult = json_decode($output);
 	echo 'Querying the REST endpoint '. $apiUrl . '<br/>';
 	echo 'Result: ' . $jsonResult->d->Title;
 }
?>
