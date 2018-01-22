<?php

namespace App\Admin\Controllers;

use App\Models\FactoryModel;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class FactoryController extends Controller
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

            $content->header('厂家管理');
            $content->description('厂家列表...');

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

            $content->header('厂家管理');
            $content->description('厂家编辑...');

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

            $content->header('厂家管理');
            $content->description('厂家新增...');

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
        return Admin::grid(FactoryModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('厂家名称');
            $grid->boss('老板');
            $grid->telephone('手机号');
            $grid->address('地址')->display(function($address){
                return mb_substr($address,0,10).'...';
            });
            $grid->mark('备注')->display(function($mark){
                if($mark){
                    return mb_substr($mark,0,10).'...';
                }
                return '';
            })->label('warning');
            $grid->created_at('创建时间')->sortable();
            $grid->updated_at('编辑时间')->sortable();

            $grid->disableExport();
            $grid->filter(function($filter){
                $filter->like('name','厂家名称');
                $filter->equal('telephone','手机号');
                $filter->equal('boss','老板');
                $filter->like('address','地址');
                $filter->like('mark','备注');
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
        return Admin::form(FactoryModel::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name','厂家名称')->rules('required');
            $form->text('boss','老板')->rules('required');
            $form->text('telephone','手机号');
            $form->text('address','地址');
            $form->text('mark','备注');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
