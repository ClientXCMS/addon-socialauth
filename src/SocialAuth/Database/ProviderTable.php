<?php

namespace App\SocialAuth\Database;

use App\SocialAuth\Entity\ProviderEntity;

class ProviderTable extends \ClientX\Database\Table
{
    protected $table = "socialauth_providers";
    protected $entity = ProviderEntity::class;
}