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

$router->get('/getLogs', ['uses' => 'WebhookController@getLogs']);
$router->get('/webhook', ['uses' => 'WebhookController@webhook']);
$router->get('/webhook_objects', ['uses' => 'WebhookController@webhookObjects']);
$router->post('/webhook_objects', ['uses' => 'WebhookController@webhookPostObjects']);

$router->get('/webhook_deals_objects', ['uses' => 'WebhookController@webhookUpdateObjectsContacts']);
$router->get('/weebhook_estate_filter', ['uses' => 'WebhookController@webhookEstateFilter']);
