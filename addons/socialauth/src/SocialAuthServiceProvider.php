<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth;

use App\Addons\SocialAuth\Controllers\AdminSocialAuthController;
use App\Extensions\BaseAddonServiceProvider;
use App\Models\Admin\Permission;

class SocialAuthServiceProvider extends BaseAddonServiceProvider
{
    protected string $uuid = 'socialauth';

    public function boot()
    {
        \Route::middleware('web')
            ->name('socialauth.')
            ->group(function () {
                require addon_path('socialauth', 'routes/web.php');
            });
        \Route::middleware(['web', 'admin'])
            ->prefix(admin_prefix('settings/extensions'))
            ->name('admin.socialauth.')
            ->group(function () {
                require addon_path('socialauth', 'routes/admin.php');
            });
        $this->loadTranslations();
        $this->loadViews();
        $service = $this->app->make('settings');
        $service->addCardItem('extensions', 'socialauth', 'socialauth::messages.modulename', 'socialauth::messages.description', 'bi bi-door-open', [AdminSocialAuthController::class, 'index'], Permission::MANAGE_EXTENSIONS);

    }
}
