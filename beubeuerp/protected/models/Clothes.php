<?php
/**
 * 衣服类
 * 与衣服相关的所有操作
 */
class Clothes {
	
	/**
	 * 用于beu_clothes表里video字段视频上传缓存入Session的命名
	 */
	private static $void_Session_name='clothesVideo';
	
	/**
	 * 用于beu_clothes表里imagescontent字段图片上传缓存入Session的命名
	 */
	private static $Img_Session_name='clothesImgCount';
	
	/**
	 * 用于beu_clothes表里video字段视频删除缓存入Session的命名
	 */
	private static $void_Delete_Session_name='clothesVideoDel';
	
	/**
	 * 用于beu_clothes表里imagescontent字段图片删除缓存入Session的命名
	 */
	private static $Img_Delete_Session_name='clothesImgCountDel';
	/**
	 * 用于beu_clothes表里imagescontent字段图片删除缓存入Session的命名
	 */
	private static $Int_max=99999999;
	
	
	/**
	 * 根据id获取touch_clothes表数据
	 * @parm $id
	 */
	public static function tclothesSelectById($id,$touchid) {
		$id = trim ( $id );
		$touchid = trim ( $touchid );
		try {
//			Comm::checkValue ( $id, 'ID', 1, 1, self::$Int_max);
//			Comm::checkValue ( $touchid, 'ID', 1, 1,self::$Int_max );
		} catch ( BeubeuException $e ) {
//			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		try {
			$sub_where='';
			if(!empty($_SESSION['table_where'])){
				$sub_where=' and '.$_SESSION['table_where'];
			}
			$sql = 'select * from '.$_SESSION['clothes_table'].' where pid=' . $id .' and touchid='.$touchid.$sub_where;
			$b = Yii::app ()->db->createCommand ( $sql )->queryAll ();
			if (count ( $b ) > 0) {
				return $b [0];
			} else {
//				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( Exception $e ) {
//			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 对触摸屏添加衣服信息
	 * @parm $clothesid_str 衣服id号字符串，每个id之间以‘，’分隔
	 * @parm $touchid 触摸屏id 要将衣服添加到的触摸屏id
	 * @return 返回数据添加情况 array{1=>'数据已存在','on'=>3}表示衣服id为1的数据已存在，另3条数据插入成功
	 */
	public static function tclothesForAdd($clothesid_str = '', $touchid = 0) {
		$touchid = trim ( $touchid );
		$clothesid_str = trim ( $clothesid_str );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1,self::$Int_max );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		
		
		if (! empty ( $clothesid_str )) {
			$clothesid_arr = explode ( ',', $clothesid_str );
			$clothesid_arr = array_unique ( $clothesid_arr ); //去重
			foreach ( $clothesid_arr as $key => $value ) {
				try {
					Comm::checkValue ( $value, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
				} catch ( Exception $e ) {
					unset ( $clothesid_arr [$key] );
				}
			}
			$clothesid_str = join ( ',', $clothesid_arr );
			$re_arr = array ();
			try {
				$se_clothes_arr = touch_clothes::model ()->findAll ( 'touchid=:touchid and clothesid in (' . $clothesid_str . ')', array (':touchid' => $touchid ) );
				foreach ( $se_clothes_arr as $value ) {
					if (in_array ( $value ['clothesid'], $clothesid_arr )) {
						$re_arr [$value ['clothesid']] = Yii::t ( 'beu_brand', '数据已存在' );
						unset ( $clothesid_arr [array_search ( $value ['clothesid'], $clothesid_arr )] ); //array_search获取值所对应的键
					}
				}
			} catch ( Exception $e ) {
			}
			if (count ( $clothesid_arr ) > 0) {
				$date = date ( 'Y-m-d H:i:s', time () );
				$sql_arr = array ();
				foreach ( $clothesid_arr as $value ) {
					$sql_arr [] = "($value,$touchid,0,11,0,0,'$date',9999)";
				}
				
				$sql = "insert into touch_clothes (clothesid,touchid,trycount,status,acquiescetry,recommend,createdate,sort) values" . join ( ',', $sql_arr );
				try {
					$b = Yii::app ()->db->createCommand ( $sql )->execute ();
					$re_arr ['on'] = $b;
					try{
					self::tclothesSelectByTouchid($touchid);
					}catch (Exception $e){}
					try{
					self::tclothesSelectByTouchidModel($touchid);
					}catch (Exception $e){}
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
				}
				return $re_arr;
			}
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
	}
	
	/**
	 * 将衣服从触摸屏删除
	 * @parm $id touch_clothes的pid 可以是多个pid用‘，’拼接起的字符串
	 * @return 成功返回true
	 */
	public static function tclothesDeleteById($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, "ID", 1, 1 ,self::$Int_max);
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$id_arr = explode ( ',', $id );
		$id_arr = array_unique ( $id_arr ); //去重
		sort ( $id_arr );
		try {
			touch_clothes::model ()->deleteByPk ( $id_arr );
			return true;
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '删除数据失败' ), BeubeuException::SQL_UPDATE_ERR );
		}
	}
	
	/**
	 * 对触摸屏修改衣服信息
	 * @parm $id touch_clothes的pid
	 * @parm $arr=array 修改数据
	 * status 衣服上下架状态
	 * acquiescetry 默认穿着
	 * recommend 是否推荐
	 * sort 排序
	 * @return 成功返回true，否则返回false
	 */
	public static function tclothesUpdateById($id = 0, $arr = array()) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, "ID", 0, 1 ,self::$Int_max);
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$updata_arr = array ();
			if (isset ( $arr ['status'] )) {
				$str_param = trim ( $arr ['status'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '状态' ), 1, 0 );
					$updata_arr ['status'] = $str_param;
				} catch ( Exception $e ) {}
			}
			if (isset ( $arr ['acquiescetry'] )) {
				$str_param = trim ( $arr ['acquiescetry'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '默认穿衣' ), 1, 0 );
					$updata_arr ['acquiescetry'] = $str_param;
				} catch ( Exception $e ) {}
			}
			if (isset ( $arr ['sort'] )) {
				$str_param = trim ( $arr ['sort'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '序号' ), 1, 1, 9999 );
					$updata_arr ['sort'] = $str_param;
				} catch ( Exception $e ) {}
			}
			if (isset ( $arr ['style_sort'] )) {
				$str_param = trim ( $arr ['style_sort'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '序号' ), 1, 1, 9999 );
					$updata_arr ['style_sort'] = $str_param;
				} catch ( Exception $e ) {}
			}
			if (isset ( $arr ['recommend'] )) {
				$str_param = trim ( $arr ['recommend'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '推荐' ), 1, 0 );
					$updata_arr ['recommend'] = $str_param;
				} catch ( Exception $e ) {}
			}
			if (count ( $updata_arr ) > 0) {
				try {
					touch_clothes::model ()->updateAll ( $updata_arr, 'pid=:textx', array (':textx' => $id ) );
					try{
//					self::tclothesSelectByTouchid($touchid);
					}catch (Exception $e){}
					try{
//					self::tclothesSelectByTouchidModel($touchid);
					}catch (Exception $e){}
					return true;
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
				}
			}
		}
	}
	
	/**
	 * 根据条件查找触摸屏里所有的衣服
	 * @parm $touchid 触摸屏ID
	 * @parm $page 当前页
	 * @parm $pagecount 每页显示数量
	 * @parm $arr:array 参数数组
	 * touchid 触摸屏id
	 * modeltype 模特类型
	 * brandid 品牌ID
	 * clothescategory 一级品类
	 * clothescategory2 二级品类
	 * status 衣服上下架状态,true表示只找上架衣服 false查找所有衣服
	 * strd_data 开始时间
	 * end_data 结束时间
	 * key 搜索关键字
	 * @return 成功后返回数据 array ('data' => $newstypelist, 'count' => $con,'pageSize'=>$pagecount,'page'=>$page ) data是查询到的数据，count是总页数，pageSize是每页显示数量，page是当前页
	 */
	public static function tclothesSelectByParam($page = 1, $pagecount = 40, $arr = array()) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			$page = trim ( $page );
			$pagecount = trim ( $pagecount );
			if (empty ( $page ) || !is_numeric($page) || intval($page)!=$page || $page<=0) { //规范页码
				$page = 1;
			}
			if (empty ( $pagecount ) || !is_numeric($pagecount) || intval($pagecount)!=$pagecount || $pagecount<=0) { //规范数据量
				$pagecount = 40;
			}
		 
			$w_s = '1';
			$w_s_arr = array ();
			if (count ( $arr ) > 0 && gettype($arr)=='array') {
				if (!empty($arr['status'])) {//设置了查询衣服状态 就按设置的状态查询 否则查询 大于等于8的衣服
					 $w_s .= ' and clothes.status in('.$arr['status'].')';
				}else{
					$w_s .= ' and clothes.status>=8';
				}
				if (isset ( $arr ['touchid'] ) && !empty ( $arr ['touchid'] ) && is_numeric($arr ['touchid']) && intval($arr ['touchid'])==$arr ['touchid'] && $arr ['touchid']>0) {
					$str_param = trim ( $arr ['touchid'] );
					$w_s .= " and clothes.touchid={$str_param}";
				}
				
				if (isset ( $arr ['brandid'] ) && !empty ( $arr ['brandid'] ) && is_numeric($arr ['brandid']) && intval($arr ['brandid'])==$arr ['brandid'] && $arr ['brandid']>0) {
					$str_param = trim ( $arr ['brandid'] );
					$w_s .= " and beu_clothes.brandid={$str_param}";
				}
				if (isset ( $arr ['modeltype'] ) && !empty ( $arr ['modeltype'] ) && is_numeric($arr ['modeltype']) && intval($arr ['modeltype'])==$arr ['modeltype'] && $arr ['modeltype']>0) {
					$str_param = trim ( $arr ['modeltype'] );
					$w_s .= " and beu_clothes.modelgender={$str_param}";
				}
				 
				if (isset ( $arr ['clothescategory'] ) && !empty ( $arr ['clothescategory'] ) && is_numeric($arr ['clothescategory']) && intval($arr ['clothescategory'])==$arr ['clothescategory'] && $arr ['clothescategory']>0) {
					$str_param = trim ( $arr ['clothescategory'] );
					$w_s .= " and beu_clothes.clothescategory={$str_param}";
				}
				if (isset ( $arr ['clothescategory2'] ) && !empty ( $arr ['clothescategory2'] )) {
					$str_param = trim ( $arr ['clothescategory2'] );
					$w_s .= " and beu_clothes.clothescategory2 in({$str_param})";
				}
				
				if (isset ( $arr ['strd_data'])) {
					$str_param = trim ( $arr ['strd_data'] );
					$w_s .= " and DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d')>='{$str_param}'";
				}
				
				if (isset ( $arr ['end_data'] )) {
					$str_param = trim ( $arr ['end_data'] );
					$w_s .= " and DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d')<='{$str_param}'";
				}
				
				if (isset ( $arr ['key'] ) && !empty ( $arr ['key'] )) { //规范关键词
					$str_param = trim ( $arr ['key'] );
					
					if(empty($arr['keytype'])){//未设置模糊查询类型 就搜索所有类型
						$w_s .= ' and (';
						if (!empty ( $str_param ) && is_numeric($str_param) && intval($str_param)==$str_param && $str_param>0) {
							$w_s .= "beu_clothes.id={$str_param} or ";
						}
						$w_s .= "beu_clothes.name like '%{$str_param}%'";
						$w_s .= " or beu_clothes.brandnumber like '%{$str_param}%' "; 
						$w_s .= " or beu_clothes.code like '%{$str_param}%')"; 
					}else{
						if($arr['keytype'] == 2){//id
							$w_s .= " and (beu_clothes.id={$str_param})";
						}else if($arr['keytype'] == 3){//品名
							$w_s .= " and (beu_clothes.name like '%{$str_param}%')";
						}else if($arr['keytype'] == 1){//款号
							$w_s .= " and (beu_clothes.brandnumber like '%{$str_param}%'))";
						} 
					} 
				}
				
			}
			if($arr['d_bol'] && count($arr['ccid_jj'])==0){//自定义标签是否搜索
				$w_s .= " && beu_clothes.id  in(0)";
			}else if(count($arr['ccid_jj'])>0){
				$w_s .= " && beu_clothes.id in(".implode(',',$arr['ccid_jj']).")";
			}
			
			//时间排序
			if(!empty($arr['dateorder'])){
				$order_date = 'beu_clothes.date_add asc';
			}else{
				$order_date = 'beu_clothes.date_add desc';
			}
			if(!empty($_SESSION['table_where'])){
				$w_s.=' and '.$_SESSION['table_where'];
			}
			//搭配数查询
			if(isset($arr['dpsun']) && is_numeric($arr['dpsun']) && intval($arr['dpsun'])==$arr['dpsun'] && $arr['dpsun']>=0){
				try{
					$dp_clothes_w=array();
					if(isset($arr ['touchid'])){
						$dp_clothes_w['touchid']='='.$arr ['touchid'];
					}
					if(!empty($_SESSION['table_where'])){
						$dp_clothes_w['baidaid']='=0';
						//查询子屏包含的搭配
						$dapei_id_arr=array();
						$sub_dapei=Sub_dapei::getSubDapei();
						if($sub_dapei['status']==1){
							$dapei_id_arr=$sub_dapei['data'];
						}
						if(count($dapei_id_arr)>0){
							$dp_clothes_w['baidaid']=' in('.implode(',',$dapei_id_arr).')';
						}else{
							throw new Exception('sub_dp empty');
						}
					}
					$status_str='8,10,11,12';
					if($arr['dapei_status']==6){
						$status_str='8,10,11,12';
					}else if($arr['dapei_status']==5){
						$status_str='8,10,11,12';
					}else if($arr['dapei_status']==4){
						$status_str='10,11,12';
					}else if($arr['dapei_status']==3){
						$status_str='8,10,11,12';
					}else if($arr['dapei_status']==2){
						$status_str='10,11,12';
					}else if($arr['dapei_status']==1){
						$status_str='10,11';
					}
					//获取屏类可显示及其待售的衣服列表
					$ret_clothes=self::getTouchclothesinfo('clothesid,status',array('touchid'=>'='.$arr ['touchid'],'status'=>' in('.$status_str.')'));
					if($ret_clothes['status']==0){
						throw new Exception($ret_clothes['msg']);
					}
					$clothesid_show_arr=array();
					$clothesid_sale_arr=array();//待售单品
					$clothesid_shelf_arr=array();//下架单品
					foreach($ret_clothes['data'] as $value){
						$clothesid_show_arr[]=$value['clothesid'];
						if($value['status']==12){
							$clothesid_sale_arr[]=$value['clothesid'];
						}else if($value['status']==8){
							$clothesid_shelf_arr[]=$value['clothesid'];
						}
					}
					
					//获取单品的搭配
					$dp_clothes_ret=baida::selectDpclothesBypram('clothesid,GROUP_CONCAT(baidaid) as dp_str',$dp_clothes_w,'','clothesid');
					if($dp_clothes_ret['status']==0){
						throw new Exception('dp_clothes empty');
					}
					$clothesid_arr=array();
					$clothes_dp=array();
					//整理数据
					foreach($dp_clothes_ret['data'] as $value){
						$clothesid_arr[$value['clothesid']]=$value['dp_str'];
					}
					$dp_id_ret=implode(',',$clothesid_arr);
					$dp_id_ret=explode(',',$dp_id_ret);
					$dp_id_ret=array_unique($dp_id_ret);
					sort($dp_id_ret);
					//获取搭配的单品
					$clothes_ret=baida::selectDpclothesBypram('baidaid,GROUP_CONCAT(clothesid) as clothes_str',array('baidaid'=>' in('.implode(',',$dp_id_ret).')'),'','baidaid');
					if($clothes_ret['status']==0){
						throw new Exception('dp_clothes empty');
					}
					//提取出 有下架单品的搭配
					$dp_hide_arr=array();
					foreach($clothes_ret['data'] as $value){
						$clothes_arr=explode(',',$value['clothes_str']);
						$diff_id=Comm::array_diff_fast($clothes_arr,$clothesid_show_arr);
						if(count($diff_id)>0){
							$dp_hide_arr[]=$value['baidaid'];
						}
						else if($arr['dapei_status']==2){
							$diff_id=Comm::array_intersect_fast($clothes_arr,$clothesid_sale_arr);//当前搭配里是否存在待售单品
							if(count($diff_id)==0){
								$dp_hide_arr[]=$value['baidaid'];
							}
						}else if($arr['dapei_status']==3){
							$diff_id=Comm::array_intersect_fast($clothes_arr,$clothesid_shelf_arr);//当前搭配里是否存在下架单品
							if(count($diff_id)==0){
								$dp_hide_arr[]=$value['baidaid'];
							}
						}else if($arr['dapei_status']==5){//去除待售搭配
							$diff_shelf_id=Comm::array_intersect_fast($clothes_arr,$clothesid_shelf_arr);//当前搭配里是否存在下架单品
							$diff_id=Comm::array_intersect_fast($clothes_arr,$clothesid_sale_arr);//当前搭配里是否存在待售单品
							if(count($diff_shelf_id)==0 && count($diff_id)>0){
								$dp_hide_arr[]=$value['baidaid'];
							}
						}else if($arr['dapei_status']==6){
							$diff_shelf_id=Comm::array_intersect_fast($clothes_arr,$clothesid_shelf_arr);//当前搭配里是否存在下架单品
							$diff_id=Comm::array_intersect_fast($clothes_arr,$clothesid_sale_arr);//当前搭配里是否存在待售单品
							if(count($diff_id)==0 && count($diff_shelf_id)==0){
								$dp_hide_arr[]=$value['baidaid'];
							}
						}
					}
					$dp_id_ret=Comm::array_diff_fast($dp_id_ret,$dp_hide_arr);
					if(count($dp_id_ret)==0){
						throw new Exception('搭配为空');
					}
					$pram=array('status'=>'=10','id'=>' in('.implode(',',$dp_id_ret).')','touchid'=>'='.$arr ['touchid']);
					if(empty($_SESSION['table_where'])){
						$pram['is_sub']='=0';
					}
					//获取符合规范的搭配
					$dP_ret=baida::selectDpBypram('id',$pram);
					if($dP_ret['status']==0){
						throw new Exception($dP_ret['msg']);
					}
					$dp_ret_arr=array();
					foreach($dP_ret['data'] as $dp_value){
						$dp_ret_arr[]=$dp_value['id'];
					}
					//清除不符合规范的搭配
					foreach($clothesid_arr as $key=>$value){
						$dp_arr=explode(',',$value);
						$diff_id=Comm::array_diff_fast($dp_arr,$dp_ret_arr);
						if(count($diff_id)>0){
							$clothesid_arr[$key]=implode(',',Comm::array_diff_fast($dp_arr,$diff_id));
						}
						if(empty($clothesid_arr[$key])){
							unset($clothesid_arr[$key]);
						}
					}
					
					$dp_id_ret=implode(',',$clothesid_arr);
					$dp_id_ret=explode(',',$dp_id_ret);
					$dp_id_ret=array_unique($dp_id_ret);
					sort($dp_id_ret);
					//获取单品勾选的搭配
					$isshowid = Yii::app()->db->createCommand()
					 ->select('cid,GROUP_CONCAT(dpid) as dp_str')->from('isshow_dp')
					 ->where('dpid in('.implode(',',$dp_id_ret).')')
					 ->group('cid')
					 ->queryAll();
					//勾选搭配的单品为空
					if(count($isshowid)==0){
						throw new Exception('isshow_dp empty');
					}
					$isshowid_arr=Array();
					//整理出单品对应的勾选搭配
					foreach($isshowid as $value){
						$isshowid_arr[$value['cid']]=trim($value['dp_str']);
					}
					//整理衣服不同穿
					$brandnumber_arr=array();
					$number_i=0;
					//获取单品的不同穿
					$c_Different=self::beuDifferentSelectByClothesid(implode(',',array_keys($clothesid_arr)));
					$c_id_arr=array();
					//整合不同穿的搭配
					foreach($clothesid_arr as $key=>$value){
						//判断单品是否有不同穿
						$is_bool_Different=false;
						foreach($c_Different as $c_d_key=>$c_d_value){
							$Bh='id_'.$c_d_value['id'];
							$brandnumber_arr[$Bh]=array();
							//查看单品是否存在此不同穿关联数据里
							$is_bool=false;
							unset($c_d_value['id']);
							foreach($c_d_value as $d_key=>$d_value){
								if(!empty($d_value) && $d_value==$key){
									$is_bool=true;
									break;
								}
							}
							if(!$is_bool){//不存在就跳过 再下一条查找
								continue;
							}
							foreach($c_d_value as $d_key=>$d_value){
								if(!empty($d_value)){
									$brandnumber_arr[$Bh][$d_value]=trim($clothesid_arr[$d_value]);
									$c_id_arr[]=$d_value;
								}
							}
							$is_bool_Different=true;
							unset($c_Different[$c_d_key]);
						}
						if(!$is_bool_Different && !in_array($key,$c_id_arr)){//单品未找到不同穿法
							$brandnumber_arr['code_'.$number_i][$key]=trim($value);
							$number_i++;
						}
					}
					$check_num_arr=array();
					//统计不同穿的勾选搭配数量
					foreach($brandnumber_arr as $key=>$value){
						$isshow_dp_str='';
						$brandnumber_dp_str='';
						foreach($value as $c_key=>$c_value){
							if(isset($isshowid_arr[$c_key]) && !empty($isshowid_arr[$c_key])){
								if(!empty($isshow_dp_str)){
									$isshow_dp_str.=',';
								}
								$isshow_dp_str.=$isshowid_arr[$c_key];
							}
							if(!empty($c_value)){
								if(!empty($brandnumber_dp_str)){
									$brandnumber_dp_str.=',';
								}
								$brandnumber_dp_str.=$c_value;
							}
						}
						
						$arr1=explode(',',$isshow_dp_str);
						$arr1=array_unique($arr1);
						$arr2=explode(',',$brandnumber_dp_str);
						$arr2=array_unique($arr2);
						$check_num=count(Comm::array_intersect_fast($arr1,$arr2));
						
						if(!isset($check_num_arr[$check_num])){
							$check_num_arr[$check_num]='';
						}else{
							$check_num_arr[$check_num].=',';
						}
						$check_num_arr[$check_num].=implode(',',array_keys($value));
					}
					//print_r($check_num_arr); 
					if($arr['dpsun']>0 && isset($check_num_arr[$arr['dpsun']])){
						$w_s.=' and beu_clothes.id in('.$check_num_arr[$arr['dpsun']].')';
					}else if($arr['dpsun']==0){
						$dp_str='';
						foreach($check_num_arr as $key=>$value){
							if($key==0){
								continue;
							}
							if(!empty($dp_str)){
								$dp_str.=',';
							}
							$dp_str.=$value;
						}
						$w_s.=' and beu_clothes.id not in('.$dp_str.')';
					}else{
						throw new Exception('empty');
					}
				}catch(Exception $dp_e){
					//echo $dp_e->getMessage();
					if($arr['dpsun']!=0){
						$w_s.=' and beu_clothes.id=0';
					}
				}
			}
			$sele_zd = 'beu_clothes.date_add,beu_clothes.modelgender,beu_clothes.id,beu_clothes.name,beu_clothes.brandid,clothes.pid,beu_clothes.brandnumber,thumbnail,clothes.trycount,beu_clothes.date_add,' 
			. 'beu_brand.name as brandname,clothes.status,touch_config.name as touchname,clothes.touchid,clothes.createdate,beu_clothes.code,beu_clothes.masks,beu_clothes.imagescontent';
			$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( $_SESSION['clothes_table'].' as clothes' )->join ( 'beu_clothes', 'beu_clothes.id=clothes.clothesid' )->join ( 'beu_brand', 'beu_clothes.brandid=beu_brand.id' )->join ( 'touch_config', 'clothes.touchid=touch_config.id' )->where ( $w_s )->order($order_date);
			$sql2 = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id' )->from ( $_SESSION['clothes_table'].' as clothes' )->join ( 'beu_clothes', 'beu_clothes.id=clothes.clothesid' )->join ( 'beu_brand', 'beu_clothes.brandid=beu_brand.id' )->join ( 'touch_config', 'clothes.touchid=touch_config.id' )->where ( $w_s )->order($order_date);
			
			$newstypelist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
			if(count($newstypelist)==0){
				throw new Exception('衣服列表数据为空！');
			}
			//获取总个数
			$con = $sql2->queryAll ();
			$arr_con = $con;
			$con = count ( $con );
			$arr_id=array();
			foreach($arr_con as $value){
				$arr_id[]=$value['id'];
			}
			$criteria = new CDbCriteria();
			$pages=new CPagination($con);
			$pages->pageSize=$pagecount;
			$pages->applyLimit($criteria);
			
			$ret['data']=array ('data' => $newstypelist, 'count' => $con, 'pageSize' => $pagecount, 'page' => $pages,'arr_id'=> $arr_id);
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	
	}
	
	
	
	/**
	 * 根据条件查找触摸屏里所有的品牌,此方法需要统计触摸屏下所有衣服的品牌号
	 * @parm $touchid 触摸屏ID
	 */
	public static function tclothesSelectByTouchid($touchid = 0) {
		$touchid = trim ( $touchid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1 ,self::$Int_max);
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$w_s = 'touch_clothes.touchid=:touchid';
			$w_s_arr = array ();
			$w_s_arr [':touchid'] = $touchid;
			$brandidarr = Yii::app ()->db->createCommand ()->select ( 'brandid' )->from ( 'touch_clothes' )->join ( 'beu_clothes', 'beu_clothes.id=touch_clothes.clothesid' )->where ( $w_s, $w_s_arr )->group ( 'beu_clothes.brandid' )->queryAll ();
			$arr = array ();
			foreach ( $brandidarr as $value ) {
				$arr [$value ['brandid']] = 0;
			}
			if(count($arr)>0){
				Touch::touchUpdateByIdBrand($touchid,$arr,false);
			}
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据条件查找触摸屏里所有的模特类型,此方法需要统计触摸屏下所有衣服的模特类型
	 * @parm $touchid 触摸屏ID
	 */
	private static function tclothesSelectByTouchidModel($touchid = 0) {
		$touchid = trim ( $touchid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1 ,self::$Int_max);
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$w_s = 'touch_clothes.touchid=:touchid';
			$w_s_arr = array ();
			$w_s_arr [':touchid'] = $touchid;
			$brandidarr = Yii::app ()->db->createCommand ()->select ( 'modelgender' )->from ( 'touch_clothes' )->join ( 'beu_clothes', 'beu_clothes.id=touch_clothes.clothesid' )->where ( $w_s, $w_s_arr )->group ( 'beu_clothes.modelgender' )->queryAll ();
			$arr = array ();
			foreach ( $brandidarr as $value ) {
				$arr [] = $value ['modelgender'];
			}
			if(count($arr)>0){
				Touch::touchUpdateByIdModel($touchid,$arr);
			}
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据触摸屏号及其衣服款号查找相同款号的衣服
	 * @parm $touchid
	 * @parm $namber
	 * @parm $cid 
	 */
	public static function tclothesSelectByBrandnumber($touchid,$namber,$cid)
	{
		$touchid = trim ( $touchid );
		$namber = trim ( $namber );
		$cid = trim ( $cid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1 ,self::$Int_max);
			Comm::checkValue ( $namber, Yii::t ( 'public', '触摸屏号' ), 0, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$w_s = 'touch_clothes.touchid=:touchid and beu_clothes.brandnumber=:namber and `status`>8 and beu_clothes.id!=:cid'; //and beu_clothes.id!=:cid
			$w_s_arr = array ();
			$w_s_arr [':touchid'] = $touchid;
			$w_s_arr [':namber'] = $namber;
			$w_s_arr [':cid'] = $cid;
			$brandidarr = Yii::app ()->db->createCommand ()->select ( 'touch_clothes.pid as id,touch_clothes.clothesid,thumbnail' )->from ( 'touch_clothes' )->join ( 'beu_clothes', 'beu_clothes.id=touch_clothes.clothesid' )->where ( $w_s, $w_s_arr )->queryAll ();
			if(count($brandidarr)>0){
				return $brandidarr;
			}
			else{
				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据触摸屏号及其衣服款号查找相同款号的衣服
	 * @parm $namber
	 * @parm $cid 
	 */
	public static function bclothesSelectByBrandnumber($namber,$cid)
	{
		$namber = trim ( $namber );
		$cid = trim ( $cid );
		try {
		 
			Comm::checkValue ( $namber, Yii::t ( 'public', '触摸屏号' ), 0, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$w_s = 'beu_clothes.brandnumber=:namber and beu_clothes.id!=:cid'; //and beu_clothes.id!=:cid
			$w_s_arr = array ();
		 
			$w_s_arr [':namber'] = $namber;
			$w_s_arr [':cid'] = $cid;
			$brandidarr = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id,beu_clothes.thumbnail' )->from ( 'beu_clothes' )->where ( $w_s, $w_s_arr )->queryAll ();
			
			
			if(count($brandidarr)>0){
				return $brandidarr;
			}
			else{
				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	
	/**
	 * 根据模特类型查找衣服表里的所对应的所有一级品类
	 */
	public static function clothesSelectByclothescategory($modeltype,$touchid=0)
	{
		$ret=array('status'=>0,'msg'=>'');
		try {
			$touchid = trim ( $touchid );
			$modeltype = trim ( $modeltype );
			//$newlist=Yii::app()->cache->get(CacheName::getCacheName('touch_Clothes_Category_list') .$modeltype.'_'.$touchid);//获取衣服一级分类列表缓存
			$newlist=false;
			if($newlist===false){
				$w_s = '';
				$w_s_arr = array ();
				$brandidarr = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.clothescategory' )->from ( 'beu_clothes' );
				if($modeltype!=0){
					$w_s = 'beu_clothes.modelgender=:modelgender';
					$w_s_arr [':modelgender'] = $modeltype;
				}
				if($touchid!=0)
				{
					if(!empty($w_s))
					{
						$w_s.=' and ';
					}
					$w_s.='clothes.touchid=:touchid';
					$w_s_arr [':touchid'] = $touchid;
					if(!empty($_SESSION['table_where'])){
						$brandidarr=$brandidarr->where ( $_SESSION['table_where'] );
					}
					$brandidarr=$brandidarr->join ( $_SESSION['clothes_table'].' as clothes', 'beu_clothes.id=clothes.clothesid' );
				}
				$brandidarr=$brandidarr->where ( $w_s, $w_s_arr )->group('beu_clothes.clothescategory')->queryAll ();
				if(count($brandidarr)==0){
					throw new Exception ('查询衣服的一级品类列表为空！');
				}
				$arr=array();
				foreach ($brandidarr as $value)
				{
					$arr[]=$value['clothescategory'];
				}
				$ret['data']=$arr;
				$ret['status']=1;
				//Yii::app()->cache->set(CacheName::getCacheName('touch_Clothes_Category_list') .$modeltype.'_'.$touchid,$ret,0);//设置衣服一级分类列表缓存
			}else{
				$ret=$newlist;
			}
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	 * 根据模特类型查找衣服表里的所对应的所有二级品类
	 */
	public static function clothesSelectByclothescategory2($modeltype,$category,$touchid=0)
	{
		$ret=array('status'=>0,'msg'=>'');
		try {
			$touchid = trim ( $touchid );
			$category = trim ( $category );
			$modeltype = trim ( $modeltype );
			//$newlist=Yii::app()->cache->get(CacheName::getCacheName('touch_Clothes_Category2_list') .$modeltype.'_'.$touchid);//获取衣服二级分类列表缓存
			$newlist=false;
			if($newlist===false){
				$w_s = '1=1';
				$w_s_arr = array ();
				$brandidarr = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.clothescategory2,beu_clothes.clothescategory' )->from ( 'beu_clothes' );
				
				if($modeltype!=0){
					$w_s .= ' and beu_clothes.modelgender=:modelgender';
					$w_s_arr [':modelgender'] = $modeltype;
				}
				
				if($touchid!=0)
				{
					$w_s .= ' and clothes.touchid=:touchid';
					$w_s_arr [':touchid'] = $touchid;
					if(!empty($_SESSION['table_where'])){
						$brandidarr=$brandidarr->where ( $_SESSION['table_where'] );
					}
					$brandidarr=$brandidarr->join ( $_SESSION['clothes_table'].' as clothes', 'beu_clothes.id=clothes.clothesid' );
				}
				if(count($w_s_arr)>0){
					 $brandidarr->where ( $w_s, $w_s_arr );
				}
				$brandidarr=$brandidarr ->group('beu_clothes.clothescategory2')->queryAll ();
				if(count($brandidarr)==0){
					throw new Exception ('查询衣服的二级品类列表为空！');
				}
				$arr=array();
				foreach ($brandidarr as $value){
					$arr[]=$value['clothescategory2'];
				}
				$ret['data']=$arr;
				$ret['status']=1;
				//Yii::app()->cache->set(CacheName::getCacheName('touch_Clothes_Category2_list') .$modeltype.'_'.$touchid,$ret,0);//设置衣服二级分类列表缓存
			}else{
				$ret=$newlist;
			}
			
			if($category!=0){
				foreach($ret['data'] as $key=>$value){
					if($value['clothescategory']!=$category){
						unset($ret['data'][$key]);
					}
				}
				if(count($ret['data'])==0){
					$ret['status']=0;
					throw new Exception('查询二级品类失败！');
				}
				sort($ret['data']);
			}
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据品牌以及款号模糊查找衣服
	*/
	public static function getclothesBybrandandCode($brandid,$code_str,$code_start,$type){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!isset($brandid) || empty($brandid) || !is_numeric($brandid) || intval($brandid)!=$brandid){
				throw new Exception('品牌ID错误！');
			}
			if(!isset($code_str) || empty($code_str)){
				throw new Exception('款号字符串错误！');
			}
			if(!isset($code_start)){
				throw new Exception('款号规则开始位数错误！');
			}
			$sql_where='brandid='.$brandid;
			if($type!=1){//不同色衣服查询条件
				$left_like='';
				if($code_start>0){
					$left_like='%';
				}
				
				//根据款号模糊搜索衣服
				$sql_where.=' and (brandnumber like \''.$left_like.$code_str.'%\')';
			}else{//不同穿查询条件
				$sql_where.=' and (brandnumber =\''.$code_str.'\')';
			}
			$clothes_arr = Yii::app ()->db->createCommand ()->select ( 'id,name,thumbnail,code,brandnumber' )->from ( 'beu_clothes' )->where($sql_where)->order('id desc')->queryAll ();
			if(count($clothes_arr)==0){
				throw new Exception('查询数据为空！');
			}
			
			$ret['status']=1;
			$ret['data']=$clothes_arr;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	 * 向beu_clothes表添加新的数据 
	 * @parm $arr：Array 其内容如下
	 * brandid：int(11) （必填）品牌id
	 * color：int(11) （必填）颜色id
	 * level：int(11) (必填)层次
	 * clothescategory：int(11) （必填）品类一级目录
	 * clothescategory2：int(11) （必填）品类二级目录
	 * thumbnail:string(255) (必填)列表页显示图
	 * 
	 * name:string(255) （可不填）衣服名
	 * colorimage：string(255) (可不填)上传的颜色图片
	 * brandnumber:string(255) (可不填)品牌衣服款号,默认模特的款号
	 * material：int(11) （可不填）质地，对应category表中style=6，单选
	 * underwear：int(4) （可不填）是否穿内衣，0是穿内衣，1是脱内衣
	 * modelgender：int(11) （可不填）模特类型,是否男女模.对应b_category style为9的.
	 * label：int(11) （可不填）标签属性ID，指新品等，对应b_category的style为10的类型
	 * foottype：int(11) （可不填）脚部类型，对应b_category中type15
	 * supportfoot：string(50) （可不填）支持不同脚的ID，以逗号分隔，如果存在就表示该衣服支持该脚
	 * imagescontent：string(6000) （可不填）json格式{一维：customImagecontent：自定义图片（二维：{"customimage序列1-2 (三维：{customImagecontent序列1-50})}）、staticImagecontent：静态图（二维{staticImagecontent序列1-50}）、detailImagecontent：细节图（二维：{detailImagecontent序列1-50}）、graphicmodel：立体图{二维：graphicmodel序列1-50}、collocationmap：搭
	 * masks：string(1000) （可不填）遮罩JSON参数
	 * price：float （可不填）价钱
	 * discountprice：float （可不填）折扣后价格
	 * date_add：data(可不填)添加时间
	 * buyurl：string(1024) （可不填）购买地址，也需序列化
	 * 
	 * @return 成功返回添加数据id，否则返回false
	 */
	public static function beuclothesForadd($arr = array()) {
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$clothes_biao = new beu_clothes ();
			if (isset ( $arr ['brandid'] ) && isset ( $arr ['color'] ) && isset ( $arr ['level'] ) && isset ( $arr ['clothescategory'] ) && isset ( $arr ['clothescategory2'] ) && isset ( $arr ['thumbnail'] )) {
				try {
					$brandid = trim ( $arr ['brandid'] );
					$color = trim ( $arr ['color'] );
					$level = trim ( $arr ['level'] );
					$clothescategory = trim ( $arr ['clothescategory'] );
					$clothescategory2 = trim ( $arr ['clothescategory2'] );
					$thumbnail = trim ( $arr ['thumbnail'] );
					Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1,self::$Int_max );
					Comm::checkValue ( $color, Yii::t ( 'clothes', '颜色ID' ), 1, 1,self::$Int_max );
					Comm::checkValue ( $level, Yii::t ( 'clothes', '衣服层次' ), 1, 1,self::$Int_max );
					Comm::checkValue ( $clothescategory, Yii::t ( 'clothes', '一级品类' ), 1, 1, self::$Int_max );
					Comm::checkValue ( $clothescategory2, Yii::t ( 'clothes', '二级品类' ), 1, 1, self::$Int_max );
					Comm::checkValue ( $thumbnail, Yii::t ( 'clothes', '列表页显示图' ), 0, 10, 255 );
					$clothes_biao->brandid = $brandid;
					$clothes_biao->color = $color;
					$clothes_biao->level = $level;
					$clothes_biao->clothescategory = $clothescategory;
					$clothes_biao->clothescategory2 = $clothescategory2;
					$clothes_biao->thumbnail = $thumbnail;
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( $e->getMessage (), $e->getCode() );
				}
			} else {
				return false;
			}
			
			if (isset ( $arr ['name'] )) { //衣服名字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['name'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '单品名称' ), 0, 0, 255 );
					$clothes_biao->name = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['id'] )) { //衣服名字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['id'] );
					Comm::checkValue ( $str_param, 'ID', 1, 1, self::$Int_max );
					$clothes_biao->id = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['colorimage'] )) { //上传的颜色图片字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['colorimage'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '上传的颜色图片' ), 0, 0, 255 );
					$clothes_biao->colorimage = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['brandnumber'] )) { //品牌衣服款号字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['brandnumber'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '款号' ), 0, 0, 255 );
					$brand_biao->brandnumber = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['material'] )) { //质地字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['material'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '质地' ), 1, 1,self::$Int_max );
						$brand_biao->material = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['modelgender'] )) { //模特类型字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['modelgender'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'public', '模特类型' ), 1, 1,self::$Int_max );
						$brand_biao->modelgender = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['label'] )) { //标签属性ID字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['label'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '标签属性ID' ), 1, 1,self::$Int_max );
						$brand_biao->label = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			
			if (isset ( $arr ['foottype'] )) { //脚部类型字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['foottype'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '脚部类型' ), 1, 1,self::$Int_max );
						$brand_biao->foottype = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['supportfoot'] )) { //支持不同脚的ID字段存在，并且长度不超过255
				$str_param = trim ( $arr ['supportfoot'] );
				if (! empty ( $str_param )) {
					$str_param_id_arr = explode ( ',', $str_param ); //将字符串拆分后进行整数检查
					foreach ( $str_param_id_arr as $key => $value ) {
						$value = trim ( $value );
						try {
							Comm::checkValue ( $value, Yii::t ( 'clothes', '脚ID' ), 1, 1 ,self::$Int_max);
						} catch ( BeubeuException $e ) {
							unset ( $str_param_id_arr [$key] );
						}
					}
					if (count ( $str_param_id_arr ) > 0) { //将检查后的数组拼接为字符串
						$str_param = join ( ',', $str_param_id_arr );
						try {
							Comm::checkValue ( $str_param, Yii::t ( 'clothes', '支持不同脚的ID' ), 0, 1, 50 );
							$clothes_biao->supportfoot = $str_param;
						} catch ( BeubeuException $e ) {}
					}
				}
			}
			if (isset ( $arr ['imagescontent'] )) { //图片JSON数据字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['imagescontent'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '图片JSON数据' ), 0, 0, 6000 );
					$clothes_biao->imagescontent = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['masks'] )) { //遮罩JSON参数字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['masks'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '遮罩JSON数据' ), 0, 0, 1000 );
					$clothes_biao->masks = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['video'] )) { //视频JSON数据字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['video'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '视频JSON数据' ), 0, 0, 500 );
					$clothes_biao->video = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['buyurl'] )) { //购买地址字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['buyurl'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '购买地址' ), 0, 0, 1024 );
					$clothes_biao->buyurl = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['price'] )) { //价钱字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['price'] );
					$clothes_biao->price = 0;
					if (! empty ( $str_param )) {
						Comm::moneySingle ( $str_param, Yii::t ( 'clothes', '价格' ) );
						$clothes_biao->price = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['discountprice'] )) { //折扣后价格字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['discountprice'] );
					$clothes_biao->discountprice = 0;
					if (! empty ( $str_param )) {
						Comm::moneySingle ( $str_param, Yii::t ( 'clothes', '折扣后价格' ) );
						$clothes_biao->discountprice = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['underwear'] ) && ! empty ( $arr ['underwear'] ) && trim ( $arr ['underwear'] ) == 1) { //是否穿内衣，默认0穿内衣，1不穿
				$clothes_biao->underwear = 1; //不穿
			} else {
				$clothes_biao->underwear = 0; //穿内衣
			}
			if (isset ( $arr ['underpants'] ) && ! empty ( $arr ['underpants'] ) && trim ( $arr ['underpants'] ) == 1) { //是否穿内裤，默认0穿内裤，1不穿
				$clothes_biao->underpants = 1; //不穿
			} else {
				$clothes_biao->underpants = 0; //穿内裤
			}
			if (isset ( $arr ['showneckline'] ) && ! empty ( $arr ['showneckline'] ) && trim ( $arr ['showneckline'] ) == 1) { //是否翻领，默认0翻，1不翻
				$clothes_biao->showneckline = 1;  //不翻领
			} else {
				$clothes_biao->showneckline = 0;  //翻领
			}
			if (isset ( $arr ['date_add'] ) && ! empty ( $arr ['date_add'] )) { //时间字段存在并格式正确
				try {
					$createdate = trim ( $arr ['date_add'] );
					if (! empty ( $createdate )) {
						$createdate = Comm::dataSingle ( $createdate, Yii::t ( 'clothes', '添加时间' ) );
						$clothes_biao->date_add = $createdate;
					}
				} catch ( BeubeuException $e ) {}
			}
			
			try {
				$count = $clothes_biao->insert ();
				if ($count > 0) {
					
					return $count;
				} else {
					throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
				}
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
			}
		
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		return false;
	}
	
	/**
	 * 向beu_clothes表修改数据 
	 * @parm $clothesid:int 衣服id
	 * @parm $arr：Array 其内容如下
	 * brandid：int(11) （可不填）品牌id
	 * color：int(11) （可不填）颜色id
	 * level：int(11) (可不填)层次
	 * clothescategory：int(11) （可不填）品类一级目录
	 * clothescategory2：int(11) （可不填）品类二级目录
	 * thumbnail:string(255) (可不填)列表页显示图
	 * name:string(255) （可不填）衣服名
	 * colorimage：string(255) (可不填)上传的颜色图片
	 * relatedclothes:string(255) (可不填)相关衣服，相同款式不同色彩，衣服ID号以逗号分隔
	 * differenttry:string(255) (可不填)不同穿法字段,衣服ID号以逗号分隔
	 * brandnumber:string(255) (可不填)品牌衣服款号,默认模特的款号
	 * material：int(11) （可不填）质地，对应category表中style=6，单选
	 * underwear：int(4) （可不填）是否穿内衣，0是穿内衣，1是脱内衣
	 * modelgender：int(11) （可不填）模特类型,是否男女模.对应b_category style为9的.
	 * label：int(11) （可不填）标签属性ID，指新品等，对应b_category的style为10的类型
	 * foottype：int(11) （可不填）脚部类型，对应b_category中type15
	 * supportfoot：string(50) （可不填）支持不同脚的ID，以逗号分隔，如果存在就表示该衣服支持该脚
	 * imagescontent：string(6000) （可不填）json格式{一维：customImagecontent：自定义图片（二维：{"customimage序列1-2 (三维：{customImagecontent序列1-50})}）、staticImagecontent：静态图（二维{staticImagecontent序列1-50}）、detailImagecontent：细节图（二维：{detailImagecontent序列1-50}）、graphicmodel：立体图{二维：graphicmodel序列1-50}、collocationmap：搭
	 * masks：string(1000) （可不填）遮罩JSON参数
	 * price：float （可不填）价钱
	 * discountprice：float （可不填）折扣后价格
	 * date_add：data(可不填)添加时间
	 * video：string(500) （可不填）视频json格式保存，{"touch":{"videourl":"","videoimage":"http://"},"ipad":{"videourl":"","videoimage":"http://"}}
	 * buyurl：string(1024) （可不填）购买地址，也需序列化
	 */
	public static function beuclothesUpdateById($clothesid = 0, $arr = array()) {
		$clothesid = trim ( $clothesid );
		try {
			Comm::checkValue ( $clothesid, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
//		self::clothesDeleteImg($clothesid);//删除图片
//		self::clothesDeleteVide($clothesid);//删除视频
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$update_arr = array ();
			if (isset ( $arr ['brandid'] )) { //品牌号字段存在
				try {
					$str_param = trim ( $arr ['brandid'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'public', '品牌号' ), 1, 1,self::$Int_max );
						$update_arr ['brandid'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['color'] )) { //颜色字段存在
				try {
					$str_param = trim ( $arr ['color'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '颜色ID' ), 1, 1,self::$Int_max );
						$update_arr ['color'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['level'] )) { //层次字段存在
				try {
					$str_param = trim ( $arr ['level'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '衣服层次' ), 1, 1,self::$Int_max );
						$update_arr ['level'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['clothescategory'] )) { //品类一级字段存在
				try {
					$str_param = trim ( $arr ['clothescategory'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '一级品类' ), 1, 1,self::$Int_max );
						$update_arr ['clothescategory'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['clothescategory2'] )) { //品类二级字段存在
				try {
					$str_param = trim ( $arr ['clothescategory2'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '二级品类' ), 1, 1,self::$Int_max );
						$update_arr ['clothescategory2'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['thumbnail'] )) { //列表页显示图字段存在
				try {
					$str_param = trim ( $arr ['thumbnail'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '列表页显示图' ), 0, 10, 255 );
						$update_arr ['thumbnail'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['name'] )) { //衣服名字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['name'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '单品名称' ), 0, 0, 255 );
					$update_arr ['name'] = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['colorimage'] )) { //上传的颜色图片字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['colorimage'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '上传的颜色图片' ), 0, 0, 255 );
					$update_arr ['colorimage'] = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['brandnumber'] )) { //品牌衣服款号字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['brandnumber'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '款号' ), 0, 0, 255 );
					$update_arr ['brandnumber'] = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['material'] )) { //质地字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['material'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '质地' ), 1, 1,self::$Int_max );
						$update_arr ['material'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['modelgender'] )) { //模特类型字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['modelgender'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'public', '模特类型' ), 1, 1,self::$Int_max );
						$update_arr ['modelgender']= $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['label'] )) { //标签属性ID字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['label'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '标签属性ID' ), 1, 1,self::$Int_max );
						$update_arr ['label'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			
			if (isset ( $arr ['foottype'] )) { //脚部类型字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['foottype'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '脚部类型' ), 1, 1,self::$Int_max );
						$update_arr ['foottype'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['supportfoot'] )) { //支持不同脚的ID字段存在，并且长度不超过255
				$str_param = trim ( $arr ['supportfoot'] );
				if (! empty ( $str_param )) {
					$str_param_id_arr = explode ( ',', $str_param ); //将字符串拆分后进行整数检查
					foreach ( $str_param_id_arr as $key => $value ) {
						$value = trim ( $value );
						try {
							Comm::checkValue ( $value, Yii::t ( 'clothes', '脚ID' ), 1, 1,self::$Int_max );
						} catch ( BeubeuException $e ) {
							unset ( $str_param_id_arr [$key] );
						}
					}
					if (count ( $str_param_id_arr ) > 0) { //将检查后的数组拼接为字符串
						$str_param = join ( ',', $str_param_id_arr );
						try {
							Comm::checkValue ( $str_param, Yii::t ( 'clothes', '支持不同脚的ID' ), 0, 1, 50 );
							$update_arr ['supportfoot'] = $str_param;
						} catch ( BeubeuException $e ) {}
					}
				}
			}
//			$clothesImagescontent=self::clothesImgUp($clothesid);
//			
//			if ($clothesImagescontent) { //图片JSON数据字段存在，并且长度不超过6000
//				try {
//					$str_param = trim ( $clothesImagescontent );
//					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '图片JSON数据' ), 0, 0, 6000 );
//					$update_arr ['imagescontent'] = $str_param;
//				} catch ( BeubeuException $e ) {}
//			}
			
			if(!empty($_SESSION['clothesImgCount'.$clothesid])){
				$aa  =json_decode($_SESSION['clothesImgCount'.$clothesid],true);
				$arr_hb=self::ImgSort($aa);//对数组进行排序
				$update_arr ['imagescontent'] = json_encode($arr_hb);
//				print_r($update_arr ['imagescontent']);
//				exit();
			}
			
			if (isset ( $arr ['masks'] )) { //遮罩JSON参数字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['masks'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '遮罩JSON数据' ), 0, 0, 1000 );
					$update_arr ['masks'] = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			$video=self::clothesVideUp($clothesid);
			if ($video) { //视频JSON数据字段存在，并且长度不超过500
				try {
					$str_param = trim ( $video );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '视频JSON数据' ), 0, 0, 500 );
					$update_arr ['video'] = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['buyurl'] )) { //购买地址字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['buyurl'] );
					Comm::checkValue ( $str_param, Yii::t ( 'clothes', '购买地址' ), 0, 0, 1024 );
					$update_arr ['buyurl'] = $str_param;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['price'] )) { //价钱字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['price'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '价格' ),1,1 );
						$update_arr ['price'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['discountprice'] )) { //折扣后价格字段存在，并且长度不超过255
				try {
					$str_param = trim ( $arr ['discountprice'] );
					if (! empty ( $str_param )) {
						Comm::checkValue ( $str_param, Yii::t ( 'clothes', '折扣后价格' ),1,1 );
						$update_arr ['discountprice'] = $str_param;
					}
				} catch ( BeubeuException $e ) {}
			}
			
			
			$update_arr['season'] = 0;
			$update_arr['stylee'] = 0;
			if(!empty( $arr['season'])){
				$update_arr['season'] = $arr['season']; //季节
			} 
			
			if(!empty( $arr['stylee'])){
				$update_arr['stylee'] = $arr['stylee'];	//风格
			} 
			
			//总管理单时候修改
			if(empty($arr['bc'])){
				$update_arr['status'] = 0;
				$update_arr['singlespace'] = 0;
				if(!empty( $arr['status'])){
					$update_arr['status'] = $arr['status']; //状态
				} 
				
				if(!empty( $arr['singlespace'])){
					$update_arr['singlespace'] = $arr['singlespace'];	//空单
				} 
				
				if (isset ( $arr ['underwear'] ) && ! empty ( $arr ['underwear'] ) && trim ( $arr ['underwear'] ) == 1) { //是否穿内衣，默认0穿内衣，1不穿
					$update_arr ['underwear'] = 1; //不穿
				} else {
					$update_arr ['underwear'] = 0; //穿内衣
				}
				if (isset ( $arr ['underpants'] ) && ! empty ( $arr ['underpants'] ) && trim ( $arr ['underpants'] ) == 1) { //是否穿内裤，默认0穿内裤，1不穿
					$update_arr ['underpants'] = 1; //不穿
				} else {
					$update_arr ['underpants'] = 0; //穿内裤
				}
				if (isset ( $arr ['showneckline'] ) && ! empty ( $arr ['showneckline'] ) && trim ( $arr ['showneckline'] ) == 1) { //是否翻领，默认0翻，1不翻
					$update_arr ['showneckline'] = 1; //不翻领
				} else {
					$update_arr ['showneckline'] = 0; //翻领
				}
			}
			
			
			
			
			if (isset ( $arr ['date_add'] ) && ! empty ( $arr ['date_add'] )) { //时间字段存在并格式正确
				try {
					$createdate = trim ( $arr ['date_add'] );
					if (! empty ( $createdate )) {
						$createdate = Comm::dataSingle ( $createdate, Yii::t ( 'clothes', '添加时间' ) );
						$update_arr ['date_add'] = $createdate;
					}
				} catch ( BeubeuException $e ) {}
			}
			if(isset($arr['brandnumber2'])){
				$update_arr['brandnumber2'] = $arr['brandnumber2'];
			}
			if(isset($arr['brandnumber3'])){
				$update_arr['brandnumber3'] = $arr['brandnumber3'];
			}
			if(isset($arr['cardigan']) && ($arr['cardigan']==0 || $arr['cardigan']==1)){
				$update_arr['cardigan'] = $arr['cardigan'];
			}
			if(isset($arr['special']) && ($arr['special']==0 || $arr['special']==1)){
				$update_arr['special'] = $arr['special'];
			}
			if(isset($arr['easy']) && ($arr['easy']==0 || $arr['easy']==1)){
				$update_arr['easy'] = $arr['easy'];
			}
			if(isset($arr['longsleeve']) && ($arr['longsleeve']==0 || $arr['longsleeve']==1)){
				$update_arr['longsleeve'] = $arr['longsleeve'];
			}
			if(isset($arr['codetobarcode'])){
				$update_arr['codetobarcode'] = $arr['codetobarcode'];
			}
			if(isset($arr['mask_type3'])){
				$update_arr['mask_type3'] = $arr['mask_type3'];
			}
			if(isset($arr['mask3'])){
				$update_arr['mask3'] = $arr['mask3'];
			}
			if (count ( $update_arr ) > 0) {
				try {
					beu_clothes::model ()->updateAll ( $update_arr, 'id=:textx', array (':textx' => $clothesid ) );
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
				}
			}
		
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
			return false;
		}
		return true;
	}
	
	/**
	 * 将衣服修改为传过来的品牌
	 * @parm $clothesid 衣服id，可以是由','分隔开的多件品牌组成
	 * @parm $brandid 品牌id，衣服需要修改为什么品牌
	 * @throws 参数错误或数据查询失败会抛出异常
	 */
	public static function beuclothesUpdataBrandid($clothesid = '', $brandid = 0) {
		$clothesid = trim ( $clothesid );
		$brandid = trim ( $brandid );
		try {
			Comm::checkValue ( $clothesid, Yii::t ( 'clothes', '衣服ID' ), 0, 1,self::$Int_max );
			Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1 ,self::$Int_max);
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$clothesid_arr = explode ( ',', $clothesid );
		foreach ( $clothesid_arr as $key => $value ) {
			try {
				Comm::checkValue ( $value, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
			} catch ( Exception $e ) {
				unset ( $clothesid_arr [$key] );
			}
		}
		try {
			beu_clothes::model ()->updateByPk ( $clothesid_arr, array ('brandid' => $brandid ) );
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
		}
	}
	
	/**
	 * 根据衣服id查找beu_clothes表里的衣服详细信息
	 * @parm $clothesid 衣服id
	 */
	public static function beuclothesSelectByClothesID($clothesid = 0) {
		$ret=array('status'=>0,'msg'=>'');
		try{
			$clothesid = trim ( $clothesid );
			if(empty($clothesid) || !is_numeric($clothesid) || intval($clothesid)!=$clothesid || $clothesid<0){
				throw new Exception('衣服编号有误！');
			}
			$sql='select * from beu_clothes where id='.$clothesid;
			$newstypelist = Yii::app ()->db->createCommand ( $sql )->queryRow ();
			
			if (empty ( $newstypelist )) {
				throw new Exception('编号为 '.$clothesid.' 的衣服不存在！');
			}
			$ret['data']=$newstypelist;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
/**
	 * 根据衣服id查找beu_clothes表里的衣服详细信息
	 * @parm $clothesid 衣服id
	 */
	public static function beuclothesSelectBybrandnumber($brandnumber) {
		$ret=array('status'=>0,'msg'=>'');
		try{
			$brandnumber = trim ( $brandnumber );
			if(empty($brandnumber)){
				throw new Exception('衣服编号有误！');
			}
			$sql="select * from beu_clothes where brandnumber like '%".$brandnumber."%'";
			
			$newstypelist = Yii::app ()->db->createCommand ( $sql )->queryRow ();
			
			if (empty ( $newstypelist )) {
				throw new Exception('编号为 '.$clothesid.' 的衣服不存在！');
			}
			$ret['data']=$newstypelist;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	
	/**
	 * 根据条件查找beu_clothes表里的数据
	 * @parm $brandid 品牌id
	 * @parm $clothescategory 品类一级
	 * @parm $clothescategory2 品类二级
	 * @parm $modeltype 模特类型
	 * @parm $strd_data 开始时间
	 * @parm $end_data 结束时间
	 * @parm $key 搜索条件（衣服名、衣服id、款号）
	 * @parm $page 多少页
	 * @parm $pagecount 每页显示数量
	 * @return 成功后返回数据 array ('data' => $newstypelist, 'count' => $con,'pageSize'=>$pagecount,'page'=>$page ) data是查询到的数据，count是总页数，pageSize是每页显示数量，page是当前页
	 */
	public static function beuclothesSelectForAll($page = 1, $pagecount = 20, $brandid = 0, $modeltype = 0, $clothescategory = 0, $clothescategory2 = 0, $strd_data = 0, $end_data = 0, $key = '') {
		$page = trim ( $page );
		$brandid = trim ( $brandid );
		$modeltype = trim ( $modeltype );
		$clothescategory = trim ( $clothescategory );
		$clothescategory2 = trim ( $clothescategory2 );
		$strd_data = trim ( $strd_data );
		$end_data = trim ( $end_data );
		$key = trim ( $key );
		if (! empty ( $page )) { //规范页码
			try {
				Comm::checkValue ( $page, Yii::t ( 'public', '页码' ), 1, 0 );
			} catch ( BeubeuException $e ) {
				$page = 1;
			}
		} else {
			$page = 1;
		}
		if (! empty ( $pagecount )) {
			try {
				Comm::checkValue ( $pagecount, '', 1, 1 );
			} catch ( BeubeuException $e ) {
				$pagecount = 20;
			}
		} else {
			$page = 20;
		}
		$w_s = '';
		$w_s_arr = array ();
		
		if (! empty ( $brandid )) { //规范品牌id
			try {
				Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1,self::$Int_max );
				$w_s = 'beu_clothes.brandid=:brandid';
				$w_s_arr [':brandid'] = $brandid;
			} catch ( BeubeuException $e ) {}
		}
		if (! empty ( $modeltype )) { //规范模特id
			try {
				Comm::checkValue ( $modeltype, Yii::t ( 'public', '模特类型' ), 1, 1 ,self::$Int_max);
				if (! empty ( $w_s )) {
					$w_s .= ' and ';
				}
				$w_s .= 'beu_clothes.modelgender=:modelgender';
				$w_s_arr [':modelgender'] = $modeltype;
			} catch ( BeubeuException $e ) {}
		}
		if (! empty ( $clothescategory )) { //规范品类一级
			try {
				Comm::checkValue ( $clothescategory, Yii::t ( 'clothes', '一级品类' ), 1, 1,self::$Int_max );
				if (! empty ( $w_s )) {
					$w_s .= ' and ';
				}
				$w_s .= 'beu_clothes.clothescategory=:clothescategory';
				$w_s_arr [':clothescategory'] = $clothescategory;
			} catch ( BeubeuException $e ) {}
		}
		if (! empty ( $clothescategory2 )) { //规范品类二级
			try {
				Comm::checkValue ( $clothescategory2, Yii::t ( 'clothes', '二级品类' ), 1, 0,self::$Int_max );
				if (! empty ( $w_s )) {
					$w_s .= ' and ';
				}
				$w_s .= 'beu_clothes.clothescategory2=:clothescategory2';
				$w_s_arr [':clothescategory2'] = $clothescategory2;
			} catch ( BeubeuException $e ) {}
		}
		if (! empty ( $strd_data )) { //开始时间
			try {
				$strd_data = Comm::dataSingle ( $strd_data, Yii::t ( 'clothes', '开始时间' ) );
				if (! empty ( $w_s )) {
					$w_s .= ' and ';
				}
				$w_s .= 'beu_clothes.date_add>=:strd_data';
				$w_s_arr [':strd_data'] = $strd_data;
			} catch ( BeubeuException $e ) {}
		}
		if (! empty ( $end_data )) { //结束时间
			try {
				$end_data = Comm::dataSingle ( $end_data, Yii::t ( 'clothes', '结束时间' ) );
				if (! empty ( $w_s )) {
					$w_s .= ' and ';
				}
				$w_s .= 'beu_clothes.date_add<=:end_data';
				$w_s_arr [':end_data'] = $end_data;
			} catch ( BeubeuException $e ) {}
		}
		if (! empty ( $key )) { //规范关键词
			if (! empty ( $w_s )) {
				$w_s .= ' and (';
			}
			try {
				Comm::checkValue ( $key, Yii::t ( 'public', '关键词' ), 1, 1,self::$Int_max );
				$w_s .= 'beu_clothes.id=:clothesid or ';
				$w_s_arr [':clothesid'] = $key;
			} catch ( BeubeuException $e ) {}
			
			$w_s .= 'beu_clothes.name like :name or beu_clothes.brandnumber like :brandnumber )';
			$w_s_arr [':name'] = "%$key%";
			$w_s_arr [':brandnumber'] = "%$key%";
		}
		
		try {
			$sql = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id,beu_clothes.name,beu_clothes.brandid,beu_clothes.clothescategory,beu_clothes.clothescategory2,beu_clothes.brandnumber,beu_clothes.modelgender,thumbnail,beu_clothes.date_add,beu_brand.name as brandname' )->from ( 'beu_clothes' )->join ( 'beu_brand', 'beu_clothes.brandid=beu_brand.id' )->where ( $w_s, $w_s_arr )->order ( 'beu_clothes.id desc' );
			$sql2=$sql;
			$newstypelist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
			
			//获取总个数
			
			$con = $sql2->queryAll ();
			$con = count ( $con );
			$criteria = new CDbCriteria();
			$pages=new CPagination($con);
			$pages->pageSize=$pagecount;
		    $pages->applyLimit($criteria);
			if (count ( $newstypelist )>0) {
				return array ('data' => $newstypelist, 'count' => $con, 'pageSize' => $pagecount, 'page' => $pages );
			} else {
				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 超找触摸屏下的衣服时间节点
	 */
	public static function tclothesGetDate($touchid)
	{
		$touchid = trim ( $touchid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 0,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$w_s='';
		if($touchid>0)
		{
			$w_s.='touchid='.$touchid;
		}
		$sql = Yii::app ()->db->createCommand ()->select ('DATE_FORMAT(createdate,"%Y-%m") AS dateadd')->from('touch_clothes')->where($w_s)->group('dateadd')->order('dateadd desc');
		try{
			$data=$sql->queryAll ();
			return $data;
		}catch(Exception $e){
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 超找触摸屏下的衣服时间节点
	 */
	public static function bclothesGetDate($touchid)
	{
		$touchid = trim ( $touchid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$sql = Yii::app ()->db->createCommand ()->select ('DATE_FORMAT(date_add,"%Y-%m") AS dateadd')->from('beu_clothes')->group('dateadd')->order('dateadd desc');
		try{
			$data=$sql->queryAll ();
			return $data;
		}catch(Exception $e){
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 查找同触摸屏同品牌下的可用作不同穿法，相关衣服的数据
	 * @parm $touchid
	 * @parm $brandid
	 * @return 查找的衣服数据
	 */
	public static function tclothesSelectByTouchAndBrand($touchid=0,$brandid=0)
	{
		$touchid = trim ( $touchid );
		$brandid = trim ( $brandid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1,self::$Int_max );
			Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		
		try {
			$w = "status>7";
			if(!empty($touchid)){
				$w .= " and touchid=".$touchid;
			}
			if(!empty($brandid)){
				$w .= "  and brandid=".$brandid;
			}
			
			
			$sql = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id,beu_clothes.name,beu_clothes.brandnumber,beu_clothes.thumbnail' )->from ( 'touch_clothes' )->join ( 'beu_clothes', 'beu_clothes.id=touch_clothes.clothesid' )->where ( $w )->order ( 'beu_clothes.brandnumber desc' )->order ( 'beu_clothes.id desc' );
			$newstypelist = $sql->queryAll ();
			if (count( $newstypelist )>0) {
				return $newstypelist;
			} else {
				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	
	/**
	 * 查找同触摸屏同品牌下的可用作不同穿法，相关衣服的数据
	 * @parm $brandid
	 * @return 查找的衣服数据
	 */
	public static function bclothesSelectByTouchAndBrand($brandid=0)
	{
		 
		$brandid = trim ( $brandid );
		try {
			Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		
		try {
			$w = '1';
			if(!empty($brandid)){
				$w .= " and brandid=".$brandid;
			}
			$sql = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id,beu_clothes.name,beu_clothes.brandnumber,beu_clothes.thumbnail' )->from ( 'beu_clothes' )->where ( $w )->order ( 'beu_clothes.brandnumber desc' )->order ( 'beu_clothes.id desc' );
			$newstypelist = $sql->queryAll ();
			if (count( $newstypelist )>0) {
				return $newstypelist;
			} else {
				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	
	
	/**
	 * 根据衣服id查找不同穿法完整数据
	 * @parm $clothesid
	 */
	public static function beuDifferentSelectByClothesid($clothesid)
	{
		$w_s = '';
		$w_s_arr = array ();
		for($i = 1; $i <= 30; $i ++) {
			if (! empty ( $w_s )) {
				$w_s .= ' or ';
			}
			$w_s .= 'clothesid' . $i . ' in('.$clothesid.')';
		}
		try {
			$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesdifferent' )->where ( $w_s )->group('id')->queryAll ();
			return $data;
		} catch ( Exception $e ) {
			return array();
		}
	}
	
	
	/**
	 * 根据衣服id查找不同穿法完整数据
	 * @parm $clothesid
	 */
	public static function beuDifferentSelectByClothesid2($clothesid)
	{
		$w_s = '';
		$w_s_arr = array ();
		for($i = 1; $i <= 30; $i ++) {
			if (! empty ( $w_s )) {
				$w_s .= ' or ';
			}
			$w_s .= 'clothesid' . $i . '=' . $clothesid;
		}
		try {
			$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesdifferent' )->where ( $w_s )->queryRow ();
			return $data;
		} catch ( Exception $e ) {
			return array();
		}
	}
	/**
	 * 删除不同穿法数据
	 */
	public static function beuDifferentDeleteByid($id)
	{
		try {
			beu_clothesdifferent::model ()->deleteByPk ( $id );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
	
	/**
	 * 给衣服添加不同穿法
	 * @parm $clothes_str 由多个衣服id拼接而成，中间用‘，’分隔
	 */
	private static function clothesDifferentForAdd($clothes_str = '') {
		$clothes_str = trim ( $clothes_str );
		try {
			Comm::checkValue ( $clothes_str, Yii::t ( 'clothes', '衣服ID' ), 0, 1 );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$clothes_ID_arr = explode ( ',', $clothes_str );
		sort ( $clothes_ID_arr );
		$zd_num = 0;
		$clothesdifferent = new beu_clothesdifferent ();
		$inser_bool = false;
		foreach ( $clothes_ID_arr as $value ) {
			try {
				$value = trim ( $value );
				Comm::checkValue ( $value, Yii::t ( 'clothes', '衣服ID' ), 1 ,1,self::$Int_max);
				$zd_num ++;
				$zd = 'clothesid' . $zd_num;
				$clothesdifferent->$zd = $value;
				$inser_bool = true;
			} catch ( Exception $e ) {
			}
		}
		if ($inser_bool) { //当有数据时才插入
			try {
				$count = $clothesdifferent->insert ();
				if ($count > 0) {
				} else {
					throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
				}
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
			}
		} else {
			throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
		}
	}
	
	/**
	 * 根据衣服ID查找其不同穿法
	 * @parm $clothesid 衣服id
	 * @return 如果找到返回衣服数组，否则返回false
	 */
	public static function clothesDifferentSelectByClothesid($clothesid = 0) {
		$clothesid = trim ( $clothesid );
		try {
			Comm::checkValue ( $clothesid, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$data=self::beuDifferentSelectByClothesid($clothesid);
		//print_r($data);exit();
		if (count( $data )>0 && isset($data[0])) {
			$arr = array ();
			foreach ( $data[0] as $key => $value ) {
				if ($key != 'id' && ! empty ( $value )) {
					$arr [] = $value;
				}
			}
			return $arr;
		} else {
			return array();
		}
	}
	
	/**
	 * 根据ID查找其不同穿法
	 * @parm $id 不同穿法id
	 * @return 如果找到返回衣服数组，否则返回false
	 */
	public static function clothesDifferentSelectById($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '不同穿法' ) . 'ID', 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$data = beu_clothesdifferent::model ()->findByPk ( $id );
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
		if (! empty ( $data )) {
			$arr = array ();
			foreach ( $data as $key => $value ) {
				if ($key != 'id' && ! empty ( $value )) {
					$arr [] = $value;
				}
			}
			return $arr;
		} else {
			return false;
		}
	}
	
	/**
	 * 根据查找已经关联好不同穿法的所有衣服
	 * @parm $id 不同穿法id
	 * @return 如果找到返回衣服数组，否则抛出异常
	 */
	public static function clothesDifferentSelectAll($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$data=self::beuDifferentSelectByClothesid($id);
			$w_s='';
			if(count($data)>0 && isset($data[0]))
			{
				$id=$data[0]['id'];
				$w_s='id<>'.$id;
			}
			$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesdifferent' )->where ( $w_s )->order ( 'brandnumber desc' )->order ( 'id desc' )->queryAll ();
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
		if (count ( $data )>0) {
			$arr = array ();
			foreach ( $data as $key => $value ) {
				foreach ( $value as $key2 => $value2 ) {
					if ($key2 != 'id' && ! empty ( $value2 )) {
						$arr [] = $value2;
					}
				}
			}
			return $arr;
		} else {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据衣服ID修改其不同穿法
	 * @parm $id 单品id
	 * @parm $clothesid_str 衣服id字符串 用','分隔，可为空。为空表示将此件衣服从此不同穿法中删除，其他不变
	 * @return 如果成功返回true，否则抛出异常
	 */
	public static function clothesDifferentUpdataById($id = 0, $clothesid_str = '') {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$ret_data=array();
		if(!empty($clothesid_str)){//关联数据不为空
			$arr = explode ( ',', $clothesid_str );
			sort ( $arr );
			$clothesid_str=implode(',',$arr);
			$where='';
			for($clothes_i=1;$clothes_i<=30;$clothes_i++){
				if($clothes_i!=1){
					$where.=' or ';
				}
				$where.='clothesid'.$clothes_i.' in('.$clothesid_str.')';
			}
			$ret_data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesdifferent' )->where ( $where )->queryAll ();
			$is_bool=true;
			if(count($ret_data)>0){
				foreach($ret_data as $ret_data_key=>$ret_data_value){
					$num=1;
					$cl_arr=array();
					foreach($ret_data_value as $key=>$value){
						if($key!='id' && !empty($value)){
							$cl_arr[]=$value;
						}
					}
					$diff_clothes=array_diff($cl_arr,$arr);
					$ret_data_new=array();
					if(count($diff_clothes)>0){
						$arr=$diff_clothes;
					}else{
						$is_bool=false;
					}
					if(count($arr)<2){
						self::beuDifferentDeleteByid($ret_data_value['id']);
						continue;
					}
					foreach($arr as $value){
						$ret_data_new['clothesid'.$num]=$value;
						$num++;
					}
					if($num<=30){
						for($num;$num<=30;$num++){
							$ret_data_new['clothesid'.$num]='';
						}
					}
					beu_clothesdifferent::model ()->updateAll ( $ret_data_new, 'id=:textx', array (':textx' => $ret_data_value['id'] ) );
				}
			}
			if($is_bool){
				self::clothesDifferentForAdd($clothesid_str);//添加数据
			}
		}
	}
	
	
	
	/**
	 * 根据衣服id查找不同颜色完整数据
	 * @parm $clothesid
	 */
	public static function beuRelatedSelectByClothesid($clothesid)
	{
		$w_s = '';
		$w_s_arr = array ();
		for($i = 1; $i <= 30; $i ++) {
			if (! empty ( $w_s )) {
				$w_s .= ' or ';
			}
			$w_s .= 'clothesid' . $i . ' in('.$clothesid.')';
		}
		
		try {
			$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesrelated' )->where ( $w_s )->group('id')->queryAll ();
			return $data;
		} catch ( Exception $e ) {
			return array();
		}
	}
	
	
/**
	 * 根据衣服id查找不同颜色完整数据
	 * @parm $clothesid
	 */
	public static function beuRelatedSelectByClothesid2($clothesid)
	{
		$w_s = '';
		$w_s_arr = array ();
		for($i = 1; $i <= 30; $i ++) {
			if (! empty ( $w_s )) {
				$w_s .= ' or ';
			}
			$w_s .= 'clothesid' . $i . '='.$clothesid;
		}
		
		try {
			$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesrelated' )->where ( $w_s )->queryRow ();
			return $data;
		} catch ( Exception $e ) {
			return array();
		}
	}
	/**
	 * 删除不同颜色数据
	 */
	public static function beuRelatedDeleteByid($id)
	{
		try {
			beu_clothesrelated::model ()->deleteByPk ( $id );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
	/**
	 * 给衣服添加不同颜色
	 * @parm $clothes_str 由多个衣服id拼接而成，中间用‘，’分隔
	 */
	public static function clothesRelatedForAdd($clothes_str = '') {
		$clothes_str = trim ( $clothes_str );
		try {
			Comm::checkValue ( $clothes_str, Yii::t ( 'clothes', '衣服ID' ), 0, 1);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$clothes_ID_arr = explode ( ',', $clothes_str );
		sort ( $clothes_ID_arr );
		$zd_num = 0;
		$clothesrelated = new beu_clothesrelated ();
		$inser_bool = false;
		foreach ( $clothes_ID_arr as $value ) {
			try {
				$value = trim ( $value );
				Comm::checkValue ( $value, Yii::t ( 'clothes', '衣服ID' ), 1 ,1,self::$Int_max);
				$zd_num ++;
				$zd = 'clothesid' . $zd_num;
				$clothesrelated->$zd = $value;
				$inser_bool = true;
			} catch ( Exception $e ) {
			}
		}
		if ($inser_bool) { //当有数据时才插入
			try {
				$count = $clothesrelated->insert ();
				if ($count > 0) {
				} else {
					throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
				}
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
			}
		} else {
			throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
		}
	}
	
	/**
	 * 根据衣服ID查找其不同颜色
	 * @parm $clothesid 衣服id
	 * @return 如果找到返回衣服数组，否则返回false
	 */
	public static function clothesRelatedSelectByClothesid($clothesid = 0) {
		$clothesid = trim ( $clothesid );
		try {
			Comm::checkValue ( $clothesid, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$data=self::beuRelatedSelectByClothesid($clothesid);
		if (count ( $data )>0 && isset($data[0])) {
			$arr = array ();
			foreach ( $data[0] as $key => $value ) {
				if ($key != 'id' && ! empty ( $value )) {
					$arr [] = $value;
				}
			}
			return $arr;
		} else {
			return array();
		}
	}
	
	/**
	 * 根据ID查找其不同颜色
	 * @parm $id 不同颜色id
	 * @return 如果找到返回衣服数组，否则抛出异常
	 */
	public static function clothesRelatedSelectById($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '不同颜色' ) . 'ID', 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$data = beu_clothesrelated::model ()->findByPk ( $id );
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
		if (! empty ( $data )) {
			$arr = array ();
			foreach ( $data as $key => $value ) {
				if ($key != 'id' && ! empty ( $value )) {
					$arr [] = $value;
				}
			}
			return $arr;
		} else {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据查找已经关联好不同颜色的所有衣服
	 * @parm $id 不同颜色id
	 * @return 如果找到返回衣服数组，否则抛出异常
	 */
	public static function clothesRelatedSelectAll($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$data=self::beuRelatedSelectByClothesid($id);
			$w_s='';
			if(count($data)>0 && isset($data[0]))
			{
				$id=$data[0]['id'];
				$w_s='id<>'.$id;
			}
			$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesrelated' )->where ( $w_s )->order ( 'brandnumber desc' )->order ( 'id desc' )->queryAll ();
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
		if (count ( $data )>0) {
			$arr = array ();
			foreach ( $data as $key => $value ) {
				foreach ( $value as $key2 => $value2 ) {
					if ($key2 != 'id' && ! empty ( $value2 )) {
						$arr [] = $value2;
					}
				}
			}
			return $arr;
		} else {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据衣服ID修改其不同颜色
	 * @parm $id 单品id
	 * @parm $clothesid_str 衣服id字符串 用','分隔，可为空。为空表示将此件衣服从此不同穿法中删除，其他不变
	 * @return 如果成功返回true，否则抛出异常
	 */
	public static function clothesRelatedUpdataById($id = 0, $clothesid_str = '') {
		$id = trim ( $id );
		$clothesid_str = trim ( $clothesid_str );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		$ret_data=array();
		if(!empty($clothesid_str)){//关联数据不为空
			$arr = explode ( ',', $clothesid_str );
			sort ( $arr );
			$clothesid_str=implode(',',$arr);
			$where='';
			for($clothes_i=1;$clothes_i<=30;$clothes_i++){
				if($clothes_i!=1){
					$where.=' or ';
				}
				$where.='clothesid'.$clothes_i.' in('.$clothesid_str.')';
			}
			$ret_data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesrelated' )->where ( $where )->queryAll ();
			$is_bool=true;
			if(count($ret_data)>0){
				foreach($ret_data as $ret_data_key=>$ret_data_value){
					$num=1;
					$cl_arr=array();
					foreach($ret_data_value as $key=>$value){
						if($key!='id' && !empty($value)){
							$cl_arr[]=$value;
						}
					}
					$diff_clothes=array_diff($cl_arr,$arr);
					$ret_data_new=array();
					if(count($diff_clothes)>0){
						$arr=$diff_clothes;
					}else{
						$is_bool=false;
					}
					if(count($arr)<2){
						self::beuRelatedDeleteByid($ret_data_value['id']);
						continue;
					}
					foreach($arr as $value){
						$ret_data_new['clothesid'.$num]=$value;
						$num++;
					}
					if($num<=30){
						for($num;$num<=30;$num++){
							$ret_data_new['clothesid'.$num]='';
						}
					}
					beu_clothesrelated::model ()->updateAll ( $ret_data_new, 'id=:textx', array (':textx' => $ret_data_value['id'] ) );
				}
			}else{
				$is_bool=true;
			}
			if($is_bool){
				self::clothesRelatedForAdd($clothesid_str);//添加数据
			}
		}
	}
	
	/**
	 * 视频上传资料整理（与数据库对比后返回的更新到数据库的json数据）
	 * @parm $id:int 衣服id（beu_clothes的id）
	 * @return 成功返回整理后的json数据，否则返回false
	 * json解码后的数据规范 array('touch'=>array('videourl'=>'http://sda.mp4','videoimage'=>'http://'),'ipad'=>array('videourl'=>'http://sda.mp4','videoimage'=>'http://'))
	 */
	private static function clothesVideUp($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$arr = uploadd::SessionGet ( $id, self::$void_Session_name ); //获取session里的暂存数据
		if ($arr) {
			$arr = trim ( $arr );
			if (! empty ( $arr )) {
				$arr = json_decode ( $arr, true );
				if (count ( $arr ) > 0) {
					try {
						$data = Yii::app ()->db->createCommand ()->select ( 'video' )->from ( 'beu_clothes' )->where ( 'id=:id', array (':id' => $id ) )->query ();
						$video = array ();
						if (! empty ( $data )) {
							$video = trim ( $data ['video'] );
							if (! empty ( $video )) {
								$video = json_decode ( $video, true );
							}
						}
						if (gettype ( $video ) != 'array') {
							$video = array ();
						}
						$arr_jj=Comm::my_array_key_intersection($video, $arr);//根据键找到两个数组的交集，返回值只是第一个数组
						Comm::arrayDelete($arr_jj);//删除需要被替换的文件
						$arr_cj=Comm::my_array_key_diff($video, $arr_jj);//找到原有数据不变的数组
						$arr_hb=array_merge_recursive($arr, $arr_cj);//将两个数组合并
						return json_encode ( $arr_hb );
					} catch ( Exception $e ) {
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * 视频资料删除（删除数据）
	 * @parm $id:int 衣服id（beu_clothes的id）
	 * @parm $arr:array 需要删除的数据 array('touch'=>array('videourl'=>'','videoimage'=>''),'ipad'=>array('videourl'=>'','videoimage'=>''))
	 * @return 成功返回true，否则返回false
	 */
	private static function clothesDeleteVide($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$arr = uploadd::SessionGet ( $id, self::$void_Delete_Session_name ); //获取session里的暂存数据
		if ($arr) {
			$arr = trim ( $arr );
			if (! empty ( $arr )) {
				$arr = json_decode ( $arr, true );
				if (count ( $arr ) > 0) {
					try {
						$data = Yii::app ()->db->createCommand ()->select ( 'video' )->from ( 'beu_clothes' )->where ( 'id=:id', array (':id' => $id ) )->query ();
						$video = array ();
						if (! empty ( $data )) {
							$video = trim ( $data ['video'] );
							if (! empty ( $video )) {
								$video = json_decode ( $video, true );
								$arr_jj = Comm::my_array_key_intersection ( $video, $arr ); //根据键找到两个数组的交集，返回值只是第一个数组
								Comm::arrayDelete ( $arr_jj ); //删除需要被替换的文件
								$arr_cj = Comm::my_array_key_diff ( $video, $arr_jj ); //找到原有数据不变的数组
								$video = json_encode ( $arr_cj );
								if ($video != $data ['video']) {
									beu_clothes::model ()->updateByPk ( $id, array ('video' => $video ) );
								}
							}
						}
						return true;
					} catch ( Exception $e ) {
					}
				} else {
				}
			}
		}
		return false;
	}
	
	/**
	 * 图片上传资料整理（与数据库对比后返回的更新到数据库的json数据）
	 * @parm $id:int 衣服id（beu_clothes的id）								)
	 * @return 成功返回整理后的json数据，否则返回false
	 * jison解码后的数据结构规范 array(
	 									'customImagecontent'=>array(
	 																'customimage1'=>array(
	 																					0=>'http://',
	 																					1=>'http://'
	 																					)
	 																),
	 									'staticImagecontent'=>array(
	 																0=>'http://',
	 																1=>'http://'
	 																),
	 									'detailImagecontent'=>array(
	 																0=>'http://',
	 																1=>'http://'
	 																),
	 									'graphicmodel'=>array(
	 															0=>'http://',
	 															1=>'http://'
	 															),
	 									'collocationmap'=>array(
	 															0=>'http://',
	 															1=>'http://'
	 															)
	 */
	private static function clothesImgUp($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$arr = uploadd::SessionGet($id,self::$Img_Session_name);//获取session里的暂存数据
//		 print_r($arr);
//		 echo '<hr/>';
		
		if ($arr) {
			$arr = trim ( $arr );
			if (! empty ( $arr )) {
				$arr = json_decode ( $arr, true );
				if (count ( $arr ) > 0) {
					try {
						$data = Yii::app ()->db->createCommand ()->select ( 'imagescontent' )->from ( 'beu_clothes' )->where ( 'id=:id', array (':id' => $id ) )->queryAll ();
						$imagescontent = array ();
						if (count ( $data )>0) {
							$imagescontent = trim ( $data[0] ['imagescontent'] );
							if (! empty ( $imagescontent )) {
								$imagescontent = json_decode ( $imagescontent, true );
							}
						}
						if (gettype ( $imagescontent ) != 'array') {
							$imagescontent = array ();
						}
						$arr_jj=Comm::my_array_key_intersection($imagescontent, $arr);//根据键找到两个数组的交集，返回值只是第一个数组
						Comm::arrayDelete($arr_jj);//删除需要被替换的文件
						$arr_cj=Comm::my_array_key_diff($imagescontent, $arr_jj);//找到原有数据不变的数组
						$arr_hb=array_merge_recursive($arr, $arr_cj);//将两个数组合并
						$arr_hb=self::ImgSort($arr_hb);//对数组进行排序
						return json_encode ( $arr_hb );
					} catch ( Exception $e ) {
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * 图片资料删除（删除数据）
	 * @parm $id:int 衣服id（beu_clothes的id）
	 * @return 成功返回true，否则返回false
	 * json解码后的数据规范 array(
	 									'customImagecontent'=>array(
	 																'customimage1'=>array(
	 																					0=>'',
	 																					1=>''
	 																					)
	 																),
	 									'staticImagecontent'=>array(
	 																0=>'',
	 																1=>''
	 																),
	 									'detailImagecontent'=>array(
	 																0=>'',
	 																1=>''
	 																),
	 									'graphicmodel'=>array(
	 															0=>'',
	 															1=>''
	 															),
	 									'collocationmap'=>array(
	 															0=>'',
	 															1=>''
	 															)
	 									)
	 */
	private static function clothesDeleteImg($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$arr = uploadd::SessionGet ( $id, self::$Img_Delete_Session_name ); //获取session里的暂存数据
		if ($arr) {
			$arr = trim ( $arr );
			if (! empty ( $arr )) {
				$arr = json_decode ( $arr, true );
				if (count ( $arr ) > 0) {
					try {
						$data = Yii::app ()->db->createCommand ()->select ( 'imagescontent' )->from ( 'beu_clothes' )->where ( 'id=:id', array (':id' => $id ) )->queryAll ();
						$imagescontent = array ();
						if (count( $data )>0) {
							$imagescontent = trim ( $data[0] ['imagescontent'] );
							if (! empty ( $imagescontent )) {
								$ftp = new classftp ();
								$imagescontent = json_decode ( $imagescontent, true );
								$arr_jj = Comm::my_array_key_intersection ( $imagescontent, $arr ); //根据键找到两个数组的交集，返回值只是第一个数组
								Comm::arrayDelete ( $arr_jj ); //删除需要被替换的文件
								$arr_cj = Comm::my_array_key_diff ( $imagescontent, $arr_jj ); //找到原有数据不变的数组
								$arr_hb=self::ImgSort($arr_hb);//对数组进行排序
								$imagescontent = json_encode ( $arr_cj );
								if ($imagescontent != $data[0] ['imagescontent']) {
									beu_clothes::model ()->updateByPk ( $id, array ('imagescontent' => $imagescontent ) );
								}
							}
						}
						return true;
					} catch ( Exception $e ) {
						throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
					}
				} else {
					throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
				}
			}
		}
	}
	
	/**
	 * 对数组进行排序，将数组里的空值的元素删除，并将其键修改为0开始
	 * @parm $arr 需要排序的数组
	 * @return 返回排序好的数组
	 */
	private static function ImgSort(&$arr) {
			$sort_arr=array();
			$i=0;
			foreach ( $arr as $key=>$value ) {
				if(gettype($value)=='array')
				{
					try{
						$arr[$key]=self::ImgSort ( $value );
					}catch(Exception $e){}
				}
				else if(!empty($value)){
					$sort_arr[$i]=$value;
					$i++;
				}
				else{
					try{
						unset($arr[$key]);
					}catch(Exception $e){}
				}
			}
			if(count($sort_arr)>0){
				return $sort_arr;
			}
			else
			{
				return $arr;
			}
	}
	
	/**
	 * 上传视频
	 * @parm $id 衣服id
	 * @parm $name 上传控件名
	 * @parm $address 图片上传后暂存地址：指的是数组结构。例：array('dd'=>array('kk'=>'da'))，要将数据保存在二维数组kk下的写法 ('dd/kk')
	 */
	public static function voidUpload($id=0,$name='',$address='')
	{
		$id = trim ( $id );
		$name = trim ( $name );
		$address = trim ( $address );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
			Comm::checkValue ( $name, '', 0, 1 );
			Comm::checkValue ( $address, '', 0, 1 );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$url = uploadd::imgupload ( $name );
		if(strpos($url, 'http://')!==false){
			Imgcaches::imgCacheAdd($url);
			uploadd::SessionAdd($id,self::$void_Session_name,$url,$address);
		}
		return $url;
	}
	
	/**
	 * 上传图片
	 * @parm $id 衣服id
	 * @parm $name 上传控件名
	 * @parm $address 图片上传后暂存地址：指的是数组结构。例：array('dd'=>array('kk'=>'da'))，要将数据保存在二维数组kk下的写法 ('dd/kk')
	 */
	public static function imgUpload($id=0,$name='',$address='')
	{
		$id = trim ( $id );
		$name = trim ( $name );
		$address = trim ( $address );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
			Comm::checkValue ( $name, '', 0, 1 );
			Comm::checkValue ( $address, '', 0, 1 );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		$url = uploadd::imgupload ( $name );
		if(strpos($url, 'http://')!==false){
			Imgcaches::imgCacheAdd($url);
			uploadd::SessionAdd($id,self::$Img_Session_name,$url,$address);
		}
		return $url;
	}
	
	/**
	 * 删除视频
	 * @parm $id 衣服id
	 * @parm $address 图片上传后暂存地址：指的是数组结构。例：array('dd'=>array('kk'=>'da'))，要将数据保存在二维数组kk下的写法 ('dd/kk')
	 */
	public static function voidDelete($id=0,$address='')
	{
		$id = trim ( $id );
		$address = trim ( $address );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1,self::$Int_max );
			Comm::checkValue ( $address, '', 0, 1 );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		uploadd::SessionAdd($id,self::$void_Delete_Session_name,'',$address);
	}
	
	/**
	 * 删除图片
	 * @parm $id 衣服id
	 * @parm $address 图片上传后暂存地址：指的是数组结构。例：array('dd'=>array('kk'=>'da'))，要将数据保存在二维数组kk下的写法 ('dd/kk')
	 */
	public static function imgDelete($id=0,$address='')
	{
		$id = trim ( $id );
		$address = trim ( $address );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
			Comm::checkValue ( $address, '', 0, 1 );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		 
		uploadd::SessionAdd($id,self::$Img_Delete_Session_name,'',$address);
	}
	
	/**
	 * 清理图片Session
	 * @parm $id 衣服id
	 */
	public static function imgSessionDelete($id=0)
	{
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		uploadd::SessionDelete($id,self::$Img_Delete_Session_name);
		uploadd::SessionDelete($id,self::$Img_Session_name);
	}
	
	/**
	 * 清理视频Session
	 * @parm $id 衣服id
	 */
	public static function voidSessionDelete($id=0)
	{
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'clothes', '衣服ID' ), 1, 1 ,self::$Int_max);
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		uploadd::SessionDelete($id,self::$void_Delete_Session_name);
		uploadd::SessionDelete($id,self::$void_Session_name);
	}
	
	/***
	 * 获取衣服表的测试数据
	 */
	public static function clothesdateget($page)
	{
		$rowsPer=500;
		$data = Yii::app ()->beudb->createCommand ()->select ( '*' )->from ( 'tclothes' )->limit($rowsPer , ($page - 1) * $rowsPer)->queryAll ();
		if(count($data)>0)
		{
			$data=Comm::Conversion($data, "GBK", "UTF-8");
			foreach ($data as $value)
			{
				$arr=array();
				$arr['id']=$value['id'];
				$arr['name']=$value['name'];
				$arr['brandid']=$value['brandid'];
				$arr['color']=$value['color'];
				$arr['colorimage']='';
				$arr['level']=$value['level'];
//				echo $value['clothescategory'];exit();
				$arr['clothescategory']=$value['clothescategory'];
				$arr['clothescategory2']=$value['clothescategory2'];
				$arr['thumbnail']=$value['3ddirectoryname'].'00_i.jpg';
				$arr['brandnumber']=$value['brandnumber'];
				$arr['material']=$value['material'];
				$arr['underwear']='';
				$arr['underpants']='';
				$arr['showneckline']='';
				$arr['modelgender']=$value['modelgender'];
				$arr['label']=$value['label'];
				$arr['foottype']=$value['foottype'];
				$arr['supportfoot']=$value['supportfoot'];
				$arr['imagescontent']='';
				$arr['masks']='';
				$arr['price']=$value['price'];
				$arr['discountprice']=$value['discountprice'];
				$arr['date_add']=$value['date_add'];
				$arr['video']='';
				$arr['buyurl']='';
				$ret_clothes=self::beuclothesSelectByClothesID($value['id']);
				if($ret_clothes['status']==0){
					self::beuclothesForadd($arr);
				}
				self::tclothesForAdd($value['id'],$value['touchid']);
			}
		}else{
			echo "null";
		}
	}
	
	/***
	 * 获取品牌表的测试数据
	 */
	public static function branddateget()
	{
		$rowsPer=500;
		$data = Yii::app ()->beudb->createCommand ()->select ( '*' )->from ( 'b_brand' )->queryAll ();
		if(count($data)>0)
		{
			$data=Comm::Conversion($data, "GBK", "UTF-8");
			foreach ($data as $value)
			{
				$arr=array();
				$arr['id']=$value['id'];
				$arr['name']=$value['name'];
				if(empty($value['englishname']))
				{
				$arr['englishname']=$value['name'];
				}else{
				$arr['englishname']=$value['englishname'];
				}
				$arr['logoimgpath']=$value['logoimgpath'];
				$arr['telephone']=$value['telephone'];
				$arr['address']=$value['address'];
//				echo $value['clothescategory'];exit();
				$arr['fax']=$value['fax'];
				$arr['website']=$value['website'];
				$arr['companyname']=$value['companyname'];
				if($value['status']==1)
				{
				$arr['status']=10;
				}else
				{
				$arr['status']=11;
				}
				
				$arr['createdate']=$value['date_added'];
				try{
				Brand::brandSelectById($value['id']);
				}catch(Exception $e){
				Brand::brandForAdd($arr);
				}
			}
		}else{
			echo "null";
		}
	}
	
	/***
	 * 获取触摸屏表的测试数据
	 */
	public static function touchdateget()
	{
		$rowsPer=500;
		$data = Yii::app ()->beudb->createCommand ()->select ( '*' )->from ( 'tconfig' )->queryAll ();
		if(count($data)>0)
		{
			$data=Comm::Conversion($data, "GBK", "UTF-8");
			foreach ($data as $value)
			{
				$arr=array();
				$arr['id']=$value['id'];
				$arr['name']=$value['name'];
				$arr['brandid']=$value['brandid'];
				$arr['enddate']=$value['enddate'];
				$arr['lock']=$value['lock'];
//				echo $value['clothescategory'];exit();
				$arr['apikey']=$value['apikey'];
				$arr['headtail']=json_encode(array('head'=>array('touch'=>$value['header'],'ipad'=>$value['ipadheader']),'tail'=>array('touch'=>$value['footer'],'ipad'=>$value['ipadfooter'])));
				$arr['modellimit']=$value['modellimit'];
				$arr['modeldefault']=$value['modeldefault'];
				$arr['modelhead']=json_encode(unserialize($value['modelhead']));
				$arr['modelsethead']=json_encode(unserialize($value['modelsethead']));
				$arr['isshow']=json_encode(unserialize($value['isshow']));
				$arr['description']=$value['description'];
				$arr['openkey']=$value['openkey'];
				$arr['ver']=0;
				$arr['sqlver']=0;
				$arr['databaseupdate']=$value['databaseupdate'];
//				
				Touch::touchForAdd($arr);
				self::tclothesSelectByTouchid($value['id']);//统计此触摸屏下的品牌并将其更新到触摸屏下
//				}
			}
		}else{
			echo "null";
		}
	}
	
	
	/**
	 * 单品列表
	 * @parm $arr
	 * */
	public static function bclothes($arr=array(),$pagenumber = 1, $pagenum = 8){
		$where = '1=1';
		$where2 ='1=1';
		if(!empty($arr['brandid'])){
			$str_param = trim($arr['brandid']);
			$where .= " && beu_clothes.brandid in ({$str_param})";
			$where2 .= " && beu_clothes.brandid in ({$str_param})";
		}
		if(!empty($arr['singlespace'])){
			$str_param = trim($arr['singlespace']);
			
			if($str_param == 3){
				$str_param = 0; 
			}
			$where .= " && beu_clothes.singlespace = {$str_param}";
		}
		$push_clothes_arr=array();
		if(!empty($arr['status'])){
			$str_param = trim($arr['status']);
			if($str_param==100 || $str_param==200){//获取已设置的推送天猫数据
				$p_where=array('tmall_push'=>'=1');
				if(!empty($arr['brandid'])){
					$p_where['brandid']='='.$arr['brandid'];
				}
				$push_clothes=self::getpushclothes('clothesid',$p_where);
				if($push_clothes['status']==1){
					foreach($push_clothes['data'] as $p_value){
						$push_clothes_arr[]=$p_value['clothesid'];
					}
				}
			}else{
				$where .= " && beu_clothes.status = {$str_param}";
			}
		}
		//开衫闭合
		if(!empty($arr['open_close'])){
			$str_param = trim($arr['open_close']);
			$where .= " && beu_clothes.open_close = ".($str_param==3?0:$str_param);
		}
		
		//模特类型
		if(!empty($arr['modeltype'])){
			$str_param = trim($arr['modeltype']);
			$where .= " && beu_clothes.modelgender = {$str_param}";
		}
		
		
		//一级分类
		if(!empty($arr['clothescategory'])){
			$str_param = trim($arr['clothescategory']);
			$where .= " && beu_clothes.clothescategory = {$str_param}";
		}
		
		//二级分类
		if(!empty($arr['clothescategory2'])){
			$str_param = trim($arr['clothescategory2']);
			$where .= " && beu_clothes.clothescategory2 = {$str_param}";
		}
		//3.0遮罩
		if(isset($arr['mask3']) && !empty($arr['mask3'])){
			$str_param = intval(trim($arr['mask3']));
			if($str_param==1){//未做过3.0遮罩
				$where .= " && (beu_clothes.mask3 ='' or beu_clothes.mask3 is null)";
			}elseif($str_param==2){//只做过正面3.0遮罩
				$where .= " && (beu_clothes.mask3 like '%\"angel0\":{%' and beu_clothes.mask3 not like '%\"angel6\":{%')";
			}elseif($str_param==3){//包含正背面3.0遮罩
				$where .= " && (beu_clothes.mask3 like '%\"angel0\":{%' and beu_clothes.mask3 like '%\"angel6\":{%')";
			}elseif($str_param==4){//只做过背面3.0遮罩
				$where .= " && (beu_clothes.mask3 not like '%\"angel0\":{%' and beu_clothes.mask3 like '%\"angel6\":{%')";
			}
		}
		if (isset ( $arr ['strd_data'] )) {
			$str_param = trim ( $arr ['strd_data'] );
			try {
				$where .= " && DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d') >='{$str_param}'";
			} catch ( BeubeuException $e ) {
			}
		}
		if (isset ( $arr ['end_data'] )) {
			$str_param = trim ( $arr ['end_data'] );
			try {
				//				 
				$where .= " && DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d') <='{$str_param}'";
			
			} catch ( BeubeuException $e ) {
			}
		}
			
			if (isset ( $arr ['key'] )) { //规范关键词
				$str_param = trim ( $arr ['key'] );
				
				if(empty($arr['keytype'])){
					if (! empty ( $str_param )) {
					 
						$where .= ' and (';
						
						try {
							Comm::checkValue ( $str_param, Yii::t ( 'public', '关键词' ), 1, 1,self::$Int_max );
							
							$where .= " beu_clothes.id={$str_param} or ";
						 
						} catch ( BeubeuException $e ) { }
							$where .= " beu_clothes.name like '%{$str_param}%'";
							$where .= " or beu_clothes.brandnumber like '%{$str_param}%'"; 
							$where .= " or beu_clothes.code like '%{$str_param}%'"; 
							$where .= ' )';
					}
				}else{
				 	if($arr['keytype'] == 2){//id
				 		$where .= " and (beu_clothes.id={$str_param})";
				 	}
					if($arr['keytype'] == 3){//品名
				 		$where .= " and (beu_clothes.name like '%{$str_param}%')";
				 	}
					if($arr['keytype'] == 1){//款号
				 		$where .= " and (beu_clothes.brandnumber like '%{$str_param}%')";
				 	} 
				 	
				 	if($arr['keytype'] == 4){//款号
				 		$where .= " and (beu_clothes.code like '%{$str_param}%')";
				 	} 
				 	if($arr['keytype'] == 5){//品牌
						$brand_arr=array();
						$brand_data = Yii::app()->db->createCommand()
						->select('beu_brand.id')->from('beu_brand')
						->where('status=10 and (id=\''.$str_param.'\' or name like \'%'.$str_param.'%\' or englishname like \'%'.$str_param.'%\')')
						->queryAll ();
						if(count($brand_data)>0){
							foreach($brand_data as $brand_value){
								$brand_arr[]=$brand_value['id'];
							}
							$brand_arr=implode(',',$brand_arr);
						}else{
							$brand_arr=0;
						}
				 		$where .= " and (beu_clothes.brandid in(".$brand_arr."))";
				 	} 
				} 
			}
		if(isset($arr['img_patch']) && is_array($arr['img_patch'])){
			 $img_patch_str='';
			foreach($arr['img_patch'] as $value){
				if(!empty($img_patch_str)){
					$img_patch_str.=' or ';
				}
				$img_patch_str.="beu_clothes.code like '_____".$value."%'";
			}
			$where .= " and (".$img_patch_str.")";
		}
		if(isset($arr['dateorder']) && $arr['dateorder']=='asc'){
			$date_order = 'beu_clothes.date_add asc';
		}else if(isset($arr['dateorder']) && $arr['dateorder']=='desc'){
			$date_order = 'beu_clothes.date_add desc';
		}
		if(isset($arr['codeorder']) && $arr['codeorder']=='asc'){
			$date_order = 'beu_clothes.code asc';
		}else if(isset($arr['codeorder']) && $arr['codeorder']=='desc'){
			$date_order = 'beu_clothes.code desc';
		}	
		$sel_str='';
		
		//获取天猫 单品
		$tm_clothes_obj = Yii::app()->db->createCommand()
		->select('clothesid,status')->from('beu_clothes_status_tmall');
		if(isset($arr['img_status']) && $arr['img_status']!=-1){
			$tm_clothes_obj->where("status=".$arr['img_status']);
		}
		$tm_clothes_obj=$tm_clothes_obj->queryAll ();
		$tm_clothes_id_arr=array();
		$tm_clothes_status_arr=array();
		foreach($tm_clothes_obj as $key=>$value){
			$tm_clothes_id_arr[$key]=$value['clothesid'];
			$tm_clothes_status_arr[$key]=$value['status'];
		}
		$tm_clothes_id_str=0;
		if(count($tm_clothes_id_arr)>0){
			$tm_clothes_id_str=implode(',',$tm_clothes_id_arr);
		}
		$tm_where='';
		if(isset($arr['push_type']) && $arr['push_type']=='tmall'){//天猫
			$tm_where=' and beu_clothes.id in('.$tm_clothes_id_str.')';
		}else{
			if(isset($arr['status']) && $arr['status']==100 && count($push_clothes_arr)==0){
				$tm_where=' and beu_clothes.id in(0)';
			}else if(isset($arr['status']) && $arr['status']==100 && count($push_clothes_arr)>0){
				$tm_where=' and beu_clothes.id in('.implode(',',$push_clothes_arr).') and beu_clothes.id not in('.$tm_clothes_id_str.')';
			}else if(isset($arr['status']) && $arr['status']==200 && count($push_clothes_arr)==0){
				$tm_where=' and beu_clothes.id not in('.$tm_clothes_id_str.')';
			}else if(isset($arr['status']) && $arr['status']==200 && count($push_clothes_arr)>0){
				$tm_where=' and beu_clothes.id not in('.implode(',',array_merge(Comm::array_diff_fast($push_clothes_arr,$tm_clothes_id_arr),$tm_clothes_id_arr)).')';
			}else{
				$tm_where=' and beu_clothes.id not in('.$tm_clothes_id_str.')';
			}
			
			
		}
		
		//获取beu_clothes单品
		$obj2 = Yii::app()->db->createCommand()
		->select('beu_clothes.*'.$sel_str)->from('beu_clothes');
		
		$obj2=$obj2->where($where.$tm_where)->order($date_order)->limit ( $pagenum, ($pagenumber - 1) * $pagenum )->queryAll ();
		
		if(isset($arr['push_type']) && $arr['push_type']=='tmall'){//天猫
			foreach($obj2 as $key=>$value){
				$arr_key=array_search($value['id'],$tm_clothes_id_arr);
				if($arr_key!==false){
					$obj2[$key]['tm_status']=$tm_clothes_status_arr[$arr_key];
				}
			}
		}
		
//		echo Yii::app()->db->createCommand()
//		->select('*')->from('beu_clothes')
//		->where($where)->order('id desc')->limit ( $pagenum, ($pagenumber - 1) * $pagenum )->gettext();
//		exit();
		
		$con = Yii::app()->db->createCommand()
		->select('count(beu_clothes.id) as conn')->from('beu_clothes');
		$con=$con->where($where.$tm_where)->queryRow ();
		
		$timeobj = Yii::app()->db->createCommand()
		->select("DATE_FORMAT(date_add,'%Y-%m-%d') as dateadd")->from('beu_clothes');
		$timeobj=$timeobj->where($where2.$tm_where)->order('dateadd desc')->group('dateadd')->queryAll();
		
		//翻页
		$criteria = new CDbCriteria ();
		$pages = new CPagination ( $con['conn'] );
		$pages->pageSize = $pagenum;
		$pages->applyLimit ( $criteria );
		
		if (! empty ( $obj2 )) {
			$objj['timeobj'] = $timeobj;
			$objj["pages"] = $pages;
//			$objj['con'] = $con;
			$objj['data'] = $obj2;
			return $objj;
		} else {
			return false;
		}
	}
	
	/**
	 * 自定义标签
	 * @tid 搭配屏ID
	 * @account 账户
	 * */
	public static function selectbiaoqian($tid,$account){
		$o=self::getBiaoQianByAccount($account);
		if($o['status']==0){
			$customName=array();
		}else{
			$customName=$o['data'];
		}
		
		$str_arr=array('&amp;'=>'&','&lt;'=>'<','&gt;'=>'>','&quot;'=>'"','&ldquo;'=>'“','&rdquo;'=>'”',"\r\n"=>' ',"\O"=>'',"\r"=>' ',"\n"=>' ');
		
		//			print_r($customName);exit();
		

		foreach ( $customName as $k => $v ) {
			if ($v ['addselect'] != 1) {
				unset ( $customName [$k] );
			}
		}
		if (! empty ( $customName )) {
			$cobj = Yii::app ()->db->createCommand ()->select ( 'id' )->from ( 'beu_clothes' )->join('touch_clothes','beu_clothes.id = touch_clothes.clothesid')->where ( "touch_clothes.touchid in ({$tid})" )->queryAll ();
			if(count($cobj)>0){
				$cid_arr = array ();
				foreach ( $cobj as $v ) {
					$cid_arr [] = $v ['id'];
				}
				$cid = implode ( ',', $cid_arr ); //品牌的所有单品Id
				

				$select_con = array ();
				foreach ( $customName as $key => $vl ) {
					$custom = substr ( $key, strlen ( 'custom' ) );
					
					$z_selectcon = Yii::app ()->db->createCommand ()
					->select ( 'count(txt) as coutxt,txt,custom' )->from ( "beu_selectcon" )
					->where ( "clothesid in ($cid)" )->andWhere( "custom=:custom", array (":custom" => $custom ) )
					->order ( 'coutxt desc' )->group ( "txt" )->queryAll ();//->limit ( 20 )
					
					$z_selectcon2 = array ();
					$z_selectcon2_i = 0;
					if (count ( $z_selectcon ) > 0) {
						foreach ( $z_selectcon as $z_selectcon_key => $z_selectcon_value ) //替换特殊字符
						{
							if (trim ( $z_selectcon_value ['txt'] ) != '') {
								$z_selectcon_value ['txt'] = strtr ( $z_selectcon_value ['txt'], $str_arr );
								$z_selectcon2 ['v' . $z_selectcon2_i] = $z_selectcon_value;
								$z_selectcon2_i ++;
							}
						}
					}
					if (count ( $z_selectcon2 ) > 0) {
						$z_selectcon2 ['name'] = $vl ['name'] . '(全)';
					} else {
						$z_selectcon2 ['name'] = $vl ['name'] . '(全),' . $key;//$vl ['key'];
					}
					$select_con [] = $z_selectcon2;
				}
				return $select_con;
			}
		}
		return '';
	}
		
		
	/**
	 * 该账户的自定义标签
	 * @parm $account 对应的账户ID
	 * */
	public static function getBiaoQianByAccount($account){
		$ret=array('status'=>0,'msg'=>'');
		try {
			if(empty($account) || !is_numeric($account) || intval($account)!=$account || $account<=0){
				throw new Exception('账户ID有误！');
			}
			$newlist=Yii::app()->cache->get(CacheName::getCacheName('account_Select') .$account);//获取账户对应自定义标签缓存
			$newlist=false;
			if($newlist===false){
				$db = Yii::app()->db->createCommand();
				//注：这里的品牌字段后期改为账户字段来用
				$db->select('customName')->from('beu_biaoqian')->where('brandId=:account',array(':account'=>$account));
				$obj = $db->queryRow();
				if(empty($obj)){
					throw new Exception('账户对应的自定义标签有误！');
				}
				if(empty($obj['customName'])){
					throw new Exception('账户对应的自定义标签为空！');
				}
				$customName = json_decode($obj['customName'],true);
				
				foreach($customName as $k=>$v){
					if($v['length'] == 1 || empty($v['name'])){
						unset($customName[$k]);
					}	
				}
				$ret['data']=$customName;
				$ret['status']=1;
				Yii::app()->cache->set(CacheName::getCacheName('account_Select') .$account,$ret,0);//设置账户对应自定义标签缓存 永不过期
			}else{
				$ret=$newlist;
			}
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}

		
	/**
	 * 该账户的自定义图片标签
	 * @parm $account 对应的账户ID
	 * */
	public static function selectClothesImg($account){
		$db = Yii::app()->db->createCommand();
		$db->select('customImageName')->from('beu_biaoqian')->where('brandId=:account',array(':account'=>$account));
		$obj = $db->queryRow();
		
		if(!empty($obj)){
			$customImageName = json_decode($obj['customImageName'],true);
			foreach ($customImageName as $k=>$v){
				if(empty($v['name'])){
					unset($customImageName[$k]);
				}
			}
			return $customImageName;
			
		}else{
			return false;
		}
		
		
	}
	 	
	 	
	/**
	 * 触摸屏单品统计列表
	 * */
	public function touchclotescountlist($arr=array(),$page = 1, $pagecount = 20){
		
		$where = '1=1';
		
		if(!empty($arr)){
	  		if(!empty($arr['keyword'])){
	  			$where .= " && touchname like '%".$arr['keyword']."%'";
	  		}
	  		
	  		if(!empty($arr['selecttouchid'])){
	  			$where .= " && touchid={$arr['selecttouchid']}";
	  		}
			if(!empty($arr['startdate'])){
	  			$where .= " && adddate>='".$arr['startdate']."'";
	  		}
			if(!empty($arr['enddate'])){
	  			$where .= " && adddate<'".$arr['enddate']."'";
	  		}
	  	}
		$obj =  Yii::app()->db->createCommand()
		->select('*')->from('touch_clothescount')->where($where)
		->order('id desc')
		->limit ( $pagecount, ($page - 1) * $pagecount )
		->queryAll();
			
			//获取总个数
		$con = Yii::app()->db->createCommand()
		->select('*')
		->from('touch_clothescount')
		->where($where)
		->queryAll();
			
		//翻页
		$criteria = new CDbCriteria();
		$pages=new CPagination(count($con));
		$pages->pageSize=$pagecount;
		$pages->applyLimit($criteria);
		    
		if (! empty ( $obj )) {
 			$obj[0]["pages"] = $pages;
			return $obj;
		} else {
			return false;
		}
	}
	/**
	* 根据衣服ID删除其相应的不同颜色关联
	*/
	public static function delrelateByclothesID($clothesid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($clothesid) || !is_numeric($clothesid) || intval($clothesid)!=$clothesid){
				throw new Exception('衣服ID不是整数！');
			}
			$data=self::beuRelatedSelectByClothesid($clothesid);
			if (count ( $data )==0 || !isset($data[0])) {//循环遍历出出去本衣服后的其他衣服及其本关联数据的id
				throw new Exception('此衣服不存在不同颜色关联数据！');
			}
			$ret_bool=self::beuRelatedDeleteByid($data[0]['id']);
			if(!$ret_bool){
				throw new Exception('删除衣服不同颜色关联数据失败！');
			}
			$ret['status']=1;
			$ret['msg']='删除衣服不同颜色关联数据成功！';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据衣服ID删除其相应的不同穿法关联
	*/
	public static function deldifferentByclothesID($clothesid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($clothesid) || !is_numeric($clothesid) || intval($clothesid)!=$clothesid){
				throw new Exception('衣服ID不是整数！');
			}
			$data=self::beuDifferentSelectByClothesid($clothesid);
			if (count ( $data )==0 || !isset($data[0])) {//循环遍历出出去本衣服后的其他衣服及其本关联数据的id
				throw new Exception('此衣服不存在不同穿法关联数据！');
			}
			$ret_bool=self::beuDifferentDeleteByid($data[0]['id']);
			if(!$ret_bool){
				throw new Exception('删除衣服不同穿法关联数据失败！');
			}
			$ret['status']=1;
			$ret['msg']='删除衣服不同穿法关联数据成功！';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}

	
	
	/**
	 * 触摸屏单品图片统计列表
	 * */
	public static  function touchclotesimagecountlist($arr=array(),$page = 1, $pagecount = 20){
		
		$where = '1=1';
		$litiCategory="15698,15699,15700,15700,15702,15703,15704,15705,15706,15707,15708";//立体图分类
		$staticCategory="15709,15710,15711,15712,15713,15714,15715,15716";//有静态图的分类
		if(!empty($arr))
		{
	  		if(!empty($arr['keyword']) && $arr['keyword']!="根据款号模糊搜索"){
	  			$where .= " && brandnumber like '%".$arr['keyword']."%'";
	  		}
	  		
	  		$where .= " && touchid='".$arr['selecttouchid']."'";
	    	
	  		if(!empty($arr['startdate'])){
	  			$where .= " && date_add>='".$arr['startdate']."'";
	  		}
				if(!empty($arr['enddate'])){
	  			$where .= " && date_add<'".$arr['enddate']."'";
	  		}
				if(!empty($arr['brand'])){
	  			$where .= " && brandid={$arr['brand']}";
	  		}
	  	
	  		$category1Where="";$category2Where="";
				if (!empty($arr['category1'])) {
					$category1Where="(imagescontent NOT  LIKE '%graphicmodel%') && beu_clothes.clothescategory in (".$litiCategory.")";
					
				}
				if (!empty($arr['category2'])) {
					
					$category2Where="(imagescontent NOT  LIKE '%staticImageContent%') && beu_clothes.clothescategory in (".$staticCategory.")";
				}
				if(!empty($arr['category1']) && !empty($arr['category2']))
				{
					$where.="&& ((".$category1Where.") || (".$category2Where."))";
					
				}else{
					if (!empty($arr['category1'])) {
						$where.=" &&".$category1Where;
					}
					if (!empty($arr['category2'])) {
						$where.=" &&".$category2Where;
					}
				}
	  }
	  //echo $where;
	  $obj = Yii::app ()->db->createCommand ()
	  ->select ( 'beu_clothes.id,beu_clothes.brandnumber,beu_clothes.imagescontent,beu_clothes.clothescategory,beu_clothes.brandid,beu_clothes.singlespace,touch_clothes.touchid' ) ->from ( 'beu_clothes' )->join ( 'touch_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
	  ->where ( $where )
	  ->order('beu_clothes.id desc')
		->limit ( $pagecount, ($page - 1) * $pagecount )
		->queryAll();
	  
			//获取总个数
		$con = Yii::app()->db->createCommand()
		->select('beu_clothes.id,beu_clothes.brandnumber,beu_clothes.imagescontent,beu_clothes.singlespace')
		->from('beu_clothes')->join ( 'touch_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
		->where($where)
		->queryAll();
		
		 for($i=0;$i<count($obj);$i++)
	   {
	   		$obj[$i]['different']="";//不同穿法
	   		$obj[$i]['related']="";//不同颜色
	   		$obj[$i]['graphicmodelcount']=0;//立体图
				$obj[$i]['collocationmapcount']=0;//搭配单品图
				$obj[$i]['staticImagecount']=0;//静态图
				$obj[$i]['detailImagecount']=0;//细节图
				if(!empty($obj[$i]['imagescontent']))
				{
						$imagescontent=json_decode($obj[$i]['imagescontent'], true);
						if(isset($imagescontent['graphicmodel']) && !empty($imagescontent['graphicmodel']))
						{
							 $obj[$i]['graphicmodelcount']=count($imagescontent['graphicmodel']);
						}
						if(isset($imagescontent['collocationmap']) && !empty($imagescontent['collocationmap']))
						{
							$obj[$i]['collocationmapcount']=count($imagescontent['collocationmap']);
						}
						if(isset($imagescontent['staticImagecontent']) && !empty($imagescontent['staticImagecontent']))
						{
							$obj[$i]['staticImagecount']=count($imagescontent['staticImagecontent']);
						}
						if(isset($imagescontent['detailImagecontent']) && !empty($imagescontent['detailImagecontent']))
						{
							$obj[$i]['detailImagecount']=count($imagescontent['detailImagecontent']);
						}
		  	}
		  	
	   		$differentArray=Clothes::beuDifferentSelectByClothesid($obj[$i]['id']);//不同穿法
	   		$differentCount=count($differentArray);
	   		if($differentCount!=0)
	   		{ 
	   			for($j=0;$j<$differentCount;$j++)
	   			{
	   				 for($y=1;$y<13;$y++)
	   				 {
	   				 	 $clothesid='clothesid'.$y;
	   				 	 	
	   				 		if(!empty($differentArray[$j][$clothesid]))
	   				 		{
	   				 				 for($k=0;$k<count($con);$k++)
	   				 				 {
	   				 				 	 if($con[$k]['id']==$differentArray[$j][$clothesid])
	   				 				 	 {
	   				 				 	 	  $obj[$i]['different'].=$con[$k]['id']."-".$con[$k]['brandnumber']."</br>";
	   				 				 	 	  break;
	   				 				 	 }
	   				 				 }
	   				 				
	   				 		}
	   				 		
	   				 }
	   			}
	   		}
	   		$relatedArray=Clothes::beuRelatedSelectByClothesid($obj[$i]['id']);//不同颜色
				$relatedCount=count($relatedArray);
	   		if($relatedCount!=0)
	   		{
	   			for($j=0;$j<$relatedCount;$j++)
	   			{
	   				 for($y=1;$y<13;$y++)
	   				 {
	   				 		$clothesid='clothesid'.$y;
	   				 		if(!empty($relatedArray[$j][$clothesid]))
	   				 		{
	   				 				 for($k=0;$k<count($con);$k++)
	   				 				 {
	   				 				 	 if($con[$k]['id']==$relatedArray[$j][$clothesid])
	   				 				 	 {
	   				 				 	 	  $obj[$i]['related'].=$con[$k]['id']."-".$con[$k]['brandnumber']."</br>";
	   				 				 	 	  break;
	   				 				 	 }
	   				 				 }
	   				 		}
	   				 }
	   			}
	   		}
				$obj[$i]['different']= trim($obj[$i]['different'],"</br>");
				$obj[$i]['related']= trim($obj[$i]['related'],"</br>");
				
	   }
	   $clothescount=count($con);//总件数
	   $Allgraphicmodelcount=0;//立体图
			$AllstaticImagecount=0;//静态图
			$AlldetailImagecount=0;//细节图
			$noclothesimagecount = 0;//空单数
			$Allcollocationmapcount=0;//记录搭配单品数量
	    for($i=0;$i<count($con);$i++)
	   	{
	   		if(!empty($con[$i]['imagescontent']))
				{
						$imagescontent=json_decode($con[$i]['imagescontent'], true);
						if(isset($imagescontent['graphicmodel']) && !empty($imagescontent['graphicmodel']))//立体图
						{
							 $Allgraphicmodelcount=$Allgraphicmodelcount+count($imagescontent['graphicmodel']);
						}
						if(isset($imagescontent['collocationmap']) && !empty($imagescontent['collocationmap']))//搭配单品
						{
						
							$Allcollocationmapcount=$Allcollocationmapcount+count($imagescontent['collocationmap']);//$con[$i]['singlespace']
						}
						if(isset($imagescontent['staticImagecontent']) && !empty($imagescontent['staticImagecontent']))//细节图
						{
							$AllstaticImagecount=$AllstaticImagecount+count($imagescontent['staticImagecontent']);
						}
						if(isset($imagescontent['detailImagecontent']) && !empty($imagescontent['detailImagecontent']))//静态图
						{
							$AlldetailImagecount=$AlldetailImagecount+count($imagescontent['detailImagecontent']);
						}
				}
				if($con[$i]['singlespace']==0)//空单
				{
					$noclothesimagecount++;
				}
	   	}
	    
		//var_dump($con);
		//翻页
		$criteria = new CDbCriteria();
		$pages=new CPagination(count($con));
		$pages->pageSize=$pagecount;
		$pages->applyLimit($criteria);
		    
		if (! empty ( $obj )) {
 			$obj[0]["pages"] = $pages;
 			$obj[0]["clothescount"] = $clothescount;
 			$obj[0]["Allgraphicmodelcount"] = $Allgraphicmodelcount;
 			$obj[0]["AllstaticImagecount"] = $AllstaticImagecount;
 			$obj[0]["AlldetailImagecount"] = $AlldetailImagecount;
 			$obj[0]["noclothesimagecount"] = $noclothesimagecount;
 			$obj[0]["Allcollocationmapcount"] = $Allcollocationmapcount;
			return $obj;
		} else {
			return false;
		}
	
	}
	
	/**
	* 设置衣服的风格
	*/
	static function setClothesStyleBybrandnumber($clothes_arr,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$category_data=Category::categorySelectForAll(24);//获取所有的风格
			if($category_data['status']==0){
				$category_data=array();
			}else{
				$category_data=$category_data['data'];
			}
			//统计出衣服款号
			$brandnumber_arr=array();
			foreach($clothes_arr as $key=>$value){
				$code=isset($value['code'])?trim($value['code']):'';
				if(!empty($code)){
					$brandnumber_arr[]='\''.$code.'\'';
				}
			}
			
			if(count($brandnumber_arr)==0){
				throw new Exception('衣服为空！');
			}
			
			//根据衣服的款号极其触摸屏id号获取所有的衣服ID
			$clothes_data = Yii::app()->db->createCommand()
			->select('beu_clothes.id,beu_clothes.brandnumber,beu_clothes.brandid')
			->from('touch_clothes')->join ( 'beu_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
			->where('touch_clothes.touchid='.$touchid.' and beu_clothes.brandnumber in('.implode(',',$brandnumber_arr).')')
			->queryAll();
			
			if(count($clothes_data)==0){
				throw new Exception('根据衣服款号未找到衣服！');
			}
			
			//循环需要修改的衣服数组
			foreach($clothes_arr as $key=>$value){
				$code=isset($value['code'])?trim($value['code']):'';
				$typetag=isset($value['typetag'])?trim($value['typetag']):'';
				
				//当款号不为空才执行
				if(!empty($code)){
					//循环衣服数据
					foreach($clothes_data as $clothes_key=>$clothes_value){
						if($code==$clothes_value['brandnumber']){//当衣服的款号等于当前衣服的款号时
							//查询当前衣服在数据库里已绑定的风格
							$style_data=Yii::app()->db->createCommand()
							->select('*')
							->from('touch_clothes_style')
							->where('touchid='.$touchid.' and brandid='.$clothes_value['brandid'].' and clothesid='.$clothes_value['id'])
							->queryAll();
							
							//if(count($style_data)>0){//如果绑定的风格不为空
							$typetag_arr=array();
							if(!empty($typetag)){
								$typetag_arr=explode(',',$typetag);
							}
								
								$typetag_id_arr=array();
								//将需要绑定的风格替换为风格所对应的ID
								foreach($category_data as $category_key=>$category_value){
									if(in_array($category_value['title'],$typetag_arr)){
										$typetag_id_arr[]=$category_value['id'];
									}
									
								}
							
								//统计已绑定的风格
								$type_style_id_arr=array();
								foreach($style_data as $style_key=>$style_value){
									$type_style_id_arr[]=$style_value['styleid'];
								}
								
								$add_style=array_diff($typetag_id_arr,$type_style_id_arr);//还未绑定过的风格
								$del_style=array_diff($type_style_id_arr,$typetag_id_arr);//需要删除的绑定风格
								//批量绑定衣服风格
								$add_arr=array();
								foreach($add_style as $add_key=>$add_value){
									$arr=array();
									$arr[]=$clothes_value['brandid'];
									$arr[]=$touchid;
									$arr[]=$clothes_value['id'];
									$arr[]=$add_value;
									$add_arr[]='('.implode(',',$arr).')';
								}
								
								//如有需要绑定的风格就绑定
								if(count($add_arr)>0){
									$sql='insert into touch_clothes_style (brandid,touchid,clothesid,styleid) values'.implode(',',$add_arr);
									$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
								}
								//如有多余的绑定需要从数据库删除
								if(count($del_style)>0){
									$sql='delete from touch_clothes_style where brandid='.$clothes_value['brandid'].' and clothesid='.$clothes_value['id'].' and touchid='.$touchid.' and styleid in('.implode(',',$del_style).')';
									$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
								}
							//}
							unset($clothes_data[$clothes_key]);//此件衣服操作完成，清除
						}
					}
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	
	/**
	 * 根据id获取beu_clothes表数据
	 * @parm $id
	 * @parm $touchid
	 */
	public static function clothesSelectByIdAndTouchid($id,$touchid) {
		$where="1=1";
		$con=array();
		if(!empty($id) && !empty($touchid))
		{
			$where.=" && beu_clothes.id=$id  && clothes.touchid=$touchid";
			$sele_sql=',beu_clothes.price,beu_clothes.discountprice';
			if(!empty($_SESSION['table_where'])){
				$sele_sql=',clothes.discountprice';
			}
			$con = Yii::app()->db->createCommand()
			->select('beu_clothes.id,beu_clothes.brandnumber as barcode,beu_clothes.clothescategory,beu_clothes.thumbnail,beu_clothes.imagescontent,beu_clothes.colorimage,beu_clothes.name,beu_clothes.color,masks,beu_clothes.modelgender'.$sele_sql)
			->from('beu_clothes')->join ( $_SESSION['clothes_table'].' as clothes', 'beu_clothes.id=clothes.clothesid' )
			->where($where);
			if(!empty($_SESSION['table_where'])){
			$con->andWhere($_SESSION['table_where']);
			}
			$con=$con->queryRow();
			
			if(!empty($con)){
				$differentclothes=Clothes::clothesDifferentSelectByClothesid($id);//不同穿法衣服ID
				$c = trim(implode(',',$differentclothes));
				if(!empty($c)){
					$wherediferent="beu_clothes.id<>$id && beu_clothes.id in($c ) ";
					$differenobj = Yii::app()->db->createCommand()
					->select('beu_clothes.imagescontent')
					->from('beu_clothes')
					->where($wherediferent);
					$differenobj=$differenobj->queryAll();
					if(count($differenobj)>0)
					{
						foreach ($differenobj as $key=>$value)
						{
							if(!empty($differenobj[$key]['imagescontent']))
							{
								$imagescontent=json_decode($differenobj[$key]['imagescontent'],true);
								if(isset($imagescontent['graphicmodel'][0]))
								{
									$con['thumbnail']=$imagescontent['graphicmodel'][0];
									break;
								}
							}
						}
						
					}
				}
			}
		}
		return $con;
	}
	

	
	/**
	 * 根据id获取beu_selectcon表数据
	 * @parm $id
	 */
	public static function selectconSelectByClothesId($id) {
		
		$where="1=1";
		$con=array();
		if(!empty($id))
		{
			$where.=" && clothesid=$id";
			$con = Yii::app()->db->createCommand()
			->select('*')
			->from('beu_selectcon')
			->where($where)
			->queryAll();
		}
		return $con;
	}
	/**
	 * 根据touchid获取beu_selectcon表数据
	 * @parm $touchid
	 */
	public static function selectconSelectByTouchid($touchid,$customnum) {
		
		$where="1=1";
		$con=array();
		if(!empty($touchid))
		{
			$sql = "SELECT id,clothesid,COUNT(txt) AS c,txt,custom FROM new_beubeu.beu_selectcon WHERE clothesid IN (SELECT clothesid FROM new_beubeu.touch_clothes WHERE touchid=$touchid && custom=$customnum) GROUP BY txt ORDER BY id DESC";
	 		$con = Yii::app ()->db->createCommand ( $sql )->queryAll ();
		}
		return $con;
	}
	public static function selectconSelectByTouchidtxtAndcustom($touchid,$txt,$custom) {
		
		$where="touchid=$touchid && custom=$custom && txt='{$txt}'";
		
		$con = Yii::app()->db->createCommand() 
			->select('beu_selectcon.clothesid')
			->from('beu_selectcon')
			->join ( 'touch_clothes', 'beu_selectcon.clothesid=touch_clothes.clothesid' )
			->where($where)
			->queryAll();
			return $con;
	}
	
	/**
	 * 根据id获取beu_clothes表数据
	 * @parm $id
	 * @parm $touchid
	 */
	public static function clothesSelectByIdAndTouchid2($arr=array(),$category=1) {
		$where="1=1";
		$clothescategory="clothescategory";
		if($category==2)
		{
			$clothescategory="clothescategory2";
		}
		$con=array();
		if(!empty($arr))
		{
			if(!empty($arr['keyword'])){
	  			$where .= " && brandnumber like '%".$arr['keyword']."%'";
	  		}
	  		$where .= " && touchid='".$arr['touchid']."'";
	    	if(!empty($arr['startdate'])){
	  			$where .= " && date_add>='".$arr['startdate']."'";
	  		}
			if(!empty($arr['enddate'])){
  				$where .= " && date_add<'".$arr['enddate']."'";
	  		}
			if(!empty($arr['modelgenderid'])){
	  			$where .= " && modelgenderid={$arr['modelgenderidd']}";
	  		}
			if(!empty($arr['ccid_jj']))
			{
	  			$c = trim(implode(',',$arr['ccid_jj']));
				if(!empty($c))
				{
					$where .= " && beu_clothes.clothescategory in($c)";
				}
	  		}
			if(!empty($arr['clothescategory2_id_aa'])){
				$c = trim(implode(',',$arr['clothescategory2_id_aa']));
	  			$where .= " && beu_clothes.clothescategory in($c)";
	  		}
		}
		$con = Yii::app()->db->createCommand()
		->select('beu_clothes.id,beu_clothes.brandnumber,beu_clothes.clothescategory,beu_clothes.clothescategory2')
		->from('beu_clothes')->join ( 'touch_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
		->where($where)
		->group($clothescategory)
		->queryAll();
		return $con;
	}

	public static function clothesSelectByarray($arr=array(),$page = 1, $pagecount = 20) {
		$where="1=1";
		
		$con=array();
		if(!empty($arr))
		{
			if(!empty($arr['keyword'])){
	  			$where .= " && brandnumber like '%".$arr['keyword']."%'";
	  		}
	  		$where .= " && touchid='".$arr['touchid']."'";
	    	if(!empty($arr['startdate'])){
	  			$where .= " && date_add>='".$arr['startdate']."'";
	  		}
			if(!empty($arr['enddate'])){
  				$where .= " && date_add<'".$arr['enddate']."'";
	  		}
			if(!empty($arr['modelgenderidd'])){
	  			$where .= " && modelgender={$arr['modelgenderidd']}";
	  		}
			if(!empty($arr['ccid_jj']))
			{
	  			$c = trim(implode(',',$arr['ccid_jj']));
				if(!empty($c))
				{
					$where .= " && beu_clothes.id in($c)";
				}
	  		}
			if(!empty($arr['categoryid1'])){
	  			$where .= " && clothescategory={$arr['categoryid1']}";
	  		}
			if(!empty($arr['categoryid'])){
	  			$where .= " && clothescategory2={$arr['categoryid']}";
	  		}
		}
		
		$obj = Yii::app()->db->createCommand()
		->select('beu_clothes.id,beu_clothes.brandnumber,beu_clothes.clothescategory,thumbnail')
		->from('beu_clothes')->join ( 'touch_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
		->where($where)
		->order('beu_clothes.id desc')
		->limit ( $pagecount, ($page - 1) * $pagecount )
		->queryAll();
		
		$con = Yii::app()->db->createCommand()
		->select('beu_clothes.id,beu_clothes.brandnumber,beu_clothes.clothescategory')
		->from('beu_clothes')->join ( 'touch_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
		->where($where)
		->queryAll();
		//翻页
		$criteria = new CDbCriteria();
		$pages=new CPagination(count($con));
		$pages->pageSize=$pagecount;
		$pages->applyLimit($criteria);
		    
		if (! empty ( $obj )) {
 			$obj[0]["pages"] = $pages;
 			return $obj;
		} else {
			return false;
		}
		return $obj;
	}
	/**
	* 获取风格对应的衣服
	* @return 返回衣服ID数组
	*/
	static function getClothesStyleBybrandnumber($touchid,$brandid,$styleid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($touchid) || !is_numeric($touchid) || intval($touchid)!=$touchid){
				throw new Exception('触摸屏ID错误');
			}
			if(empty($brandid) || !is_numeric($brandid) || intval($brandid)!=$brandid){
				throw new Exception('品牌ID错误');
			}
			if(empty($styleid) || !is_numeric($styleid) || intval($styleid)!=$styleid){
				throw new Exception('风格ID错误');
			}
			
			//根据触摸屏id号、风格id及其品牌id获取所有的衣服ID
			$clothes_data = Yii::app()->db->createCommand()
			->select('clothesid')
			->from('touch_clothes_style')
			->where('touchid='.$touchid.' and brandid='.$brandid.' and styleid='.$styleid)
			->queryAll();
			
			if(count($clothes_data)==0){
				throw new Exception('未找到衣服！');
			}
			$clothes_arr=array();
			//循环需要修改的衣服数组
			foreach($clothes_data as $key=>$value){
				$clothes_arr[]=$value['clothesid'];
			}
			$clothes_arr=array_unique($clothes_arr);
			$ret['status']=1;
			$ret['data']=$clothes_arr;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取触摸屏下的风格
	* @return 返回风格数组
	*/
	static function getStyleByTouchid($touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($touchid) || !is_numeric($touchid) || intval($touchid)!=$touchid){
				throw new Exception('触摸屏ID错误');
			}
			
			//根据触摸屏id号获取其下的所有风格ID
			$style_data = Yii::app()->db->createCommand()
			->select('styleid')
			->from('touch_style')
			->where('touchid='.$touchid)
			->queryAll();
			
			if(count($style_data)==0){
				throw new Exception('未找到风格！');
			}
			foreach($style_data as $key=>$value){
				$style_data[$key]=$value['styleid'];
			}
			$style_data=array_unique($style_data);
			if(count($style_data)==0){
				throw new Exception('整理后风格为空！');
			}
			$ret['status']=1;
			$ret['data']=$style_data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据条码获取触摸屏下款号
	* @return 返回风格数组
	*/
	static function getClothesCodeByCode($touchid,$code){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($touchid) || !is_numeric($touchid) || intval($touchid)!=$touchid){
				throw new Exception('触摸屏ID错误');
			}
			//根据扫描条码查找衣服极其不同穿法
			$ret_c=self::getClothesIdByCode($touchid,$code);
			
			if($ret_c['status']==0){
				throw new Exception($ret_c['msg']);
			}
			$cid=$ret_c['data'];
			$w_on=$cid.'= beu_clothesdifferent.clothesid1';
			for($c_i=2;$c_i<=30;$c_i++){
				$w_on.=' or '.$cid.' = beu_clothesdifferent.clothesid'.$c_i;
			}
			$clothesdifferent = Yii::app()->db->createCommand()
					->select('*')->from('beu_clothesdifferent')
					->where($w_on)
					->queryRow();
			$clothes_id_arr=array();
			$clothes_id_arr[]=$cid;
			if(!empty($clothesdifferent)){
				for($c_i=1;$c_i<=30;$c_i++){
					if(!empty($clothesdifferent['clothesid'.$c_i])){
						$clothes_id_arr[]=trim($clothesdifferent['clothesid'.$c_i]);
					}
				}
			}
			$clothes_id_arr=array_unique($clothes_id_arr);
			//获取勾选的衣服搭配数据
			$dpobj = Yii::app()->db->createCommand()
					->select('beu_baida.id,beu_baida.touchid,beu_baida.imgurl,beu_baida.modeltype')->from('isshow_dp')
					->join('beu_baidaclothes','beu_baidaclothes.baidaid=isshow_dp.dpid')
					->join('beu_baida','beu_baida.id=isshow_dp.dpid')
					->where("beu_baidaclothes.touchid ={$touchid} and beu_baidaclothes.clothesid IN (".implode(',',$clothes_id_arr).") and isshow_dp.cid IN (".implode(',',$clothes_id_arr).")")
					->queryAll();
			if(empty($dpobj)){
				throw new Exception('扫描条码未找到勾选搭配！');
			}
			foreach($dpobj as $key=>$value){
				$dpobj[$key]['imgurl']=json_decode($value['imgurl'],true);
				$dpobj[$key]['imgurl']=$dpobj[$key]['imgurl'][1];
				$dpobj[$key]['thumbnailbig_png']=str_replace('300x615','480x640',$dpobj[$key]['imgurl']['thumbnailbig']);
			}
			//获取衣服的不同颜色
			$where='clothesid1='.$clothes_id_arr[0];
			$where_on='beu_clothes.id=clothesid1';
			for($c_i=2;$c_i<=30;$c_i++){
				$where.=' or clothesid'.$c_i.'='.$clothes_id_arr[0];
				$where_on.=' or beu_clothes.id = clothesid'.$c_i;
			}
			$clothes_color = Yii::app()->db->createCommand()
					->select('beu_clothes.id,beu_clothes.code')->from('beu_clothesrelated')
					->join('beu_clothes',$where_on)
					->join('touch_clothes','touch_clothes.clothesid = beu_clothes.id')
					->where($where)
					->andwhere('touchid='.$touchid)
					->queryAll();
			$colorArray=array();
			if(!empty($clothes_color)){
				foreach($clothes_color as $value){
					if($value['id']==$clothes_id_arr[0]){
						array_unshift($colorArray,$value['code']);
					}else{
						$colorArray[]=$value['code'];
					}
				}
			}
			$ret['status']=1;
			$ret['data']=$dpobj;
			$ret['color']=$colorArray;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据款号 获取其对应的衣服 主要应用于扫描
	**/
	static function getClothesIdByCode($touchid,$code){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($touchid) || !is_numeric($touchid) || intval($touchid)!=$touchid){
				throw new Exception('触摸屏ID错误');
			}
			if(empty($code)){
				throw new Exception('输入款号为空');
			}
			if($touchid==868)
			{
				$code=substr($code,0,13);
			}
			//根据扫描条码查找衣服极其不同穿法
			$sel = Yii::app()->db->createCommand();
			$cid = $sel->select('beu_clothes.id')->from('beu_clothes')
					->join('touch_clothes','touch_clothes.clothesid = beu_clothes.id')
					->where('touch_clothes.touchid=:touchid',array(':touchid'=>$touchid))
					->andWhere("beu_clothes.brandnumber like  '%{$code}%' or beu_clothes.brandnumber2 like  '%{$code}%'")
					->queryRow();
			if(empty($cid)){
				throw new Exception('扫描条码未找到衣服！');
			}
			$ret['status']=1;
			$ret['data']=$cid['id'];
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据款号 获取当前搭配屏对应主屏的数据
	* @parm sub_id
	* @parm touchid
	**/
	static function getClothesByBrandNumber($brandnumber_arr,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_array($brandnumber_arr) || count($brandnumber_arr)==0){
				throw new Exception('款号数组为空！');
			}
			if(!is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误！');
			}
			$data=Yii::app ()->db->createCommand ()
				->select ( 'touch_clothes.*,beu_clothes.brandnumber,beu_clothes.price,beu_clothes.discountprice' )
				->from ( 'touch_clothes' )
				->join('beu_clothes','beu_clothes.id=touch_clothes.clothesid')
				->where ('touch_clothes.touchid='.$touchid.' and beu_clothes.brandnumber in (\''.implode('\',\'',$brandnumber_arr).'\')')
				->queryAll ();//查询主屏里的衣服
			if(count($data)==0){
				throw new Exception('对应主屏的衣服数据为空！');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据款号 获取当前搭配屏对应主屏的衣服ID
	* @parm sub_id
	* @parm touchid
	**/
	static function getClothesIdByBrandNumber($brandnumber_arr,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_array($brandnumber_arr) || count($brandnumber_arr)==0){
				throw new Exception('款号数组为空！');
			}
			if(!is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误！');
			}
			$data=Yii::app ()->db->createCommand ()
				->select ( 'touch_clothes.clothesid,beu_clothes.brandnumber' )
				->from ( 'touch_clothes' )
				->join('beu_clothes','beu_clothes.id=touch_clothes.clothesid')
				->where ('touch_clothes.touchid='.$touchid.' and beu_clothes.brandnumber in (\''.implode('\',\'',$brandnumber_arr).'\')')
				->queryAll ();//查询主屏里的衣服
			if(count($data)==0){
				throw new Exception('对应主屏的衣服数据为空！');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 获取衣服列表的所有添加时间
	**/
	static function getClothesAddDataByTouch($touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误！');
			}
			//$dateadd=Yii::app()->cache->get(CacheName::getCacheName('touch_clothes_adddate_cache') .$touchid);//获取时间列表缓存
			$dateadd=false;
			if($dateadd===false){
				
				$dateadd = Yii::app()->db->createCommand()
				->select('DATE_FORMAT(beu_clothes.date_add,"%Y-%m-%d") AS dateadd')
				->from('beu_clothes')
				->join($_SESSION['clothes_table'].' as clothes','beu_clothes.id=clothes.clothesid')
				->where('touchid='.$touchid);
				if(!empty($_SESSION['table_where'])){
					$dateadd=$dateadd->where($_SESSION['table_where']);
				}
				$dateadd=$dateadd->order('dateadd desc')->group('dateadd')->queryAll();
				if(count($dateadd)==0){
					throw new Exception('衣服列表没有添加时间！');
				}
				//Yii::app()->cache->set(CacheName::getCacheName('touch_clothes_adddate_cache') .$touchid,$dateadd,0);//设置时间列表缓存 永不过期
			}
			$ret['data']=$dateadd;
			$ret['status']=1;
		}catch(Exception $e){
			
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
 	 * 单品标签查询
 	 * @parm $id_arr 单品ID 数组
 	 * */
 	static public function selselectcon($id_arr){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_array($id_arr) || count($id_arr)==0){
				throw new Exception('衣服ID数组为空！');
			}
			$db = Yii::app()->db->createCommand();
			$db->select('txt,clothesid,custom')->from('beu_selectcon')->where('clothesid in('.implode(',',$id_arr).')');
			$obj = $db->queryAll();
			if(count($obj)==0){
				throw new Exception('单品标签为空！');
			}
 			$ret['data']=$obj;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
 	}
	
	/**
	* 将单品添加到搭配屏
	**/
	static function clothesAddTouchByTouchid($tid,$cid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($tid)){
				throw new Exception('搭配屏编号有误！');
			}
			if(empty($cid)){
				throw new Exception('衣服编号有误！');
			}
			$ret['text']='';
			//获取要复制的搭配屏ID 
			$tid_arr = explode(',',$tid);
			//单品ID数据
			$cid_arr = explode(',',$cid);
			$touch_clothes_modelgender=array();//需要添加到屏里的衣服所对应的模特数组
			
			//获取需要添加的衣服的部分信息
			$beu_clothes_obj = Yii::app()->db->createCommand()
				->select('status,modelgender,id')
				->from('beu_clothes')
				->where('id in('.$cid.')')
				->queryAll();
			//将衣服id提取出来
			$beu_clothes_id_arr=array();
			foreach($beu_clothes_obj as $key=>$value){
				if($value['status']>=8){
					$beu_clothes_id_arr[$key]=$value['id'];
				}else{
					$ret['text'].='编号为'.$value['id'].'的衣服当前状态暂时不能复制<br/>';
				}
			}
			for($i=0;$i<count($tid_arr);$i++){
				$touch_clothes_modelgender[$tid_arr[$i]]=array();
				//获取需要添加的衣服是否存在当前屏里
				$cidd =Yii::app()->db->createCommand()
					->select('clothesid')
					->from('touch_clothes')
					->where('clothesid in('.$cid.') and touchid='.$tid_arr[$i])
					->queryAll();
				//如果需要添加的衣服都存在于当前屏，就不执行添加
				if(count($cidd)==count($cid_arr)){
					$ret['text'].='这些衣服都已存在'.$tid_arr[$i].'屏里<br/>';
					continue;
				}
				
				//将当前屏里已存在的衣服ID提取出来
				$touch_clothes_id_arr=array();
				foreach($cidd as $key=>$value){
					$touch_clothes_id_arr[$key]=$value['clothesid'];
				}
				//循环遍历需要添加到当前屏的衣服
				foreach($beu_clothes_id_arr as $key_j=>$value_j){
					$clothes_id_key=array_search($value_j,$touch_clothes_id_arr);//获取当前衣服是否存在于当前屏内
					if($clothes_id_key!==false){//如果当前衣服存在于屏里就不添加
						$ret['text'].='编号为'.$value_j.'的衣服已存在'.$tid_arr[$i].'屏里<br/>';
						continue;
					}
					//判断当前衣服的模特类型是否在数组里已添加过
					$clothes_modelgender_key=array_search($beu_clothes_obj[$key_j]['modelgender'],$touch_clothes_modelgender[$tid_arr[$i]]);
					if($clothes_modelgender_key===false){//如果未添加过 就添加
						$touch_clothes_modelgender[$tid_arr[$i]][]=$beu_clothes_obj[$key_j]['modelgender'];
					}
					//将衣服添加到当前屏
					$tc = new touch_clothes();
					$tc->clothesid = $value_j;
					$tc->touchid =  $tid_arr[$i];
					$tc->trycount = 0;	//试穿数
					$tc->status = 12;
					$tc->acquiescetry = 0; //默认穿着1
					$tc->recommend = 0; //是否推荐
					$tc->createdate = date('Y-m-d H:i:s');
					$tc->sort = 9999;
					$tc->save();
				}
			}
			//清除搭配屏下的时间列表缓存
			foreach($tid_arr as $value){
				Yii::app()->cache->delete(CacheName::getCacheName('touch_clothes_adddate_cache') .$value);
				foreach($touch_clothes_modelgender[$value] as $modelgender_value){//当前屏添加过衣服，清除其所对应模特的缓存
					Yii::app()->cache->delete(CacheName::getCacheName('touch_Clothes_Category_list') .$modelgender_value.'_'.$value);//清除一级品类缓存
					Yii::app()->cache->delete(CacheName::getCacheName('touch_Clothes_Category2_list') .$modelgender_value.'_'.$value);//清除二级品类缓存
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据衣服编号获取其图片
	**/
	static function getClothesImgByClothesId($clothesid,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($clothesid)){
				throw new Exception('衣服编号有误！');
			}
			if(empty($touchid)){
				throw new Exception('搭配屏编号有误！');
			}
			$cidd =Yii::app()->db->createCommand()
					->select('touch_clothes.pid,thumbnail')
					->from('beu_clothes')
					->join('touch_clothes','touch_clothes.clothesid=beu_clothes.id')
					->where('touch_clothes.status<>12 and id in('.$clothesid.') and touch_clothes.touchid='.$touchid)
					->queryAll();		
			if(count($cidd)==0){
				throw new Exception('衣服数据查询为空！');
			}
			$ret['data']=$cidd;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据衣服ID获取其绑定的多脚数据
	**/
	static function getClothesfootByClothesId($clothesid){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			if(!is_numeric($clothesid) || intval($clothesid)!=$clothesid || $clothesid<=0){
				throw new Exception('衣服编号有误！');
			}
			$cidd =Yii::app()->db->createCommand()
					->select('*')
					->from('beu_manyfoot')
					->where('clothesid='.$clothesid)
					->queryRow();
			if(empty($cidd)){
				throw new Exception('衣服未绑定多脚数据！');
			}
			$ret['data']=$cidd;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据衣服ID设置其绑定的多脚数据
	**/
	static function setClothesfootByClothesId($clothesid,$arr){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			if(!is_numeric($clothesid) || intval($clothesid)!=$clothesid || $clothesid<=0){
				throw new Exception('衣服编号有误！');
			}
			if(!isset($arr['high_foot']) || empty($arr['high_foot'])){//如果高脚值不存在或为空就赋值为0
				$arr['high_foot']=0;
			}
			if(isset($arr['high_foot']) && (!is_numeric($arr['high_foot']) || intval($arr['high_foot'])!=$arr['high_foot'] || $arr['high_foot']<0)){//如果高脚值不为数字就去除其修改
				unset($arr['high_foot']);
			}
			if(!isset($arr['middle_foot']) || empty($arr['middle_foot'])){//如果中脚值不存在或为空就赋值为0
				$arr['middle_foot']=0;
			}
			if(isset($arr['middle_foot']) && (!is_numeric($arr['middle_foot']) || intval($arr['middle_foot'])!=$arr['middle_foot'] || $arr['middle_foot']<0)){//如果中脚值不为数字就去除其修改
				unset($arr['middle_foot']);
			}
			if(!isset($arr['low_foot']) || empty($arr['low_foot'])){//如果低脚值不存在或为空就赋值为0
				$arr['low_foot']=0;
			}
			
			if(isset($arr['low_foot']) && (!is_numeric($arr['low_foot']) || intval($arr['low_foot'])!=$arr['low_foot'] || $arr['low_foot']<0)){//如果低脚值不为数字就去除其修改
				unset($arr['low_foot']);
			}
			
			if(!isset($arr['def_foot']) || empty($arr['def_foot'])){//如果默认脚值不存在或为空就赋值为0
				$arr['def_foot']=0;
			}
			if(isset($arr['def_foot']) && (!is_numeric($arr['def_foot']) || intval($arr['def_foot'])!=$arr['def_foot'] || $arr['def_foot']<0)){//如果低脚值不为数字就去除其修改
				unset($arr['def_foot']);
			}
			if(count($arr)==0){
				throw new Exception('数据有误不需要修改！');
			}
			$ret_foot=self::getClothesfootByClothesId($clothesid);//获取衣服的多脚数据
			
			if($ret_foot['status']==0){//如果未找到数据，就需要添加新数据
				if(empty($arr['high_foot']) && empty($arr['middle_foot']) && empty($arr['low_foot'])&& empty($arr['def_foot'])){
					throw new Exception('单品未设置多脚数据，不需要添加新数据！');
				}
				//添加多脚数据
				$arr['clothesid']=$clothesid;
				$set_str=Comm::join_sql_set($arr);//将数组转为sql语句
				//var_dump($set_str); exit();
				Yii::app()->db
				->createCommand("insert into beu_manyfoot set ".$set_str)
				->execute();
			}else{//修改多脚数据
				$set_str=Comm::join_sql_set($arr);//将数组转为sql语句
				Yii::app()->db
				->createCommand("update beu_manyfoot set ".$set_str.' where id='.$ret_foot['data']['id'])
				->execute();
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据衣服编号获取其详细信息
	**/
	static function getClothesInfoById($clothesid,$touchid){
		
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_numeric($clothesid) || intval($clothesid)!=$clothesid || $clothesid<=0){
				throw new Exception('衣服编号有误！');
			}
			
			$sel = Yii::app()->db->createCommand();
			//拼接需要查询的字段
			$select_field='beu_clothes.id,name,beu_clothes.brandid,brandnumber,thumbnail';
			if(!empty($_SESSION['table_where'])){
				$select_field.=',clothes.price,clothes.discountprice';
			}else{
				$select_field.=',beu_clothes.price,beu_clothes.discountprice';
			}
			//查询衣服数据
			$sel->select($select_field)
			->from($_SESSION['clothes_table'] .' as clothes')
			->join('beu_clothes','beu_clothes.id=clothes.clothesid')
			->where('clothes.status>=10 and clothes.status<>12 and clothes.touchid='.$touchid.' and clothes.clothesid='.$clothesid);
			if(!empty($_SESSION['table_where'])){
				$sel->andwhere($_SESSION['table_where']);
			}
			$oj=$sel->queryRow();
			
			if(empty($oj)){
				throw new Exception('未找到当前单品的数据！');
			}
			$clothesRelated_ret=self::beuRelatedSelectByClothesid($clothesid);//获取当前衣服绑定的不同颜色
			$clothesid_arr=array($clothesid);//将当前衣服添加为默认颜色
			if(count($clothesRelated_ret)>0){//当前衣服绑定有不同颜色
				for($ii=1;$ii<=30;$ii++){
					if(!empty($clothesRelated_ret[0]['clothesid'.$ii])){
						$clothesid_arr[] = $clothesRelated_ret[0]['clothesid'.$ii];
					}
				}
			}
			
			//查询不同颜色数据
			$color_sel = Yii::app()->db->createCommand();
			$color_sel->select('beu_clothes.id,colorimage,thumbnail,beu_clothes.code,beu_clothes.name,beu_clothes.discountprice')
			->from($_SESSION['clothes_table'] .' as clothes')
			->join('beu_clothes','beu_clothes.id=clothes.clothesid')
			->where('clothes.status in(10,11)  and clothes.touchid='.$touchid.' and clothes.clothesid in('.implode(',',$clothesid_arr).')');
			if(!empty($_SESSION['table_where'])){
				$color_sel->andwhere($_SESSION['table_where']);
			}
			
			$clothesRelated_ret=$color_sel->queryAll();
			if(count($clothesRelated_ret)>0){
				foreach($clothesRelated_ret as $k=>$v){
					$clothesRelated_ret[$k]['colorimage'] = Yii::app()->params['img_server_host'].'/colorimage/'.$v['code'].'_3d_00.jpg';
				}
			}
			$oj['clothesRelated']=$clothesRelated_ret;
			$ret['data']=$oj;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	 * 前端根据条件查找衣服列表
	 * @parm $page 当前页
	 * @parm $pagecount 每页显示数量
	 * @parm $arr:array 参数数组
	 * touchid 触摸屏id
	 * modeltype 模特类型
	 * brandid 品牌ID
	 * clothescategory 一级品类
	 * clothescategory2 二级品类
	 * strd_data 开始时间
	 * end_data 结束时间
	 * key 搜索关键字
	 * @return 成功后返回数据 array ('data' => $newstypelist, 'count' => $con,'pageSize'=>$pagecount,'page'=>$page ) data是查询到的数据，count是总页数，pageSize是每页显示数量，page是当前页
	 */
	public static function getClothesListSelectByParam($page = 1, $pagecount = 8, $arr = array()) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			if (empty ( $page ) || !is_numeric($page) || intval($page)!=$page || $page<=0) { //规范页码
				$page = 1;
			}
			if (empty ( $pagecount ) || !is_numeric($pagecount) || intval($pagecount)!=$pagecount || $pagecount<=0) { //规范数据量
				$pagecount = 8;
			}
		 
			$w_s = '1=1';
			$order_date = 'clothes.sort asc,beu_clothes.date_add desc';
			$w_s_arr = array ();
			if (count ( $arr ) > 0 && gettype($arr)=='array') {
				if (isset ( $arr ['touchid'] ) && !empty ( $arr ['touchid'] ) && is_numeric($arr ['touchid']) && intval($arr ['touchid'])==$arr ['touchid'] && $arr ['touchid']>0) {
					$str_param = trim ( $arr ['touchid'] );
					$w_s .= " and clothes.touchid={$str_param}";
				}
				if (isset ( $arr ['brandid'] ) && !empty ( $arr ['brandid'] ) && is_numeric($arr ['brandid']) && intval($arr ['brandid'])==$arr ['brandid'] && $arr ['brandid']>0) {
					$str_param = trim ( $arr ['brandid'] );
					$w_s .= " and beu_clothes.brandid={$str_param}";
				}
				if (isset ( $arr ['modeltype'] ) && !empty ( $arr ['modeltype'] )) {
					$str_param = trim ( $arr ['modeltype'] );
					$w_s .= " and beu_clothes.modelgender in({$str_param})";
				}
				 
				if (isset ( $arr ['clothescategory'] ) && !empty ( $arr ['clothescategory'] ) && is_numeric($arr ['clothescategory']) && intval($arr ['clothescategory'])==$arr ['clothescategory'] && $arr ['clothescategory']>0) {
					$str_param = trim ( $arr ['clothescategory'] );
					$w_s .= " and beu_clothes.clothescategory={$str_param}";
				}
				if (isset ( $arr ['clothescategory2'] ) && !empty ( $arr ['clothescategory2'] )) {
					$str_param = trim ( $arr ['clothescategory2'] );
					$w_s .= " and beu_clothes.clothescategory2 in({$str_param})";
				}
				
				if (isset ( $arr ['strd_data'])) {
					$str_param = trim ( $arr ['strd_data'] );
					$w_s .= " and DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d')>='{$str_param}'";
				}
				
				if (isset ( $arr ['end_data'] )) {
					$str_param = trim ( $arr ['end_data'] );
					$w_s .= " and DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d')<='{$str_param}'";
				}
				if (isset ( $arr ['where'] )) {
					$str_param = trim ( $arr ['where'] );
					$w_s .= " and ".$str_param;
				}
				if (isset ( $arr ['order'] )) {
					$order_date = $arr ['order'];
				}
				$status_str='';
				if (isset ( $arr ['status'] )) {
					$status_str=$arr ['status'];
				}
				if (isset ( $arr ['key'] ) && !empty ( $arr ['key'] )) { //规范关键词
					$str_param = trim ( $arr ['key'] );
					
					if(empty($arr['keytype'])){
						$w_s .= ' and (';
						if (!empty ( $str_param ) && is_numeric($str_param) && intval($str_param)==$str_param && $str_param>0) {
							$w_s .= "beu_clothes.id={$str_param} or ";
						}
						$w_s .= "beu_clothes.name like '%{$str_param}%'";
						$w_s .= " or beu_clothes.brandnumber like '%{$str_param}%' "; 
						$w_s .= " or beu_clothes.code like '%{$str_param}%')"; 
					}else{
						if($arr['keytype'] == 2){//id
							$w_s .= " and (beu_clothes.id={$str_param})";
						}else if($arr['keytype'] == 3){//品名
							$w_s .= " and (beu_clothes.name like '%{$str_param}%')";
						}else if($arr['keytype'] == 1){//款号
							$w_s .= " and (beu_clothes.brandnumber like '%{$str_param}%' or beu_clothes.brandnumber2 like '%{$str_param}%') and clothes.status in(".(empty($status_str)?"10,11":$status_str).")";
						} 
					} 
				}else if(isset ( $arr ['id'] ) && !empty ( $arr ['id'] )){
					$w_s .= ' and beu_clothes.id in('.$arr ['id'].')';
				}else{
					$w_s .= " and clothes.status=".(empty($status_str)?11:$status_str);
				}
			}
			
			
			$sele_zd = 'beu_clothes.id,beu_clothes.name,beu_clothes.brandid,beu_clothes.brandnumber,thumbnail,beu_clothes.square,imagescontent,beu_clothes.code,beu_clothes.level,beu_clothes.foottype,beu_clothes.underwear,beu_clothes.underpants,beu_clothes.clothescategory,beu_clothes.easy,beu_clothes.longsleeve';//需要查询的字段
			if(!empty($_SESSION['table_where'])){
				$w_s.=' and '.$_SESSION['table_where'];
				$sele_zd.=',clothes.price,clothes.discountprice';
			}else{
				$sele_zd.=',beu_clothes.price,beu_clothes.discountprice';
			}
			$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( $_SESSION['clothes_table'].' as clothes' )->join ( 'beu_clothes', 'beu_clothes.id=clothes.clothesid' )->where ( $w_s )->order($order_date);
			
			$newstypelist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll();
			if(count($newstypelist)==0){
				throw new Exception('衣服列表数据为空！');
			}
			//取得衣服id
			$clothesID=array();
			foreach($newstypelist as $key=>$value){
				$newstypelist[$key]['color_array']=array();
				$clothesID_ret=self::getClothesColorByClothesID(array($value['id']),$arr['touchid']);
				if($clothesID_ret['status']==1){
					foreach($clothesID_ret['data'] as $value2){
						if(is_array($value2) && isset($value2['color_array'])){
							$newstypelist[$key]['color_array']=$value2['color_array'];
						}
					}
				}
				if(!empty($value['imagescontent'])){
					$imagescontent=json_decode($value['imagescontent'],true);
					if(isset($imagescontent['graphicmodel'][0]) && !empty($imagescontent['graphicmodel'][0])){
						$newstypelist[$key]['thumbnail']=$imagescontent['graphicmodel'][0];
					}
				}
				unset($newstypelist[$key]['imagescontent']);
			} 
			
			//获取总个数
			$sql2 = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id' )->from ( $_SESSION['clothes_table'].' as clothes' )->join ( 'beu_clothes', 'beu_clothes.id=clothes.clothesid' )->where ( $w_s )->order($order_date);
			$con = $sql2->queryAll ();
			$arr_con = $con;
			$con = count ( $con );
			
			$ret['data']=$newstypelist;
			$ret['count']=$con;
			$ret['pageSize']=$pagecount;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	
	}
	
	
	
	
public static function getClothesListSelectByParamtest($page = 1, $pagecount = 8, $arr = array()) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			if (empty ( $page ) || !is_numeric($page) || intval($page)!=$page || $page<=0) { //规范页码
				$page = 1;
			}
			if (empty ( $pagecount ) || !is_numeric($pagecount) || intval($pagecount)!=$pagecount || $pagecount<=0) { //规范数据量
				$pagecount = 8;
			}
		 
			$w_s_arr = array ();
			$sql = Yii::app ()->db->createCommand ()->select ( "*" )->from ('mytest2')->where ( 'id>0' )->group("mytest2.clothesid");
			$newstypelist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
			if(count($newstypelist)==0){
				throw new Exception('衣服列表数据为空！');
			}
			
			//获取总个数
			$sql2 = Yii::app ()->db->createCommand ()->select ( '*' )->from ('mytest2')->where (  'id>0'  )->group("mytest2.clothesid");
			$con = $sql2->queryAll ();
			$arr_con = $con;
			$con = count ( $con );
			$ret['data']=$newstypelist;
			$ret['count']=$con;
			$ret['pageSize']=$pagecount;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	
	}
	/**
	* 根据触摸屏ID获取其屏下的所有衣服ID
	**/
	public static function getAllClothesidByTouchid($touchid){
		$ret=array('status'=>0,'msg'=>'');
		try {
			$clothes_sql=Yii::app ()->db->createCommand ()->select ( 'clothes.clothesid' )
						->from ( $_SESSION['clothes_table'].' as clothes' )
						->where ( 'clothes.status in(10,11) and touchid='.$touchid);
						if(!empty($_SESSION['table_where'])){
							$clothes_sql->andWhere($_SESSION['table_where']);
						}
			$newstypelist=$clothes_sql->queryAll ();	
			if(count($newstypelist)==0){
				throw new Exception('衣服ID为空！');
			}
			$clothesid=array();
			foreach($newstypelist as $value){
				$clothesid[]=$value['clothesid'];
			}
			$ret['data']=$clothesid;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据衣服id获取其不同的颜色
	**/
	public static function getClothesColorByClothesID($clothesID,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try {
			if(empty($clothesID) || !is_array($clothesID)){
				throw new Exception('衣服编号参数不为数组！');
			}
			$color_w='';
			for($i=1;$i<=30;$i++){//拼接不同颜色查找
				if(!empty($color_w)){
					$color_w.=' or ';
				}
				$color_w.='clothesid'.$i .' in('.implode(',',$clothesID).')';
			}
			//查找单品不同色
			$color_con = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_clothesrelated' )->where ( $color_w )->queryAll ();
			
			if(count($color_con)==0){
				throw new Exception('衣服没有不同色！');
			}
			foreach($color_con as $color_con_value){
				$color_clothes_id=array();
				$list_clothes_id_key=false;
				foreach($color_con_value as $key=>$value){//将此条不同色数据里的衣服提取出来
					if($key!='id' && !empty($value)){
						$color_clothes_id[]=$value;
						if($list_clothes_id_key===false){//查看此单品是否衣服列表主单品
							$list_clothes_id_key=array_search($value,$clothesID);
						}
					}
				}
				if($list_clothes_id_key!==false){//如果此条不同色数据里包含衣服列表主单品，就将所查询到的不同色放入其下
					$clothesID[$list_clothes_id_key]=array();
					$sele_zd=',beu_clothes.price,beu_clothes.discountprice';
					if(!empty($_SESSION['table_where'])){
						$sele_zd=',clothes.price,clothes.discountprice';
					}
					//获取不同色的颜色图片
					$color_clothes_sql=Yii::app ()->db->createCommand ()->select ( 'clothes.clothesid,beu_clothes.brandnumber,beu_clothes.name,beu_clothes.code,beu_clothes.thumbnail,beu_clothes.imagescontent,beu_clothes.level,beu_clothes.foottype,beu_clothes.underwear,beu_clothes.underpants,beu_clothes.clothescategory,beu_clothes.easy,beu_clothes.longsleeve,clothes.status'.$sele_zd )
					->from ( $_SESSION['clothes_table'].' as clothes' )
					->join ( 'beu_clothes', 'beu_clothes.id=clothes.clothesid' )
					->where ( 'clothesid in('.implode(',',$color_clothes_id).') and clothes.status in(10,11) and touchid='.$touchid);
					if(!empty($_SESSION['table_where'])){
						$color_clothes_sql->andWhere($_SESSION['table_where']);
					}
					//var_dump($color_clothes_sql);exit();
					$clothesID[$list_clothes_id_key]['color_array']=$color_clothes_sql->queryAll ();
					foreach($clothesID[$list_clothes_id_key]['color_array'] as $key=>$value){
						$clothesID[$list_clothes_id_key]['color_array'][$key]['g_img']='';
						if(!empty($value['imagescontent'])){
							$imagescontent=json_decode($value['imagescontent'],true);
							if(isset($imagescontent['graphicmodel'][0]) && !empty($imagescontent['graphicmodel'][0])){
								$clothesID[$list_clothes_id_key]['color_array'][$key]['g_img']=$imagescontent['graphicmodel'][0];
							}else if(isset($imagescontent['staticImagecontent'][0]) && !empty($imagescontent['staticImagecontent'][0])){
								$clothesID[$list_clothes_id_key]['color_array'][$key]['g_img']=$imagescontent['staticImagecontent'][0];
							}
						}
						//如果当前单品没有立体图 或静态图 就查找其不同穿法的立体图 静态图
						if(empty($clothesID[$list_clothes_id_key]['color_array'][$key]['g_img'])){
							$differentclothes=Clothes::clothesDifferentSelectByClothesid($value['clothesid']);//不同穿法衣服ID
							$c = trim(implode(',',$differentclothes));
							if(!empty($c)){
								$wherediferent="beu_clothes.id<>".$value['clothesid']." && beu_clothes.id in($c ) ";
								$differenobj = Yii::app()->db->createCommand()
								->select('beu_clothes.imagescontent')
								->from('beu_clothes')
								->where($wherediferent);
								$differenobj=$differenobj->queryAll();
								if(count($differenobj)>0){
									foreach ($differenobj as $key2=>$value2){
										if(!empty($value2['imagescontent'])){
											$imagescontent2=json_decode($value2['imagescontent'],true);
											if(isset($imagescontent2['graphicmodel'][0]) && !empty($imagescontent2['graphicmodel'][0])){
												$clothesID[$list_clothes_id_key]['color_array'][$key]['g_img']=$imagescontent2['graphicmodel'][0];
												break;
											}else if(isset($imagescontent2['staticImagecontent'][0]) && !empty($imagescontent2['staticImagecontent'][0])){
												$clothesID[$list_clothes_id_key]['color_array'][$key]['g_img']=$imagescontent2['staticImagecontent'][0];
											}
										}
									}
									
								}
							}
						}
						//不同穿 没有立体图 就将其值赋为默认图片
						if(empty($clothesID[$list_clothes_id_key]['color_array'][$key]['g_img'])){
							$clothesID[$list_clothes_id_key]['color_array'][$key]['g_img']=$value['thumbnail'];
						}
						unset($clothesID[$list_clothes_id_key]['color_array'][$key]['imagescontent']);
					}
				}
			}
			$ret['data']=$clothesID;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据触摸屏号及其排序编号 将对应排序修改为默认排序
	**/
	public static function updateTClothesSortBySort($touchid,$sort,$model=0){
		$ret=array('status'=>0,'msg'=>'');
		try {
			if (empty ( $touchid ) || !is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0) { 
				throw new Exception('触摸屏ID错误');
			}
			$w_str=' and sort='.$sort;
			if($sort==-1){
				$w_str='';
				if(!empty($model)){
					try{
						$ret_touch=self::getTouchclothesinfo('clothesid',array('touchid'=>'='.$touchid,'sort'=>'<>9999'));
						if($ret_touch['status']==0){
							throw new Exception('touch empty');
						}
						$touch_arr=array();
						foreach($ret_touch['data'] as $value){
							$touch_arr[]=$value['clothesid'];
						}
						$ret_beu=self::getBeuclothesinfo('id',array('modelgender'=>'='.$model,'id'=>' in('.implode(',',$touch_arr).')'));
						if($ret_beu['status']==0){
							throw new Exception('beu empty');
						}
						$beu_arr=array();
						foreach($ret_beu['data'] as $value){
							$beu_arr[]=$value['id'];
						}
						$w_str=' and clothesid in('.implode(',',$beu_arr).')';
					}catch(Exception $e){
						$w_str=' and clothesid=0';
					}
				}
			}else if (empty ( $sort ) || !is_numeric($sort) || intval($sort)!=$sort || $sort<=0 || $sort==9999) { 
				throw new Exception('衣服排序编号错误');
			}
			Yii::app()->db
				->createCommand('update touch_clothes set sort=9999 where touchid='.$touchid.$w_str)
				->execute();
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据触摸屏号及其分类排序编号 将对应排序修改为默认排序
	**/
	public static function updateTClothesSortBystyleSort($touchid,$sort,$model=0){
		$ret=array('status'=>0,'msg'=>'');
		try {
			if (empty ( $touchid ) || !is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0) { 
				throw new Exception('触摸屏ID错误');
			}
			$w_str=' and style_sort='.$sort;
			if($sort==-1){
				$w_str='';
				if(!empty($model)){
					try{
						$ret_touch=self::getTouchclothesinfo('clothesid',array('touchid'=>'='.$touchid,'style_sort'=>'<>9999'));
						if($ret_touch['status']==0){
							throw new Exception('touch empty');
						}
						$touch_arr=array();
						foreach($ret_touch['data'] as $value){
							$touch_arr[]=$value['clothesid'];
						}
						$ret_beu=self::getBeuclothesinfo('id',array('modelgender'=>'='.$model,'id'=>' in('.implode(',',$touch_arr).')'));
						if($ret_beu['status']==0){
							throw new Exception('beu empty');
						}
						$beu_arr=array();
						foreach($ret_beu['data'] as $value){
							$beu_arr[]=$value['id'];
						}
						$w_str=' and clothesid in('.implode(',',$beu_arr).')';
					}catch(Exception $e){
						$w_str=' and clothesid=0';
					}
				}
			}else if (empty ( $sort ) || !is_numeric($sort) || intval($sort)!=$sort || $sort<=0 || $sort==9999) { 
				throw new Exception('衣服排序编号错误');
			}
			Yii::app()->db
				->createCommand('update touch_clothes set style_sort=9999 where touchid='.$touchid.$w_str)
				->execute();
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	
	/**
	 * 陈列手册 根据条件查找衣服
	 * @parm $page 当前页
	 * @parm $pagecount 每页显示数量
	 * @parm $arr:array 参数数组
	 * modeltype 模特类型
	 * brandid 品牌ID
	 * clothescategory 一级品类 此条件可为多值
	 * clothescategory2 二级品类
	 * strd_data 开始时间
	 * end_data 结束时间
	 * key 搜索关键字
	 * @return 成功后返回数据 array ('data' => $newstypelist, 'count' => $con,'pageSize'=>$pagecount,'page'=>$page ) data是查询到的数据，count是总页数，pageSize是每页显示数量，page是当前页
	 */
	public static function getDisplayClothesByParam($page = 1, $pagecount = 40, $arr = array()) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			$page = trim ( $page );
			$pagecount = trim ( $pagecount );
			if (empty ( $page ) || !is_numeric($page) || intval($page)!=$page || $page<=0) { //规范页码
				$page = 1;
			}
			if (empty ( $pagecount ) || !is_numeric($pagecount) || intval($pagecount)!=$pagecount || $pagecount<=0) { //规范数据量
				$pagecount = 40;
			}
			$w_s ='status=11';
			$w_s_c='';
			$w_s_c_all='status=11';
			if($arr ['single_type']=='display_c'){//查询单品是使用
				$w_s .= ' and specialclothes like \'%"side":%\'';
				$w_s_c_all=$w_s;
			}
			
			$w_s_arr = array ();
			if (count ( $arr ) > 0 && gettype($arr)=='array') {
				if (isset ( $arr ['brandid'] ) && !empty ( $arr ['brandid'] ) && is_numeric($arr ['brandid']) && intval($arr ['brandid'])==$arr ['brandid'] && $arr ['brandid']>0) {
					$str_param = trim ( $arr ['brandid'] );
					$w_s .= " and beu_clothes.brandid={$str_param}";
					$w_s_c_all.=" and beu_clothes.brandid={$str_param}";
				}
				if (isset ( $arr ['modelgenderidd'] ) && !empty ( $arr ['modelgenderidd'] ) && is_numeric($arr ['modelgenderidd']) && intval($arr ['modelgenderidd'])==$arr ['modelgenderidd'] && $arr ['modelgenderidd']>0) {
					$str_param = trim ( $arr ['modelgenderidd'] );
					$w_s .= " and beu_clothes.modelgender={$str_param}";
					$w_s_c_all.=" and beu_clothes.modelgender={$str_param}";
				}
				if (isset ( $arr ['categoryid1'] ) && !empty ( $arr ['categoryid1'] ) ) {
					$str_param = trim ( $arr ['categoryid1'] );
					$w_s_c .= " and beu_clothes.clothescategory in({$str_param})";
				}
				if (isset ( $arr ['clothescategory'] ) && !empty ( $arr ['clothescategory'] ) ) {
					$str_param = trim ( $arr ['clothescategory'] );
					$w_s_c_all .= " and beu_clothes.clothescategory in({$str_param})";
					if(empty($w_s_c)){
						$w_s_c .= " and beu_clothes.clothescategory in({$str_param})";
					}
				}
				if (isset ( $arr ['categoryid'] ) && !empty ( $arr ['categoryid'] ) && is_numeric($arr ['categoryid']) && intval($arr ['categoryid'])==$arr ['categoryid'] && $arr ['categoryid']>0) {
					$str_param = trim ( $arr ['categoryid'] );
					$w_s_c .= " and beu_clothes.clothescategory2={$str_param}";
				}
				
				if (isset ( $arr ['startdate']) && !empty( $arr ['startdate'])) {
					$str_param = trim ( $arr ['startdate'] );
					$w_s .= " and DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d')>='{$str_param}'";
				}
				
				if (isset ( $arr ['enddate'] ) && !empty( $arr ['enddate'])) {
					$str_param = trim ( $arr ['enddate'] );
					$w_s .= " and DATE_FORMAT(beu_clothes.date_add,'%Y-%m-%d')<='{$str_param}'";
				}
				
				if (isset ( $arr ['key'] ) && !empty ( $arr ['key'] )) { //规范关键词
					$str_param = trim ( $arr ['key'] );
					$w_s .= " and beu_clothes.brandnumber like '%{$str_param}%' ";   
				}
				
			}
			//排序
			$order_date = 'beu_clothes.id desc';
			$sele_zd = 'id,thumbnail,imagescontent,specialclothes';
			$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ('beu_clothes' )->where ( $w_s.$w_s_c )->order($order_date);
			$sql3 = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id,beu_clothes.clothescategory,beu_clothes.clothescategory2' )->from ( 'beu_clothes' )->where ( $w_s_c_all );
			$sql2 = Yii::app ()->db->createCommand ()->select ( 'beu_clothes.id' )->from ( 'beu_clothes' )->where ( $w_s.$w_s_c );
			$arr_con = $sql3->queryAll ();
			$clothescategory=array();
			$clothescategory2=array();
			if(count($arr_con)>0){
				foreach($arr_con as $value){
					if(!in_array($value['clothescategory'],$clothescategory)){
						$clothescategory[]=$value['clothescategory'];
					}
					if(!in_array($value['clothescategory2'],$clothescategory2)){
						$clothescategory2[]=$value['clothescategory2'];
					}
				}
			}
			$ret['data']=array ('clothescategory'=>$clothescategory,'clothescategory2'=>$clothescategory2 );
			$newstypelist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
			if(count($newstypelist)==0){
				throw new Exception('衣服列表数据为空！');
			}
			
			foreach($newstypelist as $key=>$value){
				if(!empty($value['imagescontent'])){
					$imagescontent=json_decode($value['imagescontent'],true);
					if(isset($imagescontent['graphicmodel'][0])){
						$newstypelist[$key]['thumbnail']=$imagescontent['graphicmodel'][0];
					}
				}
				unset($newstypelist[$key]['imagescontent']);
			}
			//获取总个数
			$con = $sql2->queryAll ();
			$arr_con = $con;
			$con = count ( $con );
			
			$criteria = new CDbCriteria();
			$pages=new CPagination($con);
			$pages->pageSize=$pagecount;
			$pages->applyLimit($criteria);
			
			$ret['data']['data']=$newstypelist;
			$ret['data']['count']=$con;
			$ret['data']['pageSize']=$pagecount;
			$ret['data']['page']=$pages;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	
	}
	
	/**
	* 根据参数获取其衣服对应的分类
	**/
	public static function getClothesStyleByModel($arr){
		$ret=array('status'=>0,'msg'=>'');
		try {
			$w_s = 'clothes.status=11';
			$w_s_arr = array ();
			if (count ( $arr ) > 0 && gettype($arr)=='array') {
				if (isset ( $arr ['touchid'] ) && !empty ( $arr ['touchid'] ) && is_numeric($arr ['touchid']) && intval($arr ['touchid'])==$arr ['touchid'] && $arr ['touchid']>0) {
					$str_param = trim ( $arr ['touchid'] );
					$w_s .= " and clothes.touchid={$str_param}";
				}
				if (isset ( $arr ['modeltype'] ) && !empty ( $arr ['modeltype'] )) {
					$str_param = trim ( $arr ['modeltype'] );
					$w_s .= " and beu_clothes.modelgender in({$str_param})";
				}
				if (isset ( $arr ['brandid'] ) && !empty ( $arr ['brandid'] )) {
					$str_param = trim ( $arr ['brandid'] );
					$w_s .= " and beu_clothes.brandid ={$str_param}";
				}
			}
			$sele_zd = 'beu_clothes.clothescategory';//需要查询的字段
			if(!empty($_SESSION['table_where'])){
				$w_s.=' and '.$_SESSION['table_where'];
			}
			$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( $_SESSION['clothes_table'].' as clothes' )->join ( 'beu_clothes', 'beu_clothes.id=clothes.clothesid' )->where ( $w_s )->group('beu_clothes.clothescategory');
			$newstypelist = $sql->queryAll ();
			if(count($newstypelist)==0){
				throw new Exception('衣服列表数据为空！');
			}
			$ret['data']=$newstypelist;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	
	
/**
	* 通过搭配ID获取其衣服详细信息
	**/
	static function beuclothesSelectByDpid($dpid,$touchid){
		
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_numeric($dpid) || intval($dpid)!=$dpid || $dpid<=0){
				throw new Exception('搭配编号有误！');
			}
			$where="baidaid=$dpid && touchid=$touchid";
			$dapeiclothesid =Yii::app()->db->createCommand()
			->select("clothesid")
			->from("beu_baidaclothes")
			->where($where)
			->queryAll ();
			if(count($dapeiclothesid)==0){
				throw new Exception('未找到单品的数据！');
			}
			$clothesarray=array();
			foreach ($dapeiclothesid as $value)
			{
				$clothesarray[]=$value["clothesid"];
			}
			$clothesid=implode($clothesarray,",");
			
			
			$sel = Yii::app()->db->createCommand();
			//拼接需要查询的字段
			$select_field='beu_clothes.id,beu_clothes.name,thumbnail,beu_clothes.imagescontent';
			if(!empty($_SESSION['table_where'])){
				$select_field.=',clothes.price,clothes.discountprice';
			}else{
				$select_field.=',beu_clothes.price,beu_clothes.discountprice';
			}
			//查询衣服数据
			$sel->select($select_field)
			->from($_SESSION['clothes_table'] .' as clothes')
			->join('beu_clothes','beu_clothes.id=clothes.clothesid')
			->where('beu_clothes.id in ('.$clothesid.') ')
			->andwhere("touchid=$touchid");
			if(!empty($_SESSION['table_where'])){
				$sel->andwhere($_SESSION['table_where']);
			}
			$con=$sel->queryAll();
			foreach($con as $key=>$value){
				
				$is_bool=false;//标识当前单品是否有立体图 默认没有
				if(!empty($value['imagescontent'])){
					$img_arr=json_decode($value['imagescontent'],true);
					if(isset($img_arr['graphicmodel'][0])){
						$con[$key]['thumbnail']=$img_arr['graphicmodel'][0];
						$is_bool=true;
					}
				}
				if(!$is_bool){//如果没有找到立体图 就查询其不同穿法的立体图
					//获取当前单品的不同穿法
					$different=Clothes::clothesDifferentSelectByClothesid($value['id']);
					//将不同穿法的“赞”数据读取出来
					if(count($different)>0){
						//从不同穿法数组里清除当前单品
						$v_key=array_search($value['id'],$different);
						if($v_key!==false){
							unset($different[$v_key]);
						}
						$different_con = Yii::app()->db->createCommand()
						->select('beu_clothes.id,beu_clothes.imagescontent')
						->from('beu_clothes')
						->where('id in ('.implode(',',$different).')');
						$different_con=$different_con->queryAll();
						//遍历不同穿法的数据 将其立体图赋给此单品
						foreach($different_con as $different_key=>$different_value){
							if(!empty($different_value['imagescontent'])){
								$img_arr=json_decode($different_value['imagescontent'],true);
								if(isset($img_arr['graphicmodel'][0])){
									$con[$key]['thumbnail']=$img_arr['graphicmodel'][0];
									break;
								}
							}
						}
					}
				}
				
				$praise = Yii::app ()->db->createCommand ()
					->select ( '*' )
					->from ('beu_clothes_praise' )
					->where ( 'clothes='.$con[$key]['id'].'' )
					->order('praise desc,date_time desc')
					->queryRow ();
				$con[$key]['praise_num']=0;
				if(!empty($praise))
				{
					$con[$key]['praise_num']=$praise['praise'];
				}
			}
			
			
			if(empty($con)){
				throw new Exception('未找到当前单品的数据！');
			}
			
			$ret['data']=$con;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据款号 获取当前搭配屏的衣服数据 款号可对应不同色
	* @parm brandnumber 衣服款号
	* @parm touchid
	**/
	static function getClothesinfoByBrandNumber($brandnumber,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($brandnumber)){
				throw new Exception('款号为空！');
			}
			if(!is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误！');
			}
			$data=Yii::app ()->db->createCommand ()
				->select ( 'touch_clothes.clothesid,beu_clothes.brandnumber,beu_clothes.brandid' )
				->from ( 'touch_clothes' )
				->join('beu_clothes','beu_clothes.id=touch_clothes.clothesid')
				->where ('touch_clothes.touchid='.$touchid.' and (beu_clothes.brandnumber=\''.$brandnumber.'\' or beu_clothes.brandnumber2 like \'%'.$brandnumber.'%\')')
				->queryRow ();//查询主屏里的衣服
			if(empty($data)){
				throw new Exception('对应主屏的衣服数据为空！');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据衣服ID 获取当前搭配屏的衣服数据
	* @parm id 衣服编号
	* @parm touchid
	**/
	static function getClothesinfoByClothesId($id,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!is_numeric($id) || intval($id)!=$id || $id<=0){
				throw new Exception('衣服编号有误！');
			}
			if(!is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误！');
			}
			$data=Yii::app ()->db->createCommand ()
				->select ( 'touch_clothes.clothesid,beu_clothes.brandnumber,beu_clothes.brandid' )
				->from ( 'touch_clothes' )
				->join('beu_clothes','beu_clothes.id=touch_clothes.clothesid')
				->where ('touch_clothes.touchid='.$touchid.' and beu_clothes.id='.$id)
				->queryRow ();//查询主屏里的衣服
			if(count($data)==0){
				throw new Exception('对应主屏的衣服数据为空！');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据触摸屏及其模特统计其衣服包含的品牌
	**/
	static function getbrendlistBytouchandmodel($touchid,$modeltype){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$w_s = 'touch_clothes.touchid='.$touchid;
			if(!empty($modeltype)){
				$w_s .=' and beu_clothes.modelgender='.$modeltype;
			}
			$brandidarr = Yii::app ()->db->createCommand ()
			->select ( 'brandid' )
			->from ( 'touch_clothes' )
			->join ( 'beu_clothes', 'beu_clothes.id=touch_clothes.clothesid' )
			->where ( $w_s )
			->group ( 'beu_clothes.brandid' )
			->queryAll ();
			$arr = array ();
			foreach ( $brandidarr as $value ) {
				$arr [] = $value ['brandid'];
			}
			$ret['status']=1;
			$ret['data']=$arr;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据款号 获取衣服小图或中图
	* @parm sub_id
	* @parm touchid
	**/
	static function getClothesBycode($code){
		$ret=array('status'=>0,'msg'=>'');
		try{
			
			if(empty($code) ){
				throw new Exception('款号不能为空！');
			}
			$brandnumber_arr=explode(',',$code);
			$data=Yii::app ()->db->createCommand ()
				->select ( 'id,brandid,brandnumber,thumbnail,imagescontent' )
				->from ( 'beu_clothes' )
				->where ('brandnumber in (\''.implode('\',\'',$brandnumber_arr).'\')')
				->queryAll ();//查询主屏里的衣服
			if(count($data)==0){
				throw new Exception('没有相关衣服数据');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据衣服ID获得其信息
	* @parm clothesid 衣服ID
	* @parm select_str 可选，需要查询的字段 ，默认查询所有
	**/
	static function getClothesInfoByClothesIdAndPram($clothesid,$select_str='*'){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$data=Yii::app ()->db->createCommand ()
				->select ( $select_str )
				->from ( 'beu_clothes' )
				->join('beu_clothes_status_tmall','clothesid=beu_clothes.id')
				->where ('beu_clothes.id in ('.$clothesid.')')
				->queryAll ();
			if(count($data)==0){
				throw new Exception('没有相关衣服数据');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 获取衣服的批次号
	* @parm clothesid 衣服ID
	* @parm select_str 可选，需要查询的字段 ，默认查询所有
	**/
	static function getClothesbatch($clothesid,$select_str='*'){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$data=Yii::app ()->db->createCommand ()
				->select ( $select_str )
				->from ( 'beu_clothes' )
				->where ('beu_clothes.id in ('.$clothesid.')')
				->queryAll ();
			if(count($data)==0){
				throw new Exception('没有相关衣服数据');
			}
			$batch_arr=array();
			foreach($data as $key=>$value){
				$batch_arr[$value['id']]=substr($value['code'],5,3);
			}
			$ret['status']=1;
			$ret['data']=$batch_arr;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取天猫的卡线位置
	* @parm clothesid 衣服ID
	**/
	static function getClothingline($clothesid){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$data=Yii::app ()->db->createCommand ()
				->select ( 'tmall_line,isSYorXY' )
				->from ( 'beu_clothes' )
				->where ('beu_clothes.id in ('.$clothesid.')')
				->queryAll ();
			if(count($data)==0){
				throw new Exception('没有相关衣服数据');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 设置天猫的卡线位置
	* @parm clothesid 衣服ID
	* @parm code 衣服条码
	* @parm data 卡线数据
	* @parm isSYorXY 可选 衣服类别 1下衣 2连衣裙 3上衣(需转为0)
	**/
	static function setClothingline($clothesid,$code,$data,$isSYorXY=0){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$where='id='.$clothesid;
			if(empty($clothesid)){
				$where='code='.$code;
			}
			$up_s='';
			if(!empty($isSYorXY)){
				$isSYorXY=$isSYorXY==3?0:$isSYorXY;
				$up_s=',isSYorXY='.$isSYorXY;
			}
			$insert_sql='update beu_clothes set tmall_line=\''.json_encode($data).'\''.$up_s.' where '.$where;
			Yii::app ()->db->createCommand ($insert_sql)->execute();
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 获取衣服信息
	* @parm sel 可选 需要的衣服信息
	* @parm search 可选 数组 搜索条件 及其值
	* @parm order 可选 排序
	**/
	static function getBeuclothesinfo($sel='*',$search=array(),$order='id desc'){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='1';
			foreach($search as $key=>$value){
				if(!empty($sel_where)){
					$sel_where.=' and ';
				}
				$sel_where.=$key.$value;
			}
			//查看品牌上传数
			$data=Yii::app ()->db->createCommand ()
				->select ( $sel )
				->from ( 'beu_clothes' )
				->where ($sel_where)
				->order($order)
				->queryAll ();
			if(count($data)==0){
				throw new Exception('单品为空');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 获取衣服信息
	* @parm sel 可选 需要的衣服信息
	* @parm search 可选 数组 搜索条件 及其值
	* @parm order 可选 排序
	**/
	static function getTouchclothesinfo($sel='*',$search=array(),$order='clothesid desc'){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='1';
			foreach($search as $key=>$value){
				if(!empty($sel_where)){
					$sel_where.=' and ';
				}
				$sel_where.=$key.$value;
			}
			//查看品牌上传数
			$data=Yii::app ()->db->createCommand ()
				->select ( $sel )
				->from ( 'touch_clothes' )
				->where ($sel_where)
				->order($order)
				->queryAll ();
			if(count($data)==0){
				throw new Exception('单品为空');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 获取接口衣服信息
	* @parm sel 可选 需要的衣服信息
	* @parm search 可选 数组 搜索条件 及其值
	* @parm limit 可选 分页 格式：页码-每页数量
	* @parm order 可选 排序
	**/
	static function getApiclothesinfo($sel='*',$search=array(),$limit='',$order='api_clothes.id desc'){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='1';
			$is_beu=false;//判断是否启用beu_clothes表
			$is_touch=false;//判断是否启用touch_clothes表
			$no_group=false;
			foreach($search as $key=>$value){
				if($key=='no_group'){
					$no_group=true;
					continue;
				}
				if(!empty($sel_where)){
					$sel_where.=' and ';
				}
				if(strstr($key,'beu_clothes.')){
					$is_beu=true;
				}
				if(strstr($key,'touch_clothes.')){
					$is_touch=true;
				}
				
				$sel_where.=$key.$value;
			}
			//查看品牌上传数
			$data=Yii::app ()->db->createCommand ()
				->select ( $sel )
				->from ( 'api_clothes' );
				if($is_beu){
					$data->join('beu_clothes','beu_clothes.brandnumber=api_clothes.brandnumber and beu_clothes.brandid=api_clothes.brandid');
				}
				if($is_touch){
					$data->join('touch_clothes','beu_clothes.id=touch_clothes.clothesid');
				}
						
				$data->where ($sel_where);
			if(!empty($limit)){
				$limit=explode('-',$limit);
				if(count($limit)==2 && is_numeric($limit[0]) && is_numeric($limit[1])){
					$data->limit ( intval($limit[1]), (intval($limit[0]) - 1) * intval($limit[1]) );
				}
			}
			if(!$no_group){
				$data->group('api_clothes.brandnumber');
			}
			$data=$data->order($order)->queryAll ();
			if(empty($data)){
				throw new Exception('API--单品为空');
			}
			$ret['status']=1;
			$ret['data']=$data;
			$ret['where']=$sel_where;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 修改Beu_clothes衣服信息
	* @parm w_p where条件
	* @parm parm 数组 修改内容
	**/
	static function upBeuclothes($parm,$w_p=''){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='';
			foreach($parm as $key=>$value){
				if(!empty($sel_where)){
					$sel_where.=',';
				}
				$sel_where.=$key.'=\''.$value.'\'';
			}
			if(!empty($w_p)){
				$w_p=' where '.$w_p;
			}
			Yii::app ()->db->createCommand ('update beu_clothes set '.$sel_where.$w_p)->execute();
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 修改Api衣服信息
	* @parm w_p where条件
	* @parm parm 数组 修改内容
	**/
	static function upApiclothes($parm,$w_p=''){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='';
			foreach($parm as $key=>$value){
				if(!empty($sel_where)){
					$sel_where.=',';
				}
				$sel_where.=$key.'=\''.$value.'\'';
			}
			if(!empty($w_p)){
				$w_p=' where '.$w_p;
			}
			Yii::app ()->db->createCommand ('update api_clothes set '.$sel_where.$w_p)->execute();
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 添加Api衣服信息
	* @parm parm 数组 添加信息
	**/
	static function insertApiclothes($parm){
		$ret=array('status'=>0,'msg'=>'');
		try{
			Yii::app ()->db->createCommand ('insert into api_clothes('.implode(',',array_keys($parm)).') values(\''.implode('\',\'',$parm).'\')')->execute();
			$insertid=Yii::app()->db->getLastInsertID();
			$ret['status']=1;
			$ret['data']=$insertid;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 获取衣服推送信息
	* @parm sel 可选 需要的衣服信息
	* @parm search 可选 数组 搜索条件 及其值
	* @parm order 可选 排序
	**/
	static function getpushclothes($sel='*',$search=array(),$order='id desc',$group=''){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='1';
			foreach($search as $key=>$value){
				if(!empty($sel_where)){
					$sel_where.=' and ';
				}
				$sel_where.=$key.$value;
			}
			//查看品牌上传数
			$data=Yii::app ()->db->createCommand ()
				->select ( $sel )
				->from ( 'beu_push_clothes' )
				->where ($sel_where)
				->order($order);
			if(!empty($group)){
				$data->group($group);
			}
			$data=$data->queryAll ();
			if(count($data)==0){
				throw new Exception('单品为空');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 添加衣服推送信息
	* @parm parm 数组 添加信息
	**/
	static function insertpushclothes($parm){
		$ret=array('status'=>0,'msg'=>'');
		try{
			Yii::app ()->db->createCommand ('insert into beu_push_clothes('.implode(',',array_keys($parm)).') values(\''.implode('\',\'',$parm).'\')')->execute();
			$insertid=Yii::app()->db->getLastInsertID();
			$ret['status']=1;
			$ret['data']=$insertid;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 修改衣服推送信息
	* @parm w_p where条件
	* @parm parm 数组 修改内容
	**/
	static function uppushclothes($parm,$w_p=''){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sel_where='';
			foreach($parm as $key=>$value){
				if(!empty($sel_where)){
					$sel_where.=',';
				}
				$sel_where.=$key.'=\''.$value.'\'';
			}
			if(!empty($w_p)){
				$w_p=' where '.$w_p;
			}
			Yii::app ()->db->createCommand ('update beu_push_clothes set '.$sel_where.$w_p)->execute();
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 修改单品的主次关系
	* @parm clothes 衣服数组 键值对 键为衣服id 值为衣服即将修改的状态
	* @parm touchid 触摸屏id
	**/
	static function upmainClothes($clothes,$touchid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$Display_status_num=11;//上架显示状态
			$no_Display_status_num=10;//上架不显示状态
			$out_status_num=8;//下架状态
			$ForSale_status_num=12;//待售状态
			$Logic_clothes_arr=array();//逻辑整理后的单品
			$Logic_clothes_arr[$Display_status_num]=array();
			$Logic_clothes_arr[$no_Display_status_num]=array();
			$Logic_clothes_arr[$out_status_num]=array();
			$Logic_clothes_arr[$ForSale_status_num]=array();
			
			$Related_arr=self::beuRelatedSelectByClothesid(implode(',',array_keys($clothes)));// 获取衣服的不同色
			//将衣服对应的每组不同色提取出来
			$Related_clothes_arr=array();//单品的不同色数组
			$Different_clothes_arr=array();//单品的不同穿数组
			$clothesid_arr=array();//衣服数组 用于查询其状态
			$clothes_status_arr=array();//衣服数组 用于查询其状态
			foreach($Related_arr as $value){
				$c_arr=array();
				foreach($value as $v_key=>$v_value){
					if($v_key=='id' || empty($v_value)){
						continue;
					}
					$c_arr[]=$v_value;
					$clothesid_arr[]=$v_value;
				}
				if(count($c_arr)>0){
					$Related_clothes_arr[]=$c_arr;
				}
			}
			$no_Related_arr=Comm::array_diff_fast(array_keys($clothes),$clothesid_arr);//获取还未做个SKU关联的单品
			$clothesid_arr=array_merge($clothesid_arr,$no_Related_arr);//将还未做个SKU关联的单品拼接进衣服数组做状态查询
			foreach($no_Related_arr as $value){
				$Related_clothes_arr[]=array($value);
			}
			
			//查询单品的状态
			$t_clothes_ret=self::getTouchclothesinfo('clothesid,status',array('touchid'=>'='.$touchid,'clothesid'=>' in('.implode(',',$clothesid_arr).')'));
			if($t_clothes_ret['status']==0){
				throw new Exception($t_clothes_ret['msg']);
			}
			//获取单品的不同穿
			$Different_arr=self::beuDifferentSelectByClothesid(implode(',',$clothesid_arr));
			$Diff_clothes=array();
			foreach($Different_arr as $value){
				$c_arr=array();
				foreach($value as $v_key=>$v_value){
					if($v_key=='id' || empty($v_value)){
						continue;
					}
					$c_arr[]=$v_value;
					$Diff_clothes[]=$v_value;
				}
				if(count($c_arr)>0){
					$Different_clothes_arr[]=$c_arr;
				}
			}
			$no_Different_arr=Comm::array_diff_fast(array_keys($clothes),$Diff_clothes);//获取还未做个SKC关联的单品
			foreach($no_Different_arr as $value){
				$Different_clothes_arr[]=array($value);
			}
			//将单品的状态进行归总
			foreach($t_clothes_ret['data'] as $value){
				$clothes_status_arr[$value['clothesid']]=$value['status'];
				if(in_array($value['clothesid'],$no_Different_arr)){//当前单品属于未做SKU关联的单品
					$Logic_clothes_arr[$clothes[$value['clothesid']]][]=$value['clothesid'];
				}
			}
			//逻辑判断单品修改后的状态
			foreach($Related_clothes_arr as $value){
				$current_status=array();//当前SKU状态数组
				$up_status=array();//SKU修改状态数组
				$Display_clothes=array();//逻辑判断后的显示单品
				$no_Display_clothes=array();//逻辑判断后的不显示单品
				$out_clothes=array();//逻辑判断后的下架单品
				$ForSale_clothes=array();//逻辑判断后的待售单品
				$up_Related_clothes=array();//需要修改的SKU单品数组
				foreach($value as $v_key=>$v_value){
					$s_str=$clothes_status_arr[$v_value];//获取单品的当前状态
					if(!isset($current_status[$s_str])){//当前SKU状态数组 不存在此状态时对其添加
						$current_status[$s_str]=array();
					}
					$current_status[$s_str][]=$v_value;//将单品添加到 SKU状态数组对应状态下
					$up_Related_clothes[]=$v_value;
					if(isset($clothes[$v_value])){
						$up_s_str=$clothes[$v_value];//获取单品修改状态
						if(!isset($up_status[$up_s_str])){//SKU修改状态数组 不存在此状态时对其添加
							$up_status[$up_s_str]=array();
						}
						$up_status[$up_s_str][]=$v_value;//将单品添加到 SKU修改状态数组对应状态下
					}
				}
				
				//显示单品
				if(isset($up_status[$Display_status_num])){//SKU修改状态数组里存在显示单品时 就将其第一个设为显示 其他设为不显示
					$Display_clothes[0]=$up_status[$Display_status_num][0];
				}
				//下架单品
				if(isset($up_status[$out_status_num])){//SKU修改状态数组存在下架状态
					$out_clothes=$up_status[$out_status_num];
				}
				//待售单品
				if(isset($up_status[$ForSale_status_num])){//SKU修改状态数组存在待售状态
					$ForSale_clothes=$up_status[$ForSale_status_num];
				}
				
				if(isset($current_status[$Display_status_num])){//当前SKU状态数组存在显示单品时
					$current_status[$Display_status_num]=Comm::array_diff_fast($current_status[$Display_status_num],array_merge($out_clothes,$ForSale_clothes));//去除原有上架修改为下架或待售的单品
					if(count($current_status[$Display_status_num])>0){
						if(isset($up_status[$Display_status_num])){
							$Display_intersect=Comm::array_intersect_fast($current_status[$Display_status_num],$up_status[$Display_status_num]);//获取其同时存在显示状态的单品
							if(count($Display_intersect)>0){//SKU修改状态数组需要显示单品与当前SKU状态数组显示单品存在相同单品 就显示其第一个相同单品
								$Display_clothes[0]=$Display_intersect[0];
							}
						}else{
							$Display_clothes[0]=$current_status[$Display_status_num][0];
						}
					}
				}
				
				//获取未设置的单品
				$dirr_Related=Comm::array_diff_fast($value,array_merge($ForSale_clothes,$out_clothes,$Display_clothes));
				foreach($dirr_Related as $dirr_value){
					if(isset($up_status[$Display_status_num]) && in_array($dirr_value,$up_status[$Display_status_num])){//暂未设置单品需设为上架，因已有单品上架 所以修改为上架不显示
						$no_Display_clothes[]=$dirr_value;
					}else if($clothes_status_arr[$dirr_value]==$Display_status_num){
						$no_Display_clothes[]=$dirr_value;
					}else if($clothes_status_arr[$dirr_value]==$ForSale_status_num){
						$ForSale_clothes[]=$dirr_value;
					}else if($clothes_status_arr[$dirr_value]==$out_status_num){
						$out_clothes[]=$dirr_value;
					}else{
						$no_Display_clothes[]=$dirr_value;
					}
				}
				//将SKU的下单品的按状态加入到数组
				$Logic_clothes_arr[$Display_status_num]=array_merge($Logic_clothes_arr[$Display_status_num],$Display_clothes);
				$Logic_clothes_arr[$no_Display_status_num]=array_merge($Logic_clothes_arr[$no_Display_status_num],$no_Display_clothes);
				$Logic_clothes_arr[$out_status_num]=array_merge($Logic_clothes_arr[$out_status_num],$out_clothes);
				$Logic_clothes_arr[$ForSale_status_num]=array_merge($Logic_clothes_arr[$ForSale_status_num],$ForSale_clothes);
			}
			//去除单品状态数组里的重复值和空值
			foreach($Logic_clothes_arr as $key=>$value){
				$value=array_filter($value);
				$Logic_clothes_arr[$key]=array_unique($value);
			}
			//单品不同穿状态设置
			foreach($Different_clothes_arr as $value){
				$out_c_arr=Comm::array_intersect_fast($value,$Logic_clothes_arr[$out_status_num]);//获取当前不同穿关联 是否做下架处理
				$ForSale_c_arr=Comm::array_intersect_fast($value,$Logic_clothes_arr[$ForSale_status_num]);//获取当前不同穿关联 是否做待售处理
				if(count($out_c_arr)>0){//当前单品状态为下架 那么其不同穿关联就做下架
					$Logic_clothes_arr[$out_status_num]=array_merge($Logic_clothes_arr[$out_status_num],$value);
				}else if(count($ForSale_c_arr)>0){//当前单品状态为待售 那么其不同穿关联就做待售
					$Logic_clothes_arr[$ForSale_status_num]=array_merge($Logic_clothes_arr[$ForSale_status_num],$value);
				}else{
					$Display_c_arr=Comm::array_intersect_fast($value,$Logic_clothes_arr[$Display_status_num]);//获取当前不同穿关联 是否做显示处理
					$Display_clothes_id_arr=array();
					$Display_c_arr_len=count($Display_c_arr);
					if($Display_c_arr_len>0){
						$Display_clothes_id_arr=array(array_shift($Display_c_arr));
					}
					//当前单品状态为上架不显示
					$no_Display_c_arr=Comm::array_diff_fast($value,array_merge($Display_clothes_id_arr,$Logic_clothes_arr[$ForSale_status_num],$Logic_clothes_arr[$out_status_num],$Logic_clothes_arr[$Display_status_num]));
					
					$Logic_clothes_arr[$no_Display_status_num]=array_merge($Logic_clothes_arr[$no_Display_status_num],$no_Display_c_arr);
					
					//上架单品数量大于1时 只取第一个做上架 其他做上架不显示
					if($Display_c_arr_len>1){
						$Logic_clothes_arr[$Display_status_num]=Comm::array_diff_fast($Logic_clothes_arr[$Display_status_num],$Display_c_arr);
					}
				}
				foreach($Related_clothes_arr as $R_key=>$R_value){
					if(count(Comm::array_intersect_fast($value,$R_value))>0){
						$R_value=array_merge($R_value,$value);
						$R_value=array_unique($R_value);
						$Related_clothes_arr[$R_key]=$R_value;
						break;
					}
				}
			}
			//检查关联单品 是否有上架单品存在
			/*foreach($Related_clothes_arr as $value){
				$no_Display_v_arr=Comm::array_intersect_fast($value,$Logic_clothes_arr[$no_Display_status_num]);
				$Display_v_arr=Comm::array_intersect_fast($value,$Logic_clothes_arr[$Display_status_num]);
				if(count($Display_v_arr)==0 && count($no_Display_v_arr)>0){//当前关联单品组合是没有上架单品 将不显示单品提取一个改为上架
					$s_vlue=array_shift($Display_c_arr);
					$Logic_clothes_arr[$Display_status_num][]=$s_vlue;
					unset($Logic_clothes_arr[$no_Display_status_num][array_search($s_vlue,$Logic_clothes_arr[$no_Display_status_num])]);
				}
			}
			print_r($Logic_clothes_arr);*/
			//修改单品的状态
			foreach($Logic_clothes_arr as $key=>$value){
				$value=array_filter($value);
				$value=array_unique($value);
				if(count($value)==0){
					continue;
				}
				Yii::app ()->db->createCommand ('update touch_clothes set status='.$key.' where clothesid in('.implode(',',$value).') and touchid='.$touchid)->execute();
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 设置衣服的不同穿 不同色
	**/
	function setClothesDifferentAndRelated($brandid,$clothesid,$brandnumber){
		try{
			$Different_data=self::beuDifferentSelectByClothesid2($clothesid);//根据衣服ID查找其不同穿法
			if(count($Different_data)>0){
				$arr = array ();
				$id=0;
				foreach ( $Different_data as $key => $value ) {
					if($key == 'id'){
						$id=$value;
					}else if (! empty ( $value ) && $value!=$clothesid) {
						$arr [] = $value;
					}
				}
				if(count($arr)<2){//当其去除当前单品后，不同穿没有其他单品 就将其关联关系删除
					self::beuDifferentDeleteByid($id);
				}else{
					$num=1;
					$ret_data_new=array();
					for($num=1;$num<=30;$num++){
						$ret_data_new['clothesid'.$num]=isset($arr[$num-1])?$arr[$num-1]:'';
					}
					beu_clothesdifferent::model ()->updateAll ( $ret_data_new, 'id=:textx', array (':textx' => $id ) );
				}
			}
			/*$Related_data=self::beuRelatedSelectByClothesid2($clothesid);//根据衣服id查找不同颜色完整数据
			if(count($Related_data)>0){
				$arr = array ();
				$id=0;
				foreach ( $Related_data as $key => $value ) {
					if($key == 'id'){
						$id=$value;
					}else if (! empty ( $value ) && $value!=$clothesid) {
						$arr [] = $value;
					}
				}
				if(count($arr)<2){//当其去除当前单品后，不同穿没有其他单品 就将其关联关系删除
					self::beuRelatedDeleteByid($id);
				}else{
					$num=1;
					$ret_data_new=array();
					for($num=1;$num<=30;$num++){
						$ret_data_new['clothesid'.$num]=isset($arr[$num-1])?$arr[$num-1]:'';
					}
					beu_clothesrelated::model ()->updateAll ( $ret_data_new, 'id=:textx', array (':textx' => $id ) );
				}
			}*/
			
			if(!empty($brandnumber)){//款号不为空的时候才自动绑定
				//查询品牌的款号规则
				$brand_code=Brand::brandSelectById($brandid);
				if(empty($brand_code) || !isset($brand_code[0]['code_start']) || (empty($brand_code[0]['code_start']) && empty($brand_code[0]['code_end']))){
					throw new Exception('查询款号规则错误！');
				}
				
				//根据品牌的款号规则截取款号字符段
				$barcode_str=substr($brandnumber,$brand_code[0]['code_start'],($brand_code[0]['code_end']-$brand_code[0]['code_start']));
				//查询此品牌下的衣服
				$ret_clothes=self::getclothesBybrandandCode($brandid,$barcode_str,isset($brand_code[0]['code_start'])?$brand_code[0]['code_start']:0,2);
				if($ret_clothes['status']==0){
					throw new Exception($ret_clothes['msg']);
				}
				$Different_arr=array();//不同穿数组
				$Related_arr=array();//不同色数组
				foreach($ret_clothes['data'] as $value){
					if($value['brandnumber']==$brandnumber){//添加不同穿数据
						$Different_arr[]=$value['id'];
					}
					//$Related_arr[$value['brandnumber']]=
				}
				if(count($Different_arr)>1){
					self::clothesDifferentUpdataById($clothesid,implode(',',$Different_arr));
				}
			}
		}catch(Exception $e){
			
		}
	}
}