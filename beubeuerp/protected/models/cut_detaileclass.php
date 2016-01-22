<?php
/**
 * 订单衣服类
 */
class cut_detaileclass {

	/**
	 * 通过品牌id和水印类型查询是否存在
	 */
	public static function select_erp_cut_detaile($brandid) 
	{
		
		$cut_detaile = Yii::app ()->db->createCommand ()
				->select ( '*' )
				->from ( 'erp_cut_detaile' )
				->where("brandid=$brandid && status=0")
				->queryAll ();
		
		return $cut_detaile;
		
	}
	/**
	 * 通过品牌id和平台id查询是否存在
	 */
	public static function select_cut_detailebypatformid($brandid,$patform=0) 
	{
		$where="brandid=$brandid";
		if($patform!=0)
		{
			$where.=" && patformid=$patform";
		}
		$cut_detaile = Yii::app ()->db->createCommand ()
				->select ( '*' )
				->from ( 'erp_cut_detaile' )
				->where($where)
				->queryRow ();
		
		return $cut_detaile;
		
	}
	/**
	 * 裁剪数据插入
	 * @param $brandid 品牌ID号
	 * @param $array 裁剪数据信息
	 */
	public static function insertcutdetail($brandid,$array=array()) 
	{
		if(count($array)==0)
		{
			return false;
		}
		$erp_cut_detaile = new erp_cut_detaile();
		$erp_cut_detaile->patformid=$array['patformid'];
		$erp_cut_detaile->width=$array['width'];
		$erp_cut_detaile->height=$array['height'];
		$erp_cut_detaile->brandid=$brandid;
		$erp_cut_detaile->addtime=$array['addtime'];
		$erp_cut_detaile->barcodetype=$array['barcodetype'];
		$erp_cut_detaile->status=$array['status'];
		$erp_cut_detaile->havewatermark=$array['havewatermark'];;
		$erp_cut_detaile->watermarkwidth=$array['watermarkwidth'];
		$erp_cut_detaile->watermarkheight=$array['watermarkheight'];
		$erp_cut_detaile->positionx=$array['positionx'];
		$erp_cut_detaile->positiony=$array['positiony'];
		$erp_cut_detaile->insert ();
		return true;
		
	}
	
/**
	* 通过裁剪id号修改信息
	**/
	public function updatecutdetailbyid($id,$arr){
		
		$is_bool=false;
		$ret=erp_cut_detaile::model()->updateAll($arr,'id=:id',array(':id'=>$id));
		if($ret>0){
			$is_bool=true;
		}
		return $is_bool;//更新成功返回
		
	}
/**
	* 通过裁剪平台id号修改信息
	**/
	public function updatecutdetailbypatformid($brandid,$patformid,$arr){
		
		$is_bool=false;
		$ret=erp_cut_detaile::model()->updateAll($arr,'brandid=:brandid and patformid=:patformid',array(':brandid'=>$brandid,'patformid'=>$patformid));
		if($ret>0){
			$is_bool=true;
		}
		return $is_bool;//更新成功返回
		
	}
}
