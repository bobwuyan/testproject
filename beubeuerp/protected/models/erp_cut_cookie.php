<?php
class erp_cut_cookie extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'erp_cut_cookie';
    }
	/**
	* 添加数据
	**/
	public function insert_cookie($param){
		$cut_cookie=new erp_cut_cookie();
		$cut_cookie->sessionid=$param['session_id'];
		$cut_cookie->end_time=date("Y-m-d H:i:s",strtotime('+2 hour'));
		$cut_cookie->SKU=$param['SKU'];
		$cut_cookie->brandid=$param['brandid'];
		$cut_cookie->text=$param['text'];
		
		$cut_cookie->save();
	}
	/**
	* 删除指定的数据
	**/
	public function del_cookie($param=array()){
		$condition=array();
		$params=array();
		foreach($param as $key=>$value){
			if(strstr($key,'_if_')!==false){
				$like_arr=explode('_if_',$key);
				$condition[]=$like_arr[0].$like_arr[1].':'.$like_arr[0];
				$params[':'.$like_arr[0]]=$value;
			}else{
				$condition[]=$key.'=:'.$key;
				$params[':'.$key]=$value;
			}
		}
		$ret=self::model()->deleteAll(implode(' and ',$condition),$params); 
	}
	/**
	* 修改数据
	**/
	public function update_cookie($up_data,$param){
		$condition=array();
		$params=array();
		foreach($param as $key=>$value){
			if(strstr($key,'_if_')!==false){
				$like_arr=explode('_if_',$key);
				$condition[]=$like_arr[0].$like_arr[1].':'.$like_arr[0];
				$params[':'.$like_arr[0]]=$value;
			}else{
				$condition[]=$key.'=:'.$key;
				$params[':'.$key]=$value;
			}
		}
		$ret=self::model()->updateAll ($up_data,implode(' and ',$condition),$params); 
		return $ret;
	}
	/**
	* 查询的数据
	**/
	public function select_all_cookie($param=array()){
		$condition=array();
		$params=array();
		foreach($param as $key=>$value){
			if(strstr($key,'_if_')!==false){
				$like_arr=explode('_if_',$key);
				$condition[]=$like_arr[0].$like_arr[1].':'.$like_arr[0];
				$params[':'.$like_arr[0]]=$value;
			}else{
				$condition[]=$key.'=:'.$key;
				$params[':'.$key]=$value;
			}
		}
		$ret=self::model()->findAll (implode(' and ',$condition),$params); 
		return $ret;
	}
}