<?php

namespace App\SocialAuth;

interface ResourceOwnerInterface
{
    public function getId();

    public function getEmail():string;
    public function getUsername():string;
    public function toArray();
}