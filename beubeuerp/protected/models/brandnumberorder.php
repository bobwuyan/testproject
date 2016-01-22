<?php
/**
 * 款号与订单关联类
 */
class brandnumberorder {
	/**
	 * 款号查询
	 * @param $brandid 品牌ID
	 * @param $param_arr 查询参数
	 */
	public function select_all_brandnumber($brandid=0,$param_arr=array()){
		$data=Yii::app ()->db->createCommand ()
				->select ( '*' )
				->from ( 'erp_brandnumber_order' )
				->order('id DESC')
				->where('1=1');
		if(!empty($brandid)){
			$data->andwhere('brandid=:brandid',array(':brandid'=>$brandid));
		}
		foreach($param_arr as $key=>$value){
			if(is_array($value)){
					$data->andwhere($key.' in(\''.implode('\',\'',$value).'\')');
			}else{
				$key_str=strtr($key,'.','_');
				if(strstr($value,'like_')!==false){
					$like_arr=explode('like_',$value);
					$data->andwhere($key.' like :'.$key_str,array(':'.$key_str=>$like_arr[1]));
				}else{
					$data->andwhere($key.'=:'.$key_str,array(':'.$key_str=>$value));
				}
			}
		}
		$data = $data->queryAll();
		return $data;
	}
}