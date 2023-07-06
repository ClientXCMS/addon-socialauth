<?php

namespace App\SocialAuth\Event;

use App\SocialAuth\Database\SocialAuthUserTable;
use ClientX\Event\Event;
use ClientX\Session\FlashService;
use ClientX\Translator\Translater;

class SocialAuthEventManager
{
    private SocialAuthUserTable $authUserTable;
    private FlashService $flash;
    private Translater $translater;

    public function __construct(SocialAuthUserTable $authUserTable, FlashService $flash, Translater $translater)
    {
        $this->authUserTable = $authUserTable;
        $this->flash = $flash;
        $this->translater = $translater;
    }
    public function __invoke(Event $event)
    {
        $target = $event->getTarget();
        if ($this->authUserTable->isSignupWithSocial($target->getId())){
            $event->stopPropagation(true);
            $this->flash->info($this->translater->trans('socialauth.cannotlog'));
        }
    }
}