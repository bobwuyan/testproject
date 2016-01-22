<?php
/**
 * 搭配管理类
 */
class clothesorderclass {
	/**
	 * 
	 * @param $brandnumber 衣服款号
	 * @param $orderid	订单ID
	 * @param $brand_id 品牌ID号
	 */
	public function create_erp_clothes($sku,$brandnumber,$orderid,$brand_id) {	
		$clothes_order=new erp_clothes_order();  
		$clothes_order->sku=$sku;		
		$clothes_order->brandnumber=$brandnumber;
		$clothes_order->pcount=0;
		$clothes_order->mcount=0;
		$clothes_order->dcount=0;
		$clothes_order->description='';
		$clothes_order->orderid=$orderid;
		$clothes_order->brandcategoryid=0;
		$clothes_order->addtime=date("Y-m-d H:i:s");
		$clothes_order->brandid=$brand_id;
		$clothes_order->status=0;
		$insert_id=0;
		if($clothes_order->save ()>0){
		   $insert_id=Yii::app()->db->getLastInsertID();
		}
		return $insert_id;//添加成功返回 ID
	}
	/**
	 * 更新衣服图片数量
	 * @param $clothesid 衣服id
	 * @param $num	图片数量
	 * @param $type	1产品图，2模特图，3细节图
	 */
	public function update_imagecount($clothesid,$num,$type) 
	{
		return true;//更新成功返回
	}
	/**
	 * 更新备注
	 * @param $clothesid
	 * @param $text
	 */
	public function update_description($clothesid,$text) 
	{
		return true;//更新成功返回
	}
	/**
	 * 更新衣服数据
	 * @param $id erp_clothes_order表ID
	 * @param $arr
	 */
	public function update_clothes($id,$arr) 
	{
		$is_bool=false;
		$ret=erp_clothes_order::model()->updateAll($arr,'id=:id',array(':id'=>$id));
		if($ret>0){
			$is_bool=true;
		}
		return $is_bool;//更新成功返回
	}
	/**
	 * 通过条件查询订单衣服
	 * @param $brandid 品牌ID号
	 * @param $status	订单衣服状态 -1为所有
	 * @param $limit 每页显示数量
	 * @param $offset 查询起点
	 * @param $order 排序
	 * @param $arr 查询条件包括
	 * @param $code_template 查询版本
	 * {$arr
	 * brandnumber:款号、
	 * brandcategoryid：品牌自定义分类id、
	 * startime:开始时间、
	 * endtime：结束时间、
	 * imagestatus：图片状态
	 * }
	 */
	public function select_all_clothes($brandid,$status=-1,$arr = array(),$limit=-1,$offset=-1,$order='',$code_template='SKC') 
	{
		$select_str='';
		$order_str='';
		if($code_template!='SKC'){
			$select_str=', max(erp_order.addtime) as max_o_addtime';
			if($order=='erp_order.addtime desc'){
				$order_str='max_o_addtime desc,';
			}else{
				$order_str='max_o_addtime asc,';
			}
		}
		$ret_data=array('data'=>array(),'page_sum'=>0);
		$sql_where_arr=array();
		$sql_where_pram_arr=array();
		$data=Yii::app ()->db->createCommand ()
				->select ( 'erp_clothes_order.*,erp_order.addtime as o_addtime'.$select_str)
				->from ( 'erp_clothes_order' )
				->join('erp_order','erp_order.id=erp_clothes_order.orderid')
				->order($order_str.(empty($order)?'':$order.',').'erp_clothes_order.id DESC')
				->limit ( $limit, $offset);
		if($code_template!='SKC'){
			$data->group('erp_clothes_order.sku');
		}
		$sql_where_arr[]='erp_clothes_order.brandid=:brandid';
		$sql_where_pram_arr[':brandid']=$brandid;
		if($status==0 || $status==1){
			$sql_where_arr[]='erp_clothes_order.status=:status';
			$sql_where_pram_arr[':status']=$status;
		}
		foreach($arr as $key=>$value){
			if($key=='startime'){
				$sql_where_arr[]='DATE_FORMAT(erp_order.`addtime`,"%Y-%m-%d")>=:startime';
				$sql_where_pram_arr[':startime']=$value;
			}elseif($key=='endtime'){
				$sql_where_arr[]='DATE_FORMAT(erp_order.`addtime`,"%Y-%m-%d")<=:endtime';
				$sql_where_pram_arr[':endtime']=$value;
			}elseif($key=='imagestatus'){
				if($value==3){//未审核
					$img_data=Yii::app ()->db->createCommand ()
					->select ( 'distinct(clothes_order_id) as clothes_order_id' )
					->from ( 'erp_image' )
					->where('isshow=0 and status=0')->queryAll();
					$clothes_order_id_arr=array();
					$clothes_order_id_arr[]=0;
					foreach($img_data as $img_value){
						$clothes_order_id_arr[]=$img_value['clothes_order_id'];
					}
					$sql_where_arr[]='erp_clothes_order.id in('.implode(',',$clothes_order_id_arr).')';
				}else if($value==2){//已审核不合格
					$img_data=Yii::app ()->db->createCommand ()
					->select ( 'clothes_order_id,GROUP_CONCAT(status) as str_status' )
					->from ( 'erp_image' )
					->where('isshow=0')->group('clothes_order_id')->queryAll();
					$clothes_order_id_arr=array();
					$clothes_order_id_arr[]=0;
					foreach($img_data as $img_value){
						$str_status=explode(',',$img_value['str_status']);
						if(in_array(2,$str_status) && !in_array(0,$str_status)){
							$clothes_order_id_arr[]=$img_value['clothes_order_id'];
						}
					}
					$sql_where_arr[]='erp_clothes_order.id in('.implode(',',$clothes_order_id_arr).')';
				}else if($value==1){//已审核合格
					$img_data=Yii::app ()->db->createCommand ()
					->select ( 'clothes_order_id,GROUP_CONCAT(status) as str_status' )
					->from ( 'erp_image' )
					->where('isshow=0')->group('clothes_order_id')->queryAll();
					$clothes_order_id_arr=array();
					$clothes_order_id_arr[]=0;
					foreach($img_data as $img_value){
						$str_status=explode(',',$img_value['str_status']);
						if(!in_array(2,$str_status) && !in_array(0,$str_status)){
							$clothes_order_id_arr[]=$img_value['clothes_order_id'];
						}
					}
					$sql_where_arr[]='erp_clothes_order.id in('.implode(',',$clothes_order_id_arr).')';
				}else{//所有
					//$sql_where_arr[]='erp_clothes_order.id not in('.implode(',',$clothes_order_id_arr).')';
				}
			}elseif($key=='search'){
				$search_arr=array();
				foreach($value as $search_key=>$search_value){
					$search_key_str=strtr($search_key,'.','_');
					if(strstr($search_value,'like_')!==false){
						$like_arr=explode('like_',$search_value);
						$search_arr[]=$search_key.' like :'.$search_key_str;
						$sql_where_pram_arr[':'.$search_key_str]=$like_arr[1];
					}else{
						$search_arr[]=$search_key.'=:'.$search_key_str;
						$sql_where_pram_arr[':'.$search_key_str]=$search_value;
					}
				}
				$sql_where_arr[]=implode(' or ',$search_arr);
			}else{
				if(is_array($value)){
					if(strstr($key,'_like')!==false){
						$like_arr=explode('_like',$key);
						$sql_arr=array();
						foreach($value as $v_key=>$v_value){
							$sql_arr[]=$like_arr[0].' like :'.$like_arr[0].'_'.$v_key;
							$sql_where_pram_arr[':'.$like_arr[0].'_'.$v_key]='%'.$v_value.'%';
						}
						$sql_where_arr[]=implode(' or ',$sql_arr);
					}else if(strstr($key,'_not')!==false){
						$like_arr=explode('_not',$key);
						$sql_where_arr[]=$like_arr[0].' not in(\''.implode('\',\'',$value).'\')';
					}else{
						$sql_where_arr[]=$key.' in(\''.implode('\',\'',$value).'\')';
					}
				}else{
					$key_str=strtr($key,'.','_');
					if(strstr($value,'like_')!==false){
						$like_arr=explode('like_',$value);
						$sql_where_arr[]=$key.' like :'.$key_str;
						$sql_where_pram_arr[':'.$key_str]=$like_arr[1];
					}else{
						$sql_where_arr[]=$key.'=:'.$key_str;
						$sql_where_pram_arr[':'.$key_str]=$value;
					}
				}
			}
		}
		$data->where('('.implode(') and (',$sql_where_arr).')',$sql_where_pram_arr);
		$data2=clone $data;
		$ret_data['data'] = $data->queryAll();
		//统计数量
		$data2->limit ( -1, -1)->select('count(*) as count'.$select_str);
		if($code_template!='SKC'){
			$count=$data2->queryAll();
			$data_count['count']=count($count);
		}else{
			$data_count=$data2->queryRow();
		}
		$ret_data['page_sum']=$data_count['count'];
		return $ret_data;//结果成功返回
	}
	
	/**
	 * 通过条件查询单件订单衣服
	 * @param $brandid 品牌ID号
	 * @param $arr 查询条件包括
	 * @param $code_template 查询版本
	 */
	public function select_Single_clothes($brandid,$arr = array(),$code_template='SKC') 
	{
		$ret_data=array('data'=>array(),'page_sum'=>0);
		$select_str='';
		if($code_template!='SKC'){
			//$select_str=', max(erp_order.addtime) as max_o_addtime';
		}
		$data=Yii::app ()->db->createCommand ()
				->select ( 'erp_clothes_order.*'.$select_str )
				->from ( 'erp_clothes_order' )
				->limit (1)
				->where('erp_clothes_order.brandid=:brandid and erp_clothes_order.status=:status',array(':brandid'=>$brandid,':status'=>0));
		if($code_template!='SKC'){
			$data->group('erp_clothes_order.sku');
		}
		$id_Symbol_str='=';
		if(isset($arr['seach_direction'])){
			$order_str='';
			if($arr['seach_direction']=='next'){
				if($code_template!='SKC'){
					//$order_str='max_o_addtime desc,';
				}
				$data->order($order_str.'erp_clothes_order.id DESC');
				$id_Symbol_str='<';
			}else if($arr['seach_direction']=='prev'){
				if($code_template!='SKC'){
					//$order_str='max_o_addtime asc,';
				}
				$data->order($order_str.'erp_clothes_order.id ASC');
				$id_Symbol_str='>';
			}
		}
		foreach($arr as $key=>$value){
			if($key=='seach_direction'){
			}else if($key=='id'){
				$data->andwhere($key.$id_Symbol_str.':'.$key,array(':'.$key=>$value));
			}else{
				if(strstr($key,'_not')!==false){
					$like_arr=explode('_not',$key);
					$data->andwhere($like_arr[0].'<>:'.$key,array(':'.$key=>$value));
				}else if(is_array($value)){
					$data->andwhere($key.' in(\''.implode('\',\'',$value).'\')');
				}else{
					$key_str=strtr($key,'.','_');
					$data->andwhere($key.'=:'.$key_str,array(':'.$key_str=>$value));
				}
			}
		}
		//print_r( $data);exit();
		$ret_data = $data->queryRow();
		return $ret_data;//结果成功返回
	}
	/**
	* 统计图片数量
	**/
	public function selsect_img_count($brandid,$status){
		$data=Yii::app ()->db->createCommand ()
				->select ( 'sum(pcount) as pcount,sum(mcount) as mcount,sum(dcount) as dcount' )
				->from ( 'erp_clothes_order' )
				->where('erp_clothes_order.status=:status and erp_clothes_order.brandid=:brandid',array(':status'=>$status,':brandid'=>$brandid))->queryRow();
		return $data;
	}
	
	/**
	* 通过款号来找出订单名
	**/
	public function selsectordernamebybrandnumber($brandid,$brandnumber_array,$orderid=0){
		
		$where='';
		$data=Yii::app ()->db->createCommand ()
				->select ( 'orderid,brandnumber,ordername' )
				->from ( 'erp_brandnumber_order' )
				->join('erp_order','erp_order.id=erp_brandnumber_order.orderid')
				->where("erp_brandnumber_order.brandnumber in ('".$brandnumber_array."') and erp_brandnumber_order.brandid=$brandid");
		if($orderid>0)
		{
			$data->andwhere("erp_brandnumber_order.orderid<>$orderid");
		}
		$ret_data=$data->queryAll();
		return $ret_data;
	}
	
	
}
