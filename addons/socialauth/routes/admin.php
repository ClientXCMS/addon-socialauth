<?php

/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
Route::post('/socialauth/enable/{provider}', [\App\Addons\SocialAuth\Controllers\AdminSocialAuthController::class, 'enable'])->name('enable');
Route::post('/socialauth/disable/{provider}', [\App\Addons\SocialAuth\Controllers\AdminSocialAuthController::class, 'disable'])->name('disable');
Route::get('/socialauth/{providerEntity:name}', [\App\Addons\SocialAuth\Controllers\AdminSocialAuthController::class, 'show'])->name('show');
Route::post('/socialauth/{providerEntity:name}', [\App\Addons\SocialAuth\Controllers\AdminSocialAuthController::class, 'update']);
