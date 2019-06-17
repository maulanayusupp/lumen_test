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

// Generate Key
$router->get('/key', function() {
    return str_random(32);
});

// Auth
$router->group(['prefix' => 'auth'], function () use ($router) {
    // Login
    $router->post('login', 'AuthController@login');
    $router->get('me', 'AuthController@me');
});

// Authenticated
$router->group(['middleware' => 'auth'], function () use ($router) {
    // Users
    $router->get('users', 'UserController@list');
    $router->post('users', 'UserController@store');
    $router->get('users/{id}/show', 'UserController@show');
    $router->patch('users/{id}/update', 'UserController@update');
    $router->delete('users/{id}/delete', 'UserController@delete');
    $router->delete('users/bulk_delete', 'UserController@bulkDelete');
    $router->post('users/bulk_update', 'UserController@bulkUpdate');

    // Templates
    $router->get('templates', 'TemplateController@list');
    $router->post('templates', 'TemplateController@store');
    $router->get('templates/{id}/show', 'TemplateController@show');
    $router->patch('templates/{id}/update', 'TemplateController@update');
    $router->delete('templates/{id}/delete', 'TemplateController@delete');
    $router->delete('templates/bulk_delete', 'TemplateController@bulkDelete');
    $router->post('templates/bulk_update', 'TemplateController@bulkUpdate');

    // Checklists
    $router->get('checklists', 'ChecklistController@list');
    $router->get('checklists/{checklist_id}/items', 'ChecklistController@listByChecklistId');
    $router->post('checklists', 'ChecklistController@store');
    $router->get('checklists/{id}/show', 'ChecklistController@show');
    $router->patch('checklists/{id}/update', 'ChecklistController@update');
    $router->delete('checklists/{id}/delete', 'ChecklistController@delete');
    $router->delete('checklists/bulk_delete', 'ChecklistController@bulkDelete');
    $router->post('checklists/bulk_update', 'ChecklistController@bulkUpdate');

    // Items
    $router->get('items', 'ItemController@list');
    $router->post('items', 'ItemController@store');
    $router->get('items/{id}/show', 'ItemController@show');
    $router->patch('items/{id}/update', 'ItemController@update');
    $router->delete('items/{id}/delete', 'ItemController@delete');
    $router->delete('items/bulk_delete', 'ItemController@bulkDelete');
    $router->post('items/bulk_update', 'ItemController@bulkUpdate');
    $router->post('items/complete', 'ItemController@complete');
    $router->post('items/incomplete', 'ItemController@incomplete');
    $router->get('items/summaries', 'ItemController@summaries');
});

