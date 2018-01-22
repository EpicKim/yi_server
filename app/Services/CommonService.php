<?php
namespace App\Services;

class CommonService{

    /**
     * 生成指定下标的key的数组 二维数组
     * @param $arr
     * @param $key
     * @return bool
     */
    public static function arrayKey($arr,$key){
        if(!$arr || !$key){
            return false;
        }
        $data=[];
        foreach($arr as $val){
            if(!isset($val[$key])){
                return false;
            }
            $data[$val[$key]]=$val;
        }
        return $data;

    }

}