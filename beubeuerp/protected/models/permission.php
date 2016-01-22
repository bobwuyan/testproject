<?php
class permission
{
/**
	 * 修改用户信息
	 * @param $id修改的时候用
	 * @param $userinfo数组
	 * （
	 * username：用户名, password：密码,
	 * permissions：权限, type：类型,
	 * brandid：品牌号, touchid：触摸屏id
	 * createtime：创建时间，endtime到期时间
	 * ）
	 * @return "true"为成功，不为"true"为失败提示
	 */
	static public function userUpdateById($id,$userinfo=NULL)
	{
		$message="true";
		$userarray = array ();	
		$userstatus=true;
		try 
		{
			Comm::checkValue($id,yii::t('beu_users',"用户编号"),1,0);//ID
		} catch (BeubeuException $e) {
			$message=$e->getMessage();
		} 
		if(is_array($userinfo) && $userinfo!=null)//修改的内容要存在
		{
			//用户名暂时不能修改
			try {
				Comm::checkValue($userinfo['username'],yii::t('beu_users',"用户名"),0,1,20);//用户名
				$userarray['username']=$userinfo['username'];
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			} 
			try {
				Comm::checkValue($userinfo['password'],yii::t('beu_users',"密码"),0,1);//密码
				$userarray['password']=md5("beubeu".md5($userinfo['password']));
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			} 
			try {
				Comm::checkValue($userinfo['permissions'],yii::t('beu_users',"权限"),0,1,512);//权限
				$userarray['permissions']=$userinfo['permissions'];
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			}  
			try {
				Comm::checkValue($userinfo['type'],yii::t('beu_users',"用户类型"),1,1,127);//用户类型
				$userarray['type']=$userinfo['type'];
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			}
			try {
				Comm::checkValue($userinfo['brandid'],yii::t('public',"品牌号"),0,1,255);//品牌ID号
				$userarray['brandid']=$userinfo['brandid'];
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			} 
			try {
				Comm::checkValue($userinfo['touchid'],yii::t('public',"触摸屏号"),0,1,255);//触摸屏ID号
				$userarray['touchid']=$userinfo['touchid'];
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			} 
			try {
				Comm::checkValue($userinfo['endtime'],yii::t('beu_users',"到期时间"),1,1);//到期时间
				$userarray['endtime']=$userinfo['endtime'];
			} catch (BeubeuException $e) {
				$message=$e->getMessage();
			} 
		}else {
			$message=yii::t('public',"修改数据失败");
		}
		//更新操作
		if($message=="true")
		{
			if(count($userarray)!=0 )
			{
				try{
					//不是当前用户的信息，并且和其他用户名不重复
					$user=beu_users::model()->findAllBySql("select * from beu_users where id!=$id and username='".$userarray['username']."'");
					if(count($user)>0)
					{
	//					echo "该账号已被占用";
						$message=yii::t('beu_users',"该账号已被占用");
					}else{
						//如果数据没有发生改变，count为0,
						$count =beu_users::model()->updateAll($userarray,'id=:textx',array(':textx'=>$id));
						if($count>0){
						}else{
							$message=yii::t('public',"修改数据失败");
						}
					}
				}catch(Exception $e){
					$message=yii::t('public',"修改数据失败");
				}
			}
			else {
				$message=yii::t('public',"修改数据失败");
			}
		}
		
		return $message;
	}
	
	/**
	 * 添加用户
	 * @param $userinfo数组
	 * （
	 * username：用户名, password：密码,
	 * permissions：权限, type：类型,
	 * brandid：品牌号, touchid：触摸屏id
	 * createtime：创建时间，endtime到期时间
	 * ）
	 * @return "true"为成功，不为"true"为失败提示
	 */
	static  public function userForAdd($userinfo=NULL)
	{
		$message="true";
		if(!empty($userinfo))
		{
			$beuUserArray=new beu_users();
			//首先判断这些字段是否存在，这些都是必填项
			if(isset($userinfo['username']) && isset($userinfo['password']) && isset($userinfo['permissions'])  && isset($userinfo['type'])  && isset($userinfo['brandid'])  && isset($userinfo['touchid']) && isset($userinfo['createtime']) && isset($userinfo['endtime']))
			{
				try {
					Comm::checkValue($userinfo['username'],yii::t('beu_users',"用户名"),0,1,20);//用户名
					//查询该用户是否注册过，如果被注册就返回
					$count=permission::userSelectByParm("",$userinfo['username'],"");
					if(count($count)>0)
					{
						$message=yii::t('beu_users',"该账号已被占用");
					}else {
						$beuUserArray->username=$userinfo['username'];
					}
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				}
				try {
					Comm::checkValue($userinfo['password'],yii::t('beu_users',"密码"),0,1);//密码
					$beuUserArray->password=md5("beubeu".md5($userinfo['password']));
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				} 
				try {
					Comm::checkValue($userinfo['permissions'],yii::t('beu_users',"权限"),0,1,512);//权限
					$beuUserArray->permissions=$userinfo['permissions'];
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				}  
				try {
					Comm::checkValue($userinfo['type'],yii::t('beu_users',"用户类型"),1,1,127);//用户类型
					$beuUserArray->type=$userinfo['type'];
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				}
				try {
					Comm::checkValue($userinfo['brandid'],yii::t('public',"品牌号"),0,1,255);//品牌ID号
					$beuUserArray->brandid=$userinfo['brandid'];
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				} 
				try {
					Comm::checkValue($userinfo['touchid'],yii::t('public',"触摸屏号"),0,1,255);//触摸屏ID号
					$beuUserArray->touchid=$userinfo['touchid'];
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				} 
				try {
					Comm::checkValue($userinfo['createtime'],yii::t('beu_users',"创建时间"),1,1);//创建时间
					$beuUserArray->createtime=$userinfo['createtime'];
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				} 
				try {
					Comm::checkValue($userinfo['endtime'],yii::t('beu_users',"到期时间"),1,1);//到期时间
					$beuUserArray->endtime=$userinfo['endtime'];
				} catch (BeubeuException $e) {
					$message=$e->getMessage();
				} 
				if($message=="true")
				{
					try{
						$count=$beuUserArray->save();
						if($count>0){
						}else{
							$message=yii::t('public',"插入数据失败");//插入数据失败
						}
					}catch(Exception $e){
						$message=yii::t('public',"插入数据失败");
					}
				}
			}else {
				$message=yii::t('public',"插入数据失败");//插入数据失败
			}
		}else{
			$message=yii::t('public',"插入数据失败");
		}
		return $message;
	}
	
	/**
	 * 查找搜索用户信息
	 * @param  $keyword 关键字
	 */
	static public function userinfoSelectByKeyword($keyword, $page = 1, $pagecount = 20)
	{
		$where ="id>0";
		if(!empty($keyword))
		{
			$where.="&& username like '%".$keyword."%'";
		}
		//获取总个数
		$userinfo = Yii::app ()->db->createCommand ()
		->select ( '*' )
		->from ( "beu_users" )
		->where ( $where )
		->order('createtime desc')
		->limit ( $pagecount, ($page - 1) * $pagecount )
		->queryAll ();
		//总数量
		$usercount = Yii::app ()->db->createCommand ()
		->select ( '*' )
		->from ( "beu_users" )
		->where ( $where )
		->order('createtime desc')
		->queryAll ();
		
		//翻页
		$criteria = new CDbCriteria();
		$pages=new CPagination(count($usercount));
		$pages->pageSize=$pagecount;
	    $pages->applyLimit($criteria);
	    
		if (! empty ( $userinfo )) {
 				$userinfo[0]["pages"] = $pages;
				return $userinfo;
			} else {
				return false;
			}
		return $userinfo;
	}
	/**
	 * 删除用户
	 * @param $id用户id号
	 * @return true删除成功，不为true是删除失败提示
	 */
	static public function userDeleteById($id)
	{
		if(!empty($id))
		{
			$idArray=explode(",",$id);
			foreach ($idArray as $value)
			{
				if(!is_numeric($value) || intval($value)!=$value || intval($value)<=0)
				{
					return yii::t('beu_users',"用户ID号不正确");
				}
			}
			$count = beu_users::model()->deleteAll("id in ($id)");
			if($count>0)
			{ 
	   			return "true";
			}else
			{ 			
	    		return yii::t('public',"删除数据失败"); //删除数据失败
			} 
		}else {
			return yii::t('public',"删除数据失败"); //删除数据失败
		}
	}
	
	/**
	 * 创建用户状态表
	 * 如果不存在就创建，存在就进行查询
	 */
	public function createtable()
	{
		//判断此表是否存在
		$result= Yii::app()->db->createCommand("SHOW TABLES LIKE 'beu_usermode' ")->execute();
		if($result==0)//为0是不存在，不存在就创建表
		{
			$sqlTable="CREATE TABLE IF NOT EXISTS `beu_usermode` ( `id` INT(11) NOT NULL AUTO_INCREMENT,`status` INT(11) DEFAULT NULL COMMENT '用户状态', `userid` INT(11) DEFAULT NULL COMMENT '用户id号',PRIMARY KEY (`id`)) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='用户状态表' AUTO_INCREMENT=1 ";
			Yii::app()->db->createCommand($sqlTable)->execute();
		}
		else
		{
		
		}
	}
	/**
	 *用户登录 
	 * @param $username用户名
	 * @param $password密码
	 * 返回"true"为成功,不为true返回失败信息
	 */
	static public function userLogin($username=NULL,$password=NULL)
	{
		$ret=array('status'=>0,'msg'=>'');
		$ret_num=2;
		try{
			if(!empty($_SESSION ['user_id']))//如果sessionid有值，直接返回登录成功
			{
			}
			if(empty($username) || empty($password))
			{
				throw new Exception("用户名或密码输入有误");
			}
			$password=md5("beubeu".md5($password));
			$users=self::userSelectByParm("",$username,$password);//查询用户表用户名和密码是否正确
			if(empty($users)){//大于0说明数据库查询有数据
				$ret_num=2;	//用户名密码错误
				throw new Exception('用户名密码错误');
			}
			$currenttime=time ();
			///echo $currenttime."  ".$users['endtime'];exit();
			if($currenttime>strtotime($users['endtime'])){//如果当前时间大于到期时间说明已过期
				$ret_num=3;	
				throw new Exception('帐号过期');	
			}
			self::usermodelUpdateByUserid($users['id'],2);
			//$ret_status=self::userSeleteStatus($users['id']);
			//print_r($ret_status);exit();
			//if($ret_status['status']==1){//说明可登录
			//	$ret_num=8;	
			//	throw new Exception('帐号已登陆');	
			//}
			//设置seesion和cookie
			if(!empty($users["type"]) && $users["type"]==Yii::app()->params['sub_type']){
				$ret_num=6;	
				throw new Exception('用户访问权限不够');
			}
			//用户需要进行IP验证的就验证
			if($users['ip_limit']==1){
				$sel = Yii::app()->db->createCommand();
				$ipp2 = $sel->select('IP')->from('beu_user_ip_limit')->where('status=1 and userid='.$users['id'])->queryAll();
				if(count($ipp2)==0){
					$ret_num=5;	
					throw new Exception('IP未设置');
				}
				$is_bool=false;
				foreach($ipp2 as $value){
					if($value['IP']==Comm::getSourceIp()){
						$is_bool=true;
						break;
					}
				}
				if(!$is_bool){
					$ret_num=5;	
					throw new Exception('当前访问IP不在设置范围内');
				}
			}
			$users['type']=self::userTypeChange($users['type'],$users['ERP3_status']);//权限转换
			if($users['type'] >70 || $users['type'] <51){//用户权限级别不再范围表示其权限不可访问后台
				$ret_num=6;	
				throw new Exception('用户访问权限不够');
			}
			//$_SESSION ['type'] = $users['type'];
			//$_SESSION ['touchid'] = json_decode ( $userinfo ['touchid'], true );
			//$_SESSION ['permissions'] = json_decode ( $userinfo ['permissions'], true );
			$_SESSION['user_id'] = $users['id'];
			$_SESSION['userid'] = $users['id'];
			$_SESSION['user'] = $users['username'];  //用户名
			$_SESSION['type']  = $users['type'];	//用户类型，2为管理员，5为品牌，10为普通用户
			$_SESSION['istotalaccount']  = $users['istotalaccount'];	//是非品牌总管理 1.是 0.否
			$_SESSION['account']  = (isset($users['account'])&& !empty($users['account']))?$users['account']:0;	//对应的账户类型 beu_useraccount.id
			$_SESSION['touchidd']  = (isset($users['touchid'])&& !empty($users['touchid']))?implode(',',json_decode($users['touchid'],true)):'';
			$_SESSION['xiazai_v'] = 1; //是有可下载图片
			$_SESSION['brandid']=(isset($users['brandid'])&& !empty($users['brandid']))?$users['brandid']:0;
			
			/************* 总分屏配置 start ********************/
			$_SESSION ['clothes_table']='touch_clothes';
			$_SESSION ['table_where']='';
			$_SESSION ['is_push']=0;
			$_SESSION ['sub_id']=0;
			
			/************* 总分屏配置 end ********************/
			Yii::app()->cache->delete(CacheName::getCacheName('user_action_Info').$users['id']);//清除用户的可访问页面列表缓存
			usercookie::userSet($users['id'],$username,$password);
			//删除该用户临时数据
			//self::usermodeDeleteByUserid($users['id']);
			//将该用户添加到临时表中
			//self::usermodeForAdd($users['id']);
			$ret_num=4;		
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		$ret['data']=$ret_num;
		return $ret;
	}
	/**
	 * 通过用户编号、用户名、密码查询用户表
	 * @param $id 用户编号
	 * @param $username 用户名
	 * @param $password 密码(需加密后的密码)
	 * @return 数组
	 */
	static public function userSelectByParm($id=NULL,$username=NULL,$password=NULL)
	{
		$users=array();
		$userinfo=array();
		$w_str='1=1';
		if(!empty($id)){
			$w_str.=' and id='.$id;
		}
		if(!empty($username)){
			$w_str.=' and username=\''.$username.'\'';
		}
		if(!empty($password)){
			$w_str.=' and password=\''.$password.'\'';
		}
		if($w_str!='1=1'){
			$userinfo = Yii::app ()->db->createCommand ()
			->select ( '*' )
			->from ( "beu_users" )
			->where ( $w_str )
			->queryRow ();
		}
		return $userinfo;
	}
	/**
	 * 修改登录状态
	 * @param $userid用户id
	 * @param $status状态1为可登录，2为不可登录
	 * @return true修改成功，false失败
	 */
	static public function usermodelUpdateByUserid($userid,$status=NULL)
	{
		try {
			$userarray=array();
			$userarray['lastupdate']=date("Y-m-d H:i:s");
			$userarray['sessionid']=session_id();
			if(!empty($status))
			{
				$userarray['status']=$status;
			}
			$count =beu_usermode::model()->updateAll($userarray,'userid=:textx',array(':textx'=>$userid));
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
	/**
	 * 用户状态 判断是否可登录
	 * @param $userid用户id
	 * @return status=0可以登录，0表示已登录不可登录
	 */
	static public function userSeleteStatus($userid){
		$ret=array('status'=>0,'msg'=>'');
		try {
			if(empty($userid) || intval($userid)!=$userid || !is_numeric($userid)){
				throw new Exception('帐号ID错误');
			}
			//查找用户登陆状态表
			$ret_status=self::__getUserStatus($userid);
			if($ret_status['status']==0){//状态表里未添加过此帐号的数据
				self::__addUserStatus($userid);
				throw new Exception($ret_status['msg']);
			}
			//判断帐号是否可登陆
			if(strtotime($ret_status['data'][0]['lastupdate'])<=(time()-3600*2)){//最后操作时间大于10分钟 将其帐号设为可登录
				self::usermodelUpdateByUserid($userid,2);
				throw new Exception('原帐号操作超时，可登录');
			}
			if($ret_status['data'][0]['sessionid']==session_id()){//当是同一个用户访问时 表示帐号可登陆
				throw new Exception('同一人操作，帐号可登陆');
			}
			$ret['status']=1;
		} catch (Exception $e) {
			$ret['msg']=$e->getMessage();
		} 
		return $ret;
	}
	/**
	* 添加用户登陆状态
	**/
	static public function __addUserStatus($userid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			//验证当前用户是否在状态表里存在
			$ret_status=self::__getUserStatus($userid);
			if($ret_status['status']==0){//状态表里未添加过此帐号的数据
				Yii::app()->db
					->createCommand('insert into beu_usermode (status,sessionid,userid,lastupdate) values(2,\''.session_id().'\','.$userid.',\''.date("Y-m-d H:i:s").'\')')
					->execute();
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
	}
	/**
	* 获取用户的登陆状态
	**/
	static public function __getUserStatus($userid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$beu_usermode = Yii::app ()->db->createCommand ()
			->select ( '*' )
			->from ( "beu_usermode" )
			->where('userid='.$userid)
			->queryAll ();
			if(count($beu_usermode)==0){
				throw new Exception('用户未添加状态数据');
			}
			$ret['status']=1;
			$ret['data']=$beu_usermode;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	static public function userSeleteAll(){
		$userobj = Yii::app ()->db->createCommand ()
		->select ( '*' )
		->from ( "beu_users" )
		->queryAll ();
		return $userobj;
	}
	/**
	 * 删除用户
	 * @param $id用户id号
	 * @return true删除成功，不为true是删除失败提示
	 */
	static public function usermodeDeleteByUserid($userid)
	{
		if(!empty($userid))
		{
			$count =beu_usermode::model()->updateAll(array('lastupdate'=>date("Y-m-d H:i:s", time()-3600*24),'status'=>1),'userid=:textx',array(':textx'=>$userid));
			if($count>0){ 
	   			return true;
			}else{ 			
	    		return false; //删除数据失败
			} 
		}else {
			return false; //删除数据失败
		}
	}
	/**
	 * 删除用户
	 * @param $userid用户id号
	 * @return true删除成功，不为true是删除失败提示
	 */
	public function usermodeForAdd($userid)
	{
		$message="true";
		try {
			Comm::checkValue($userid,yii::t('beu_users',"用户编号"),1,1);//用户编号
		} catch (BeubeuException $e) {
			$message=$e->getMessage();
		} 
		$beuUsermodeArray=new beu_usermode();
		$beuUsermodeArray->lastupdate=date("Y-m-d H:i:s");
		$beuUsermodeArray->status=2;//2代表已登录不可登录
		$beuUsermodeArray->userid=$userid;
		try{
			$count=$beuUsermodeArray->save();
			if($count>0){
			}else{
				$message=yii::t('public',"插入数据失败");//插入数据失败
			}
		}catch(Exception $e){
			$message=yii::t('public',"插入数据失败");
		}
		return $message;
	}
	/**
	 * 查询状态表
	 * @param $userid
	 * @return usermode用户状态数组
	 */
	public function usermodeSeleteStatus($userid)
	{
		$usermode=array();
		try {
			Comm::checkValue($userid,yii::t('beu_users',"用户编号"),1,1);//用户编号
		} catch (BeubeuException $e) {
			return $usermode;
		} 
		$where="userid=$userid";
		$usermode = Yii::app ()->db->createCommand ()
		->select ( '*' )
		->from ( "beu_usermode" )
		->where ( $where )
		->queryAll ();
		return $usermode;
	}
	/**
	 * 通过日期删除用户临时表
	 */
	static public function usermodeDeleteBydate()
	{
		//$datetime=date("Y-m-d H:i:s", time()-12000);//当前时间减去200分钟
		//$count = beu_usermode::model()->deleteAll("lastupdate<'".$datetime."'");
	}
	/**
	 * 通过分类表中的style查询分类数据
	 * @param $style 12为用户权限2级
	 * @return 数组
	 */
	static public function userSeletecategoryinfoBystyle($style="")
	{
		$category=array();
		if(!empty($style))
		{
			$category=Category::categorySelectForAll(array($style));
			if($category['status']==0){
				$category=array();
			}else{
				$category=$category['data'];
			}
		}
		return $category;
	}
	/**
	 * 查看当前用户是否有权限访问
	 * @param $style 12为用户权限2级
	 * @return true为成功，false为失败
	 */
	static public function userCategorySeleteByStyle($style=NULL)
	{
		$message=true;
		if(!empty($_SESSION ['userid']))
		{
		 	 $userid=$_SESSION ['userid'];
		     $user = beu_users::model()->findByPk($userid);
		     if ($user==null){
		        $message= false;
		     }
		     else
		     {
		    	if(!empty($user['permissions']))
		    	{
		    		$permissions=json_decode ( $user ['permissions'], true );
		    		if(count($permissions)>0)
		    		{
		    			if(!in_array($style,$permissions))
		    			{
		    				$message= false;
		    			}
		    		}
		    	 }else{
		    		  $message= false;
		    	 }
			  }
	  	  }else
	  	  {
	  	 	  $message= false;
	  	  }
		return $message;
	}
	/**
	 * 判断用户是否需要验证来源IP
	 */
	public static function judgeUserIP()
	{
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!isset($_SESSION['user_id'])){
				throw new Exception('用户ID不能为空');
			}
			$is_bool_ip=false;//不需要验证来源IP
			$userid=$_SESSION['user_id'];
			$data=Yii::app ()->db->createCommand ()->select ( 'type,ip_limit,ERP3_status' )->from ( 'beu_users' )->where ('id='.$userid)->queryAll ();//查询用户的权限极其是否启用IP限制
			if(count($data)==0){
				throw new Exception('用户数据不存在');
			}
			$data[0]['type']=self::userTypeChange($data[0]['type'],$data[0]['ERP3_status']);
			if($data[0]['type']!=1 && $data[0]['ip_limit']==1){//不为超级管理员并且启用了IP限制
				$is_bool_ip=true;
			}
			$ret['status']=1;
			$ret['is_bool_ip']=$is_bool_ip;
			$ret['user_type']=$data[0]['type'];
		} catch (Exception $e) {
			$ret['msg']=$e->getMessage();
		} 
		return $ret;
		
	}
	/**
	* 权限转换 将可用品牌权限 转为 3.0 ERP权限
	**/
	public static function userTypeChange($type,$ERP3_status){
		$_SESSION['userTypeChange']=0;//是否转换权限
		if(($type==5 || $type==6) && $ERP3_status==1){//当用户权限为 品牌完整权限 或 品牌查看权限
			if($type==5){
				$type=52;
			}else{
				$type=53;
			}
			$_SESSION['userTypeChange']=1;
		}
		return $type;
	}
}
