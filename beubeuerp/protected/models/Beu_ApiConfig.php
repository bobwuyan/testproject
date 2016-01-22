<?php
	
	/**
	* 接口配置的查询 调用  
	**/
	class Beu_ApiConfig{
		//接口配置数组
		public $api_config_arr=array();
		//构造函数 创建对象时调用
		function __construct(){
			$this->api_config_arr=include_once('/protected/config/Api_config.php');
			foreach($this->api_config_arr as $config_key=>$config_value){
				unset($this->api_config_arr[$config_key]['Tmall']['brandkey']);
			}
			$brand_docking_ret=Brand::getbranddocking('*',array('status'=>'=1'));
			if($brand_docking_ret['status']==1){
				foreach($brand_docking_ret['data'] as $value){
					$is_bool=false;
					foreach($this->api_config_arr as $config_key=>$config_value){
						if($value['brandid']==$config_value['brandid']){
							if(!isset($this->api_config_arr[$config_key]['Tmall'])){
								$this->api_config_arr[$config_key]['Tmall']=array();
							}
							$this->api_config_arr[$config_key]['Tmall']['brandkey']=$value['tamllid'];
							$is_bool=true;
						}
					}
					if(!$is_bool){
						$this->api_config_arr[]=array(
							'brandid'=>$value['brandid'],
							'Tmall'=>array(
								'brandkey'=>$value['tamllid'],
								)
							);
					}
				}
			}
		}
		/**
		* 根据品牌KEY 获取品牌的配置数据
		* @parm key 
		* @return 返回对应的 配置数据 不存在报错
		**/
		function getApiConfigByKey($key){
			$ret=array('status'=>0,'msg'=>'');
			try{
				foreach($this->api_config_arr as $config_key=>$config_value){
					//查找key对应的配置
					if(isset($config_value['APIkey']) && $config_value['APIkey']==$key){
						$ret['data']=$this->api_config_arr[$config_key];
						break;
					}
				}
				//配置不存在
				if(!isset($ret['data'])){
					throw new Exception('暂未定义API KEY');
				}
				$ret['status']=1;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		/**
		* 根据品牌Id 获取品牌的配置数据
		* @parm key 
		* @return 返回对应的 配置数据 不存在报错
		**/
		function getApiConfigById($brandid){
			$ret=array('status'=>0,'msg'=>'');
			try{
				foreach($this->api_config_arr as $config_key=>$config_value){
					//查找key对应的配置
					if(isset($config_value['brandid']) && $config_value['brandid']==$brandid){
						$ret['data']=$this->api_config_arr[$config_key];
						break;
					}
				}
				//配置不存在
				if(!isset($ret['data'])){
					throw new Exception('品牌暂未做配置');
				}
				$ret['status']=1;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		
		
		
		
		/**
		* 设置接口单日访问数量
		**/
		static function setApiRequest($key,$app,$request_num=0){
			$ret=array('status'=>0,'msg'=>'');
			try{
				$api_ret=self::getApiRequest(array('app'=>'=\''.$app.'\'','app_key'=>'=\''.$key.'\''));
				$history_id=0;
				//添加新的接口数量限制
				if($api_ret['status']==0){
					Yii::app ()->db->createCommand ('insert into beu_api_request_config(app_key,app,Request_num,Request_sum,up_date) values(\''.$key.'\',\''.$app.'\',1,1,\''.date('Y-m-d H:i:s').'\')')->execute();
					$insertid=Yii::app()->db->getLastInsertID();
					//添加请求日志
					$history_ret=self::addhistory($insertid);
					if($history_ret['status']==1){
						$history_id=$history_ret['data'];//返回日志id
					}
				}else if(($request_num>0 && $request_num>$api_ret['data'][0]['Request_num']) || $request_num==0){
					Yii::app ()->db->createCommand ('update beu_api_request_config set Request_num=Request_num+1,Request_sum=Request_sum+1,up_date=\''.date('Y-m-d H:i:s').'\' where id='.$api_ret['data'][0]['id'])->execute();
					//添加请求日志
					$history_ret=self::addhistory($api_ret['data'][0]['id']);
					if($history_ret['status']==1){
						$history_id=$history_ret['data'];//返回日志id
					}
				}else{
					throw new Exception('单日请求超过上限');
				}
				$ret['status']=1;
				$ret['history_id']=$history_id;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		/**
		* 清0接口单日访问数量
		* @parm key 可选 
		* @parm app 可选 接口名
		**/
		static function delApiRequest($key='',$app=''){
			$ret=array('status'=>0,'msg'=>'');
			try{
				$where =' where 1';
				if(!empty($key)){
					$where.=' and app_key=\''.$key.'\'';
				}
				if(!empty($app)){
					$where.=' and app=\''.$app.'\'';
				}
				Yii::app ()->db->createCommand ('update beu_api_request_config set Request_num=0'.$where)->execute();
				$ret['status']=1;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		/**
		* 获取接口单日访问数量
		* @parm search 可选 数组 搜索条件 及其值
		* @parm order 可选 排序
		**/
		static function getApiRequest($search=array(),$order='id desc'){
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
					->select ( '*' )
					->from ( 'beu_api_request_config' )
					->where ($sel_where)
					->order($order)
					->queryAll ();
				if(empty($data)){
					throw new Exception('接口未设置访问数量');
				}
				$ret['status']=1;
				$ret['data']=$data;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		/**
		* 添加日志
		**/
		static function addhistory($id){
			$ret=array('status'=>0,'msg'=>'');
			try{
				Yii::app ()->db->createCommand ('insert into beu_api_request_config_history(api_request_id,add_date) values('.$id.',\''.date('Y-m-d H:i:s').'\')')->execute();
				$insertid=Yii::app()->db->getLastInsertID();
				$ret['status']=1;
				$ret['data']=$insertid;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		/**
		* 记录请求 返回数据进日志
		**/
		static function updatehistory($id,$request_data,$return_data,$return_type=''){
			$ret=array('status'=>0,'msg'=>'');
			try{
				$Format=array(
					'\\'=>'\\\\'
				);
				$request_data=strtr($request_data,$Format);
				$return_data=strtr($return_data,$Format);
				//表示数据需要增量修改
				if($return_type=='+'){
					$data=Yii::app ()->db->createCommand ()
					->select ( 'request_data,return_data' )
					->from ( 'beu_api_request_config_history' )
					->where ('id='.$id)
					->queryRow ();
					$request_data=$data['request_data'].$request_data;
					$return_data=$data['return_data'].$return_data;
				}
				Yii::app ()->db->createCommand ('update beu_api_request_config_history set request_data=\''.$request_data.'\',return_data=\''.$return_data.'\' where id='.$id)->execute();
				$ret['status']=1;
				$ret['data']=$id;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
		/**
		* 添加自动任务日志
		**/
		static function addAutomatichistory($request_data,$return_data,$action){
			$ret=array('status'=>0,'msg'=>'');
			try{
				Yii::app ()->db->createCommand ('insert into beu_api_automatic(action,request_data,return_data,adddate) values(\''.$action.'\',\''.$request_data.'\',\''.$return_data.'\',\''.date('Y-m-d H:i:s').'\')')->execute();
				$insertid=Yii::app()->db->getLastInsertID();
				$ret['status']=1;
				$ret['data']=$insertid;
			}catch(Exception $e){
				$ret['msg']=$e->getMessage();
			}
			return $ret;
		}
	}
?>