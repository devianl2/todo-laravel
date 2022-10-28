<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {

    // Future extension: add register, login, change password, forget password route.

    // Frontend should have it own way to obtain the token from social login, then pass it to route below
    // for validation. The route below will callback to social platform to check the validity of token.
    // Common frontend (react/angular) social library could obtain token easily.
    Route::post('social/{platform}/callback', [AuthController::class, 'socialTokenValidate']);

    // Only logged in user could signout
    Route::post('/sign-out', [AuthController::class, 'signOut'])->middleware(['auth:api']);
});

Route::group(['middleware' => ['auth:api']], function ()
{
    // Todo routes
    Route::prefix('todo')->group(function ()
    {
        Route::controller(TodoController::class)->group(function ()
        {
            Route::get('/list','todoList');
            Route::post('/{id?}', 'todoStore');
            Route::post('/{id}/status', 'statusUpdate');
            Route::delete('/{id}', 'todoDelete');
        });
    });
});
