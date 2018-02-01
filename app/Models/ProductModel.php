<?php

namespace App\Models;

use App\Services\CommonService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductModel extends Model
{

    protected $table = 'product';

    public function product_img()
    {
        return $this->hasOne(ProductImageModel::class);
    }

    /**
     * 根据分类id获取商品列表
     * @param $categoryID
     * @param $page
     * @param $size
     * @return array
     */
    public static function productList($categoryID, $page, $size)
    {
        $ret['code'] = 1;
        $count = ProductModel::where('is_show', 1)->count('id');
        if (!$count) {
            $ret['error'] = '未查询到数据';
            return $ret;
        }
        $page_total = ceil($count / $size);     //总页数

        $offset = ($page - 1) * $size;
        $sql = "select p.id,p.name,p.price,p.original_price from product p
          where p.category_id=$categoryID and p.is_show=1 order by p.created_at desc limit $offset,$size";
        $data = DB::select($sql);
        $data = json_decode(json_encode($data), true);
        if (!$data) {
            $ret['error'] = '未查询到数据';
            return $ret;
        }

        $product_id_arr = [];
        foreach ($data as $val) {
            $product_id_arr[] = $val['id'];
        }
        $product_ids = join(',', $product_id_arr);
        $sql = "select product_id,thumb_img from product_img where product_id in($product_ids)";
        $product_img = DB::select($sql);
        $product_img = json_decode(json_encode($product_img), true);
        $product_img = CommonService::arrayKey($product_img, 'product_id');

        foreach ($data as &$val) {
            $val['product_img'] = $product_img[$val['id']]['thumb_img'];
        }

        return [
            'code' => 0,
            'data' => [
                'product' => $data,
                'page_total' => $page_total
            ],
        ];

    }

    /**
     * 根据id查询商品
     * @param $productID
     * @return array|bool
     */
    public static function getProductByID($productID)
    {
        $sql = "select p.id,p.name,p.price,p.original_price,p.size,p.color,pi.img,pi.thumb_img 
        from product p left join product_img pi on p.id=pi.product_id 
        where is_show=1 and p.id=?";
        $data = DB::select($sql, [$productID]);
        if (!$data) {
            return false;
        }

        $ret = [];
        $i = 0;
        foreach ($data as $val) {
            if ($i == 0) {
                $ret = [
                    'id' => $val->id,
                    'name' => $val->name,
                    'price' => $val->price,
                    'original_price' => $val->original_price,
                    'size' => [],
                    'color' => [],
                    'imgs' => [],
                ];
                if ($val->size) {
                    $size = explode('||', $val->size);
                    $ret['size'] = $size;
                }
                if ($val->color) {
                    $color = explode('||', $val->color);
                    $ret['color'] = $color;
                }
            }
            $ret['imgs'][] = [
                'thumb_img' => $val->thumb_img,
                'img' => $val->img,
            ];

            $i++;
        }
        return [
            'code'=>0,
            'data'=>$ret,
        ];

    }


}
