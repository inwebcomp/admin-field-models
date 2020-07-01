<?php

Route::group(['prefix' => 'fields/models/{resource}/{resourceId}/{relatedResource}'], function () {
    Route::get('', 'FieldController@index');
    Route::get('create', 'FieldController@create');
    Route::put('update', 'FieldController@update');
    Route::post('store', 'FieldController@store');
    Route::put('positions', 'FieldController@updatePositions');
    Route::delete('{relatedResourceId}', 'FieldController@destroy');
});