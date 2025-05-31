<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
Route::get('auth/{provider}', [\App\Addons\SocialAuth\Controllers\SocialAuthController::class, 'authorizeProvider'])->name('authorize');
Route::get('auth/{provider}/unlink', [\App\Addons\SocialAuth\Controllers\SocialAuthController::class, 'unlinkProvider'])->middleware('auth')->name('unlink');
Route::get('auth/{provider}/callback', [\App\Addons\SocialAuth\Controllers\SocialAuthController::class, 'callback'])->name('callback');
Route::get('socialauth/finish', [\App\Addons\SocialAuth\Controllers\SocialAuthController::class, 'finish'])->name('finish');
Route::post('socialauth/finish', [\App\Addons\SocialAuth\Controllers\SocialAuthController::class, 'finishSignup']);
