<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Models;

use App\Addons\SocialAuth\Providers\Discord\DiscordSocialAuthProvider;
use App\Addons\SocialAuth\Providers\FaceBook\FaceBookSocialAuthProvider;
use App\Addons\SocialAuth\Providers\Github\GithubSocialAuthProvider;
use App\Addons\SocialAuth\Providers\Google\GoogleSocialAuthProvider;
use App\Addons\SocialAuth\Providers\SocialAuthProviderInterface;
use App\Casts\EncryptCast;
use App\Models\Account\Customer;
use App\Models\Metadata;
use Illuminate\Database\Eloquent\Model;

class ProviderEntity extends Model
{
    protected $table = 'social_auth_providers';

    protected $fillable = [
        'name',
        'enabled',
        'client_id',
        'client_secret',
        'redirect_url',
    ];

    protected $attributes = [
        'enabled' => true,
    ];

    protected $casts = [
        'client_id' => EncryptCast::class,
        'client_secret' => EncryptCast::class,
    ];

    public static function getProviders()
    {
        return [new GoogleSocialAuthProvider, new DiscordSocialAuthProvider, new GithubSocialAuthProvider, new FaceBookSocialAuthProvider];
    }

    public function provider(): ?SocialAuthProviderInterface
    {
        $provider = collect($this->getProviders())->first(function (SocialAuthProviderInterface $provider) {
            return $provider->name() === $this->name;
        });
        if ($provider) {
            $class = get_class($provider);

            return new $class(['clientId' => $this->client_id, 'clientSecret' => $this->client_secret, 'redirectUri' => $this->redirect_url]);
        }
    }

    public function isSynced(): bool
    {
        if (auth('web')->guest()) {
            return false;
        }
        /** @var Customer $user */
        $user = auth('web')->user();

        return $user->getMetadata('social_'.$this->name) != null;
    }

    public static function linkedCustomers(string $name)
    {
        return Metadata::where('key', 'social_'.$name)->count();
    }
}
