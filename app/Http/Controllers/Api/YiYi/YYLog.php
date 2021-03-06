<?php
/* 记录log */
namespace App\Http\Controllers\Api\YiYi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YYLog{

    /**
     * 记录分类的点击次数
     * @param Request $request
     * @return array
     */
    public function categoryLog(Request $request){
        $category_id=$request->input('category_id',0);
        if(!$category_id){
            return [
                'code'=>1,
                'error'=>'参数不正确'
            ];
        }
        $ip=$_SERVER['REMOTE_ADDR'];
        $click_time=date("Y-m-d H:i:s");

        $sql="insert into category_log(category_id,click_time,ip) values(?,?,?)";
        DB::insert($sql,[$category_id,$click_time,$ip]);

        $sql="update category set view=view+1 where id=?";
        DB::update($sql,[$category_id]);
        return [
            'code'=>0,
            'data'=>''
        ];

    }


    /**
     * 商品点击log
     * @param Request $request
     * @return array
     */
    public function productLog(Request $request){

        $product_id=$request->input('product_id');
        if(!$product_id){
            return [
                'code'=>1,
                'error'=>'参数不正确'
            ];
        }
        $ip=$_SERVER['REMOTE_ADDR'];
        $click_time=date("Y-m-d H:i:s");
        $sql="insert into product_log(product_id,click_time,ip) values(?,?,?)";
        DB::insert($sql,[$product_id,$click_time,$ip]);

        $sql="update product set view=view+1 where id=?";
        DB::update($sql,[$product_id]);
        return [
            'code'=>0,
            'data'=>'',
        ];



    }


}