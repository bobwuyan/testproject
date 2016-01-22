<?php
/**
 * 订单衣服类
 */
class pushcountclass {

	/**
	 * 统计出推送的时间
	 * @param unknown_type $brandid
	 */
	public static function select_push_date($brandid=0) 
	{
		$erp_push_list = Yii::app ()->db->createCommand ()
				->select ( 'addtime' )
				->from ( 'erp_push_count' )
				->where ( "brandid=$brandid && status=0" )
				->queryAll ();
		if(count($erp_push_list)>0)
		{
			$datearray=array();
			foreach($erp_push_list as $value)
			{
				$addtime=substr($value['addtime'],0,10);
				if(!empty($value['addtime']) && !in_array($addtime,$datearray))
				{
					$datearray[]=$addtime;
				}
			}
		}
		return $datearray;
		
	}
	
/**
	 * 通过条件查询推送数据
	 * @param $arr 查询条件包括
	 * {
	 * 		startime:开始时间、
	 * 		endtime：结束时间、
	 * 		patformid 平台id
	 * 		sku或skc 搜索类型 
	 * }
	 * @param  $brandid 品牌ID
	 * @param  $page 当前页
	 * @param  $pagecount = 20, 每页数量
	 */
	public static function select_all_push($page = 1, $pagecount = 20,$brandid=0,$arr = array()) 
	{
		$page = trim ( $page );
		$pagecount = trim ( $pagecount );
		if (empty ( $page ) || !is_numeric($page) || intval($page)!=$page || $page<=0) { //规范页码
			$page = 1;
		}
		if (empty ( $pagecount ) || !is_numeric($pagecount) || intval($pagecount)!=$pagecount || $pagecount<=0) { //规范数据量
			$pagecount = 20;
		}
		
		$w_s = 'status=0';
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			if (isset ( $arr ['strd_data'])) {
				$str_param = trim ( $arr ['strd_data'] );
				$w_s .= " and DATE_FORMAT(erp_push_count.addtime,'%Y-%m-%d')>='{$str_param}'";
			}
			if (isset ( $arr ['end_data'] )) {
				$str_param = trim ( $arr ['end_data'] );
				$w_s .= " and DATE_FORMAT(erp_push_count.addtime,'%Y-%m-%d')<='{$str_param}'";
			}
			if (isset ( $arr ['key'] ) && !empty ( $arr ['key'] )) { //规范关键词
				$str_param = trim ( $arr ['key'] );
				$w_s .= " and (erp_push_count.skc like '%{$str_param}%')";
				
			}
			echo $arr ['patformid'];
			if (isset ( $arr ['patformid'] ) && !empty ( $arr ['patformid'] )) { //规范关键词
				$patformid = trim ( $arr ['patformid'] );
				$w_s .= " and (erp_push_count.patformid=$patformid)";
			}
		}
		
		$w_s .= " and (erp_push_count.brandid=$brandid)";
		
		$sql = Yii::app ()->db->createCommand ()
			->select ( "id,skc,addtime,patformid,count(skc) as count" )
			->from ( 'erp_push_count' )
			->where ( $w_s )
			->group('skc')
			->order("id desc");
			
		$erp_push_list = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
		
		
		$erp_push_count = Yii::app ()->db->createCommand ()
				->select ( 'id' )
				->from ( 'erp_push_count' )
				->where ( $w_s )
				->group('skc')
				->queryAll ();
				
		$con=count($erp_push_count);
		
		
		$criteria = new CDbCriteria();
		$pages=new CPagination($con);
		$pages->pageSize=$pagecount;
		$pages->applyLimit($criteria);
		//获取总个数
		//$con = $sql2->queryAll ();
		$ret['data']=array ('data' => $erp_push_list, 'count' => $con, 'pageSize' => $pagecount, 'page' => $pages);
		$ret['status']=1;
		return $ret;//结果成功返回
	}
	/**
	 * 创建文件夹
	 * */
	public static  function createFolder($path) {
		
		if (! file_exists ( $path )) {
			mkdir ( $path, 0777, true );
		}
	}
	//插入推送统计表
	public function insert_push_count($data) {	
		$push_count=new erp_push_count();       
		$push_count->skc=$data['skc'];
		$push_count->sku=$data['sku'];
		$push_count->addtime=$data['addtime'];
		$push_count->patformid=$data['patformid'];
		$push_count->status=$data['status'];
		$push_count->clothesimageid=$data['clothesimageid'];
		$push_count->userid=$data['userid'];
		$push_count->brandid=$data['brandid'];
		
		$insert_id=0;
		if($push_count->save ()>0){
		   $insert_id=Yii::app()->db->getLastInsertID();
		}
		return $insert_id;//添加成功返回 ID
	}
}
