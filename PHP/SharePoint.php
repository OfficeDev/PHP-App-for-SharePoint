<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class SharePoint extends AbstractProvider
{
    public $scopes = array('refresh_token');
    public $responseType = 'json';

	protected $tenantId = 'common';
	protected $tokenServiceUri;
	
    public function __contruct($options = array())
    {
        if(array_key_exists('tenantId', $options))
        {
            $this->tenantId = $options['tenantId'];
            unset($options['tenantId']);
		}
		parent::__construct($options);
		
    }
    
    public function urlAuthorize()
    {
        return 'https://login.windows.net/'. $tenantId .'/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return $this->tokenServiceUri;
    }
    
    public function setUrlAccessToken($tokenService)
    {
    	$this->tokenServiceUri = $tokenService;
    }

    public function urlUserDetails(AccessToken $token)
    {
        return ''. $token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $client = $this->getHttpClient();
        $client->setBaseUrl('https://apis.live.net/v5.0/' . $response->id . '/picture');
        $request = $client->get()->send();
        $info = $request->getInfo();
        $imageUrl = $info['url'];

        $user = new User;

        $email = (isset($response->emails->preferred)) ? $response->emails->preferred : null;

        $user->exchangeArray(array(
            'uid' => $response->id,
            'name' => $response->name,
            'firstname' => $response->first_name,
            'lastname' => $response->last_name,
            'email' => $email,
            'imageurl' => $imageUrl,
            'urls' => $response->link . '/cid-' . $response->id,
        ));

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->emails->preferred) && $response->emails->preferred
            ? $response->emails->preferred
            : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return array($response->first_name, $response->last_name);
    }
}
