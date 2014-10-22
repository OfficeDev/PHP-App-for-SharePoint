<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
// See full license at the bottom of this file.

$loader = require 'vendor/autoload.php';

// Include the configuration parameters
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
    try{
    	// Initialize the SharePoint provider with parameters from the POST request
    	//  and the client_id and secret
        $provider = new League\OAuth2\Client\Provider\SharePoint(array(
        		'clientId' => $client_id,
        		'clientSecret'=> $client_secret,
        		'SPAppToken' => $_POST['SPAppToken'],
        		'SPSiteUrl' => $_POST['SPSiteUrl']
        ));
    }
    catch(Exception $exception){
        echo '<p>An exception occurred creating the SharePoint provider: ' . 
             $e->getMessage().'</p>'; 		
        exit;
    }

    try{
    	// Get the refresh token from the provider
    	// at this point we haven't requested anything from
    	// the token service
        $refreshToken = $provider->getRefreshToken();
        echo 'Refresh token extracted from the context token: ', '<br />', 
        		$refreshToken, '<p />';
        
        // Getting ready to request an access token for this resource 
        // to the token service.        
        $resource = $provider->getResource();
        $tokenServiceUri = $provider->urlAccessToken();
    	$grant = new \League\OAuth2\Client\Grant\RefreshToken();
    	echo 'Getting ready to request an access token: ', '<br />', 
    			'Resource: ', $resource, '<br />',
    			'Token service: ', $tokenServiceUri, '<p />';
    	$accessToken = $provider->getAccessToken($grant, ['refresh_token' => $refreshToken, 'resource' => $resource]);
    	// We have an access token. Save the token to a
    	//   session variable for reuse until it expires
    	echo 'Access token: ', '<br />', $accessToken->accessToken, '<p />';
    	
    	//This is the REST endpoint that we are sending our request to
    	$apiUrl = $_POST['SPSiteUrl'] . '/_api/web/title';
    	
    	// Using guzzle to create an http client
    	// pass the access token in the Authorization header
    	$client = new Guzzle\Service\Client();
    	$request = $client->createRequest('GET', $apiUrl);
    	$request->setHeader('Authorization', 'Bearer ' . $accessToken->accessToken);
    	$request->setHeader('Accept', 'application/json;odata=verbose');
    	$response = $client->send($request);

    	//Decode the response using the JSON decoder
    	$output = $response->getBody(true);
    	$jsonResult = json_decode($output);
    	
    	if($jsonResult->d != null){
    		// Print the result to the page
    	 	echo '<p>Querying the REST endpoint ', $apiUrl , '<br/>';
    	   	echo 'Result: ' , $jsonResult->d->Title , '</p>';
    	}
    	else{
    	  	// There was a problem with the request
    	   	echo '<p>There was a problem querying the REST endpoint '. $apiUrl . '<br/>';
    	   	echo 'Error code: ' . $jsonResult->error->code . '<br/>';
    	   	echo 'Error message: '. $jsonResult->error->message->value . '</p>';
    	}
    }
    catch(Exception $exception){
        echo '<p>An exception occurred getting an access token: ' . 
             $exception->getMessage().'</p>';
        exit;
    }
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
