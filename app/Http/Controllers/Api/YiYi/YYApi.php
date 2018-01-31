<?php
namespace App\Http\Controllers\Api\YiYi;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class YYApi{

    public $ret=[
        'code'=>1
    ];

    //获取父分类
    public function parentCategories(){
        $data=CategoryModel::getParentCategories();
        if(!$data) {
            $this->ret['error'] = '未获取到分类';
            return $this->ret;
        }
        return $data;
    }

    //根据父id获取子分类
    public function getSonCategoriesByID(Request $request){
        $categoryID=$request->input('parentCategoryID',0);
        if(!$categoryID){
            $this->ret['error']='参数不正确';
            return $this->ret;
        }

        $data=CategoryModel::getSonCategoriesByID($categoryID);
        if(!$data){
            $this->ret['error']='未获取到分类';
            return $this->ret;
        }
        return $data;
    }

    //商品列表页面
    public function productList(Request $request){
        $categoryID=$request->input('categoryID',0);
        $page=$request->input('page',1);
        $size=$request->input('size',10);
        if(!$categoryID){
            $this->ret['error']='参数不正确';
            return $this->ret;
        }

        $data=ProductModel::productList($categoryID,$page,$size);
        return $data;

    }

    //商品详情
    public function productDetail(Request $request){
        $productID=$request->input('productID',0);
        if(!$productID){
            $this->ret['error']='参数不正确';
            return $this->ret;
        }
        $data=ProductModel::getProductByID($productID);
        if(!$data){
            $this->ret['error']='未查询出数据';
            return $this->ret;
        }
        return $data;

    }



}