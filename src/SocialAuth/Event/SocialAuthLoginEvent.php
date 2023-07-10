<?php
namespace App\SocialAuth\Event;

use App\SocialAuth\Entity\SocialAuthUser;
use ClientX\Event\Event;

class SocialAuthLoginEvent extends Event {

    public $name = "socialauth.login";
    
    public function __construct(SocialAuthUser $user)
    {
        $this->setTarget($user);
    }
    public function getTarget(): SocialAuthUser
    {
        return parent::getTarget();
    }
}