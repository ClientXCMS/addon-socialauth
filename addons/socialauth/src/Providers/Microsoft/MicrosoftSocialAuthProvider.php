<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Microsoft;

use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class MicrosoftSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public function hex(): string
    {
        return '#00a4ef';
    }

    public function name(): string
    {
        return 'microsoft';
    }

    public function title(): string
    {
        return 'Microsoft';
    }

    public function logo(): string
    {
        return 'https://cdn.clientxcms.com/ressources/socialauth/microsoft.svg';
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://graph.microsoft.com/v1.0/me';
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getDefaultScopes()
    {
        return [
            'openid',
            'profile',
            'User.Read'
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                is_array($data) ? ($data['error']['message'] ?? $response->getBody()->getContents()) : $response->getBody()->getContents(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MicrosoftResourceOwner($response);
    }
}
