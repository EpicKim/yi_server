<?php

namespace App\Admin\Controllers;

use App\Models\CategoryModel;
use App\Models\FactoryModel;
use App\Models\ProductModel;

use App\Services\Oss;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Encore\Admin\Form\Builder;
use Illuminate\Support\Facades\Input;

class ProductController extends Controller
{
    use ModelForm;

    public $states
        = [
            'on'  => ['value' => 1, 'text' => '上架', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '下架', 'color' => 'danger'],
        ];
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('商品管理');
            $content->description('商品列表...');

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

            $content->header('商品列表');
            $content->description('商品编辑...');

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

            $content->header('商品列表');
            $content->description('商品创建...');

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
        return Admin::grid(ProductModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('商品名称')->sortable();
            $grid->category_id('分类')->display(function($category_id){
                $data=CategoryModel::find($category_id);
                if($data){
                    return $data->name;
                }
                return '';
            });
            $grid->factory_id('厂家')->display(function($factory_id){
                $data=FactoryModel::find($factory_id);
                if($data){
                    return $data->name;
                }
                return '';
            });
            $grid->price('现价')->sortable();
            $grid->original_price('原价')->sortable();
            $grid->is_show('是否上架')->display(function($is_show){
                if($is_show==1){
                    return '上架';
                }
                return '下架';
            });
            $grid->created_at('创建时间')->sortable();
            $grid->updated_at('修改时间')->sortable();


            $grid->disableExport();

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(ProductModel::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->select('category_id','分类')->options('/admin/api/getcates');
            $form->select('factory_id','厂家')->options('/admin/api/getfactories');
            $form->text('name','商品名称');
            $form->switch('is_show','是否上架')->states($this->states)->default(1);
            $form->text('price','现价');
            $form->text('original_price','原价');
            $form->multipleImage('img','商品图片')->removable();

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '修改时间');
        });
    }


    public function store(Request $request){

        $category_id=$request->input('category_id');
        $factory_id=$request->input('factory_id');
        $name=$request->input('name');
        $is_show=$request->input('is_show')=='on'?1:0;
        $price=$request->input('price');
        $original_price=$request->input('original_price');
        $time=date('Y-m-d H:i:s');
        $updated_at=$time;
        $created_at=$time;

        $model=new ProductModel();
        $model->category_id=$category_id;
        $model->factory_id=$factory_id;
        $model->name=$name;
        $model->is_show=$is_show;
        $model->price=$price;
        $model->original_price=$original_price;
        $model->updated_at=$updated_at;
        $model->created_at=$created_at;

        if($model->save()){
            //保存成功,上传图片并写入product_img
            $product_id=$model->id;

            $pic   = $request->file('img');
            if($pic){
                if(is_array($pic)){
                    $sql_pre='insert into product_img(product_id,img,thumb_img,updated_at,created_at) values';
                    $sql_val='';
                    foreach($pic as $val){
                        $data=$this->uploadImages($val);
                        if($data){
                            $sql_val.=",($product_id,'{$data[0]}','{$data[1]}','$time','$time')";
                        }
                    }
                    if($sql_val){
                        $sql_val=substr($sql_val,1);
                        DB::insert($sql_pre.$sql_val);
                    }

                }else{
                    $data=$this->uploadImages($pic);
                    if(!$data){
                        $sql="insert into product_img(product_id,img,thumb_img,updated_at,created_at) 
                          values($product_id,'{$data[0]}','{$data[1]}','$time','$time')";
                        DB::insert($sql);
                    }

                }

            }

        }

    }


    /**
     * 这个方法中没有删除oss的图片,原因控件中的图片不显示
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id){
        $category_id=$request->input('category_id');
        $factory_id=$request->input('factory_id');
        $name=$request->input('name');
        $is_show=$request->input('is_show')=='on'?1:0;
        $price=$request->input('price');
        $original_price=$request->input('original_price');
        $time=date('Y-m-d H:i:s');
        $updated_at=$time;
        $created_at=$time;

        $model=ProductModel::find($id);
        $model->category_id=$category_id;
        $model->factory_id=$factory_id;
        $model->name=$name;
        $model->is_show=$is_show;
        $model->price=$price;
        $model->original_price=$original_price;
        $model->updated_at=$updated_at;
        $model->created_at=$created_at;

        $model->save();
        admin_toastr(trans('admin.save_succeeded'));
        $url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->resource(0);
        return redirect($url);

    }

    public function destroy($id){

        //删除product_img,product中数据并删除oss上对应的图片
        $data=DB::select("select img,thumb_img from product_img where product_id=$id");
        ProductModel::destroy($id);
        if($data){
            foreach($data as $val){
                $img=$val->img;
                $thumb_img=$val->thumb_img;
                Oss::deleteObject(config('alioss.product_dir') . CategoryModel::getFileName($img));
                Oss::deleteObject(config('alioss.product_dir') . CategoryModel::getFileName($thumb_img));
            }
            DB::delete("delete from product_img where product_id=?",[$id]);
        }
        return response()->json([
            'status'  => true,
            'message' => trans('admin.delete_succeeded'),
        ]);

    }


    /**
     * 上传图片至oss
     * @param $pic
     * @return array|bool
     */
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
