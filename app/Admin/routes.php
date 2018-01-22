<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('category', CategoryController::class);   //分类模块
    $router->resource('product',ProductController::class);      //商品模块
    $router->resource('product_img',ProductImageController::class); //产品图片模块
    $router->resource('factory',FactoryController::class);      //厂家模块

});


//路由api
Route::group([
    'prefix' => config('admin.route.prefix') . '/api',
    'namespace' => config('admin.route.namespace') . '\\Api',
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('parentcates', 'Category@parentCates');    //获取父分类
    $router->get('getcates','Category@getCates');           //获取分类
    $router->get('getfactories','Factory@getFactories');    //获取厂家

});
