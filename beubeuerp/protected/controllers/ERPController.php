<?php
require_once 'protected/qiniuSDK/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class ERPController extends Controller {
	public $layout = '/layouts/columnAdmin'; //设置调用的布局模板
	public $ret_msg='';//错误信息
	public $login_type = false;//是否C#登录
	public $Website_template='SKC';//调用网站模版类型
	
	//不登陆可访问
	public $interface_arr=array(
		'/erp/updatedescription',
		'/erp/PushWebsite'
	);
	/*
	'/erp/login',
		'/erp/loginout',
		'/erp/getlogin',
		'/erp/getClothesByBrandnumberOrOrder',//C# 获取衣服列表
		'/tmalladmin/addblobclothes',//检查衣服图片并修改天猫单品状态
		'/tmalladmin/addtmallclothes',//根据条件将衣服加入到天猫
		'/tmalladmin/getbatchlist',//获取品牌可用批次列表
		'/tmalladmin/batchbinding',//天猫批次绑定
		'/tmalladmin/PushTmallDp',//推送已有搭配给天猫
		'/tmalladmin/setClothingline',//设置卡线位置
		'/tmalladmin/getClothingline',
		'/tmalladmin/SetClothesStatus',
		'/tmalladmin/ccc',
		'/erp/delwatermark'
		
	*/
	
	/**
	 * 初始化函数
	 */
	public function init() {
		//$_SESSION['brandid']=192;
		
		//Yii::app()->cache->flush(); 
		$this->login_type=Yii::app()->request->getParam('login_type');//C# 软件登录
		
		//此Cookie在每个Controller 里必须加载 用于域名的绑定
		$cookie = new CHttpCookie('img_server_host',Yii::app()->params['img_server_host']);
		$cookie->expire = time()+3600*24;  //有限期1天
		Yii::app()->request->cookies['img_server_host']=$cookie;
		$cookie = new CHttpCookie('web_server_host',Yii::app()->params['web_server_host']);
		$cookie->expire = time()+3600*24;  //有限期1天
		Yii::app()->request->cookies['web_server_host']=$cookie;
		
		/**************************获取 设置 网站模版类型 start *************************************/
		$http_cookie = Yii::app()->request->getCookies();
		if(!empty($http_cookie['Website_template']->value)){
			$this->Website_template=$http_cookie['Website_template']->value;
		}
		$http_cookie = new CHttpCookie('Website_template', $this->Website_template);
		//定义cookie的有效期
		$http_cookie->expire = time()+60*60;  //有限期1小时
		//把cookie写入cookies使其生效
		Yii::app()->request->cookies['Website_template']=$http_cookie;
		/**************************获取 设置 网站模版类型 end *************************************/
		
		$status=0;//0表示登录错误，1 表示 权限错误
		$REDIRECT_URL=isset($_SERVER['REDIRECT_URL'])?$_SERVER['REDIRECT_URL']:$_SERVER['REQUEST_URI'];
		//当前URL规范
		if(!empty($REDIRECT_URL)){
			$REDIRECT_URL =str_replace('?','/',$REDIRECT_URL);
			$REDIRECT_URL=explode('/',$REDIRECT_URL);
			if(!isset($REDIRECT_URL[2]) || empty($REDIRECT_URL[2])){
				$this->renderPartial ('/erp/login');
				exit();
			}
			if(count($REDIRECT_URL)>1){
				$REDIRECT_URL='/'.$REDIRECT_URL[1].'/'.$REDIRECT_URL[2];
			}
		}
		
		try{
			if(!isset($_SESSION)){
				session_start();
			}
			//验证账号登录状态，不需要修改账户登录时间 也不需要记录日志
			if($REDIRECT_URL=='/erp/GetUserType' && isset($_SESSION['user_id'])){
				throw new Exception('验证账号登录状态，不需要修改账户登录时间');
			}
			usercookie::userCheckCookie();//获取登录状态
			//echo session_id();//exit();
			$_SESSION['touchid_new']=isset($_SESSION['touchid_new'])?$_SESSION['touchid_new']:0;
			$_SESSION['Disable']=false;//是否禁用权限管理
			
			//判断用户是否登陆
			if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) ){
				$user=Yii::app()->request->getParam('upwo');
				if(!$user){
					throw new Exception('对不起，您尚未登录系统！');
				}else{
					throw new Exception('自动登录失败！');
				}
			}
			//获取用户访问权限
			$user=WebUser::getusersById($_SESSION['user_id']);
			if($user['status']==0){
				throw new Exception($user['msg']);
			}
			if(empty($user['data']->touchid)){
				throw new Exception('帐号未绑定搭配屏');
			}
			$touch_arr=json_decode($user['data']->touchid,true);//用户绑定的搭配屏
			$touch_ret=Touch::touchSelectById($touch_arr[0]);//根据搭配屏获取品牌
			if($touch_ret['status']==0){
				throw new Exception($touch_ret['msg']);
			}
			$_SESSION['brandid']=$touch_ret['data']['brandid'];
			if(empty($_SESSION['brandid'])){
				throw new Exception('帐号未绑定品牌');
			}
			$_SESSION['type']=$user['data']->type;
			//判断用户的访问权限是否可以访问系统
			$user_type=isset($_SESSION['type'])?$_SESSION['type']:0;
			$user_type=permission::userTypeChange($user_type,$user['data']->ERP3_status);//权限转换
			if(empty($user_type) || $user_type > 70 || $user_type<=50){//只有ERP3.0账户可访问
				throw new Exception('对不起，您的权限不能访问系统！');
			}
			Yii::app()->params['user_type']=$user_type;
			
			$cookie = new CHttpCookie('user_type',$user_type);
			$cookie->expire = time()+60*60*2;  //有限期2分钟
			Yii::app()->request->cookies['user_type']=$cookie;
			if($user_type>1){
				
				//将获取到的品牌存入cookie,以供前台js读取
				$cookie = new CHttpCookie('brand',$_SESSION['brandid']);
				$cookie->expire = time()+60*2;  //有限期2分钟
				Yii::app()->request->cookies['brand']=$cookie;
				$arr=explode(':',$_SERVER['HTTP_HOST']);
				if((isset($_SESSION['Disable']) && $_SESSION['Disable'])){
					$status=1;
					throw new Exception('权限被禁用或域名不正确');
				}
				//echo "wb1";exit();
				$ret=Beu_Power::getPowerAll();//获取用户可访问的页面
				if($ret['status']==0){
					throw new Exception($ret['msg']);
				}
				
				Yii::app()->params['power_action']=isset($ret['data'])?$ret['data']:array();//将权限列表存入全局
				//print_r(Yii::app()->params['power_action']);
				if(!Beu_Power::selectAction($REDIRECT_URL)){//查询当前路径是否可访问
					$status=1;
					throw new Exception('对不起，本账号无此权限');
				}
			}
			$this->setHistory();//设置日志
		}catch(Exception $e){
			if($REDIRECT_URL!='/erp/GetUserType'){
				$arr=explode(':',$_SERVER['HTTP_HOST']);
				$this->ret_msg=$e->getMessage();
				$ret_a=Beu_Power::getActionInfoByPower(2,1,2);//获取不登陆可访问的链接
				$gc_ret=Beu_Power::getActionInfoByPower(2,45,2);//工厂软件使用 工厂软件不验证登陆
				$action_path=array();
				if($ret_a['status']==1){
					foreach($ret_a['data'] as $value){
						$action_path[]=$value['path'];
					}
					foreach($gc_ret['data'] as $value){
						$action_path[]=$value['path'];
					}
					foreach($this->interface_arr as $value){
						$action_path[]=$value;
					}
				}else{
					$this->ret_msg=$ret_a['msg'];
				}
				if((!isset($_SESSION['Disable']) || !$_SESSION['Disable']) && $REDIRECT_URL!='' && !in_array($REDIRECT_URL,$action_path)){//判断当前用户是有有权限访问 当前链接
					Yii::app()->params['power_action']=array();
					$this->__errorview($status,$this->ret_msg);
				}
			}
		}
	}
	
	
	
	/**
	* 错误页面
	* @param is_ajax 强制以json格式返回
	* @param main_callback 是否返回父框架 此值表示返回父框架的哪一个方法
	**/
	public function __errorview($status,$ret_msg){
		//$_SERVER['HTTP_X_REQUESTED_WITH'] ajax提交时的特定使用 用于判断需要返回错误的格式
		if((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || Yii::app ()->request->getParam ( 'is_ajax' )){//判断是否ajax请求 如果是ajax就返回json数组，否则返回错误页面
			$ret_str=json_encode(array('status'=>0,'msg'=>$ret_msg));
			if(Yii::app ()->request->getParam ( 'main_callback' )){//需要调用父框架里方法时使用
				echo '<script>parent.'.Yii::app ()->request->getParam ( 'main_callback' ).'('.$ret_str.')</script>';
			}else{
				echo $ret_str;
			}
		}else{
			//$status 1为权限不够 0为未登录 其他为错误提示
			$this->render('error',array('msg'=>$ret_msg,'status'=>$status));
		}
		exit();
	}
	
	/*
	 * 七牛自定义域名接口
	 */
  public function actionsetdomainbyqiniu(){
  		$ret=array('status'=>0,'msg'=>'Set failure!');
		try{
			$phppost=new PhpPost();//post提交
	  		$qiniu=new erp_qiniu();//七牛的接口类
	  		$f_access_token='';
	  		$f_username='2210108192@qq.com';
	  		$f_password='baiyi1716';//$f_bucket='baiyitest';
	  		
	  		 //父级获取账户凭证
	        $f_access_token=$qiniu->oauth2_token($f_username,$f_password);
	     
	        if(empty($f_access_token))
	        {
	        	throw new Exception('Set failure!');
	        }
	  	
	        $brandid=0;//品牌ID号
	  		$access_token='';
	  		$username='test5@beubeu.com';//test5@beubeu.com
	  		$password='123456789';//123456789
	  		$uid=0;
	  		$bucket='baiyitest2';//baiyitest2
	  		$domain='www.beubeu.com';//自定义域名www.beubeu.com
	  		$icp='沪ICP备10035694号';//域名的备案号沪ICP备10035694号
			if (Yii::app ()->request->getParam ( 'brandid' )) {
				$brandid =  Yii::app ()->request->getParam ( 'brandid' );
			}
	  		if (Yii::app ()->request->getParam ( 'username' )) {
				$username =  Yii::app ()->request->getParam ( 'username' );
			}
	 		 if (Yii::app ()->request->getParam ( 'password' )) {
				$password =  Yii::app ()->request->getParam ( 'password' );
			}
	  		if (Yii::app ()->request->getParam ( 'bucket' )) {
				$bucket =  Yii::app ()->request->getParam ( 'bucket' );
			}
	 		 if (Yii::app ()->request->getParam ( 'domain' )) {
				$domain =  Yii::app ()->request->getParam ( 'domain' );
			}
	 		 if (Yii::app ()->request->getParam ( 'icp' )) {
				$icp =  Yii::app ()->request->getParam ( 'icp' );
			}
			if(empty($brandid) || empty($username) || empty($password) || empty($bucket) || empty($domain) || empty($icp))
			{
				throw new Exception('请将数据信息填写完成后再设置!');
			}
			
	 		 //子账户获取账户凭证
	        $access_token=$qiniu->oauth2_token($username,$password);
	  		if(empty($access_token))
	        {
	        	throw new Exception('Set failure!');
	        }
	        
	       if(!empty($access_token))
	       {
	       		//获取账户信息
	       		$access_token='Bearer '.$access_token;
				$user_array=$qiniu->user_info($username,$password,$access_token); 
				if(count($user_array)>0 && isset($user_array['uid']))
		        {
		       		$uid=$user_array['uid'];
		       		$f_access_token='Bearer '.$f_access_token;
		       		//自定义域名
		       		$url = "https://api.qiniu.com/v6/oem/user/".$uid."/bucket/".$bucket."/customdomain";
		        	$data = array('domain'=>$domain,'icp'=>$icp);
		      		$data = http_build_query($data);   
					$domain_result = $phppost->curl_post($url, $data,$f_access_token);
					
					$domain_result_array= json_decode($domain_result,true);
					
					if(count($domain_result_array)>0)
					{
						if(isset($domain_result_array['success']) && $domain_result_array['success']==true)
						{   
							try {
								//通过品牌ID修改设置的域名信息
								erp_qiniu_account::model ()->updateAll ( array ('domain' => $domain ), 'brandid=:textx', array (':textx' => $brandid ) );
							} catch ( BeubeuException $e ) {
								throw new Exception('Set failure!');
							}
							$ret['msg']='set success';
							$ret['status']=1;
						}else{
							$ret['msg']=$domain_result_array['message'];
						}
					}
					
			  		
		        }
	       }
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
  		echo json_encode($ret);
  		exit();
    }
	
	/**
	* 帮助页面
	**/
	public function actionhelpbasicinformation(){
		$this->render('error',array('msg'=>"建设中......",'status'=>2));
	}
	
	public function getIP() { 
		if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) 
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
		else if (@$_SERVER["HTTP_CLIENT_IP"]) 
		$ip = $_SERVER["HTTP_CLIENT_IP"]; 
		else if (@$_SERVER["REMOTE_ADDR"]) 
		$ip = $_SERVER["REMOTE_ADDR"]; 
		else if (@getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR"); 
		else if (@getenv("HTTP_CLIENT_IP")) 
		$ip = getenv("HTTP_CLIENT_IP"); 
		else if (@getenv("REMOTE_ADDR")) 
		$ip = getenv("REMOTE_ADDR"); 
		else 
		$ip = "Unknown"; 
		return $ip; 
	}
	//日志插入
	public function setHistory(){
//			$REDIRECT_URL=isset($_SERVER['REDIRECT_URL'])?$_SERVER['REDIRECT_URL']:'';
//				//当前URL规范
//				if(!empty($REDIRECT_URL)){
//					$REDIRECT_URL=explode('/',$REDIRECT_URL);
//					if(count($REDIRECT_URL)>1){
//						$REDIRECT_URL='/'.$REDIRECT_URL[1].'/'.$REDIRECT_URL[2];
//					}
//				}
//			echo $_SESSION['user']."//".$_SESSION['user_id']."//".$_SESSION['type']."//".$_SESSION['account'];
//			echo '<p>';
//			echo $this->getIP();
//			echo '<p>';
//			echo $REDIRECT_URL;
//			echo '<p>';
			$url=  'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;//用户访问的URL
			$url2 =str_replace('?','/',$_SERVER['REQUEST_URI']);
			$REDIRECT_URL=explode('/',$url2);
			if(count($REDIRECT_URL)>1){
				$REDIRECT_URL='/'.$REDIRECT_URL[1].'/'.$REDIRECT_URL[2];
			}
//			echo $REDIRECT_URL;exit();
//			echo '<p>';
//			var_dump($_SESSION['touchidd']);
//			echo '<p>';
//			var_dump($_SESSION['touchid_new']);
//			echo '<p>';
			//echo $REDIRECT_URL;exit();
			//print_r($_SESSION);exit();
			$_SESSION['touchid_new']=isset($_SESSION['touchid_new'])?$_SESSION['touchid_new']:0;
			if($REDIRECT_URL != '/admin/getusername'){
			//$touchid_new = 854;
			$command = Yii::app()->db->createCommand();
			$array = array ("usertype" => $_SESSION['type'], "userid" => $_SESSION['user_id'], "useraccount" => empty($_SESSION['account'])?0:$_SESSION['account'], "ip" => $this->getIP(), "url" => $url, 'date' => date("Y-m-d H:i:s"), 'action' => $REDIRECT_URL, 'touchid' => $_SESSION['touchid_new']);
			$command->insert('beu_history', $array);
			//print_r($array);
			
			}
			
			//exit();
	}
	
	
	/**
	* 获取用户的登录状态
	**/
	public function actionGetUserType(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$ps=new permission();
			$ps_ret=$ps->usermodeSeleteStatus($_SESSION['userid']);
			if(!isset($ps_ret[0]['sessionid']) || $ps_ret[0]['sessionid']!=session_id()){
				throw new Exception('账户已在其他地方登录');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	//获取用户信息
	public function actiongetusername() {
		$arr["uname"] =  isset($_SESSION['user'])?$_SESSION['user']:'';
		$arr["typetype"] =  isset($_SESSION['type'])?$_SESSION ["type"]:0;
		$arr["xiazai_v"] =  isset($_SESSION['xiazai_v'])?$_SESSION ["xiazai_v"]:0;
		if(isset($_SESSION['power']) && !empty($_SESSION['power'])){
			$arr["power"] =  $_SESSION['power'];
		}else{
			$arr["power"] = '';
		}
		$arr['is_push']= isset($_SESSION['is_push'])?$_SESSION['is_push'].'':'0';
		$arr['sub_main']='';
		if(!empty($_SESSION['sub_id'])){//判断当前使用子帐号
		}
		echo json_encode($arr);
	}
	/**
	 * 验证表单
	 * */
	public function actiongetlogin(){
		$ret=array('status'=>1,'msg'=>'','data'=>10);
		try{
			$unmae = trim(urldecode(Yii::app()->request->getParam("unmae")));
			$upwo = Yii::app()->request->getParam('upwo');
			$code = Yii::app()->request->getParam('code');
			if(!$this->login_type && $code != $_SESSION['Checknum']){
				$ret['data']=1;  //验证码错误
				throw new Exception('验证码错误');
			}
			$ret=permission::userLogin($unmae,$upwo);
			if($this->login_type){//C# 软件登录
				$ret['msg']=$ret['data'];
				$ret['data']=array();
				if($ret['msg']==4){
					//获取用户访问权限
					$user=WebUser::getusersById($_SESSION['user_id']);
					if($user['status']==0){
						throw new Exception(2);
					}
					if(empty($user['data']->touchid)){
						throw new Exception(6);
					}
					$touch_arr=json_decode($user['data']->touchid,true);//用户绑定的搭配屏
					$touch_ret=Touch::touchSelectById($touch_arr[0]);//根据搭配屏获取品牌
					if($touch_ret['status']==0 || empty($touch_ret['data']['brandid'])){
						throw new Exception(6);
					}
					try{
						$brand_ret=Brand::brandSelectById($touch_ret['data']['brandid']);
						if(count($brand_ret)==0){
							throw new Exception('');
						}
						$qiniu=new erp_qiniu();//七牛的接口类
						$brand_qiniu_account=$qiniu->getAccountByBrand($touch_ret['data']['brandid']);//获取品牌的七牛子账号信息
						if(count($brand_qiniu_account)==0){
							throw new Exception('品牌暂未绑定七牛帐号');
						}
						$ret['data']['domain']=$brand_qiniu_account[0]['domain'];
						$ret['data']['brandname']=$brand_ret[0]['name'];
						$ret['data']['erpak']=empty($brand_ret[0]['erpak'])?'':$brand_ret[0]['erpak'];
						$ret['data']['erpsk']=empty($brand_ret[0]['erpsk'])?'':$brand_ret[0]['erpsk'];
						$ret['data']['code_start']=$brand_ret[0]['code_start'];
						$ret['data']['code_end']=$brand_ret[0]['code_end'];
					}catch(Exception $e){
						throw new Exception(6);
					}
					$ret['data']['brandid']=$touch_ret['data']['brandid'];
					$ret['data']['type']=$_SESSION['type'];
				}
			}else{
				$ret['href']='/erp/manage';
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['mag']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	 * 退出登录
	 * */
	public function actionloginout(){
		usercookie::userLoginOut();
		$this->redirect('/erp/login');
	}
	/**
	 * 登入页
	 * */
	public function actionlogin(){
		$ret_login=usercookie::userCheckCookie();
		if($ret_login){//已登录帐号 直接跳转到首页
			$user_type=isset($_SESSION['type'])?$_SESSION['type']:0;
			$this->redirect('/erp/manage');
		}else{
			$this->renderPartial('/erp/login',array('ret_msg'=>$this->ret_msg));
		}
	}
	/**
	* 图片管理列表
	**/
	function actionmanage(){
		if($this->Website_template!='SKC'){
			$this->manageSKU();
			exit();
		}
		$ret=array('status'=>0,'msg'=>'','Website_template'=>$this->Website_template);
		try{
			$page=Yii::app()->request->getParam("page");
			$sort=Yii::app()->request->getParam("sort");
			$Auditing=Yii::app()->request->getParam("Auditing");//审核状态
			$category=Yii::app()->request->getParam("category");//分类
			$order_date=Yii::app()->request->getParam("order_date");//订单时间
			$search_type=Yii::app()->request->getParam("search_type");//搜索类别
			$search_val=Yii::app()->request->getParam("search_val");//搜索内容
			$patform_val=Yii::app()->request->getParam("patform_val");//平台
			
			if(empty($page)){
				$page=1;
			}
			if(empty($sort)){
				$sort='down';
			}
			$ret['sort']=$sort;
			$ret['search_val']=$search_val;//搜索内容
			$ret['search_type']=$search_type;//搜索类别
			$ret['Auditing']=$Auditing;//审核状态
			$ret['category']=$category;//分类
			$ret['order_date']=$order_date;//订单时间
			$ret['patform_val']=$patform_val;//平台筛选
			
			$limit=20;
			//获取用户信息
			$user_ret=WebUser::getusersall('beu_users.id='.$_SESSION['user_id']);
			if($user_ret['status']==0){
				throw new Exception($user_ret['msg']);
			}
			
			unset($user_ret['data'][0]['pwd']);
			$ret['user']=$user_ret['data'][0];
			//获取品牌名
			$brand_ret=Brand::brandSelectById($_SESSION['brandid']);
			if(count($brand_ret)==0){
				throw new Exception('');
			}
			$ret['user']['brandname']=$brand_ret[0]['name'];
			
			//获取平台ID
			$patform=new patformclass();
			$patform_ret=$patform->select_erp_patform();//获取所有平台
			
			$cut_detaile=new cut_detaileclass();
			$cut_ret=$cut_detaile->select_erp_cut_detaile($_SESSION['brandid']);//获取品牌所有的裁剪模版
			
			$patform_to_cut=array();
			foreach($cut_ret as $value){
				foreach($patform_ret as $p_value){
					if($value['patformid']==$p_value['id']){
						$patform_to_cut[$p_value['name']]=$p_value['Englishname'].'_'.$value['id'];
					}
				}
			}
			$ret['patform']=$patform_to_cut;
			
			$clothes_where_arr=array();//衣服列表查询条件
			if(!empty($order_date)){
				$clothes_where_arr['startime']=$order_date;
				$clothes_where_arr['endtime']=$order_date;
			}
			if(!empty($category)){
				$clothes_where_arr['brandcategoryid']=$category;
			}
			if(!empty($Auditing)){
				$clothes_where_arr['imagestatus']=$Auditing;
			}
			if(!empty($search_val)){
				if(empty($search_type)){
					$clothes_where_arr['search']=array('brandnumber'=>'like_%'.$search_val.'%','erp_order.ordername'=>'like_%'.$search_val.'%');
				}else if($search_type=='brandnumber'){
					$clothes_where_arr['brandnumber']='like_%'.$search_val.'%';
				}else{
					$clothes_where_arr['erp_order.ordername']='like_%'.$search_val.'%';
				}
			}
			if(!empty($ret['patform_val'])){//平台筛选不为空 需要筛选
				$patform_val_arr=explode('_',$ret['patform_val']);
				
				$pushDetaile=new pushDetaileclass();
				$pushDetaile_ret=$pushDetaile->select_all_push($_SESSION['brandid'],array('cutid'=>$patform_val_arr[1],'distinct'=>'skc'));
				foreach($pushDetaile_ret['data'] as $value){
					$sk_array[]=$value['count_skc'];
				}
				if(count($sk_array)>0){
					$clothes_where_arr['brandnumber'.($patform_val_arr[2]==0?'_not':'')]=$sk_array;
				}elseif($patform_val_arr[2]==1){
					$clothes_where_arr['id']=0;
				}
			}
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr,$limit,$limit*($page-1),$sort=='down'?'erp_order.addtime desc':'erp_order.addtime asc');
			
			$c_order_name=array();//订单序号
			//查询单品的图片
			$c_img_data=array();
			foreach($c_ret['data'] as $value){
				$c_img_data[$value['id']]=array('url'=>'','pcount'=>0,'mcount'=>0,'dcount'=>0,'img_type'=>-1,'img_data'=>'2035-12-31 00:00:00','skc'=>$value['brandnumber']);
				$c_order_name[$value['orderid']]=$value['orderid'];
			}
			$clothesimage=new clothesimageclass();
			$img_ret=$clothesimage->select_all_image(array_keys($c_img_data),-1,array('isshow'=>0),'','id asc');
			foreach($img_ret as $value){
				switch ($value->type){
					case 0: 
						$value->status==0?$c_img_data[$value->clothes_order_id]['pcount']++:'';//灰模图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(0,1)) && strtotime($c_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 1: 
						$value->status==0?$c_img_data[$value->clothes_order_id]['pcount']++:'';//立体图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(1)) && strtotime($c_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 2: 
						$value->status==0?$c_img_data[$value->clothes_order_id]['pcount']++:'';//静态图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(0,1,2)) && strtotime($c_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 3: 
						$value->status==0?$c_img_data[$value->clothes_order_id]['mcount']++:'';//模特图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(0,1,2,3)) && strtotime($c_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 4: 
						$value->status==0?$c_img_data[$value->clothes_order_id]['dcount']++:'';//细节图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(0,1,2,4)) && strtotime($c_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
				}
			}
			//获取分类
			$brandcategory=new brandcategoryclass();
			$b_ret=$brandcategory->select_category($_SESSION['brandid']);
			$b_data=array();
			if(count($b_ret)>0){
				foreach($b_ret as $b_value){
					$b_data[$b_value->id]=$b_value->name;
				}
			}
			
			//分页
			$criteria = new CDbCriteria();
			$pages=new CPagination($c_ret['page_sum']);
			$pages->pageSize=$limit;
			$pages->applyLimit($criteria);
			
			
			//获取订单时间列表
			$order_date_list=orderclass::select_order_date($_SESSION['brandid']);
			
			//获取订单名
			
			$C_oreder_ret=orderclass::selectOrderNameByOrderID($_SESSION['brandid'],$c_order_name);
			
			foreach($C_oreder_ret as $value){
				$c_order_name[$value['id']]=$value['ordername'];
			}
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			
			$ret['domain']=$brand_qiniu_account[0]['domain'];
			$ret['order_date_list']=$order_date_list;
			$ret['img_data']=$c_img_data;
			$ret['category_data']=$b_data;
			$ret['data']=$c_ret['data'];
			$ret['order_name']=$c_order_name;
			$ret['pages']=$pages;
			if(count($c_ret['data'])==0){
				throw new Exception('查询无果');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['mag']=$e->getMessage();
		}
		$this->render ( 'manage', $ret);
	}
	/**
	* 图片管理列表 SKU版
	**/
	function manageSKU(){
		$ret=array('status'=>0,'msg'=>'','Website_template'=>$this->Website_template);
		try{
			$page=Yii::app()->request->getParam("page");
			$sort=Yii::app()->request->getParam("sort");
			$Auditing=Yii::app()->request->getParam("Auditing");//审核状态
			$category=Yii::app()->request->getParam("category");//分类
			$order_date=Yii::app()->request->getParam("order_date");//订单时间
			$search_type=Yii::app()->request->getParam("search_type");//搜索类别
			$search_val=Yii::app()->request->getParam("search_val");//搜索内容
			$patform_val=Yii::app()->request->getParam("patform_val");//平台
			
			if(empty($page)){
				$page=1;
			}
			if(empty($sort)){
				$sort='down';
			}
			$ret['sort']=$sort;
			$ret['search_val']=$search_val;//搜索内容
			$ret['search_type']=$search_type;//搜索类别
			$ret['Auditing']=$Auditing;//审核状态
			$ret['category']=$category;//分类
			$ret['order_date']=$order_date;//订单时间
			$ret['patform_val']=$patform_val;//平台筛选
			
			$limit=20;
			//获取用户信息
			$user_ret=WebUser::getusersall('beu_users.id='.$_SESSION['user_id']);
			if($user_ret['status']==0){
				throw new Exception($user_ret['msg']);
			}
			
			unset($user_ret['data'][0]['pwd']);
			$ret['user']=$user_ret['data'][0];
			//获取品牌名
			$brand_ret=Brand::brandSelectById($_SESSION['brandid']);
			if(count($brand_ret)==0){
				throw new Exception('');
			}
			$ret['user']['brandname']=$brand_ret[0]['name'];
			
			//获取平台ID
			$patform=new patformclass();
			$patform_ret=$patform->select_erp_patform();//获取所有平台
			
			$cut_detaile=new cut_detaileclass();
			$cut_ret=$cut_detaile->select_erp_cut_detaile($_SESSION['brandid']);//获取品牌所有的裁剪模版
			
			$patform_to_cut=array();
			foreach($cut_ret as $value){
				foreach($patform_ret as $p_value){
					if($value['patformid']==$p_value['id']){
						$patform_to_cut[$p_value['name']]=$p_value['Englishname'].'_'.$value['id'];
					}
				}
			}
			
			$ret['patform']=$patform_to_cut;
			
			$clothes_where_arr=array();//衣服列表查询条件
			if(!empty($order_date)){
				$clothes_where_arr['startime']=$order_date;
				$clothes_where_arr['endtime']=$order_date;
			}
			if(!empty($category)){
				$clothes_where_arr['brandcategoryid']=$category;
			}
			if(!empty($Auditing)){
				$clothes_where_arr['imagestatus']=$Auditing;
			}
			if(!empty($search_val)){
				if(empty($search_type)){
					$clothes_where_arr['search']=array('sku'=>'like_%'.$search_val.'%','erp_order.ordername'=>'like_%'.$search_val.'%');
				}else if($search_type=='brandnumber'){
					$clothes_where_arr['sku']='like_%'.$search_val.'%';
				}else{
					$clothes_where_arr['erp_order.ordername']='like_%'.$search_val.'%';
				}
			}
			if(!empty($ret['patform_val'])){//平台筛选不为空 需要筛选
				$patform_val_arr=explode('_',$ret['patform_val']);
				
				$pushDetaile=new pushDetaileclass();
				$pushDetaile_ret=$pushDetaile->select_all_push($_SESSION['brandid'],array('cutid'=>$patform_val_arr[1],'distinct'=>'sku'));
				foreach($pushDetaile_ret['data'] as $value){
					$sk_array[]=$value['count_sku'];
				}
				if(count($sk_array)>0){
					$clothes_where_arr['sku'.($patform_val_arr[2]==0?'_not':'')]=$sk_array;
				}elseif($patform_val_arr[2]==1){
					$clothes_where_arr['id']=0;
				}
			}
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr,$limit,$limit*($page-1),$sort=='down'?'erp_order.addtime desc':'erp_order.addtime asc','SKU');
			
			$SKU_array=array();//分页查询后的SKU数组
			$c_order_name=array();//订单序号
			//查询单品的图片
			$c_img_data=array();
			$SKU_key_arr=array();
			$SKU_date_arr=array();//用于二次排序
			foreach($c_ret['data'] as $key=>$value){
				$c_img_data[$value['id']]=$value['sku'];
				$SKU_array[]=$value['sku'];
				$clothes_where_arr['sku']=$SKU_array;
				$SKU_key_arr[$value['sku']]=array('key'=>$key,'o_addtime'=>$value['o_addtime']);
				$SKU_date_arr[$key]=$value['o_addtime'];
			}
			//获取SKU对应的所有的SKC数据
			$c_skc_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr,-1,-1);
			$Skc_img_data=array();
			$SKC_to_SKU=array();
			$Skc_img_num_data=array();//图片数量
			$SKU_order_data=array();//SKU对应的订单
			$SKU_order_date_data=array();//SKU对应的订单时间
			foreach($c_skc_ret['data'] as $value){
				
				$Skc_img_data[$value['id']]=array('url'=>'','pcount'=>0,'mcount'=>0,'dcount'=>0,'img_type'=>-1,'img_data'=>'2035-12-31 00:00:00');
				$SKC_to_SKU[$value['sku']][]=$value['id'];
				$Skc_img_num_data[$value['id']]=array('pcount'=>0,'mcount'=>0,'dcount'=>0);
				$c_order_name[$value['orderid']]=$value['orderid'];
				if(!in_array($value['orderid'],$SKU_order_data[$value['sku']])){
					$SKU_order_data[$value['sku']][]=$value['orderid'];
					$SKU_order_date_data[$value['sku']][]=$value['o_addtime'];
				}
			}
			
			
			//获取款号下所有图片
			$clothesimage=new clothesimageclass();
			$img_ret=$clothesimage->select_all_image(array_keys($Skc_img_data),-1,array('isshow'=>0),'','id asc');
			$img_ret_data=array();//将款号的图片统计到一起
			foreach($img_ret as $value){
				switch ($value->type){
					case 0: 
						$Skc_img_num_data[$value->clothes_order_id]['pcount']++;
						$value->status==0?$Skc_img_data[$value->clothes_order_id]['pcount']++:'';//灰模图
						if(!empty($value->url) && !in_array($Skc_img_data[$value->clothes_order_id]['img_type'],array(0,1)) && strtotime($Skc_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$Skc_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$Skc_img_data[$value->clothes_order_id]['url']=$value->url;
							$Skc_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 1: 
						$Skc_img_num_data[$value->clothes_order_id]['pcount']++;
						$value->status==0?$Skc_img_data[$value->clothes_order_id]['pcount']++:'';//立体图
						if(!empty($value->url) && !in_array($Skc_img_data[$value->clothes_order_id]['img_type'],array(1)) && strtotime($Skc_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$Skc_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$Skc_img_data[$value->clothes_order_id]['url']=$value->url;
							$Skc_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 2: 
						$Skc_img_num_data[$value->clothes_order_id]['pcount']++;
						$value->status==0?$Skc_img_data[$value->clothes_order_id]['pcount']++:'';//静态图
						if(!empty($value->url) && !in_array($Skc_img_data[$value->clothes_order_id]['img_type'],array(0,1,2)) && strtotime($Skc_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$Skc_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$Skc_img_data[$value->clothes_order_id]['url']=$value->url;
							$Skc_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 3: 
						$Skc_img_num_data[$value->clothes_order_id]['mcount']++;
						$value->status==0?$Skc_img_data[$value->clothes_order_id]['mcount']++:'';//模特图
						if(!empty($value->url) && !in_array($Skc_img_data[$value->clothes_order_id]['img_type'],array(0,1,2,3)) && strtotime($Skc_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$Skc_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$Skc_img_data[$value->clothes_order_id]['url']=$value->url;
							$Skc_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 4: 
						$Skc_img_num_data[$value->clothes_order_id]['dcount']++;
						$value->status==0?$Skc_img_data[$value->clothes_order_id]['dcount']++:'';//细节图
						if(!empty($value->url) && !in_array($Skc_img_data[$value->clothes_order_id]['img_type'],array(0,1,2,4)) && strtotime($Skc_img_data[$value->clothes_order_id]['img_data'])>strtotime($value->addtime)){
							$Skc_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$Skc_img_data[$value->clothes_order_id]['url']=$value->url;
							$Skc_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
				}
			}
			
			//统计SKU下未审核的图片数量
			foreach($c_img_data as $key=>$value){
				$SKU_data=array('url'=>'','pcount'=>0,'mcount'=>0,'dcount'=>0,'img_type'=>-1);
				foreach($SKC_to_SKU[$value] as $SKC_key=>$SKC_value){
					if(!empty($Skc_img_data[$SKC_value]['url'])){
						if(($Skc_img_data[$SKC_value]['img_type']==0 && !in_array($SKU_data['img_type'],array(0,1))) || ($Skc_img_data[$SKC_value]['img_type']==1 && !in_array($SKU_data['img_type'],array(1))) || ($Skc_img_data[$SKC_value]['img_type']==2 && !in_array($SKU_data['img_type'],array(0,1,2))) || ($Skc_img_data[$SKC_value]['img_type']==3 && !in_array($SKU_data['img_type'],array(0,1,2,3))) || ($Skc_img_data[$SKC_value]['img_type']==4 && !in_array($SKU_data['img_type'],array(0,1,2,4)))){
							$SKU_data['img_type']=$Skc_img_data[$SKC_value]['img_type'];
							$SKU_data['url']=$Skc_img_data[$SKC_value]['url'];
						}
					}
					$SKU_data['pcount']+=$Skc_img_data[$SKC_value]['pcount'];
					$SKU_data['mcount']+=$Skc_img_data[$SKC_value]['mcount'];
					$SKU_data['dcount']+=$Skc_img_data[$SKC_value]['dcount'];
				}
				$c_img_data[$key]=$SKU_data;
			}
			
			//统计SKU下图片数量
			foreach($c_ret['data'] as $key=>$value){
				$c_ret['data'][$key]['pcount']=0;
				$c_ret['data'][$key]['mcount']=0;
				$c_ret['data'][$key]['dcount']=0;
				foreach($SKC_to_SKU[$value['sku']] as $SKC_key=>$SKC_value){
					$c_ret['data'][$key]['pcount']+=$Skc_img_num_data[$SKC_value]['pcount'];
					$c_ret['data'][$key]['mcount']+=$Skc_img_num_data[$SKC_value]['mcount'];
					$c_ret['data'][$key]['dcount']+=$Skc_img_num_data[$SKC_value]['dcount'];
				}
				$c_ret['data'][$key]['orderid']=$SKU_order_data[$value['sku']];
				$c_ret['data'][$key]['o_addtime']=$SKU_order_date_data[$value['sku']];
			}
			
			//获取分类
			$brandcategory=new brandcategoryclass();
			$b_ret=$brandcategory->select_category($_SESSION['brandid']);
			$b_data=array();
			if(count($b_ret)>0){
				foreach($b_ret as $b_value){
					$b_data[$b_value->id]=$b_value->name;
				}
			}
			
			//分页
			$criteria = new CDbCriteria();
			$pages=new CPagination($c_ret['page_sum']);
			$pages->pageSize=$limit;
			$pages->applyLimit($criteria);
			
			//获取订单时间列表
			$order_date_list=orderclass::select_order_date($_SESSION['brandid']);
			//获取订单名
			$C_oreder_ret=orderclass::selectOrderNameByOrderID($_SESSION['brandid'],$c_order_name);
			
			foreach($C_oreder_ret as $value){
				$c_order_name[$value['id']]=$value['ordername'];
			}
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			
			$ret['domain']=$brand_qiniu_account[0]['domain'];
			$ret['order_date_list']=$order_date_list;
			$ret['img_data']=$c_img_data;
			$ret['category_data']=$b_data;
			$ret['data']=$c_ret['data'];
			$ret['order_name']=$c_order_name;
			$ret['pages']=$pages;
			if(count($c_ret['data'])==0){
				throw new Exception('查询无果');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['mag']=$e->getMessage();
		}
		$this->render ( 'manage', $ret);
	}
	/**
	* 获取订单下款对应的所有款色号
	**/
	public function actiongetSkcAll(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sku=Yii::app()->request->getParam("sku");
			$orderid=Yii::app()->request->getParam("orderid");
			$clothes_where_arr=array();
			$clothes_where_arr['sku']=$sku;
			$clothes_where_arr['erp_order.id']=$orderid;
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr);
			$skc_arr=array();
			foreach($c_ret['data'] as $value){
				$skc_arr[]=$value['brandnumber'];
			}
			$ret['data']=$skc_arr;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['mag']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* C# 获取衣服列表
	**/
	public function actiongetClothesByBrandnumberOrOrder(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$page=Yii::app()->request->getParam("page");
			$brandid=Yii::app()->request->getParam("brandid");
			$search_type=Yii::app()->request->getParam("search_type");//搜索类别
			$search_val=strtolower(Yii::app()->request->getParam("search_val"));//搜索内容
			if(empty($page)){
				$page=1;
			}
			if(empty($brandid)){
				throw new Exception('品牌未设置');
			}
			$clothes_where_arr=array();
			if(!empty($search_val)){
				if(empty($search_type)){
					$clothes_where_arr['search']=array('sku'=>'like_%'.$search_val.'%','erp_order.ordername'=>'like_%'.$search_val.'%');
				}else if($search_type=='brandnumber'){
					$clothes_where_arr['sku']='like_%'.$search_val.'%';
				}else{
					$clothes_where_arr['erp_order.ordername']='like_%'.$search_val.'%';
				}
			}
			$limit=20;
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_all_clothes($brandid,0,$clothes_where_arr,-1,-1);
			
			//获取品牌名
			$brand_ret=Brand::brandSelectById($brandid);
			if(count($brand_ret)==0){
				throw new Exception('');
			}
			$ret['data']['brandname']=$brand_ret[0]['name'];
			$code_start=$brand_ret[0]['code_start'];
			$code_end=$brand_ret[0]['code_end'];
			$brand_number_data=array();
			$brandnumber_arr=array();
			if(!empty($code_start) || !empty($code_end)){//品牌未设置款号规则
				//合并SKU
				foreach($c_ret['data'] as $value){
					//根据品牌的款号规则截取款号字符段
					$brandnumber_str=$value['sku'];
					if((empty($search_type) || $search_type=='brandnumber') && !empty($search_val) && strstr(strtolower($brandnumber_str),$search_val)===false){
						continue;
					}
					if(in_array($brandnumber_str,$brandnumber_arr)){
						continue;
					}
					$brand_number_data[]=array('brandnumber'=>$brandnumber_str);
				}
			}else{
				foreach($c_ret['data'] as $value){
					//根据品牌的款号规则截取款号字符段
					$brand_number_data[]=array('brandnumber'=>$value['brandnumber']);
				}
			}
			
			$ret['data']=$brand_number_data;
			$ret['page_sum']=count($brand_number_data);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['mag']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	//查询裁剪图
	public function actiongetClothescutByAllBarcode(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$code=Yii::app()->request->getParam("code");
			if(empty($code)){
				throw new Exception('code is empty');
			}
			$brandid=Yii::app()->request->getParam("brandid");
			if(empty($brandid)){
				throw new Exception('品牌未设置');
			}
			$cuttype=Yii::app()->request->getParam("cuttype");
			if(empty($cuttype)){
				throw new Exception('裁剪类型未设置');
			}
	
			$code_str = "";
			$arr =explode(",",$code);
			foreach($arr as $value){
				//echo $value;
				if($code_str == ""){
					$code_str = "erp_push_detaile.sku ='".$value."' ";
					
				}else{
					
					$code_str =$code_str."|| erp_push_detaile.sku ='".$value."' ";
				}			
			}
			
			
			
			$where = "({$code_str}) and erp_push_detaile.brandid ={$brandid} and erp_push_detaile.cutid ={$cuttype}";
			$oj = Yii::app()->db->createCommand()
			->select('
			erp_push_detaile.id,
			erp_push_detaile.cutid,
			erp_push_detaile.watermarkid,
			erp_push_detaile.imageid,
			erp_push_detaile.sku,
			erp_push_detaile.skc,
			erp_push_detaile.sort,
			erp_push_detaile.top,
			erp_push_detaile.positionx,
			erp_push_detaile.positiony,
			erp_push_detaile.CutOut_width,
			erp_push_detaile.CutOut_height,
			erp_push_detaile.width as c_width,
			erp_push_detaile.height as c_height,
			erp_push_detaile.img_w,
			erp_push_detaile.img_h,
			erp_push_detaile.cut_src,
			erp_image.name,
			erp_image.type,
			erp_image.url
			')
			->from('erp_push_detaile')
			->join("erp_image",'erp_image.id=erp_push_detaile.imageid',array())
			->where($where)
			->order('erp_push_detaile.id desc')
			->queryAll();
			if(empty($oj)){
				throw new Exception('当前选择的款号下没有可下载的图片');
			}
			
			
			
			foreach($oj as $key=>$value){
				//查询裁剪平台参数
						$oj2 = Yii::app()->db->createCommand()
						->select('*')
						->from('erp_cut_detaile')
						->where('id ='.$value['cutid'])
						->queryRow();
						
						//查询水印
						$oj3 = Yii::app()->db->createCommand()
						->select('*')
						->from('erp_watermark')
						->where('id ='.$value['watermarkid'])
						->queryRow();
						if(empty($oj3)){
							$oj3["url"] = "";
							$value['watermarkid'] =0;
						}
						
						//查询平台信息
						$oj4 = Yii::app()->db->createCommand()
						->select('*')
						->from('erp_patform')
						->where('id ='.$oj2['patformid'])
						->queryRow();
				if($value['cut_src']== ""){
					try{
						/**
						 * $array 裁剪信息
						 * {watermark_id	水印ID号，为0代表无水印
						 * watermark_width 水印图片的宽
						 * watermark_height 水印图片的高
						 * watermark_url	水印图片的地址
						 * watermark_positionx 水印图片x坐标
						 * watermark_positiony 水印图片y坐标
						 * patform_width 平台的宽
						 * patform_height 平台的高
						 * image_url 推送的图片地址
						 * cut_positionx 裁剪的x坐标
						 * cut_positiony 裁剪的y坐标
						 * cut_width 裁剪的宽
						 * cut_height 裁剪的高}
						 * $brandid品牌ID
						 */
					$array=array(
							"watermark_id"=>$value['watermarkid'],
							"watermark_width"=>$oj2['watermarkwidth'],
							"watermark_height"=>$oj2['watermarkheight'],
							"watermark_url"=>$oj3["url"],
							"watermark_positionx"=>$oj2['positionx'],
							"watermark_positiony"=>$oj2['positiony'],
							"patform_width"=>$oj2['width'],
							"patform_height"=>$oj2['height'],
							"image_url"=>$value['url'],
							"cut_positionx"=>$value['positionx'],
							"cut_positiony"=>$value['positiony'],
							"cut_width"=>$value['c_width'], 
							"cut_height"=>$value['c_height'],
							"img_w"=>$value['img_w'], 
							"img_h"=>$value['img_h']
							);
								$pushdetaile_class=new pushDetaileclass();
								$pushdetaile_ret=$pushdetaile_class->cut_image($array,$brandid);
								if($pushdetaile_ret['status']==0){
									throw new Exception($pushdetaile_ret['msg']);
								}
								$ret['data']=$pushdetaile_ret['url'];
								//echo "url".$ret['data']."<p></p>";
								$oj[$key]["cut_src"] = $pushdetaile_ret['url'];
								$oj[$key]["pt"] = $oj4['Englishname'];
								$ret['status']=1;
								
							}catch(Exception $e){
								$ret['msg']=$e->getMessage();
							}
					
				}else{
					//查询平台域名
						$oj5 = Yii::app()->db->createCommand()
						->select('*')
						->from('erp_qiniu_account')
						->where('brandid ='.$brandid)
						->queryRow();
					$oj[$key]["cut_src"] = $oj5['domain'].$value['url'];
					$oj[$key]["pt"] = $oj4['Englishname'];
				}
				
				
			}
			
			/*$id ="";
			foreach($oj as $value){
				
				if($id == ""){
					
					$id = $value['id'];
				}else{
					
					$id= $id.",".$value['id'];
				}
			}
			$where2 ="clothes_order_id in(".$id.") and isshow =0 and erp_image.status =1";
			$oj2 = Yii::app()->db->createCommand()
			->select('*')->from('erp_image')
			->join("erp_clothes_order",'erp_image.clothes_order_id=erp_clothes_order.id',array())
			//->join("erp_push_detaile",'erp_image.id=erp_push_detaile.imageid',array())
			->where($where2)
			->order('erp_image.id desc')
			->queryAll();
			
			if(empty($oj2)){
				throw new Exception('数据为空-002');
			}*/
				$ret['data'] = $oj;
				$ret['status']=1;
		}catch(Exception $e){
				$ret['mag']=$e->getMessage();
			}
		echo json_encode($ret);
	}
	
	public function actiongetClothesByAllBarcode(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$code=Yii::app()->request->getParam("code");
			if(empty($code)){
				throw new Exception('code is empty');
			}
			$brandid=Yii::app()->request->getParam("brandid");
			if(empty($brandid)){
				throw new Exception('品牌未设置');
			}

	
			$code_str = "";
			$arr =explode(",",$code);
			foreach($arr as $value){
				//echo $value;
				if($code_str == ""){
					$code_str = "brandnumber like '%".$value."%' ";
					
				}else{
					
					$code_str =$code_str."|| brandnumber like '%".$value."%' ";
				}			
			}
			
			$where = "({$code_str}) and brandid ={$brandid} and status =0";
			$oj = Yii::app()->db->createCommand()->select('id')->from('erp_clothes_order')->where($where)->order('id desc')->queryAll();
			if(empty($oj)){
				throw new Exception('数据为空-001');
			}
			$id ="";
			foreach($oj as $value){
				
				if($id == ""){
					
					$id = $value['id'];
				}else{
					
					$id= $id.",".$value['id'];
				}
			}
			$where2 ="clothes_order_id in(".$id.") and isshow =0 and erp_image.status =1";
			$oj2 = Yii::app()->db->createCommand()
			->select('*')->from('erp_image')
			->join("erp_clothes_order",'erp_image.clothes_order_id=erp_clothes_order.id',array())
			//->join("erp_push_detaile",'erp_image.id=erp_push_detaile.imageid',array())
			->where($where2)
			->order('erp_image.id desc')
			->queryAll();
			
			if(empty($oj)){
				throw new Exception('数据为空-002');
			}
				$ret['data'] = $oj2;
				$ret['status']=1;
		}catch(Exception $e){
				$ret['mag']=$e->getMessage();
			}
		echo json_encode($ret);
	}
	
	public function actiongetpatform(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$brandid=Yii::app()->request->getParam("brandid");
			if(empty($brandid)){
				throw new Exception('品牌未设置');
			}
			$con = Yii::app ()->db->createCommand ()
			->select ( '*' )
			->from ( "erp_cut_detaile" )
			->join("erp_patform",'erp_cut_detaile.patformid=erp_patform.id',array())
			->where ('erp_cut_detaile.brandid=:brandid',array(':brandid'=>$brandid))
			->andwhere("erp_cut_detaile.status=0")
			->queryAll ();
				if(empty($con)){
					throw new Exception('数据为空');
				}
				$ret['data'] = $con;
				$ret['status']=1;
		}catch(Exception $e){
				$ret['mag']=$e->getMessage();
			}
		echo json_encode($ret);
	}
	
	public function actiongetposter(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$brandid=Yii::app()->request->getParam("brandid");
			if(empty($brandid)){
				throw new Exception('品牌未设置');
			}
			$con = Yii::app ()->db->createCommand ()
			->select ( '*' )
			->from ( "erp_poster" )
			->order ( "addtime desc" )
			->where ( 'erp_poster.brandid=:brandid',array(':brandid'=>$brandid))
			->andwhere("status=0")
			->queryAll ();
				if(empty($con)){
					throw new Exception('数据为空');
				}
				$ret['data'] = $con;
				$ret['status']=1;
		}catch(Exception $e){
				$ret['mag']=$e->getMessage();
			}
		echo json_encode($ret);
	}
	/**
	* 根据搭配ID删除单品
	**/
	public function actiondelClothesByclothesid(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$clothes_order_id=Comm::strdecipher($clothes_order_id);
			if(empty($clothes_order_id) || intval($clothes_order_id)!=$clothes_order_id || intval($clothes_order_id)<1){
				throw new Exception('传参失败');
			}
			$clothes_order_id_arr=array();
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				$ret_skc=$this->getSKCbySKUid($clothes_order_id);
				foreach($ret_skc as $value){
					$clothes_order_id_arr=array_keys($value);
				}
			}else{
				$clothes_order_id_arr[]=$clothes_order_id;
			}
			$clothesorder=new clothesorderclass();
			foreach($clothes_order_id_arr as $value){
				$up_ret=$clothesorder->update_clothes($value,array('status'=>1));
				if(!$up_ret){
					throw new Exception('删除失败');
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['status']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 保存备注
	**/
	public function actionwantRemarks(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$clothes_order_id=Comm::strdecipher($clothes_order_id);
			$description=trim(urldecode(Yii::app()->request->getParam("description")));
			if(empty($clothes_order_id) || intval($clothes_order_id)!=$clothes_order_id || intval($clothes_order_id)<1){
				throw new Exception('传参失败');
			}
			$clothes_order_id_arr=array();
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				$ret_skc=$this->getSKCbySKUid($clothes_order_id);
				foreach($ret_skc as $value){
					$clothes_order_id_arr=array_keys($value);
				}
			}else{
				$clothes_order_id_arr[]=$clothes_order_id;
			}
			$clothesorder=new clothesorderclass();
			foreach($clothes_order_id_arr as $value){
				$up_ret=$clothesorder->update_clothes($value,array('description'=>$description));
				if(!$up_ret){
					throw new Exception('修改失败');
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 查看图片总计
	**/
	public function actiongetImgsum(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->selsect_img_count($_SESSION['brandid'],0);
			$ret['data']=$c_ret;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 修改衣服的分类
	**/
	public function actionsetClothesStyle(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$Style=Yii::app()->request->getParam("Style");
			
			if(empty($clothes_order_id)){
				throw new Exception('传参失败');
			}
			$clothesorder=new clothesorderclass();
			$clothesid_arr=explode(',',$clothes_order_id);
			$clothes_order_id_arr=array();
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				foreach($clothesid_arr as $value){
					$value=Comm::strdecipher($value);
					$ret_skc=$this->getSKCbySKUid($value);
					foreach($ret_skc as $skc_value){
						$arr_key=array_keys($skc_value);
						foreach($arr_key as $key_value){
							$clothes_order_id_arr[]=$key_value;
						}
					}
				}
			}else{
				foreach($clothesid_arr as $value){
					$clothes_order_id_arr[]=Comm::strdecipher($value);
				}
			}
			$num=0;
			foreach($clothes_order_id_arr as $value){
				$c_ret=$clothesorder->update_clothes($value,array('brandcategoryid'=>$Style));
				if($c_ret){
					$num++;
				}
			}
			if($num==0){
				throw new Exception('修改分类失败');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 设置图片状态
	**/
	public function actionsetClothesimgstatus(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$img_id=Yii::app()->request->getParam("img_id");
			$status=Yii::app()->request->getParam("status");
			if(empty($status)){
				$status=1;
			}
			
			if(empty($clothes_order_id)){
				throw new Exception('传参失败');
			}
			$clothes_order_id=Comm::strdecipher($clothes_order_id);
			$img_id=Comm::strdecipher($img_id);
			$clothesimage=new clothesimageclass();
			if(empty($img_id)){
				$clothes_order_id_arr=array();
				if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
					$ret_skc=$this->getSKCbySKUid($clothes_order_id);
					foreach($ret_skc as $skc_value){
						$clothes_order_id_arr=array_keys($skc_value);
					}
				}else{
					$clothes_order_id_arr[]=$clothes_order_id;
				}
				$img_id_arr=array();
				//获取单品所有待审核的图片
				foreach($clothes_order_id_arr as $value){
					$img_ret=$clothesimage->select_all_image($value,0,array('isshow'=>0));
					if(count($img_ret)>0){
						foreach($img_ret as $value){
							$img_id_arr[]=$value['id'];
						}
					}
				}
				if(count($img_id_arr)==0){
					throw new Exception('没找到待审核图片');
				}
				$img_id=implode(',',$img_id_arr);
			}
			$num=0;
			$c_ret=$clothesimage->update_image($clothes_order_id,$img_id,$status);
			if($c_ret){
				$num++;
			}
			if($num==0){
				throw new Exception('一键合格失败');
			}
			$ret['img_id']=$img_id;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 删除单品的图片
	**/
	public function actiondelClothesimg(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$img_id=Yii::app()->request->getParam("img_id");
			if(empty($clothes_order_id) || empty($img_id)){
				throw new Exception('传参失败');
			}
			$clothes_order_id=Comm::strdecipher($clothes_order_id);
			$img_id=Comm::strdecipher($img_id);
			$clothesimage=new clothesimageclass();
			$clothesimage->del_image($clothes_order_id,$img_id,1);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 删除单品分类的图片
	**/
	public function actiondelClothesimgBystyle(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$style=Yii::app()->request->getParam("style");
			if(empty($clothes_order_id) || empty($style)){
				throw new Exception('传参失败');
			}
			
			$style_arr=explode(',',$style);
			$type_arr=array();
			foreach($style_arr as $value){
				if($value=='pcount'){
					$type_arr[]=0;
					$type_arr[]=1;
					$type_arr[]=2;
				}else if($value=='mcount'){
					$type_arr[]=3;
				}else if($value=='dcount'){
					$type_arr[]=4;
				}
			}
			$clothes_order_id_arr2=explode(',',$clothes_order_id);
			$clothes_order_id_arr=array();
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				foreach($clothes_order_id_arr2 as $key=>$value){
					$value=Comm::strdecipher($value);
					$ret_skc=$this->getSKCbySKUid($value);
					foreach($ret_skc as $skc_value){
						$skc_id_arr=array_keys($skc_value);
						foreach($skc_id_arr as $skc_id_value){
							$clothes_order_id_arr[]=$skc_id_value;
						}
					}
				}
			}else{
				foreach($clothes_order_id_arr2 as $key=>$value){
					$clothes_order_id_arr[$key]=Comm::strdecipher($value);
				}
			}
			$clothesimage=new clothesimageclass();
			$img_data_ret=$clothesimage->select_all_image($clothes_order_id_arr,-1,array('isshow'=>0));
			$img_arr=array();
			foreach($img_data_ret as $value){
				if(in_array($value['type'],$type_arr)){
					$img_arr[]=$value['id'];
				}
			}
			if(count($img_arr)==0){
				throw new Exception('');
			}
			$clothesimage->del_image('',implode(',',$img_arr),1);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 下载衣服图片
	**/
	public function actiondownclothesimg() {
		$ret=array('status'=>0,'mag'=>'');
		try{
			//清除1分钟前生成的本地缓存衣服图片		
			$date=time();
			$DOCUMENT=$_SERVER['DOCUMENT_ROOT'].'/cacheimg/';//设置文件路径
			$flies=opendir($DOCUMENT);//获取文件夹下的所有文件
			while( ($filename = readdir($flies)) !== false ){
				if($filename != '.' && $filename != '..'){
					$fileatime=fileatime($DOCUMENT.$filename);//获取文件时间
					if($fileatime<$date-60){//如果文件是1分钟生成的就删除
						@unlink($DOCUMENT.$filename);
					}
				}
			}
			closedir($flies);
			
			$clothes_order_id=Yii::app()->request->getParam('clothes_order_id');
			$domain=Yii::app()->request->getParam('domain');
			
			$clothes_arr=explode(',',$clothes_order_id);
			foreach($clothes_arr as $key=>$value){
				$clothes_arr[$key]=Comm::strdecipher($value);
			}
			
			$clothes_where_arr=array();
			$clothes_where_arr['erp_clothes_order.id']=$clothes_arr;
			/**
			* 获取衣服款号
			**/
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr);
			$brandnumber_arr=array();
			foreach($c_ret['data'] as $value){
				$brandnumber_arr[$value['id']]=$value['brandnumber'];
			}
			//获取衣服需要下载的图片地址
			$clothesimage=new clothesimageclass();
			$param_arr=array('isshow'=>0,'isback'=>0,'type'=>array(0,1,2));
			$ret_data=$clothesimage->select_all_image($clothes_arr,-1,$param_arr);
			$c_img_data=array();
			foreach($ret_data as $value){
				if(!isset($c_img_data[$value->clothes_order_id])){
					$c_img_data[$value->clothes_order_id]=array('url'=>'','pcount'=>0,'mcount'=>0,'dcount'=>0,'img_type'=>-1,'img_data'=>'2035-12-31 00:00:00');
				}
				switch ($value->type){
					case 0: 
						$value->status!=1?$c_img_data[$value->clothes_order_id]['pcount']++:'';//灰模图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(0,1)) && strtotime($c_img_data['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 1: 
						$value->status!=1?$c_img_data[$value->clothes_order_id]['pcount']++:'';//立体图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(1)) && strtotime($c_img_data['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
					case 2: 
						$value->status!=1?$c_img_data[$value->clothes_order_id]['pcount']++:'';//静态图
						if(!empty($value->url) && !in_array($c_img_data[$value->clothes_order_id]['img_type'],array(0,1,2)) && strtotime($c_img_data['img_data'])>strtotime($value->addtime)){
							$c_img_data[$value->clothes_order_id]['img_type']=$value->type;
							$c_img_data[$value->clothes_order_id]['url']=$value->url;
							$c_img_data[$value->clothes_order_id]['img_data']=$value->addtime;
						}
						break;
				}
			}

			//下载网络图片 保存到本地
			foreach($c_img_data as $key=>$value){
				if(empty($value['url'])){//图片地址为空
					continue;
				}
				$fp2=@file_get_contents($domain.$value['url']);
				if(empty($fp2) || $fp2=='﻿404 Error!'){//如果图片获取失败 就退出
					continue;
				}
				$filename=$brandnumber_arr[$key].'_'.time().'_'.rand(100,999).'.png';
				$img_tmp = new Gmagick ();
				$img_tmp->readImageBlob($fp2);
				$file2 = fopen($_SERVER['DOCUMENT_ROOT'].'/cacheimg/'.$filename,"w");//打开文件准备写入
				fwrite($file2,$img_tmp);//写入
				fclose($file2);//关闭
				$img_tmp->clear();
				$img_tmp->destroy();
				
				$c_img_data[$key]['filename']=$filename;
			}
			//将图片打包后返回其地址
			$zip = new ZipArchive ();
			$zipname = '/cacheimg/img_'.$_SESSION['brandid'].'.zip';//用户下载时默认的名字
			@unlink($_SERVER ['DOCUMENT_ROOT'] .$zipname);
			$zip->open ( $_SERVER ['DOCUMENT_ROOT'] .$zipname, ZipArchive::CREATE );
			$is_bool=false;
			foreach($c_img_data as $key=>$value){
				if(isset($value['filename'])){
					$is_bool=true;
					$zip->addFile ( $_SERVER ['DOCUMENT_ROOT'].'/cacheimg/'.$value['filename'] ,$value['filename']);
				}
			}
			$zip->close ();
			if(!$is_bool){
				throw new Exception('下载图片失败');
			}
			$ret['out_url']='http://'.$_SERVER['HTTP_HOST'].$zipname;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 根据SKU对应ID获取其下所有SKC的ID
	**/
	public function getSKCbySKUid($id){
		$clothes_ret=array();
		$clothesorder=new clothesorderclass();
		$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],array('id'=>$id));
		$ret_sku=$c_ret['sku'];
		$clothes_ret[$ret_sku]=array();
		$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],-1,array('sku'=>$ret_sku));
		foreach($c_ret['data'] as $value){
			$clothes_ret[$ret_sku][$value['id']]=$value;
		}
		return $clothes_ret;
	}
	
	
	/**
	 * 图片详情
	 */
	public function actionmanageinfo(){
		if($this->Website_template!='SKC'){
			$this->manageinfoSKU();
			exit();
		}
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$id=Yii::app ()->request->getParam ( 'id' );
			$id=Comm::strdecipher($id);
			$sort=Yii::app()->request->getParam("sort");
			if(empty($id)){
				throw new Exception("ID 为空");
			}
			if(empty($sort)){
				$sort='down';
			}
			//获取品牌名
			$brand_ret=Brand::brandSelectById($_SESSION['brandid']);
			if(count($brand_ret)==0){
				throw new Exception('');
			}
			$ret['data']['brandname']=$brand_ret[0]['name'];
			$code_start=$brand_ret[0]['code_start'];
			$code_end=$brand_ret[0]['code_end'];
			
			$clothesimage=new clothesimageclass();
			//获取单品信息
			$clothes_where_arr=array();
			$clothes_where_arr['id']=$id;
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],$clothes_where_arr);
			if(count($c_ret)==0){
				throw new Exception('单品不存在');
			}
			$ret['data']['clothes']=$c_ret;
			$ret['data']['prev']='';
			$ret['data']['next']='';
			//获取单品上一条
			$clothes_where_arr['seach_direction']='prev';
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],$clothes_where_arr);
			if(!empty($c_ret)){
				$ret['data']['prev']=$c_ret['id'];
			}
			//获取单品下一条
			$clothes_where_arr['seach_direction']='next';
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],$clothes_where_arr);
			if(!empty($c_ret)){
				$ret['data']['next']=$c_ret['id'];
			}
			//获取单品不同色
			$ret['data']['color']=array();
			$ret['data']['color'][]=array('id'=>$c_ret['id'],'url'=>$c_ret['url'],'brandnumber'=>$c_ret['brandnumber']);
			if(!empty($code_start) || !empty($code_end)){//品牌未设置款号规则
			
				//根据品牌的款号规则截取款号字符段
				$barcode_str=$this->SKCToSKU($code_start,$code_end,$ret['data']['clothes']['brandnumber']);
				$clothes_where_arr=array();
				$clothes_where_arr['erp_clothes_order.brandnumber']='like_%'.$barcode_str.'%';
				$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr);
				$ret['data']['color']=array();
				foreach($c_ret['data'] as $value){
					$img_all_ret=$clothesimage->select_all_image($value['id'],'-1',array('isshow'=>0),'','id asc');
					$c_img_data=array('id'=>$value['id'],'url'=>$value['url'],'img_type'=>-1,'brandnumber'=>$value['brandnumber'],'img_data'=>'2035-12-31 00:00:00');
					foreach($img_all_ret as $img_value){
						switch ($img_value->type){
							case 0: 
								//灰模图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 1: 
								//立体图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(1)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 2: 
								//静态图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1,2)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 3: 
								//模特图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1,2,3)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 4: 
								//细节图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1,2,4)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
						}
					}
					$ret['data']['color'][]=$c_img_data;
				}
				//$dq_color=array();
				//foreach($ret['data']['color'] as $key=>$value){
				//	if($value['id']==$ret['data']['clothes']['id']){
				//		$dq_color=$value;
				//		array_splice($ret['data']['color'],$key,1);
				//		break;
				//	}
				//}
				//array_unshift($ret['data']['color'],$dq_color);
			}
			$ret['data']['clothes']['brandcategoryname']='';
			if(!empty($ret['data']['clothes']['brandcategoryid'])){//单品已设置款号
				//获取品牌的所有分类
				$brandcategory=new brandcategoryclass();
				$category_ret=$brandcategory->select_category($_SESSION['brandid']);
				
				foreach($category_ret as $value){
					if($value->id==$ret['data']['clothes']['brandcategoryid']){
						$ret['data']['clothes']['brandcategoryname']=$value->name;
						break;
					}
				}
			}
			$param_arr=array();
			$param_arr['isshow']=0;
			$date='';
			$orderdate='addtime desc';
			if($sort=='down'){
				$orderdate='addtime asc';
			}
			
			//获取单品的图片
			
			$img_ret=$clothesimage->select_all_image($ret['data']['clothes']['id'],'-1',$param_arr,$date,$orderdate);
			
			$date_arr=array();
			foreach($img_ret as $value){
				$date_arr[]=substr($value->addtime,0,10);
			}
			sort($date_arr);
			$date_arr=array_unique($date_arr);
			
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			$ret['domain']=$brand_qiniu_account[0]['domain'];
			$ret['data']['img']=$img_ret;
			$ret['date_arr']=$date_arr;
			$ret['sort']=$sort;
			$ret['status']=1;
		}catch(Exception $e){
			$this->render('error',array('msg'=>$e->getMessage(),'status'=>2));
			exit();
		}
		$this->render ( 'manageinfo', $ret);
	}
	/**
	 * 图片详情 SKU
	 */
	public function manageinfoSKU(){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$id=Yii::app ()->request->getParam ( 'id' );
			$id=Comm::strdecipher($id);
			$sort=Yii::app()->request->getParam("sort");
			if(empty($id)){
				throw new Exception("ID 为空");
			}
			if(empty($sort)){
				$sort='down';
			}
			//获取SKU对应所有SKC
			$ret_skc=$this->getSKCbySKUid($id);
			$id_arr=array();
			foreach($ret_skc as $value){
				$id_arr=array_keys($value);
			}
			//echo $id;exit();
			//获取品牌名
			$brand_ret=Brand::brandSelectById($_SESSION['brandid']);
			if(count($brand_ret)==0){
				throw new Exception('');
			}
			$ret['data']['brandname']=$brand_ret[0]['name'];
			$code_start=$brand_ret[0]['code_start'];
			$code_end=$brand_ret[0]['code_end'];
			
			$clothesimage=new clothesimageclass();
			//获取单品信息
			$clothes_where_arr=array();
			$clothes_where_arr['id']=$id;
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],$clothes_where_arr,'SKU');
			if(count($c_ret)==0){
				throw new Exception('单品不存在');
			}
			$ret['data']['clothes']=$c_ret;
			$ret['data']['prev']='';
			$ret['data']['next']='';
			//获取单品上一条
			$clothes_where_arr['seach_direction']='prev';
			$clothes_where_arr['sku_not']=$ret['data']['clothes']['sku'];
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],$clothes_where_arr,'SKU');
			if(!empty($c_ret)){
				$ret['data']['prev']=$c_ret['id'];
			}
			//获取单品下一条
			$clothes_where_arr['seach_direction']='next';
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],$clothes_where_arr,'SKU');
			if(!empty($c_ret)){
				$ret['data']['next']=$c_ret['id'];
			}
			//获取单品不同色
			$clothes_where_arr=array();
			$clothes_where_arr['erp_clothes_order.sku']=$ret['data']['clothes']['sku'];
			$c_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],0,$clothes_where_arr);
			
			$ret['data']['color']=array();
			if($c_ret['page_sum']>0){
				foreach($c_ret['data'] as $value){
					$img_all_ret=$clothesimage->select_all_image($value['id'],'-1',array('isshow'=>0),'','id asc');
					$c_img_data=array('id'=>$value['id'],'url'=>$value['url'],'img_type'=>-1,'brandnumber'=>$value['brandnumber'],'img_data'=>'2035-12-31 00:00:00');
					foreach($img_all_ret as $img_value){
						switch ($img_value->type){
							case 0: 
								//灰模图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 1: 
								//立体图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(1)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 2: 
								//静态图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1,2)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 3: 
								//模特图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1,2,3)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
							case 4: 
								//细节图
								if(!empty($img_value->url) && !in_array($c_img_data['img_type'],array(0,1,2,4)) && strtotime($c_img_data['img_data'])>strtotime($img_value->addtime)){
									$c_img_data['img_type']=$img_value->type;
									$c_img_data['url']=$img_value->url;
									$c_img_data['img_data']=$img_value->addtime;
								}
								break;
						}
					}
					$ret['data']['color'][]=$c_img_data;
				}
				$dq_color=array();
				foreach($ret['data']['color'] as $key=>$value){
					if($value['id']==$ret['data']['clothes']['id']){
						$dq_color=$value;
						array_splice($ret['data']['color'],$key,1);
						break;
					}
				}
				array_unshift($ret['data']['color'],$dq_color);
			}
			$ret['data']['clothes']['brandcategoryname']='';
			if(!empty($ret['data']['clothes']['brandcategoryid'])){//单品已设置款号
				//获取品牌的所有分类
				$brandcategory=new brandcategoryclass();
				$category_ret=$brandcategory->select_category($_SESSION['brandid']);
				
				foreach($category_ret as $value){
					if($value->id==$ret['data']['clothes']['brandcategoryid']){
						$ret['data']['clothes']['brandcategoryname']=$value->name;
						break;
					}
				}
			}
			$param_arr=array();
			$param_arr['isshow']=0;
			$date='';
			$orderdate='addtime desc';
			if($sort=='down'){
				$orderdate='addtime asc';
			}
			$id_to_str_arr=array();
			foreach($id_arr as $value){
				$id_to_str_arr[$value]=Comm::strencrypt($value);
			}
			//获取单品的图片
			$img_ret=$clothesimage->select_all_image($id_arr,'-1',$param_arr,$date,$orderdate);
			//print_r($img_ret);
			$date_arr=array();
			foreach($img_ret as $value){
				$date_arr[]=substr($value->addtime,0,10);
			}
			sort($date_arr);
			$date_arr=array_unique($date_arr);
			
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			$ret['domain']=$brand_qiniu_account[0]['domain'];
			$ret['data']['img']=$img_ret;
			$ret['date_arr']=$date_arr;
			$ret['id_to_str_arr']=$id_to_str_arr;
			$ret['sort']=$sort;
			$ret['status']=1;
		}catch(Exception $e){
			$this->render('error',array('msg'=>$e->getMessage(),'status'=>2));
			exit();
		}
		$this->render ( 'manageinfoSKU', $ret);
	}
	/**
	* 上传图片
	**/
	function actionuploadimg(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$skc=Yii::app ()->request->getParam ( 'skc' );
			$sku=Yii::app ()->request->getParam ( 'sku' );
			$img_type=Yii::app ()->request->getParam ( 'img_type' );
			$orderid_str=Comm::strdecipher(Yii::app ()->request->getParam ( 'orderid_str' ));
			$id=Comm::strdecipher(Yii::app ()->request->getParam ( 'id' ));
			
			$order_ret=orderclass::selectOrderNameByOrderID($_SESSION['brandid'],array($orderid_str));
			if(count($order_ret)==0){
				throw new Exception('订单查询失败');
			}
			$img_name=$_FILES['facebox_file']['name'];
			$images = Common::upload ( 'facebox_file', 'uploads/' );
			if ($images == '文件为空' || $images == "文件太大" || $images == '只能上传图像' || $images == '上传图片失败') {//表示页面图片更改过
				throw new Exception($images);         
			}
			$file='orderlist/'.$order_ret[0]['ordername'].'/'.$sku.'/'.$skc.'/'.($img_type==1?'product':($img_type==3?'model':'details')).'/'.$img_name;
			$clothesimage=new clothesimageclass();
			$img_ret=$clothesimage->select_all_image($id,'-1',array('url'=>$file));
			if(count($img_ret)>0){
				@unlink($_SERVER['DOCUMENT_ROOT'].'/'.$images);
				throw new Exception('图片已存在');
			}
			//上传到服务器上的临时文件名
			$uploadfile_ret=$this->qinuu_upload($file,$_SERVER['DOCUMENT_ROOT'].'/'.$images);
			
			//if(!isset($uploadfile_ret[0]['hash'])){
				//@unlink($_SERVER['DOCUMENT_ROOT'].'/'.$images);
			//	throw new Exception('图片上传失败');
			//}
			$clothesimage->create_clothes_img($img_name,$img_type,$file,$id,'');
			@unlink($_SERVER['DOCUMENT_ROOT'].'/'.$images);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		//echo '<script>parent.facebox_upload_img_ret(\''.json_encode($ret).'\')</script>';
	}
	/**
	* 单品设主图
	**/
	function actionsetIstop(){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$new_istop_id=Yii::app ()->request->getParam ( 'new_istop_id' );//新主图id
			$istop_id=Yii::app ()->request->getParam ( 'istop_id' );//主图id
			$clothes_order_id=Yii::app()->request->getParam('clothes_order_id');//单品id
			$clothes_order_id=Comm::strdecipher($clothes_order_id);
			$istop_id=Comm::strdecipher($istop_id);
			$new_istop_id=Comm::strdecipher($new_istop_id);
			if($istop_id==$new_istop_id){
				throw new Exception('新旧主图不能相同');
			}
			$clothesimage=new clothesimageclass();
			if(!empty($istop_id)){//已设主图id 就将其去除
				$clothesimage->set_top($clothes_order_id,$istop_id,0);
			}
			$clothesimage->set_top($clothes_order_id,$new_istop_id,1);
			$ret['istop_id']=$istop_id;
			$ret['new_istop_id']=$new_istop_id;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 设单品图分类
	**/
	function actionsetClothesImgstyle(){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$clothes_order_id=Yii::app()->request->getParam("clothes_order_id");
			$img_id=Yii::app()->request->getParam("img_id");
			$Style=Yii::app()->request->getParam("Style");
			if(empty($Style) || empty($clothes_order_id) || empty($img_id)){
				throw new Exception('传参失败');
			}
			if($Style==7){
				$Style=0;
			}
			$clothes_order_id=Comm::strdecipher($clothes_order_id);
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				$ret_skc=$this->getSKCbySKUid($clothes_order_id);
				foreach($ret_skc as $skc_value){
					$clothes_order_id=array_keys($skc_value);
				}
			}
			$img_id_arr=explode(',',$img_id);
			foreach($img_id_arr as $key=>$value){
				$img_id_arr[$key]=Comm::strdecipher($value);
			}
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			
			$clothesimage=new clothesimageclass();
			$p_w=array();
			$p_w['id']=$img_id_arr;
			$img_ret=$clothesimage->select_all_image($clothes_order_id,-1,$p_w);
			$ret['ret_img_url']=array();//修改后的图片路径
			if(count($img_ret)>0){
				//获取品牌的七牛权限
				$atch=new Auth($brand_qiniu_account[0]['access_key'],$brand_qiniu_account[0]['secret_key']);
				//创建七牛资源类
				$Bucket= new BucketManager($atch);
				$Style_name='product';
				if($Style==4){
					$Style_name='details';
				}else if($Style==3){
					$Style_name='model';
				}
				foreach($img_ret as $value){
					$url_arr=explode('/',$value->url);
					$url_arr[4]=$Style_name;
					$new_url=implode('/',$url_arr);
					if($value->url==$new_url){
						continue;
					}
					$Bucket->delete($brand_qiniu_account[0]['space'],$new_url);//删除原有的文件
					//修改图片路径
					$Bucket_ret=$Bucket->rename($brand_qiniu_account[0]['space'],$value->url,$new_url);
					if(!empty($Bucket_ret)){//没有返回值 表示修改成功
						throw new Exception('修改图片分类失败');
					}
					$ret['ret_img_url'][$value->id]=$new_url;
					$clothesimage->update_image_type($clothes_order_id,$value->id,$Style,$new_url);
				}
			}else{
				throw new Exception('图片为空');
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 获取平台信息
	**/
	public function actiongetStore(){
		$ret=array('status'=>0,'msg'=>'');
		try{	
			$sku=Yii::app()->request->getParam("sku");
			$skc=Yii::app()->request->getParam("skc");
			//获取平台ID
			$patform=new patformclass();
			$patform_ret=$patform->select_erp_patform();//获取所有平台
			
			//获取水印列表
			$watermark=new watermarkclass();
			$watermark_ret=$watermark->select_erp_watermark($_SESSION['brandid']);
			
			$cut_detaile=new cut_detaileclass();
			$cut_ret=$cut_detaile->select_erp_cut_detaile($_SESSION['brandid']);//获取品牌所有的裁剪模版
			$cut_data=array();
			foreach($patform_ret as $P_key=>$P_value){
				$is_bool=false;
				foreach($cut_ret as $key=>$value){
					if($value['patformid']==$P_value['id']){
						$cut_data[$P_value['id']]=$value;
						$is_bool=true;
						break;
					}
				}
				if(!$is_bool){
					unset($patform_ret[$P_key]);
				}
			}
			$cut_id_arr=array();
			//统计未推送图片的 裁剪模版数
			if(count($cut_ret)>0){
				$cut_id=array();
				$pushDetaile=new pushDetaileclass();
				foreach($cut_ret as $key=>$value){
					$parm_arr=array('pushstatus'=>0,'cutid'=>$value['id'],'distinct'=>'cutid');
					if($value['barcodetype']==0){
						$parm_arr['skc']=$skc;
					}else{
						$parm_arr['sku']=$sku;
					}
					$push_ret=$pushDetaile->select_all_push($_SESSION['brandid'],$parm_arr);
					if(count($push_ret['data'])>0){
						foreach($push_ret['data'] as $k=>$v){
							if(!in_array($v['count_cutid'],$cut_id_arr)){
								$cut_id_arr[]=$v['count_cutid'];
							}
						}
					}
				}
			}
			$ret['patform']=$patform_ret;
			$ret['cut']=$cut_data;
			$ret['watermark']=$watermark_ret;
			$ret['cut_id_arr']=$cut_id_arr;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 保存图片裁剪信息
	**/
	public function actionsetCutOutImg(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$data=Yii::app()->request->getParam("data");
			$zoom_data=Yii::app()->request->getParam("zoom_data");
			$del_data=Yii::app()->request->getParam("del_data");
			$data=json_decode($data,true);
			$zoom_data=json_decode($zoom_data,true);
			$del_data=json_decode($del_data,true);
			//获取平台ID
			$patform=new patformclass();
			$patform_ret=$patform->select_erp_patform();//获取所有平台
			
			$cut_detaile=new cut_detaileclass();
			$cut_ret=$cut_detaile->select_erp_cut_detaile($_SESSION['brandid']);//获取品牌所有的裁剪模版
			
			$patform_to_cut=array();
			foreach($cut_ret as $value){
				foreach($patform_ret as $p_value){
					if($value['patformid']==$p_value['id']){
						$patform_to_cut[$p_value['Englishname']]=$value['id'];
					}
				}
			}
			
			$pushDetaile=new pushDetaileclass();
			$clothesorder=new clothesorderclass();
			if(count($del_data)>0){//当其有删除数据时 先将数据删除
				$pushDetaile->del_push(implode(',',$del_data));
			}
			foreach($data as $key=>$value){
				foreach($value as $img_key=>$img_value){
					if(empty($img_value['imageid'])){//图片不存在
						continue;
					}
					if(empty($img_value['skc'])){
						$clothes_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],array('id'=>Comm::strdecipher($img_value['coid'])));
						if(empty($clothes_ret)){
							continue;
						}
						$data[$key][$img_key]['skc']=$clothes_ret['brandnumber'];
					}
					$data[$key][$img_key]['cutid']=$patform_to_cut[$key];
					$data[$key][$img_key]['brandid']=$_SESSION['brandid'];
					
					$data[$key][$img_key]['positionx']=0;
					$data[$key][$img_key]['positiony']=0;
					
					$data[$key][$img_key]['width']=0;
					$data[$key][$img_key]['height']=0;
					if(isset($zoom_data[$key][$img_value['imageid']])){
						$data[$key][$img_key]['positionx']=$zoom_data[$key][$img_value['imageid']]['left'];
						$data[$key][$img_key]['positiony']=$zoom_data[$key][$img_value['imageid']]['top'];
						$data[$key][$img_key]['width']=$zoom_data[$key][$img_value['imageid']]['width'];
						$data[$key][$img_key]['height']=$zoom_data[$key][$img_value['imageid']]['height'];
						$data[$key][$img_key]['CutOut_width']=$zoom_data[$key][$img_value['imageid']]['CutOut_width'];
						$data[$key][$img_key]['CutOut_height']=$zoom_data[$key][$img_value['imageid']]['CutOut_height'];
					}
					if(empty($img_value['push_id'])){//当没有存储是 判断图片是否已推送
						$push_ret=$pushDetaile->select_all_push($_SESSION['brandid'],array('cutid'=>$patform_to_cut[$key],'imageid'=>$img_value['imageid']));
						if($push_ret['page_sum']>0){
							$img_value['push_id']=$push_ret['data'][0]['id'];
							$data[$key][$img_key]['push_id']=$push_ret['data'][0]['id'];
						}
					}
					if(empty($img_value['push_id'])){//当图片不存在时添加
						$pushDetaile->create_erp_push($data[$key][$img_key]);
					}else{//修改
						$push_id=$data[$key][$img_key]['push_id'];
						$data[$key][$img_key]['pushstatus']=0;
						$data[$key][$img_key]['addtime']=date("Y-m-d H:i:s");
						unset($data[$key][$img_key]['push_id']);
						$pushDetaile->update_push($push_id,$data[$key][$img_key]);
					}
				}
			}
			
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 获取图片裁剪信息
	**/
	public function actiongetCutOutImg(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$Store_type=Yii::app()->request->getParam("Store_type");
			$skc=Yii::app()->request->getParam("skc");
			$sku=Yii::app()->request->getParam("sku");
			$patform=new patformclass();
			$patform_ret=$patform->select_erp_patform();//获取所有平台
			//获取平台ID
			$patform_id=0;
			foreach($patform_ret as $value){
				if($value['Englishname']==$Store_type){
					$patform_id=$value['id'];
					break;
				}
			}
			if(empty($patform_id)){
				throw new Exception('平台不存在');
			}
			
			$cut_detaile=new cut_detaileclass();
			$cut_ret=$cut_detaile->select_erp_cut_detaile($_SESSION['brandid']);//获取品牌所有的裁剪模版
			foreach($cut_ret as $key=>$value){
				if($value['patformid']!=$patform_id){
					unset($cut_ret[$key]);
				}
			}
			if(count($cut_ret)==0){
				throw new Exception('平台的裁剪模版不存在');
			}
			$cutid=array();
			foreach($cut_ret as $key=>$value){
				$cutid[]=$value['id'];
			}
			$parm_arr=array('pushstatus'=>0);
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				$parm_arr['sku']=$sku;
			}else{
				$parm_arr['skc']=$skc;
			}
			$parm_arr['cutid']=$cutid;
			$pushDetaile=new pushDetaileclass();
			$push_ret=$pushDetaile->select_all_push($_SESSION['brandid'],$parm_arr);
			if(count($push_ret)==0){
				throw new Exception('平台待推送图不存在');
			}
			$ret['data']=$push_ret['data'];
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 保存裁剪缓存数据
	**/
	public function actionsetCutCookie(){
		$data=Yii::app()->request->getParam("data");
		$SKU=Yii::app()->request->getParam("SKU");
		$session_id=session_id();
		$new_date=date("Y-m-d H:i:s");//获取当前时间
		
		$cut_cookie=new erp_cut_cookie();
		//清除过期的缓存数据
		$condition=array('end_time_if_<='=>$new_date);
		$cut_cookie->del_cookie($condition);
		
		//查询数据是否存在
		$condition=array('brandid'=>$_SESSION['brandid'],'SKU'=>$SKU,'sessionid'=>$session_id);
		$cut_ret=$cut_cookie->select_all_cookie($condition);
		if(count($cut_ret)==0){//不存在时添加
			$param=array();
			$param['session_id']=$session_id;
			$param['SKU']=$SKU;
			$param['brandid']=$_SESSION['brandid'];
			$param['text']=$data;
			$cut_cookie->insert_cookie($param);
		}else{
			$cut_id=$cut_ret[0]['id'];
			$up_data=array();
			$up_data['end_time']=date("Y-m-d H:i:s",strtotime('+2 hour'));
			$up_data['text']=$data;
			$param=array();
			$param['id']=$cut_id;
			$cut_cookie->update_cookie($up_data,$param);
		}
	}
	/**
	* 获取裁剪缓存数据
	**/
	public function actiongetCutCookie(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$SKU=Yii::app()->request->getParam("SKU");
			$session_id=session_id();
			$new_date=date("Y-m-d H:i:s");//获取当前时间
			
			$cut_cookie=new erp_cut_cookie();
			//清除过期的缓存数据
			$condition=array('end_time_if_<='=>$new_date);
			$cut_cookie->del_cookie($condition);
			
			//查询数据是否存在
			$condition=array('brandid'=>$_SESSION['brandid'],'SKU'=>$SKU,'sessionid'=>$session_id);
			$cut_ret=$cut_cookie->select_all_cookie($condition);
			
			if(count($cut_ret)==0){
				throw new Exception('没有缓存');
			}
			$ret['data']=$cut_ret[0]['text'];	
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	} 
	/**
	* 删除裁剪缓存数据
	**/
	public function actiondelCutCookie(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$SKU=Yii::app()->request->getParam("SKU");
			$session_id=session_id();
			$new_date=date("Y-m-d H:i:s");//获取当前时间
			
			$cut_cookie=new erp_cut_cookie();
			//清除过期的缓存数据
			$condition=array('end_time_if_<='=>$new_date);
			$cut_cookie->del_cookie($condition);
			
			//删除数据
			$condition=array('brandid'=>$_SESSION['brandid'],'SKU'=>$SKU);
			$cut_cookie->del_cookie($condition);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 图片预览
	**/
	public function actionCutOutpreview(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$array=array(
					"watermark_id"=>Yii::app()->request->getParam("watermarkid"),
					"watermark_width"=>Yii::app()->request->getParam("watermark_width"),
					"watermark_height"=>Yii::app()->request->getParam("watermark_height"),
					"watermark_url"=>Yii::app()->request->getParam("watermark_url"),
					"watermark_positionx"=>Yii::app()->request->getParam("watermark_positionx"),
					"watermark_positiony"=>Yii::app()->request->getParam("watermark_positiony"),
					"patform_width"=>Yii::app()->request->getParam("patform_width"),
					"patform_height"=>Yii::app()->request->getParam("patform_height"),
					"image_url"=>Yii::app()->request->getParam("image_url"),
					"cut_positionx"=>Yii::app()->request->getParam("cut_positionx"),
					"cut_positiony"=>Yii::app()->request->getParam("cut_positiony"),
					"cut_width"=>Yii::app()->request->getParam("cut_width"), 
					"cut_height"=>Yii::app()->request->getParam("cut_height"),
					"img_w"=>Yii::app()->request->getParam("img_w"), 
					"img_h"=>Yii::app()->request->getParam("img_h")
					);
			$pushdetaile_class=new pushDetaileclass();
			$pushdetaile_ret=$pushdetaile_class->cut_image($array,$_SESSION['brandid']);
			if($pushdetaile_ret['status']==0){
				throw new Exception($pushdetaile_ret['msg']);
			}
			
			$ret['data']=$pushdetaile_ret['url'];
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 海报
	**/
	public function actionposter(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$sort=Yii::app()->request->getParam("sort");
			$start_date=Yii::app()->request->getParam("start_date");//开始时间
			$end_date=Yii::app()->request->getParam("end_date");//分类
			$dirname=Yii::app()->request->getParam("dirname");//订单时间
			if(empty($sort)){
				$sort='down';
			}
			$limit=18;
			$page=1;
			//获取品牌的所有海报
			$poster=new posterclass();
			//统计海报目录
			$dirname_ret=$poster->statistics_poster_dirname($_SESSION['brandid'],0);
			$dirname_arr=array();
			foreach($dirname_ret as $value){
				$dirname_arr[]=$value->dirname;
			}
			sort($dirname_arr);
			$dirname_arr=array_unique($dirname_arr);
			
			//统计海报时间
			$date_ret=$poster->statistics_poster_date($_SESSION['brandid'],0);
			$date_arr=array();
			foreach($date_ret as $value){
				$date_arr[]=substr($value->addtime,0,10);
			}
			sort($date_arr);
			$date_arr=array_unique($date_arr);
			
			//分别获取文件夹下的内容
			$ret['data']=array();
			if($sort!='down'){
				rsort($dirname_arr);
			}
			foreach($dirname_arr as $value){
				if(!empty($dirname) && $dirname!=$value){
					continue;
				}
				$p_w=array();
				$p_w['dirname']=$value;
				if(!empty($start_date)){
					$p_w['startime']=$start_date;
				}
				if(!empty($end_date)){
					$p_w['endtime']=$end_date;
				}
				$poster_ret=$poster->select_all_poster($_SESSION['brandid'],0,$p_w,$limit,$limit*($page-1));
				if($poster_ret['page_sum']>0){
					//分页
					$criteria = new CDbCriteria();
					$pages=new CPagination($poster_ret['page_sum']);
					$pages->pageSize=$limit;
					$pages->applyLimit($criteria);
					$ret['data'][$value]['data']=$poster_ret['data'];
					$ret['data'][$value]['pages']=$pages;
				}
			}
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			$ret['domain']=$brand_qiniu_account[0]['domain'];
			$ret['dirname']=$dirname;
			$ret['start_date']=$start_date;
			$ret['end_date']=$end_date;
			$ret['sort']=$sort;
			$ret['date_arr']=$date_arr;
			$ret['dirname_arr']=$dirname_arr;
			$ret['status']=1;
		}catch(Exception $e){
			$this->render('error',array('msg'=>$e->getMessage(),'status'=>2));
			exit();
		}
		$this->render ( 'poster', $ret);
	}
	/**
	* 根据分页获取海报图片
	**/
	public function actiongetposterImgByPage(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$limit=18;
			$page=Yii::app()->request->getParam("page");
			$dirname=Yii::app()->request->getParam("dirname");
			if(empty($page)){
				$page=1;
			}
			$poster=new posterclass();
			$p_w=array();
			$p_w['dirname']=$dirname;
			$poster_ret=$poster->select_all_poster($_SESSION['brandid'],0,$p_w,$limit,$limit*($page-1));
			
			$ret['data']=$poster_ret['data'];
			$ret['page_sum']=$poster_ret['page_sum'];
			$ret['page']=$page;
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 根据分页获取海报分页标签
	**/
	public function actiongetposterPages(){
		$limit=18;
		$page_sum=Yii::app()->request->getParam("page_sum");
		$criteria = new CDbCriteria();
		$pages=new CPagination($page_sum);
		$pages->pageSize=$limit;
		$pages->applyLimit($criteria);
		
		$this->widget ( 'CLinkPager', array (
			'header' => $pages->getItemCount()."条&nbsp;&nbsp;共".$pages->getPageCount()."页&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
			'firstPageLabel' => '首页',    
			'lastPageLabel' => '末页',    
			'prevPageLabel' => '上一页',    
			'nextPageLabel' => '下一页', 
			'pagelist' => true,
			'maxButtonCount' => 5, 'pages' => $pages, "cssFile" => "/css/pager.css" ));
	}
	/**
	* 删除海报的图片
	**/
	public function actiondelPosterimg(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$poster_id=Yii::app()->request->getParam("poster_id");
			if(empty($poster_id)){
				throw new Exception('传参失败');
			}
			$poster=new posterclass();
			$poster->del_poster($poster_id,1);
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 删除海报的文件夹
	**/
	function actiondelPosterdir(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$del_dir=Yii::app()->request->getParam("del_dir");
			$new_dir=Yii::app()->request->getParam("new_dir");
			if(empty($new_dir) || empty($del_dir) || $del_dir==$new_dir){
				throw new Exception('传参失败');
			}
			
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			
			$poster=new posterclass();
			//获取将要删除文件夹下的所有图片
			$p_w=array();
			$p_w['dirname']=$del_dir;
			$poster_ret=$poster->select_all_poster($_SESSION['brandid'],-1,$p_w);
			if($poster_ret['page_sum']>0){
				//获取品牌的七牛权限
				$atch=new Auth($brand_qiniu_account[0]['access_key'],$brand_qiniu_account[0]['secret_key']);
				//创建七牛资源类
				$Bucket= new BucketManager($atch);
				foreach($poster_ret['data'] as $value){
					$new_url=strtr($value['url'],array('/'.$del_dir.'/'=>'/'.$new_dir.'/'));
					//修改图片路径
					$Bucket_ret=$Bucket->rename($brand_qiniu_account[0]['space'],$value['url'],$new_url);
					if(empty($Bucket_ret)){//没有返回值 表示修改成功
						$poster->up_poster($value['id'],array('url'=>$new_url,'dirname'=>$new_dir));
					}
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	* 复制海报图片到指定款号下
	**/
	function actionCopyPosterImg(){
		$ret=array('status'=>0,'mag'=>'');
		try{
			$brandnumber=trim(Yii::app()->request->getParam("brandnumber"));
			$img_type=Yii::app()->request->getParam("img_type");
			$id=Yii::app()->request->getParam("id");
			if(empty($brandnumber) || empty($img_type) || empty($id)){
				throw new Exception('传参失败');
			}
			//查询当前款号是否存在
			$brandnumberorder=new brandnumberorder();
			$brandnumber_ret=$brandnumberorder->select_all_brandnumber($_SESSION['brandid'],array('brandnumber'=>$brandnumber));
			if(count($brandnumber_ret)==0){
				throw new Exception('款号不存在');
			}
			//获取品牌 SKU 规则
			$brand_ret=Brand::brandSelectById($_SESSION['brandid']);
			if(count($brand_ret)==0){
				throw new Exception('');
			}
			$code_start=$brand_ret[0]['code_start'];
			$code_end=$brand_ret[0]['code_end'];
			$SKU=$brandnumber;
			if($code_start!=0 || $code_end!=0){//设置了款号规则后 按规则提取款号
				$SKU=$this->SKCToSKU($code_start,$code_end,$brandnumber);
			}
			//获取订单名
			$orderobj = Yii::app ()->db->createCommand ()
			->select ( 'ordername' )
			->from ( 'erp_order' )
			->where ( "id=".$brandnumber_ret[0]['orderid'] )
			->queryRow ();
			//拼接图片的路径
			$img_src='orderlist/'.$orderobj['ordername'].'/'.$SKU.'/'.$brandnumber;
			
			$qiniu=new erp_qiniu();//七牛的接口类
			$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
			if(count($brand_qiniu_account)==0){
				throw new Exception('品牌暂未绑定七牛帐号');
			}
			
			$img_type_arr=explode(',',$img_type);
			$poster=new posterclass();
			//获取品牌勾选的图片
			$poster_ret=$poster->select_all_poster($_SESSION['brandid'],-1,array('id'=>explode(',',$id)));
			
			if($poster_ret['page_sum']>0){
				$clothesorder=new clothesorderclass();
				$clothesimage=new clothesimageclass();
				
				//获取品牌的七牛权限
				$atch=new Auth($brand_qiniu_account[0]['access_key'],$brand_qiniu_account[0]['secret_key']);
				//创建七牛资源类
				$Bucket= new BucketManager($atch);
				
				$clothes_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],array('brandnumber'=>$brandnumber));//查看衣服是否已添加
				$insert_id=0;
				if(empty($clothes_ret)){//衣服没有添加 就新增加
					
					$insert_id=$clothesorder->create_erp_clothes($SKU,$brandnumber,$brandnumber_ret[0]['orderid'],$_SESSION['brandid']);
				}else{
					$insert_id=$clothes_ret['id'];
				}
				if(empty($insert_id)){
					throw new Exception('海报图复制失败');
				}
				
				foreach($img_type_arr as $img_value){
					$new_img_src=$img_src;
					$type=0;
					if($img_value=='model_img'){
						$new_img_src.='/model/';
						$type=3;
					}else if($img_value=='product_img'){
						$new_img_src.='/product/';
						$type=0;
					}else if($img_value=='detaile_img'){
						$new_img_src.='/details/';
						$type=4;
					}else{
						continue;
					}
					
					foreach($poster_ret['data'] as $value){
						$url_arr=explode('/',$value['url']);
						//查看图片是否存在
						$img_ret=$clothesimage->select_all_image($insert_id,-1,array('url'=>$new_img_src.$url_arr[2]));
						
						if(count($img_ret)>0){//图片存在 修改
							$clothesimage->upload_imageinfo($insert_id,$img_ret[0]->id,array('isshow'=>0));
						}else{//图片不存在 添加
							//修改图片路径
							$Bucket_ret=$Bucket->copy($brand_qiniu_account[0]['space'],$value['url'],$brand_qiniu_account[0]['space'],$new_img_src.$url_arr[2]);
							if(empty($Bucket_ret)){//没有返回值 表示图片复制成功 将数据添加到衣服图片库里
								$clothesimage->create_clothes_img($url_arr[2],$type,$new_img_src.$url_arr[2],$insert_id,$brand_qiniu_account[0]['space']);
							}else{
								throw new Exception('图片复制失败');
							}
						}
					}
				}
			}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	/**
	 * 订单列表
	 */
	public function actionorderlist(){
		
		
		
		$ret=array('status'=>0,'mag'=>'');
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
		
		$page = 1;
		$pagecount=20;
		$keyword="";
		$startdate="";
		$dateorder="desc";
		$type="1";
		$havedata=0;
		if (Yii::app ()->request->getParam ( 'keyword' )) {
			$keyword =  Yii::app ()->request->getParam ( 'keyword' );
			$arr['key'] = $keyword;
		}
		if (Yii::app ()->request->getParam ( 'startdate' )) {
			$startdate = Yii::app ()->request->getParam ( 'startdate' );
			$arr['strd_data'] = $startdate;
		}
		if (Yii::app ()->request->getParam ( 'enddate' )) {
			$enddate = Yii::app ()->request->getParam ( 'enddate' );
			$arr['end_data'] = $enddate;
		}
		if(Yii::app()->request->getParam('dateorder')){
			$dateorder=Yii::app()->request->getParam('dateorder');//时间排序
			$arr['dateorder'] = $dateorder;
		}
		if(Yii::app()->request->getParam('type')){
			$type=Yii::app()->request->getParam('type');//时间排序
			$arr['type'] = $type;
		}
		$arr['brandid']=$brandid;
		if (Yii::app ()->request->getParam ( 'page' )) {
			$page = Yii::app ()->request->getParam ( 'page' );
		}
		$data=array();
		//通过条件查询订单表
		$orderobj=orderclass::select_all_order($page,$pagecount,$brandid,$arr);
		if($orderobj['status']!=0){
			$data=$orderobj['data'];
			
			$orderlist=$data['data'];
			
			for ($i=0;$i<count($orderlist);$i++)
			{
				$orderlist[$i]['haveimage']=1;//1为不显示
				$array=array('orderid'=>$orderlist[$i]['id']);
				$clothesorderclass=new clothesorderclass();
				$orderclothes=$clothesorderclass->select_Single_clothes($brandid,$array);
				if(empty($orderclothes))
				{
					$orderlist[$i]['haveimage']=0;//0为显示
				}
			}
			$data['data']=$orderlist;
		}
		$datearray=orderclass::select_order_date($brandid);
		
		$prme_arr=array('data'=>$data,
				'brandid'=>$brandid,
				'strd_data'=>$startdate,
				'end_data'=>$enddate,
				'dateorder'=>$dateorder,
				'key'=>$keyword,
				'type'=>$type,
				'datearray'=>$datearray);
		
		
		$this->render ( 'orderlist', $prme_arr);
	}
	
	/**
	 * 创建订单
	 */
	public function actionaddorder(){
		$this->render ( 'addorder');
	}
	
	public function actionupdatedescription()
	{
		$ret=array('status'=>0,'msg'=>'','err_data'=>array());
		try{
			$description="";
			$id=0;
			if(Yii::app()->request->getParam('description')){
				$description=urldecode( Yii::app()->request->getParam('description'));//备注
			}
			if(Yii::app()->request->getParam('id')){
				$id=Yii::app()->request->getParam('id');//备注
			}
			$update_arr = array ();
			$update_arr['description']=$description;
			if($id>0)
			{
				erp_order::model ()->updateAll ( $update_arr, 'id=:textx', array (':textx' => $id ) );
				$ret['status']=1;
			}
			
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
	}
	
	/**
	* 导入单品Excel数据
	**/
	public function actionaddsuborder(){
		$ret=array('status'=>0,'msg'=>'','err_data'=>array());
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
		$description="";//备注内容
		$ordername="";//订单号
		$orderid=0;
		$type='';
		$uploadfile='';
		try{
			
			if(Yii::app()->request->getParam('description')){
				$description=Yii::app()->request->getParam('description');//备注
				
			}
			if(Yii::app()->request->getParam('ordername')){
				$ordername=Yii::app()->request->getParam('ordername');//订单名
				
			}
			if(Yii::app()->request->getParam('orderid')){
				$orderid=Yii::app()->request->getParam('orderid');//订单id
				
			}
			if(Yii::app()->request->getParam('type')){
				$type=Yii::app()->request->getParam('type');//表格类型
				
			}
			//验证该订单是否上传过图片，如果传过禁止修改
			if($orderid>0 && !empty($ordername))
			{
				$array=array('orderid'=>$orderid);
				$clothesorderclass=new clothesorderclass();
				$orderclothes=$clothesorderclass->select_Single_clothes($brandid,$array);
				if(!empty($orderclothes)){
					throw new Exception('不可对该订单做操作');
				}
			}
			
				
			//echo $orderid."-----".$ordername."-----".$description;
			//exit();
				//获取上传的文件名
			$file = $_FILES['inputExcel']['name'];
			$file = md5($file).'.xls';
			if(!empty($file))
			{
				//上传到服务器上的临时文件名
				$filetempname = $_FILES['inputExcel']['tmp_name'];
				 //自己设置的上传文件存放路径
				$filePath = 'uploads/saveexcel/';
				$uploadfile=$filePath.$file;//上传后的文件名地址
				
				//如果上传文件成功，就执行导入excel操作
				spl_autoload_unregister(array('YiiBase','autoload'));//取消YII自动加载  
				require_once 'protected/extensions/PHPExcel.php';
				require_once 'protected/extensions/PHPExcel/IOFactory.php';
				require_once 'protected/extensions/PHPExcel/Reader/Excel5.php';	
			    spl_autoload_register(array('YiiBase', 'autoload')); // Enable Yii autoloader
			    //move_uploaded_file() 函数将上传的文件移动到新位置。若成功，则返回 true，否则返回 false。
				
			    $uploadfile =iconv("utf-8","gb2312",$uploadfile);
			    $result=move_uploaded_file($filetempname,$uploadfile);//假如上传到当前目录下
				
				
				if(!$result){
					throw new Exception('文件上传失败！');
				}
				$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
				$objPHPExcel = $objReader->load($uploadfile); 
				$sheet = $objPHPExcel->getSheet(0);
				$highestRow = $sheet->getHighestRow(); // 取得总行数
				$highestColumn = $sheet->getHighestColumn(); // 取得总列数
				$srow =2; //起始行
				$str = "";
				$brandnumber_array=array();
				$brandnumber_str="";
				$code_array=array();//序号
				$code_str="";
				$category_str="";
				$category_array=array();
				 //循环读取excel文件,读取一条,插入一条
		        for($j=2;$j<=$highestRow;$j++)                     //从第一行开始读取数据
		        { 
		            for($k='A';$k<=$highestColumn;$k++)            //从A列读取数据
		            { 
		            	$str .=$objPHPExcel->getSheet()->getCell("$k$j")->getValue().'\\';//读取单元格
						if($k=='A')
						{
							$brandnumber_str.=trim($objPHPExcel->getSheet()->getCell("$k$j")->getValue()).'\\';
						}else if($k=='B'){
							$category_str.=trim($objPHPExcel->getSheet()->getCell("$k$j")->getValue()).'\\';
						}else if($k=='C'){
							$code_str.=trim($objPHPExcel->getSheet()->getCell("$k$j")->getValue()).'\\';
						}else{
							break;
						}
		            }
		        }
				$brandnumber_array1=array();
				$brandnumber_array1 = explode("\\",$brandnumber_str);//款色号数组
				$code_array1=array();
				$code_array1 = explode("\\",$code_str);//款色号数组
				foreach ($brandnumber_array1 as $value)
				{
					if(!empty($value))
					{
						$brandnumber_array[]=$value;
					}
				}
				
				
				foreach($code_array1 as $key=>$value)
				{
					if(!empty($brandnumber_array1[$key]))
					{
						$code_array[]=$value;
					}
				}
				$category_array= explode("\\",$category_str);
	 			//防止重复字段
		 		$u_strs = array_unique($brandnumber_array);
		 		
		 		if(count($brandnumber_array) > count($u_strs)){
		 			@unlink($_SERVER ['DOCUMENT_ROOT'].'/'.$uploadfile);
		 			throw new Exception('本表中款色号不能有重复');
		 		}
		 		if($type!='style'){
		 			//图片管理 导表分类管理
					$brandnumber_string=implode("','",$brandnumber_array);
					$clothesorderclass=new clothesorderclass();
					$clothesorder_obj=$clothesorderclass->selsectordernamebybrandnumber($brandid,$brandnumber_string,$orderid);
					
					$ret['repeat_data']=$clothesorder_obj;
					$repeat_array=array();
					foreach ($clothesorder_obj as $clothesorder_obj_value)
					{
						$repeat_array[]=$clothesorder_obj_value['brandnumber'];//将重复的加入数组
					}
					
					$brandnumber_array=array_diff($brandnumber_array,$repeat_array);//将重复的款号删除
					if(count($brandnumber_array)==0)
					{
						$ret['status']=1;
						@unlink($_SERVER ['DOCUMENT_ROOT'].'/'.$uploadfile);
						throw new Exception('订单内容与之前订单重复');
					}
					if(count($brandnumber_array)>0 && count($repeat_array)>0)
					{
						
						$ret['msg']="订单已创建，发现有其他订单下的款色号";
					}
		 			if($orderid>0 && !empty($ordername))
					{
						$orderclass=new orderclass();
						//删除该订单下的所有款号
						$orderclass->deletebrandnumberbyorderid($brandid,$orderid);
						$orderdir=$_SERVER['DOCUMENT_ROOT'].'/orderlist/'.$ordername;
						$orderclass->delFolder($orderdir);
					}
				}
			}
			if($type=='style'){//图片管理 导表分类管理
				if(count(array_filter($brandnumber_array))==0){
					throw new Exception('款号为空！');
				}
				if(count(array_filter($category_array))==0){
					throw new Exception('分类为空！');
				}
				
				//获取品牌 SKU 规则
				$brand_ret=Brand::brandSelectById($_SESSION['brandid']);
				if(count($brand_ret)==0){
					throw new Exception('');
				}
				$code_start=$brand_ret[0]['code_start'];
				$code_end=$brand_ret[0]['code_end'];
				
				//将SKC转为SKU
				$brandnumber_SKU_arr=array();
				$category_SKU_array=array();
				foreach($brandnumber_array as $key=>$value){
					$brandnumber_SKU=$value;
					if($code_start!=0 || $code_end!=0){//设置了款号规则后 按规则提取款号
						$brandnumber_SKU=$this->SKCToSKU($code_start,$code_end,$value);
					}
					$brandnumber_key=array_search($brandnumber_SKU,$brandnumber_SKU_arr);
					if($brandnumber_key!==false){
						$category_SKU_array[$brandnumber_key]=$category_array[$key];
					}else{
						$brandnumber_SKU_arr[]=$brandnumber_SKU;
						$category_SKU_array[]=$category_array[$key];
					}
				}
				
				//获取品牌的所有分类
				$brandcategory=new brandcategoryclass();
				$category_ret=$brandcategory->select_category($brandid);
				if(count($category_ret)==0){
					throw new Exception('品牌暂未设置分类！');
				}
				//提取已有款号到数组
				$category_data=array();
				foreach($category_ret as $value){
					$category_data[$value->name]=$value->id;
				}
				$category_array_copy=array_unique($category_SKU_array);//清除重复的分类
				$category_array_copy=array_filter($category_array_copy);//清除空元素
				//不存在的分类
				$no_category=Comm::array_diff_fast($category_array_copy,array_keys($category_data));
				$no_category=array_values($no_category);
				//获取款号对应衣服
				$clothesorder=new clothesorderclass();
				$clothes_ret=$clothesorder->select_all_clothes($brandid,-1,array('sku'=>$brandnumber_SKU_arr));
				$no_brandnumber=array();//不存在的款号
				foreach($clothes_ret['data'] as $value){
					$brandnumber_copy_str=$value['sku'];
					$no_brandnumber[]=$brandnumber_copy_str;
					$brandnumber_key=array_search($brandnumber_copy_str,$brandnumber_SKU_arr);
					if(isset($category_data[$category_SKU_array[$brandnumber_key]])){//当款号设置分类 已存在时才可以设置
						$clothesorder->update_clothes($value['id'],array('brandcategoryid'=>$category_data[$category_SKU_array[$brandnumber_key]]));
					}
				}
				$ret['no_brandnumber']=Comm::array_diff_fast($brandnumber_SKU_arr,$no_brandnumber);
				$ret['no_brandnumber']=array_values($ret['no_brandnumber']);
				$ret['no_category']=$no_category;
			}else{
				
				$user36=$this->createBrandCode($brandid);//品牌号生成5位编码
				$order_id=0;	
				if(empty($ordername) && empty($orderid))//订单名为空时说明是添加新订单
				{
					$ordername='';
					$orderobj = Yii::app ()->db->createCommand ()
					->select ( 'ordername' )
					->from ( 'erp_order' )
					->where ( "brandid=$brandid" )
					->andwhere ( "ordername<>' '" )
					->order("id desc")
					->queryRow ();
					
					if(!empty($orderobj))
					{
						$ordername=$orderobj['ordername'];
						$ordername=substr($ordername,5)+1;
						if(strlen($ordername)==1)
							$ordername=$user36."00".$ordername;
						else if(strlen($ordername)==2)
							$ordername=$user36."0".$ordername;
					}else{
						$ordername=$user36.'001';
					}
					
					$erp_order = new  erp_order ();
					$erp_order->ordername=$ordername;
					$erp_order->description=$description;
					$erp_order->barcodecount=count($brandnumber_array);
					$erp_order->addtime=date("Y-m-d H:i:s");
					$erp_order->brandid=$brandid;
					$count = $erp_order->insert ();
					$order_id= Yii::app()->db->getLastInsertID();
					if ($count > 0) {
						
					}else{
						throw new Exception('插入数据失败');
					}
				}else{
					$order_id=$orderid;
					$update_arr = array ();
					$update_arr['description']=$description;
					if(!empty($file))
					{
						$update_arr['barcodecount']=count($brandnumber_array);
					}
					if($orderid>0)
					{
						erp_order::model ()->updateAll ( $update_arr, 'id=:textx', array (':textx' => $orderid ) );
					}
					
				}
				$url='uploads/saveexcel/'.$brandid.'_'.$ordername.'.xls';
				rename($_SERVER['DOCUMENT_ROOT']."/".$uploadfile, $_SERVER['DOCUMENT_ROOT']."/".$url);
				
				//将款号插入到订单对应的款号表中
				foreach($brandnumber_array as $key=>$value)
				{
					$erp_brandnumber_order = new erp_brandnumber_order();
					$erp_brandnumber_order->brandnumber=$value;
					$erp_brandnumber_order->orderid=$order_id;
					$erp_brandnumber_order->brandid=$brandid;
					$erp_brandnumber_order->snumber=$code_array[$key];
					$count = $erp_brandnumber_order->insert ();
				}
				
				$orderclass=new orderclass();
				$orderclass->create_order($brandid,$ordername,$brandnumber_array);
				
				//$bb=orderclass::create_order($ordername,$brandnumber_array);
	 		}
			$ret['status']=1;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		if(count($ret['err_data'])==0){
			$ret['err_data']='';
		}
		
		echo '<script> parent.setsubclothes('.json_encode( $ret).');</script>';
	}
	/**
	 * 获取日志时间
	 */
	public function actiongetorderlogdate() {
		$ret=array('status'=>0,'msg'=>'','err_data'=>array());
		
		try{
			$orderid=0;
			if(Yii::app()->request->getParam('orderid')){
					$orderid=Yii::app()->request->getParam('orderid');//时间排序
			}
			$orderobj=array();
			$order_obj = Yii::app ()->db->createCommand ()
					->select ( 'addtime' )
					->from ( 'erp_orderlog' )
					->where ( "orderid=$orderid" )
					->order("id desc")
					->queryAll ();
					
		    foreach ($order_obj as $value) {
		    	$data=substr($value['addtime'],0,10);
		        if (!in_array($data, $orderobj))
			    {
		           $orderobj[] = $data;
		        }
		    }
			if(count($orderobj)==0)
			{
				$ret['status']=0;
				throw new Exception('no data');
			}else{
				$ret['status']=1;
				$ret['data']=$orderobj;
			}
			
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		
		echo json_encode($ret);
		
	}
/**
	 * 获取日志
	 */
	public function actiongetorderlog() {
		$ret=array('status'=>0,'msg'=>'','err_data'=>array());
		try{
			$orderid=0;
			$addtime="";
			$where="1=1";
			if(Yii::app()->request->getParam('orderid')){
					$orderid=Yii::app()->request->getParam('orderid');//时间排序
			}
			if(Yii::app()->request->getParam('addtime')){
					$addtime=Yii::app()->request->getParam('addtime');//时间排序
			}
			
			$where.=" and orderid=$orderid";
			if(!empty($addtime))
			{
				$str_param = trim ( $addtime );
				$where.=" and DATE_FORMAT(erp_orderlog.addtime,'%Y-%m-%d')='{$str_param}'";
			}
			
			$orderobj = Yii::app ()->db->createCommand ()
					->select ( 'orderlog' )
					->from ( 'erp_orderlog' )
					->where ( $where )
					->order("addtime desc")
					->queryAll ();
			if(empty($orderobj))
			{
				$ret['status']=0;
				throw new Exception('no data');
			}
			$ret['status']=1;
			$ret['data']=$orderobj;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
		exit();
	}
	
	//下载
	public function actiondownfile() {
		
		$url='';
		$file_name='';
		if(Yii::app()->request->getParam('url')){
					$url=Yii::app()->request->getParam('url');//时间排序
			}
		if(Yii::app()->request->getParam('file_name')){
			$file_name=Yii::app()->request->getParam('file_name');//时间排序
		}
		if(!empty($url)){
			header("Content-Type: application/force-download;"); //告诉浏览器强制下载
			header("Content-Transfer-Encoding: binary"); 
			header('Content-Disposition: attachment; filename="'.(empty($file_name)?basename($url):$file_name).'"'); 
			header("Expires: 0"); 
			header("Cache-control: private"); 
			header("Pragma: no-cache"); //不缓存页面
			ob_clean();  
  			flush();
			readfile($url);
		}
		exit();
	}
	
	
	//下载文件夹
	public function actiondownfolder() {
		$orderid="";
		$url=''; 
		$zip=new ZipArchive();
		if(Yii::app()->request->getParam('orderid')){
			$orderid=Yii::app()->request->getParam('orderid');//时间排序
		}
		$zipname='floderzip/order_'.$orderid.'.zip';//服务器中zip的名字
		$url='floderzip/order_'.$orderid.'.zip';//用户下载时默认的名字
		
		if($zip->open($zipname, ZipArchive::OVERWRITE)=== TRUE)
		{ 
		    $orderclass=new orderclass(); 
		 	$orderclass->addFileToZip('orderlist/'.$orderid, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
		    $zip->close(); //关闭处理的zip文件
		}
		header("Content-Type: application/force-download;"); //告诉浏览器强制下载
		header("Content-Transfer-Encoding: binary"); 
		header('Content-Disposition: attachment; filename="'.basename($url).'"'); 
		header("Expires: 0"); 
		header("Cache-control: private"); 
		header("Pragma: no-cache"); //不缓存页面 
		readfile($url);
		exit();
		
	}
	
	
	//下载重复订单的文件夹
	public function actiondownrepeatfolder() {
		$ret=array('status'=>0,'msg'=>'','err_data'=>array());
		try{
			$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
			$memcache = md5('getbrandsku'.$brandid);
			$boo = Yii::app()->cache->get($memcache);
			$arr=array();
			//缓存 不存在时候调用
			if(empty($boo)){
				$arr=Yii::app ()->db->createCommand ()
					->select ( 'code_start,code_end' )
					->from ( 'beu_brand' )->where ( "id=$brandid" )
					->queryRow ();
				if(!empty($arr))
				{
					$output = json_encode ( $arry );
					Yii::app()->cache->set($output,$memcache,18000);//添加缓存
				}
			}else{
				 $arr = json_decode($boo,true);
			}
			
			$order_array=array();
			$url=''; 
			
			if(Yii::app()->request->getParam('orderinfo')){
				$order_array=Yii::app()->request->getParam('orderinfo');//时间排序
			}
			
//			$order_array=array(array('orderid'=>'73','brandnumber'=>'115230C211262','ordername'=>'z005C007'),
//			array('orderid'=>'74','brandnumber'=>'115250C032734','ordername'=>'z005C007'),
//			array('orderid'=>'75','brandnumber'=>'115230E321115','ordername'=>'z005C008'),
//			array('orderid'=>'76','brandnumber'=>'115220A620517','ordername'=>'z005C009')
//			);			
			if(count($order_array)==0 || $brandid==0 || count($arr)==0 || empty($arr['code_end']))
			{
				$ret['msg']='信息错误';
				echo json_encode($ret);
				exit();
			}
			$rand=rand(0,9999999);
			$url='floderzip/repeat_skc_folder_'.$rand.'.zip'; 
			$zipname=$url;//服务器中zip的名字
			$zip=new ZipArchive();
			if($zip->open($zipname, ZipArchive::OVERWRITE)=== TRUE)
			{ 
				$start=$arr['code_start'];//品牌款号开始位置
				$end=$arr['code_end'];//品牌款号结束位置
				$orderclass=new orderclass(); 
				 foreach($order_array as $value)
			    {
			    	$brand_sku=$this->SKCToSKU($start,$end,$value['brandnumber']);
			    	$fileurl='orderlist/'.$value['ordername'].'/'.$brand_sku.'/'.$value['brandnumber'];
			    	//$fileurl='orderlist/'.$value['ordername'].'/'.$value['brandnumber'];
			    	$orderclass->addFileToZip($fileurl, $zip); 
			    }
			    $zip->close(); //关闭处理的zip文件
				//调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
			}
			
			$ret['status']=1;
			$ret['url']=$url;
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		
		echo json_encode($ret);
		exit();
		
	}
	
	//品牌自定义分类
	public function actionsetcustomcategory (){
	 	
		$brandid=$_SESSION['brandid'];
		$count=100;
 		$submit_status=1;
 		$mes="";
		//添加 修改标签
		if(strtolower ( $_SERVER ['REQUEST_METHOD'] ) == 'post')
		{
			//获取该品牌下的所有分类
			$brandcategory_obj = Yii::app()->db->createCommand()
				->select('*')->from('erp_brandcategory')
				->where('brandId=:brandId and status=:status',array(':brandId'=>$brandid,':status'=>0))
				->queryAll();
			
			$category_array=array();
			for($i=0;$i<100;$i++)
	 		{
	 			$category_array[$i]=array();
	 			$category_array[$i]['id']='';
	 			$category_array[$i]['name']='';
	 			
	 			if(Yii::app()->request->getParam('category_id_'.$i)){	
					$category_array[$i]['id']=Yii::app()->request->getParam('category_id_'.$i);
				}
	 			if(Yii::app()->request->getParam('category_name_'.$i)){	
					$category_array[$i]['name']=Yii::app()->request->getParam('category_name_'.$i);
				}
				
			}
			$category_name=array();
			for($i=0;$i<count($category_array);$i++)
	 		{
	 			if(!empty($category_array[$i]['id']) || !empty($category_array[$i]['name']))
	 			{
	 				if(!in_array($category_array[$i]['name'],$category_name))
		 			{
		 				$category_name[]=$category_array[$i]['name'];
		 			}else{
		 				$submit_status=0;
		 				$mes="分类名已创建";
		 			}
	 			}
	 		}
	 		
	 		if($submit_status==1)
	 		{
				for($i=0;$i<count($category_array);$i++)
		 		{
	 				
	 					if(empty($category_array[$i]['id']) && !empty($category_array[$i]['name']))
			 			{
			 				$erp_brandcategory = new  erp_brandcategory ();
							$erp_brandcategory->name=$category_array[$i]['name'];
							$erp_brandcategory->addtime=date("Y-m-d H:i:s");
							$erp_brandcategory->brandid=$brandid;
							$erp_brandcategory->status=0;
							$erp_brandcategory->insert ();
							
		 				}else if(!empty($category_array[$i]['id']))//分类id不为空，说明有对应的分类，做修改
			 			{
				 			$update_arr = array ();
				 			$update_arr['name']=$category_array[$i]['name'];
							erp_brandcategory::model ()->updateAll ( $update_arr, 'id=:textx', array (':textx' => $category_array[$i]['id'] ) );
						}
						$mes="编辑分类成功";
			 	 }
	 		}
		}
		//获取该品牌下的所有分类
		$brandcategory_obj = Yii::app()->db->createCommand()
					->select('*')->from('erp_brandcategory')
					->where('brandId=:brandId and status=:status',array(':brandId'=>$brandid,':status'=>0))->queryAll();
		$arry = array(
			'brandcategory_obj' => $brandcategory_obj,
			'submit_status' => $submit_status,
			'mes' =>$mes
		);
		
		echo $this->render('/erp/setcustomcategory',$arry); 
	}
	
	/**
	 * 分类对应的衣服数量
	 */
	public function actionselectclothescount()
	{
		$ret=array('status'=>0,'count'=>0,'msg'=>'','err_data'=>array());
		
		try{
			$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
			
			$categoryid=0;
			if(Yii::app()->request->getParam('categoryid')){
					$categoryid=Yii::app()->request->getParam('categoryid');//修改后的分类ID
			}
			if($brandid>0 && $categoryid>0)
			{
				$clothes_obj = Yii::app()->db->createCommand()
						->select('count(*) as count')->from('erp_clothes_order')
						->where('brandId=:brandId and brandcategoryid=:brandcategoryid',array(':brandId'=>$brandid,':brandcategoryid'=>$categoryid))->queryRow();
				if(!empty($clothes_obj))
				{
					$ret['count']=$clothes_obj['count'];
				}
				$ret['status']=1;
			}
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
		exit();
	}
	/**
	 * 删除分类
	 */
	public function actiondeletecategory()
	{
		$ret=array('status'=>0,'msg'=>'','err_data'=>array());
		
		try{
			$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
			$id=0;
			$categoryid=0;
			$where="id>0";
			if(Yii::app()->request->getParam('id')){
					$id=Yii::app()->request->getParam('id');//原分类ID
			}
			if(Yii::app()->request->getParam('categoryid')){
					$categoryid=Yii::app()->request->getParam('categoryid');//修改后的分类ID
			}
			if($id>0)
			{
				//修改分类
				Yii::app()->db
				->createCommand("update erp_clothes_order set brandcategoryid=$categoryid where brandcategoryid=$id and brandid=$brandid")
				->execute();
				//删除分类
				Yii::app()->db
				->createCommand("update erp_brandcategory set status=1 where id=$id and brandid=$brandid")
				->execute();
				$ret['status']=1;
			}
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		echo json_encode($ret);
		exit();
	}
	
	/**
	 * 保存日志信息
	 */
	public function actionsavelog()
	{
		$access_key='';
		$secret_key='';
		$log_string='';
		$domain='';
		$rand=md5(date("Y-m-d H:i:s"));
		
		
		if(Yii::app()->request->getParam('access_key')){
			$access_key=Yii::app()->request->getParam('access_key');
		}
		if(Yii::app()->request->getParam('secret_key')){
			$secret_key=Yii::app()->request->getParam('secret_key');
		}
		
		
		if(empty($access_key) || empty($secret_key))
		{
			echo "验证失败";
			exit();
		}
		$brand_obj = Yii::app()->db->createCommand()
				->select('brandid,domain')->from('erp_qiniu_account')
				->where('access_key=:access_key and secret_key=:secret_key',array(':access_key'=>$access_key,':secret_key'=>$secret_key))
				->queryRow();
		if(empty($brand_obj))	
		{
			echo '没有对应的品牌数据';
			exit();
		}
		$domain=$brand_obj['domain'];
		$logurl=$domain."log/op.log?".$rand;
		$log_string=file_get_contents($logurl);
		$brand_id=$brand_obj['brandid'];//品牌ID号
		
		
		//获取品牌 SKU 规则
		$brand_ret=Brand::brandSelectById($brand_id);
		if(count($brand_ret)==0){
			throw new Exception('');
		}
		$code_start=$brand_ret[0]['code_start'];
		$code_end=$brand_ret[0]['code_end'];
		
		
		if(empty($log_string))
		{
			echo "log data fail";
			exit();
		}
//		$command = Yii::app()->db->createCommand();
//		$array = array ("orderlog" =>$log_string, "addtime" => date('Y-m-d H:i:s'), "orderid" => $brand_id);
//		$command->insert('erp_orderlog', $array);
//		exit();
//		$log_string='ok|orderlist/z004X001/115230C211261/model/UQ15002441_B_100.jpg|2015-11-06 12:12:12
//ok|orderlist/z004X001/115230C2112/115230C211261/model/UQ15002441_B_101.jpg|2015-11-06 12:12:12
//fail|orderlist/z004X001/115250C0327/115250C032734/model/UQ15002441_B_102.jpg|2015-11-06 12:12:12
//fail|orderlist/z004X002/115230E3211/115230E321115/product/UQ15002441_A0_101.jpg|2015-11-06 12:12:12
//ok|orderlist/z004X003/115220A6205/115220A620517/product/UQ15002441_A2_101.jpg|2015-11-06 12:12:12
//ok|poster/15ss/dscn001.jpg|2015-11-06 12:12:12
//ok|poster/14ss/dscn002.jpg|2015-11-06 12:12:12';
		
		$log_array=explode("\n",$log_string);//按回车进行分隔，日志数组
		
		$log_array=array_filter($log_array);
		
		$order_array=array();//订单数组
		$brandnumber_array=array();//款号数组
		$order_name_array=array();//订单名数组
		$poster_array=array();//海报数组
		foreach($log_array as $value)
		{
			$string_array=explode(" | ",$value);//按"|"进行分隔,得到（状态|路径|时间）
			
			if(count($string_array)>0)
			{
				$geshi_array=array();//格式数组
				$geshi_array['url']='';//路径
				if($string_array[0]=='ok' || $string_array[0]=='fail')//状态必须是ok或者fail
				{
					$url_array=array();//路径数组
					$geshi_array['status']=$string_array[0];//状态
					$geshi_array['date']=$string_array[2];//时间
					if(!empty($string_array[1]))//路径不为空
					{
						$geshi_array['url']=$string_array[1];//路径
						$url_array=explode("/",$string_array[1]);//将url路径分隔为数组
						if(count($url_array)==6)//6层代表是订单图片
						{
							
							$order_array[$url_array[1]][]=$geshi_array;
							$brandnumber_array[$url_array[3]][]=$geshi_array;
							if(!in_array($url_array[1],$order_name_array))
							{ 
								$order_name_array[]=$url_array[1];//订单名数组
							}
						}
						
						if(count($url_array)==3)//3层代表是海报图片
						{
							$poster_array[$url_array[1]][]=$geshi_array;//海报数据数组
						}
					}
				}
			}
			
		}
		/***************海报****************开始**************/
		
		$key_string='';
		$poster_image_obj=array();
		$image_obj_count=0;
		if(count($poster_array)>0)
		{
			//查询一个款号下的所有的图片url
			$poster_image_obj=Yii::app()->db->createCommand()->select('url')
			->from('erp_poster')->where ("brandid=$brand_id")
			->queryAll();
			$image_obj_count=count($image_obj);
		}
		foreach($poster_array as $key=>$value)
		{
			if($key!=$key_string)
			{
				foreach($poster_array[$key] as $poster_array_value)
				{
					if($poster_array_value['status']=='ok')//如果上传成功
					{
						$ishave=false;//默认数据库中不存在
						foreach($poster_image_obj as $poster_image_obj_value)
						{
							if($poster_image_obj_value['url']==$poster_array_value['url'])
							{
								$ishave=true;
							}
						}
						if($ishave==false){
							$image_name='';//图片名字
							$image_url=explode("/",$poster_array_value['url']);
							if(count($image_url)>0)
							{
								$image_name=$image_url[count($image_url)-1];
							}
							$array = array ("name" =>$image_name,"url" => $poster_array_value['url'], "addtime" => $poster_array_value['date'], 
								"brandid" => $brand_id,"status" => 0,"dirname" => $key);
							$erp_image = Yii::app()->db->createCommand();
							$erp_image->insert('erp_poster', $array);
						}
					}
				}
			}
		}
		
		/***************海报****************结束**************/
		
		
		$order_name_obj=array();
		if(count($order_name_array)>0)
		{
			//获取所有需要的订单的ID号
			$order_name_obj=orderclass::select_order_id($brand_id,$order_name_array);
		}
		/***************订单日志表****************开始**************/
		$key_string='';
		
		foreach($order_array as $key=>$value)
		{
			if($key!=$key_string)
			{
				$log_show_string='';
				$key_string=$key;
				$success_pnum=0;//成功产品图数量
				$success_mnum=0;//成功模特图数量
				$success_dnum=0;//成功细节图数量
				$fail_pnum=0;//失败产品图数量
				$fail_mnum=0;//失败模特图数量
				$fail_dnum=0;//失败细节图数量
				$success_url='';//成功的图片url
				$fail_url='';//失败的图片url
				$date=date("Y-m-d H:i:s");
				foreach($order_array[$key] as $order_value)
				{
					$date=$order_value['date'];
					if($order_value['status']=='ok')//成功的图片
					{
						if(strpos($order_value['url'],'product')){
							$success_pnum++;
						}else if(strpos($order_value['url'],'model')){
							$success_mnum++;
						}else if(strpos($order_value['url'],'details')){
							$success_dnum++;
						}
						$success_url.=$order_value['url']."<br>";
					}
				}
				
				$log_show_string.="时间：".$date."<br>";
				$log_show_string.="产品图:成功".$success_pnum."张；模特图:成功".$success_mnum."张；细节图:成功".$success_dnum."张"."<br>";
				$log_show_string.="详情："."<br>";
				$log_show_string.="成功图片列表："."<br>";
				$log_show_string.=$success_url."<br>";
				
				$orderobj = Yii::app ()->db->createCommand ()->select ( 'id' )
					->from ( 'erp_order' )
					->where ( "ordername='".$key."'" )
					->queryRow ();
					
				if(!empty($orderobj))
				{
					$command = Yii::app()->db->createCommand();
					$array = array ("orderlog" =>$log_show_string, "addtime" => $date, "orderid" => $orderobj['id']);
					$command->insert('erp_orderlog', $array);
				}
			}
		}
		/***************订单日志表****************结束**************/
		/***************订单衣服表****************开始**************/
		//通过款号录入衣服信息
		$key_string='';
		$clothesorder=new clothesorderclass();
		foreach($brandnumber_array as $key=>$value)
		{
			if($key!=$key_string)
			{
				$order_obj=array();//款号所属的订单数
				//查询是否录入过此款号
				$brand_obj=Yii::app()->db->createCommand()->select('id')
				->from('erp_clothes_order')
				->where ("brandnumber='".$key."' and brandid=$brand_id and status=0")
				->queryRow();
				if(empty($brand_obj))//没有数据，说明没添加过
				{
					$orderid=0;
					if(count($order_name_obj)>0)//订单名存在时
					{
						foreach($brandnumber_array[$key] as $brandnumber_value)
						{
							
							$image_url=explode("/",$brandnumber_value['url']);
							$order_name=$image_url[1];
							foreach($order_name_obj as $order_name_obj_value)
							{
								if($order_name==$order_name_obj_value['ordername'])
								{
									$orderid=$order_name_obj_value['id'];
									break;
								}
							}
							
							break;
						}
					}
				//将SKC转为SKU
					$brandnumber_SKU=$key;
					if($code_start!=0 || $code_end!=0){//设置了款号规则后 按规则提取款号
						$brandnumber_SKU=$this->SKCToSKU($code_start,$code_end,$key);
					}
					$clothes_order_id=$clothesorder->create_erp_clothes($brandnumber_SKU,$key,$orderid,$brand_id);
					/*$array = array ("sku"=>$brandnumber_SKU,"brandnumber" =>$key, "pcount" => 0, 
					"mcount" => 0,"dcount" => 0,"description" => '',
					"orderid" => $orderid,"brandcategoryid" => 0,"addtime" => date("Y-m-d H:i:s"),
					"brandid" => $brand_id,"status" => 0);
					$command = Yii::app()->db->createCommand();
					$command->insert('erp_clothes_order', $array);
					$clothes_order_id= Yii::app()->db->getLastInsertID();
					*/
					$clothes_image_array=array("clothes_order_id" =>$clothes_order_id,"tablename" =>"erp_image");
					$erp_clothes_image = Yii::app()->db->createCommand();
					$erp_clothes_image->insert('erp_clothes_image', $clothes_image_array);
				}else{
					$clothes_order_id=$brand_obj['id'];
				}
				
				//查询一个款号下的所有的图片url
				$image_obj=Yii::app()->db->createCommand()->select('url,type')
				->from('erp_image')->where ("clothes_order_id=$clothes_order_id and isshow=0")
				->queryAll();
				$image_obj_count=count($image_obj);
				foreach($brandnumber_array[$key] as $brandnumber_value)
				{
					if($brandnumber_value['status']=='ok')//如果上传成功
					{
						$ishave=false;//默认数据库中不存在
						if($image_obj_count>0)//一个款号下的所有的图片数量
						{
							foreach($image_obj as $image_obj_value)
							{
								//找到相同的url了
								if($image_obj_value['url']==$brandnumber_value['url'])
								{
									$ishave=true;
									break;
								}
							}
						}
						if($ishave==false)
						{
							$image_name='';//图片名字
							$image_url=explode("/",$brandnumber_value['url']);
							if(count($image_url)>0)
							{
								$image_name=$image_url[count($image_url)-1];
//								if(strpos($image_name,'.'))
//								{
//									$name=explode(".",$image_name);
//									$image_name=$name[0];
//								}
							}
							//A图、B模特图、C细节图、D海报图
							$type=1;//默认为产品图
							if(strpos($brandnumber_value['url'],'model'))
							{
								$type=3;
							}else if(strpos($brandnumber_value['url'],'details'))
							{
								$type=4;
							}
							$array = array ("name" =>$image_name, "status" => 0,"addtime" => $brandnumber_value['date'], 
								"updatetime" => $brandnumber_value['date'],"istop" => 0,"type" => $type,
								"url" => $brandnumber_value['url'],"isshow" => 0,"isback" => 0,"clothes_order_id" => $clothes_order_id);
							$erp_image = Yii::app()->db->createCommand();
							$erp_image->insert('erp_image', $array);
						}
					}
				}
				
			}
		}
		/***************订单衣服表****************结束**************/
		echo "success";
		exit();
	}
	/**
	 * 平台裁剪设置
	 */
	public function actionsetpatform()
	{
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
		$count=100;
 		$submit_status=1;
 		$mes="";
 		//获取该品牌下的所有分类
		$patform_obj = patformclass::select_erp_patform();
		//添加 修改标签
		if(strtolower ( $_SERVER ['REQUEST_METHOD'] ) == 'post')
		{
			//总个数
			$patform_count=Yii::app()->request->getParam('patformcount');
			
			for($i=1;$i<=$patform_count;$i++)
	 		{
	 			$cut_array=array();//存储裁剪信息的数组
	 			$cut_array['patformid']=0;
	 			$cut_array['width']=0;//裁剪宽
	 			$cut_array['height']=0;//裁剪高
	 			$cut_array['barcodetype']=0;//0为skc，1为sku
	 			$cut_array['status']=0;//0为显示，1为删除
	 			$cut_array['havewatermark']=0;//0为有，1为没有
	 			$cut_array['watermarkwidth']=0;//水印宽
	 			$cut_array['watermarkheight']=0;//水印高
	 			$cut_array['positionx']=0;//水印x坐标
	 			$cut_array['positiony']=0;//水印y坐标
	 			$cut_array['addtime']=date("Y-m-d H:i:s");
	 			$id=0;
	 			if(Yii::app()->request->getParam('id'.$i)){	
					$id=Yii::app()->request->getParam('id'.$i);
				}
	 			if(Yii::app()->request->getParam('patform'.$i)){	
					$cut_array['patformid']=Yii::app()->request->getParam('patform'.$i);
				}
	 			if(Yii::app()->request->getParam('width'.$i)){	
					$cut_array['width']=Yii::app()->request->getParam('width'.$i);
				}
	 			if(Yii::app()->request->getParam('height'.$i)){	
					$cut_array['height']=Yii::app()->request->getParam('height'.$i);
				}
	 			if(Yii::app()->request->getParam('cutradio'.$i)){	
					$cut_array['barcodetype']=Yii::app()->request->getParam('cutradio'.$i);
				}
	 			if(Yii::app()->request->getParam('waterradio'.$i)){	
					$cut_array['havewatermark']=Yii::app()->request->getParam('waterradio'.$i);
				}
	 			if(Yii::app()->request->getParam('watermarkwidth'.$i)){	
					$cut_array['watermarkwidth']=Yii::app()->request->getParam('watermarkwidth'.$i);
				}
	 			if(Yii::app()->request->getParam('watermarkheight'.$i)){	
					$cut_array['watermarkheight']=Yii::app()->request->getParam('watermarkheight'.$i);
				}
	 			if(Yii::app()->request->getParam('positionx'.$i)){	
					$cut_array['positionx']=Yii::app()->request->getParam('positionx'.$i);
				}
	 			if(Yii::app()->request->getParam('positiony'.$i)){	
					$cut_array['positiony']=Yii::app()->request->getParam('positiony'.$i);
				}
				if($cut_array['patformid']!=0 && $brandid!=0)
				{
					if($id==0)
					{
					 	$cut_obj=cut_detaileclass::select_cut_detailebypatformid($brandid,$cut_array['patformid']);
						//var_dump($cut_obj);
						if(!empty($cut_obj))
						{
							//通过裁剪平台id号修改信息
							cut_detaileclass::updatecutdetailbypatformid($brandid,$cut_array['patformid'],$cut_array);
						}else{
							cut_detaileclass::insertcutdetail($brandid,$cut_array);
						}
					}else{
						cut_detaileclass::updatecutdetailbyid($id,$cut_array);//通过裁剪id号修改信息
					}
				}
			}
		}
		
		$cut_detaile_obj=cut_detaileclass::select_erp_cut_detaile($brandid);
		$watermark_obj=watermarkclass::select_erp_watermark($brandid);
		
		$watermark_url="";
		$watermark_url2="";
		if(count($watermark_obj)>0)
		{
			foreach ($watermark_obj as $value)
			{
				if($value['type']==1)
				{
					$watermark_url=$value['url'];
				}
				else if($value['type']==2){
					$watermark_url2=$value['url'];
				}
			}
		}
		
		$arry = array(
			'cut_detaile_obj' => $cut_detaile_obj,
			'watermark_url' => $watermark_url,
			'watermark_url2' => $watermark_url2,
			'patform_obj'=>$patform_obj,
			'mes' =>$mes
		);
		
		echo $this->render('/erp/setpatform',$arry); 
	}
	

	/**
	 * 推送页面
	 */
	public function actionpushlist()
	{
		$res=array("status"=>0,"mes"=>"","data"=>array());
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;//品牌ID
		$userid=$_SESSION['user_id'];//用户ID
		$patformid=Yii::app()->request->getParam("patformid");//平台ID
		//以逗号分隔款号所对应的ID，是加密的，需转换为ID号
		$clothes_string=Yii::app()->request->getParam("clothes_string");
		$clothes_m_array=explode(",",$clothes_string);
		$clothes_id_array=array();
		foreach($clothes_m_array as $value){
			
			$id=Comm::strdecipher($value);
			$clothesorder=new clothesorderclass();
			$c_ret=$clothesorder->select_Single_clothes($_SESSION['brandid'],array('id'=>$id));
			$ret_sku=$c_ret['sku'];
			if($this->Website_template!='SKC'){//获取SKU下所有的SKC信息
				$sku_ret=$clothesorder->select_all_clothes($_SESSION['brandid'],-1,array('sku'=>$ret_sku));
				foreach($sku_ret['data'] as $sku_value){
					$clothes_id_array[]=$sku_value;
				}
			}else{
				//通过衣服skcID查找skc对应的信息
				$clothes_id_array[]=$c_ret;
			}
		}
		
		
		
		
		//获取平台信息
		$cut_class=new cut_detaileclass();
		$cut_obj=$cut_class->select_erp_cut_detaile($brandid);	
		$cutid=0;
		$cut_array=array();
		foreach($cut_obj as $value)
		{
			if($value['patformid']==$patformid)
			{
				$cutid=$value['id'];
				$cut_array['cut_obj']=$value;
				break;
			}
		}
		
		//水印信息
		$watermark_obj=watermarkclass::select_erp_watermark($brandid);
		
		$pushDetaileclass=new pushDetaileclass();
		foreach($clothes_id_array as $key=>$value)
		{
			$pushdetaile_obj=$pushDetaileclass->select_all_push($brandid,array("cutid"=>$cutid,"pushstatus"=>0,"skc"=>$value['brandnumber']));
			foreach($pushdetaile_obj['data'] as $push_key=>$push_value)
			{
				$pushdetaile_obj['data'][$push_key]['patformid']=$patformid;
				foreach($watermark_obj as $watermark_value)
				{
					if($watermark_value['id']==$push_value['watermarkid'])
					{
						$pushdetaile_obj['data'][$push_key]['watermarkurl']=$watermark_value['url'];
						break;
					}
				}
			}
			$clothes_id_array[$key]['push_detaile']=json_encode($pushdetaile_obj['data']);
		}
		$res['status']=1;
		$res['cut_obj']=$cut_array['cut_obj'];
		$res['data']=$clothes_id_array;
		echo $this->render('/erp/pushlist',$res); 
		//echo json_encode($res);
	
		exit();
	}
	/**
	* 推送图片
	**/
	public function actionpushimage() 
	{
		
		$res=array("status"=>0,"mes"=>"","data"=>array(),"image_url"=>"");
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;//品牌ID
		$userid=$_SESSION['user_id'];//用户ID
		$push_detaile=Yii::app()->request->getParam("push_detaile");//推送信息
		$cut_detaile=Yii::app()->request->getParam("cut_detaile");//裁剪信息
		$imageid=Yii::app()->request->getParam("imageid");//图片ID
		$skc=Yii::app()->request->getParam("skc");//衣服skc
		
		
		try{
			//通过图片ID查找图片信息
			$image_obj = Yii::app ()->db->createCommand ()
					->select ( 'url,name' )
					->from ( 'erp_image' )
					->where ( "id=$imageid" )
					->queryRow ();
			$image_url="";
			$image_name=""; 
			if(!empty($image_obj))
			{
				$image_url=$image_obj['url'];//衣服图片原图
				$res['image_url']=$image_obj['url'];//衣服名 
			}else{
				throw new Exception('图片信息无法获取');
			}
			                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
			$pushdetaile_class=new pushDetaileclass();
			
			$array=array(
			"watermark_id"=>$push_detaile['watermarkid'],
			"watermark_width"=>$cut_detaile['watermarkwidth'],
			"watermark_height"=>$cut_detaile['watermarkheight'],
			"watermark_url"=>$push_detaile['watermarkurl'],
			"watermark_positionx"=>$cut_detaile['positionx'],
			"watermark_positiony"=>$cut_detaile['positiony'],
			"patform_width"=>$cut_detaile['width'],
			"patform_height"=>$cut_detaile['height'],
			"image_url"=>$image_url,
			"cut_positionx"=>$push_detaile['positionx'],
			"cut_positiony"=>$push_detaile['positiony'],
			"cut_width"=>$push_detaile['CutOut_width'], 
			"cut_height"=>$push_detaile['CutOut_height'],
			"img_w"=>$push_detaile['img_w'], 
			"img_h"=>$push_detaile['img_h']
			);
			
			$end_obj=$pushdetaile_class->cut_image($array,$brandid);
			
			//print_r($end_obj);
			if($end_obj['status']==0){
				throw new Exception('图片生成失败');
			}
			
			$qiniu_image_url=$end_obj['url'];
			$qiniu_array=$end_obj['qiniu_array'];//裁剪完成的数据
			if(count($qiniu_array)==0)
			{
				throw new Exception('七牛账户信息有误');
			}
			//access_key、secret_key、space、domain
			$status=1;//1为失败，0为成功
			$content=@file_get_contents($qiniu_image_url);
					
			
			if($content)
			{
				$imagename=md5($qiniu_image_url);
				$image=$content;
				$ProcessImagepath=$_SERVER ['DOCUMENT_ROOT'] ."/uploads/image/".$imagename.".jpg";
				$fp = fopen ( $ProcessImagepath, 'w' );
				fwrite ( $fp, $image); //写入文件
				fclose ( $fp );
				$image_array=explode("/",$image_url);
				$file="";
				$image_count=count($image_array);
				for ($i=0;$i<$image_count;$i++ )
				{
					if($i==$image_count-1){
						$file.=$imagename.".jpg";
					}else{
						$file.=$image_array[$i]."/";
					}
				}
				
				$key3=$file;
				$bucket=$qiniu_array[0]['space'];
				$auth = new Auth($qiniu_array[0]['access_key'], $qiniu_array[0]['secret_key']);
				$bucketMgr = new BucketManager($auth);
				//删除$bucket 中的文件 $key
				$err = $bucketMgr->delete($bucket, $key3);
				
				$uploadfile_ret=$this->qinuu_upload($file,$ProcessImagepath);
				
				@unlink($ProcessImagepath);
				if(!isset($uploadfile_ret[0]['hash'])){
					throw new Exception('图片上传失败');
				}
				/*******推送到平台***start***/
				
				/*******推送到平台***end***/
				$status=0;
				//修改推送数据
				$pushdetaile_class->update_push($push_detaile['id'],array('pushstatus'=>1,'cut_src'=>$file));
				$res['status']=1;
				
			}
			
			$pushcount_array=array('skc'=>$push_detaile['skc'],'sku'=>$push_detaile['sku'],'addtime'=>date("Y-m-d H:i:s"),'patformid'=>$cut_detaile['patformid'],'status'=>$status,'clothesimageid'=>$imageid,'userid'=>$userid,'brandid'=>$brandid);
			$pushcount_class=new pushcountclass();	
			$pushcount_class->insert_push_count($pushcount_array);//插入信息
			$res['id']=$push_detaile['imageid'];
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}	
		echo json_encode($res);
		exit();
		
	}
	/**
	 * 推送统计页面
	 */
	public function actionpushcountinfo()
	{
		
		$ret=array('status'=>0,'mag'=>'');
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
		$userid=$_SESSION['user_id'];
		
		$page = 1;
		$pagecount=5;
		$keyword="";
		$startdate="";
		$type="1";
		$havedata=0;
		if (Yii::app ()->request->getParam ( 'keyword' )) {
			$keyword =  Yii::app ()->request->getParam ( 'keyword' );
			$arr['key'] = $keyword;
		}
		if (Yii::app ()->request->getParam ( 'startdate' )) {
			$startdate = Yii::app ()->request->getParam ( 'startdate' );
			$arr['strd_data'] = $startdate;
		}
		if (Yii::app ()->request->getParam ( 'enddate' )) {
			$enddate = Yii::app ()->request->getParam ( 'enddate' );
			$arr['end_data'] = $enddate;
		}
		
		if(Yii::app()->request->getParam('patformid')){
			$patfromid=Yii::app()->request->getParam('patformid');//时间排序
			$arr['patformid'] = $patfromid;
		}
		$arr['brandid']=$brandid;
		if (Yii::app ()->request->getParam ( 'page' )) {
			$page = Yii::app ()->request->getParam ( 'page' );
		}
		$data=array();
		//通过条件查询订单表
		$push_obj=pushcountclass::select_all_push($page,$pagecount,$brandid,$arr);
		if($push_obj['status']!=0){
			$data=$push_obj['data'];
		}
		
		$datearray=pushcountclass::select_push_date($brandid);//推送的时间列表
		$patform_obj=patformclass::select_erp_patform();//平台信息
		$prme_arr=array('data'=>$data,
				'brandid'=>$brandid,
				'strd_data'=>$startdate,
				'end_data'=>$enddate,
				'key'=>$keyword,
				'patform_obj'=>$patform_obj,
				'patfromid'=>$patfromid,
				'datearray'=>$datearray);
		
		
		$this->render ( 'pushcountinfo', $prme_arr);
		
	}
	
	/**
	 * 删除裁剪信息
	 */
	public function actiondelcutdetaile()
	{
		$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
		$id=Yii::app()->request->getParam('id');//原分类ID
		$cut_array=array();//存储裁剪信息的数组
	 	$cut_array['status']=1;//0为显示，1为删除
	 	if($id>0){
			cut_detaileclass::updatecutdetailbyid($id,$cut_array);
	 	}
		echo $this->redirect('/erp/setpatform'); 
	}
	/**
	 * 删除水印信息
	 */
	public function actiondelwatermark()
	{ 
		
		$res=array("status"=>0,"mes"=>"","data"=>array(),"image_url"=>"");
		try{
			
			$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
			$type=Yii::app()->request->getParam('type');//原分类ID
			
			watermarkclass::delwatermarkbytypeandbrandid($type,$brandid);
		 	$res['status']=1;
	 	}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}	
		echo json_encode($res);
		exit();
	}
	/**
	 * 上传水印图
	 * 
	 */
	public function actionuploadwatermarkimg()
	{
		
		$ret=array('status'=>0,'msg'=>'','url'=>'','err_data'=>array());
		$imgname="";
		try{
			$brandid=isset($_SESSION['brandid'])?$_SESSION['brandid']:0;
			$id=0;
			$categoryid=0;
			$where="id>0";
			if(strtolower ( $_SERVER ['REQUEST_METHOD'] ) == 'post')
			{
				if(Yii::app()->request->getParam('type')){
					$type=Yii::app()->request->getParam('type');//原分类ID
				}
				if($type==1 || $type==2)
				{
					$imgname='watermark'.$type;
					
					$images = Common::upload ( $imgname, 'uploads/watermark/' );
					if ($images == '文件为空' || $images == "文件太大" || $images == '只能上传图像' || $images == '上传图片失败') //表示页面图片更改过
					{
						 $ret['msg']=$images;          
					}else{
						$array=array();
						$array['name']="";
						$array['type']=$type;
						$array['url']=$images;
						//查该品牌下是否有水印信息
						$watermarklist=watermarkclass::select_erp_watermark($brandid,$type);
						if(count($watermarklist)>0)
						{
							$url=$_SERVER ['DOCUMENT_ROOT'].'/'.$watermarklist[0]['url'];
							@unlink($url);
							//修改水印信息
							
							$result=watermarkclass::updatewatermarkbyid($watermarklist[0]['id'],$array);
							 
						}else{
							//插入水印信息 
							$result=watermarkclass::insertwatermarkimg($brandid,$array);
						}	
						if($result)
						{
							$ret['type']=$type;
							$ret['url']=$images;
							$ret['status']=1;
						}
						
					}
				}
				
			}
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
		//echo json_encode($ret);
		echo '<script> parent.setsubclothes('.json_encode( $ret).');</script>';
		exit();
	}
	/**
	* 官网推送
	**/
	public function actionPushWebsite(){
		$ret=array('status'=>0,'msg'=>'');
		try{
			$data=array(
				'app'=>'beu_PushWebsite',
				'key'=>'DJksjus@GAP_sY1Ld',
				'date'=>time()
				);
			$data['sig']=md5($data['app'].$data['key'].'beubeu'.$data['date']);
			$control=Yii::app()->runController('/api/getApi/app/'.$data['app'].'/key/'.$data['key'].'/date/'.$data['date'].'/sig/'.$data['sig']);
			//$apiClass=new ApiController();
			//$ret_dd=$apiClass->actiongetApi();
			print_r($control);
		}catch(Exception $e){
			$ret['msg']=$e->getMessage();
		}
	}
	/**
	* 将10进制转为36位条码
	**/
	public function from10to36($shu)
	{
		 $zifu = "";
         while ($shu!=0){
         $linshi = bcmod($shu,36);
                 if ($linshi>=10)
                  {
                  $zifu.= chr(($linshi+55));
                  }else{
                       $zifu.= $linshi;
                  }
        
                  
         $shu = intval(bcdiv($shu,36));
         }    
        return strrev($zifu);
	}
	
	/**
	* 品牌号生成5位编码
	**/
	function createBrandCode($brandid){
		$user36=$this->from10to36($brandid);//用品牌ID号生成16进制，至少需要3为数字不如补零
		if(strlen($user36)==1)
			$user36='z000'.$user36;
		else if(strlen($user36)==2)
			$user36='z00'.$user36;
		else if(strlen($user36)==3)
			$user36='z0'.$user36;
		else if(strlen($user36)==4)
			$user36='z'.$user36;
		return $user36;
	}
	/**
	* 将品牌SKC转为SKU
	**/
	function SKCToSKU($code_start,$code_end,$brandnumber){
		return substr($brandnumber,$code_start,($code_end-$code_start));
	}
	
	/**
	* 七牛上传图片
	* file_name 上传文件名
	* file_data 文件二进制
	**/
	function qinuu_upload($file_name,$file_data,$file_to_name=0){
		$qiniu=new erp_qiniu();//七牛的接口类
		$brand_qiniu_account=$qiniu->getAccountByBrand($_SESSION['brandid']);//获取品牌的七牛子账号信息
		if(count($brand_qiniu_account)==0){
			throw new Exception('品牌暂未绑定七牛帐号');
		}
		//获取品牌的七牛权限
		$auth=new Auth($brand_qiniu_account[0]['access_key'],$brand_qiniu_account[0]['secret_key']);
		// 生成上传 Token
		$token = $auth->uploadToken($brand_qiniu_account[0]['space']);
		
		//创建七牛资源类
		$Upload= new UploadManager();
		$ret=$Upload->putFile($token,$file_name,$file_data);
		return $ret;
	}
	
}