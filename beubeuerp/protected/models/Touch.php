<?php
/**
 * 触摸屏类
 */
class Touch{
	
	/**
	 * 根据触摸屏ID查询一条touch_config表数据
	 * @param $touchid:int 触摸屏ID 默认为0
	 * @return 查询成功返回结果集
	 * 查询失败返回false
	 */
	public static function touchSelectById($touchid=0)
	{
		$ret=array('status'=>0,'msg'=>'');
		try {
			$touchid = trim ( $touchid );
			if(empty($touchid) || !is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误!');
			}
			$newlist=Yii::app()->cache->get(CacheName::getCacheName('touch_info') .$touchid);//获取搭配屏的详细信息缓存
		
			if($newlist===false){
				$data = Yii::app ()->db->createCommand ()
					->select ( '*' )
					->from ( 'touch_config' )
					->where('id='.$touchid)
					->queryRow ();
				if(empty($data)){
					throw new Exception('未找到相应的搭配屏！');
				}
				$ret['data']=$data;
				$ret['status']=1;
				Yii::app()->cache->set(CacheName::getCacheName('touch_info') .$touchid,$ret,0);//设置搭配屏的详细信息缓存 永不过期
			}else{
				$ret=$newlist;
			}
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	 * 获取触摸屏里的品牌 
	 * @param $touchid
	 * @return 成功返回品牌数组，失败返回false
	 */
	public static function touchBrand($touchid = 0) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			$touchid = trim ( $touchid );
			if(empty($touchid) || !is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<=0){
				throw new Exception('搭配屏编号有误！');
			}
			$row = self::touchSelectById ( $touchid );
			if($row['status']==0){
				throw new Exception($row['msg']);
			}
			$row=$row['data'];
			$branid=0;
			if(empty($row ['brandid']) && empty ( $row ['touchbrand'] )){
				throw new Exception('搭配屏未绑定品牌！');
			}
			if(!empty($row ['brandid'])){
				$branid=$row ['brandid'];
			}
			if (! empty ( $row ['touchbrand'] )) {
				$arr = json_decode ( $row ['touchbrand'], true );
				$is_bool=false;
				foreach($arr as $key=>$value){
					if($key==$branid){
						$arr[$key]=10000;
						$is_bool=true;
						break;
					}
				}
				if(!$is_bool){
					$arr[$branid]=10000;
				}
				arsort ( $arr );
			}else if($branid){
				$arr=array($branid=>10000);
			}
			$ret['data']=$arr;
			$ret['status']=1;
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	 * 查询所有触摸屏信息
	 * @param $page:int 当前页
	 * @param $pagecount:int 数量
	 * @param $key (可不添)查询条件，可以为id也可以为触摸屏名
	 * @return 查询成功返回结果集
	 * 查询失败返回false
	 */
	public function touchSelectForAll($page=1,$pagecount=20,$key = '') {
		try {
			Comm::checkValue ( $page, '', 1, 1 );
		} catch ( BeubeuException $e ) {
			$page=1;
		}
		try {
			Comm::checkValue ( $pagecount, '', 1, 1 );
		} catch ( BeubeuException $e ) {
			$pagecount=20;
		}
		$key = trim ( $key );
		$w_s = '';
		$w_s_arr = array ();
		if (! empty ( $key )) {
			$w_s = "name like :t_name";
			$w_s_arr [':t_name'] = "%$key%";
			try {
				Comm::checkValue ( $key, Yii::t ( 'public', '触摸屏号' ), 1, 1 );
				$w_s .= ' or id=:t_id';
				$w_s_arr [':t_id'] = $key;
			} catch ( BeubeuException $e ) {
			}
		}
		try {
			$sele_zd = 'touch_config.id,touch_config.name,touch_config.brandid,touch_config.lock,touch_config.enddate,beu_brand.name as brandname';
			$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( 'touch_config' )->join ( 'beu_brand', 'beu_brand.id=touch_config.brandid' )->where ( $w_s, $w_s_arr )->order ( 'touch_config.id desc' );
			$newstypelist = $sql->limit ( $pagecount, ($page - 1) * $pagecount )->queryAll ();
			//获取总个数
			$con = $sql->queryAll ();
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
			throw new BeubeuException(Yii::t ( 'public', '查询数据失败' ), BeubeuException::SQL_SELECT_ERR);
		}
	}
	
	/**
	 * 查询所有触摸屏名、id
	 * @return 查询成功返回结果集
	 * 查询失败返回false
	 */
	static  public function touchSelectForAllName() {
		$ret=array('status'=>0,'msg'=>'');
		try {
			$cache=Yii::app()->cache->get(CacheName::getCacheName('touch_name_list'));//获取搭配屏名称列表缓存
			if($cache===false){
				$sele_zd = 'touch_config.id,touch_config.name';
				$sql = Yii::app ()->db->createCommand ()->select ( $sele_zd )->from ( 'touch_config' )->where('touch_config.lock<>1')->order ( 'touch_config.id desc' );
				 
				$newstypelist = $sql->queryAll ();
				if(count($newstypelist)==0){
					throw new Exception ( '查询触摸屏名称列表失败！' );
				}
				$ret['data']=$newstypelist;
				$ret['status']=1;
				Yii::app()->cache->set(CacheName::getCacheName('touch_name_list'),$ret,0);//设置搭配屏名称列表缓存
			}else{
				$ret=$cache;
			}
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	 * 添加触摸屏
	 * @param $arr：Array 其内容如下
	 * name：string（50） （必填，触摸屏名）
		brandid：int（4） （必填，品牌ID）
		isshow：text （必填，是否显示对应的功能.存放的是二维数组.其中第一维为ipad,touch,brand分别表示IPAD,触摸屏和品牌专区.第二维里面对应的ID为b_category表的style为13的.1为显示）
		touchbrand：string（1000）（必填，触摸屏下的品牌  arr[brandid]=order 0-100）
		openkey：string（1000）（可不填，打开触摸屏密钥）
		description：string（5000） （可不填，试衣间简介）
		headtail：string（1000） （可不填，页头,页尾 array[head][touch] 或 array[tail][touch]）
		enddate：date （可不填，到期时间）
		lock：int（4） （可不填，锁定）
		apikey：string（100） （可不填，同步密钥）
	 * @return 如果添加数据成功返回true
	 * 否则返回其错误信息
	 */
	public function touchForAdd($arr=array()){
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$config_biao = new touch_config ();
			if (isset ( $arr ['name'] ) && isset ( $arr ['brandid'] ) && isset ( $arr ['isshow'] )) {
				try {
					$name = trim ( $arr ['name'] );
					$brandid = trim ( $arr ['brandid'] );
					$isshow = trim ( $arr ['isshow'] );
					Comm::checkValue ( $name, Yii::t ( 'touch_config', '触摸屏名' ), 0, 1,50 );
					Comm::checkValue ( $brandid, Yii::t ( 'public', '品牌号' ), 1, 1 );
					Comm::checkValue ( $isshow, Yii::t ( 'touch_config', '对应的功能' ), 0, 1 );
					$config_biao->name = $name;
					$config_biao->brandid = $brandid;
					$config_biao->isshow = $isshow;
				} catch ( BeubeuException $e ) {
					throw new BeubeuException ( $e->getMessage (), $e->getCode() );
				}
			}
			echo 'll';
			if (isset ( $arr ['id'] )) {
				try {
					$openkey = trim ( $arr ['id'] );
					Comm::checkValue ( $openkey, '',1,1,99999999 );
					$config_biao->id = $openkey;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['openkey'] )) {
				try {
					$openkey = trim ( $arr ['openkey'] );
					Comm::checkValue ( $openkey, Yii::t ( 'touch_config', '触摸屏密钥' ),0,1,1000 );
					$config_biao->openkey = $openkey;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['description'] )) {
			try {
					$description = trim ( $arr ['description'] );
					Comm::checkValue ( $description, Yii::t ( 'touch_config', '试衣间简介' ),0,1,5000 );
					$config_biao->description = $description;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['headtail'] )) {
			try {
					$headtail = trim ( $arr ['headtail'] );
					Comm::checkValue ( $headtail, Yii::t ( 'touch_config', '页头,页尾' ),0,1,1000 );
					$config_biao->headtail = $headtail;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['enddate'] )) {
			try {
					$enddate = Comm::dataSingle ( $arr ['enddate'],Yii::t ( 'touch_config', '到期时间' ) );
					$config_biao->enddate = $enddate;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['lock'] )) {
			try {
					$lock = trim ( $arr ['lock'] );
					Comm::checkValue ( $lock, Yii::t ( 'touch_config', '锁定' ),1,1,1000 );
					$config_biao->lock = $lock;
				} catch ( BeubeuException $e ) {}
			}
			if (isset ( $arr ['apikey'] )) {
			try {
					$apikey = trim ( $arr ['apikey'] );
					Comm::checkValue ( $apikey, Yii::t ( 'touch_config', '同步密钥' ),0,1,100 );
					$config_biao->apikey = $apikey;
				} catch ( BeubeuException $e ) {}
			}
			try {
				$count = $config_biao->insert ();
				if ($count > 0) {
				} else {
					throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
				}
			} catch ( Exception $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '插入数据失败' ), BeubeuException::SQL_INSERT_ERR );
			}
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
	}
	
	/**
	 * 修改触摸屏
	 * @param $id：int 触摸屏ID 默认0
	 * @param $arr：Array 其内容如下
	 * name：string（50） （可不填，触摸屏名）
		brandid：int（4） （可不填，品牌ID）
		isshow：text （可不填，是否显示对应的功能.存放的是二维数组.其中第一维为ipad,touch,brand分别表示IPAD,触摸屏和品牌专区.第二维里面对应的ID为b_category表的style为13的.1为显示）
		touchbrand：string（1000）（可不填，触摸屏下的品牌  arr[brandid]=order 0-100）
		openkey：string（1000）（可不填，打开触摸屏密钥）
		description：string（5000） （可不填，试衣间简介）
		headtail：string（1000） （可不填，页头,页尾 array[head][touch] 或 array[tail][touch]）
		enddate：date （可不填，到期时间）
		lock：int（4） （可不填，锁定）
		apikey：string（100） （可不填，同步密钥）
	 * @return 如果添加数据成功返回true
	 * 否则返回其错误信息
	 */
	public function touchUpdateById($id = 0, $arr = array()) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'public', '触摸屏号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		if (count ( $arr ) > 0 && gettype($arr)=='array') {
			$updata_arr = array ();
			if (isset ( $arr ['name'] )) {
				$str_param = trim ( $arr ['name'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '触摸屏名' ), 0, 0, 50 );
					$updata_arr ['name'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['brandid'] )) {
				$str_param = trim ( $arr ['brandid'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'public', '品牌号' ), 1, 1, 50000 );
					$updata_arr ['brandid'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['touchbrand'] )) {
				$str_param = trim ( $arr ['touchbrand'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '触摸屏下的品牌' ), 0, 1, 1000 );
					$updata_arr ['touchbrand'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['isshow'] )) {
				$str_param = trim ( $arr ['isshow'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '对应的功能' ), 0, 1, 1000 );
					$updata_arr ['isshow'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['openkey'] )) {
				$str_param = trim ( $arr ['openkey'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '触摸屏密钥' ), 0, 1, 1000 );
					$updata_arr ['openkey'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['description'] )) {
				$str_param = trim ( $arr ['description'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '试衣间简介' ), 0, 1, 5000 );
					$updata_arr ['description'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['headtail'] )) {
				$str_param = trim ( $arr ['headtail'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '页头,页尾' ), 0, 1, 1000 );
					$updata_arr ['headtail'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['enddate'] )) {
				$str_param = trim ( $arr ['enddate'] );
				try {
					Comm::dataSingle ( $str_param, Yii::t ( 'touch_config', '到期时间' ) );
					$updata_arr ['enddate'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['lock'] )) {
				$str_param = trim ( $arr ['lock'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '锁定' ), 1, 1, 1000 );
					$updata_arr ['lock'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			if (isset ( $arr ['apikey'] )) {
				$str_param = trim ( $arr ['apikey'] );
				try {
					Comm::checkValue ( $str_param, Yii::t ( 'touch_config', '同步密钥' ), 0, 1, 100 );
					$updata_arr ['apikey'] = $str_param;
				} catch ( Exception $e ) {
				}
			}
			try {
				touch_config::model ()->updateByPk ( $id, $updata_arr );
				return true;
			} catch ( Exception $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
			}
		} else {
			throw new BeubeuException ( Yii::t ( 'beu_brand', '数据' ) . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
		}
	}
	
	/**
	 * 根据触摸屏id对其下的touchbrand字段修改
	 * @param $touchid 触摸屏id
	 * @param $arr 品牌id数组 array('品牌id'=>排序)
	 * @param $up_bool 是否修改排序,默认true修改
	 */
	public static function touchUpdateByIdBrand($touchid, $arr = array(), $up_bool = true) {
		$touchid = trim ( $touchid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		if ($up_bool != true) {
			$up_bool = false;
		}
		if (count ( $arr ) > 0 && gettype ( $arr ) == 'array') {
			if (! $up_bool) {
				$brandID_arr = self::touchBrand ( $touchid );
				if ($brandID_arr) {
					$arr_jj = array_intersect_key ( $arr, $brandID_arr ); //找到新的品牌数组与老品牌数组的交集，并取出新数组里的相同数据
					$aee_cj = array_diff_key ( $arr_jj, $arr );
					$brandID_arr = array_merge_recursive ( $aee_cj, $arr_jj );
					$touchbrand = json_encode ( $brandID_arr );
				} else {
					$touchbrand = json_encode ( $arr );
				}
			} else {
				$touchbrand = json_encode ( $arr );
			}
			try {
				touch_config::model ()->updateAll ( array ('touchbrand' => $touchbrand ), 'id=:textx', array (':textx' => $touchid ) );
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
			}
		}
	}
	
	/**
	 * 根据触摸屏id对其下的modellimit字段修改
	 * @param $touchid 触摸屏id
	 * @param $arr 模特数组 array(模特)
	 */
	public static function touchUpdateByIdModel($touchid, $arr = array()) {
		$touchid = trim ( $touchid );
		try {
			Comm::checkValue ( $touchid, Yii::t ( 'public', '触摸屏号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			throw new BeubeuException ( $e->getMessage (), $e->getCode () );
		}
		if (count ( $arr ) > 0 && gettype ( $arr ) == 'array') {
			$modellimit = json_encode ( $arr );
			try {
				touch_config::model ()->updateAll ( array ('modellimit' => $modellimit ), 'id=:textx', array (':textx' => $touchid ) );
			} catch ( BeubeuException $e ) {
				throw new BeubeuException ( Yii::t ( 'public', '修改数据失败' ), BeubeuException::SQL_UPDATE_ERR );
			}
		}
	}
	
	/**
	 * 查询搭配屏名称
	 * @param tid 搭配屏ID
	 * @return 搭配屏名
	 * */
	public function getTounameById($tid){
		$obj = Yii::app()->db->createCommand()->select('name')->from('touch_config')->where('id=:id',array(":id"=>$tid))->queryRow();
		if(!empty($obj['name']))
			return $obj['name'];
		else 
			return '';
	}
	
	/**
	* 获取触摸屏的风格列表
	*/
	static function getTouchStyleByTouchid($touchid,$brandid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			//根据触摸屏id号与品牌号获取触摸屏绑定的风格
			$touch_style_data = Yii::app()->db->createCommand()
			->select('*')
			->from('touch_style')
			->where('touchid='.$touchid.' and brandid='.$brandid)
			->queryAll();
			if(count($touch_style_data)==0){
				throw new Exception('触摸屏还未绑定风格！');
			}
			$ret['status']=1;
			$ret['data']=$touch_style_data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 设置触摸屏的风格列表
	*/
	static function setTouchStyleByTouchid($touchid,$brandid,$model_list){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$ret_style_data=self::getTouchStyleByTouchid($touchid,$brandid);
			$style_data=array();
			if($ret_style_data['status']==1){
				$style_data=$ret_style_data['data'];
			}
			//整理出已存入数据库的模特风格
			$model_data=array();
			foreach($style_data as $style_key=>$style_value){
				$modeltype=isset($model_data[$style_value['modeltype']])?$model_data[$style_value['modeltype']]:array();
				if(!in_array($style_value['styleid'],$modeltype)){
					$modeltype[]=$style_value['styleid'];
				}
				$model_data[$style_value['modeltype']]=$modeltype;
			}
			if(count($model_data)>0){//如果整理后的数据不为空
				foreach($model_list as $model_key=>$model_value){
					foreach($model_data as $model_data_key=>$model_data_value){
						if($model_key==$model_data_key){
							$add_arr=array_diff($model_value,$model_data_value);//找出需要添加的数据
							$del_arr=array_diff($model_data_value,$model_value);//找出需要删除的数据
							if(count($add_arr)==0){//需要添加的数据为空时就从数据中删除此项
								unset($model_list[$model_key]);
							}else{
								$model_list[$model_key]=$add_arr;
							}
							if(count($del_arr)==0){//需要删除的数据为空时就从数据中删除此项
								unset($model_data[$model_data_key]);
							}else{
								$model_data[$model_data_key]=$del_arr;
							}
						}
					}
				}
			}
			if(count($model_list)>0){//如果添加数据不为空
				$add_arr=array();
				foreach($model_list as $model_key=>$model_value){//拼接添加数据
					foreach($model_value as $key=>$value){
						if(!empty($value) && is_numeric($value) && intval($value)==$value){
							$arr=array();
							$arr[]=$model_key;
							$arr[]=$brandid;
							$arr[]=$touchid;
							$arr[]=$value;
							$add_arr[]='('.implode(',',$arr).')';
						}
					}
				}
				$add_arr=array_unique($add_arr);
				if(count($add_arr)>0){
					$sql='insert into touch_style (modeltype,brandid,touchid,styleid) values'.implode(',',$add_arr);
					$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
				}
			}
			
			if(count($model_data)>0){//如果删除数据不为空
				//获取需要删除数据的id
				$del_arr=array();
				foreach($style_data as $style_key=>$style_value){
					if(isset($model_data[$style_value['modeltype']]) && in_array($style_value['styleid'],$model_data[$style_value['modeltype']])){
						$del_arr[]=$style_value['id'];
					}
				}
				$del_arr=array_unique($del_arr);
				if(count($del_arr)>0){
					$sql='delete from touch_style where id in('.implode(',',$del_arr).')';
					$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
} 