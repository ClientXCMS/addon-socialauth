<?php

namespace App\SocialAuth\Database;

use App\SocialAuth\Entity\SocialAuthUser;

class SocialAuthUserTable extends \ClientX\Database\Table
{
    protected $table = "socialauth_users";
    protected $entity = SocialAuthUser::class;

    public function isSignupWithSocial(int $userId){
        return $this->makeQuery()->where('user_id = :userId')
            ->params(['userId' => $userId])
            ->count() == 1;
    }

    public function signup(int $userId, int $providerId, string $provider, string $refreshToken)
    {
        $this->insert([
            'user_id' => $userId,
            'provider' => $provider,
            'provider_id' => $providerId,
            'refresh_token' => $refreshToken
        ]);
    }
}