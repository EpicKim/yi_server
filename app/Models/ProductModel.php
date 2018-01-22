<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{

    protected $table='product';

    public function product_img(){
        return $this->hasOne(ProductImageModel::class);
    }


}
