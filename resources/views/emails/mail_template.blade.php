<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>订单邮件</title>
</head>
<body>
    <div id="container">
        <div id="header">
            <h1 style="text-align:center">订单邮件</h1>
            <h3>
                订单编号: {{$order_code}}<br/>
                买家留言:@if($seller_message=='') 无 @else {{$seller_message}}@endif<br/>
                订单总价: {{$total_price}}<br/>
                收货地址: {{$address['province']['name']}} {{$address['city']['name']}} {{$address['area']['name']}} {{$address['info']['detail']}}<br/>
                收货人: {{$address['info']['receiver']}} 电话: {{$address['info']['mobile']}}
            </h3>
        </div>

        <div id="main">
            <table cellpadding='0' cellspacing='0' border="1px solid">
                <tr>
                    <th>商品编号</th>
                    <th>商品名称</th>
                    <th>商品图片</th>
                    <th>数量</th>
                    <th>价格</th>
                    <th>尺寸</th>
                    <th>颜色</th>
                </tr>

                @foreach($shop_cart as $val)
                    <tr>
                        <td>{{$val['product_id']}}</td>
                        <td>{{$val['name']}}</td>
                        <td><img src="{{$val['thumb_img']}}" alt="{{$val['name']}}" style="width:30px;height:30px"></td>
                        <td>{{$val['num']}}</td>
                        <td>{{$val['price']}}</td>
                        <td>{{$val['size']}}</td>
                        <td>{{$val['color']}}</td>
                    </tr>
                @endforeach
            </table>
        </div>


    </div>

</body>
</html>