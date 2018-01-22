<?php

namespace App\Models;

use App\Services\CommonService;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{

    protected $table='category';

    public static function getFileName($url){

        return substr($url,strrpos($url,'/')+1);

    }



    /**
     * 获取分类
     * @return array|bool
     */
    public static function getParentCategories(){

        //获取父分类
        $parent=self::where('parent_id',0)
            ->where('is_show',1)
            ->orderBy('z_index','asc')
            ->select(['id','name','img','thumb_img'])
            ->get()
            ->toArray();
        if(!$parent){
            return false;
        }
        return [
            'code'=>0,
            'data'=>$parent,
        ];
//        $parent=CommonService::arrayKey($parent,'id');
//        if(!$parent){
//            return false;
//        }

//        $son=self::where('parent_id','<>',1)
//            ->where('is_show',1)
//            ->orderBy('z_index','asc')
//            ->select(['id','name','img','thumb_img','parent_id'])
//            ->get()
//            ->toArray();
//        if(!$son){
//            return false;
//        }
//
//        $data=[];
//
//        foreach($parent as $parent_id=>$p){
//            $data[$parent_id]=$p;
//            foreach($son as $s){
//                if($s['parent_id']==$parent_id){
//                    $data[$parent_id]['son'][]=$s;
//                }
//            }
//        }

    }


}
