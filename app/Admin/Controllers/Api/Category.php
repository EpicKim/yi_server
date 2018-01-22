<?php
namespace App\Admin\Controllers\Api;

use Illuminate\Support\Facades\DB;

class Category{

    public function parentCates(){
        $sql="select id,name as text from category where is_show=1 and parent_id=0";
        $res=DB::select($sql);
        $data=[
            [
                'id'=>0,
                'text'=>'父分类',
            ]
        ];
        array_splice($res,0,0,$data);
        return $res;
    }

    public function getCates(){
        $sql="select id,name as text from category where is_show=1 and parent_id!=0";
        return DB::select($sql);
    }
}