<?php

namespace App\Admin\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductImageModel;

use App\Models\ProductModel;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use App\Services\Oss;
use Intervention\Image\Facades\Image;

class ProductImageController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('商品图片管理');
            $content->description('列表...');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('商品图片管理');
            $content->description('编辑...');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('商品图片管理');
            $content->description('新增...');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ProductImageModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->product_id('商品id');
            $grid->column('商品名称')->display(function(){
                 $product_id=$this->product_id;
                 return ProductModel::find($product_id)->name;
            });
            $grid->thumb_img('缩略图')->image(50,50);
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->disableExport();

            $grid->filter(function($filter){
                $filter->equal('product_id','商品id');
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(ProductImageModel::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->display('product.name','商品名称');
            $form->text('product_id','商品id');
            $form->image('img','商品图片');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }

    public function store(Request $request){

        $product_id=$request->input('product_id');
        $img=$request->file('img');
        $time=date('Y-m-d H:i:s');
        $updated_at=$time;
        $created_at=$time;

        $model=new ProductImageModel();
        $model->product_id=$product_id;
        $model->updated_at=$updated_at;
        $model->created_at=$created_at;
        if($img){
            $data=$this->uploadImages($img);
            if($data){
                $model->img=$data[0];
                $model->thumb_img=$data[1];
            }
        }
        $model->save();

    }

    public function update(Request $request,$id){
        $product_id=$request->input('product_id');
        $time=date('Y-m-d H:i:s');
        $updated_at=$time;

        $model=ProductImageModel::find($id);

        $img=$request->file('img');
        if($img){
            $data=$this->uploadImages($img);
            if($data){
                Oss::deleteObject(config('alioss.product_dir') . CategoryModel::getFileName($model->img));
                Oss::deleteObject(config('alioss.product_dir') . CategoryModel::getFileName($model->thumb_img));

                $model->img=$data[0];
                $model->thumb_img=$data[1];
            }
        }
        $model->product_id=$product_id;
        $model->updated_at=$updated_at;
        $model->save();

        admin_toastr(trans('admin.save_succeeded'));
        $url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->resource(0);
        return redirect($url);

    }

    public function destroy($id){
        $model=ProductImageModel::find($id);
        if(ProductImageModel::destroy($id)){
            Oss::deleteObject(config('alioss.product_dir') . CategoryModel::getFileName($model->img));
            Oss::deleteObject(config('alioss.product_dir') . CategoryModel::getFileName($model->thumb_img));
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        }
        return response()->json([
            'status'  => false,
            'message' => trans('admin.delete_failed'),
        ]);
    }


    protected function uploadImages($pic){
        $ext = $pic->getClientOriginalExtension();
        if ($ext == 'jpg') {
            $ext = 'jpeg';
        }
        $key  = time() . uniqid() . '.' . $ext;
        $res1 = Oss::publicUpload(config('alioss.BucketName'), config('alioss.product_dir') . $key, $pic, [
            'ContentType' => 'application/' . $ext,
        ]);

        //生成缩略图并上传
        $tmp_pic = public_path('cate_images') . '/tmp.' . $ext;
        Image::make($pic)->resize(config('filesystems.thumb.width'), config('filesystems.thumb.height'))->save($tmp_pic);
        $key2 = config('filesystems.thumb.prefix') . time() . uniqid() . '.' . $ext;
        $res2 = Oss::publicUpload(config('alioss.BucketName'), config('alioss.product_dir') . $key2, $tmp_pic, [
            'ContentType' => 'application/' . $ext,
        ]);
        unlink($tmp_pic);

        if ($res1 && $res2) {
            $filepath   = config('alioss.prefix') . config('alioss.product_dir') . $key;
            $thumb_filepath   = config('alioss.prefix') . config('alioss.product_dir') . $key2;
            return [$filepath,$thumb_filepath];
        }

        return false;
    }


    public function resource($slice = -2)
    {
        $segments = explode('/', trim(app('request')->getUri(), '/'));

        if ($slice != 0) {
            $segments = array_slice($segments, 0, $slice);
        }

        return implode('/', $segments);
    }


}
