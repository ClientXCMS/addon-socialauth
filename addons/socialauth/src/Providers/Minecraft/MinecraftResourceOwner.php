<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers\Minecraft;

use App\Addons\SocialAuth\Contracts\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class MinecraftResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId()
    {
        $id = $this->getValueByKey($this->response, 'id');
        if ($id && strlen($id) === 32) {
            return preg_replace('/(.{8})(.{4})(.{4})(.{4})(.{12})/', '$1-$2-$3-$4-$5', $id);
        }

        return $id;
    }

    public function getUsername(): string
    {
        return $this->getValueByKey($this->response, 'name', '');
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
