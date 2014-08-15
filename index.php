<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$config = parse_ini_file('application.ini', true, INI_SCANNER_NORMAL);
	$authorize_endpoint = $config['Office365']['authorize_endpoint'];
	$token_endpoint = $config['Office365']['token_endpoint'];
	$redirect_uri = $config['Office365']['redirect_uri'];
	$client_id = $config['Office365']['client_id'];
	$scope = $config['Scopes'];
	$resource = $_POST['site'];
		
	require_once 'vendor/autoload.php';
	
	$apiUri = $_POST['site'] . '/_api/Web/CurrentUser/Title';
	
	$clientConfig = new fkooman\OAuth\Client\ClientConfig(
			array(
					'authorize_endpoint' => $authorize_endpoint,
					'token_endpoint' => $token_endpoint,
					'client_id' => $client_id,
					'redirect_uri' => $redirect_uri
			)
	);

	// This object initializes a session if not already created.
	// For production scenarios it is recommended to use a database for session management.
	$tokenStorage = new fkooman\OAuth\Client\SessionStorage();
	$httpClient = new Guzzle\Http\Client();

	$api = new fkooman\OAuth\Client\Api('foo', $clientConfig, $tokenStorage, $httpClient);

	// You should substitute the first parameter with an user identifier in your web app. 
	$context = new fkooman\OAuth\Client\Context('<the_userid_in_your_webapp>', $scope);
	
	$accessToken = $api->getAccessToken($context);
	if (false === $accessToken) {
		/* no valid access token available, go to authorization server */
		//FIXME: ricardol This is a workaround, the getAuthorizeUri should return the resource in the qs
		$authUri = $api->getAuthorizeUri($context) . '&resource=' . urlencode($resource);
		header("HTTP/1.1 302 Found");
		header("Location: " . $authUri);
		exit;
	}
	
	try {
		$client = new Guzzle\Http\Client();
		$bearerAuth = new fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
		$client->addSubscriber($bearerAuth);
		$response = $client->get($apiUri)->send();
		echo $response->getBody();
	} catch (fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
		if ("invalid_token" === $e->getBearerReason()) {
			// the token we used was invalid, possibly revoked, we throw it away
			$api->deleteAccessToken($context);
			$api->deleteRefreshToken($context);
			/* no valid access token available, go to authorization server */
			header("HTTP/1.1 302 Found");
			header("Location: " . $api->getAuthorizeUri($context));
			exit;
		}
		throw $e;
	} catch (Exception $e) {
		die(sprintf('ERROR: %s', $e->getMessage()));
	}
}
?>
<!DOCTYPE>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <title>Index</title>
</head>
<body>
    ##Index Page
    <div>
    <form action="index.php" method="post">
    	SharePoint endpoint: <input type="text" name="site" value="https://patsoldemo.sharepoint.com" />/_api/Web/CurrentUser/Title<br/>
    	<input type="submit" value="Get user title"/>
    </form>
    </div>
</body>
</html>