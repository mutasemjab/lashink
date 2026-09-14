<?php


use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::get('/', fn () => redirect('/admin'));

Route::group([
    'prefix'     => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'website.mode'],
], function () {

   

});
