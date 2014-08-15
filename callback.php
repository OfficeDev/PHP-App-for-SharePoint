<?php
$config = parse_ini_file('application.ini', true, INI_SCANNER_NORMAL);
$authorize_endpoint = $config['Office365']['authorize_endpoint'];
$token_endpoint = $config['Office365']['token_endpoint'];
$redirect_uri = $config['Office365']['redirect_uri'];
$client_id = $config['Office365']['client_id'];
$client_secret = $config['Office365']['client_secret'];


require_once 'vendor/autoload.php';

$clientConfig = new fkooman\OAuth\Client\ClientConfig(
    array(
    	"authorize_endpoint" => $authorize_endpoint,
    	"token_endpoint" => $token_endpoint,
    	"credentials_in_request_body" => true,
        "client_id" => $client_id,
        "client_secret" => $client_secret,
    	"redirect_uri" => $redirect_uri
    )
);

try {
    $tokenStorage = new fkooman\OAuth\Client\SessionStorage();
    $httpClient = new Guzzle\Http\Client();
    $cb = new fkooman\OAuth\Client\Callback("foo", $clientConfig, $tokenStorage, $httpClient);
    $cb->handleCallback($_GET);

    header("HTTP/1.1 302 Found");
    header("Location: http://localhost/EclipseTemplateWeb/index.php");
    exit;
} catch (fkooman\OAuth\Client\Exception\AuthorizeException $e) {
    // this exception is thrown by Callback when the OAuth server returns a
    // specific error message for the client, e.g.: the user did not authorize
    // the request
    die(sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription()));
} catch (Exception $e) {
    // other error, these should never occur in the normal flow
    die(sprintf("ERROR: %s", $e->getMessage()));
}