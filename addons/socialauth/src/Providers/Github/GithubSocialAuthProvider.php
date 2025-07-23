<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Github;

use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class GithubSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public string $host = 'https://github.com';

    public string $apiDomain = 'https://api.github.com';

    public function hex(): string
    {
        return '#333';
    }

    public function icon(): string
    {
        return resource_path('global/socialauth/github.svg');
    }

    public function name(): string
    {
        return 'github';
    }

    public function title(): string
    {
        return 'Github';
    }

    public function logo(): string
    {
        return 'https://api-nextgen.clientxcms.com/assets/da244d79-db15-41dc-b47a-7b5981be13be';
    }

    /**
     * Get authorization URL to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->host.'/login/oauth/authorize';
    }

    /**
     * Get access token URL to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->host.'/login/oauth/access_token';
    }

    /**
     * Get provider URL to retrieve user details
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {

        return $this->apiDomain.'/user';
    }

    protected function fetchResourceOwnerDetails(AccessToken $token)
    {

        $response = parent::fetchResourceOwnerDetails($token);

        if (empty($response['email'])) {
            $url = $this->getResourceOwnerDetailsUrl($token).'/emails';

            $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

            $responseEmail = $this->getParsedResponse($request);

            $response['email'] = isset($responseEmail[0]['email']) ? $responseEmail[0]['email'] : null;
        }

        return $response;
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
            'user.email',
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
        return (new GithubResourceOwner($response))->setDomain($this->host);
    }
}
