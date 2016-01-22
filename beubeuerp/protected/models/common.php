<?php
define('TIMESTAMP', time());

//注意f70d98blSxC7pNYU此需和discuz X config目录下的配置文件中的secuty中auth一样.
define('AUTHCODE', md5("f70d98blSxC7pNYU".$_SERVER['HTTP_USER_AGENT']));

class  Common
{
	
	static protected $in;       
	static protected $out;       

	/**
	 * 防攻击过滤
	 * @param $str
	 */
	static function common_htmlspecialchars($str)
	{
        $str = preg_replace('/&(?!#[0-9]+;)/s', '&amp;', $str);
        $str = str_replace(array('<', '>', '"','script',"'",'create','select','update'), array('&lt;', '&gt;', '&quot;','','&#39;','','',''), $str);

        return $str;
	}
	
	//帝国CMS 新闻列表地址连接
	static public function newcmsurl($classid,$newspath,$id){
		db::dbcms();
		global $dbcms;
		if($classid==''|| $classid==null || $newspath==''|| $newspath==null || $id==''|| $id==null)
		{
			return 12;
		}
		else {
		$select = $dbcms->select();
		$select = $select->from("phome_enewsclass");
		$select->where("classid =?",$classid);
		$sql = $select->__toString();
		$obj = $dbcms->fetchRow($sql);
		$url = "http://news.beubeu.com/".$obj['classpath']."/".$newspath."/".$id.".html";
		
		return $url;
		}
	}
		
	/**
	 * 获取远程URL,并带缓存$url是URL,time是缓时间,秒为单位,默认300
	*/
	static public function geturl($url,$time=300)
	{
		$md5=md5($url);
    	$cached = zend_shm_cache_fetch($md5);//从内存缓存读取
    	
    	if ($cached=== false || !empty($_GET['update'])) //读取失败或带有update命名强制更新
    	{
	    	$cached=file_get_contents($url); 
			zend_shm_cache_store($md5, $cached, $time);//存至内存，最后一个是过期时间，单位为秒
			
			return $cached;
    	}
		else
		{
			return $cached;
		}
	}
	
	//触摸屏使用模特查询  返回模特类型
	static public function tmodel($id){
		db::db1();
		global $db;

		$select = $db->select();
		$select->from("b_category"); //权限表
		$select->where("id in ($id)");
		$sql = $select->__toString();
		$obj = $db->fetchAll($sql);
		
		$o=0;
		foreach ($obj as $obj2){
			if($o){
				$sr .= ','.$obj2["title"];
			} 
			else{
				$sr = $obj2["title"];
				$o = 1;
			}
			
		}
		return $sr;
	}
	
	//获得模特类型	b_category style=9
	static public function  model_type(){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_category"); //权限表
		$select->where("style=9");
		$sql = $select->__toString();
		$obj = $db->fetchAll($sql);
		
		return $obj; 
	}
	
	//获取模特头像 图片
	static public function model_img($id){
		db::db1();
		global $db;
		
		if(empty($id)){
			$id=0;
		}
		
		$select = $db->select();
		$select->from("b_modelhead",array('image')); //权限表
		$select->where("id=?",$id);
		$sql = $select->__toString();
		$obj = $db->fetchOne($sql);
		
		return $obj; 
	}
	
	
	
	//查询用户类型
	static public function admintype($type){
		$array = array('','总管理员','ONLYLADY','品牌','品牌店铺','商场触摸屏中的品牌店铺','商场','子屏');
		if(empty($type)){
			return '';
		}else{
			return $array[$type];
		}
	}
	
	//查询触摸屏 所有信息  
	static public function tconfigall(){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("tconfig");
		$sql = $select->__toString();
		$tconfig = $db->fetchAll($sql);
		
		return $tconfig;
	}
	//通过品牌查询触摸屏
	static public function tconfigbybrandid($shopid,$brandid){
		db::db1();
		global $db;
		$where="id>0";
		if($brandid!=0){
			$where.= "&& brandid=$brandid";
		}
		if($shopid !=0 ){
			$where .= "&& shopid=$shopid";
		}
		$select = $db->select();
		$select->from("tconfig");
		$select->where($where);
		$sql = $select->__toString();
		$tconfig = $db->fetchAll($sql);
		
		return $tconfig;
	}	
	/**
	 * 获取触摸屏编号 tshopbrandtouch
	 * #$shopid 0时为空
	 * #$brandid 0时为空
	 */
	static public function tshopbrandtouch($shopid,$brandid){
		db::db1();
		global $db;
		
		if($shopid !=0){
			$where = "shopid=$shopid";	
		}
		if($brandid!=0){
			$where = "brandid=$brandid";
		}
		if($shopid !=0 && $brandid!=0){
			$where = "shopid=$shopid && brandid=$brandid";
		}
		
		$select = $db->select();
		$select->from('tshopbrandtouch',array('touchid'));
		$select->where($where);
		$sql = $select->__toString();
		$touchid = $db->fetchOne($sql);
		
		return $touchid;
	}
	
	//查询所有品牌 店铺
	static public function brandshopall(){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_brandshop");
		$sql = $select->__toString();
		$brandshop = $db->fetchAll($sql);
		return $brandshop;
	}
	
	
	
	//查询品牌 所有
	static public function tbrandall(){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_brand");
		$sql = $select->__toString();
		$tbrandall = $db->fetchAll($sql);
		
		return $tbrandall;
	}
	
	//查询触摸屏名
	static public  function tconfigname($id){
		db::db1();
		global $db;
		
		if(!empty($id)){
		$select = $db->select();
		$select->from("tconfig",array('name'));
		$select->where("id=?",$id);
		$sql = $select->__toString();
		$tconfigname = $db->fetchOne($sql);
		
		return $tconfigname;
		}
		else{
			return "";
		}
	}
	//通过机器编号查询触摸屏信息
	static public  function tconfigallbyid($id){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("tconfig");
		$select->where("id=?",$id);
		$sql = $select->__toString();
		$tconfigname = $db->fetchRow($sql);
		
		return $tconfigname;
	}
	/**
	 * 通过触摸屏编号和品牌ID查询商铺ID
	 * @param unknown_type $touchid 触摸屏编号
	 * @param unknown_type $brandid 品牌ID
	 */
	static public  function selectshopid($touchid,$brandid){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("tshopbrandtouch");
		$select->where("touchid=$touchid && brandid=$brandid");
		$sql = $select->__toString();
		$shopid = $db->fetchRow($sql);
		
		return $shopid;
	}
	static public  function utf8ToGBK($value) { 
		return iconv("UTF-8", "gbk", $value); 
	} 
	static function utf8_strlen($string = null) {
		// 将字符串分解为单元
		preg_match_all("/./us", $string, $match);
		// 返回单元个数
		return count($match[0]);
	}
	/*
	 * @查询商铺名
	 */	 static public function brandshopname($id){
	 	db::db1();
		global $db;
		
		if(!empty($id)){
			$selectbrand = $db->select();
	    	$selectbrand->from('b_brandshop',array('name'));
			$selectbrand->where("id=?",$id); 
		   	$sqlbrand=$selectbrand->__toString(); 
		    $name=$db->fetchOne($sqlbrand);
			
		    return $name;
		}
		else{
			return '';
		}
	 }
	
	/**
	 * @查询商场名
	 */
	 static public function marketname($id){
	 	db::db1();
		global $db;
		
		$selectbrand = $db->select();
    	$selectbrand->from('tmarket',array('name'));
		$selectbrand->where("id=?",$id); 
	   	$sqlbrand=$selectbrand->__toString(); 
	    $name=$db->fetchOne($sqlbrand);
		
	    return $name;
	 }
	 
	 
	/**
	 * 查询2D 单品详情信息
	*/
	static public function b2dclothes($id){
		db::db1();
		global $db;
		
		$selectbrand = $db->select();
    	$selectbrand->from('b_2dclothes');
		$selectbrand->where("id=?",$id); 
	   	$sqlbrand=$selectbrand->__toString(); 
	    $b2dclothes=$db->fetchRow($sqlbrand);
		
	    return $b2dclothes;
	}
	
	//获取触摸屏名称
	static public function touchname($touchid){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from('tconfig',array('name'));
		$select->where("id=?",$touchid);
		$sql = $select->__toString();
		$touchname = $db->fetchOne($sql);
		
		return $touchname;
	}
	
	static  public function userid($name){
			db::dbk();
			global $dbk;
			
		$selectbrand = $dbk->select();
    	$selectbrand->from('preb_common_member',array('uid'));
		$selectbrand->where("username=?",$name); 
	   	$sqlbrand=$selectbrand->__toString(); 
	    $username=$dbk->fetchOne($sqlbrand);
			
	    return $username;
	}
	
	static  public function brandAll($id){
		db::db1();
		global $db;
		
		$selectbrand = $db->select();
    	$selectbrand->from('b_brand');
		$selectbrand->where("id=?",$id); 
	   	$sqlbrand=$selectbrand->__toString(); 
	    $brandAll=$db->fetchRow($sqlbrand);

	    return $brandAll;
	}
	
	//查询权限信息
	static public function selecAdminByUid($uid){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_admin");
		$select->where("id=?",$uid);
		$sql = $select->__toString();
		$admin = $db->fetchRow($sql);
		return $admin;
	}
	
	//查询所有品牌信息
	static public function selectbrand(){
		db::db1();
		global $db;
		
		$selectbrand = $db->select();
    	$selectbrand->from('b_brand', array('id','name')); 
	   	$sqlbrand=$selectbrand->__toString(); 
	    $brand=$db->fetchAll($sqlbrand);

	    return $brand;
	}
	/**
	 * 查询品牌名称
	 * @id 品牌ID
	 */
	static public function brand($id){
		db::db1();
		global $db;
		
		if(!empty($id)){
			$selectbrand = $db->select();
	    	$selectbrand->from('b_brand', array('name'));
			$selectbrand->where("id=?",$id); 
		   	$sqlbrand=$selectbrand->__toString(); 
		    $name=$db->fetchOne($sqlbrand);
	
		    return $name;
		}else{
			return '';
		}
	}
	
	
/**
	 * 查询品牌信息
	 * @id 品牌ID
	 */
	static public function brandd($id){
		db::db1();
		global $db;
		
		if(!empty($id)){
			$selectbrand = $db->select();
	    	$selectbrand->from('b_brand' );
			$selectbrand->where("id=?",$id); 
		   	$sqlbrand=$selectbrand->__toString(); 
		    $name=$db->fetchRow($sqlbrand);
	
		    return $name;
		}else{
			return '';
		}
	}
	
	/**
	 * 查询用户名
	 * @id 用户Id
	 */
	static public function username($id){
		db::dbk();
		global $dbk;
		
		$select = $dbk->select();
		$select->from("preb_common_member", array('username'));
		$select->where("uid=?",$id);
		$sql = $select->__toString();
		$username = $dbk->fetchOne($sql);
		
		return $username;
	}
	
	
	/**
	 * 查询回复应用上一级
	 */
	static  public function  selecthuifupinglun_superior($parentid){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_pinglun");
		$select->where("id=?",$parentid);
		$sql = $select->__toString();
		
		$obj = $db->fetchRow($sql);
		return $obj;
	}
	
	/**
	 * 查询回复评论 
	 */
	static public function selecthuifupinglun($id){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_pinglun");
		$select->where("parentid=?",$id);
		$sql = $select->__toString();
		
		$obj = $db->fetchAll($sql);
		return $obj;
	}
	
	
	/**     
	  * 静态方法,该方法输入数组并返回数组     
	  *     
	  * @param unknown_type $array 输入的数组     
	  * @param unknown_type $in 输入数组的编码     
	  * @param unknown_type $out 返回数组的编码     
	  * @return unknown 返回的数组     
	  */      
	static public function Conversion($array,$in,$out)       
	{       
	  self::$in=$in;       
	  self::$out=$out;      
	   
	  return self::arraymyicov($array);       
	}
	/**     
	  * 内部方法,循环数组     
	  *     
	  * @param unknown_type $array     
	  * @return unknown     
	  */      
	static private function arraymyicov($array)       
	{       
	  foreach ($array as $key=>$value)       
	  {       
	   $key=self::myiconv($key);       
	   if (!is_array($value)) {       
	    $value=self::myiconv($value);       
	   }else {       
	    $value=self::arraymyicov($value);       
	   }       
	   $temparray[$key]=$value;       
	  }       
	  return $temparray;       
	}       
	/**     
	  * 替换数组编码     
	  *     
	  * @param unknown_type $str     
	  * @return unknown     
	  */      
	static private function myiconv($str)       
	{       
	  return iconv(self::$in,self::$out,$str);       
	}       
	
	/**
	 * 
	 */
 		static public function getfirstchar($s0)
 		{ 
			if(ord($s0{0})>=ord("A") and ord($s0{0})<=ord("z"))
			{
				return 	strtoupper($s0{0});
			}
			else if ($s0=="" || $s0==null)
			{
				return $s0="";
			}
			else if (is_numeric($s0{0}))
			{
				switch ($s0{0})
				{
					case 0:return "L";
					case 1:return "Y";
					case 2:return "E";
					case 3:return "S";
					case 4:return "S";
					case 5:return "W";
					case 6:return "L";
					case 7:return "Q";
					case 8:return "B";
					case 9:return "J";
					
					
				}
			}
			else 
			{
				$s= $s0;       
		        $asc=ord($s{0})*256+ord($s{1})-65536;      
		        if($asc>=-20319 and $asc<=-20284)return "A"; 
		        if($asc>=-20283 and $asc<=-19776)return "B";    
		        if($asc>=-19775 and $asc<=-19219)return "C";    
		        if($asc>=-19218 and $asc<=-18711)return "D";    
		        if($asc>=-18710 and $asc<=-18527)return "E";    
		        if($asc>=-18526 and $asc<=-18240)return "F";   
		        if($asc>=-18239 and $asc<=-17923)return "G";    
		        if($asc>=-17922 and $asc<=-17418)return "H";    
		        if($asc>=-17417 and $asc<=-16475)return "J";    
		        if($asc>=-16474 and $asc<=-16213)return "K";
		        if($asc>=-16212 and $asc<=-15641)return "L";       
		        if($asc>=-15640 and $asc<=-15166)return "M";       
		        if($asc>=-15165 and $asc<=-14923)return "N"; 
		        if($asc>=-14922 and $asc<=-14915)return "O"; 
		        if($asc>=-14914 and $asc<=-14631)return "P"; 
		        if($asc>=-14630 and $asc<=-14150)return "Q";  
		        if($asc>=-14149 and $asc<=-14091)return "R";      
		        if($asc>=-14090 and $asc<=-13319)return "S";  
		        if($asc>=-13318 and $asc<=-12839)return "T";
		        if($asc>=-12838 and $asc<=-12557)return "W";  
		        if($asc>=-12556 and $asc<=-11848)return "X";  
		        if($asc>=-11847 and $asc<=-11056)return "Y";   
		        if($asc>=-11055 and $asc<=-10247)return "Z";        
		        return null;
			}
	 
		      
 		}
	
/**
 * 2维数组一列转换成一维数组
 * @param unknown_type $arrs
 * @param unknown_type $key
 */
	static public function arrs2arr($arrs,$key){
		 $array = array();
		 foreach($arrs as $val){
		  foreach ($val as $k => $v) {
		   if($k===$key)$array[]=$v;
		  }
		 }
		 //$array = resetkey(array_unique($array)); 
		 return $array;
		}
		/**
		 * $zifu是字符
		 * bcpow次方
		 * ordASCII码
		 * bcmul相乘
		 * bcadd相加
		 * @param unknown_type $zifu
		 */
	static public function from36to10($zifu)
   {    
   		$shu=0;
		for($i = 0; $i <strlen ( $zifu ); $i ++) {
			$linshi = substr ( $zifu, $i, 1 );
			if (ord ( $linshi ) < 58) {
				$shu = bcadd ( $shu, bcmul ( (ord ( $linshi ) - 48), bcpow ( 36, strlen ( $zifu ) - $i - 1 ) ) );
			
			} else {
				$shu = bcadd ( $shu, bcmul ( (ord ( $linshi ) - 55), bcpow ( 36, strlen ( $zifu ) - $i - 1 ) ) );
			}
		}
		return $shu;
	}
	/**
	 * bcmod：取得高精准度数字的余数
	 * chr：传回参数 ascii指定的字元
	 * bcdiv：将二个高精准度数字相除
	 * intval：取得变量的整数值
	 * @param unknown_type $shu
	 * @param unknown_type $w
	 */
	static public function from10to36($shu)
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
	 * 上传
	 */
	static public function upload($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
						if(!in_array($file["type"], $uptypes))
						//检查文件类型
						{
						$error="只能上传图像";
							
						}	
						else 
						{
							$error=$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
							$name=$upload11.'zancun'.$imgtype;
							if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
							{
							
								$filepath = $error;
								/* 输出图片路径 */
								$refilepath = $filepath.".i.jpg";
									
					    	}
					    	else
					    	{
				    			$error="上传图片失败";
					    	}
						 }
					
					 }
				  }
		}
		return $error;
	}		

	static public function upload2($upfile,$upload11,$type=null)
	{
//	echo $_FILES[$upfile]['type'];
//	exit();
	//上传文件类型列表
	 $uptypes=array( 
		'video/x-flv',
//		'video/x-ms-wmv',
//		'video/avi',
	    'application/octet-stream',
//		'video/x-ms-asf'
		'video/mp4'
		
	 );
	    $error="";
		$max_file_size=9995240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		
		switch($_FILES[$upfile]['type'])
            {
//                case "video/avi":
//                    $imgtype = ".avi";
//                    break;
//                case "video/x-ms-asf":
//                    $imgtype = ".asf";
//                    break;
//                case "video/x-ms-wmv":
//                    $imgtype = ".wmv";
//                    break;
                case "video/x-flv":
                    $imgtype = ".flv";
                    break;
//                case "application/octet-stream":
//                    $imgtype = ".rm";
//                    break;
            	case "application/octet-stream":
            		 $imgtype = ".mp4";
            		  break;
             	case "video/mp4":
            		 $imgtype = ".mp4";
            		  break;
            		
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "视频文件太大";
					
					}
					else 
					{
						if($type=='mp4')
						{
							$uptypes=array('video/mp4');
						}
						elseif($type=='flv')
						{
							$uptypes=array('video/x-flv');
						}
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
								if($type=='mp4' || $type=='flv')
								{
									$error="只能上传".$type."视频文件";
								}
								else
								{
									$error="只能上传mp4\flv视频文件";
								}
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										
			//				    			echo "上传图片成功";
										$error=  $destination_folder;
//										$error= $upload11.date('YmdHis').$random.$imgtype;
//											/* 图片路径 */
//											$filepath = $error;
//											/* 创建缩略图 */
//												//定义画布大小
//												$canvas = new Imagick($error);  
//												$canvas->thumbnailImage(768, 944,true);
//												$canvas->setImageFormat('jpeg'); 
//												$canvas->setCompressionQuality( 100 ); 
//												$canvas->writeImage($filepath.".i.jpg");
//												$canvas->destroy();
//										
//												//定义画布大小
//												$canvas = new Imagick($error);  
//												$canvas->thumbnailImage(110, 150,false);
//												$canvas->setImageFormat('jpeg'); 
//												$canvas->setCompressionQuality( 100 ); 
//												$canvas->writeImage($filepath.".s.jpg");
//												$canvas->destroy();
												
							    	}
							    	else
							    	{
							    			$error="上传视频失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}		
	static public function upload4($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
										$error= $destination_folder;
//										$error= $upload11.date('YmdHis').$random.$imgtype;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = $filepath.".i.jpg";
											/* 创建缩略图 */
//											$Thumbnail= new imagick($filepath);
//											$dst_im=imagecreatetruecolor(1322, 1080); 
//											$src_im=imagecreatefromjpeg($Thumbnail); 
//											imagecopy($dst_im,$src_im,0,0,0,0,1322,1080); 
//											imagejpeg($dst_im);
//											/* 把图片转为jpg格式 */
//											$Thumbnail->setImageFormat('jpeg'); 
//											/* 设置jpg压缩质量，1 - 100 */
//											$Thumbnail->setCompressionQuality( 100 ); 
//											/* 上下裁去296个像素 */
//											/* 等比例缩放到 768 x 944 大小 */
//											$Thumbnail->thumbnailImage(768, 944,true);
//											/* 写文件 */
//											$Thumbnail->writeImage($refilepath);
//											/* 释放资源 */
//											$Thumbnail->destroy();
												//定义画布大小
												$canvas = new Imagick($error);  
												//$canvas->newImage( 1080, 1322, 'white', 'jpg' ); 
												//$im = new Imagick($filepath);
												//$canvas->compositeImage( $im, imagick::COMPOSITE_OVER,0,-296);
												$canvas->thumbnailImage(110, 80,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath);
												$canvas->destroy();
												
//												try{
//												unlink ( $upload11.'zancun'.$imgtype );
//												}catch (Exception $e)
//												{}
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}

		/**
	 * 上传
	 */
	static public function upload_bisai($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											$error= $destination_folder;
//											$error= $upload11.date('YmdHis').$random.$imgtype;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = $filepath.".mx.jpg";
											/* 创建缩略图 */
												//定义画布大小
												$canvas = new Imagick($error);  
												//$canvas->newImage( 1080, 1322, 'white', 'jpg' ); 
												//$im = new Imagick($filepath);
												//$canvas->compositeImage( $im, imagick::COMPOSITE_OVER,0,-296);
												$canvas->thumbnailImage(495, 300,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath);
												$canvas->destroy();
												
//												try{
//												unlink ( $upload11.'zancun'.$imgtype );
//												}catch (Exception $e)
//												{}
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}		
	static public function upload_bisai_small($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
//											$error= $upload11.date('YmdHis').$random.$imgtype;
											$error= $destination_folder;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = $filepath.".smx.jpg";
											/* 创建缩略图 */
												//定义画布大小
												$canvas = new Imagick($error);  
												//$canvas->newImage( 1080, 1322, 'white', 'jpg' ); 
												//$im = new Imagick($filepath);
												//$canvas->compositeImage( $im, imagick::COMPOSITE_OVER,0,-296);
												$canvas->thumbnailImage(222, 122,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath);
												$canvas->destroy();
												
//												try{
//												unlink ( $upload11.'zancun'.$imgtype );
//												}catch (Exception $e)
//												{}
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
	static public function uploadbisai_smallimg($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
//											$error= $upload11.date('YmdHis').$random.$imgtype;
											$error= $destination_folder;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = $filepath.".ssmi.jpg";
											/* 创建缩略图 */
												//定义画布大小
												$canvas = new Imagick($error);  
												//$canvas->newImage( 1080, 1322, 'white', 'jpg' ); 
												//$im = new Imagick($filepath);
												//$canvas->compositeImage( $im, imagick::COMPOSITE_OVER,0,-296);
												$canvas->thumbnailImage(120,36,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath);
												$canvas->destroy();
												
//												try{
//												unlink ( $upload11.'zancun'.$imgtype );
//												}catch (Exception $e)
//												{}
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}			
		/**
	 * 上传
	 */
	static public function upload6($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											$error= $destination_folder;
//											$error= $upload11.date('YmdHis').$random.$imgtype;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = $filepath.".i.jpg";
											/* 创建缩略图 */
 
												//定义画布大小
												$canvas = new Imagick($error);  
												//$canvas->newImage( 1080, 1322, 'white', 'jpg' ); 
												//$im = new Imagick($filepath);
												//$canvas->compositeImage( $im, imagick::COMPOSITE_OVER,0,-296);
												$canvas->thumbnailImage(222, 122,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath);
												$canvas->destroy();
												
//												try{
//												unlink ( $upload11.'zancun'.$imgtype );
//												}catch (Exception $e)
//												{}
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
	/**
	 * 上传
	 */
	static public function showdapei_upload($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$error=$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = $filepath.".s.jpg";
											/* 创建缩略图 */
//											$Thumbnail= new imagick($filepath);
//											$dst_im=imagecreatetruecolor(1322, 1080); 
//											$src_im=imagecreatefromjpeg($Thumbnail); 
//											imagecopy($dst_im,$src_im,0,0,0,0,1322,1080); 
//											imagejpeg($dst_im);
//											/* 把图片转为jpg格式 */
//											$Thumbnail->setImageFormat('jpeg'); 
//											/* 设置jpg压缩质量，1 - 100 */
//											$Thumbnail->setCompressionQuality( 100 ); 
//											/* 上下裁去296个像素 */
//											/* 等比例缩放到 768 x 944 大小 */
//											$Thumbnail->thumbnailImage(768, 944,true);
//											/* 写文件 */
//											$Thumbnail->writeImage($refilepath);
//											/* 释放资源 */
//											$Thumbnail->destroy();
												//定义画布大小
												$canvas = new Imagick($error);  
												//$canvas->newImage( 1080, 1322, 'white', 'jpg' ); 
												//$im = new Imagick($filepath);
												//$canvas->compositeImage( $im, imagick::COMPOSITE_OVER,0,-296);
												$canvas->thumbnailImage(164, 336,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath);
												$canvas->destroy();
												
//												try{
//												unlink ( $upload11.'zancun'.$imgtype );
//												}catch (Exception $e)
//												{}
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
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
				$string[addslashes($key)] = common::daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
		return $string;
	}
	
	/**
	 * 登陆cookie,注意Qv0r_此需和discuz X config目录下的配置文件中的cookie前缀一样.
	 */
	static function LoginCookie()
	{
		global $_COOKIE;
		if (!empty($_COOKIE['Qv0r_d03e_auth']))
		return $_COOKIE['Qv0r_d03e_auth'];	
		else
		return '';
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
	 * 生成hash,和discuz x的一样
	 * @param $uid 用户ID
	 * @param $username 用户名
	 */
	static function formhash($uid,$username) {
		return substr(md5(substr(TIMESTAMP, 0, -7).$username.$uid.AUTHCODE), 8, 8);
	//	return substr(md5(substr(TIMESTAMP, 0, -7).$username.$_G['uid'].md5("f70d98blSxC7pNYU".$_SERVER['HTTP_USER_AGENT']).$hashadd.$specialadd), 8, 8);
	}
	/**
	 * 退出链接退出链接Hash生成生成
	 */
	static function LogoutHash($uid) {
		if(empty($uid))
			return "";
		db::dbk();
		global $dbk;
		$session=new session($dbk);
		$session2=$session->fetchRow('uid='.$uid);//查询用户名
		return common::formhash($uid,$session2['username']);
	}
	/**
	 * 退出链接生成
	 */
	static function Logout($uid) {
		if(!empty($_GET['referer']))
			return "http://www.beubeu.com/member.php?mod=logging&action=logout&formhash=".common::LogoutHash($uid)."&referer=".urlencode($_GET['referer']);
		else
			return "http://www.beubeu.com/member.php?mod=logging&action=logout&formhash=".common::LogoutHash($uid);
	}
	
	//截取中文字符串
	static function mysubstr($str, $start, $len) {
    $tmpstr = "";
    $strlen = $start + $len;
    for($i = 0; $i < $strlen; $i++) {
        if(ord(substr($str, $i, 1)) > 0xa0) {
            $tmpstr .= substr($str, $i, 2);
            $i++;
        } else
            $tmpstr .= substr($str, $i, 1);
    }
    return $tmpstr;
}

//截取中文字符串
	static function mysubstr2($str, $start, $len) {
    $tmpstr = "";
    $strlen = $start + $len;
    for($i = 0; $i < $strlen; $i++) {
        if(ord(substr($str, $i, 1)) > 0xa0) {
            $tmpstr .= substr($str, $i, 2);
            $i++;
        } else
            $tmpstr .= substr($str, $i, 1);
    }
    if($tmpstr!=$str)
    {
    $tmpstr.='...';
    }
    return $tmpstr;
}
	/**
	 * 时间相减得出年或天或小时
	 * @param unknown_type $date1
	 * @param unknown_type $date2
	 */
	static function compare($date1,$date2)
	{
		 $time=abs(strtotime($date1)-strtotime($date2));
		 $year=	$time/31536000;
		 $day=$time/86400;
		 $hour=$time/3600;
		 $min=$time/60;
		 $s=$time/1;
		 if($year>=1)
		 {
			echo round($year)."年前";
		 }
		 else{
			if($day>=1)
			{
				echo round($day)."天前";
			}
			else 
			{
				if($hour>=1)
				{
					echo round($hour)."小时前";
				}else {
					if($min>=1)
					{
						echo round($min)."分钟前";
					}
					else {
						if ($s>=1)
						{
							echo round($s)."秒前";
						}
					}
				}
			}
		}
	}
	
	/**
	 * 获取用户名和ID
	 * @param unknown_type $array 传来的数组
	 */
	static function getusermessage($array)
	{
		db::db1 (); //连接数据库1
		db::dbk();
		global $db;
		global $dbk;
		if($array!=null && count($array)!=0)
    	{
	    	$usernamearray=array();
	    	foreach ($array as $key=>$value )
	    	{
	    		$usernamearray[]=$array[$key]['memberid'];
	    		
	    	}
	    	
	    	$select = $dbk->select();
	    	$select->from('preb_common_member', '*');
	    	$select->orWhere('uid IN(?)', $usernamearray);
	    	$sql=$select->__toString();
	    	$kmemberid=$dbk->fetchAll($sql);
			foreach($usernamearray as $username)
	    	{
	    		$clotheslist="";
		    	foreach ($kmemberid as $key1=>$value1 )
		    	{
		    		//合并每个衣服的可穿模特列表
		    		if($kmemberid[$key1]['uid']==$username)
		    		{
		    			$clotheslist.=$kmemberid[$key1]['username'];
		    		}
		    	}
	    		foreach ($array as $key=>$value )
		    	{
		    		if($array[$key]['memberid']==$username)
		    		{
		    			$array[$key]['username']=$clotheslist;
		    		}
		    	}
	    	}
    	
    	}
    	
    	return  $array;
	}
	

	/**
	 * 获取模特后2位级25位编码
	 * @param unknown_type $array 传来的数组
	 */
	static function getmodelmessage($array)
	{
		db::db1 (); //连接数据库1
		db::dbk();
		global $db;
		global $dbk;
		if($array!=null && count($array)!=0)
    	{
	    	$clothesidarray=array();
    		foreach ($array as $key=>$value )
	    	{
	    		$clothesidarray[]=$array[$key]['id'];
	    	}
	    	$select = $db->select();
	    	$select->from('b_cothesmodel', '*');
	    	$select->orWhere('clothesid IN(?)', $clothesidarray);
	    	$sql=$select->__toString();
    		$cothesmodel=$db->fetchAll($sql);
    		foreach($clothesidarray as $clothesid)
	    	{
	    		$clotheslist="";
				    		$last2="";
		    	foreach ($cothesmodel as $key1=>$value1 )
		    	{
		    		//合并每个衣服的可穿模特列表
		    		if($cothesmodel[$key1]['clothesid']==$clothesid)
		    		{
		    			
		    			$clotheslist.=$cothesmodel[$key1]['modelcode'];
		    			$last2=substr($cothesmodel[$key1]['clothescode'], -2);//右截取二个字符
		    		}
		    	}
	    		foreach ($array as $key=>$value )
		    	{
		    			if($array[$key]['id']==$clothesid)
			    		{
			    			$array[$key]['modellist']=$clotheslist;
			    			$array[$key]['last2']=$last2;
			    		}
		    	}
	    	}
    	
    	}
    	
    	return  $array;
	}
	/**
	 * 全角替换为半角
	 * 去除首尾空格
	 * @param unknown_type $string
	 */
	static function replacestring($string)
	{
		$string=trim($string," ");//去除首尾的空格
		$string=str_replace("，",",",$string);//替换全角逗号为半角逗号
	    $string=trim($string,",");//去除首尾的逗号
	    $string=trim($string," ");//去除首尾的空格
	    $string=str_replace("\n","",$string);
	    $string=str_replace("\t","",$string);
	    $string=str_replace("\n\t","",$string);
	    return $string;
	}
	 
	
		/**
	 * 上传
	 */
	static public function upload3($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(100,999);
		$imgtype="";
		
		
		
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											$error= $destination_folder;
//											$error= $upload11.date('YmdHis').$random.$imgtype;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = "";
											
												//定义画布大小
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage(683, 1024,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath.".i.jpg");
												$canvas->destroy();
												
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage(480, 720,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath.".b.jpg");
												$canvas->destroy();
												
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage(240, 360,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath.".m.jpg");
												$canvas->destroy();
												
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage(110, 150,true);
												
												//重新生成110*150
												$new_img= new Imagick();
									   		$new_img->newImage( 100, 150, 'white', 'jpg' ); //pink,black
										    //合并图片
										    $new_img->compositeImage( $canvas, imagick::COMPOSITE_OVER, (110-$canvas->getImageWidth())/2,  (150-$canvas->getImageHeight())/2); 
										    //生成图片
												$new_img->setCompressionQuality( 100 ); 
										    $new_img->setImageFileName($filepath.".s.jpg");
										    $new_img->writeImage();
												
												$new_img->destroy();
												$canvas->destroy();

							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
		/**
	 * 上传
	 */
	static public function upload5($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(100,999);
		$imgtype="";
		
		
		
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											$error= $destination_folder;
//											$error= $upload11.date('YmdHis').$random.$imgtype;
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = "";
											
												//定义画布大小
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage(683, 1024,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath.".i.jpg");
												$canvas->destroy();
												
											
												
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage(110, 150,false);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath.".s.jpg");
												$canvas->destroy();
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
	//晒单  //$size  图片缩放尺寸
	static public function uploadd2($upfile,$upload11,$size,$name1="0")
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(100,999);
		$imgtype="";
		
		
		
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									if($name1 == "0"){
										$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									}else{
										$destination_folder=$upload11.$name1.$imgtype; 
									}
									
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
//										  	if($name1 == "0"){
//												$error=$upload11.date('YmdHis').$random.$imgtype; 
//											}else{
//												$error=$upload11.$name1.$imgtype; 
//											}
											$error= $destination_folder;	
												
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = "";
											
											if($size != '0*0'){
												$ar = explode('*',$size);
												
												//定义画布大小
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage($ar[0], $ar[1],true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath);
												$canvas->destroy();
											
												 
											}
										
									}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
	//搭配秀场 //$size  图片缩放尺寸
	static public function uploaddpxc($upfile,$upload11,$size='0*0',$name1="0")
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(100,999);
		$imgtype="";
		
		
		
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									if($name1 == "0"){
										$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									}else{
										$destination_folder=$upload11.$name1.$imgtype; 
									}
									
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
//										  	if($name1 == "0"){
//												$error=$upload11.date('YmdHis').$random.$imgtype; 
//											}else{
//												$error=$upload11.$name1.$imgtype; 
//											}
											$error= $destination_folder;	
												
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = "";
											
											if($size != '0*0'){
												$ar = explode('*',$size);
												
												//定义画布大小
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage($ar[0], $ar[1],true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath.'.i.jpg');
												$canvas->destroy();
												
												
												
											}
										
									}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
	//海报首页大图  //$size  图片缩放尺寸
	static public function uploadd($upfile,$upload11,$size,$name1="0")
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(100,999);
		$imgtype="";
		
		
		
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
								 
									 
									if($name1 == "0"){
										$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									}else{
										$destination_folder=$upload11.$name1.$imgtype; 
									}
									
//									$error=$destination_folder; 
//									$name=$upload11.'zancun'.$imgtype;
									 
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
//										  	if($name1 == "0"){
//												$error=$upload11.date('YmdHis').$random.$imgtype; 
//											}else{
//												$error=$upload11.$name1.$imgtype; 
//											}
											$error= $destination_folder;
											
											
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath = "";
											
											if($size != '0*0'){
												$ar = explode('*',$size);
												
												//定义画布大小
												$canvas = new Imagick($error);  
												$canvas->thumbnailImage($ar[0], $ar[1],true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($filepath);
												$canvas->destroy();
											
												 
											}
										
									}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	
	
	
	/* 
	 *搭配秀场 判断 是否店家过喜欢
	 *@$showdpid,$uid
	 * */
	static public function addlovedp($showdpid,$uid){
			db::db1 ();
//			db::dbk ();
//			db::dbcms();
			global $db; //$dbk,$dbcms;	
			
			$select = $db->select();
			$select->from("showlikes");
			$select->where("memberid=?",$uid);
			$select->where("dapeiid=?",$showdpid);
			$sql = $select->__toString();
			$obj = $db->fetchRow($sql);
			return $obj;
			
	}
	
	/* 
	 *我的单品判断 是否店家过喜欢
	 *@$showdpid,$uid
	 * */
	static public function addlovedanpin($showdpid,$uid){
			db::db1 ();
			global $db; 
			
			$select = $db->select();
			$select->from("myclotheslikes");
			$select->where("memberid=?",$uid);
			$select->where("clothesid=?",$showdpid);
			$sql = $select->__toString();
			$obj = $db->fetchRow($sql);
			return $obj;
			
	}
	
	/*
	 * 活动 参赛人数
	 * */
	static public function dpshownum($showbdid){
		db::db1();
		global $db;
		
		$sql = "SELECT  COUNT(DISTINCT(showbaida.memberid) ) AS COUNT  FROM showbaida WHERE showbdid=$showbdid";
		$obj = $db->fetchOne($sql);
		return $obj;
		
	}
	
	
	/*
	 * 活动 数组
	 * */
	static public function showbdlist(){
		db::db1();
		global $db;
		
		$select= $db->select();
		$select->from("showbdlist");
		$sql = $select->__toString();
		$obj = $db->fetchAll($sql);
		
		foreach ($obj as $o){
			$oi[$o['id']] = $o;
		}
		
		return $oi;
	}
	
	
	
	
	
	/*
	 * @ 活动状态
	 * */
	static public function showdpstatus($status){
		 
		switch ($status){
			case 1:  $a = "正在进行";break;
			case 2:  $a = '即将开始';break;
			case 3:  $a = '已经结束';break;
			
		}
		return  $a;
		
	}
	
	
	static public function selectpurchasemethod($brandid){
		db::db1();
		global $db;
		$sql = "SELECT promotionalmodel FROM b_clothes WHERE brandid=$brandid LIMIT 1  ";
		$promotionalmodel = $db->fetchOne($sql);
	
		if($promotionalmodel == 1){
			return '网购';
		}
		else{
			return "店销";	
		}
		
	}
	/**
	 * 查询比赛名
	 * @id 用户Id
	 */
	static public function dapeilistname($id){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("showbdlist", array('title'));
		$select->where("id=?",$id);
		$sql = $select->__toString();
		$username = $db->fetchOne($sql);
		
		return $username;
	}
	
	/**
	 * 查询广告类型（位置）
	 * @id 
	 */
	static public function dvertisementosition($id){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("b_category", array('title'));
		$select->where("id=?",$id);
		$sql = $select->__toString();
		$title = $db->fetchOne($sql);
		
		return $title;
	}
	
	
	/**
	 * 查询广告
	 * @id  15273
	 */
	static public function getbeubeuadvertisement($style){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("beubeuadvertisement");
		$select->where("style=?",$style);
		$select->where("state=1");
		$sql = $select->__toString();
		$obj = $db->fetchRow($sql);
		
		return $obj;
	}
	
	
		/**
	 * 查询活动名称
	 * @param id
	 */
	static function getnotepreferential($id){
		db::db1 (); //连接数据库1
		global $db;

		$sql = "SELECT activitytitile FROM note_preferential WHERE id=$id";
		$activitytitile = $db->fetchOne($sql);
		
		return $activitytitile;
	}
	
	
	/**
	 * 获取模特类型
	 * @param id
	 */
	static function modelname($id){
		db::db1 (); //连接数据库1
		global $db;

		$sql = "SELECT b_category.title FROM b_category WHERE b_category.id=$id";
		$activitytitile = $db->fetchOne($sql);
		
		return $activitytitile;
	}
	
	/**
	 * 品牌海报顶部图上传
	 * */
	static public function uupload($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$error=$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath_m = $filepath.".m.jpg"; //442*164的.m.jpg和尺寸小图320*119的.s.jpg
											$refilepath_s = $filepath.".s.jpg";
										 
												//定义画布大小
												$canvas = new Imagick($error);  
											 	$canvas->thumbnailImage(442, 164,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath_m);
												$canvas->destroy();
												
												$canvas2 = new Imagick($error);  
											 	$canvas2->thumbnailImage(320, 119,true);
												$canvas2->setImageFormat('jpeg'); 
												$canvas2->setCompressionQuality( 100 ); 
												$canvas2->writeImage($refilepath_s);
												$canvas2->destroy();
//							 
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	

	/**
	 * 风尚单品第一张图上传
	 * */
	static public function fashionitemsupload($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$error=$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										    
			//				    			echo "上传图片成功";
											//echo "W_00".$_FILES[$upfile]["tmp_name"];
											/* 图片路径 */
											$filepath = $error;
											/* 输出图片路径 */
											$refilepath_m = $filepath.".m.jpg"; //990*368的.m.jpg和尺寸小图234*87的.s.jpg
											$refilepath_s = $filepath.".s.jpg";
										 
												//定义画布大小
												$canvas = new Imagick($error);  
											 	$canvas->thumbnailImage(990, 368,true);
												$canvas->setImageFormat('jpeg'); 
												$canvas->setCompressionQuality( 100 ); 
												$canvas->writeImage($refilepath_m);
												$canvas->destroy();
												
												$canvas2 = new Imagick($error);  
											 	$canvas2->thumbnailImage(234, 87,true);
												$canvas2->setImageFormat('jpeg'); 
												$canvas2->setCompressionQuality( 100 ); 
												$canvas2->writeImage($refilepath_s);
												$canvas2->destroy();
//							 
							    	}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}

	/**
	 * @搭配总价 
	 * */
	static public function dpzprice($dpid,$touchid){
		db::db1 (); //连接数据库1
		global $db;
		
		 $sql = "SELECT  discountprice FROM newtbaida3d,tclothes WHERE tclothes.id=newtbaida3d.otherid AND baidaid=$dpid and newtbaida3d.touchid=$touchid and tclothes.touchid=$touchid";
		
		$obj = $db->fetchAll($sql);
		$discountprice = 0;
		foreach ($obj as $obj){
			$discountprice = $discountprice+$obj['discountprice'];
		}
		return $discountprice;
	}
	
	static public function uploadd1($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										$error= $destination_folder;
 							 		}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}
	 
	/**
	 * @密码随机 
	 * @len 生产的密码长度
	 * */
	static public function pwostochastic($len){
		
		$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',1,2,3,4,5,6,7,8,9,0,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$pwo = '';
		for($i=0;$i<$len;$i++){
		 	$aa = rand(0,58);
		 	$pwo .= $arr[$aa];
		}
		return $pwo;
	}
	
	
	static public function uploads($upfile,$upload11)
	{

	 $uptypes=array('image/jpg', //上传文件类型列表
		'image/jpeg',
		'image/png',
		'image/pjpeg',
		'image/gif',
		'image/bmp',
		'image/x-png');
	    $error="";
		$max_file_size=5240000; //上传文件大小限制, 单位BYTE
		$random = rand(0,9);
		$imgtype="";
		switch($_FILES[$upfile]['type'])
            {
                case "image/gif":
                    $imgtype = ".gif";
                    break;
                case "image/jpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/pjpeg":
                    $imgtype = ".jpg";
                    break;
                case "image/x-png":
                    $imgtype = ".png";
                    break;
                case "image/png":
                    $imgtype = ".png";
                    break;
            }
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				$file = $_FILES[$upfile];
				if ($file['name']=="")
				{
					$error= "文件为空";
				}
				else 
				{
					if($max_file_size < $file["size"])
					//检查文件大小
					{
						$error= "文件太大";
					
					}
					else 
					{
							if(!in_array($file["type"], $uptypes))
							//检查文件类型
							{
							$error="只能上传图像";
								
							}	
							else 
							{
									$error=$destination_folder=$upload11.date('YmdHis').$random.$imgtype; 
									$name=$upload11.'zancun'.$imgtype;
									if(move_uploaded_file ($_FILES[$upfile]["tmp_name"], $destination_folder))
									{
										$canvas= new Imagick ($error);
										$canvas->thumbnailImage (1000,1000,true);
										$canvas->setImageFormat ( 'jpg' );
										$canvas->setImageCompressionQuality ( 95 );
										$canvas->writeImage ( $error.'.i.jpg' );	
										$canvas->thumbnailImage (150,150,true);
										$canvas->writeImage ( $error.'.s.jpg' );
										$canvas->destroy();
									}
							    	else
							    	{
							    			$error="上传图片失败";
							    			
							    	}
							  }
						
						 }
				  }
		}
		return $error;
	}	

	//标签查询  
	static public function selectlabel($brandid,$custom){
		db::db1();
		global $db;
		
		$custom = substr($custom,strlen('custom'));
		
		$select = $db->select();
		$select->from("z_selectcon");
		$select->where("brandid=?",$brandid);
		$select->where("custom=?",$custom);
		$select->where("txt  IS NOT NULL");
		$select->where("txt <> ''");
		$select->order("id desc");
		$select->group("txt");
		$select->limit(30);
		$sql = $select->__toString();
		
		$obj = $db->fetchAll($sql);
		return $obj;
	}
	
	//绑定综合管理 单品信息 返回ID
	static public function zintegrate_id($cid){
		db::db1();
		global $db;
		
		$select = $db->select();
		$select->from("z_integrate",array("id"));
		$select->where("clothesId=?",$cid);
		$sql = $select->__toString();
		$obj = $db->fetchOne($sql);
		return $obj;
	
	} 
	
	/**
	 * 获取搭配数量
	*/
	static public function getdpcount($id){
		db::db1();
		global $db;
		$arr = explode('-',$id);
		$cid = $arr[0];
		$tid = $arr[1];
		
		$sql = "SELECT COUNT(tbaida.id) as con FROM tbaida WHERE tbaida.touchid = {$tid} AND tbaida.id IN (SELECT tbaida3d.baidaid FROM tbaida3d WHERE tbaida3d.otherid= {$cid} )";
		return $db->fetchOne($sql);
	}
	/**
	* 字符串加密
	**/
	static public function strencrypt($str_in){
		try{
			if(empty($str_in)){
				throw new Zend_Exception('需要加密的字符串不能为空！');
			}
			$str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			
			/*设置需要生成的随机字符串的长度 
			* 算法：
			* 需要加密的字符串长度小于等于5 长度就默认为10
			* 需要加密的字符串长度大于5 长度就是字符串长度的1.5倍
			**/
			$len = strlen($str)-1; //用于加密的字符串的长度
			$str_in_len=strlen($str_in);//需要加密字符串的长度
			$str_in_start=3;//插入字符串的起始位置
			$add_str_num=3;//一次最多插入几个字符
			if($str_in_len>=200){
				throw new Zend_Exception('需要加密的字符串太长！');
			}
			$str_in_len_len=1;
			if($str_in_len>=10 && $str_in_len<100){
				$str_in_len_len=2;
			}elseif($str_in_len>=100 && $str_in_len<1000){
				$str_in_len_len=3;
			}
			$length=10;
			if($str_in_len>5){
				$length=intval(($str_in_len*1.5)+2);
			}
			$randString = '';
			for($i = 0;$i < $length;$i ++){ 
				$num = mt_rand(0, $len); 
				$randString .= $str[$num]; 
			}
			/**
			* 字符串 按照以下规则替换进随机字符串里
			* 头部第一位是字符串长度放置位置 
			* 头部第二位是字符串长度值是几位数，也就是字符串长度值需要占几位
			* 头部第5位开始放置字符串，每隔一个字符放入最多3个字符串直到放完为止，如果遇见字符串长度就向后顺延
			**/
			srand();
			$len_start=rand(3,9);//生成字符串长度需要存放的位置
			
			$randString_arr=str_split($randString);
			$str_in_arr=str_split($str_in);
			
			for($i = 0;$i < $length;$i ++){
				
				$char=$randString_arr[$i];
				if($i==0){
					$char=self::__strreplace($len_start,'in');
				}else if($i==1){
					$char=self::__strreplace($str_in_len_len,'in');
				}else if($i==$len_start){
					$char=self::__strreplace($str_in_len,'in');
				}else if($i>=$str_in_start){
					if(count($str_in_arr)==0){
						continue;
					}
					if($add_str_num>0){
						$char=array_shift($str_in_arr);
					}
					if($add_str_num==0){
						$add_str_num=3;
					}else{
						$add_str_num--;
					}
				}
				$randString_arr[$i]=$char;
			}
			return implode('',$randString_arr);
			
		}catch(Zend_Exception $e){}
	}
	
	/**
	* 字符串解密
	**/
	static public function strdecipher($str_in){
		try{
			if(empty($str_in)){
				throw new Zend_Exception('需要解密的字符串不能为空！');
			}
			
			$str_in_len=strlen($str_in);//需要解密字符串的长度
			$str_in_arr=str_split($str_in);//将字符串拆分为数组
			$len_start=self::__strreplace($str_in_arr[0],'out');//字符串长度放置位置
			$str_in_len_len=self::__strreplace($str_in_arr[1],'out');//字符串长度的字符数量
			$str_in_start=3;//插入字符串的起始位置
			$add_str_num=3;//一次最多插入几个字符
			$str_len='';
			$len_start2=$len_start;
			for($i=0;$i<$str_in_len_len;$i++){
				$str_len.=$str_in_arr[$len_start2];
				$len_start2++;
			}
			$str_len=self::__strreplace($str_len,'out');//字符串长度
			$str='';
			for($i=$str_in_start;$i<$str_in_len;$i++){
				if($i<$len_start || $i>=($len_start+$str_in_len_len)){
					if($str_len==0){
						break;
					}
					if($add_str_num>0){
						$str.=$str_in_arr[$i];
						$str_len--;
					}
					if($add_str_num==0){
						$add_str_num=3;
					}else{
						$add_str_num--;
					}
				}
			}
		}catch(Zend_Exception $e){
			$str='';
		}
		return $str;
	}
	/**
	* 加解密算法使用的字符串替换
	**/
	static function __strreplace($str,$type){
		$chr=array(
			'0'=>'L',
			'1'=>'R',
			'2'=>'c',
			'3'=>'K',
			'4'=>'B',
			'5'=>'t',
			'6'=>'9',
			'7'=>'P',
			'8'=>'e',
			'9'=>'5'
		);
		$len=strlen($str);
		$str_arr=str_split($str);
		for($i=0 ; $i<$len; $i++){
			if($type=='in'){
				$str_arr[$i]=$chr[$str_arr[$i]];
			}else{
				$str_arr[$i]=array_search($str_arr[$i],$chr);
			}
		}
		return implode('',$str_arr);
	}
	
}