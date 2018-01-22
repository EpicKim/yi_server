<?php
namespace App\Http\Controllers\Api\YiYi;

use App\Models\CategoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class YYApi{

    public $ret=[
        'code'=>1
    ];

    public function parentCategories(){
        $data=CategoryModel::getParentCategories();
        if(!$data) {
            $this->ret['error'] = '未获取到分类';
            return $this->ret;
        }
        return $data;
    }



}