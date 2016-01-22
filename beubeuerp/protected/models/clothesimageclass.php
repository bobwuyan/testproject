<?php
/**
 * 衣服图片相关方法
 */
class clothesimageclass {
	
	/**
	 * 添加衣服图片
	 * @param $name 图片名
	 * @param $type	图片类型
	 * @param $url 图片地址
	 * @param $clothes_order_id	衣服订单表ID号
	 * @param $space 空间名
	 */
	public function create_clothes_img($name,$type,$url,$clothes_order_id,$space) {	
		$image=new erp_image();       
		$image->name=$name;
		$image->status=0;
		$image->istop=0;
		$image->type=$type;
		$image->url=$url;
		$image->isshow=0;
		$image->isback=0;
		$image->addtime=date("Y-m-d H:i:s");
		$image->updatetime=$image->addtime;
		$image->clothes_order_id=$clothes_order_id;
		$image->space=$space;
		
		$insert_id=0;
		if($image->save ()>0){
		   $insert_id=Yii::app()->db->getLastInsertID();
		}
		return $insert_id;//添加成功返回 ID
	}
	/**
	 * 修改图片信息，状态修改
	 * @param $clothes_order_id 衣服订单表ID号
	 * @param $imageid 图片ID
	 * @param $status 0为待审核、1为合格、2为不合格
	 */
	public function update_image($clothes_order_id,$imageid,$status) 
	{
		$is_bool=false;
		if(!empty($imageid) || !empty($clothes_order_id)){
			$p_str='clothes_order_id in('.$clothes_order_id.')';
			if(!empty($imageid)){
				$p_str='id in('.$imageid.')';
			}
			$ret=erp_image::model()->updateAll(array('status'=>$status),$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
	/**
	 * 修改图片信息，图片类型修改
	 * @param $clothes_order_id 衣服订单表ID号
	 * @param $imageid 图片ID
	 * @param $type 0为灰模图、1为立体图、2为静态图、3为真人模特图、4为细节图
	 */
	public function update_image_type($clothes_order_id,$imageid,$type,$url) 
	{
		$is_bool=false;
		if(!empty($imageid) || !empty($clothes_order_id)){
			$p_str='clothes_order_id in('.$clothes_order_id.')';
			if(!empty($imageid)){
				$p_str='id in('.$imageid.')';
			}
			$ret=erp_image::model()->updateAll(array('type'=>$type,'url'=>$url),$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
	/**
	 * 删除图片，只将状态修改
	 * @param $clothes_order_id 衣服订单表ID号
	 * @param $imageid 图片ID
	 * @param $isshow 0为显示，1为删除
	 */
	public function del_image($clothes_order_id,$imageid,$isshow) 
	{
		$is_bool=false;
		if(!empty($imageid) || !empty($clothes_order_id)){
			$p_str='clothes_order_id in('.$clothes_order_id.')';
			if(!empty($imageid)){
				$p_str='id in('.$imageid.')';
			}
		
			$ret=erp_image::model()->updateAll(array('isshow'=>$isshow),$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
	/**
	 * 衣服图片查询
	 * @param $clothes_order_id 衣服订单表ID号
	 * @param $status 0为待审核、1为合格、2为不合格,不在此区间的代表所有
	 * @param $param_arr 查询参数
	 * @param $date上架时间
	 * @param $orderdate时间排序
	 */
	public function select_all_image($clothes_order_id,$status='-1',$param_arr=array(),$date='',$orderdate='id desc') 
	{
		$criteria = new CDbCriteria;
		$criteria->select = '*';
		$criteria->order = $orderdate!='id desc'?$orderdate.',id DESC':'id desc';
		if(is_array($clothes_order_id)){
			$criteria->addInCondition('clothes_order_id',$clothes_order_id);
		}else{
			$criteria->addCondition('clothes_order_id=:clothes_order_id');
			$criteria->params[':clothes_order_id']=$clothes_order_id;
		}
		
		foreach($param_arr as $key=>$value){
			if(is_array($value)){
				$criteria->addInCondition($key,$value);
			}else{
				$key_str=strtr($key,'.','_');
				$criteria->addCondition($key.'=:'.$key_str);
				$criteria->params[':'.$key_str]=$value;
			}
		}
		if(!empty($date)){
			$criteria->addCondition('addtime like \':addtime%\'');
			$criteria->params[':addtime']=$date;
		}
		if(in_array($status,array(0,1,2))){
			$criteria->addCondition('status=:status');
			$criteria->params[':status']=$status;
		}
		$ret_data = erp_image::model()->findAll( $criteria );
		
		return $ret_data;//创建成功返回
	}
	
	/**
	 * 设置图片主图
	 * @param $clothes_order_id 衣服订单表ID号
	 * @param $imageid 图片ID
	 * @param $istop 主图：0为否，1为主图
	 */
	public function set_top($clothes_order_id,$imageid,$istop) 
	{
		$is_bool=false;
		if(!empty($imageid) || !empty($clothes_order_id)){
			$p_str='clothes_order_id in('.$clothes_order_id.')';
			if(!empty($imageid)){
				$p_str='id in('.$imageid.')';
			}
		
			$ret=erp_image::model()->updateAll(array('istop'=>$istop),$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
	
	/**
	 * 通过条件查询订单数据
	 * @param $clothesid 衣服ID
	 * @param $name 图片名
	 * @param $isback 正背面：0为正面，1为背面
	 * @param $type 图片类型：0为灰模图、1为立体图、2为静态图、3为真人模特图、4为细节图
	 * @param $url	图片地址
	 */
	public function upload_imageinfo($clothes_order_id,$imageid,$param) 
	{
		$is_bool=false;
		if(!empty($imageid) || !empty($clothes_order_id)){
			$p_str='clothes_order_id in('.$clothes_order_id.')';
			if(!empty($imageid)){
				$p_str='id in('.$imageid.')';
			}
			$ret=erp_image::model()->updateAll($param,$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
}
