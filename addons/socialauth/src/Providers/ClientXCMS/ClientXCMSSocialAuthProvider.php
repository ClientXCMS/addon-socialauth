<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\ClientXCMS;

use App\Addons\SocialAuth\Providers\ClientXCMS\ClientXCMSResourceOwner;
use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class ClientXCMSSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public string $host = 'https://clientxcms.com';

    public function hex(): string
    {
        return '#333';
    }

    public function name(): string
    {
        return 'clientxcms';
    }

    public function title(): string
    {
        return 'ClientXCMS';
    }

    public function logo(): string
    {
        return 'https://cdn.clientxcms.com/ressources/socialauth/clientxcms.png';
    }

    /**
     * Get authorization URL to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->host . '/oauth2/authorize';
    }

    /**
     * Get access token URL to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->host . '/oauth2/access_token';
    }

    /**
     * Get provider URL to retrieve user details
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {

        return $this->host . '/oauth2/user';
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * Discord's scope separator is space (%20)
     *
     * @return string Scope separator
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getDefaultScopes()
    {

        return [];
    }

    protected function getAuthorizationHeaders($token = null)
    {
        if ($token) {
            return ['Authorization' => 'Bearer ' . $token->getToken()];
        }

        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException($response->getBody()->getContents(), $response->getStatusCode(), $response);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return (new ClientXCMSResourceOwner($response));
    }
}
