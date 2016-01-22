<?php
/**
 * 海报图片相关方法
 */
class posterclass {
	/**
	 * 修改图片信息，状态修改
	 * @param $brandid 品牌ID
	 * @param $addtime 添加时间
	 * @param $status 状态 0为可用，1为删除
	 * @param $name 文件名
	 * @param $url 图片路径
	 */
	public function add_poster($brandid,$addtime,$status,$name,$url) 
	{
		return true;//添加成功返回
	}
	/**
	 * 删除海报，只将状态修改
	 * @param $id 海报ID
	 * @param $status 0为可用，1为删除
	 */
	public function del_poster($id,$status) 
	{
		$is_bool=false;
		if(!empty($id)){
			$p_str='id in('.$id.')';
			$ret=erp_poster::model()->updateAll(array('status'=>$status),$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
	/**
	 * 修改海报
	 * @param $id 海报ID
	 * @param $param 修改的内容
	 */
	public function up_poster($id,$param) 
	{
		$is_bool=false;
		if(!empty($id)){
			$p_str='id='.$id;
			$ret=erp_poster::model()->updateAll($param,$p_str);
			if($ret>0){
				$is_bool=true;
			}
		}
		return $is_bool;//修改成功返回
	}
	/**
	 * 海报查询
	 * @param $brandid 品牌ID
	 * @param $status -1所有 0为可用，1为删除
	 * @param $param_arr 查询参数
	 * @param $limit 每页显示数量
	 * @param $offset 查询起点
	 * @param $order 排序
	 */
	public function select_all_poster($brandid,$status=-1,$param_arr=array(),$limit=-1,$offset=-1,$order='') 
	{
		$ret_data=array('data'=>array(),'page_sum'=>0);
		
		$data=Yii::app ()->db->createCommand ()
				->select ( '*' )
				->from ( 'erp_poster' )
				->order((empty($order)?'':$order.',').'id DESC')
				->limit ( $limit, $offset)
				->where('brandid=:brandid',array(':brandid'=>$brandid));
				if(in_array($status,array(0,1))){
					$data->andwhere('status=:status',array(':status'=>$status));
				}
		foreach($param_arr as $key=>$value){
			if($key=='startime'){
				$data->andwhere('DATE_FORMAT(`addtime`,"%Y-%m-%d")>=:startime',array(':startime'=>$value));
			}elseif($key=='endtime'){
				$data->andwhere('DATE_FORMAT(`addtime`,"%Y-%m-%d")<=:endtime',array(':endtime'=>$value));
			}elseif(is_array($value)){
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
		$data2=clone $data;
		$ret_data['data'] = $data->queryAll();
		
		//统计数量
		$data2->limit ( -1, -1)->select('count(*) as count');
		$data_count=$data2->queryRow();
		$ret_data['page_sum']=$data_count['count'];
		return $ret_data;//结果成功返回
	}
	
	/**
	 * 海报统计二级目录
	 * @param $brandid 品牌ID
	 * @param $status -1所有 0为可用，1为删除
	 */
	public function statistics_poster_dirname($brandid,$status=-1) 
	{
		$criteria = new CDbCriteria;
		$criteria->select = 'distinct(dirname) as dirname';
		$criteria->addCondition('brandid=:brandid');
		$criteria->params[':brandid']=$brandid;
		if(in_array($status,array(0,1))){
			$criteria->addCondition('status=:status');
			$criteria->params[':status']=$status;
		}
		$criteria->order = 'addtime desc,id desc';
		$ret_data = erp_poster::model()->findAll( $criteria );
		return $ret_data;//创建成功返回
	}
	/**
	 * 海报统计图片时间
	 * @param $brandid 品牌ID
	 * @param $status -1所有 0为可用，1为删除
	 */
	public function statistics_poster_date($brandid,$status=-1) 
	{
		$criteria = new CDbCriteria;
		$criteria->select = 'distinct(addtime) as addtime';
		$criteria->addCondition('brandid=:brandid');
		$criteria->params[':brandid']=$brandid;
		if(in_array($status,array(0,1))){
			$criteria->addCondition('status=:status');
			$criteria->params[':status']=$status;
		}
		$criteria->order = 'addtime desc,id desc';
		$ret_data = erp_poster::model()->findAll( $criteria );
		return $ret_data;//创建成功返回
	}
	/**
	 * 设置图片主图
	 * @param $clothesid 衣服ID
	 * @param $imageid 图片ID
	 * @param $istop 主图：0为否，1为主图
	 */
	public function set_top($clothesid,$imageid,$istop) 
	{
		return true;//修改成功返回
	}
	
	/**
	 * 通过条件查询订单数据
	 * @param $clothesid 衣服ID
	 * @param $name 图片名
	 * @param $isback 正背面：0为正面，1为背面
	 * @param $type 图片类型：0为灰模图、1为立体图、2为静态图、3为真人模特图、4为细节图
	 * @param $url	图片地址
	 */
	public function upload_imageinfo($clothesid,$name,$isback,$type,$url) 
	{
		/*
		 $istop=0
		 $status=0
		  */
		$obj=array();
		return $obj;//结果成功返回
	}
}
