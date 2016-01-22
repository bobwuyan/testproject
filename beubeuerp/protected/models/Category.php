<?php
/**
 * 分类类
 * 与分类相关的所有操作
 */
class Category {
	
		/**
	 * 向beu_category表添加新分类数据
	 * @param $arr：Array 其内容如下
	 *  parent：int（11） （是否有父级，0为一级，其他ID为其父级）
		title：string（150） （不为空  分类名称）
		style：tinyint（3） （101为用户权限1级目录，102为用户权限2级目录。）
		order：tinyint（4） （排序1在前面）
		code：string（150） （相关描述，style为9是是模特编码）
	 * @return 如果添加数据成功返回true
	 * 否则返回其错误信息
	 */
	public function categoryForAdd($arr = array()) {
		if (count ( $arr ) > 0) {
			$category_biao = new beu_category ();
			if (isset ( $arr ['title'] ) && isset ( $arr ['parent'] ) && isset ( $arr ['style'] )) {
				try {
					$title = trim ( $arr ['title'] );
					$parent = trim ( $arr ['parent'] );
					$style = trim ( $arr ['style'] );
					Comm::checkValue ( $title, Yii::t ( 'beu_category', '分类名' ), 0, 1, 150 );
					Comm::checkValue ( $parent, Yii::t ( 'beu_category', '父级编号' ), 1, 0, 11 );
					Comm::checkValue ( $style, Yii::t ( 'beu_category', '类型编号' ), 1, 0, 3 );				
					$category_biao->title = $title;
					$category_biao->parent = $parent;
					$category_biao->style = $style;
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
					return false;
				}
			} else {
				return false;
			}
			
			if (isset ( $arr ['order'] )) { //排序存在，并不超过长度4
				try {
					$order = trim ( $arr ['order'] );
					if (! empty ( $order )) {
						Comm::checkValue ( $style, Yii::t ( 'beu_category', '排序编号' ), 1, 0, 4 );	
						$category_biao->order = $order;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}
			
			if (isset ( $arr ['code'] )) { //排序存在，并不超过长度150
				try {
					$code = trim ( $arr ['code'] );
					if (! empty ( $code )) {
						Comm::checkValue ( $code, Yii::t ( 'beu_category', '相关描述' ), 0, 0, 150 );
						$category_biao->code = $code;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}
							
			try {
				$count = $category_biao->insert ();
				if ($count > 0) {
				} else {
					throw new BeuBeuException ( Yii::t ( 'public', '插入数据失败' ), BeuBeuException::SQL_INSERT_ERR );
					return false;
				}
			} catch ( BeubeuException $e ) {
				throw new BeuBeuException ( Yii::t ( 'public', '插入数据失败' ), BeuBeuException::SQL_INSERT_ERR );
				return false;
			}
		
		} else {
			throw new BeuBeuException ( Yii::t ( 'beu_category', '数据' ) . Yii::t ( 'public', '不能为空' ), BeuBeuException::FIELD_EMPTY );
			return false;
		}
		return true;
	}
	
	/**
	 * 根据分类id将其删除，此参数支持将多个分类id用‘,’分隔拼接为字符串
	 * @param $categoryid：string 分类id字符串，默认为null
	 * @return 只要有一个分类操作成功就返回true，反之返回false
	 */
	public function categoryDeleteById($categoryid = null) {
		if (empty ( $categoryid )) { //检查字符串是否为空
			throw new BeuBeuException ( Yii::t ( 'public', '分类编号' ) . Yii::t ( 'public', '不能为空' ), BeuBeuException::FIELD_EMPTY );
			return false;
		}
		$this->categoryOperateById ( $categoryid, 12 );
	}
	
	/**
	 * 根据分类id将其分类(删除)，此参数支持将多个分类id用‘,’分隔拼接为字符串
	 * @param $categoryid：string 分类id字符串，默认为null
	 * @param $type：int 需要执行的操作，默认为12 删除。如果都不是就不做任何操作
	 * @return 只要有一个分类操作成功就返回true，反之返回false
	 */
	protected function categoryOperateById($categoryid = null, $type = 12) {
		if (empty ( $categoryid )) { //检查字符串是否为空
			throw new BeuBeuException ( Yii::t ( 'public', '分类编号' ) . Yii::t ( 'public', '不能为空' ), BeuBeuException::FIELD_EMPTY );
			return false;
		}
		$categoryid = trim ( $categoryid ); //清除前后空格
		$categoryid_arr = explode ( ',', $categoryid );
		if (count ( $categoryid_arr ) > 1) {
			$categoryid_arr = array_unique ( $categoryid_arr ); //去除数组中重复的值
			sort ( $categoryid_arr ); //将数组按照值的大小顺序排列
		}
		foreach ( $categoryid_arr as $key => $value ) {
			try {
				Comm::checkValue ( $value, Yii::t ( 'public', '分类编号' ), 1, 1 );
			} catch ( BeubeuException $e ) {
				unset ( $categoryid_arr [$key] );
			}
		}
		if (count ( $categoryid_arr ) > 0) {
			try {
				beu_category::model ()->deleteByPk ( $categoryid_arr, array ('status' => $type ) );
				return true;
			} catch ( Exception $e ) {
				throw new BeuBeuException ( Yii::t ( 'public', '修改数据失败' ), BeuBeuException::SQL_UPDATE_ERR );
				return false;
			}
		}
	}
	
	

	/**
	 * 根据分类ID对beu_category表中数据的数据进行修改
	 * @param $id：int 分类id 默认0
	 * @param $arr：Array 其内容如下
	 *  parent：int（11） （是否有父级，0为一级，其他ID为其父级）
		title：string（150） （不为空  分类名称）
		style：tinyint（3） （101为用户权限1级目录，102为用户权限2级目录。）
		order：tinyint（4） （排序1在前面）
		code：string（150） （相关描述，style为9是是模特编码）
	 * @return 如果修改数据成功返回true
	 * 否则返回false
	 */
	public function categoryUpdateById($id = 0, $arr = array()) {
		$id = trim ( $id );
		try {
			Comm::checkValue ( $id, Yii::t ( 'public', '分类编号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			echo $e->getMessage ();
			return false;
		}
		if (count ( $arr ) > 0) {
			$insert_arr = array ();
			if (isset ( $arr ['parent'] )) {
				try {
					$parent = trim ( $arr ['parent'] );
					if (! empty ( $parent )) {
						Comm::checkValue ( $parent, Yii::t ( 'beu_category', '父级编号' ), 1, 1, 11 );
						$insert_arr ['parent'] = $parent;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}
			if (isset ( $arr ['title'] )) { 
				try {
					$title = trim ( $arr ['title'] );
					if (! empty ( $title )) {
						Comm::checkValue ( $parent, Yii::t ( 'beu_category', '分类名' ), 0, 1, 150 );
						$insert_arr ['title'] = $title;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}
			if (isset ( $arr ['style'] )) { 
				try {
					$style = trim ( $arr ['style'] );
					if (! empty ( $style )) {
						Comm::checkValue ( $style, Yii::t ( 'beu_category', '类型编号' ), 1, 0, 3 );
						$insert_arr ['style'] = $style;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}
			if (isset ( $arr ['order'] )) { //排序存在，并不超过长度4
				try {
					$order = trim ( $arr ['order'] );
					if (! empty ( $order )) {
						Comm::checkValue ( $style, Yii::t ( 'beu_category', '排序编号' ), 1, 0, 4 );	
						$category_biao->order = $order;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}
			
			if (isset ( $arr ['code'] )) { //排序存在，并不超过长度150
				try {
					$code = trim ( $arr ['code'] );
					if (! empty ( $code )) {
						Comm::checkValue ( $code, Yii::t ( 'beu_category', '相关描述' ), 0, 0, 150 );
						$category_biao->code = $code;
					}
				} catch ( BeubeuException $e ) {
					echo $e->getMessage ();
				}
			}	
			try {
				beu_category::model ()->updateAll ( $insert_arr, 'id=:textx', array (':textx' => $id ) );
				Yii::app()->cache->delete(CacheName::getCacheName('touch_Category_list'));//清空分类列表缓存
			} catch ( BeubeuException $e ) {
				throw new BeuBeuException ( Yii::t ( 'public', '修改数据失败' ), BeuBeuException::SQL_UPDATE_ERR );
			}
		} else {
			throw new BeuBeuException ( Yii::t ( 'beu_category', '数据' ) . Yii::t ( 'public', '不能为空' ), BeuBeuException::FIELD_EMPTY );
		}
		return true;
	}
	
	/**
	 * 根据类型编号查询所有分类信息
	 * @param $styleid：int 类型id 默认0 或者为数组
	 * @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	 */
	public static function categorySelectForAll($styleid = 0) {
		$ret=array('status'=>0,'msg'=>'');
		try {
			if($styleid!=0 && !is_array($styleid)){
				$styleid = trim ( $styleid);
				$styleid=array($styleid);
			}
			$newlist=Yii::app()->cache->get(CacheName::getCacheName('touch_Category_list'));//获取分类列表缓存
			if($newlist===false){
				$data = Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_category' )->where('status=1')->order('id')->queryAll ();
				if(count($newlist)==0){
					throw new Exception ('类型为空！');
				}
				$ret['data']=$data;
				$ret['status']=1;
				Yii::app()->cache->set(CacheName::getCacheName('touch_Category_list') ,$ret,0);//设置分类列表缓存 永不过期
			}else{
				$ret=$newlist;
			}
			if(is_array($styleid)){
				foreach($ret['data'] as $key=>$value){
					if(!in_array($value['style'],$styleid)){
						unset($ret['data'][$key]);
					}
				}
				if(count($ret['data'])==0){
					$ret['status']=0;
					throw new Exception('查询类型为空！');
				}
				sort($ret['data']);
			}
			
		} catch ( Exception $e ) {
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据类型编号查询所有分类信息
	* @param styleid int 类型id 默认0
	* @param arr 类别ID数组 默认为空数组
	* @return 返回查询结果 结果为数组
	**/
	public static function categorySelectBystyleAndid($styleid = 0,$arr=array()) {
		$styleid = trim ( $styleid);
		$where="style=$styleid";
		if(!empty($arr))
		{
			$c = trim(implode(',',$arr));
			$where.="&& id in ($c)";
		}
		$con = Yii::app()->db->createCommand()
		->select('*')
		->from('beu_category')
		->where($where)
		->queryAll();
		return $con;
	}
	/**
	 * 根据条件查询分类详细信息
	 * @param categoryid int 分类id 默认0
	 * @return 如果查询数据成功返回结果集
	 * 否则返回false
	 */
	public static function categorySelectById($categoryid = 0) {
		$categoryid = trim ( $categoryid );
		try {
			Comm::checkValue ( $categoryid, Yii::t ( 'public', '分类编号' ), 1, 1 );
		} catch ( BeubeuException $e ) {
			echo $e->getMessage ();
			return false;
		}
		try {
			$date = beu_category::model ()->find ( 'id=:categoryid', array (':categoryid' => $categoryid ) );
			if (empty ( $date )) {
				throw new BeuBeuException ( Yii::t ( 'public', '查询数据失败' ), BeuBeuException::SQL_SELECT_ERR );
			} else {
				return $date;
			}
		} catch ( BeubeuException $e ) {
			throw new BeuBeuException ( Yii::t ( 'public', '查询数据失败' ), BeuBeuException::SQL_SELECT_ERR );
		}
	}
	
	/**
	 * 根据父级查询分类详细信息 
	 * @param parentid int 分类id 默认0查询所有
	 * @return 如果查询数据成功返回结果集
	 * 否则返回false
	 */
	public function categorySelectByParent($parentid = 0) {
		$parentid = trim ( $parentid );
		try {
			Comm::checkValue ( $parentid, Yii::t ( 'beu_category', '父级编号'), 1, 1 );
		} catch ( BeubeuException $e ) {
			echo $e->getMessage ();
			return false;
		}
		try {
			$date = beu_category::model ()->find ( 'id=:parentid', array (':parentid' => $parentid ) );
			if (empty ( $date )) {
				throw new BeuBeuException ( Yii::t ( 'public', '查询数据失败' ), BeuBeuException::SQL_SELECT_ERR );
			} else {
				return $date;
			}
		} catch ( BeubeuException $e ) {
			throw new BeuBeuException ( Yii::t ( 'public', '查询数据失败' ), BeuBeuException::SQL_SELECT_ERR );
		}
	}
	
}