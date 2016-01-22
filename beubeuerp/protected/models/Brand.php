<?php

/**
 * 品牌类
 * 与品牌相关的所有操作
 */
class Brand {
	private static $Int_max=99999999;
	/**
	 * beu_brand表的logoimgpath字段使用的暂存图片地址名
	 */
	private static $Img_Session_name='BrandImgSrc';
	
	/**
	 * 向beu_brand表添加新品牌数据
	 * @param $arr：Array 其内容如下
	 * name：string（255） （必填，品牌名）
		englishname：string（255） （必填，品牌英文名）
		logoimgpath：string（255） （必填，品牌图片）
		telephone：string（255） （可不填，品牌电话）
		address：string（255） （可不填，品牌地址）
		fax：string（255） （可不填，传真）
		website：string（255） (可不填，网站地址：http://dfa.com)
		companyname：string（255） （可不填，公司名）
		status：int （可不填，是否显示，默认为1。参数值也只能为1或2）
		createdate：string（10） （可不填，创建时间）
		code_start:款号规则开始位置
		code_end:款号规则结束位置
	 * @return 如果添加数据成功返回true
	 * 否则返回其错误信息
	 */
	public static function brandForAdd($arr = array()) {
		$insertid=0;
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$brand_biao = new beu_brand ();
			if (isset ( $arr ['name'] ) && isset ( $arr ['englishname'] )) {
				try {
					$name = trim ( $arr ['name'] );
					$englishname = trim ( $arr ['englishname'] );
					Comm::checkValue ( $name, Yii::t ( 'beu_brand', '品牌名' ), 0, 1, 255 );
					Comm::checkValue ( $englishname, Yii::t ( 'beu_brand', '品牌英文名' ), 0, 1, 255 );
					$brand_biao->name = $name;
					$brand_biao->englishname = $englishname;
					
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( $e->getMessage (), $e->getCode() );
				}
			} else {
				return false;
			}
			if (isset ( $arr ['logoimgpath'] )) { 
				try {
					$telephone = trim ( $arr ['logoimgpath'] );
					$brand_biao->telephone = '';
					if (! empty ( $telephone )) {
						Comm::checkValue ( $telephone, '', 0,0,255 );
						$brand_biao->logoimgpath = $telephone;
					}
				} catch ( BeubeuException $e ) {}
			}else{
				$img = uploadd::SessionGet(self::$Int_max,self::$Img_Session_name);//获取session里的暂存数据
				if($img)
				{
					$img=json_decode($img,true);
					try{
						$brand_biao->logoimgpath =$img['brand']['img'];
					}catch(Exception $e){}
				}
			}
			if (isset ( $arr ['id'] )) { //公司电话字段存在，并且长度不超过255
				try {
					$telephone = trim ( $arr ['id'] );
					if (! empty ( $telephone )) {
						Comm::checkValue ( $telephone, '', 1,1, self::$Int_max);
						$brand_biao->id = $telephone;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['telephone'] )) { //公司电话字段存在，并且长度不超过255
				try {
					$telephone = trim ( $arr ['telephone'] );
					$brand_biao->telephone = '';
					if (! empty ( $telephone )) {
						Comm::phoneToSingle ( $telephone, Yii::t ( 'beu_brand', '电话号码' ), true );
						$brand_biao->telephone = $telephone;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['address'] )) { //公司地址字段存在，并且长度不超过255
				try {
					$address = trim ( $arr ['address'] );
					Comm::checkValue ( $address, Yii::t ( 'beu_brand', '公司地址' ), 0, 0, 255 );
					$brand_biao->address = $address;
				} catch ( BeubeuException $e ) {}
			}
			if(!isset($arr['code_start']) || intval($arr['code_start'])!=$arr['code_start'] ){
				$brand_biao->code_start=0;
			}else{
				$brand_biao->code_start=$arr['code_start'];
			}
			if(!isset($arr['code_end']) || intval($arr['code_end'])!=$arr['code_end'] ){
				$brand_biao->code_end=0;
			}else{
				$brand_biao->code_end=$arr['code_end'];
			}
			if (isset ( $arr ['fax'] )) { //传真字段存在，并且长度不超过255
				try {
					$fax = trim ( $arr ['fax'] );
					$brand_biao->fax = '';
					if (! empty ( $fax )) {
						Comm::phoneToSingle ( $fax, Yii::t ( 'beu_brand', '传真' ), true );
						$brand_biao->fax = $fax;
					}
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['website'] )) { //网站地址字段存在，并且长度不超过255
				try {
					$website = trim ( $arr ['website'] );
					Comm::checkValue ( $website, Yii::t ( 'beu_brand', '网站地址' ), 0, 0, 255 );
					$brand_biao->website = $website;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['companyname'] )) { //公司名字段存在，并且长度不超过255
				try {
					$companyname = trim ( $arr ['companyname'] );
					Comm::checkValue ( $companyname, Yii::t ( 'beu_brand', '公司名称' ), 0, 0, 255 );
					$brand_biao->companyname = $companyname;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['status'] ) && ! empty ( $arr ['status'] ) && trim ( $arr ['status'] ) == 11) { //显示状态字段存在，并且为2表示不显示
				$brand_biao->status = 11; //不显示
			} else {
				$brand_biao->status = 10; //显示
			}
			if (isset ( $arr ['angle'] ) && ! empty ( $arr ['angle'] ) && intval($arr ['angle'])==$arr ['angle'] && $arr ['angle']>0) { //衣服支持的角度
				$brand_biao->angle = $arr ['angle']; 
			} else {
				$brand_biao->angle = 1; //只有正面
			}
			if (isset ( $arr ['model'] )) { //简版模特
				$brand_biao->model_type = $arr ['model']; 
			}
			$brand_biao->createdate = date("Y-m-d H:i:s", time ());
			try {
				$count = $brand_biao->insert ();
				$insertid=Yii::app()->db->getLastInsertID();
				if ($count > 0) {
					Yii::app()->cache->delete(CacheName::getCacheName('brand_name_list_all'));//清除品牌列表
				} else {
					throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
				}
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
			}
		
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		return $insertid;
	}
	
	/**
	 * 根据品牌id将其品牌删除，此参数支持将多个品牌id用‘,’分隔拼接为字符串
	 * @param $brandid：string 品牌id字符串，默认为null
	 * @return 只要有一个品牌操作成功就返回true，反之返回false
	 */
	public static function brandDeleteById($brandid = null) {
		if (empty ( $brandid )) { //检查字符串是否为空
			throw new BeubeuException ( Yii::t ( 'public', '品牌号' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		self::brandOperateById ( $brandid, 12 );
	}
	
	/**
	 * 根据品牌id将其品牌下架，此参数支持将多个品牌id用‘,’分隔拼接为字符串
	 * @param $brandid：string 品牌id字符串，默认为null
	 * @return 只要有一个品牌操作成功就返回true，反之返回false
	 */
	public static function brandShelvesById($brandid = null) {
		if (empty ( $brandid )) { //检查字符串是否为空
			throw new BeubeuException ( Yii::t ( 'public', '品牌号' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		self::brandOperateById ( $brandid, 11 );
	}
	
	/**
	 * 根据品牌id将其品牌上架，此参数支持将多个品牌id用‘,’分隔拼接为字符串
	 * @param $brandid：string 品牌id字符串，默认为null
	 * @return 只要有一个品牌操作成功就返回true，反之返回false
	 */
	public static function brandShelfById($brandid = null) {
		if (empty ( $brandid )) { //检查字符串是否为空
			throw new BeubeuException ( Yii::t ( 'public', '品牌号' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		self::brandOperateById ( $brandid, 10 );
	}
	
	/**
	 * 根据品牌id将其品牌(删除、下架或上架)，此参数支持将多个品牌id用‘,’分隔拼接为字符串
	 * @param $brandid：string 品牌id字符串，默认为null
	 * @param $type：int 需要执行的操作，默认为12 删除，11为下架，10为上架。如果都不是就不做任何操作
	 * @return 只要有一个品牌操作成功就返回true，反之返回false
	 */
	private static function brandOperateById(&$brandid = null, $type = 12) {
		if (empty ( $brandid )) { //检查字符串是否为空
			throw new BeubeuException ( Yii::t ( 'public', '品牌号' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		if ($type != 10 && $type != 12 && $type != 11) {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '状态' ) . Yii::t ( 'public', '格式不正确' ), BeubeuException::FIELD_FORMAT );
		}
		$brandid = trim ( $brandid ); //清除前后空格
		$brandid_arr = explode ( ',', $brandid );
		if (count ( $brandid_arr ) > 1) {
			$brandid_arr = array_unique ( $brandid_arr ); //去除数组中重复的值
			sort ( $brandid_arr ); //将数组按照值的大小顺序排列
		}
		foreach ( $brandid_arr as $key => $value ) {
			try {
				Comm::checkValue ( $value, Yii::t ( 'public', '品牌号' ), 1, 1 );
			} catch ( BeubeuException $e ) {
				unset ( $brandid_arr [$key] );
			}
		}
		if (count ( $brandid_arr ) > 0) {
			try {
				beu_brand::model ()->updateByPk ( $brandid_arr, array ('status' => $type ) );
				Yii::app()->cache->delete(CacheName::getCacheName('brand_name_list_all'));//清除品牌列表
				return true;
			} catch ( Exception $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
			}
		}
	}
	
	/**
	 * 根据品牌ID对beu_brand表中数据的数据进行修改
	 * @param $id：int 品牌id 默认0
	 * @param $arr：Array 其内容如下
	 * logoimgpath：string（255） （可不填，品牌图片）
		telephone：string（255） （可不填，品牌电话）
		address：string（255） （可不填，品牌地址）
		fax：string（255） （可不填，传真）
		website：string（255） (可不填，网站地址：http://dfa.com)
		companyname：string（255） （可不填，公司名）
		status：int （可不填，是否显示，默认为1。参数值也只能为1或2）
		createdate：string（10） （可不填，创建时间）
		code_start:款号规则开始位置
		code_end:款号规则结束位置
	 * @return 如果修改数据成功返回true
	 * 否则返回false
	 */
	public static function brandUpdateById($id = 0, $arr = array()) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'public', '品牌号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$updata_arr = array ();
			if (isset ( $arr ['englishname'] )) {
				try {
					$englishname = trim ( $arr ['englishname'] );
					Comm::checkValue ( $englishname, Yii::t ( 'beu_brand', '品牌英文名' ), 0, 1, 255 );
					$updata_arr ['englishname'] = $englishname;
				} catch ( BeubeuException $e ) {
				}
			}
			if (isset ( $arr ['name'] )) {
				try {
					$name = trim ( $arr ['name'] );
					Comm::checkValue ( $name, Yii::t ( 'beu_brand', '品牌名' ), 0, 1, 255 );
					$updata_arr ['name'] = $name;
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( $e->getMessage (), $e->getCode() );
				}
			}
			if (isset ( $arr ['logoimgpath'] )) {
				try {
					$logoimgpath = trim ( $arr ['logoimgpath'] );
					Comm::checkValue ( $logoimgpath, Yii::t ( 'beu_brand', '公司地址' ), 0, 0, 255 );
					$updata_arr ['logoimgpath'] = $logoimgpath;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['telephone'] )) { //公司电话字段存在，并且长度不超过255
				try {
					$telephone = trim ( $arr ['telephone'] );
					Comm::phoneToSingle ( $telephone, Yii::t ( 'beu_brand', '电话号码' ) );
					$updata_arr ['telephone'] = $telephone;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['address'] )) { //公司地址字段存在，并且长度不超过255
				try {
					$address = trim ( $arr ['address'] );
					Comm::checkValue ( $address, Yii::t ( 'beu_brand', '公司地址' ), 0, 0, 255 );
					$updata_arr ['address'] = $address;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['fax'] )) { //传真字段存在，并且长度不超过255
				try {
					$fax = trim ( $arr ['fax'] );
					Comm::phoneToSingle ( $fax, Yii::t ( 'beu_brand', '传真' ) );
					$updata_arr ['fax'] = $fax;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['website'] )) { //网站地址字段存在，并且长度不超过255
				try {
					$website = trim ( $arr ['website'] );
					Comm::checkValue ( $website, Yii::t ( 'beu_brand', '网站地址' ), 0, 0, 255 );
					$updata_arr ['website'] = $website;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['companyname'] ) && ! empty ( $arr ['companyname'] ) && strlen ( $arr ['companyname'] ) <= 255) { //公司名字段存在，并且长度不超过255
				try {
					$companyname = trim ( $arr ['companyname'] );
					Comm::checkValue ( $companyname, Yii::t ( 'beu_brand', '公司名称' ), 0, 0, 255 );
					$updata_arr ['companyname'] = $companyname;
				} catch ( BeubeuException $e ) {}
			}
			if(!isset($arr['code_start']) || intval($arr['code_start'])!=$arr['code_start'] ){
				$updata_arr['code_start']=0;
			}else{
				$updata_arr['code_start']=$arr['code_start'];
			}
			if(!isset($arr['code_end']) || intval($arr['code_end'])!=$arr['code_end'] ){
				$updata_arr['code_end']=0;
			}else{
				$updata_arr['code_end']=$arr['code_end'];
			}
			if (isset ( $arr ['status'] ) && ! empty ( $arr ['status'] ) && trim ( $arr ['status'] ) == 11) { //显示状态字段存在，并且为2表示不显示
				$updata_arr ['status'] = 11; //不显示
			} else if (isset ( $arr ['status'] ) && ! empty ( $arr ['status'] ) && trim ( $arr ['status'] ) == 10) {
				$updata_arr ['status'] = 10; //显示
			}
			if (isset ( $arr ['angle'] ) && ! empty ( $arr ['angle'] ) && intval($arr ['angle'])==$arr ['angle'] && $arr ['angle']>0) { //衣服支持的角度
				$updata_arr ['angle'] = $arr ['angle']; 
			} 
			if (isset ( $arr ['model'] )) { //衣服支持的角度
				$updata_arr ['model_type'] = $arr ['model']; 
			}
			if (isset ( $arr ['createdate'] ) && ! empty ( $arr ['createdate'] )) { //时间字段存在并格式正确
				try {
					$createdate = trim ( $arr ['createdate'] );
					Comm::dataSingle ( $createdate, Yii::t ( 'beu_brand', '添加时间' ), false );
					$updata_arr ['createdate'] = $createdate;
				} catch ( BeubeuException $e ) {}
			}
			$img=self::brandImgUp($id);
			if($img)
			{
				$img=json_decode($img,true);
				try{
					$updata_arr ['logoimgpath'] =$img['brand']['img'];
				}catch (Exception $e){}
			}
			try {
				beu_brand::model ()->updateAll ( $updata_arr, 'id=:textx', array (':textx' => $id ) );
				Yii::app()->cache->delete(CacheName::getCacheName('brand_name_list_all'));//清除品牌列表
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
			}
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
		return true;
	}
	
	/**
	 * 根据条件查询所有品牌信息
	 * @param $touchid：int 可不填,触摸屏id 默认0
	 * @param $key:string 可不填,搜索条件 （品牌名，品牌id）
	 * @param $page:int 可不填,当前页
	 * @param $pagecount:int 可不填,数量
	 * @param $modeltype：int （可不填，模特类型，默认0表示所有模特）
	 * @param $satus:int (可不填)品牌状态，默认1只查找显示状态的品牌，2查找不显示状态的品牌，3查找显示与不显示状态的品牌，4查找删除状态的品牌，5查找所有不区分状态，其他值会被改为1
	 
	 * @return 如果查询数据成功返回结果集array ('data' => $newstypelist, 'count' => $con, 'pageSize' => $pagecount, 'page' => $page )
	 * 否则返回false
	 */
	public static function brandSelectForAll($touchid = 0, $key = '', $page = 1, $pagecount = 20, $modeltype = 0, $satus = 1) {
		$touchid = trim ( $touchid );
		$modeltype = trim ( $modeltype );
		$search_arr=$key;
		if(is_array($key)){
			$key=$search_arr['seach_str'];
		}
		$key = trim ( $key );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 0 );
			Comm::checkValue ( $modeltype, Yii::t ( 'public', '模特类型' ), 1, 0 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		if ($satus != 1 && $satus != 2 && $satus != 3 && $satus != 4 && $satus != 5) {
			$satus = 1;
		}
		try {
			Comm::checkValue ( $page, '', 1, 1 );
		} catch ( BeubeuException $e ) {
			$page = 1;
		}
		try {
			Comm::checkValue ( $pagecount, '', 1, 1 );
		} catch ( BeubeuException $e ) {
			$pagecount = 20;
		}
		
		try {
			$w_s = '';
			$w_s_arr = array ();
			if ($satus == 1) {
				$w_s = 'status=:sta';
				$w_s_arr = array (':sta' => 10 );//上架
			} else if ($satus == 2) {
				$w_s = 'status=:sta';
				$w_s_arr = array (':sta' => 11 );//下架
			} else if ($satus == 3) {
				$w_s = '(status=:sta or status=:sta2)';
				$w_s_arr = array (':sta' => 11, ':sta2' => 10 );//上下架
			} else if ($satus == 4) {
				$w_s = 'status=:sta';
				$w_s_arr = array (':sta' => 12 );//删除
			} else if ($satus == 5) {
			}
			if (! empty ( $key )) {
				if (! empty ( $w_s )) {
					$w_s .= ' and ';
				}
				$w_s .= '( name like :name or englishname like :englishname';
				$w_s_arr [':name'] = "%$key%";
				$w_s_arr [':englishname'] = "%$key%";
				try {
					if(!is_array($search_arr) || !isset($search_arr['seach_id'])){
						Comm::checkValue ( $key, '', 1, 1 );
						$w_s .= ' or id=\':id\'';
						$w_s_arr [':id'] = $key;
					}else{
						$seach_id=0;
						if(!empty($search_arr['seach_id'])){
							$seach_id=$search_arr['seach_id'];
						}
						$w_s .= ' or id in('.$seach_id.')';
					}
				} catch ( Exception $e ) {
				}
				$w_s .= ')';
			}
			if ($touchid != 0) {
				//获取触摸屏下的品牌
				
				$arr = Touch::touchBrand ( $touchid );
				if ($arr['status']==1) {
					$touch_data = array_keys ( $arr['data'] ); //将数组里的key值获取出来存为数组
				}
				if (count ( $touch_data ) > 0) {
					if (! empty ( $w_s )) {
						$w_s .= ' and ';
					}
					$w_s .= 'id in(:id2)';
					$w_s_arr [':id2'] = $touch_data;
				
				} else {
					throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
				}
			}
			$sele_zd = 'id,name,englishname,status,createdate,code_start,code_end,model_type';
			$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( 'beu_brand' )->where ( $w_s, $w_s_arr )->order ( 'id desc' );
			//获取总个数
			$con = $sql->queryAll ();
			$newstypelist = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( 'beu_brand' )->where ( $w_s, $w_s_arr )->order ( 'id desc' )->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
			$con = count ( $con );
			$criteria = new CDbCriteria();
			$pages=new CPagination($con);
			$pages->pageSize=$pagecount;
		    $pages->applyLimit($criteria);
			if (! empty ( $newstypelist )) {
				return array ('data' => $newstypelist, 'count' => $con, 'pageSize' => $pagecount, 'page' => $pages );
			} else {
				throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
			}
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据条件查询所有显示品牌列表（品牌id,名，英文名）
	 * @param $touchid：int （可不填，触摸屏id 默认0）
	 * @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	 */
	public static function brandSelectForAllName($touchid = 0) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			$touch_data = array ();
			$touchid = trim ( $touchid );
			//获取触摸屏下的品牌
			if(!empty($touchid)){//当搭配屏ID不为空
				$arr=Touch::touchBrand($touchid);
				if($arr['status']==0){
					throw new Exception ($arr['msg']);
				}
				$touch_data=array_keys($arr['data']);
				if(!in_array(Yii::app()->params['cocoa_brandid'],$touch_data)){
					$touch_data[]=Yii::app()->params['cocoa_brandid'];
				}
			}
			
			$brand_all_ret=self::brandSelectAll();
			if($brand_all_ret['status']==0){
				throw new Exception ($brand_all_ret['msg']);
			}
			if (count ( $touch_data ) > 0) {
				foreach($brand_all_ret['data'] as $key=>$value){
					if(!in_array($value['id'],$touch_data)){
						unset($brand_all_ret['data'][$key]);
					}
				}
			}
			if (count ( $brand_all_ret['data'] ) == 0) {
				throw new Exception ('未找到品牌！');
			} 
			$ret['data']=$brand_all_ret['data'];
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	 * 查询所有品牌
	 * @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	 */
	static public function brandSelectAll()
	{
		$ret=array('status'=>0,'msg'=>'');
		try{
			$cache=Yii::app()->cache->get(CacheName::getCacheName('brand_name_list_all'));//获取所有品牌名列表缓存
			if($cache===false){
				//查询可显示的品牌 不显示及其删除品牌不查询
				$brandidarr = Yii::app ()->db->createCommand ()
				->select ( 'id,name,englishname' )
				->from ( "beu_brand" )
				->where ( 'status=10' )
				->queryAll ();
				if(count($brandidarr)==0){
					throw new Exception('品牌列表为空！');
				}
				$ret['data']=$brandidarr;
				$ret['status']=1;
				Yii::app()->cache->set(CacheName::getCacheName('brand_name_list_all') ,$ret,0);//设置所有品牌名列表缓存 永不过期
			}else{
				$ret=$cache;
			}
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	 * 根据条件查询品牌详细信息
	 * @param $brandid：int 品牌id 默认0
	 * @return 如果查询数据成功返回结果集
	 * 否则返回false
	 */
	public static function brandSelectById($brandid = 0) {
		$brandid = trim ( $brandid );
		try {
			Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$sql=Yii::app()->db->createCommand()->select ('*')->from('beu_brand' )->where('id='.$brandid);
			$date = $sql->queryAll ();
			return $date;
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 检查品牌名是否符合要求
	 * @param $id 品牌id
	 * @param $name 品牌名
	 * @return 符合规范并且在数据库里不存在就返回true，否则返回false
	 */
	 static function brandNameInspect($id=0,$name = null) {
		$name = trim ( $name );
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'public', '品牌号' ), 1, 0 );
			Comm::checkValue ( $name, Yii::t ( 'beu_brand', '品牌名' ),0, 1, 255 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode() );
		}
		try {
			$w_s='name=:name';
			$w_arr=array(':name' => $name);
			if($id>0)
			{
				$w_s.=' and id<>:id';
				$w_arr[':id']=$id;
			}
			$w_s .= ' and  status<>12';//验证名称 不包含删除品牌
			
			$date = beu_brand::model ()->find ( $w_s, $w_arr );
			if (empty ( $date )) {
				return true;
			} else {
				return false;
			}
		} catch ( Exception $e ) {
			throw new BeubeuException ( Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 上传图片
	 * @param $id 品牌id
	 * @param $name 上传控件名
	 * @param $address 图片上传后暂存地址：指的是数组结构。例：array('dd'=>array('kk'=>'da'))，要将数据保存在二维数组kk下的写法 ('dd/kk')
	 * @return 返回图片路径
	 */
	public static function imgUpload($id=0,$name='',$address='')
	{
		$id = trim ( $id );
		$name = trim ( $name );
		$address = trim ( $address );
		try {
			Comm::checkValue ( $id, Yii::t ( 'public', '品牌号' ), 1, 1 );
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
	 * 清理图片Session
	 * @param $id 品牌id
	 */
	public static function imgSessionDelete($id=0)
	{
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'public', '品牌号' ), 1, 1 );
		} catch ( Exception $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		uploadd::SessionDelete($id,self::$Img_Session_name);
	}
	
	/**
	 * 图片上传资料整理（与数据库对比后返回的更新到数据库的json数据）
	 * @param $id:int 品牌id	
	 * @return 成功返回整理后的json数据，否则返回false
	 */
	private static function brandImgUp($id = 0) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, 'ID', 1, 1 );
		} catch ( Exception $e ) {
			return false;
		}
		$arr = uploadd::SessionGet($id,self::$Img_Session_name);//获取session里的暂存数据
		if ($arr) {
			$arr = trim ( $arr );
			if (! empty ( $arr )) {
				$arr = json_decode ( $arr, true );
				if (count ( $arr ) > 0) {
					try {
						$data = Yii::app ()->db->createCommand ()->select ( 'logoimgpath' )->from ( 'beu_brand' )->where ( 'id=:id', array (':id' => $id ) )->queryAll ();
						if($data[0]['logoimgpath']!='')
						{
							Comm::arrayDelete($data[0]['logoimgpath']);//删除需要被替换的文件
						}
					} catch ( Exception $e ) {
					}
					return json_encode ( $arr );
				}
			}
		}
		return false;
	}
	
	/**
	 * 获取品牌对接编号
	 * @parm sel 可选 需要的品牌对接编号信息
	 * @parm search 可选 数组 搜索条件 及其值
	 * @parm limit 可选 分页 格式：页码-每页数量
	 * @parm order 可选 排序
	 */
	public static function getbranddocking($sel='*',$search=array(),$limit='',$order='id desc'){
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
				->from ( 'beu_branddocking' )	
				->where ($sel_where);
			if(!empty($limit)){
				$limit=explode('-',$limit);
				if(count($limit)==2 && is_numeric($limit[0]) && is_numeric($limit[1])){
					$data->limit ( intval($limit[1]), (intval($limit[0]) - 1) * intval($limit[1]) );
				}
			}
			$data=$data->order($order)
				->queryAll ();
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
	* 修改品牌对接编号信息
	* @parm w_p where条件
	* @parm parm 数组 修改内容
	**/
	static function upbranddocking($parm,$w_p=''){
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
			Yii::app ()->db->createCommand ('update beu_branddocking set '.$sel_where.$w_p)->execute();
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 添加品牌对接编号信息
	* @parm parm 数组 添加信息
	**/
	static function insertbranddocking($parm){
		$ret=array('status'=>0,'msg'=>'');
		try{
			Yii::app ()->db->createCommand ('insert into beu_branddocking('.implode(',',array_keys($parm)).') values(\''.implode('\',\'',$parm).'\')')->execute();
			$insertid=Yii::app()->db->getLastInsertID();
			$ret['status']=1;
			$ret['data']=$insertid;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
}