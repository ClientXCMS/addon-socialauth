<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Providers;

interface SocialAuthProviderInterface
{
    public function hex(): string;

    public function name(): string;

    public function title(): string;

    public function logo(): string;

    public function icon(): string;
}
