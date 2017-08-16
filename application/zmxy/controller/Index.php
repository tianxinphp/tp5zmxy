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
use think\Db;
include ZMXY_PATH.'Logger.php';
include 'ZmxyCustomerCertificationInitialize.php';
include 'ZhimaCustomerCertificationCertify.php';
include 'ZhimaAuthInfoAuthorize.php';
include 'ZmxyAuthRetrun.php';
include 'ZhimaCreditWatchlistiiGet.php';
include 'ZhimaCreditScoreGet.php';
include 'ZhimaCreditAntifraudScoreGet.php';
include 'ZhimaCreditAntifraudVerify.php';
include 'ZhimaCreditAntifraudRiskList.php';
class Index extends Controller
{
    //请求令牌
    private $token;

    //请求返回域名
    private $responseDomain='http://xiaocong.tpzmxy.com/test';

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

    //用户open_id
    private $open_id;
    /**
     * 在使用芝麻认证接口前调用
     * @var array前置方法列表
     */
    protected $beforeActionList = [
        'securityVerificate'=>  ['only'=>'zmxy'],//安全认证
        'whitelist'=>  ['only'=>'zmxy']//白名单调用
    ];

    /**
     * 芝麻信用授权页面
     */
    public function zmxy(){
        if($this->requestResult){
            //身份认证通过,查看数据库是否进行过信用授权
            $isAuthorize=$this->isAuthorize($this->uuid);
            if($isAuthorize){
                //没有进行授权认证查询信息进行授权
                $zhimaAuthInfo=new ZhimaAuthInfoAuthorize();
                $authInfo=$this->authInfo($this->uuid);//查询出用户的信息
                if($authInfo){
                    $authUrl=$zhimaAuthInfo->zhimaAuthInfo($authInfo);//进入芝麻认证页面
                    if($authUrl){//有返回值
                        Header("HTTP/1.1 303 See Other");
                        Header("Location: $authUrl");//跳转芝麻信用接口
                        exit;
                    }else{
                        $this->requestResult=false;
                        $this->requestMsg="芝麻信用初始化认证失败";
                        $this->failPost();
                    }
                }else{
                    $this->requestResult=false;
                    $this->requestMsg="无此用户";
                    $this->failPost();
                }
            }else{
                $this->requestResult=false;
                if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {//是在微信中打开
                    $this->requestMsg="history.go(-1)";
                }else{
                    $this->requestMsg="apiready=function(){
	api.alert({title: '小葱钱包', msg: '芝麻信用已认证', }, function(ret, err) {
		api.closeWin({})
	});
}";
                }
                $this->failPost();
            }
        }else{
            $this->failPost();
        }
    }

    /**
     * 芝麻信用权限成功返回解码
     */
    public function zmxyAuthRetrun(){
        $db1 = Db::connect('database.db1');
        $request = Request::instance();//建立请求实例
        $retrunObj=new ZmxyAuthRetrun;//建立解码实例
        $retrunResult=urldecode($retrunObj->zmxyRetrun());//调用解码方法
        //=================分割返回数据参数，比如open_id等START==========
        $firstInterception=explode('&',$retrunResult);
        $resultArray=array();
        foreach ($firstInterception as $value){
            $secondInterception=explode('=',$value);
            $resultArray[$secondInterception[0]]=$secondInterception[1];
        }
        //================END=================================================
        if($resultArray['success']){//授权成功
            $this->open_id=$resultArray['open_id'];//赋予当前授权用户open_id
            $inlineObj=new ZhimaCreditWatchlistiiGet();//建立行内关注验证
            $inlineAttention=$inlineObj->zhimaWatchlist($resultArray);//执行行内关注实例
            $authInfo=$this->authInfo($resultArray['state']);//查询出用户的信息
            //授权成功,添加数据库信息
            $db1::startTrans();
            try {
                $db1::name('zhima_watch_list')
                    ->insert(['uid'=>$authInfo['uid'],'result'=>$inlineAttention,'name'=>$authInfo['real_name'],'phone'=>$authInfo['cell_phone'],'idcard'=>$authInfo['idcard'],'openid'=>$this->open_id,'raw'=>$request->url(),'add_time'=>date('Y-m-d H-i-s')]);
                // 提交事务
                $db1::commit();
            } catch (\Exception $e) {
                // 回滚事务
                $db1::rollback();
                $this->requestMsg='授权失败';
                $this->failPost();
                return false;
            }
            //================获取芝麻信用分STRAT=======================
            $scoreObj=new ZhimaCreditScoreGet();//初始化查询芝麻信用实例
            $scoretion=$scoreObj->zhimaQueryScore($resultArray);//执行查询方法
            if($scoretion->success) {
                //查询成功,添加数据库信息
                $db1::startTrans();
                try {
                    $db1::name('zhima_score')
                        ->insert(['uid'=>$authInfo['uid'],'phone'=>$authInfo['cell_phone'],'idcard'=>$authInfo['idcard'],'openid'=>$this->open_id,'score'=>$scoretion->zm_score,'raw'=>$request->url(),'add_time'=>date('Y-m-d H-i-s')]);
                    // 提交事务
                    $db1::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    $db1::rollback();
                    $this->requestMsg='查询芝麻分失败';
                    $this->failPost();
                    return false;
                }
            }else{//查询失败
                $this->requestMsg=$scoretion->error_message;
                $this->failPost();
                return false;
            }
            //================END=======================================
            //==============信用认证执行到结束START==========================
            $db1::startTrans();
            try {
                $db1::name('info_step')
                    ->insert(['uid'=>$authInfo['uid'],'step'=>'credit_validate','add_time'=>date('Y-m-d H-i-s')]);
                // 提交事务
                $db1::commit();
            } catch (\Exception $e) {
                // 回滚事务
                $db1::rollback();
                $this->requestMsg='授权失败';
                $this->failPost();
                return false;
            }
            //==============信用认证执行到结束END==========================
            //一切成功,返回
            if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//是在微信中打开
                Header("HTTP/1.1 303 See Other");
                Header("Location: http://api.yingjila.com/weixin/html/user/user_info_review.html");//跳转微信页面
                exit;
            }else{
                //返回成功
                $this->requestResult=true;
                $this->requestMsg='apiready=function(){api.closeWin()}';
                $responseResult=array(
                    'requestMsg'=>$this->requestMsg,
                    'requestResult'=>$this->requestResult,
                );
                $this->getHttpResponsePOST($this->responseDomain,$responseResult);
            }
        }else{
            $this->requestMsg=$resultArray['error_message'];
            $this->recordLog($resultArray);
            $this->failPost();
        }
    }

    public function test(){
        Header("HTTP/1.1 303 See Other");
        Header("Location: http://www.baidu.com");//跳转微信页面
        exit;
    }

    /**
     * @param $uuid 用户id
     * @return bool 返回是否已绑定
     */
    private function isAuthorize($uuid){
        $db1 = Db::connect('database.db1');
        $result = $db1::name('zhima_score')->where('uid', $uuid)->select();
        if($result){
//            return false;
            return true;
        }else{
            return true;
        }
    }

    /**
     * @param $uuid 用户id
     * @return false|\PDOStatement|string|\think\Collection
     */
    private function authInfo($uuid){
        $db1 = Db::connect('database.db1');
        $result = $db1::name('member_info')->where('uid', $uuid)->select();
        return $result[0];
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
        $onlyPram=$request->only(['token']);
        $requestLog['token']=$this->token=$onlyPram['token'];//获取token
//        $this->token=555;测试
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
            'token'=>$this->token,
        );
        //======================验证token 调用接口 START==============================
//        $uuidResponse=$this->getHttpResponsePOST($this->requestIp,$curl_date);
//        $val = json_decode($uuidResponse);//将数据流转为json对象
//        if($val->token&&$val->token==$this->token&&$val->uuid){//token返回进行验证uuid必须存在,进行下一步
//            $this->requestResult=true;//安全认证成功
//            $this->uuid=$val->uuid;
//            $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
//        }else{
//            $requestLog['requestMsg']=$this->requestMsg='验证失败,请求数据错误';
//            $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
//            return false;
//        }
        //====================验证token 调用接口 END===================================
        //======================验证token 直接查数据库 START===========================
        $tokenInfo=$this->queryToken($curl_date);//token信息
        if($tokenInfo&&$tokenInfo[0]['token']&&$tokenInfo[0]['token']==$this->token&&$tokenInfo[0]['user_id']){//token返回进行验证uuid必须存在,进行下一步
            $this->requestResult=true;//安全认证成功
            $this->uuid=$tokenInfo[0]['user_id'];
            $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
        }else{
            $requestLog['requestMsg']=$this->requestMsg='验证失败,请求数据错误';
            $this->recordLog($requestLog);//记录到日志中,以后再记录到数据库中
            return false;
        }
        //======================验证token 直接查数据库 END===========================
    }

    /**
     * 验证token正确性
     * @param $curl_date token
     * @return false|\PDOStatement|string|\think\Collection 返回uid+token
     */
    private function queryToken($token){
        $db2 = Db::connect('database.db2');
        $result=$db2->query('select * from user_token where token="'.$token['token'].'" ');
        return $result;
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
        return $data;
//        return '{"uuid":333,"token":555}';测试
    }

    /**
     * 三合一调用接口
     */
    public function zhimaCreditQueryByIDCard(){
        $request = Request::instance();//生成tp请求实例化对象
        //获取idcard参数与querydatalist参数
        $onlyPram=$request->only(['idcard','querydatalist']);
        if(!$onlyPram['idcard']||!$onlyPram['querydatalist']){//两个有一个不存在退回
            $this->requestMsg='请求数据有误';
            $this->failPost();
        }else{
            $queryMemberResult=$this->queryMemberInfo($onlyPram['idcard']);//根据身份证查出基本信息
            if($queryMemberResult&&$queryMemberResult[0]['uid']){
                $queryPramResult=$this->queryPramInfo($queryMemberResult[0]['uid']);//根据uid查出详细信息
                if($queryPramResult){
                    $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";//过滤特殊字符
                    $midata=array(
                        'CertType'=>'IDENTITY_CARD',
                        'CertNo'=>$onlyPram['idcard'],
                        'Name'=>$queryMemberResult[0]['real_name'],
                        'Mobile'=>$queryMemberResult[0]['user_name'],
                        'Address'=>preg_replace($regex,"",$queryMemberResult[0]['address']),
                        'Mac'=>$queryPramResult[0]['mac_addr'],
                        'Wifimac'=>'N/A',
                        'BankCard'=>$queryPramResult[0]['bank_num'],
                        'Email'=>'N/A',
                        'Ip'=>$queryPramResult[0]['ip'],
                        'Imei'=>$queryPramResult[0]['deviceid'],
                    );
                    $result=$this->getCreditQuery($midata,$onlyPram['querydatalist']);//获取芝麻信用一系列接口结果
                    echo $result;
//                    $this->getHttpResponsePOST($this->responseDomain,$result);
                }else{
                    $this->requestMsg='无此用户';
                    $this->failPost();
                }
            }else{
                $this->requestMsg='无此用户';
                $this->failPost();
            }
        }
    }

    /**
     * @param $idcard 身份证
     * @return mixed 返回身份信息
     */
    private function queryMemberInfo($idcard){
        $db1 = Db::connect('database.db1');//链接小葱钱包数据库
        $queryMemberInfo=$db1->query('select mi.uid , m.user_name , mi.idcard ,mi.address ,mi.real_name from lzh_member_info mi inner join lzh_members m on mi.uid = m.id where mi.idcard="'.$idcard.'" ');
        return  $queryMemberInfo;
    }


    /**
     * @param $uid uid
     * @return mixed 返回身份详细信息
     */
    private function queryPramInfo($uid){
        $db1 = Db::connect('database.db1');//链接小葱钱包数据库
        $sql='select m.id , t1.mac_addr , t2.deviceid , t2.ip , t3.dev ,t4.address , t5.bank_num
                        from lzh_members m
                        left join 
                        (
                            select uid , mac_addr from (select * from lzh_member_info_ext where uid = "'.$uid.'" ) t order by id desc limit 1
                        ) t1 on m.id = t1.uid
                        left join
                        (
                            select uid , deviceid, ip from ( select *  from lzh_member_localtion where uid = "'.$uid.'" ) t order by id desc limit 1
                        ) t2 on m.id = t2.uid
                        left join
                        (
                            select uid ,dev  from  (select * from lzh_member_tongxunlu where uid = "'.$uid.'" ) t  order by t.id desc limit 1
                        ) t3 on m.id = t3.uid
                        left join
                        (
                            select uid , address  from  (select * from lzh_member_info where uid =  "'.$uid.'" ) t   limit 1
                        ) t4 on m.id = t4.uid
                        left join
                        (
                            select uid , bank_num  from  (select * from lzh_member_banks where uid =  "'.$uid.'" ) t   limit 1
                        ) t5 on m.id = t5.uid
                        where m.id = "'.$uid.'" ';
        $queryPramInfo =$db1->query($sql);
        return $queryPramInfo;
    }



    private function getCreditQuery($midata,$querydatalist){
        $result='';//返回结果
        if(!is_string($querydatalist)){
            $querydatalist=(string)$querydatalist;
        }
        $queryArray=str_split($querydatalist);//将请求过来的要求转为数组
        if($queryArray[0]==1){
            //调用芝麻分查询接口
        }
        if($queryArray[1]==1){
            //调用行业关注接口
        }
        if($queryArray[2]==1){
            //调用欺诈评分接口
            $result=$result.",\"ZhimaCreditAntiFraudScore\":" .$this->generateAntiFraudScoreJsonString($midata);
        }
        if($queryArray[3]==1){
            //调用欺诈信息验证接口
            $result = $result. ",\"ZhimaCreditAntiFraudVerify\":".$this->GenerateAntiFraudVerifyJsonString($midata);
        }
        if($queryArray[4]==1){
            //调用欺诈关注清单接口
            $result = $result. ",\"ZhimaCreditAntiFraudVerify\":".$this->GenerateAntiFraudRiskListJsonString($midata);
        }
        $result = "{".trim( $result,',')."}";
        return $result;
    }

    /**
     * @param $midata 欺诈需要的参数
     * @return string 欺诈分信息
     */
    private function generateAntiFraudScoreJsonString($midata){
        //用身份证去查数据库，有记录返回，无记录去查芝麻
        $db3 = Db::connect('database.db3');//链接欺诈分信用库
        $antiFraudScoreInfo=$db3->query('select * from cqs_zhima_AntiFraudScore_QueryData where MemberIDCard = "'.$midata['CertNo'].'" order by id desc limit 1');
        if($antiFraudScoreInfo){//数据库有数据
            //直接返回body
            return $antiFraudScoreInfo[0]['body'];
        }else{//调用芝麻信用查欺诈分系统
            $antiFraudScore=new ZhimaCreditAntifraudScoreGet();//建立查欺诈分实例
            $antiFraudScoreResult=$antiFraudScore->zhimaAntifraudScore($midata);
            if($antiFraudScoreResult->success){//查询成功
                //记录到数据库中
                $db3->startTrans();
                try {
                    $db3->execute('INSERT INTO cqs_zhima_AntiFraudScore_QueryData (MemberIDCard ,PostData,RequestDateTime,Tag,bizNo,Score,body ,Success,TransactionID) VALUES ("'.$midata['CertNo'].'","'.addslashes(json_encode($midata)).'","'.date('Y-m-d H:i:s').'",0,"'.$antiFraudScoreResult->biz_no.'","'.$antiFraudScoreResult->score.'","'.addslashes(json_encode($antiFraudScoreResult)).'","True","'.$antiFraudScoreResult->transactionId.'")');
                    // 提交事务
                    $db3->commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    $db3->rollback();
                    $this->requestMsg='将欺诈分添加到数据库失败';
                    $this->failPost();
                    exit;
                }
                return json_encode($antiFraudScoreResult);
            }else{
                $this->requestMsg='查询欺诈分失败';
                $this->failPost();
                exit;
            }
        }
    }

    /**
     * @param $midata 欺诈信息需要的参数
     * @return string 欺诈信息
     */
    private function GenerateAntiFraudVerifyJsonString($midata){
        //用身份证去查数据库，有记录返回，无记录去查芝麻
        $db3 = Db::connect('database.db3');//链接欺诈分信息信用库
        $antiFraudInfo=$db3->query('select * from cqs_zhima_antifraudverify_querydata where MemberIDCard = "'.$midata['CertNo'].'" order by id desc limit 1');
        if($antiFraudInfo){//数据库有数据
            //直接返回body
            return $antiFraudInfo[0]['body'];
        }else{//调用芝麻信用查欺诈信息系统
            $antiFraudVerify=new ZhimaCreditAntifraudVerify();//建立查欺诈分信息实例
            $antiFraudVerifyResult=$antiFraudVerify->zhimaQueryVerify($midata);
            if($antiFraudVerifyResult->success){//查询成功
                //记录到数据库中
                $db3->startTrans();
                try {
                    $db3->execute('INSERT INTO cqs_zhima_antifraudverify_querydata (MemberIDCard ,PostData,RequestDateTime,Tag,bizNo,verifyCode,body ,Success,TransactionID) VALUES ("'.$midata['CertNo'].'","'.addslashes(json_encode($midata)).'","'.date('Y-m-d H:i:s').'",0,"'.$antiFraudVerifyResult->biz_no.'","'.addslashes(json_encode($antiFraudVerifyResult->verify_code)).'","'.addslashes(json_encode($antiFraudVerifyResult)).'","True","'.$antiFraudVerifyResult->transactionId.'")');
                    // 提交事务
                    $db3->commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    $db3->rollback();
                    $this->requestMsg='将欺诈分信息添加到数据库失败';
                    $this->failPost();
                    exit;
                }
                return json_encode($antiFraudVerifyResult);
            }else{
                $this->requestMsg='查询欺诈分信息失败';
                $this->failPost();
                exit;
            }
        }
    }

    /**
     * @param $midata 欺诈列表需要的参数
     * @return string 欺诈列表信息
     */
    private function GenerateAntiFraudRiskListJsonString($midata){
        //用身份证去查数据库，有记录返回，无记录去查芝麻
        $db3 = Db::connect('database.db3');//链接欺诈列表信用库
        $antiFraudListInfo=$db3->query('select * from cqs_zhima_antifraudrisklist_querydata where MemberIDCard = "'.$midata['CertNo'].'" order by id desc limit 1');
        if($antiFraudListInfo){//数据库有数据
            //直接返回body
            return $antiFraudListInfo[0]['body'];
        }else{//调用芝麻信用查欺诈信息系统
            $antiFraudList=new ZhimaCreditAntifraudRiskList();//建立查欺诈列表信息实例
            $antiFraudListResult=$antiFraudList->zhimaQueryList($midata);
            if($antiFraudListResult->success){//查询成功
                //记录到数据库中
                $db3->startTrans();
                try {
                    $db3->execute('INSERT INTO cqs_zhima_antifraudrisklist_querydata (MemberIDCard ,PostData,RequestDateTime,Tag,bizNo,riskcode,body ,Success,TransactionID,hit) VALUES ("'.$midata['CertNo'].'","'.addslashes(json_encode($midata)).'","'.date('Y-m-d H:i:s').'",0,"'.$antiFraudListResult->biz_no.'","'.addslashes(json_encode($antiFraudListResult->risk_code)).'","'.addslashes(json_encode($antiFraudListResult)).'","True","'.$antiFraudListResult->transactionId.'","'.$antiFraudListResult->hit.'")');
                    // 提交事务
                    $db3->commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    $db3->rollback();
                    $this->requestMsg='将欺诈分列表添加到数据库失败';
                    $this->failPost();
                    exit;
                }
                return json_encode($antiFraudListResult);
            }else{
                $this->requestMsg='查询欺诈分列表失败';
                $this->failPost();
                exit;
            }
        }
    }
}


