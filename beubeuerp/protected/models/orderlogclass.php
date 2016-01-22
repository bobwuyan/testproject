<?php
/**
 * 订单日志类
 */
class orderlogclass {
	/**
	 * 修改订单日志
	 * @param $orderid 订单ID
	 * @param $text	日志内容
	 */
	public function update_log($orderid,$text) 
	{
		return true;//修改成功返回
	}
	/**
	 * 通过订单ID号查询日志内容
	 * @param $orderid 订单ID
	 */
	public function select_log($orderid) 
	{
		$obj=array();
		return $obj;//查询结果返回
		
	}
	
}
