<?php
/**
 * ZHIMA API: zhima.merchant.creditlife.preauth.unfreeze request
 *
 * @author auto create
 * @since 1.0, 2017-08-04 10:25:40
 */
class ZhimaMerchantCreditlifePreauthUnfreezeRequest
{
	/** 
	 * 待解冻资金(元)
	 **/
	private $payAmount;
	
	/** 
	 * 预授权后产生的预授权号
	 **/
	private $preAuthNo;
	
	/** 
	 * 发起资金解冻原因
	 **/
	private $remark;
	
	/** 
	 * 交易流水号
	 **/
	private $transactionId;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setPayAmount($payAmount)
	{
		$this->payAmount = $payAmount;
		$this->apiParas["pay_amount"] = $payAmount;
	}

	public function getPayAmount()
	{
		return $this->payAmount;
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

	public function setTransactionId($transactionId)
	{
		$this->transactionId = $transactionId;
		$this->apiParas["transaction_id"] = $transactionId;
	}

	public function getTransactionId()
	{
		return $this->transactionId;
	}

	public function getApiMethodName()
	{
		return "zhima.merchant.creditlife.preauth.unfreeze";
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
