<?php

namespace App\Admin\Controllers;

use App\Models\CategoryModel;

use App\Services\Oss;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Intervention\Image\Facades\Image;
use Encore\Admin\Form\Builder;

class CategoryController extends Controller
{
    use ModelForm;

    public $states
        = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('分类管理');
            $content->description('分类列表');

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

            $content->header('分类管理');
            $content->description('分类编辑...');

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

            $content->header('分类管理');
            $content->description('创建分类');

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
        return Admin::grid(CategoryModel::class, function (Grid $grid) {

            $grid->id('id')->sortable();
            $grid->name('分类名称');
            $grid->thumb_img('缩略图')->image();
            $grid->parent_id('父分类id');
            $grid->column('父分类')->display(function(){
                $pid=$this->parent_id;
                if ($pid == 0) {
                    return '父分类';
                }
                return CategoryModel::find($pid)->name;
            });

            $grid->is_show('显示')->display(function ($is_show) {
                if ($is_show) {
                    return '显示';
                } else {
                    return '隐藏';
                }
            });
            $grid->z_index('排序');

            $grid->created_at('创建时间');
            $grid->updated_at('修改时间');

            $grid->disableExport();
            $grid->filter(function($filter){
                $filter->like('name','分类名称');
                $filter->equal('is_show','显示为1,不显示为0');
                $filter->equal('parent_id','父分类id');
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
        return Admin::form(CategoryModel::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', '分类名称');
            $form->select('parent_id', '父分类')->options('/admin/api/parentcates');
            $form->switch('is_show', '是否显示')->states($this->states)->default(1);
            $form->text('z_index', '排序');
            $form->image('img', '分类图片');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

    public function store(Request $request)
    {
        $name      = $request->input('name', '');
        $parent_id = $request->input('parent_id') ? $request->input('parent_id') : 0;
        $is_show   = $request->input('is_show') == 'on' ? 1 : 0;
        $z_index   = $request->input('z_index', 0);

        $time       = date('Y-m-d H:i:s');
        $updated_at = $time;
        $created_at = $time;

        $model = new CategoryModel();
        $pic   = $request->file('img');
        if ($pic) {
            $ext = $pic->getClientOriginalExtension();
            if ($ext == 'jpg') {
                $ext = 'jpeg';
            }
            $key  = time() . uniqid() . '.' . $ext;
            $res1 = Oss::publicUpload(config('alioss.BucketName'), config('alioss.dir') . $key, $pic, [
                'ContentType' => 'application/' . $ext,
            ]);

            //生成缩略图并上传
            $tmp_pic = public_path('cate_images') . '/tmp.' . $ext;
            Image::make($pic)->resize(config('filesystems.thumb.width'), config('filesystems.thumb.height'))->save($tmp_pic);
            $key2 = config('filesystems.thumb.prefix') . time() . uniqid() . '.' . $ext;
            $res2 = Oss::publicUpload(config('alioss.BucketName'), config('alioss.dir') . $key2, $tmp_pic, [
                'ContentType' => 'application/' . $ext,
            ]);
            unlink($tmp_pic);

            if ($res1 && $res2) {
                $filepath   = config('alioss.prefix') . config('alioss.dir') . $key;
                $model->img = $filepath;

                $thumb_filepath   = config('alioss.prefix') . config('alioss.dir') . $key2;
                $model->thumb_img = $thumb_filepath;
            }
        }


        $model->name       = $name;
        $model->parent_id  = $parent_id;
        $model->is_show    = $is_show;
        $model->z_index    = $z_index;
        $model->updated_at = $updated_at;
        $model->created_at = $created_at;

        $model->save();

    }


    public function update(Request $request, $id)
    {
        $name      = $request->input('name', '');
        $parent_id = $request->input('parent_id') ? $request->input('parent_id') : 0;
        $is_show   = $request->input('is_show') == 'on' ? 1 : 0;
        $z_index   = $request->input('z_index', 0);

        $time       = date('Y-m-d H:i:s');
        $updated_at = $time;
        $created_at = $time;

        $model = CategoryModel::find($id);
        $pic   = $request->file('img');
        if ($pic) {
            $ext = $pic->getClientOriginalExtension();
            if ($ext == 'jpg') {
                $ext = 'jpeg';
            }
            $key  = time() . uniqid() . '.' . $ext;
            $res1 = Oss::publicUpload(config('alioss.BucketName'), config('alioss.dir') . $key, $pic, [
                'ContentType' => 'application/' . $ext,
            ]);
            Oss::deleteObject(config('alioss.dir') . CategoryModel::getFileName($model->img));

            //生成缩略图并上传
            $tmp_pic = public_path('cate_images') . '/tmp.' . $ext;
            Image::make($pic)->resize(config('filesystems.thumb.width'), config('filesystems.thumb.height'))->save($tmp_pic);
            $key2 = config('filesystems.thumb.prefix') . time() . uniqid() . '.' . $ext;
            $res2 = Oss::publicUpload(config('alioss.BucketName'), config('alioss.dir') . $key2, $tmp_pic, [
                'ContentType' => 'application/' . $ext,
            ]);
            Oss::deleteObject(config('alioss.dir') . CategoryModel::getFileName($model->thumb_img));
            unlink($tmp_pic);

            if ($res1 && $res2) {
                $filepath   = config('alioss.prefix') . config('alioss.dir') . $key;
                $model->img = $filepath;

                $thumb_filepath   = config('alioss.prefix') . config('alioss.dir') . $key2;
                $model->thumb_img = $thumb_filepath;
            }
        }

        $model->name       = $name;
        $model->parent_id  = $parent_id;
        $model->is_show    = $is_show;
        $model->z_index    = $z_index;
        $model->updated_at = $updated_at;
        $model->created_at = $created_at;

        $model->save();

        admin_toastr(trans('admin.save_succeeded'));
        $url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->resource(0);
        return redirect($url);

    }


    public function destroy($id)
    {

        $model     = CategoryModel::find($id);
        $img       = $model->img;
        $thumb_img = $model->thumb_img;

        if (CategoryModel::destroy($id)) {
            Oss::deleteObject(config('alioss.dir') . CategoryModel::getFileName($img));
            Oss::deleteObject(config('alioss.dir') . CategoryModel::getFileName($thumb_img));
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }

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
