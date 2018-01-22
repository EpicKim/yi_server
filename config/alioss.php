<?php
return [
    'ossServer'         => env('ALIOSS_SERVER', 'http://oss-cn-shanghai.aliyuncs.com/'),                                // 外网
    'ossServerInternal' => env('ALIOSS_SERVERINTERNAL', 'http://oss-cn-shanghai-internal.aliyuncs.com/'),       // 内网
    'AccessKeyId'       => env('ALIOSS_KEYID', 'LTAIMsfZU8hfWYHc'),                                                   // key
    'AccessKeySecret'   => env('ALIOSS_KEYSECRET', 'BStTrw3qDlq3inMqmEabGfy5zEkgQ1'),                             // secret
    'BucketName'        => env('ALIOSS_BUCKETNAME', 'wxyiyi'),                                                        // bucket

    //文件夹
    'dir'               => env('CATEGORY_DIR', 'clothes/category/'),    //分类图片目录
    'product_dir'       => 'clothes/product/',          //产品图片目录


    'prefix'            => env('ALIOSS_PREFIX', 'http://wxyiyi.oss-cn-shanghai.aliyuncs.com/'),
    'city'              => '上海',
];