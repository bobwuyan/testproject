<?php

/**
* 裁剪推送数据
**/
class pushDetaileclass{
	/**
	 * 增加图片推送数据
	 */
	public function create_erp_push($data) {	
		$push_detaile=new erp_push_detaile();       
		$push_detaile->cutid=$data['cutid'];
		$push_detaile->brandid=$data['brandid'];
		$push_detaile->watermarkid=$data['watermarkid'];
		$push_detaile->imageid=$data['imageid'];
		$push_detaile->sku=$data['sku'];
		$push_detaile->skc=$data['skc'];
		$push_detaile->sort=$data['sort'];
		$push_detaile->pushstatus=0;
		$push_detaile->top=$data['top'];
		$push_detaile->positionx=$data['positionx'];
		$push_detaile->positiony=$data['positiony'];
		$push_detaile->width=$data['width'];
		$push_detaile->height=$data['height'];
		$push_detaile->CutOut_width=$data['CutOut_width'];
		$push_detaile->CutOut_height=$data['CutOut_height'];
		$push_detaile->img_w=$data['img_w'];
		$push_detaile->img_h=$data['img_h'];
		$push_detaile->addtime=date("Y-m-d H:i:s");
		$insert_id=0;
		if($push_detaile->save ()>0){
		   $insert_id=Yii::app()->db->getLastInsertID();
		}
		return $insert_id;//添加成功返回 ID
	}
	/**
	 * 通过条件查询推送数据
	 * @param $brandid 品牌ID号
	 * @param $limit 每页显示数量
	 * @param $offset 查询起点
	 * @param $order 排序
	 * @param $arr 查询条件包括
	 * {$arr
	 * brandnumber:款号、
	 * brandcategoryid：品牌自定义分类id、
	 * startime:开始时间、
	 * endtime：结束时间、
	 * imagestatus：图片状态
	 * }
	 */
	public function select_all_push($brandid,$arr = array(),$limit=-1,$offset=-1,$order='',$pram=array()) 
	{
		$ret_data=array('data'=>array(),'page_sum'=>0);
		$sql_where_arr=array();
		$sql_where_pram_arr=array();
		$data=Yii::app ()->db->createCommand ()
				->select ( '*')
				->from ( 'erp_push_detaile' )
				->order((empty($order)?'':$order.',').'id DESC')
				->limit ( $limit, $offset);
		foreach($pram as $key=>$value){
			$data->$key($value);
		}
		$sql_where_arr[]='brandid=:brandid';
		$sql_where_pram_arr[':brandid']=$brandid;
		foreach($arr as $key=>$value){
			if($key=='distinct'){
				$data->select('distinct('.$value.') as count_'.$value);
			}else if($key=='startime'){
				$sql_where_arr[]='DATE_FORMAT(`addtime`,"%Y-%m-%d")>=:startime';
				$sql_where_pram_arr[':startime']=$value;
			}elseif($key=='endtime'){
				$sql_where_arr[]='DATE_FORMAT(`addtime`,"%Y-%m-%d")<=:endtime';
				$sql_where_pram_arr[':endtime']=$value;
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
		$data_count=$data2->queryRow();
		
		$ret_data['page_sum']=$data_count['count'];
		return $ret_data;//结果成功返回
	}
	
	/**
	 * 更新推送数据
	 * @param $id erp_push_detaile表ID
	 * @param $arr
	 */
	public function update_push($id,$arr) 
	{
		$is_bool=false;
		$ret=erp_push_detaile::model()->updateAll($arr,'id=:id',array(':id'=>$id));
		if($ret>0){
			$is_bool=true;
		}
		return $is_bool;//更新成功返回
	}
	/**
	 * 删除推送数据
	 * @param $id erp_push_detaile表ID
	 * @param $arr
	 */
	public function del_push($id) 
	{
		$is_bool=false;
		$ret=erp_push_detaile::model()->deleteAll('id in('.$id.')');
		if($ret>0){
			$is_bool=true;
		}
		return $is_bool;//更新成功返回
	}
	/**
	 * $array 裁剪信息
	 * {watermark_id	水印ID号，为0代表无水印
	 * watermark_width 水印图片的宽
	 * watermark_height 水印图片的高
	 * watermark_url	水印图片的地址
	 * watermark_positionx 水印图片x坐标
	 * watermark_positiony 水印图片y坐标
	 * patform_width 平台的宽
	 * patform_height 平台的高
	 * image_url 推送的图片地址
	 * cut_positionx 裁剪的x坐标
	 * cut_positiony 裁剪的y坐标
	 * cut_width 裁剪的宽
	 * cut_height 裁剪的高}
	 * $brandid品牌ID
	 */
	public function cut_image($array,$brandid)
	{
		$ret=array('status'=>0,'mag'=>'','url'=>"",'qiniu_array'=>array());
		if(count($array)==0)
		{
			return $ret;
		}else if(empty($array['image_url']) || empty($array['patform_width']) || empty($array['patform_height']))
		{
			return $ret;
		}
		$back_array=array();
		$memcache = md5('qiniucount'.$brandid);
		$boo = Yii::app()->cache->get($memcache);
		$qiniu_array=array();
		if(empty($boo)){
			$erp_qiniu =new erp_qiniu();
			$qiniu_array=$erp_qiniu->getAccountByBrand($brandid);
			$output = json_encode ( $qiniu_array );
			Yii::app()->cache->set($output,$memcache,18000);//添加缓存
		}else{
			$qiniu_array = json_decode($boo,true);
		}
		
		if(count($qiniu_array)>0)
		{
			$type=".jpg";
			if(strpos($array['image_url'],".png"))
			{
				$type=".png";
			}
			$background_y_url=$qiniu_array[0]['domain']."background".$type;
			
		}else{
			return $ret;
		}
		//将背景放大到平台设置的尺寸
		$background_m_url=$background_y_url."?imageMogr2/thumbnail/{$array['patform_width']}x{$array['patform_height']}!";
		
		
		$cut_y_width=$array['cut_width'];//数据库记录的图片裁剪宽
		$cut_y_height=$array['cut_height'];//数据库记录的图片裁剪高
		$array['cut_width']=$array['cut_width']==0?$array['img_w']:$array['cut_width'];
		$array['cut_height']=$array['cut_height']==0?$array['img_h']:$array['cut_height'];
		
		
		//通过原始坐标和宽高计算在原图裁剪具体的位置及宽高
		$cut_m_positionx=$array['cut_positionx']<0?0:$array['cut_positionx'];
		$cut_m_positiony=$array['cut_positiony']<0?0:$array['cut_positiony'];
		
		$cut_m_width=$array['cut_width'];//需要裁剪的宽
		$cut_m_height=$array['cut_height'];//需要裁剪的高
		
		//echo $cut_m_positionx."==".$cut_m_positiony."==".$cut_m_width."==".$cut_m_height;
		
		//通过裁剪坐标及宽高对原图进行裁剪
		$cut_m_url=$qiniu_array[0]['domain'].$array['image_url']."?imageMogr2/crop/!{$cut_m_width}x{$cut_m_height}a{$cut_m_positionx}a{$cut_m_positiony}";
		
		$image_width=$array['img_w'];//原图宽
		$image_height=$array['img_h'];//原图高
		$zoom_ratio=1;
		
		
		$image_width_cut_ratio=$array['patform_width']/$cut_m_width;//平台设置的宽和需要裁剪的宽之间的比例
		$image_height_cut_ratio=$array['patform_height']/$cut_m_height;
		if($cut_m_width>$array['patform_width'] || $cut_m_height>$array['patform_height'])
		{
			$zoom_ratio=$image_width_cut_ratio<$image_height_cut_ratio?$image_width_cut_ratio:$image_height_cut_ratio;
		}
		$a=$zoom_ratio*100;
		$cut_m_url=$cut_m_url."|imageMogr2/thumbnail/!{$a}p";
		
		
		
		//echo $cut_m_url."\n\r";
		$dx=$array['cut_positionx']<0?round(abs($array['cut_positionx'])*$zoom_ratio):0;
		$dy=$array['cut_positiony']<0?round(abs($array['cut_positiony'])*$zoom_ratio):0;
		if($cut_y_width==0 && $cut_y_height==0)//为0时说明是没有进行裁剪处理
		{
			
			$cut_y_width=round($cut_m_width*$zoom_ratio);
			$cut_y_height=round($cut_m_height*$zoom_ratio);
			$dx=round(($array['patform_width']-$cut_y_width)/2);
			$dy=round(($array['patform_height']-$cut_y_height)/2);
		}

		$cut_m_64_url= \Qiniu\base64_urlSafeEncode($cut_m_url);
		
		//http://developer.qiniu.com/resource/gogopher.jpg?imageMogr2/thumbnail/200x300!
		
		
		//$water_url=$baiyi_domin.$array['watermark_url']"?imageMogr2/thumbnail/200x300!";
		
		
		$cut_m_url=$background_m_url."|watermark/3/image/$cut_m_64_url/gravity/NorthWest/dx/{$dx}/dy/{$dy}";
		
		if(isset($array['watermark_id']) && $array['watermark_id']>0 && !empty($array['watermark_url']) && !empty($array['watermark_width']) && !empty($array['watermark_height']))
		{
			$baiyi_domin="http://erp.beubeu.com/";
			$water_url=\Qiniu\base64_urlSafeEncode($baiyi_domin.$array['watermark_url']);
			$watermark_url=getimagesize($baiyi_domin.$array['watermark_url']);
			$weight=$watermark_url["0"];////获取水印图片的宽
			$height=$watermark_url["1"];///获取水印图片的高
			$back_warter_url=$background_y_url."?imageMogr2/thumbnail/{$weight}x{$height}!";
			$back_warter_url=$back_warter_url."|watermark/3/image/$water_url/gravity/NorthWest/dx/0/dy/0|imageMogr2/thumbnail/{$array['watermark_width']}x{$array['watermark_height']}!";
			
			$water_url=\Qiniu\base64_urlSafeEncode($back_warter_url);
			$water_dx=$array['watermark_positionx']>0?$array['watermark_positionx']:0;
			$water_dy=$array['watermark_positiony']>0?$array['watermark_positiony']:0;
			$cut_m_url.="|watermark/3/image/$water_url/gravity/NorthWest/dx/{$water_dx}/dy/{$water_dy}";
			
		}
		
		$ret['qiniu_array']=$qiniu_array;
		$ret['url']=$cut_m_url;
		$ret['status']=1;
		return $ret;
		
	
	}
	
	
}?>