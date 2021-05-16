<?php
/**
 * API: qianmi.elife.cardPassword.localItem.createBill request
 * 
 * @author auto create
 * @since 1.0
 */
class CardPasswordLocalItemCreateBillRequest
{
	private $apiParas = array();

	/** 
	 * 回调地址
	 */
	private $callback;

	/** 
	 * 标准商品编号
	 */
	private $itemId;

	/** 
	 * 购买数量
	 */
	private $itemNum;

	/** 
	 * 外部订单号
	 */
	private $outerTid;

	public function setCallback($callback)
	{
		$this->callback = $callback;
		$this->apiParas["callback"] = $callback;
	}
	public function getCallback() {
		return $this->callback;
	}

	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->apiParas["itemId"] = $itemId;
	}
	public function getItemId() {
		return $this->itemId;
	}

	public function setItemNum($itemNum)
	{
		$this->itemNum = $itemNum;
		$this->apiParas["itemNum"] = $itemNum;
	}
	public function getItemNum() {
		return $this->itemNum;
	}

	public function setOuterTid($outerTid)
	{
		$this->outerTid = $outerTid;
		$this->apiParas["outerTid"] = $outerTid;
	}
	public function getOuterTid() {
		return $this->outerTid;
	}

	public function getApiMethodName()
	{
		return "qianmi.elife.cardPassword.localItem.createBill";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		RequestCheckUtil::checkNotNull($this->itemId, "itemId");
		RequestCheckUtil::checkNotNull($this->itemNum, "itemNum");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}