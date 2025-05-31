<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
?>
@extends('admin.settings.sidebar')
@section('title', __('socialauth::messages.modulename'))
@section('setting')
    <div class="container mx-auto">
    @include('admin/shared/alerts')
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">

                    <div class="grid sm:grid-cols-4 gap-4">
                    @foreach($providers as $item)
                        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-slate-900 dark:border-gray-700 dark:shadow-slate-700/[.7]">
                            <img class="w-50 h-1/2 mx-auto rounded-t-xl mt-2 mb-2" style="max-height: 147px;" src="{{ $item->logo() }}" alt="{{ $item->title() }}">
                            <div class="p-4 md:p-5">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white text-center">
                                    {{ $item->title() }}
                                </h3>
                                <p class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-400 mt-2">{{ __('socialauth::messages.count_linked', ['count' => \App\Addons\SocialAuth\Models\ProviderEntity::linkedCustomers($item->name())]) }}</p>

                            @if (!in_array($item->name(), $enabledProviders))

                                    <form action="{{ route('admin.socialauth.enable', $item->name()) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <button type="submit" class="btn-primary w-full mt-3">
                                            <i class="bi bi-arrow-repeat"></i>
                                            {{ __('extensions.settings.enable') }}
                                        </button>
                                    </form>
                                @else

                                    <form class="flex " action="{{ route('admin.socialauth.disable', $item->name()) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <button type="submit" class="btn-danger mt-3 mr-2" style="width: 50%">
                                            <i class="bi bi-ban"></i>
                                            {{ __('extensions.settings.disable') }}
                                        </button>

                                        <a href="{{ route('admin.socialauth.show', $item->name()) }}"  style="width: 50%" class="inline btn-info mt-3">
                                            <i class="bi bi-arrow-repeat"></i>
                                            {{ __('socialauth::messages.configurate') }}
                                        </a>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
