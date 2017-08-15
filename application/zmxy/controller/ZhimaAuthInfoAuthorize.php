<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/13
 * Time: 20:20
 */

namespace app\zmxy\controller;
use think\Controller;
include_once ZMXY_PATH.'zmop/ZmopClient.php';
include_once ZMXY_PATH.'Logger.php';
include_once ZMXY_PATH.'zmop/request/ZhimaAuthInfoAuthorizeRequest.php';
defined('SIGNTYPE') or define('SIGNTYPE','RSA');//签名方式,默认为RSA,可使用RSA2
class ZhimaAuthInfoAuthorize extends Controller
{
//芝麻信用网关地址
    public $gatewayUrl = "https://zmopenapi.zmxy.com.cn/openapi.do";
    //商户私钥文件
    public $privateKeyFile;
    //芝麻公钥文件
    public $zmPublicKeyFile;
    //数据编码格式
    public $charset = "UTF-8";
    //芝麻分配给商户的 appId
    public $appId = "1000668";
    //log日志对象
    public $logger;
    //构造函数
    public function __construct()
    {
        $this->logger=new \logger();
        if(VENDOR_STATUS=='dev'){
            $this->logger->setLogLevel(4);//设置日志等级在开发模式下为debug最高级
            $this->gatewayUrl="https://zmopenapi.zmxy.com.cn/openapi.do";//测试状态网关地址
        }
        if(file_exists(ZMXY_PATH.'zmop/key/private_key_'.SIGNTYPE.'.pem')&&file_exists(ZMXY_PATH.'zmop/key/private_key_'.SIGNTYPE.'.pem')){
            $this->privateKeyFile=ZMXY_PATH.'zmop/key/private_key_'.SIGNTYPE.'.pem';//商户私钥
            $this->zmPublicKeyFile=ZMXY_PATH.'zmop/key/alipay_public_key_'.SIGNTYPE.'.pem';//芝麻公钥
            $this->logger->info('初始化加载'.SIGNTYPE.'商户私钥and芝麻公钥成功！');
        }else{
            $this->logger->warn(SIGNTYPE.'商户公钥或者私钥文件不存在！');
            return $this->error('获取芝麻授权失败！');
        }
    }

    /**
     * 芝麻认证
     * @return bool|mixed H5url路径
     */
    public function zhimaAuthInfo($pram)
    {
        $client = new \ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new \ZhimaAuthInfoAuthorizeRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setIdentityType("2");// 必要参数
//        $pram['real_name']='田鑫';
//        $pram['idcard']='321023199507252612';
        $request->setIdentityParam("{\"name\":\"".$pram['real_name']."\",\"certType\":\"IDENTITY_CARD\",\"certNo\":\"".$pram['idcard']."\",\"state\":\"".$pram['uid']."\"}");// 必要参数
        $request->setBizParams("{\"auth_code\":\"M_H5\",\"channelType\":\"app\"}");//
        $url = $client->generatePageRedirectInvokeUrl($request);
        return $url;
    }

}