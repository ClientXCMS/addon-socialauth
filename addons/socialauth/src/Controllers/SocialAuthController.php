<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Controllers;

use App\Addons\SocialAuth\Models\ProviderEntity;
use App\Addons\SocialAuth\Requests\FinishSignupRequest;
use App\Helpers\Countries;
use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Account\Customer;
use App\Models\Metadata;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Session;

class SocialAuthController extends AbstractCrudController
{
    public function authorizeProvider($provider)
    {
        $provider = ProviderEntity::where('name', $provider)->where('enabled', true)->first();
        if (! $provider) {
            abort(404);
        }
        $provider = $provider->provider();
        if (! $provider) {
            abort(404);
        }
        $url = $provider->getAuthorizationUrl();
        Session::put('oauth2state', $provider->getState());
        Session::put('oauth2pkceCode', $provider->getPkceCode());

        return redirect($url);
    }

    public function unlinkProvider($provider)
    {
        $provider = ProviderEntity::where('name', $provider)->first();
        if (! $provider) {
            abort(404);
        }
        $provider = $provider->provider();
        if (! $provider) {
            abort(404);
        }
        if (auth('web')->check()) {
            $customer = auth('web')->user();
            $customer->detachMetadata('social_'.$provider->name());
            $customer->detachMetadata('social_'.$provider->name().'_email');
            $customer->detachMetadata('social_'.$provider->name().'_refresh_token');

            return redirect()->route('front.profile.index')->with('success', __('socialauth::messages.unlinked'));
        }

        return redirect()->route('login')->with('error', __('socialauth::messages.error'));
    }

    public function callback($provider, Request $request)
    {
        /** @var ProviderEntity|null $provider */
        $provider = ProviderEntity::where('name', $provider)->where('enabled', true)->first();
        if (! $provider) {
            abort(404);
        }
        $provider = $provider->provider();
        if (! $provider) {
            abort(404);
        }
        if (empty(Session::get('oauth2state')) || empty($request->state) || Session::get('oauth2state') !== $request->state) {
            Session::forget('oauth2state');
            Session::forget('oauth2pkceCode');
            abort(403);
        }
        $provider->setPkceCode(Session::get('oauth2pkceCode'));
        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $request->code,
            ]);
            $user = $provider->getResourceOwner($token);
            $metadata = Metadata::where('key', 'social_'.$provider->name())->where('value', $user->getId())->first();
            /** @var Customer|null $customer */
            $customer = $metadata ? Customer::find($metadata->model_id) : null;
            // Si le client n'existe pas et que l'utilisateur n'est pas connecté
            if (! $customer && auth('web')->guest()) {
                // Si un compte existe déjà avec cet email, mais qu'il n'est pas lié à un compte social
                if ($user->getEmail() && $customer = Customer::where('email', $user->getEmail())->first()) {
                    return redirect()->route('login')->with('error', __('socialauth::messages.email_exists'));
                }
                Session::put('social_user', $user);
                Session::put('social_provider', $provider->name());

                return redirect()->route('socialauth.finish')->with('info', __('socialauth::messages.finish_signup'));
            }
            // Si le client existe et que l'utilisateur n'est pas connecté
            if ($customer && $customer->getMetadata('signup_social') && auth('web')->guest()) {
                \Auth::login($customer);
                event(new Login('web', $customer, false));

                return redirect()->route('front.client.index')->with('success', __('socialauth::messages.logged_in'));
            }
            // Si le client existe et que l'utilisateur est connecté
            if (auth('web')->check()) {
                if (Metadata::where('key', 'social_'.$provider->name())->where('value', $user->getId())->exists()) {
                    return redirect()->route('front.profile.index')->with('error', __('socialauth::messages.already_linked'));
                }
                $customer = auth('web')->user();
                $customer->attachMetadata('social_'.$provider->name(), $user->getId());
                if ($customer->getMetadata('signup_social') == null) {
                    $customer->attachMetadata('signup_social', true);
                }
                $customer->attachMetadata('social_'.$provider->name().'_email', $user->getEmail());
                $customer->attachMetadata('social_'.$provider->name().'_refresh_token', $token->getRefreshToken());

                return redirect()->route('front.profile.index')->with('success', __('socialauth::messages.linked'));
            }
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
        }

        return redirect()->route('login')->with('error', __('socialauth::messages.error'));
    }

    public function finish()
    {
        if (Session::has('social_user')) {
            $user = Session::get('social_user');
            $countries = Countries::names();
            $firstname = method_exists($user, 'getFirstName') ? $user->getFirstName() : null;
            $lastname = method_exists($user, 'getLastName') ? $user->getLastName() : null;

            return view('socialauth::finish', compact('user', 'countries', 'firstname', 'lastname'));
        }

        return redirect()->route('login');
    }

    public function finishSignup(FinishSignupRequest $request)
    {
        if (Session::has('social_user')) {
            $user = Session::get('social_user');
            $provider = Session::get('social_provider', 'discord');
            $customer = new Customer;
            $customer->fill($request->validated() + ['password' => Hash::make(\Str::random()), 'email' => $user->getEmail()]);
            $customer->save();
            $customer->attachMetadata('signup_social', true);
            $customer->attachMetadata('social_'.$provider, $user->getId());
            $customer->attachMetadata('social_'.$provider.'_email', $user->getEmail());
            $customer->attachMetadata('social_'.$provider.'_refresh_token', $request->refresh_token);
            $customer->markEmailAsVerified();
            $this->clearSession();
            \Auth::login($customer);
            event(new Login('web', $customer, false));

            return redirect()->route('front.client.index')->with('success', __('socialauth::messages.signup_success'));
        }

        return redirect()->route('login');
    }

    private function clearSession()
    {
        Session::forget('social_user');
        Session::forget('social_provider');
    }
}
