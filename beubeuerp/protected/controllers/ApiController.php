<?php
class ApiController extends Controller {
	public $touchid=0;
	public function init() {
		//此Cookie在每个Controller 里必须加载 用于域名的绑定
		$cookie = new CHttpCookie('img_server_host',Yii::app()->params['img_server_host']);
		$cookie->expire = time()+3600*24;  //有限期1天
		Yii::app()->request->cookies['img_server_host']=$cookie;
		$cookie = new CHttpCookie('web_server_host',Yii::app()->params['web_server_host']);
		$cookie->expire = time()+3600*24;  //有限期1天
		Yii::app()->request->cookies['web_server_host']=$cookie;
		
		if(Yii::app()->request->getParam('touchid')){
			$this->touchid=Yii::app()->request->getParam('touchid');
		}elseif(Yii::app()->request->getParam('tid')){
			$this->touchid=Yii::app()->request->getParam('tid');
		}
	}

	/**********************************************API接口*******************************************************/
	/***********************************定时任务 start*****************/
	/**
	* 自动获取可用接口品牌
	**/
	public function actionAutomatic_beu_Apikey_list(){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$app=trim(Yii::app()->request->getParam('AppName'));
			if(empty($app)){
				throw new Exception('API 名为空！');
			}
			//验证key值是否存在
			$api_config=new Beu_ApiConfig();
			$config=$api_config->api_config_arr;//所有的配置数据
			$api_key_list=array();
			foreach($config as $key => $value){
				if(isset($value['brandid']) && isset($value['touchid']) && isset($value['APIkey']) && isset($value[$app]) && isset($value[$app]['Status']) && !empty($value[$app]['Status'])){
					$api_key_list[]=$value['APIkey'];
				}
			}
			if(count($api_key_list)==0){
				throw new Exception('接口未做配置');
			}
			$ret['data']=$api_key_list;
			$ret['AppName']=$app;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		$ret=json_encode($ret);
		Beu_ApiConfig::addAutomatichistory($app,$ret,'Automatic_beu_Apikey_list');
		echo $this->renderPartial('/api_php/beu_Api',array('ret'=>$ret));
		
	}
	/**
	* 自动获取可用接口
	**/
	public function actionAutomatic_beu_Api_list(){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$app=trim(Yii::app()->request->getParam('AppName'));
			$AppKey_unicode=trim(Yii::app()->request->getParam('AppKey'));
			$AppKey=$this->unicode_decode($AppKey_unicode);
			if(empty($app) || empty($AppKey)){
				throw new Exception('API 名为空！');
			}
			$ret['data']=array('AppName'=>$app,'AppKey'=>$AppKey_unicode);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		$ret=json_encode($ret);
		Beu_ApiConfig::addAutomatichistory(json_encode(array('AppName'=>$app,'AppKey'=>$AppKey)),$ret,'Automatic_beu_Api_list');
		echo $this->renderPartial('/api_php/beu_Api_push',array('ret'=>$ret));
	}
	/**
	* 自动添加搭配缓存
	**/
	public function actionAutomatic_beu_for_saved(){
		Beu_ApiConfig::addAutomatichistory('','','Automatic_beu_for_saved');
		echo $this->renderPartial('/api_php/beu_for_saved');
	}
	/***********************************定时任务 end*****************/
	
	
	/**
	* http请求
	**/
	public function http_post($url,$postfields){
		$post_data = '';
		 foreach($postfields as $key=>$value){
			 $post_data .="$key=".urlencode($value)."&";}
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, $url);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		 curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		 //指定post数据
		 curl_setopt($ch, CURLOPT_POST, true);
		 //添加变量
		 curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data,0,-1));
		 $output = curl_exec($ch);
		 $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		 //echo $httpStatusCode;
		 curl_close($ch);
		 return $output;
	}
	/**
	* 将UNICODE编码后的内容进行解码
	**/
	function unicode_decode($name){
		//转换编码，将Unicode编码转换成可以浏览的utf-8编码
		$pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
		preg_match_all($pattern, $name, $matches);
		if (!empty($matches)){
			$name = '';
			for ($j = 0; $j < count($matches[0]); $j++){
				$str = $matches[0][$j];
				if (strpos($str, '\\u') === 0){
					$code = base_convert(substr($str, 2, 2), 16, 10);
					$code2 = base_convert(substr($str, 4), 16, 10);
					$c = chr($code).chr($code2);
					$c = iconv('UCS-2', 'UTF-8', $c);
					$name .= $c;
				}else{
					$name .= $str;
				}
			}
		}
		return $name;
	}
	/**
	* 重置接口的访问数据
	**/
	public function actionResetApiNumber(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			Beu_ApiConfig::delApiRequest();//重置所有接口访问数
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		if(Yii::app()->request->getParam('Automatic')){
			echo '<script>window.opener=null;window.open(\'\',\'_self\');window.close();</script>';
		}else{
			echo json_encode($ret);
		}
	}
	/**
	* 重置接口衣服数据
	**/
	public function actionResetApiClothes(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$brandid=trim(Yii::app()->request->getParam('brandid'));
			Beu_ApiConfig::delApiRequest();//重置所有接口访问数
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 调用API 接口并验证
	* @param app 接口名
	* @param key 接口密钥
	* @param datetime 接口请求时间
	* @param sig 验证码 加密规则 md5(app+key+beubeu+datetime) 最后转大写
	* @param data 接口数据
	* @param callback JSON回调
	**/
	public function actiongetApi(){
		$ret=array('code'=>'-1','msg'=>array());
		//请求数据
		$Request_arr=array();
		
		$history_id=0;//日志编号
		try{
			$app=trim(Yii::app()->request->getParam('app'));
			$key=trim(Yii::app()->request->getParam('key'));
			$sig=trim(Yii::app()->request->getParam('sig'));
			$datetime=trim(Yii::app()->request->getParam('date'));
			$data=trim(Yii::app()->request->getParam('data'));
			//记录日志
			$Request_arr=array_merge($_POST,$_GET);
			$current_time=time();//获取当前时间
			//验证数据有效性
			if(empty($app)){
				throw new Exception('API传递参数错误 -- app！');
			}
			//判断接口是否存在
			if(!method_exists($this,$app)){
				throw new Exception('API接口不存在！');
			}
			if(empty($key)){
				throw new Exception('API传递参数错误 -- key');
			}
			
			//验证key值是否存在
			$api_config=new Beu_ApiConfig();
			$config_ret=$api_config->getApiConfigByKey($key);
			if($config_ret['status']==0){
				throw new Exception('API接口配置不完整 -- key！');
			}
			if(!isset($config_ret['data']['touchid']) && empty($config_ret['data']['touchid'])){
				throw new Exception('API接口配置不完整 -- touchid！');
			}
			if(!isset($config_ret['data']['brandid']) && empty($config_ret['data']['brandid'])){
				throw new Exception('API接口配置不完整 -- brandid！');
			}
			//获取接口数据限制数量
			$app_max=(isset($config_ret['data'][$app]['Data_Max']) && $config_ret['data'][$app]['Data_Max']>0)?$config_ret['data'][$app]['Data_Max']:0;
			//获取接口单日请求上限
			$app_Request_max=(isset($config_ret['data'][$app]['Request_Max']) && $config_ret['data'][$app]['Request_Max']>0)?$config_ret['data'][$app]['Request_Max']:0;
			
			//设置接口访问次数
			$ApiRequest_ret=Beu_ApiConfig::setApiRequest($key,$app,$app_Request_max);
			if($ApiRequest_ret['status']==0){
				throw new Exception('API单日调用次数超限，最大次数：'.$app_Request_max);
			}
			//取得日志编号
			if(isset($ApiRequest_ret['history_id'])){
				$history_id=$ApiRequest_ret['history_id'];
			}
			
			if(empty($datetime)){
				throw new Exception('API传递参数错误 -- date！');
			}
			//请求时间不能大于当前时间 并且 请求时间在5分钟内才有效
			if(($current_time+150)<$datetime || ($datetime+150)<$current_time){
				throw new Exception('API传递参数错误 -- date！server time:'.date('Y-m-d H:i:s',$current_time).'  Request time:'.date('Y-m-d H:i:s',$datetime).' 时间误差超过左右2.5分钟');
			}
			$ret_sig=$this->sevaed_sig('',array('app'=>$app,'key'=>$key,'date'=>$datetime,'str'=>'beubeu'));
			if($ret_sig['status']==0){
				throw new Exception($ret_sig['msg']);
			}
			if(empty($sig) || $ret_sig['data']!=strtoupper($sig)){
				throw new Exception('API传递参数错误 -- sig！');
			}
			
			//日志编号计入
			$config_ret['data']['history_id']=$history_id;
			//调用API接口
			$ret=$this->$app($data,$config_ret['data'],$app_max);
			
		}catch(Exception $e){
			$ret['msg']['error']=$e->getMessage();
		}
		$ret=json_encode($ret);
		//jsonp 反回数据
		if (Yii::app()->request->getParam('callback')) {
			$ret=Yii::app()->request->getParam('callback') . '(' .$ret . ')';
		}
		//日志编号不为空 将请求 及其返回数据记录进数据库
		if(!empty($history_id)){
			$ret_d=Beu_ApiConfig::updatehistory($history_id,json_encode($Request_arr),$ret,'+');
		}
		echo $ret;
	}
	/**
	* 生成sig接口
	**/
	public function actionsevaveSIG(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$brandid=trim(Yii::app()->request->getParam('brandid'));
			$appname=trim(Yii::app()->request->getParam('appname'));
			$str=trim(Yii::app()->request->getParam('str'));
			if(empty($brandid)){
				throw new Exception('品牌为空');
			}
			if(empty($appname)){
				throw new Exception('接口名为空');
			}
			if(empty($str)){
				$str='beubeu';
			}
			$Beu_ApiConfig=new Beu_ApiConfig();
			$config=$Beu_ApiConfig->getApiConfigById($brandid);
			if($config['status']==0){
				throw new Exception('获取接口密码错误');
			}
			$datetime=time();
			$SIG_Format='';
			if(isset($config['data']['SIG_Format']) && !empty($config['data']['SIG_Format'])){
				$SIG_Format=$config['data']['SIG_Format'];
			}
			//生成sig
			$ret_sig=$this->sevaed_sig($SIG_Format,array('app'=>$appname,'key'=>$config['data']['APIkey'],'date'=>$datetime,'str'=>$str));
			if($ret_sig['status']==0){
				throw new Exception($ret_sig['msg']);
			}
			$ret['data']=array('sig'=>$ret_sig['data'],'date'=>$datetime,'apikey'=>$config['data']['APIkey']);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 生成sig
	* @param SIG_Format sig生成格式 如果为空 使用百一的格式生成
	* @param pram 数组 生成sig需要的参数
	**/
	Private function sevaed_sig($SIG_Format,$pram){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sig='';
			if(!isset($pram['app']) || empty($pram['app'])){
				throw new Exception('SIG生成错误 -- app！');
			}
			if(!isset($pram['key']) || empty($pram['key'])){
				throw new Exception('SIG生成错误 -- key！');
			}
			if(!isset($pram['date']) || empty($pram['date'])){
				throw new Exception('SIG生成错误 -- date！');
			}
			if(!isset($pram['str'])){
				throw new Exception('SIG生成错误 -- str！');
			}
			if(empty($SIG_Format)){
				$sig=strtoupper(md5($pram['app'].$pram['key'].$pram['str'].$pram['date']));
			}else{
				$Format=array(
					'_APP_'=>$pram['app'],
					'_KEY_'=>$pram['key'],
					'_DATE_'=>$pram['date'],
					'_STR_'=>$pram['str'],
				);
				$sig=strtoupper(md5(strtr($SIG_Format,$Format)));
			}
			$ret['status']=1;
			$ret['data']=$sig;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
	/**
	* 推送品牌官网数据
	**/
	Private function beu_PushWebsite($data,$config,$app_max){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$ret['msg']=array('success'=>array(1,2,3),'fail'=>array(4));
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		return $ret;
	}
}