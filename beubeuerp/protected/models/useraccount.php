<?php
/**
 * 账户类
 * */
class useraccount {
	
	static public function useraccountSelectAll()
	{
		 $obj =  Yii::app()->db->createCommand()
		->select('*')->from('beu_useraccount')
		->order('id desc')
		->queryAll();
		return $obj;
	}
}

 