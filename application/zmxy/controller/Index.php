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

    /**
     * 在使用芝麻认证接口前调用
     * @var array前置方法列表
     */
    protected $beforeActionList = [
        'securityVerificate'//安全认证
    ];
    public function zmxy(){
        return 'add';
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
        $requestLog['token']=$this->token=$request->only(['token']);//获取token
        //==========先这么多参数吧,以后有在加===END=================================
        $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
        if(VENDOR_STATUS=='prod'){//生产环境下配置
            if(!in_array($requestLog['requestWay'], $this->requestWay)){
                $this->requestMsg='验证失败,提交方式不正确';
                return false;
            }else if($this->requestIp!=$requestLog['ip']){
                $this->requestMsg='验证失败,请求ip不正确';
                return false;
            }else if(empty($requestLog['token'])){
                $this->requestMsg='验证失败,请求数据错误';
                return false;
            }
        }
        //数据初步校验通过,发送请求，验证token有效性获取
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


}


