<?php
/**
 * ZHIMA API: zhima.merchant.creditlife.preauth.cancel request
 *
 * @author auto create
 * @since 1.0, 2017-08-04 10:25:57
 */
class ZhimaMerchantCreditlifePreauthCancelRequest
{
	/** 
	 * 待解冻预授权冻结资金订单号，或解冻请求流水号
	 **/
	private $outOrderNo;
	
	/** 
	 * 预授权号
	 **/
	private $preAuthNo;
	
	/** 
	 * 取消预授权冻结资金原因
	 **/
	private $remark;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setOutOrderNo($outOrderNo)
	{
		$this->outOrderNo = $outOrderNo;
		$this->apiParas["out_order_no"] = $outOrderNo;
	}

	public function getOutOrderNo()
	{
		return $this->outOrderNo;
	}

	public function setPreAuthNo($preAuthNo)
	{
		$this->preAuthNo = $preAuthNo;
		$this->apiParas["pre_auth_no"] = $preAuthNo;
	}

	public function getPreAuthNo()
	{
		return $this->preAuthNo;
	}

	public function setRemark($remark)
	{
		$this->remark = $remark;
		$this->apiParas["remark"] = $remark;
	}

	public function getRemark()
	{
		return $this->remark;
	}

	public function getApiMethodName()
	{
		return "zhima.merchant.creditlife.preauth.cancel";
	}

	public function setScene($scene)
	{
		$this->scene=$scene;
	}

	public function getScene()
	{
		return $this->scene;
	}
	
	public function setChannel($channel)
	{
		$this->channel=$channel;
	}

	public function getChannel()
	{
		return $this->channel;
	}
	
	public function setPlatform($platform)
	{
		$this->platform=$platform;
	}

	public function getPlatform()
	{
		return $this->platform;
	}

	public function setExtParams($extParams)
	{
		$this->extParams=$extParams;
	}

	public function getExtParams()
	{
		return $this->extParams;
	}	

	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function getFileParas()
	{
		return $this->fileParas;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

}
