<?php
/* 发送通知接口 */

namespace App\Http\Controllers\Api\YiYi;

use Illuminate\Http\Request;

use App\Services\NoticeService;

class YYNotice{

    protected $notice;
    public function __construct(NoticeService $notice){
        $this->notice=$notice;
    }

    /**
     * 发送邮件与短信接口
     * @param Request $request
     * @return array
     */
    public function mailAndSMS(Request $request){

        $shop_cart=$request->input('shop_cart','');
        $address=$request->input('address','');
        $seller_message=$request->input('seller_message','');
        $total_price=$request->input('total_price',0);

        $res=$this->notice->verify($shop_cart,$address,$total_price);
        if(!$res){
            return ['code'=>1,'error'=>'订单信息不完整'];
        }

        //发送邮件与短信通知
        $this->notice->order_code=date('YmdHis').rand(100,999);
        $mail=$this->notice->sendMail($shop_cart,$address,$seller_message,$total_price);
        if(!$mail){
            return ['code'=>1,'error'=>'邮件发送失败'];
        }
        $sms=$this->notice->sendSMS($shop_cart,$address);
        if(!$sms){
            return ['code'=>1,'error'=>'短信发送失败'];
        }

        return ['code'=>0,'msg'=>'订单提交成功'];

    }

}


