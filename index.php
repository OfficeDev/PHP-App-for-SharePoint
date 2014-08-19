<?php
$config = parse_ini_file('application.ini', true, INI_SCANNER_NORMAL);
$authorize_endpoint = $config['Office365']['authorize_endpoint'];
$token_endpoint = $config['Office365']['token_endpoint'];
$redirect_uri = $config['Office365']['redirect_uri'];
$client_id = $config['Office365']['client_id'];
$scope = $config['Scopes'];

session_start();

if(isset($_POST['SPSiteUrl']))
{
	$resource = urldecode($_POST['SPSiteUrl']);
	$_SESSION["SPSiteUrl"] = $resource;
}
else if(isset($_SESSION["SPSiteUrl"]))
{
	$resource = $_SESSION["SPSiteUrl"];
}
else 
{
	echo "Launch the app from the contents page of your SharePoint site.";
	exit;
}
	
require_once 'vendor/autoload.php';

$apiUri = $resource . '/_api/Web/CurrentUser/Title';

if(isset($_POST['submit']) | isset($_SESSION['php-oauth-client']))
{
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
	$context = new fkooman\OAuth\Client\Context('<your_userid_in_your_webapp>', $scope);
	
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
		$result = $response->getBody();
		echo "<script>window.onload = function(){document.getElementById('result').innerHTML = '" . $result . "';}</script>";
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
	This page issues a call to a REST endpoint in a SharePoint using an Azure Active Directory access token.  
    <div>
    <form action="index.php" method="post">
    	SharePoint endpoint: <?php echo $apiUri ?><br/>
    	<input type="submit" name="submit" value="Issue the request"/>
    </form>
    </div>
    Response: <div id="result" ></div>
</body>
</html>