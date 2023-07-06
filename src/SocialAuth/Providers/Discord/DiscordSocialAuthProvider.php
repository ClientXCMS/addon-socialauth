<?php

namespace App\SocialAuth\Providers\Discord;

use App\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class DiscordSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public string $host = "https://discord.com";
    public string $apiDomain = "https://discord.com/api/v9";
    public function hex(): string
    {
        return '#23272A';
    }

    public function icon(): string
    {
        return "fab fa-discord";
    }

    public function name(): string
    {
        return "discord";
    }

    public function title(): string
    {
        return 'Discord';
    }

    public function logo():string
    {
        return "https://logovtor.com/wp-content/uploads/2022/06/discord-logo-vector-2022.png";
    }

    /**
     * Get authorization URL to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->host.'/oauth2/authorize';
    }

    /**
     * Get access token URL to retrieve token
     *
     * @param  array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->apiDomain.'/oauth2/token';
    }

    /**
     * Get provider URL to retrieve user details
     *
     * @param  AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->apiDomain.'/users/@me';
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

        return [
            'identify',
            'email',
            'connections',
            'guilds',
            'guilds.join'
        ];
    }


    protected function getAuthorizationHeaders($token = null)
    {
        if ($token){
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
        return new DiscordResourceOwner($response);
    }
}