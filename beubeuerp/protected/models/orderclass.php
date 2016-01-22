<?php
/**
 * 订单衣服类
 */
class orderclass {
	/**
	 * 下载订单文件夹
	 * @param $orderid 订单ID
	 */
	public static function download_folder($orderid) 
	{
		return true;//下载成功返回
	}
	function addFileToZip($path,$zip){
	    $handler=opendir($path); //打开当前文件夹由$path指定。
	    while(($filename=readdir($handler))!==false){
	        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
	            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
	            	if($filename!="orderlist"){
	            	 $zip->addEmptyDir($path."/".$filename);
	            	 $this->addFileToZip($path."/".$filename, $zip);
	            	}
	            }else{ //将文件加入zip对象
	            	// $zip->addFile($path."/".$filename);
	            }
	        }
	    }
	    @closedir($path);
	}
	/**
	 * 创建文件夹
	 * */
	public static  function createFolder($path) {
		
		if (! file_exists ( $path )) {
			mkdir ( $path, 0777, true );
		}
	}
	public  function delFolder($dir) 
	{
		
	  	//先删除目录下的文件：
		  $dh=opendir($dir);
		  while ($file=readdir($dh)) {
		    if($file!="." && $file!="..") {
		      $fullpath=$dir."/".$file;
		      
		      if(!is_dir($fullpath)) {
		      	 unlink($fullpath);
		      } else {
		      	
		          $this->delFolder($fullpath);
		      }
		    }
		  }
		 
		  closedir($dh);
		
		  //删除当前文件夹：
		  if(rmdir($dir)) {
		    return true;
		  } else {
		    return false;
		  }
	}
	/**
	 * 创建文件夹
	 * @param $ordername 品牌id
	 * @param $ordername 订单号名字
	 * @param $brandnumberarray	款号数组
	 */
	public static function create_order($brandid,$ordername,$brandnumberarray= array()) 
	{
		$arr=array();
		$output='';
		$memcache = md5('getbrandsku'.$brandid);
		$boo = Yii::app()->cache->get($memcache);
		//缓存 不存在时候调用
		if(empty($boo)){
			$arr=Yii::app ()->db->createCommand ()
				->select ( 'code_start,code_end' )
				->from ( 'beu_brand' )
				->where ( "id=$brandid" )
				->queryRow ();
			if(!empty($arr))
			{
				$output = json_encode ( $arry );
				Yii::app()->cache->set($output,$memcache,18000);//添加缓存
			}
		}else{
			 $arr = json_decode($boo,true);
		}
		
		if(count($brandnumberarray)>0)
		{
			if(!empty($arr))
			{
				$start=$arr['code_start'];//开始
				$end=$arr['code_end'];//结束
				foreach ($brandnumberarray as $value)
				{
					
					$brand_sku=substr($value,$start,$end-$start);
					if(!empty($value)){
						$url=$_SERVER ['DOCUMENT_ROOT'].'/orderlist/'.$ordername.'/'.$brand_sku.'/'.$value.'/product/';
						orderclass::createFolder($url);
						$url=$_SERVER ['DOCUMENT_ROOT'].'/orderlist/'.$ordername.'/'.$brand_sku.'/'.$value.'/model/';
						orderclass::createFolder($url);
						$url=$_SERVER ['DOCUMENT_ROOT'].'/orderlist/'.$ordername.'/'.$brand_sku.'/'.$value.'/details/';
						orderclass::createFolder($url);
					}
				}
			}
		}
		return true;//创建成功返回
	}
	
	/**
	 * 改变订单备注
	 * @param $orderid 订单ID
	 * @param $description 备注
	 */
	public static function change_order($orderid,$description) 
	{
		return true;//修改成功返回
	}
	
	/**
	 * 通过条件查询订单数据
	 * @param $arr 查询条件包括
	 * {
	 * 		ordername:订单名、需支持模糊查询
	 * 		startime:开始时间、
	 * 		endtime：结束时间、
	 * 		dateorder 时间排序 默认是倒序
	 * 		type 搜索类型 1为订单号，2为备注
	 * }
	 * @param  $brandid 品牌ID
	 * @param  $page 当前页
	 * @param  $pagecount = 20, 每页数量
	 */
	public static function select_all_order($page = 1, $pagecount = 20,$brandid=0,$arr = array()) 
	{
		$page = trim ( $page );
		$pagecount = trim ( $pagecount );
		if (empty ( $page ) || !is_numeric($page) || intval($page)!=$page || $page<=0) { //规范页码
			$page = 1;
		}
		if (empty ( $pagecount ) || !is_numeric($pagecount) || intval($pagecount)!=$pagecount || $pagecount<=0) { //规范数据量
			$pagecount = 20;
		}
		
		$w_s = '1=1';
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			if (isset ( $arr ['strd_data'])) {
				$str_param = trim ( $arr ['strd_data'] );
				$w_s .= " and DATE_FORMAT(erp_order.addtime,'%Y-%m-%d')>='{$str_param}'";
			}
			if (isset ( $arr ['end_data'] )) {
				$str_param = trim ( $arr ['end_data'] );
				$w_s .= " and DATE_FORMAT(erp_order.addtime,'%Y-%m-%d')<='{$str_param}'";
			}
			if (isset ( $arr ['key'] ) && !empty ( $arr ['key'] )) { //规范关键词
				$str_param = trim ( $arr ['key'] );
				if(isset ( $arr ['type'] ) && !empty ( $arr ['type'] ))
				{
					if($arr ['type']==1){
						$w_s .= " and (erp_order.ordername like '%{$str_param}%')";
					}else{
						$w_s .= " and (erp_order.description like '%{$str_param}%')";
					}
				}
			}
		}
		//时间排序
		if(!isset($arr['dateorder'])){
			$order_date = 'erp_order.addtime desc';
		}else{
			$str_param=$arr['dateorder'];
			$order_date = "erp_order.addtime {$str_param}";
		}
		
		$w_s .= " and (erp_order.brandid=$brandid)";
		
		$sql = Yii::app ()->db->createCommand ()
			->select ( "erp_order.id,erp_order.ordername,erp_order.description,erp_order.barcodecount,erp_order.addtime,erp_order.brandid" )
			->from ( 'erp_order' )
			->where ( $w_s )
			->order($order_date);
		$orderlist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
		$ordercount = Yii::app ()->db->createCommand ()
				->select ( 'count(id) as count' )
				->from ( 'erp_order' )
				->where ( $w_s )
				->queryRow ();
		if(count($orderlist)==0)
		{
			//throw new Exception('衣服列表数据为空！');
		}
		$con=0;
		if(!empty($ordercount))
		{
			$con=$ordercount['count'];
		}
		
		$criteria = new CDbCriteria();
		$pages=new CPagination($con);
		$pages->pageSize=$pagecount;
		$pages->applyLimit($criteria);
		//获取总个数
		//$con = $sql2->queryAll ();
		$ret['data']=array ('data' => $orderlist, 'count' => $con, 'pageSize' => $pagecount, 'page' => $pages);
		$ret['status']=1;
		return $ret;//结果成功返回
	}
	/**
	 * 统计出订单的时间
	 * @param unknown_type $brandid
	 */
	public static function select_order_date($brandid=0) 
	{
		
		$orderlist = Yii::app ()->db->createCommand ()
				->select ( 'addtime' )
				->from ( 'erp_order' )
				->where ( "brandid=$brandid" )
				->queryAll ();
		if(count($orderlist)>0)
		{
			$datearray=array();
			foreach($orderlist as $value)
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
	 * 通过品牌id和订单名获取订单ID号
	 * @param unknown_type $brandid 品牌ID号
	 * @param unknown_type $name_array 订单名字数组
	 */
	public static function select_order_id($brandid=0,$name_array=array()) 
	{
		if(count($name_array)==0)
		{
			return array();
		}else{
			$name_array=implode ( "','", $name_array );
		}
		
		$orderlist = Yii::app ()->db->createCommand ()
				->select ( 'id,ordername' )
				->from ( 'erp_order' )
				->where ( "brandid=$brandid" )
				->where ( "ordername in('".$name_array."')" )
				->queryAll ();
		
		return $orderlist;
		
	}
	/**
	 * 通过品牌id和订单ID号获取订单名
	 * @param unknown_type $brandid 品牌ID号
	 * @param unknown_type $id_array 订单ID数组
	 */
	public static function selectOrderNameByOrderID($brandid,$id_array=array()) 
	{
		$orderlist = Yii::app ()->db->createCommand ()
				->select ( 'id,ordername' )
				->from ( 'erp_order' )
				->where ( "brandid=$brandid" );
				if(count($id_array)>0){
					$orderlist->where ( "id in('".implode ( "','", $id_array )."')" );
				}
		$orderlist=$orderlist->queryAll ();
		
		return $orderlist;
		
	}
/**
	* 通过订单号删除该订单下的所有款号
	**/
	public function deletebrandnumberbyorderid($brandid,$orderid){
		
		$sql='delete from erp_brandnumber_order where brandid='.$brandid.' and orderid='.$orderid;
		$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
		
	}
}
