<?php
//上传类
class uploadd {
	
	
	
	/**
	 * 上传图片 
	 * @param  file: <\input type="file" name="file"/>
	 * imgname: 文件名
	 * FTP_HOST -- FTP主机
     * FTP_PORT -- 端口
	 * */
	public static function imgupload($file = 'file', $imgname = "",$url = 'textimg/') {
		//FTP 测试期 图片存放目录
		$image = CUploadedFile::getInstanceByName ( $file );
		//var_dump($image);exit();
		if(empty($image)){
			return 'orrer';
		}
		$type = explode("/",$image->type);
		$typee = $image->type;
		
		if($typee == 'image/gif' || $typee ==  'image/jpeg' || $typee ==  'image/pjpeg' || $typee ==  'image/x-png' || $typee ==  'image/png'){
			if (! empty ( $imgname )) {
				$imgurl = md5 ( $imgname .'.'.$type[1] );
				$name = $imgurl .'.'.$type[1];
			} else {
				$imgurl = md5 ( $image->name.time());
				if(isset($type[1]))
				{
					if($type[1] == 'jpeg'){
						$name = $imgurl .'.jpg';
					}else{
						$name = $imgurl .'.'.$type[1];
					}
					
					
				}
				else{
					$name = $imgurl .'.jpg';
				}
			}
		 
			$arr [0] = substr ( $imgurl, 0, 1 );
			$arr [1] = substr ( $imgurl, 1, 1 );
			$arr [2] = substr ( $imgurl, 2, 1 );
			
			for($i = 0; $i < count ( $arr ); $i ++) {
				$url .= $arr [$i] . '/';
			}
			$url .= $name;
			
			$dirlocal = '';
			$ftp = new classftp ();
			if (! $ftp->up_file ( $image->tempName, $dirlocal . $url )) {
	// 			echo "ftperrpr";exit();
			}
			
//			if (! $ftp->up_file ( $image->tempName, $dirlocal .$name )) {
//	// 			echo "ftperrpr";exit();
//			}
			
			$ftp->close ();
			$i = rand(1,5);
	//		$u = 'http://i'.$i.'.beubeu.com/';
			$u = Yii::app()->params['img_server_host'].'/'; 
			
//			return $u.$name;
			return $u.$url;
			
		}else{
			return 'orrer';
		}
		
		 
		
		
	}
	
	
	/**
	 * 删除图片
	 * $path Ftp地址 textimg/c/1/b/232456ww.jpeg
	 * */
	public static function imgdel($path){
		$url_tou=substr($path,0,7);
		if($url_tou=='http://')
		{
//			$arr=explode('.com/',$path);
//			$path=$arr[1];
		}
		$ftp = new classftp ();
		$ftp->del_file($path);
		$ftp->close ();
	}
	

	
	/**
	 * 清理缓存sessin数据
	 * @param $id 衣服id
	 * @param $type 谁用 例：clothesVoid
	 */
	public static function SessionDelete($id=0,$type=''){
		$id = trim ( $id );
		$type = trim ( $type );
		try {
			Comm::checkValue ( $id, 'ID', 1, 1 );
			Comm::checkValue ( $type, '', 0, 1 );
		} catch ( Exception $e ) {
			return false;
		}
		if($id==999999999)
		{
			$id=session_id();
		}
		if(isset($_SESSION[$type.$id]))
		{
			$json_data=$_SESSION[$type.$id];
			$json_data=trim($json_data);
			if(!empty($json_data))
			{
				$json_data=json_decode($json_data,true);
				Comm::arrayDelete($json_data);
				$arr=Comm::array_multi2single($json_data);//将多维数组转一维
				array_unique($arr);//去重
				Imgcaches::imgCacheDeleteByimg($arr);//从数组中删除数据
				try{
					unset ($_SESSION[$type.$id]);
				}catch(Exception $e){}
			}
		}
	}
	
	/**
	 * 获取缓存sessin数据
	 * @param $id 衣服id
	 * @param $type 谁用 例：clothesVoid
	 * @return 成功返回json数据,否则返回false
	 */
	public static function SessionGet($id=0,$type=''){
		$id = trim ( $id );
		$type = trim ( $type );
//		print_r($type.$id);exit();
		
		try {
			Comm::checkValue ( $id, 'ID', 1, 1 );
			Comm::checkValue ( $type, '', 0, 1 );
		} catch ( Exception $e ) {
			return false;
		}
		if($id==999999999)
		{
			$id=session_id();
		}
		if(isset($_SESSION[$type.$id]))
		{
			$json_data=$_SESSION[$type.$id];
			try{
				unset ($_SESSION[$type.$id]);
			}catch(Exception $e){}
			$json_data=trim($json_data);
			if(!empty($json_data))
			{
				$arr = json_decode ( $json_data, true );
				if(count($arr)>0)
				{
					$arr=Comm::array_multi2single($arr);//将多维数组转一维
					array_unique($arr);//去重
					Imgcaches::imgCacheDeleteByimg($arr);//从数组中删除数据
				}
				return $json_data;
			}
		}
		return false;
	}
	
	/**
	 * 添加缓存sessin数据
	 * @param $id 衣服id
	 * @param $type 谁用 例：clothesVoid
	 * @param $data 需要存在数据里的值 
	 * @param $addess 需要存在数据里的值的位置 例：array('dd'=>array('kk'=>'da'))，要将数据保存在二维数组kk下的写法 ('dd/kk')
	 */
	public static function SessionAdd($id=0,$type='',$data='',$addess=''){
		
		$id = trim ( $id );
		$type = trim ( $type );
		$addess = trim ( $addess );
		$data = trim ( $data );
		try {
			Comm::checkValue ( $id, 'ID', 1, 1 );
			Comm::checkValue ( $type, '', 0, 1 );
			Comm::checkValue ( $addess, '', 0, 1 );
			Comm::checkValue ( $data, '', 0, 1 );
		} catch ( Exception $e ) {
			return false;
		}
		$addess2=explode('/',$addess);
		$json_data='';
		if($id==999999999)
		{
			$id=session_id();
		}
		if(isset($_SESSION[$type.$id]))
		{
			$json_data=$_SESSION[$type.$id];
			$json_data=trim($json_data);
			if(!empty($json_data))
			{
				$json_data=json_decode($json_data,true);
			}
		}
		if(gettype($json_data)!='array')
		{
			$json_data=array();
		}
		$xb=array_pop($addess2);//去除数组最后一个元素
		$json_data=json_encode(Comm::arrayAdd($json_data,$addess2,array($xb=>$data)));
		$_SESSION[$type.$id]=$json_data;
//		echo $type.$id.'---';
//		print_r($json_data);exit();
	}
}

