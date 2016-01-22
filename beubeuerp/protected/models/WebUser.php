<?php
class WebUser  extends CWebUser 
{
	private $_model;
	function getFirst_Name()
	{
//		echo Yii::app()->user->id;
    	return;
	    $user = $this->loadUser($userid);
	    return $user->first_name;
  	}
  	/**
  	 * 查询当前用户
  	 */
	function isBrandUser()
    {
	   	 if(!empty($_SESSION ['userid']))
		 {
		 	 $userid=$_SESSION ['userid'];
			    $user = $this->loadUser($userid);
			    if ($user==null){
			        return false;//普通用户
			    }
			    else
			    {
			    	if($user->type<40)//当权限分类小于40就为管理员或品牌级别
			    	{
			    		 return true;//以为管理员或品牌
			    	}else{
			    		return false;
			    	}
				}
	  	  }else
	  	  {
	  	 	 return false;//普通用户
	  	  }
    }
    function isAdmin()
    {
    	
	   	 if(!empty($_SESSION ['userid']))
		 {
		 	 $userid=$_SESSION ['userid'];
			    $user = $this->loadUser($userid);
			    if ($user==null){
			        return false;//普通用户
			    }
			    else
			    {
			    	if($user->type<5)//当权限分类小于5就为管理员级别
			    	{
			    		 return true;//以为管理员
			    	}else{
			    		return false;
			    	}
				}
	  	  }else
	  	  {
	  	 	 return false;//普通用户
	  	  }
    }
	protected function loadUser($id=NULL)
    {
        if($this->_model===null)
        {
            if($id!==null)
            {
                $this->_model=beu_users::model()->findByPk($id);
            }
        }
        return $this->_model;
    }
	
	/**
	* 获取所有用户
	**/
	static public function getusersall($where=''){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$oj = Yii::app()->db->createCommand()
			->select('beu_users.*,beu_user_config.name as type_name')->from('beu_users')
			->join('beu_user_config','beu_user_config.rand=beu_users.type');
			if(!empty($where)){
				$oj =$oj->where($where);
			}
			$oj =$oj->order('id desc')->queryAll();
			if(count($oj)==0){
				throw new Exception('查询用户数据为空');
			}
			$ret['status']=1;
			$ret['data']=$oj;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据ID获取当前用户
	**/
	static public function getusersById($id){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($id) || !is_numeric($id) || intval($id)!=$id){
				throw new Exception('用户ID必须是整数');
			}
			$cache=Yii::app()->cache->get(CacheName::getCacheName('user_info') .$id);//获取用户信息缓存
			if($cache===false){
				$user=beu_users::model()->findByPk($id);
				if ($user==null){
					throw new Exception('查询用户数据为空');
				}
				$ret['status']=1;
				$ret['data']=$user;
				Yii::app()->cache->set(CacheName::getCacheName('user_info') .$id,$ret,0);//设置用户信息缓存
			}else{
				$ret=$cache;
			}
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 验证用户名是否可用
	**/
	static public function VerificationUserName($username){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($username)){
				throw new Exception('用户名不能为空');
			}
			$user=beu_users::model()->findAll ('username=:username',array(':username'=>$username));
			if (!empty($user)){
			    throw new Exception('用户名已存在，不可用！');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
}