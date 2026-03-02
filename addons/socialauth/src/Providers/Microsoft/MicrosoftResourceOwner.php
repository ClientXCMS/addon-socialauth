<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Microsoft;

use App\Addons\SocialAuth\Contracts\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class MicrosoftResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getUsername(): string
    {
        return $this->getValueByKey($this->response, 'displayName', '');
    }

    public function getEmail(): string
    {
        return $this->getValueByKey($this->response, 'userPrincipalName', $this->getValueByKey($this->response, 'mail', ''));
    }

    public function toArray()
    {
        return $this->response;
    }
}
