<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/15 0015
 * Time: 上午 9:07
 */

namespace app\zmxy\controller;
use think\Controller;
include_once ZMXY_PATH.'zmop/ZmopClient.php';
include_once ZMXY_PATH.'Logger.php';
include_once ZMXY_PATH.'zmop/request/ZhimaCreditScoreGetRequest.php';
defined('SIGNTYPE') or define('SIGNTYPE','RSA');//签名方式,默认为RSA,可使用RSA2
class ZhimaCreditScoreGet extends controller
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
     * 查询芝麻信用分
     * @return 查询芝麻信用分等参数
     */
    public function zhimaQueryScore($pram)
    {
        $client = new \ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new \ZhimaCreditScoreGetRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setTransactionId($this->createTransactionId());// 必要参数
        $request->setProductCode("w1010100100000000001");// 必要参数
        $request->setOpenId($pram['open_id']);// 必要参数
        $response = $client->execute($request);
        return $response;
    }

    /**
     * 生成32位商户请求的唯一标志
     * @param string $definition商户请求的唯一标志前缀
     * @return 32位商户请求的唯一标志
     */
    private function createTransactionId($definition='XCQB'){
        $timestrap=time();
        $date=date('YmdHis',$timestrap);
        $radom=rand(1000,9999);//末尾4位随机数
        return $definition.$timestrap.$date.$radom;
    }
}