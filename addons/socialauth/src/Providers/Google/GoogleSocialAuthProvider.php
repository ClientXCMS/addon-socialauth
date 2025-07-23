<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Google;

use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class GoogleSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public function hex(): string
    {
        return '#DB4437';
    }

    public function icon(): string
    {
        return resource_path('global/socialauth/google.svg');
    }

    public function name(): string
    {
        return 'google';
    }

    public function title(): string
    {
        return 'Google';
    }

    public function logo(): string
    {
        return 'https://api-nextgen.clientxcms.com/assets/91ba546e-3154-4006-bfd9-19d66214ae13';
    }

    /**
     * Get authorization URL to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    /**
     * Get access token URL to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth2.googleapis.com/token';
    }

    /**
     * Get provider URL to retrieve user details
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {

        return 'https://openidconnect.googleapis.com/v1/userinfo';
    }

    protected function getAuthorizationParameters(array $options): array
    {

        $scopes = $this->getDefaultScopes();

        if (! empty($options['scope'])) {
            $scopes = array_merge($scopes, $options['scope']);
        }

        $options['scope'] = array_unique($scopes);

        $options = parent::getAuthorizationParameters($options);

        unset($options['approval_prompt']);

        return $options;
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * Discord's scope separator is space (%20)
     *f
     *
     * @return string Scope separator
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getDefaultScopes()
    {

        // "openid" MUST be the first scope in the list.
        return [

            'openid',
            'email',
            'profile',

        ];
    }

    protected function getAuthorizationHeaders($token = null)
    {
        if ($token) {
            return ['Authorization' => 'Bearer '.$token->getToken()];
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
        return new GoogleResourceOwner($response);
    }
}
