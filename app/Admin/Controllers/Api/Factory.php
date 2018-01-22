<?php
namespace App\Admin\Controllers\Api;

use Illuminate\Support\Facades\DB;

class Factory{

    //获取厂家
    public function getFactories(){
        return DB::select('select id,name as text from factory');
    }
}