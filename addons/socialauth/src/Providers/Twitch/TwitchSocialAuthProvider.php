<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Twitch;

use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class TwitchSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public function hex(): string
    {
        return '#9146FF';
    }

    public function name(): string
    {
        return 'twitch';
    }

    public function title(): string
    {
        return 'Twitch';
    }

    public function logo(): string
    {
        return 'https://cdn.clientxcms.com/ressources/socialauth/twitch.png';
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://id.twitch.tv/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.twitch.tv/helix/users';
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getDefaultScopes()
    {
        return [
            'user:read:email',
        ];
    }

    protected function getAuthorizationHeaders($token = null)
    {
        $headers = [];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . ($token instanceof AccessToken ? $token->getToken() : $token);
            $headers['Client-Id'] = $this->clientId;
        }

        return $headers;
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                is_array($data) ? ($data['message'] ?? $response->getBody()->getContents()) : $response->getBody()->getContents(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new TwitchResourceOwner($response);
    }
}
