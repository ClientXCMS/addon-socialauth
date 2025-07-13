<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Controllers;

use App\Addons\SocialAuth\Models\ProviderEntity;
use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Account\Customer;
use App\Models\Admin\Permission;
use App\Models\Metadata;

class AdminSocialAuthController extends AbstractCrudController
{
    protected string $viewPath = 'socialauth_admin::';

    protected string $routePath = 'admin.socialauth';

    protected string $model = ProviderEntity::class;

    public function getIndexParams($items, string $translatePrefix, $filter = null, $filters = [])
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);
        $data = parent::getIndexParams($items, $translatePrefix);
        $data['providers'] = ProviderEntity::getProviders();
        $data['enabledProviders'] = ProviderEntity::all()->where('enabled', true)->pluck('name')->toArray();

        return $data;
    }

    public function enable(string $provider)
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);
        $names = collect(ProviderEntity::getProviders())->map(function (SocialAuthProviderInterface $providerEntity) {
            return $providerEntity->name();
        })->toArray();
        abort_if(! in_array($provider, $names), 404);
        $entity = ProviderEntity::firstOrCreate([
            'name' => $provider,
        ]);
        $provider = $entity->provider();
        abort_if(! $provider, 404);
        $entity->update(['enabled' => true]);

        return redirect()->route($this->routePath.'.show', $provider->name());
    }

    public function disable(string $provider)
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);
        $names = collect(ProviderEntity::getProviders())->map(function (SocialAuthProviderInterface $providerEntity) {
            return $providerEntity->name();
        })->toArray();
        abort_if(! in_array($provider, $names), 404);
        $entity = ProviderEntity::firstOrCreate([
            'name' => $provider,
        ]);
        $provider = $entity->provider();
        abort_if(! $provider, 404);
        $entity->update(['enabled' => false]);

        return back();
    }

    public function show(ProviderEntity $providerEntity)
    {
        staff_aborts_permission(Permission::MANAGE_EXTENSIONS);
        $provider = $providerEntity->provider();
        abort_if(! $provider, 404);
        $customers = Metadata::where('key', 'social_'.$provider->name())->get()->map(function ($item) {
            return Customer::find($item->model_id);
        });

        return view($this->viewPath.'.show', [
            'provider' => $provider,
            'entity' => $providerEntity,
            'items' => $customers,
        ]);
    }

    public function update(ProviderEntity $providerEntity)
    {
        $validated = request()->validate([
            'client_id' => 'required|string|max:1000',
            'client_secret' => 'required|string|max:1000',
            'redirect_url' => 'required|url|max:1000',
        ]);
        $providerEntity->update($validated);

        return back()->with('success', __('admin.flash.updated'));
    }
}
