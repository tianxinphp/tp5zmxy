<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14 0014
 * Time: 下午 4:00
 */

namespace app\zmxy\controller;
use think\Controller;
use think\Request;
include_once ZMXY_PATH.'zmop/ZmopClient.php';
include_once ZMXY_PATH.'Logger.php';
include_once ZMXY_PATH.'zmop/request/ZhimaAuthInfoAuthorizeRequest.php';
defined('SIGNTYPE') or define('SIGNTYPE','RSA');//签名方式,默认为RSA,可使用RSA2

class ZmxyAuthRetrun extends controller
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
    public function zmxyRetrun()
    {
        $request = Request::instance();
        //从回调URL中获取params参数，此处为示例值
        $params=$request->only(['params']);
        //从回调URL中获取sign参数，此处为示例值
        $sign=$request->only(['sign']);
        $params = 'gRvUAUCxxlXvVnsKnpf8yHfN6aWmAust31Jm0%2Fg0yvwAtHedKQkkGI8%2BvVB8o8%2FurEeixnY3Rmt6R0GzWFsKOAGz7kkOmu0ZGK1qG0sA%2BWJgz0FK4UyXS%2FyrZsu0BzRVFjLVUxSwKpUY6QJBQI3woygloS%2F3Z6bWyrb7Mrw2979P2lvcURD5%2Fom%2B3YBp%2Fpr3aPjKpIOAKlGZrTDqDeaHsbUmnhHAD4RFcYEnqRco38NNtgLEyZtZ1pinVj8QRa9y5xTjEigcWSBUlcrMGe3piQd0cwGI4B4J8wH%2FfREoILH2p3R61LASzsM6OEH7s5U1ZrtmvmgLNmsHQLGpEogl2A%3D%3D';
        $sign = 'WEC7%2FwdlrTXF0KzB1j7Y0K7UM4m0jkd0e78Q6huqDEdgCxrWvSMqzIyS5ax7aIErQqeO37MJpiaJt627OWVcVeoHnZGB8A8GNKCDykN%2FZ5cb9GPd2yb2az2ZpCrJyArjWErGMRy3gSyrA%2B2mpfW1l1otMDtQzldoStwPPsu83oQ%3D';
        // 判断串中是否有%，有则需要decode
        $params = strstr ( $params, '%' ) ? urldecode ( $params ) : $params;
        $sign = strstr ( $sign, '%' ) ? urldecode ( $sign ) : $sign;

        $client = new \ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $result = $client->decryptAndVerifySign ( $params, $sign );
        return $result;
    }
}