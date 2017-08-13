<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/10 0010
 * Time: 下午 1:20
 */
namespace app\zmxy\controller;
use think\Controller;
use think\Request;
include ZMXY_PATH.'Logger.php';
include 'ZmxyCustomerCertificationInitialize.php';
include 'ZhimaCustomerCertificationCertify.php';
include 'ZhimaAuthInfoAuthorize.php';
class Index extends Controller
{
    //请求令牌
    private $token;

    //请求返回域名
    private $responseDomain;

    //用户id
    private $uuid;

    //芝麻信用业务号,相当于每一次请求在芝麻信用那的订单号
    private $biz_no ;

    //请求方式,目前只有post和get
    private $requestWay=array('GET','POST');

    //请求结果
    private $requestResult=false;

    //请求失败恢复msg
    private $requestMsg;

    //请求域名
    private $requestIp;

    //认证成功返回url
    private $returnUrl='http://www.baidu.com';
    /**
     * 在使用芝麻认证接口前调用
     * @var array前置方法列表
     */
    protected $beforeActionList = [
        'securityVerificate',//安全认证
        'whitelist'//白名单调用
    ];

    public function zmxy(){
        if($this->requestResult){
            //芝麻信用认证初始化
            $zmxyInitialize=new ZmxyCustomerCertificationInitialize();
            //=======================查询数据库,获取必要参数START============

            //========================查询数据库,获取必要参数END========
            $initializeResult=$zmxyInitialize->zhimaInitialize($pram='');
            if($initializeResult->success&&$initializeResult->biz_no){
                $this->biz_no=$initializeResult->biz_no;
                $zhimaAuthInfo=new ZhimaAuthInfoAuthorize();

                $AuthInfo=$zhimaAuthInfo->zhimaAuthInfo($pram='');
                $this->recordLog($AuthInfo);
                var_dump($AuthInfo);
                die();
                //初始化成功
                $zmxyCertify =new ZhimaCustomerCertificationCertify();
                $certifyPram=array(
                    'biz_no'=>$this->biz_no,
                    'returnUrl'=>$this->returnUrl
                );
                $CertifyResult=$zmxyCertify->zhimaCertify($certifyPram);
                $this->recordLog($CertifyResult);
            }else{
                $this->requestResult=false;
                $this->requestResult="芝麻信用初始化认证失败";
                $this->failPost();
            }
        }else{
            $this->failPost();
        }
    }

    /**
     *失败发送失败请求
     */
    private function failPost(){
        $responseResult=array(
            'requestMsg'=>$this->requestMsg,
            'requestResult'=>$this->requestResult,
        );
        $this->getHttpResponsePOST($this->responseDomain,$responseResult);
    }

    /***
     *安全认证接口,获取客户端token,并转发到另外的服务端,验证token,成功返回uuid,进行下一步
     */
    protected  function securityVerificate(){
        $request = Request::instance();//生成tp请求实例化对象
        //========记录请求日志，现在是存放在文件中，以后可以是存放在数据库中=========
        $requestLog['headerInfo']=json_encode($request->header());//请求头信息
        $requestLog['ip']=$request->ip();//ip
        $requestLog['domain']=$request->domain();//域名
        $requestLog['requestWay']=$request->method();//请求方式,目前只支持post/get
        $requestLog['isAjax']=var_export($request->isAjax(), true);//是否为ajax
        $requestLog['param']=json_encode($request->param());//参数
        $requestLog['receiveDate']=date('Y-m-d H:i:s');//请求接受时间
//        $requestLog['token']=$this->token=$request->only(['token']);//获取token
        $this->token=555;
        //==========先这么多参数吧,以后有在加===END=================================
        if(VENDOR_STATUS=='prod'){//生产环境下配置
            if(!in_array($requestLog['requestWay'], $this->requestWay)){
                $requestLog['requestMsg']=$this->requestMsg='验证失败,提交方式不正确';
                $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
                return false;
            }else if($this->requestIp!=$requestLog['ip']){
                $requestLog['requestMsg']=$this->requestMsg='验证失败,请求ip不正确';
                $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
                return false;
            }else if(empty($requestLog['token'])){
                $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
                $requestLog['requestMsg']=$this->requestMsg='验证失败,请求数据错误';
                $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
                return false;
            }
        }
        //数据初步校验通过,发送请求，验证token有效性获取uuid
        $curl_date=array(
//            'token'=>$requestLog['token'],
            'token'=>555,
        );
        $uuidResponse=$this->getHttpResponsePOST($this->requestIp,$curl_date);
        $val = json_decode($uuidResponse);//将数据流转为json对象
        if($val->token&&$val->token==$this->token&&$val->uuid){//token返回进行验证uuid必须存在,进行下一步
            $this->requestResult=true;//安全认证成功
            $this->uuid=$val->uuid;
            $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
        }else{
            $requestLog['requestMsg']=$this->requestMsg='验证失败,请求数据错误';
            $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
            return false;
        }
    }

    /**
     * 身份认证白名单
     */
    protected function whitelist(){

    }

    /**
     * 记录日志,以后可以记录在数据库中
     */
    private function recordLog($requestLog){
        $logger=new \Logger;
        if(VENDOR_STATUS=='dev'){
            $logger->setLogLevel(4);//设置日志等级在开发模式下为debug最高级
        }
        $logger->info('本次请求信息：'.json_encode($requestLog));
    }

    /**
     * php url发送post请求
     * @param $url 发送请求url
     * @param $post_data 发送请求参数数组
     * 返回php文件流
     */
    private function getHttpResponsePOST($url, $post_data) {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
//        return $data;
        return '{"uuid":333,"token":555}';
    }

}


