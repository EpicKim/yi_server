<?php

namespace App\Services;
use Illuminate\Support\Facades\Mail;
//use Aliyun\DySDKLite\SignatureHelper;
use App\Vendor\Sms_lite\SignatureHelper;
class NoticeService{

    public $order_code; //订单编号

    /**
     * 发送邮件
     * @param $shop_cart
     * @param $address
     * @param $seller_message
     * @param $total_price
     * @return bool
     */
    public function sendMail($shop_cart,$address,$seller_message,$total_price){
        $order_code=$this->order_code;

        $data=[
            'order_code'=>$order_code,
            'shop_cart'=>$shop_cart,
            'address'=>$address,
            'seller_message'=>$seller_message,
            'total_price'=>$total_price
        ];

        // Mail::send()的返回值为空，所以可以其他方法进行判断
        Mail::send('emails.mail_template',$data,function($msg) use($address){
            $to="tonyzxln@163.com";
            $msg->to($to)->subject("来自{$address['info']['receiver']}的订单");
        });

        // 返回的一个错误数组，利用此可以判断是否发送成功
        if(Mail::failures()){
            return false;
        }
        return true;

    }



    /**
     * 发送短信
     * @param $shop_cart
     * @param $address
     * @return bool
     */
    function sendSmS($shop_cart,$address) {

        $config=config('sms');

        $PhoneNumbers=$address['info']['mobile'];
        $order_code=$this->order_code;
        $params = array ();
        $product_list=$this->generateSMSContent($shop_cart);
        //$product_list="您的订单我们已经收到";

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $config['accessKeyId'];
        $accessKeySecret = $config['accessKeySecret'];

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $PhoneNumbers;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $config['SignName'];

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $config['TemplateCode'];

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "order_code" => $order_code,
            "product_list" => $product_list
        );

        // fixme 可选: 设置发送短信流水号
        //$params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        try{
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ))
            );
        }catch(\Exception $e){
            return false;
        }


        if($content->Code=='OK'){
            return true;
        }
        return false;
    }


    /**
     * 参数验证
     * @return bool
     */
    public function verify(){
       $args=func_get_args();
       if(!$args){
           return false;
       }
       foreach($args as $v){
           if(is_array($v) && count($v)==0){
               return false;
           }else if(!$v){
               return false;
           }
       }
       return true;

    }

    /**
     * 生成短信内容
     * @param $shop_cart
     * @return string
     */
    protected function generateSMSContent($shop_cart){

        $content='';
        foreach($shop_cart as $v){
            $content.=
                "商品名称：".$v['name']."，颜色：".$v['color']."，尺寸：".$v['size']."，单价：".$v['price']."，数量：".$v['num']."；";
        }
        $content=rtrim($content,'；');
        $content.="。稍后我们的客服经理会联系您！";
        return $content;

    }


}

