<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('{path:.*}', function ($path, Illuminate\Http\Request $request) use ($app)
{
    return App\Services\PassThrough::makeRequest($request);
});

$app->post('{path:.*}', function ($path, Illuminate\Http\Request $request) use ($app)
{
    return App\Services\PassThrough::makeRequest($request);
});

$app->put('{path:.*}', function ($path, Illuminate\Http\Request $request) use ($app)
{
    return App\Services\PassThrough::makeRequest($request);
});

$app->delete('{path:.*}', function ($path, Illuminate\Http\Request $request) use ($app)
{
    return App\Services\PassThrough::makeRequest($request);
});
