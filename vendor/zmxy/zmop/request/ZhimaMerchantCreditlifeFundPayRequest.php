<?php
/**
 * ZHIMA API: zhima.merchant.creditlife.fund.pay request
 *
 * @author auto create
 * @since 1.0, 2017-08-04 10:26:31
 */
class ZhimaMerchantCreditlifeFundPayRequest
{
	/** 
	 * 代扣协议号(代扣扣款时必须提供)
	 **/
	private $agreementNo;
	
	/** 
	 * 扣款类型(withholding_pay:代扣扣款,preauth_pay:预授权转支付)
	 **/
	private $fundPayType;
	
	/** 
	 * 
	 **/
	private $goodsTitle;
	
	/** 
	 * 商品类型(0:虚拟物品,1:实物)
	 **/
	private $goodsType;
	
	/** 
	 * 商户订单号
	 **/
	private $outOrderNo;
	
	/** 
	 * 支付金额
	 **/
	private $payAmount;
	
	/** 
	 * 预授权号(付款方式为预授权转支付时必须提供)
	 **/
	private $preAuthNo;
	
	/** 
	 * 芝麻用户id
	 **/
	private $roleId;
	
	/** 
	 * 收款方支付宝id
	 **/
	private $sellerId;
	
	/** 
	 * 
	 **/
	private $transactionId;
	
	/** 
	 * 支付宝用户id（付款方id）
	 **/
	private $userId;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setAgreementNo($agreementNo)
	{
		$this->agreementNo = $agreementNo;
		$this->apiParas["agreement_no"] = $agreementNo;
	}

	public function getAgreementNo()
	{
		return $this->agreementNo;
	}

	public function setFundPayType($fundPayType)
	{
		$this->fundPayType = $fundPayType;
		$this->apiParas["fund_pay_type"] = $fundPayType;
	}

	public function getFundPayType()
	{
		return $this->fundPayType;
	}

	public function setGoodsTitle($goodsTitle)
	{
		$this->goodsTitle = $goodsTitle;
		$this->apiParas["goods_title"] = $goodsTitle;
	}

	public function getGoodsTitle()
	{
		return $this->goodsTitle;
	}

	public function setGoodsType($goodsType)
	{
		$this->goodsType = $goodsType;
		$this->apiParas["goods_type"] = $goodsType;
	}

	public function getGoodsType()
	{
		return $this->goodsType;
	}

	public function setOutOrderNo($outOrderNo)
	{
		$this->outOrderNo = $outOrderNo;
		$this->apiParas["out_order_no"] = $outOrderNo;
	}

	public function getOutOrderNo()
	{
		return $this->outOrderNo;
	}

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

	public function setRoleId($roleId)
	{
		$this->roleId = $roleId;
		$this->apiParas["role_id"] = $roleId;
	}

	public function getRoleId()
	{
		return $this->roleId;
	}

	public function setSellerId($sellerId)
	{
		$this->sellerId = $sellerId;
		$this->apiParas["seller_id"] = $sellerId;
	}

	public function getSellerId()
	{
		return $this->sellerId;
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

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function getApiMethodName()
	{
		return "zhima.merchant.creditlife.fund.pay";
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
