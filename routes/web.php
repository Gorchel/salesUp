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

$router->get('/test', ['uses' => 'TestController@index']);
$router->get('/getLogs', ['uses' => 'WebhookController@getLogs']);
$router->get('/webhook', ['uses' => 'WebhookController@webhook']);
$router->get('/webhook_objects', ['uses' => 'WebhookController@webhookObjects']);
$router->post('/webhook_objects', ['uses' => 'WebhookController@webhookPostObjects']);

$router->get('/webhook_deals_objects', ['uses' => 'WebhookObjectsController@webhookUpdateObjectsContacts']);
$router->get('/weebhook_estate_filter', ['uses' => 'WebhookObjectsController@webhookEstateFilter']);
$router->get('/weebhook_estate_get', ['uses' => 'WebhookObjectsController@webhookEstateGet']);

$router->get('/copy', ['uses' => 'WebhookController@copyContactsView']);
