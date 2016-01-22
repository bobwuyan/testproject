<?php
define('TIMESTAMP', time());
//注意f70d98blSxC7pNYU此需和discuz X config目录下的配置文件中的secuty中auth一样.
define('AUTHCODE', md5("f70d98blSxC7pNYU"));
class usercookie
{
	static $static_session=array('Tmall_access_token'=>'','Checknum'=>'','Tmall_request_url'=>'');//不可自动清除的session
	/**
	 * 设置seesion和cookie
	 * @param $userid 用户ID号
	 * @param $username 用户名
	 * @param $password 加密后的密码
	 */
	static public function userSet($userid, $username,$password) 
	{
		$_SESSION ['userid'] = $userid;//将用户ID号存入session中
		$_SESSION['username'] = $username;//用户名存入session中
		setcookie("userinfo","", time()-3600*24);//删除cookie
		setcookie ("userinfo",usercookie::authcode ( "{$userid}\t{$username}\t{$password}", 'ENCODE' ), time ()+(3600*2) , "/");//+ (3600*2)
	}
	/**
	 * 检查cookie中是否有用户信息
	 */
	static public function userCheckCookie(){
		$ret=false;
		//cookie和session 有一个不存在就退出
		if(!isset($_COOKIE ['userinfo']) || !isset($_SESSION ['userid']) || empty($_SESSION ['userid'])){
			self::userLoginOut();//退出登陆
		}else if(!empty($_SESSION ['userid'])){//如果session存在 表示当前帐号已登录 需要更新帐号最后操作时间
			//echo 2;
			$status=permission::userSeleteStatus($_SESSION ['userid']);
			//print_r($status);
			if($status['status']==0){//说明可登录
				//更新临时表的时间
				permission::usermodelUpdateByUserid($_SESSION ['userid'],2);
				$ret=true;
			}else{
				self::userLoginOut();//退出登陆
			}
		}else{	
			$ret=true;
		}
		if($ret){
			$auth = usercookie::daddslashes ( explode ( "\t", usercookie::authcode ( $_COOKIE ['userinfo'], 'DECODE' ) ) ); //
			list ( $cookieUserid,$cookieUsername ,$cookiePassword ) = empty ( $auth ) || count ( $auth ) < 3 ? array ('','','' ) : $auth; //赋值auth至userid和username和密码
			usercookie::userSet($cookieUserid,$cookieUsername ,$cookiePassword);
		}
		return $ret;
	}
	/**
	 * 用户登出
	 * @return true删除成功，不为true是删除失败提示
	 */
	 
	static public function userLoginOut(){
		if(isset($_SESSION ['userid']) && !empty($_SESSION ['userid'])){
			permission::usermodeDeleteByUserid($_SESSION ['userid']);//删除状态临时表该用户的数据
		}
		$static_session=self::$static_session;
		foreach($static_session as $s_key=>$value){
			if(isset($_SESSION[$s_key])){
				$static_session[$s_key]=$_SESSION[$s_key];//session 保存
			}
		}
		$_SESSION=array();
		foreach($static_session as $s_key=>$value){
			if(!empty($value)){
				$_SESSION[$s_key]=$static_session[$s_key];
			}
		}
		$cookie = new CHttpCookie('userinfo',Yii::app()->params['web_server_host']);
		$cookie->expire = time()-3600*24;  //删除cookie
		Yii::app()->request->cookies['userinfo']=$cookie;
	}
	
	/**
	 * cookie auth解码,和discuz X一样
	 * @param $string
	 * @param $operation
	 * @param $key
	 * @param $expiry
	 */
	static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = md5($key != '' ? $key : AUTHCODE);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	
	}
	/**
	 * 对输入进行转义,防止'"等出现
	 * @param $string
	 * @param $force
	 */
	static function daddslashes($string, $force = 1) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				unset($string[$key]);
				$string[addslashes($key)] = usercookie::daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
		return $string;
	}
	
}
