<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'users'], function () use ($router) {
    $router->get('', ['uses' => 'UsersController@filters']);
    $router->post('', ['uses' => 'UsersController@create']);
    $router->post('consumers', ['uses' => 'UsersController@createConsumers']);
    $router->post('sellers', ['uses' => 'UsersController@createSellers']);
    $router->get('{user_id}', ['uses' => 'UsersController@show']);
});

$router->group(['prefix' => 'transactions'], function () use ($router) {
    $router->get('{id}', ['uses' => 'TransactionsController@show']);
    $router->post('', ['uses' => 'TransactionsController@create']);
});
