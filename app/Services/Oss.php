<?php
namespace App\Services;

use JohnLui\AliyunOSS;

class Oss{
    private $city;

    // 经典网络 or VPC
    private $networkType = '经典网络';

    private $AccessKeyId;
    private $AccessKeySecret;
    private $ossClient;

    /**
     * 私有初始化 API，非 API，不用关注
     * @param boolean 是否使用内网
     */
    public function __construct($isInternal = false)
    {
        $this->city=config('alioss.city');
        $this->AccessKeyId=config('alioss.AccessKeyId');
        $this->AccessKeySecret=config('alioss.AccessKeySecret');

        if ($this->networkType == 'VPC' && !$isInternal) {
            throw new Exception("VPC 网络下不提供外网上传、下载等功能");
        }
        $this->ossClient = AliyunOSS::boot(
            $this->city,
            $this->networkType,
            $isInternal,
            $this->AccessKeyId,
            $this->AccessKeySecret
        );
    }


    /**
     * 使用外网上传文件
     * @param  string bucket名称
     * @param  string 上传之后的 OSS object 名称
     * @param  string 删除文件路径
     * @return mixed 上传是否成功
     */
    public static function publicUpload($bucketName, $ossKey, $filePath, $options = [])
    {
        $oss = new Oss();
        $oss->ossClient->setBucket($bucketName);
        return $oss->ossClient->uploadFile($ossKey, $filePath, $options);
    }

    /**
     * 内网
     * @param       $bucketName
     * @param       $ossKey
     * @param       $filePath
     * @param array $options
     * @return \Aliyun\OSS\Models\PutObjectResult
     */
    public static function upload($bucketName, $ossKey, $filePath, $options = [])
    {
        $oss = new Oss(true);
        $oss->ossClient->setBucket($bucketName);
        return $oss->ossClient->uploadFile($ossKey, $filePath, $options);
    }

    /**
     * 删除存储在oss中的文件
     *
     * @param string $ossKey 存储的key（文件路径和文件名）
     * @return
     */
    public static function deleteObject($ossKey)
    {
        $oss = new OSS();

        return $oss->ossClient->deleteObject(config('alioss.BucketName'), $ossKey);
    }

    /**
     * 获取上传以后的url链接
     * @param $ossKey
     * @return mixed
     */
    public static function getUrl($ossKey)
    {
        $oss = new Oss();
        $oss->ossClient->setBucket(config('alioss.BucketName'));
        return $oss->ossClient->getUrl($ossKey, new \DateTime("+1 day"));
    }
}