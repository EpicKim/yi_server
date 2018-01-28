<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//分类接口
Route::get('/yiyi/category/parent','Api\YiYi\YYApi@parentCategories');
Route::get('/yiyi/category/son','Api\YiYi\YYApi@getSonCategoriesByID');

//商品接口
Route::get('yiyi/product/list','Api\YiYi\YYApi@productList');
