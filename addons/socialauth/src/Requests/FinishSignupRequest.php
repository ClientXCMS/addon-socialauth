<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */

namespace App\Addons\SocialAuth\Requests;

use Session;

class FinishSignupRequest extends \Illuminate\Foundation\Http\FormRequest
{
    public function authorize()
    {
        return Session::has('social_user');
    }

    public function rules()
    {
        $rules = \App\Services\Account\AccountEditService::rules($this->country, true);
        if (setting('register_toslink')) {
            $rules['accept_tos'] = ['accepted'];
        }

        return $rules;
    }
}
