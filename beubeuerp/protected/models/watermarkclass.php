<?php
/**
 * 订单衣服类
 */
class watermarkclass {

	/**
	 * 通过品牌id和水印类型查询是否存在
	 */
	public static function select_erp_watermark($brandid=0,$type=0) 
	{
		$where="brandid=$brandid";
		if($type!=0)
		{
			$where.=" && type=$type";
		}
		$watermark = Yii::app ()->db->createCommand ()
				->select ( '*' )
				->from ( 'erp_watermark' )
				->where ( $where )
				->queryAll ();
		
		return $watermark;
		
	}
	/**
	 * 水印数据插入
	 * @param $brandid 品牌ID号
	 * @param $array 水印信息
	 */
	public static function insertwatermarkimg($brandid,$array=array()) 
	{
		if(count($array)==0)
		{
			return false;
		}
		$erp_watermark = new erp_watermark();
		$erp_watermark->name=$array['name'];
		$erp_watermark->url=$array['url'];
		$erp_watermark->type=$array['type'];
		$erp_watermark->brandid=$brandid;
		$erp_watermark->insert ();
		return true;
		
	}
	
/**
	* 通过水印id号修改信息
	**/
	public function updatewatermarkbyid($id,$arr){
		
		$is_bool=false;
		$ret=erp_watermark::model()->updateAll($arr,'id=:id',array(':id'=>$id));
		if($ret>0){
			$is_bool=true;
		}
		return $is_bool;//更新成功返回
		
	}
/**
	* 通过水印水印类型和品牌号修改信息
	**/
	public function delwatermarkbytypeandbrandid($type,$brandid){
		
		$sql='delete from erp_watermark where brandid='.$brandid.' and type='.$type;
		$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
		
	}
}
