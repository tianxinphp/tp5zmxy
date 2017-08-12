<?php
/**
 * ZHIMA API: zhima.merchant.creditlife.fund.refund request
 *
 * @author auto create
 * @since 1.0, 2017-08-04 10:26:12
 */
class ZhimaMerchantCreditlifeFundRefundRequest
{
	/** 
	 * 
	 **/
	private $bizProduct;
	
	/** 
	 * 商户发起扣款时的订单号
	 **/
	private $outOrderNo;
	
	/** 
	 * 退款金额
	 **/
	private $payAmount;
	
	/** 
	 * 交易信息说明(退款原因)
	 **/
	private $remark;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setBizProduct($bizProduct)
	{
		$this->bizProduct = $bizProduct;
		$this->apiParas["biz_product"] = $bizProduct;
	}

	public function getBizProduct()
	{
		return $this->bizProduct;
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
		return "zhima.merchant.creditlife.fund.refund";
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
