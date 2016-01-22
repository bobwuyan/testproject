<?php
/**
 * 权限类
 * 与权限相关的所有操作
 */
class Beu_Power {
	/**
	* 获取action列表
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getActionlist(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$data=Yii::app ()->db->createCommand ()->select ( 'beu_user_action.*,beu_category.title as type_title' )->from ( 'beu_user_action' )->leftjoin('beu_category', 'beu_category.id=beu_user_action.typeid and beu_category.style=25')->order('id desc')->queryAll ();
			if(count($data)==0){
				throw new Exception('Action数据查询失败');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据ID获取action详情
	* @param id Action ID
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getActionById($id){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($id) || !is_numeric($id) || intval($id)!=$id){
				throw new Exception('ID必须是整数');
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_action' )->where('id='.$id)->queryAll ();
			if(count($data)==0){
				throw new Exception('Action数据查询失败');
			}
			$ret['status']=1;
			$ret['data']=$data[0];
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据ID修改action
	* @param id Action ID
	* @param title Action介绍
	* @param typeid Action类型
	* @param path Action路径
	* @param status Action状态
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function updateActionById($id,$title,$typeid,$path,$status){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($id) || !is_numeric($id) || intval($id)!=$id){
				throw new Exception('ID必须是整数');
			}
			$status=$status==1?1:0;
			if(empty($title)){
				throw new Exception('Action介绍不能为空');
			}
			if(empty($path)){
				throw new Exception('Action路径不能为空');
			}
			if(empty($typeid)){
				throw new Exception('类型不能为空');
			}
			$sql='update beu_user_action set title=\''.$title.'\',status='.$status.',path=\''.$path.'\',typeid='.$typeid.' where id='.$id;
			$data=Yii::app ()->db->createCommand ($sql)->execute();
			if(empty($data)){
				throw new Exception('Action修改失败或未做修改！');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 添加action到数据库
	* @param $title Action介绍
	* @param $path Action路径
	* @param typeid Action类型
	* @param status Action状态
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function addAction($title,$typeid,$path,$status){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$title=isset($title)?trim($title):'';
			$path=isset($path)?trim($path):'';
			$status=$status==1?1:0;
			if(empty($title)){
				throw new Exception('Action介绍不能为空');
			}
			if(empty($path)){
				throw new Exception('Action路径不能为空');
			}
			if(empty($typeid)){
				throw new Exception('类型不能为空');
			}
			$sel_path=self::selAction($path);//判断此数据是否添加过
			if($sel_path['status']==0){
				throw new Exception($sel_path['msg']);
			}
			$sql='insert into beu_user_action (title,path,typeid,status) values(\''.$title.'\',\''.$path.'\','.$typeid.','.$status.')';
			$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
			if($ret_id==0){
				throw new Exception('Action添加失败');
			}
			$ret['status']=1;
			$ret['msg']='Action添加成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 查看action是否存在数据库
	* @param $path Action路径
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function selAction($path){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$path=isset($path)?trim($path):'';
			if(empty($path)){
				throw new Exception('Action路径不能为空');
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_action' )->where ('path=\''.$path.'\'')->queryAll ();
			if(count($data)>0){
				throw new Exception('Action已存在');
			}
			$ret['status']=1;
			$ret['msg']='Action不存在';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	
	/**
	* 获取模块列表
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getPowerlist(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_power' )->order('id desc')->queryAll ();
			if(count($data)==0){
				throw new Exception('模块数据查询失败');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据ID获取模块详情
	* @param id 模块ID
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getPowerById($id){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($id) || !is_numeric($id) || intval($id)!=$id){
				throw new Exception('ID必须是整数');
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_power' )->where('id='.$id)->queryAll ();
			if(count($data)==0){
				throw new Exception('模块数据查询失败');
			}
			$ret['status']=1;
			$ret['data']=$data[0];
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 根据ID修改模块
	* @param id 模块ID
	* @param name 模块名
	* @param visit_power 模块访问权限
	* @param status 模块状态
	* @param set_special 特殊设置
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function updatePowerById($id,$name,$visit_power,$status,$set_special){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($id) || !is_numeric($id) || intval($id)!=$id){
				throw new Exception('ID必须是整数');
			}
			$status=$status==1?1:0;
			if(empty($name)){
				throw new Exception('模块名不能为空');
			}
			if(empty($visit_power)){
				throw new Exception('模块访问权限不能为空');
			}
			if(empty($set_special)){
				$set_special=0;
			}
			$sql='update beu_user_power set name=\''.$name.'\',status='.$status.',visit_power=\''.$visit_power.'\',set_special=\''.$set_special.'\' where id='.$id;
			$data=Yii::app ()->db->createCommand ($sql)->execute();
			if(empty($data)){
				throw new Exception('模块修改失败或未做修改！');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 添加功能
	* @param name 模块名
	* @param visit_power 模块访问权限
	* @param status 模块状态
	* @param set_special 特殊设置
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function addPower($name,$visit_power,$status,$set_special){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$name=isset($name)?trim($name):'';
			$visit_power=isset($visit_power)?trim($visit_power):'';
			if(empty($name)){
				throw new Exception('模块名字不能为空');
			}
			if(empty($set_special)){
				$set_special=0;
			}
			$sql='insert into beu_user_power (name,visit_power,status,set_special) values(\''.$name.'\',\''.$visit_power.'\','.$status.',\''.$set_special.'\')';
			$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
			if($ret_id==0){
				throw new Exception('模块添加失败');
			}
			$ret['status']=1;
			$ret['msg']='模块添加成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 根据用户的权限获取其能访问的功能模块
	* @param $user_type 用户权限
	* @param $is_status 获取数据模式：0为获取禁用数据，1为获取启用数据，2为获取所有数据 默认为1
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getPower($user_type,$is_status=1){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($user_type) || !is_numeric($user_type) || intval($user_type)!=$user_type){
				throw new Exception('用户权限级别必须是整数');
			}
			$status_str='status=1';
			if($is_status==0){
				$status_str='status=0';
			}else if($is_status==2){
				$status_str='';
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_power' )->where ($status_str)->queryAll ();
			if(count($data)==0){
				throw new Exception('获取功能模块为空');
			}
			if($user_type!=1){//当其用户的权限不为超级管理员时
				$touchid_arr=explode(',',$_SESSION['touchidd']);//当前用户绑定的屏
				$brandid_arr=$_SESSION['brandid'];//当前用户绑定的品牌
				foreach($data as $key=>$value){//遍历并清除用户没有权限访问的数据
					$visit_arr=explode(',',$value['visit_power']);
					$special=(isset($value['set_special']) && !empty($value['set_special']))? explode(':',$value['set_special']):array('','') ;//查看此功能模块是否为特殊功能
					$data_sp=isset($special[1])?explode(',',$special[1]):array();//拆分数据
					if(count($visit_arr)==0 || !in_array($user_type,$visit_arr)){
						unset($data[$key]);
					}else if($special[0]=='t' && count(array_intersect($touchid_arr,$data_sp))==0){//此功能为搭配屏特殊功能时，如果用户绑定屏与此功能设定屏不同就删除此功能
						unset($data[$key]);
					}else if($special[0]=='b' && count(array_intersect($brandid_arr,$data_sp))==0){//此功能为品牌特殊功能时，如果用户绑定品牌与此功能设定品牌中没有一个相同的就删除此功能
						unset($data[$key]);
					}
				}
				if(count($data)==0){
					throw new Exception('整理后功能模块为空');
				}
			}
			$ret['status']=1;
			$ret['msg']='获取功能模块成功';
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 添加访问IP白名单
	* @param $userid 用户ID
	* @param $ip 访问IP白名单 是数组
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function addIpLimit($userid,$ip){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			if(!is_array($ip) || count($ip)==0){
				throw new Exception('IP地址错误');
			}
			$insert_arr=array();
			foreach($ip as $value){
				if(Comm::is_ip($value)){
					$arr=array($userid,'\''.$value.'\'');
					$insert_arr[]='('.implode(',',$arr).')';
				}
			}
			if(count($insert_arr)==0){
				throw new Exception('IP地址整理后为空');
			}
			$sql='insert into beu_user_ip_limit (userid,IP) values'.implode(',',$insert_arr);
			$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
			if($ret_id==0){
				throw new Exception('IP白名单添加失败');
			}
			$ret['status']=1;
			$ret['msg']='IP白名单添加成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 修改访问IP白名单
	* @param $ipid IP地址Id号
	* @param $ip 访问IP白名单
	* @param $status 访问IP白名单的状态
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function updateIpLimit($ipid,$ip,$status){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$ip=isset($ip)?trim($ip):'';
			$status=isset($status)?trim($status):0;
			if(empty($ipid) || !is_numeric($ipid) || intval($ipid)!=$ipid){
				throw new Exception('IP ID必须是整数');
			}
			if(empty($ip) || !Comm::is_ip($ip)){
				throw new Exception('IP地址格式错误');
			}
			if(!is_numeric($status) || intval($status)!=$status || ($status!=0 && $status!=1)){
				throw new Exception('IP状态参数错误');
			}
			$sql='update beu_user_ip_limit set IP=\''.$ip.'\',status='.$status.' where id='.$ipid;
			$data=Yii::app ()->db->createCommand ($sql)->execute();
			if(empty($data)){
				throw new Exception('IP白名单修改失败');
			}
			$ret['status']=1;
			$ret['msg']='IP白名单修改成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 删除访问IP白名单
	* @param $ipid IP地址Id号
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function deleteIpLimit($ipid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($ipid) || !is_numeric($ipid) || intval($ipid)!=$ipid){
				throw new Exception('IP ID必须是整数');
			}
			$sql='delete from beu_user_ip_limit where id='.$ipid;
			$data=Yii::app()->db->createCommand ($sql)->execute();
			if($data==0){
				throw new Exception('IP白名单删除失败');
			}
			$ret['status']=1;
			$ret['msg']='IP白名单删除成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}

	/**
	* 获取访问IP白名单
	* @param $userid 用户ID
	* @param $is_status 获取数据模式：0为获取禁用数据，1为获取启用数据，2为获取所有数据 默认为1
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getIpLimit($userid,$is_status=1){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			$status_str='status=1 and ';
			if($is_status==0){
				$status_str='status=0 and ';
			}else if($is_status==2){
				$status_str='';
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_ip_limit' )->where ($status_str.'userid='.$userid)->queryAll ();
			if(count($data)==0){
				throw new Exception('IP白名单获取失败');
			}
			$ret['status']=1;
			$ret['msg']='IP白名单获取成功';
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 关联Action到功能模块
	* @param $prowerid 功能模块ID
	* @param $actionid_str actionID字符串
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function actionToPower($prowerid,$actionid_str){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$prowerid=isset($prowerid)?trim($prowerid):0;
			$actionid_str=isset($actionid_str)?trim($actionid_str):'';
			if(empty($prowerid) || !is_numeric($prowerid) || intval($prowerid)!=$prowerid){
				throw new Exception('功能模块ID必须是整数');
			}
			$actionid_arr=array();
			if(!empty($actionid_str)){
				$actionid_arr=explode(',',$actionid_str);
			}
			if(count($actionid_arr)==0){
				$ret_s=self::setActionidStatusByPower($prowerid);//如果传入关联数据为空就将以前数据全部禁用
				if($ret_s['status']==0){
					throw new Exception($ret_s['msg']);
				}
			}else{
				$add_data=$actionid_arr;//默认添加所有新数据
				$ret_powertoaction_data=self::getActionidByPower($prowerid,2);//如果传入数据不为空就查出其所有数据，包括禁用数据
				if($ret_powertoaction_data['status']==1){
					$ret_powertoaction_data=$ret_powertoaction_data['data'];
					$ret_powertoaction_actionid=array();
					foreach($ret_powertoaction_data as $value){//获取到已经存在的数据
						$ret_powertoaction_actionid[]=$value['actionid'];
					}
					$add_data=array_diff($actionid_arr,$ret_powertoaction_actionid);//获取新数据在原数据中不存在项，也就是需要添加的数据
					$update_data_d=array_diff($ret_powertoaction_actionid,$actionid_arr);//获取原数据在新数据中不存在项，也就是需要禁用的数据
					$update_data_u=array_intersect($ret_powertoaction_actionid,$actionid_arr);//获取原数据与新数据都存在项，也就是需要启用的数据
					$update_id_arr_d=array();
					$update_id_arr_u=array();
					foreach($ret_powertoaction_data as $value){
						if(in_array($value['actionid'],$update_data_d) && $value['status']==1){//判断此数据是否需要禁用
							$update_id_arr_d[]=$value['id'];
						}elseif(in_array($value['actionid'],$update_data_u) && $value['status']==0){//判断此数据是否需要开启
							$update_id_arr_u[]=$value['id'];
						}
					}
					if(count($update_id_arr_d)>0){//需要禁用的数据
						$ret_s=self::setActionidStatusByPower($prowerid,0,$update_id_arr_d);//禁用数据
						if($ret_s['status']==0){
							throw new Exception($ret_s['msg']);
						}
					}
					if(count($update_id_arr_u)>0){//需要开启的数据
						$ret_s=self::setActionidStatusByPower($prowerid,1,$update_id_arr_u);//开启数据
						if($ret_s['status']==0){
							throw new Exception($ret_s['msg']);
						}
					}
				}
				if(count($add_data)>0){//添加新数据
					$ret_s=self::addActionByPower($prowerid,$add_data);
					if($ret_s['status']==0){
						throw new Exception($ret_s['msg']);
					}
				}
			}
			$ret['status']=1;
			$ret['msg']='关联Action到功能模块成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 添加Action到功能模块
	* @param $prowerid 功能模块ID
	* @param $action_arr Action数组
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function addActionByPower($prowerid,$action_arr){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$prowerid=isset($prowerid)?trim($prowerid):0;
			if(empty($prowerid) || !is_numeric($prowerid) || intval($prowerid)!=$prowerid){
				throw new Exception('功能模块ID必须是整数');
			}
			if(!is_array($action_arr) || count($action_arr)==0){
				throw new Exception('Action数组不能为空');
			}
			$insert_value=array();
			foreach($action_arr as $value){
				$arr=array();
				$arr[]=$value;
				$arr[]=$prowerid;
				$insert_value[]='('.implode(',',$arr).')';
			}
			$sql='insert into beu_user_action_prower (actionid,prowerid) values'.implode(',',$insert_value);
			$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
			if($ret_id==0){
				throw new Exception('添加Action到功能模块失败');
			}
			$ret['status']=1;
			$ret['msg']='添加Action到功能模块成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取关联到功能模块Action
	* @param $prowerid 功能模块ID
	* @param $is_status 获取数据模式：0为获取禁用数据，1为获取启用数据，2为获取所有数据 默认为1
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getActionidByPower($prowerid,$is_status=1){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$prowerid=isset($prowerid)?trim($prowerid):0;
			if(empty($prowerid) || !is_numeric($prowerid) || intval($prowerid)!=$prowerid){
				throw new Exception('功能模块ID必须是整数');
			}
			if($is_status!=0 && $is_status!=1 && $is_status!=2){
				$is_status=1;
			}
			$status_str='status=1 and ';
			if($is_status==0){
				$status_str='status=0 and ';
			}else if($is_status==2){
				$status_str='';
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_action_prower' )->where ($status_str.'prowerid='.$prowerid)->queryAll ();
			if(count($data)==0){
				throw new Exception('查询功能模块关联数据失败');
			}
			$ret['status']=1;
			$ret['msg']='查询功能模块关联数据成功';
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	
	/**
	* 关联到功能模块Action设置状态
	* @param $prowerid 功能模块ID
	* @param $is_status 设置状态，0为禁用 1为开启 默认0
	* @param $id_arr id数组 默认为空数组
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function setActionidStatusByPower($prowerid,$is_status=0,$id_arr=array()){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$prowerid=isset($prowerid)?trim($prowerid):0;
			if(empty($prowerid) || !is_numeric($prowerid) || intval($prowerid)!=$prowerid){
				throw new Exception('功能模块ID必须是整数');
			}
			if($is_status!=0 && $is_status!=1){//判断并设置状态
				$is_status=0;
			}
			$id_str='';
			if(is_array($id_arr) && count($id_arr)>0){//拼接禁用模块ID
				$id_str=' and id in('.implode(',',$id_arr).')';
			}
			$sql='update beu_user_action_prower set status='.$is_status.' where prowerid='.$prowerid.$id_str;
			$data=Yii::app ()->db->createCommand ($sql)->execute();
			if($data==0){
				throw new Exception('修改关联数据失败');
			}
			$ret['status']=1;
			$ret['msg']='修改关联数据成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 设置用户禁用的模块
	* @param $userid 用户ID
	* @param $power_str 禁用的模块
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function powerDisable($userid,$power_str){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			$power_str=isset($power_str)?trim($power_str):'';
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			$power_str_arr= !empty($power_str)?explode(',',$power_str):array();
			foreach($power_str_arr as $key=>$value){
				if(empty($value) || !is_numeric($value) || intval($value)!=$value){
					unset($power_str_arr[$key]);
				}
			}
			if(count($power_str_arr)==0){
				$ret_s=self::setPowerDisableStatusByUser($userid);//如果传入禁用模块数据为空就将以前数据全部禁用
				if($ret_s['status']==0){
					throw new Exception($ret_s['msg']);
				}
			}else{
				$add_data=$power_str_arr;//默认添加所有新数据
				$ret_PowerDisable_data=self::getPowerDisableByUser($userid,2);//获取此账户下所有禁用模块数据
				if($ret_PowerDisable_data['status']==1){
					$ret_PowerDisable_id=array();
					$ret_PowerDisable_data=$ret_PowerDisable_data['data'];
					foreach($ret_PowerDisable_data as $value){//获取到已经存在的数据
						$ret_PowerDisable_id[]=$value['powerid'];
					}
					$add_data=array_diff($power_str_arr,$ret_PowerDisable_id);//获取新数据在原数据中不存在项，也就是需要添加的数据
					$update_data_d=array_diff($ret_PowerDisable_id,$power_str_arr);//获取原数据在新数据中不存在项，也就是需要禁用的数据
					$update_data_u=array_intersect($ret_PowerDisable_id,$power_str_arr);//获取原数据与新数据都存在项，也就是需要启用的数据
					$update_id_arr_d=array();
					$update_id_arr_u=array();
					
					foreach($ret_PowerDisable_data as $value){
						if(in_array($value['powerid'],$update_data_d) && $value['status']==1){//判断此数据是否需要禁用
							$update_id_arr_d[]=$value['id'];
						}elseif(in_array($value['powerid'],$update_data_u) && $value['status']==0){//判断此数据是否需要开启
							$update_id_arr_u[]=$value['id'];
						}
					}
					if(count($update_id_arr_d)>0){//需要禁用的数据
						$ret_s=self::setPowerDisableStatusByUser($userid,0,$update_id_arr_d);//禁用数据
						if($ret_s['status']==0){
							throw new Exception($ret_s['msg']);
						}
					}
					if(count($update_id_arr_u)>0){//需要开启的数据
						$ret_s=self::setPowerDisableStatusByUser($userid,1,$update_id_arr_u);//开启数据
						if($ret_s['status']==0){
							throw new Exception($ret_s['msg']);
						}
					}
				}
				if(count($add_data)>0){//添加新数据
					$ret_s=self::addPowerDisable($userid,$add_data);
					if($ret_s['status']==0){
						throw new Exception($ret_s['msg']);
					}
				}
			}
			$ret['status']=1;
			$ret['msg']='修改关联数据成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 用户禁用模块设置状态
	* @param $userid 用户ID
	* @param $is_status 设置状态，0为禁用 1为开启 默认0
	* @param $id_arr id数组 默认为空数组
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function setPowerDisableStatusByUser($userid,$is_status=0,$id_arr=array()){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			if($is_status!=0 && $is_status!=1){
				$is_status=0;
			}
			$id_str='';
			if(is_array($id_arr) && count($id_arr)>0){
				$id_str=' and id in('.implode(',',$id_arr).')';
			}
			$sql='update beu_user_power_disable set status='.$is_status.' where userid='.$userid.$id_str;
			$data=Yii::app ()->db->createCommand ($sql)->execute();
			if($data==0){
				throw new Exception('修改禁用模块数据失败');
			}
			$ret['status']=1;
			$ret['msg']='修改禁用模块数据成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取用户禁用模块数据
	* @param $userid 用户ID
	* @param $is_status 获取数据模式：0为获取禁用数据，1为获取启用数据，2为获取所有数据 默认为1
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getPowerDisableByUser($userid,$is_status=1){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			if($is_status!=0 && $is_status!=1 && $is_status!=2){
				$is_status=1;
			}
			$status_str='status=1 and ';
			if($is_status==0){
				$status_str='status=0 and ';
			}else if($is_status==2){
				$status_str='';
			}
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_power_disable' )->where ($status_str.'userid='.$userid)->queryAll ();
			if(count($data)==0){
				throw new Exception('查询禁用模块数据失败');
			}
			$ret['status']=1;
			$ret['msg']='查询禁用模块数据成功';
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 添加用户禁用模块数据
	* @param $userid 用户ID
	* @param $prower_arr 功能模块数组
	* @return array('status'=>0,'msg'=>'') 成功status为1
	*/
	static function addPowerDisable($userid,$prower_arr){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			if(!is_array($prower_arr) || count($prower_arr)==0){
				throw new Exception('功能模块数组不能为空');
			}
			$insert_value=array();
			foreach($prower_arr as $value){
				$arr=array();
				$arr[]=$userid;
				$arr[]=$value;
				$insert_value[]='('.implode(',',$arr).')';
			}
			$sql='insert into beu_user_power_disable (userid,powerid) values'.implode(',',$insert_value);
			$ret_id=Yii::app ()->db->createCommand ( $sql )->execute ();
			if($ret_id==0){
				throw new Exception('禁用功能模块失败');
			}
			$ret['status']=1;
			$ret['msg']='禁用功能模块成功';
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取模块下的Action详情
	* @param $powerid_str 模块id字符串
	* @param $user_type 用户的权限级别
	* @param $is_status 获取数据模式：0为获取禁用数据，1为获取启用数据，2为获取所有数据 默认为1
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getActionInfoByPower($user_type,$powerid_str='',$is_status=1){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(empty($user_type) || !is_numeric($user_type) || intval($user_type)!=$user_type){
				throw new Exception('用户权限级别必须是整数');
			}
			$status_str='prower.status=1';
			if($is_status==0){
				$status_str='prower.status=0';
			}else if($is_status==2){
				$status_str='';
			}
			if($user_type!=1){//用户访问权限等级不为超级管理员时
				if(empty($powerid_str)){
					throw new Exception('用户没有任何访问权限');
				}
				if($status_str!=''){
					$status_str.=' and ';
				}
				$status_str.='prower.prowerid in('.$powerid_str.')';
			}
			$data=Yii::app ()->db->createCommand ()->select ( 'prower.id,prower.actionid,prower.prowerid,path.title,path.path' )->from ( 'beu_user_action_prower prower' )->join('beu_user_action path','prower.actionid=path.id and path.status=1')->where ($status_str)->queryAll ();
			if(count($data)==0){
				throw new Exception('查询关联Action详情为空');
			}
			
			$ret['status']=1;
			$ret['msg']='查询关联Action详情成功';
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取关联到用户下的Action详情
	* @param $userid 用户ID
	* @param $user_type 用户的权限级别
	* @param $is_status 获取数据模式：0为获取禁用数据，1为获取启用数据，2为获取所有数据 默认为1
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getActionInfoByUser($userid,$user_type,$is_status=1){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$userid=isset($userid)?trim($userid):0;
			if(empty($userid) || !is_numeric($userid) || intval($userid)!=$userid){
				throw new Exception('用户ID必须是整数');
			}
			if(empty($user_type) || !is_numeric($user_type) || intval($user_type)!=$user_type){
				throw new Exception('用户权限级别必须是整数');
			}
			$status_str='prower.status=1';
			if($is_status==0){
				$status_str='prower.status=0';
			}else if($is_status==2){
				$status_str='';
			}
			$action_data=array();
			if($user_type!=1){//用户不为超级管理员时查找禁用权限
				$default_power_id_arr=array();
				$disable_action_arr=array();
				
				$ret_default_power=self::getPower($user_type);//获取用户默认能访问的功能模块
				if($ret_default_power['status']==0){
					throw new Exception($ret_default_power['msg']);
				}
				
				foreach($ret_default_power['data'] as $value){//统计默认模块ID
					$default_power_id_arr[]=$value['id'];
				}
				$ret_PowerDisable=self::getPowerDisableByUser($userid);//获取用户被禁用的功能模块
				if($ret_PowerDisable['status']==1){//清除默认访问里用户被禁用的访问权限
					$ret_PowerDisable_arr=array();
					foreach($ret_PowerDisable['data'] as $value){//将被禁用的功能模块提取出来
						$ret_PowerDisable_arr[]=$value['powerid'];
					}
					$default_power_id_arr2=array_diff($default_power_id_arr,$ret_PowerDisable_arr);//获取默认模块里用户未被禁用的模块
					$ret_PowerDisable_arr=array_diff($ret_PowerDisable_arr,$default_power_id_arr);//获取在默认模块里被禁用的模块
					$default_power_id_arr=$default_power_id_arr2;
					if(count($ret_PowerDisable_arr)>0){//查找被禁掉的action
						$ret_disable_arr=self::getActionInfoByPower($user_type,implode(',',$ret_PowerDisable_arr));
						if($ret_disable_arr['status']==1){
							$disable_action_arr=$ret_disable_arr['data'];
						}
					}
				}
				$data=self::getActionInfoByPower($user_type,implode(',',$default_power_id_arr));//查找默认能访问的action
				if($data['status']==0 || count($data['data'])==0){
					throw new Exception('查询关联Action详情为空');
				}
				if(count($disable_action_arr)>0){//删除默认访问里的被禁掉的action
					foreach($disable_action_arr as $value){
						foreach($data['data'] as $default_key=>$default_value){
							if($value['actionid']==$default_value['actionid']){
								unset($data['data'][$default_key]);
							}
						}
					}
					if(count($data['data'])>0){
						throw new Exception('整理后的关联Action详情为空');
					}
				}
				$action_data=$data['data'];
			}
			
			$ret['status']=1;
			$ret['msg']='查询关联Action详情成功';
			$ret['data']=$action_data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取用户权限级别列表
	* @return array('status'=>0,'msg'=>'','data'=>array()) 成功status为1 有data数据
	*/
	static function getPowerConfigList(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$data=Yii::app ()->db->createCommand ()->select ( '*' )->from ( 'beu_user_config' )->order('rand asc')->queryAll ();
			if(count($data)==0){
				throw new Exception('获取权限级别失败');
			}
			$ret['status']=1;
			$ret['data']=$data;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 获取用户可访问的权限
	* @return array('status'=>0,'msg'=>'','data'=>array(),'user_type'=>0) 成功status为1 有data数据,用户权限
	*/
	static function getPowerAll(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			if(!isset($_SESSION['user_id'])){
				throw new Exception('用户ID不能为空');
			}
			$userid=$_SESSION['user_id'];
			$ret_judge_bool=permission::judgeUserIP();//获取用户是否需要判断来源IP
			if($ret_judge_bool['status']==0){
				throw new Exception($ret_judge_bool['msg']);
			}
			if($ret_judge_bool['is_bool_ip']){//需要判断来源IP
				$ret_ip=self::getIpLimit($userid);//获取用户的IP白名单
				if($ret_ip['status']==0 && !isset($ret_ip['data'])){
					throw new Exception($ret_ip['msg']);
				}
				$is_bool=false;
				foreach($ret_ip['data'] as $value){
					if($value['IP']==Comm::getSourceIp()){
						$is_bool=true;
						break;
					}
				}
				if(!$is_bool){//未在IP白名单里找到来源IP
					throw new Exception('来源IP未添加到用户的IP白名单里');
				}
			}
			$user_type=$ret_judge_bool['user_type'];//用户权限级别
			if($user_type!=1){//不为超级管理员时才查询其访问权限
				$cache=Yii::app()->cache->get(CacheName::getCacheName('user_action_Info').$userid.$user_type);
				if($cache===false){
					$ret_Action=self::getActionInfoByUser($userid,$user_type);
					if($ret_Action['status']==0){
						throw new Exception($ret_Action['msg']);
					}
					Yii::app()->cache->set(CacheName::getCacheName('user_action_Info').$userid.$user_type, $ret_Action, 300);//设置用户的可访问页面列表缓存
				}else{
					$ret_Action=$cache;
				}
				if($user_type==Yii::app()->params['main_type'] && $_SESSION['sub_id']!=0){//当期帐号权限为总屏权限时,并且切换到了子屏
					$ret_Action=self::getActionInfoByUser($_SESSION['sub_id'],Yii::app()->params['sub_type']);//获取子屏访问权限
				}
				$ret['data']=$ret_Action['data'];
			}
			$ret['status']=1;
			$ret['user_type']=$user_type;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	
	/**
	* 判断传入路径是否可访问 
	* @return 可访问返回true否则返回false
	**/
	public static function selectAction($action_url=''){
		$is_bool=false;
		try{
			if((isset($_SESSION['Disable']) && $_SESSION['Disable']) || Yii::app()->params['user_type']==1){//当用户权限为超级管理员时不用判断是否可访问
				$is_bool=true;
			}else{
				if(count(Yii::app()->params['power_action'])==0){
					throw new Exception('可访问地址列表为空！');
				}
				$action_url=trim($action_url);//去除前后空格
				if(empty($action_url) || strpos($action_url,'http://')!==false){
					throw new Exception('路径为空或格式错误！');
				}
				$find=array('/?','?','&','=');
				$action_url=str_replace($find,'/',$action_url);//将路径规范化
				
				$url_str_arr=explode('/',$action_url);
				$first_str=trim($url_str_arr[0]);
				if(empty($first_str)){//当第一个字段为空时将其删除
					unset($url_str_arr[0]);
				}
				if(count($url_str_arr)<2){//当数组的元素个数小于2时表示路径规则错误
					throw new Exception('路径格式错误！');
				}
				
				//将数据整合为路径
				$url_str_arr_i=1;
				$action_url=array('');
				foreach($url_str_arr as $value){
					$action_url[]=$value;
					if($url_str_arr_i==2){
						break;
					}
					$url_str_arr_i++;
				}
				$action_url=join('/',$action_url);
				//在可访问路径列表里查找当前路劲
				foreach(Yii::app()->params['power_action'] as $value){
					if($value['path']==$action_url){
						$is_bool=true;
						break;
					}
				}
			}
		}catch(Exception $e){
			$is_bool=false;
			$e->getMessage();
		}
		return $is_bool;
	}
	/**
	* 插件调用记录
	* @return array('status'=>0,'msg'=>'') 成功status为1
	**/
	public static function beuhistoryapi($apitype,$brandid,$clothesid,$clotheslevle,$hairid,$color,$touchid,$dpid){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$array_insert=array();
			$apitype=trim($apitype);
			$array_insert['apitype']=(!is_numeric($apitype) || intval($apitype)!=$apitype || $apitype<0)?0:$apitype;

			$brandid=trim($brandid);
			$array_insert['brandid']=(!is_numeric($brandid) || intval($brandid)!=$brandid || $brandid<0)?0:$brandid;

			$clothesid=trim($clothesid);
			$array_insert['clothesid']=empty($clothesid)?'':$clothesid;

			$clotheslevle=trim($clotheslevle);
			$array_insert['clotheslevle']=empty($clotheslevle)?'':$clotheslevle;

			$hairid=trim($hairid);
			$array_insert['hairid']=(!is_numeric($hairid) || intval($hairid)!=$hairid || $hairid<0)?0:$hairid;

			$color=trim($color);
			$array_insert['color']=empty($color)?'':$color;

			$touchid=trim($touchid);
			$array_insert['touchid']=(!is_numeric($touchid) || intval($touchid)!=$touchid || $touchid<0)?0:$touchid;
			
			$dpid=trim($dpid);
			$array_insert['dpid']=(!is_numeric($dpid) || intval($dpid)!=$dpid || $dpid<0)?0:$dpid;
			
			$array_insert['datetime']=date('Y-m-d H:i:s');
			
			$command = Yii::app()->db->createCommand();
			$command->insert('beu_history_api', $array_insert);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
}