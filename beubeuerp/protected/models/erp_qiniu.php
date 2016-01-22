<?php
/**
 * 触摸屏类
 */

class erp_qiniu{
	public $f_username='2210108192@qq.com';
	public $f_password='baiyi1716';
	/**
	 * 七牛中获取账户凭证
	 * @param  $username 七牛登录账号一般为邮箱
	 * @param  $password 七牛登录密码
	 */
	public  function oauth2_token($username,$password) {
		$phppost=new PhpPost();//post提交
		$access_token='';
		$url = "https://acc.qbox.me/oauth2/token";
        $data = array('grant_type'=>'password','username'=>$username,'password'=>$password);
		$data = http_build_query($data);
        $result = $phppost->curl_post($url, $data);
        $result= json_decode($result,true);
        if(count($result)>0 && isset($result['access_token']))
        {
       		$access_token=$result['access_token'];
        }
       return $access_token;
    }
	
	/**
	 * 七牛中获取账户信息
	 * @param  $username 七牛登录账号一般为邮箱
	 * @param  $password 七牛登录密码
	 * @param  $access_token 授权码
	 */
	public  function user_info($username,$password,$access_token) {
		$phppost=new PhpPost();//post提交
		$url = "https://acc.qbox.me/user/info";
       	$data = array('grant_type'=>'password','username'=>$username,'password'=>$password);
		$data = http_build_query($data);
		$user_result = $phppost->curl_post($url, $data,$access_token);
		$user_array= json_decode($user_result,true);
        return $user_array;
    }
    /**
	 * 获取品牌的七牛子账户信息
	 * @param  $brandid 品牌号
	 */
	public function getAccountByBrand($brandid){
		$data=Yii::app ()->db->createCommand ()
				->select ( '*' )
				->from ( 'erp_qiniu_account' )
				->where('brandid=:brandid',array(':brandid'=>$brandid))
				->queryAll();
		return $data;
	}
	function urlsafe_base64_encode($data) {
	   $data = base64_encode($data);
	   $data = str_replace(array('+','/'),array('-','_'),$data);
	   return $data;
	}
	
	/**
	 * 获取hmac_sha1签名的值
	 * @link 代码来自： http://www.educity.cn/develop/406138.html
	 * 
	 * @param $str 源串
	 * @param $key 密钥
	 *
	 * @return 签名值
	 */
	function hmac_sha1($str, $key) {
		$signature = "";
		if (function_exists('hash_hmac')) {
			$signature = hash_hmac("sha1", $str, $key);
		}else {
			$blocksize = 64;
			$hashfunc = 'sha1';
			if (strlen($key) > $blocksize) {
				$key = pack('H*', $hashfunc($key));
			}
			$key = str_pad($key, $blocksize, chr(0x00));
			$ipad = str_repeat(chr(0x36), $blocksize);
			$opad = str_repeat(chr(0x5c), $blocksize);
			$hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $str))));
			$signature =$hmac;
		}
		return $signature;
	}
}