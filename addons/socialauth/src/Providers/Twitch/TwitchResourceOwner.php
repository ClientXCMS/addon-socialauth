<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Twitch;

use App\Addons\SocialAuth\Contracts\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class TwitchResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $response;

    public function __construct(array $response = [])
    {
        $this->response = $response['data'][0] ?? $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getUsername(): string
    {
        return $this->getValueByKey($this->response, 'display_name', $this->getValueByKey($this->response, 'login', ''));
    }

    public function getEmail(): string
    {
        return $this->getValueByKey($this->response, 'email', '');
    }

    public function toArray()
    {
        return $this->response;
    }
}
