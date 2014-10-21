<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
// See full license at the bottom of this file.

$loader = require 'vendor/autoload.php';

// Include the configuration class
require_once 'TokenHelper.php';
require_once 'config.php';

// The PHP web application should be started with a SharePoint context, 
//   which uses a POST request to send token information
// Make sure that you browse to the contents page of the 
//   SharePoint site where you deployed the app.
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo '<p>You must launch this web app from the contents page of the ' . 
         'SharePoint site where you deployed the app for SharePoint.</p>';
    exit;
}
// When the web application is launched from the SharePoint 
//   contents page, it sends SPSiteUrl and SPAppToken parameters
//	in the body of the request. 
else {
    // Initialize the TokenHelper class with parameters from the POST request
    try{
        //$tokenHelper = new TokenHelper($_POST['SPSiteUrl'], $_POST['SPAppToken']);
    	// Extract the host part of the SharePoint site URL
    	$host = parse_url($_POST['SPSiteUrl'], PHP_URL_HOST);
    	// Validate that parse_url at least could parse the SPSiteUrl parameter
    	if(!$host){
    		throw new DomainException('The SPSiteUrl parameter' .
    				' is not a valid URI');
    	}
    	 
    	// The JWT token is base 64 coded.
    	//   Decoding it gives us a string in JSON format.
    	$json = base64_decode($_POST['SPAppToken']);
    	// Remove the extra characters from the JSON string
    	$start = strpos($json, '}') + 1;
    	$length = strrpos($json, '}') + 1 - $start;
    	$json = substr($json, $start, $length);
    	 
    	// Get a JSON object from the string and and extract the appCtx
    	$jsonObj = json_decode($json);
    	 
    	if($jsonObj === null){
    		throw new DomainException('The SPAppToken parameter is ' .
    				' not a base64 JSON string');
    	}
    	 
    	$appCtx = json_decode($jsonObj->appctx);
    	 
    	// The appCtxSender contains values that we need to
    	//   construct parameters that we send to the token service
    	$appCtxSender = explode("@", $jsonObj->appctxsender);
    	$resource = $appCtxSender[0].'/'.
    			$host . '@'.$appCtxSender[1];
    	$clientId = $client_id.'@'.$appCtxSender[1];
    	
    	$clientSecret = $client_secret;
    	 
    	// Extract the refresh token from the JSON object.
    	$refreshToken = $jsonObj->refreshtoken;
    	 
    	// Get the token service URI from the JSON object.
    	$tokenServiceUri = $appCtx->SecurityTokenServiceUri;
        $provider = new League\OAuth2\Client\Provider\SharePoint(array(
        		'clientId' => $clientId,
        		'clientSecret'=> $clientSecret,
        		'resource' => $resource
        ));
        
        $provider->setUrlAccessToken($tokenServiceUri);
    }
    catch(Exception $exception){
        echo '<p>An exception occurred creating the TokenHelper object: ' . 
             $e->getMessage().'</p>'; 		
        exit;
    }
    // Get the access token object from the TokenHelper class

    try{
        // We have an access token. Save the token to a 
        //   session variable for reuse until it expires
    	//$grant = new \League\OAuth2\Client\Grant\RefreshToken();
    	//$token = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken]);
    	echo $refreshToken;
    	$grant = new \League\OAuth2\Client\Grant\RefreshToken();
    	$accessToken = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken, 'resource' => $resource]);
    	//$tokenHelper = new TokenHelper($_POST['SPSiteUrl'], $_POST['SPAppToken']);
    	//$accessToken = $tokenHelper->GetAccessToken();
    	echo '<p>';
    	echo $accessToken->accessToken;
    	
    	//This is the REST endpoint that we are sending our request to
    	$apiUrl = $_POST['SPSiteUrl'] . '/_api/web/title';
    	$client = new Guzzle\Service\Client();
    	$request = $client->createRequest('GET', $apiUrl, [
    			'headers' => ['Authorization' => 'Bearer ' . $accessToken->accessToken,
    							'Accept' => 'application/json;odata=verbose'
    			]
    	]);
    	
    	$response = $client->send($request);
    	echo $response->getBody(true);
    	echo 'something';
    }
    catch(Exception $exception){
        echo '<p>An exception occurred getting an access token: ' . 
             $exception->getMessage().'</p>';
        exit;
    }
//     //Initialize a CURL instance
//     $curlObj = curl_init();
//     // //This is the REST endpoint that we are sending our request to
//     $apiUrl = $_POST['SPSiteUrl'] . '/_api/web/title';
//     curl_setopt($curlObj, CURLOPT_URL, $apiUrl);
//     // Indicate that we want the response back as a string
//     curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
//     // FIXME: By default, HTTPS does not work with curl.
//     //   This is a workaround for developer environments.
//     // Using this CURL option exposes the PHP server to man-in-the-middle attacks.
//     //   Remove in production scenarios.
//     curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, false);
//     //Add accept and authorization headers, along with the access token
//     curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.
//     		$accessToken->access_token,
//     		'Accept:application/json;odata=verbose'));
     
//     // Execute the request and get the response in the
//     //   output variable as a string
//     $output = curl_exec($curlObj);
//     // Close curl instance to free up system resources
//     curl_close($curlObj);
//     // Decode the response using the JSON decoder
//     $jsonResult = json_decode($output);
     
//     if($jsonResult->d != null){
//     	// Print the result to the page
//     	echo '<p>Querying the REST endpoint '. $apiUrl . '<br/>';
//     	echo 'Result: ' . $jsonResult->d->Title.'</p>';
//     }
//     else{
//     	// There was a problem with the request
//     	echo '<p>There was a problem querying the REST endpoint '. $apiUrl . '<br/>';
//     	echo 'Error code: ' . $jsonResult->error->code . '<br/>';
//     	echo 'Error message: '. $jsonResult->error->message->value . '</p>';
//     }
    
 }
 
 //*********************************************************
 //
 //PHP-App-for-SharePoint, https://github.com/OfficeDev/PHP-App-for-SharePoint
 //
 //Copyright (c) Microsoft Corporation
 //All rights reserved.
 //
 //MIT License:
 //
 //Permission is hereby granted, free of charge, to any person obtaining
 //a copy of this software and associated documentation files (the
 //""Software""), to deal in the Software without restriction, including
 //without limitation the rights to use, copy, modify, merge, publish,
 //distribute, sublicense, and/or sell copies of the Software, and to
 //permit persons to whom the Software is furnished to do so, subject to
 //the following conditions:
 //
 //The above copyright notice and this permission notice shall be
 //included in all copies or substantial portions of the Software.
 //
 //THE SOFTWARE IS PROVIDED ""AS IS"", WITHOUT WARRANTY OF ANY KIND,
 //EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 //MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 //NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 //LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 //OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 //WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 //
 //*********************************************************
