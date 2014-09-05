<?php

/*! @class TokenHelper
	@abstract A helper class that retrieves the access token given the SPAppToken and SharePoint SiteUrl. 
				The class assumes that the client_id, client_secret, and redirect_uri are declared in the application.ini configuration file.
*/
class TokenHelper {
	
	private $clientId;
	private $clientSecret;
	private $tokenServiceUri;
	private $refreshToken;
	private $resource;
	
	/*! @function __construct
		@abstract Initializes an instance of the TokenHelper class with values passed as parameters,
					as well as values declared in the application.ini configuration file.
		@param SPSiteUrl string - The URL of the SharePoint site from where the PHP web application was invoked.
		@param SPAppToken The JWT token that contains the refresh token and the token service URI.
		@result Object - The TokenHelper instance that we can use to get an access token.
	 */
	function __construct($SPSiteUrl, $SPAppToken){
		// Get the values from the application.ini configuration file
		// The configuration file should have client_id, client_secret, and redirect_uri declared
		$config = parse_ini_file('application.ini', false, INI_SCANNER_NORMAL);
		
		// We need to URLEncode the parameters that we are going to send to the token service.
		$this->clientSecret = urlencode($config['client_secret']);
		
		// Extract the host part of the SharePoint site URL
		$host = parse_url($SPSiteUrl, PHP_URL_HOST);
		
		// The JWT token is base 64 coded. Dedocing it gives us a string in JSON format.
		$json = base64_decode($SPAppToken);
		// Clean the json string
		$start = strpos($json, '}') + 1;
		$length = strrpos($json, '}') + 1 - $start;
		$json = substr($json, $start, $length);
		
		// Get a JSON object from the string and and extract the appCtx
		$jsonObj = json_decode($json);
		$appCtx = json_decode($jsonObj->appctx);
		
		// The appCtxSender contains values that we need to construct parameters that we send to the token service
		$appCtxSender = explode("@", $jsonObj->appctxsender);
		$this->resource = urlencode($appCtxSender[0].'/'. $host . '@'.$appCtxSender[1]);
		$this->clientId = urlencode($config['client_id'].'@'.$appCtxSender[1]);
		
		// Extract the refresh token from the JSON object.
		$this->refreshToken = urlencode($jsonObj->refreshtoken);
		
		// Get the token service URI from the JSON object. 
		$this->tokenServiceUri = $appCtx->SecurityTokenServiceUri;
	}

	/*! @function GetAccessToken
	 	@abstract The constructor has already extracted all the parameters we need to get an access token from the token service.
	 				This function builds the HTTP request and parses the response from the token service.
	 	@result Object - An object that represents the access token.
	 */
	public function GetAccessToken(){
		// Build the request body with the values built in the constructor.
		$authenticationRequestBody = 'grant_type=refresh_token&client_id='.$this->clientId.'&client_secret='.$this->clientSecret.'&refresh_token='.$this->refreshToken.'&resource='.$this->resource;
		
		// Initialize a CURL object
		$ch = curl_init();
		// set the url to the token service URI
		$stsUrl = $this->tokenServiceUri;
		curl_setopt($ch, CURLOPT_URL, $stsUrl);
		// Indicate that we want the response as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// Indicate that the request method is POST
		curl_setopt($ch, CURLOPT_POST, 1);
		// Set the parameters for the request, including the body
		curl_setopt($ch, CURLOPT_POSTFIELDS, $authenticationRequestBody);
		
		// FIXME: By default, HTTPS does not work with curl. This is a workaround for developer environments.
		// Using this CURL option exposes the PHP server to man-in-the-middle attacks. Remove in production scenarios.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Execute the request
		$output = curl_exec($ch);
		// Close CURL resource to free up system resources
		curl_close($ch);
		// Decode the response using json decoder
		$tokenOutput = json_decode($output);
		
		// Return the access token object
		return $tokenOutput;
	}
}