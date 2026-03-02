<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Minecraft;

use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

class MinecraftSocialAuthProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public function hex(): string
    {
        return '#4d4d4d'; // default generic dark hex for Minecraft, or #000000
    }

    public function name(): string
    {
        return 'minecraft';
    }

    public function title(): string
    {
        return 'Minecraft';
    }

    public function logo(): string
    {
        return 'https://cdn.clientxcms.com/ressources/socialauth/minecraft.png';
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://login.live.com/oauth20_authorize.srf';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://api.minecraftservices.com/authentication/login_with_xbox';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.minecraftservices.com/minecraft/profile';
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getDefaultScopes()
    {
        return [
            'XboxLive.signin',
        ];
    }

    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
        ];
        $params = $grant->prepareRequestParameters($params, $options);

        // 1. Get raw access token from Microsoft
        $request = $this->createRequest(self::METHOD_POST, 'https://login.live.com/oauth20_token.srf', null, [
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/x-www-form-urlencoded'],
            'body'    => $this->buildQueryString($params)
        ]);
        $response = $this->getParsedResponse($request);
        if (!isset($response['access_token'])) {
            throw new UnexpectedValueException('Failed to get Microsoft access token.');
        }
        $msToken = $response['access_token'];

        // 2. Sign into Xbox Live using the Microsoft access token
        $request = $this->createRequest(self::METHOD_POST, 'https://user.auth.xboxlive.com/user/authenticate', null, [
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            'body'    => json_encode([
                'Properties'   => [
                    'AuthMethod' => 'RPS',
                    'SiteName'   => 'user.auth.xboxlive.com',
                    'RpsTicket'  => 'd=' . $msToken,
                ],
                'RelyingParty' => 'http://auth.xboxlive.com',
                'TokenType'    => 'JWT',
            ])
        ]);
        $xboxResponse = $this->getParsedResponse($request);
        if (!isset($xboxResponse['Token']) || !isset($xboxResponse['DisplayClaims']['xui'][0]['uhs'])) {
            throw new UnexpectedValueException('Failed to get Xbox Live token.');
        }
        $xboxToken = $xboxResponse['Token'];
        $uhs       = $xboxResponse['DisplayClaims']['xui'][0]['uhs'];

        // 3. Get XSTS token using Xbox Live token
        $request = $this->createRequest(self::METHOD_POST, 'https://xsts.auth.xboxlive.com/xsts/authorize', null, [
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            'body'    => json_encode([
                'Properties'   => [
                    'SandboxId'  => 'RETAIL',
                    'UserTokens' => [$xboxToken],
                ],
                'RelyingParty' => 'rp://api.minecraftservices.com/',
                'TokenType'    => 'JWT',
            ])
        ]);
        $xstsResponse = $this->getParsedResponse($request);
        if (!isset($xstsResponse['Token'])) {
            throw new UnexpectedValueException('Failed to get XSTS token.');
        }
        $xstsToken = $xstsResponse['Token'];

        // 4. Get Minecraft Token
        $request = $this->createRequest(self::METHOD_POST, $this->getBaseAccessTokenUrl([]), null, [
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            'body'    => json_encode([
                'ensureLegacyEnabled' => true,
                'identityToken'       => sprintf('XBL3.0 x=%s;%s', $uhs, $xstsToken),
            ])
        ]);
        $mcResponse = $this->getParsedResponse($request);
        if (!isset($mcResponse['access_token'])) {
            throw new UnexpectedValueException('Failed to get Minecraft access token.');
        }

        return new AccessToken([
            'access_token' => $mcResponse['access_token'],
            'expires_in'   => $mcResponse['expires_in'] ?? 86400,
            'resource_owner_id' => null,
        ]);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                is_array($data) ? ($data['errorMessage'] ?? $data['error'] ?? $response->getBody()->getContents()) : $response->getBody()->getContents(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MinecraftResourceOwner($response);
    }
}
