<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@php($container = 'max-w-6xl')


@extends('layouts/auth')
@section('title', __('socialauth::messages.finish_title'))
@section('content')
    <div class="p-4 sm:p-7">
        <div class="text-center">
            <h1 class="block text-2xl font-bold text-gray-800 dark:text-white">{{ __('socialauth::messages.finish_title') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('socialauth::messages.finish_subtitle') }}
            </p>
        </div>

        <div class="mt-5">

            <form method="POST" action="{{ route('socialauth.finish') }}">
                @csrf

                @csrf
                <div class="space-y-12">
                    <div class="pb-6">
                        <div class="border-b border-gray-900/10 pb-6">
                            <div class="mt-5 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    @include("shared.input", ["name" => "firstname", "label" => __('global.firstname'), "value" => $firstname ?? old("firstname")])
                                </div>

                                <div class="sm:col-span-3">
                                    @include("shared.input", ["name" => "lastname", "label" => __('global.lastname'), "value" => $lastname ?? old("lastname")])
                                </div>

                                <div class="sm:col-span-3">
                                    @include("shared.input", ["name" => "email", "label" => __('global.email'), "type" => "email", "value" => $user->getEmail(), 'attributes' => ['readonly' => true]])
                                </div>

                                <div class="sm:col-span-3">
                                    @include("shared.input", ["name" => "phone", "label" => __('global.phone')])
                                </div>
                                <div class="sm:col-span-3">
                                    @include("shared.input", ["name" => "address", "label" => __('global.address')])
                                </div>
                                <div class="sm:col-span-2">
                                    @include("shared.input", ["name" => "address2", "label" => __('global.address2')])
                                </div>

                                <div class="sm:col-span-1">
                                    @include("shared.input", ["name" => "zipcode", "label" => __('global.zip')])
                                </div>

                                <div class="sm:col-span-2">
                                    @include("shared.select", ["name" => "country", "label" => __('global.country'), "options" => $countries, "value" => old("country", "FR")])
                                </div>

                                <div class="sm:col-span-2">
                                    @include("shared.input", ["name" => "city", "label" => __('global.city')])
                                </div>

                                <div class="sm:col-span-2">
                                    @include("shared.input", ["name" => "region", "label" => __('global.region')])
                                </div>
                                @if (setting('register.toslink'))
                                    <div class="sm:col-span-3 flex gap-x-6 mb-2">
                                        <div class="flex h-6 items-center">
                                            <input id="accept_tos" name="accept_tos" type="checkbox" class="h-4 w-4 rounded border-gray-300 @error("accept_tos") border-red-300 @enderror text-indigo-600 focus:ring-indigo-600">
                                        </div>
                                        <div class="text-sm leading-6">
                                            <label for="accept_tos" class="900 dark:text-white font-medium text-gray-900 ">{{ __('auth.register.accept') }} <a href="{{ setting('register.toslink') }}" class="text-indigo-600">{{ __('auth.register.terms') }}</a></label>
                                        </div>
                                    </div>
                                @endif

                                @if (isset($redirect))
                                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                                @endif
                            </div>
                        </div>
                        <button class="btn-primary block w-full">
                            {{ __('auth.register.btn') }}
                        </button>
                    </div>
                </div>

        </div>
    </div>

@endsection
