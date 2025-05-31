<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\FaceBook;

use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class FaceBookSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    /**
     * Production Graph API URL.
     *
     * @const string
     */
    protected const BASE_FACEBOOK_URL = 'https://www.facebook.com/';

    /**
     * Beta tier URL of the Graph API.
     *
     * @const string
     */
    protected const BASE_FACEBOOK_URL_BETA = 'https://www.beta.facebook.com/';

    /**
     * Production Graph API URL.
     *
     * @const string
     */
    protected const BASE_GRAPH_URL = 'https://graph.facebook.com/';

    /**
     * Beta tier URL of the Graph API.
     *
     * @const string
     */
    protected const BASE_GRAPH_URL_BETA = 'https://graph.beta.facebook.com/';

    /**
     * Regular expression used to check for graph API version format
     *
     * @const string
     */
    protected const GRAPH_API_VERSION_REGEX = '~^v\d+\.\d+$~';

    /**
     * The Graph API version to use for requests.
     *
     * @var string
     */
    protected $graphApiVersion = 'v2.10';

    /**
     * A toggle to enable the beta tier URL's.
     *
     * @var bool
     */
    private $enableBetaMode = false;

    /**
     * The fields to look up when requesting the resource owner
     *
     * @var string[]
     */
    protected $fields;

    public function hex(): string
    {
        return '#4267B2';
    }

    public function icon(): string
    {
        return resource_path('global/socialauth/facebook.svg');

    }

    public function name(): string
    {
        return 'facebook';
    }

    public function title(): string
    {
        return 'Facebook';
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (! empty($options['enableBetaTier']) && $options['enableBetaTier'] === true) {
            $this->enableBetaMode = true;
        }

        if (! empty($options['fields']) && is_array($options['fields'])) {
            $this->fields = $options['fields'];
        } else {
            $this->fields = [
                'id', 'name', 'first_name', 'last_name',
                'email', 'hometown', 'picture.type(large){url,is_silhouette}',
                'gender', 'age_range',
            ];

            // backwards compatibility less than 2.8
            if (version_compare(substr($this->graphApiVersion, 1), '2.8') < 0) {
                $this->fields[] = 'bio';
            }
        }
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getBaseFacebookUrl().$this->graphApiVersion.'/dialog/oauth';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getBaseGraphUrl().$this->graphApiVersion.'/oauth/access_token';
    }

    public function getDefaultScopes(): array
    {
        return ['public_profile', 'email'];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $appSecretProof = hash_hmac('sha256', $this->clientSecret, $token->getToken());

        return $this->getBaseGraphUrl()
            .$this->graphApiVersion
            .'/me?fields='.implode(',', $this->fields)
            .'&access_token='.$token.'&appsecret_proof='.$appSecretProof;
    }

    public function getAccessToken($grant = 'authorization_code', array $params = []): AccessToken
    {
        if (isset($params['refresh_token'])) {
            throw new \Exception('Facebook does not support token refreshing.');
        }

        return parent::getAccessToken($grant, $params);
    }

    /**
     * Exchanges a short-lived access token with a long-lived access-token.
     */
    public function getLongLivedAccessToken(string $accessToken): AccessToken
    {
        $params = [
            'fb_exchange_token' => $accessToken,
        ];

        return $this->getAccessToken('fb_exchange_token', $params);
    }

    protected function createResourceOwner(array $response, AccessToken $token): FaceBookResourceOwner
    {
        return new FaceBookResourceOwner($response);
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (empty($data['error'])) {
            return;
        }

        $message = $data['error']['type'].': '.$data['error']['message'];
        throw new IdentityProviderException($message, $data['error']['code'], $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContentType(ResponseInterface $response): string
    {
        $type = parent::getContentType($response);

        // Fix for Facebook's pseudo-JSONP support
        if (strpos($type, 'javascript') !== false) {
            return 'application/json';
        }

        // Fix for Facebook's pseudo-urlencoded support
        if (strpos($type, 'plain') !== false) {
            return 'application/x-www-form-urlencoded';
        }

        return $type;
    }

    /**
     * Get the base Facebook URL.
     */
    protected function getBaseFacebookUrl(): string
    {
        return $this->enableBetaMode ? static::BASE_FACEBOOK_URL_BETA : static::BASE_FACEBOOK_URL;
    }

    /**
     * Get the base Graph API URL.
     */
    protected function getBaseGraphUrl(): string
    {
        return $this->enableBetaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
    }

    public function logo(): string
    {
        return 'https://api.clientxcms.com/assets/4543fddb-8fb5-4e75-9927-7803a80fe9b2';
    }

    protected function getAuthorizationHeaders($token = null)
    {
        if ($token) {
            return ['Authorization' => 'Bearer '.$token->getToken()];
        }

        return [];
    }
}
