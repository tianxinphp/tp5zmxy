<?php
namespace app\zmxy\controller;
use think\Controller;
include ZMXY_PATH.'zmop/ZmopClient.php';
include ZMXY_PATH.'Logger.php';
include ZMXY_PATH.'zmop/request/ZhimaCustomerCertificationInitializeRequest.php';
defined('SIGNTYPE') or define('SIGNTYPE','RSA');//签名方式,默认为RSA,可使用RSA2
class ZmxyCustomerCertificationInitialize extends Controller
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
     * 芝麻认证初始化
     * @return bool|mixed认证结果
     */
    public function zhimaInitialize($pram)
    {
        $client = new \ZmopClient($this->gatewayUrl,$this->appId,$this->charset,$this->privateKeyFile,$this->zmPublicKeyFile);
        $request = new \ZhimaCustomerCertificationInitializeRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setTransactionId($this->createTransactionId());// 必要参数
        $request->setProductCode("w1010100000000002978");// 必要参数
        $request->setBizCode("FACE");// 必要参数
        $request->setIdentityParam("{\"identity_type\":\"CERT_INFO\",\"cert_type\":\"IDENTITY_CARD\",\"cert_name\":\"田鑫\",\"cert_no\":\"321023199507252612\"}");// 必要参数
        $request->setMerchantConfig("{\"need_user_authorization\":\"false\"}");//
        $request->setExtBizParam("{}");// 必要参数
        $response = $client->execute($request);
        return $response;
//        $this->success(json_encode($response));
//        echo json_encode($response);
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

