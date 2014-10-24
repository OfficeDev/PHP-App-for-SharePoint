<?php
// Copyright (c) Microsoft. All rights reserved. Licensed under the MIT license.
// See full license at the bottom of this file.

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

// Class that implements the AbstractProvider class and methods
class SharePoint extends AbstractProvider
{
    public $scopes = array('refresh_token');
    public $responseType = 'json';
	protected $tenantId = 'common';
	protected $tokenServiceUri;
	protected $refreshToken;
	protected $resource;
	protected $spsite;
	
    public function __construct($options = array())
    {
    	$host = parse_url($options['SPSiteUrl'], PHP_URL_HOST);
    	// Validate that parse_url at least could parse the SPSiteUrl parameter
    	if(!$host){
    		throw new DomainException('The SPSiteUrl parameter' .
    				' is not a valid URI');
    	}
    	$this->spsite = $options['SPSiteUrl'];
    	
    	// The JWT token is base 64 coded.
    	//   Decoding it gives us a string in JSON format.
    	$json = base64_decode($options['SPAppToken']);

    	// Remove the JWT header
    	$start = strpos($json, '{"typ":"JWT","alg":"HS256"}') + 27;
	// And get the body of the token
    	$length = strrpos($json, '"}') + 2 - $start;
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
    	$this->resource = $appCtxSender[0].'/'.
    			$host . '@'.$appCtxSender[1];
    	$clientId = $options['clientId'].'@'.$appCtxSender[1];
    	 
    	// Extract the refresh token from the JSON object.
    	$this->refreshToken = $jsonObj->refreshtoken;
    	
    	// Get the token service URI from the JSON object.
    	$this->tokenServiceUri = $appCtx->SecurityTokenServiceUri;
    	
        if(array_key_exists('tenantId', $options))
        {
            $this->tenantId = $options['tenantId'];
            unset($options['tenantId']);
		}
		$options['clientId'] = $clientId;
		$options['resource'] = $this->resource;
		
		parent::__construct($options);
    }
    
    // Return the refresh token from the context token
    // provided by SharePoint
    public function getRefreshToken(){
    	return $this->refreshToken;
    }
    
    // Get the resource formatted as guid/uri@guid
    public function getResource(){
    	return $this->resource;
    }
    
    // Get the authorization service URI. Note that this is not 
    // used in this project because SharePoint provides a refresh token
    // when the app is launched from the Site contents page.
    public function urlAuthorize()
    {
        return 'https://login.windows.net/'. $tenantId .'/oauth2/authorize';
    }

    // Get the token service uri
    // SharePoint provides this value in the context token
    public function urlAccessToken()
    {
        return $this->tokenServiceUri;
    }
    
    // The function is provided to honor the contract 
    // with the AbstractProvider class.
    // The function returns the REST endpoint to 
    // get information about the current user.
    public function urlUserDetails(AccessToken $token)
    {
    	// Return the REST endpoint for information about 
    	// the current user
    	return $this->spsite . '/_api/web/currentuser';
    }

    // The function is provided to honor the contract
    // with the AbstractProvider class.
    // The function returns an object with data 
    // about the current user.    
    public function userDetails($response, AccessToken $token)
    {
    	//Prepare and send the http request
        $client = $this->getHttpClient();
        $request = $client->createRequest('GET', $this->urlUserDetails($token));
        $request->setHeader('Authorization', 'Bearer ' . $token->accessToken);
    	$request->setHeader('Accept', 'application/json;odata=verbose');
    	$response = $client->send($request);
        
        //Decode the response using the JSON decoder
        $output = $response->getBody(true);
        $jsonResult = json_decode($output);

        $user = new User;

        // Populate the user object
        $user->exchangeArray(array(
            'uid' => $jsonResult->d->UserId->NameId,
            'name' => $jsonResult->d->Title,
            'email' => $jsonResult->d->Email,
            'urls' => $jsonResult->d->__metadata->uri,
        ));

        return $user;
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