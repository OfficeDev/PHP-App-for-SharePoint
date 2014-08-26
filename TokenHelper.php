<?php

class TokenHelper {
	
	private $clientId;
	private $clientSecret;
	private $tokenServiceUri;
	private $refreshToken;
	private $resource;
	
	function __construct($SPSiteUrl, $SPAppToken){
		$config = parse_ini_file('application.ini', false, INI_SCANNER_NORMAL);
		$this->clientSecret = urlencode($config['client_secret']);
		
		$host = parse_url($SPSiteUrl, PHP_URL_HOST);
		
		$json = base64_decode($SPAppToken);
		// Clean the json string
		$start = strpos($json, '}') + 1;
		$length = strrpos($json, '}') + 1 - $start;
		$json = substr($json, $start, $length);
		
		$jsonObj = json_decode($json);
		$appCtx = json_decode($jsonObj->appctx);
		
		$appCtxSender = explode("@", $jsonObj->appctxsender);
		$this->resource = urlencode($appCtxSender[0].'/'. $host . '@'.$appCtxSender[1]);
		$this->clientId = urlencode($config['client_id'].'@'.$appCtxSender[1]);
		$this->refreshToken = urlencode($jsonObj->refreshtoken);
		$this->tokenServiceUri = $appCtx->SecurityTokenServiceUri;
	}

	public function GetAccessToken(){
		$authenticationRequestBody = 'grant_type=refresh_token&client_id='.$this->clientId.'&client_secret='.$this->clientSecret.'&refresh_token='.$this->refreshToken.'&resource='.$this->resource;
		
		$ch = curl_init();
		// set the url
		$stsUrl = $this->tokenServiceUri;
		curl_setopt($ch, CURLOPT_URL, $stsUrl);
		// Get the response as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// Post request
		curl_setopt($ch, CURLOPT_POST, 1);
		// Set the parameters for the request
		curl_setopt($ch, CURLOPT_POSTFIELDS,  $authenticationRequestBody);
		
		// FIXME: By default, HTTPS does not work with curl.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// read the output from the post request
		$output = curl_exec($ch);
		// close curl resource to free up system resources
		curl_close($ch);
		// decode the response from sts using json decoder
		$tokenOutput = json_decode($output);
		return $tokenOutput;
	}
}