<?php

class Comm {
	static protected $in;       
	static protected $out; 
	/**
	 * 检查长度
	 * @throws 如果长度不符合，会抛出异常
	 */
	static public function checkLenght($txt, $min = 0, $max = 20) { 
		if (strlen ( $txt ) > $max) {
			throw new BeubeuException ( '字符过长', BeubeuException::TOLONG );
		} else if (strlen ( $txt ) < $min) {
			throw new BeubeuException ( '字符过短', BeubeuException::TOSHORT );
		}
	}
	/**
	 * 检查文本是否存在、是否为空、最大值、最小值
	 * @param $txt 文本
	 * @param $type 0为string，1为int
	 * @param $min 长度最小值 1为判断为空，0为不判断为空
	 * @param $max 长度最大值
	 * @return 符合规范返回true，否则返回false
	 */
	static public function checkValue(&$txt, $name, $type = 0, $min = 1, $max = NULL) {
		$re=true;
		if (! isset ( $txt ))
		 {
			throw new BeubeuException ( $name .yii::t('public',"不存在"), BeubeuException::FIELD_EXIST );
		}
		 else 
		 {
			if (empty ( $txt ) && $min>0)
		    {
				throw new BeubeuException ( $name . yii::t('public',"为空"), BeubeuException::FIELD_EMPTY );
			} 
			else if($type == 1)//1为整形
			{
				if (! is_numeric ( $txt ) || intval($txt)!=$txt) {
					throw new BeubeuException ( $name . yii::t('public',"不为整形"), BeubeuException::FIELD_INT );
				}else 
				{
					if($max!=NULL)//不为null需要判断最大值
					{
						if ($txt>$max) 
						{
							throw new BeubeuException ( $name . yii::t('public',"过长"), BeubeuException::TOLONG );
						}
					}
					if($txt<$min)
					{
						throw new BeubeuException ( $name . yii::t('public',"过短"), BeubeuException::TOLONG );
					}
				}
			}
			else if($type ==0)//为0为string类型
			{
				if($max!=NULL)//不为null需要判断最大值
				{
					if (strlen ( $txt ) > $max) 
					{
						throw new BeubeuException ( $name . yii::t('public',"过长"), BeubeuException::TOLONG );
					}
				} 
				if (strlen ( $txt ) < $min)
				 {
					throw new BeubeuException ( $name . yii::t('public',"过短"), BeubeuException::TOSHORT );
				}
			} 
		}
		return $re;
	}
	
	/**
	 * 检查货币是否符合规范
	 * @param $data 需要检查的字符串 （此值用传址格式传递，当其格式误差不大时会将其修改为货币格式）
	 * @param $name 错误提示名
	 * @return 成功返回转换后的字符串，否则返回false
	 */
	static function moneySingle(&$data = null,$name='data') {
		if (empty ( $data )) {
			throw new BeubeuException ( $name . yii::t('public',"为空"), BeubeuException::FIELD_EMPTY );
		}
		if (! is_numeric ( $data )) {
			throw new BeubeuException ( $name . Yii::t('public','格式不正确'), BeubeuException::FIELD_FORMAT );
		}else {
			$data=number_format($data,2);
		}
	}
	
	/**
	 * 检查时间字符串是否符合规范(Y-m-d m:i:s)
	 * @param $data 需要检查的字符串
	 * @param $name 错误提示名
	 * @param $time 是否需要时间，默认true需要 ，false不需要
	 * @return 成功返回转换后的字符串，否则返回false
	 */
	static function dataSingle($data = null,$name='data', $time = true) {
		$data = trim ( $data );
		if (empty ( $data )) { 
			throw new BeubeuException ( $name.Yii::t('public','不能为空'), BeubeuException::FIELD_EMPTY );
		}
		$data = strtotime ( $data );
		if ($data) {
			if ($time) {
				return date ( 'Y-m-d m:i:s', $data );
			} else {
				return date ( 'Y-m-d', $data );
			}
		} else {
			throw new BeubeuException ( $name.Yii::t('public','格式不正确'), BeubeuException::FIELD_FORMAT );
		}
	}
	
	/**
	 * 检查电话格式是否符合
	 * @param $phone 电话号码
	 * @param $name 错误提示名
	 * @param $null_bool 是否可为空 默认不可为空
	 * @return 成功返回true，否则返回false
	 */
	
	
	
	static function phoneToSingle($phone=0,$name='phone',$null_bool=false)
	{
		if (empty ( $phone )) {
			if (! $null_bool) {
				throw new BeubeuException ( $name . Yii::t ( 'public', '不能为空' ), BeubeuException::FIELD_EMPTY );
			} else {
				return true;
			}
		}
		$preg=preg_match('/^(d{3}-)(d{8})$|^(d{4}-)(d{7})$|^(d{4}-)(d{8})$/',$phone);
		if($preg){
			return true;
		}
		else{
			throw new BeubeuException ( $name . Yii::t('public','格式不正确'), BeubeuException::FIELD_FORMAT );
		}
	}
	/**
	 * 将全角字符转换为半角字符
	 * @param $str 需要转换的字符串
	 * @return 返回转换后的字符串
	 */
	static function doubleToSingle($string) {
//	 	 return  preg_replace($search,$replace,$str);	
		$string=trim($string," ");//去除首尾的空格
		$string=str_replace("，",",",$string);//替换全角逗号为半角逗号
	    $string=trim($string,",");//去除首尾的逗号
	    $string=trim($string," ");//去除首尾的空格
	    $string=str_replace("\n","",$string);
	    $string=str_replace("\t","",$string);
	   return $string=str_replace("\n\t","",$string);
//	 	    preg_replace ( '/\xa3([\xa1-\xfe])/e', 'chr(ord(\1)-0x80)', ($string) );
	}
	
	/**
	 * 截取中文字符串
	 * @param $str 需要截取的字符串
	 * @param $start 从什么位置开始截取，默认0
	 * @param $len 截取的长度，默认0。如果为0就返回其原字符串
	 * @return 返回截取后的字符串
	 */
	static function stringIntercept($str = NULL, $start = 0, $len = 0) {
		$str = trim ( $str );
		if ($str == null || $len == 0) {
			return $str;
		}
		$tmpstr = "";
		$strlen = $start + $len;
		for($i = 0; $i < $strlen; $i ++) {
			if (ord ( substr ( $str, $i, 1 ) ) > 0xa0) {
				$tmpstr .= substr ( $str, $i, 2 );
				$i ++;
			} else
				$tmpstr .= substr ( $str, $i, 1 );
		}
		if ($tmpstr != $str) {
			$tmpstr .= '...';
		}
		return $tmpstr;
	}
	
	/**
	 * 新建数组递归方法
	 * 为数组下建多位数组
	 * @param $goal_arr 目标数组，也就是需要为谁创建多维
	 * @param $subscript_arr 下标数组，存放下标的数组
	 * @param $data 需要存在数据里的值
	 * @return 返回新的数组
	 */
	static function arrayAdd(&$goal_arr = array(), $subscript_arr = array(), $data = '') {
		if(count($subscript_arr)>0){
			$xb = $subscript_arr [0];
			if (! isset ( $goal_arr [$xb] )) {
				$goal_arr [$xb] = array ();
			}
		}
		if (count ( $subscript_arr ) == 1 && gettype ( $data ) == 'array') {
			foreach ( $data as $key => $value ) {
				$goal_arr [$xb] [$key] = $value;
			}
		} else if (count ( $subscript_arr ) == 1 && gettype ( $data ) != 'array' && ! empty ( $data )) {
			$goal_arr [$xb] [] = $data;
		}
		if (count ( $subscript_arr ) > 1) {
			array_shift ( $subscript_arr );
			try{
				self::arrayAdd ( $goal_arr [$xb], $subscript_arr, $data );
			}catch(Exception $e){}
		}
		return $goal_arr;
	}
	
	
	/**
	 * 数组递归方法，读取数据并删除其里边的图片
	 * @param $goal_arr 目标数组，删除数组里的值的文件
	 */
	static function arrayDelete($goal_arr = array()) {
		if (gettype ( $goal_arr ) == 'array') {
			foreach ( $goal_arr as $value ) {
				try{
					self::arrayDelete ( $value );
				}catch(Exception $e){}
			}
		} else if (! empty ( $goal_arr )) {
			try {
				uploadd::imgdel($goal_arr);
			} catch ( Exception $e ) {
			}
		}
	}
	
	/**
	 * 数组递归方法，更具键获取多维数组里的交集
	 * @param $arr1
	 * @param $arr2
	 * @return 返回交集函数
	 */
	static function my_array_key_intersection($arr1 = array(), $arr2 = array()) {
		$arr_jj = array_intersect_key ( $arr1, $arr2 ); //根据键找到两个数组的交集，返回值只是第一个数组
		$arr_jj2 = array_intersect_key ( $arr2, $arr1 ); //根据键找到两个数组的交集，返回值只是第一个数组
//		print_r($arr1);
//		echo "<hr />";
//		print_r($arr2);
//		echo "<hr />";
//		print_r($arr_jj);exit();
		
		foreach ( $arr_jj as $key => $value ) {
			if (gettype ( $value ) == 'array') {
				try {
					$arr_b = self::my_array_key_intersection ( $value, $arr_jj2 [$key] );
					if (count ( $arr_b ) > 0) {
						$arr_jj [$key] = $arr_b;
					} else {
						try {
							unset ( $arr_jj [$key] );
						} catch ( Exception $e ) {
						}
					}
				} catch ( Exception $e ) {
				}
			}
		}
		return $arr_jj;
	}
	/**
	 * 数组递归方法，更具键获取多维数组里的差集
	 * @param $arr1
	 * @param $arr2
	 * @return 返回交集函数
	 */
	static function my_array_key_diff(&$arr1 = array(), $arr2 = array()) {
		foreach ( $arr1 as $key => $value ) {
			if (isset ( $arr2 [$key] ) && gettype ( $arr2 [$key] ) == 'array' && gettype ( $value ) == 'array') {
				$arr1 [$key] = self::my_array_key_diff ( $value, $arr2 [$key] );
			} elseif (isset ( $arr2 [$key] ) && (gettype ( $arr2 [$key] ) != 'array' || gettype ( $value ) != 'array')) {
				try {
					unset ( $arr1 [$key] );
				} catch ( Exception $e ) {
				}
			}
		}
		return $arr1;
	}
	/**
	 * 数组递归方法将多维数组转为一维数组
	 * @param $array
	 */
	static function array_multi2single($array = array()) {
		static $result_array = array ();
		if (gettype ( $array ) == 'array') {
			foreach ($array as $value)
			{
				try{
					self::array_multi2single ( $value );
				}catch(Exception $e){}
			}
		} else {
			$result_array [] = $array;
		}
		return $result_array;
	} 
	
	/**
	 * 获取对应分类名
	 * @param $id
	 */
	static function categoryByTitle($id){
		$t = Yii::app()->db->createCommand()
		->select('title')->from('beu_category')
		->where('id=:id',array(':id'=>$id))
		->queryRow();
		if(!empty($t['title'])){
			return $t['title'];
		}else{
			return '&nbsp;';
		}
		
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
	 * 查询模特名
	 * @param $id 
	 * @return $name  字符串形式 多个返回;
	 * */
	static public  function modelnamestr($id){
		$t = Yii::app()->db->createCommand()
		->select('title')->from('beu_category')
		->where("id in ({$id})")
		->queryAll();
		
		if(!empty($t)){
			$arr = array();
			foreach ($t as $v){
				$arr[] = $v['title'];
			}
			$srt = implode(',',$arr);
			return $srt;
		}else{
			return '';
		}
		  
	} 
	
	
	/**
	 * 获取该屏的默认品牌
	 * @param $touchid 搭配屏ID
	 * @return brandid 品牌id
	 * */
	static public function selecttbrand($touchid){
  			$obj = Yii::app()->db->createCommand()
			->select("brandid")->from('touch_config')
			->where("id = :id",array(":id"=>$touchid))
			->queryRow();
			 
			if(!empty($obj)){
				return $obj['brandid'];
			}else{
				return 0;
			}
			
			
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
	 * 获取默认模特
	 * */
	static function setdefmodel($tid){
		$obj = Yii::app()->db->createCommand()->select('modeldefault')->from('touch_config')->where('id=:id',array('id'=>$tid))->queryRow();
		return $obj['modeldefault'];
	}
	 
	/**
	 * 模特分类
	 * */
	static function selectmodeltype(){
		try {
			$modeltype=Category::categorySelectForAll(array(9));
			if($modeltype['status']==0){
				$modeltype=array();
			}else{
				$modeltype=$modeltype['data'];
			}
			return $modeltype;
		} catch (Exception $e) {
		}
		
	}
	
	/**
	 * 获取品牌名
	 * */
	static function selbrandname($brandid){
		$obj = Yii::app()->db->createCommand()->select('name')->from('beu_brand')
		->where("id=:id",array(':id'=>$brandid))->queryRow();
		
		if(!empty($obj)){
			echo $obj['name'];
		}else{
			echo '';
		}
		
	}
	
	/**
	 * 单品状态
	 * */
 	static  function clothesstatus($status){
 		switch ($status){
 			case 0: return '添加衣服完毕';
 			case 1: return '衣服图片上传完毕';
 			case 2: return '遮罩选择完毕';
 			case 3: return '未审核';
 			case 8: return '已下架';
 			case 9: return '综合管理';
 			case 10: return '列表不显示';
 			case 11: return '列表显示';
			case 12: return '待售';
 		}
 	} 
 	
 	/**
 	 * 前台返回 各个部位选用的遮罩
 	 * @param cid 单品id
 	 * */
 	static function maskinformationenabled($cid){
 		$returnarr = array();
 		
 		$cobj = Yii::app()->db->createCommand()->select('modelgender,mask,foottype')->from('beu_clothes')->where('id=:id',array(':id'=>$cid))->queryRow();
 		// 查找对应的模特信息
 		$where = "(beu_model.mask  IS  NOT NULL) AND (beu_model.mask  NOT LIKE '') AND  (beu_model.type = {$cobj['modelgender']})"; 
 		
 		$model_obj = Yii::app()->db->createCommand()->select('mask')->from('beu_model')->where($where)->limit(1)->queryRow();
 		
 		
 		
 		if(!empty($model_obj['mask'])){
 			$m_mask = json_decode($model_obj['mask'],true); //模特 遮罩
 			
 			if($cobj['mask']){
 				$c_masl = json_decode($cobj['mask'],true); // 单品 区块的遮罩ID 
 			}
 			
 		 
 		 
	 		foreach ($c_masl as $k=>$v){
	 			
	 			foreach ($c_masl[$k] as $ck=>$cv){
					
	 				if(!empty($v[$ck])){
	 					if($ck == 'the_outside_right_shoe' || $ck == 'the_left_side_outside_the_shoe'){
	 						$foot = 15308;
	 						if(!empty($cobj['foottype'])){
	 							$foot = $cobj['foottype'];
	 						}
	 							
		 					 if(!empty($m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']])){
					 			if(!empty($m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask'])){	
						 			$returnarr[$k][$ck]['maskimgurl'] = $m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['maskimgurl'];
							 		$returnarr[$k][$ck]['x'] = (int)$m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['x'];
							 		$returnarr[$k][$ck]['y'] = (int)$m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['y'];
							 		$returnarr[$k][$ck]['width'] = (int)$m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['width'];
							 		$returnarr[$k][$ck]['height'] = (int)$m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['height'];
							 		$returnarr[$k][$ck]['center_pointx'] = (int)$m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['center_pointx'];
							 		$returnarr[$k][$ck]['center_pointy'] = (int)$m_mask[$k][$foot][$ck]['larm'.$v[$ck]['maskid']]['mask']['center_pointy'];
					 			}
					 		}
	 					}else{
		 					 if(!empty($m_mask[$k][$ck]['larm'.$v[$ck]['maskid']])){
					 			if(!empty($m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask'])){	
						 			$returnarr[$k][$ck]['maskimgurl'] = $m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['maskimgurl'];
							 		$returnarr[$k][$ck]['x'] = (int)$m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['x'];
							 		$returnarr[$k][$ck]['y'] = (int)$m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['y'];
							 		$returnarr[$k][$ck]['width'] = (int)$m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['width'];
							 		$returnarr[$k][$ck]['height'] = (int)$m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['height'];
							 		$returnarr[$k][$ck]['center_pointx'] = (int)$m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['center_pointx'];
							 		$returnarr[$k][$ck]['center_pointy'] = (int)$m_mask[$k][$ck]['larm'.$v[$ck]['maskid']]['mask']['center_pointy'];
					 			}
					 		}
	 					}
	 					
	 				}
		 		}
	 		}
 		} 
 		 
 		return ($returnarr);
 	}
 	
 	/**
 	 * 遍历特殊单品
 	 * */
 	static  public function getspecialclothes($json){
 		$obj = json_decode($json,true);
 		if(!empty($obj['type'])){
// 			print_r($obj);exit();
 			
 			$type_arr = explode(',',$obj['type']);
 			
 			foreach($type_arr as $v){
 				if(!empty($obj['angel0'])){
 					if(!empty($obj['angel0'][$v])){
 						$arr['angel0'][$v] = $obj['angel0'][$v];
 					}
 				}
 				
 				if(!empty($obj['angel6'])){
 					if(!empty($obj['angel6'][$v])){
 						$arr['angel6'][$v] = $obj['angel6'][$v];
 					}
 				}
 				
// 				if(!empty($obj['angel0']) && !empty($obj['angel6'])){
// 					if(!empty($obj['angel0'][$v]) && !empty($obj['angel6'][$v])){
// 						$arr['angel0'][$v] = $obj['angel0'][$v];
// 					    $arr['angel6'][$v] = $obj['angel6'][$v];
// 					}
// 				}
 			}
 			
 			if(!empty($arr)){
 				$arr['type'] = $obj['type'];
 				return ($arr);
 			}else{
 				return '';
 			}
 			
 		}else{
 			return '';
 		}
 		
 	}
 	
 	
 	/**
 	 * 获取多个搭配屏名
 	 * @param tid 搭配屏ID
 	 * @return ARR touch_config（id name）
 	 * */
 	static public function gettconfigname($tid){
 		$sel = Yii::app()->db->createCommand();
 		$obj = $sel->select('id,name')->from('touch_config')->where("id in ($tid)")->queryAll();
 		return $obj;
 	} 
 	
 	/**
 	 * 获取账户信息
 	 * @param $id 账户ID 
 	 * */
 	static public function getuseraccount($id){
 		$sl = Yii::app()->db->createCommand()->select('id,title')->from('beu_useraccount')->where('id=:id',array(':id'=>$id))->queryRow();
 		if(!empty($sl['title'])){
 			return $sl['title'];
 		}else{
 			return '';
 		}
 		
 	}
 	
 	/**
 	 * 单品标签查询
 	 * @param $id 单品ID
 	 * @param $custom 对应的标签项 
 	 * */
 	static public function selselectcon($id,$custom){
 		$db = Yii::app()->db->createCommand();
 		$db->select('txt')->from('beu_selectcon')->where('clothesid=:clothesid',array(':clothesid'=>$id))->andWhere('custom=:custom',array(':custom'=>$custom));
 		$obj = $db->queryRow();
 		
 		if(!empty($obj)){
 			return $obj['txt'];
 		}else{
 			return '&nbsp';
 		}
 		
 	}
 	
 	
 	/**
 	 * 根据单品条码 获取单品ID
 	 * */
 	static public function getidbycode($code){
 		if(!empty($code)){
 			$code_arr = explode(',',$code);
 			
 			$new_code_arr = array();
 			foreach ($code_arr as $v){
				$v=trim($v);
				if(!empty($v)){
					$new_code_arr[] = "'".$v."'";
				}
 			} 
			$id_arr='';
			if(count($new_code_arr)>0){
				$newcode = implode(',',$new_code_arr);
				$db = Yii::app ()->db->createCommand ()->select('id')->from('beu_clothes')->where('code in ('.$newcode.')');
				$id_arr = $db->queryAll();
 			}
 			if(!empty($id_arr)){
 				$arry = array();
 				foreach ($id_arr as $vv){
 					 $arry[] = trim($vv['id']);
 				} 
 				return implode(',',$arry);
 			}else{
 				return '';
 			}
 			
 		}else{
 		 	return '';
 		}
 	}
 	
 	
 	
 	/**
 	 * 根据单品ID 获取单品条码
 	 * @param $id  1,2,3 
 	 * */
 	 static public function getcodebyid($id){
 	 	if(!empty($id)){
 	 		$db = Yii::app()->db->createCommand()->select('code')->from('beu_clothes')->where("id in ($id)");
 	 		$code_arr = $db->queryAll();
 	 		
 	 		if(!empty($code_arr)){
 	 			$arry = array();
 	 			foreach ($code_arr as $v){
 	 				if(!empty($v['code'])){
 	 					$arry[] = $v['code'];
 	 				}
 	 			}
		 	if(count($arry)>0){
		 		return implode(',',$arry);
		 	}else{
		 		return '';
		 	}
 	 			
 	 		}else{
 	 			return '';
 	 		}
 	 	}else{
 	 		return '';
 	 	}
 	 
 	 }
 	
 	
 	/**
 	 * 条码验证
 	 * */
 	static function verification($code,$type){
 	 	 $id= comm::getidbycode(trim($code));

 	 	 if(empty($id)){
 	 	 	return false;
 	 	 }
 	 	 
 	 	 if($type == 1){
 	 	 	$sel = 'beu_clothesrelated';
 	 	 }else{
 	 	 	$sel = 'beu_clothesdifferent';
 	 	 }
 	 	 
 	 	 $where = "clothesid1={$id}";
 	 	 
 	 	 for($i=2;$i<13;$i++){
 	 	 	 $where .= " or clothesid{$i}={$id}";
 	 	 }
 	 	 
 	 	 $db = Yii::app()->db->createCommand();
 	 	 $db->select('id')->from($sel)->where($where);
 	 	 $obj = $db->queryAll();
 	 	 
 	 	 if(!empty($obj)){
 	 	 	return true;
 	 	 }else{
 	 	 	return false;
 	 	 }
 	 	 
 	 }
 	
 	static function getdpcon($stt){
 		$arr = explode('_',$stt);
 		$sqll = "SELECT count(id) as con FROM beu_baida WHERE id IN (SELECT baidaid FROM beu_baidaclothes WHERE beu_baidaclothes.clothesid = ({$arr[0]}) ) AND beu_baida.touchid={$arr [1]} AND beu_baida.status>=9";
 		$connt = Yii::app()->db->createCommand($sqll)->queryScalar();
 		return $connt;
 	}
 	 
 	
	/**
 	 * 获取拼音首字母
 	 * */
 	static function getfirstchar($s0){   
	$fchar = ord($s0{0});
	if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
	$s1 = iconv("UTF-8","gb2312", $s0);
	$s2 = iconv("gb2312","UTF-8", $s1);
	if($s2 == $s0){$s = $s1;}else{$s = $s0;}
	$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
	if($asc >= -20319 and $asc <= -20284) return "A";
	if($asc >= -20283 and $asc <= -19776) return "B";
	if($asc >= -19775 and $asc <= -19219) return "C";
	if($asc >= -19218 and $asc <= -18711) return "D";
	if($asc >= -18710 and $asc <= -18527) return "E";
	if($asc >= -18526 and $asc <= -18240) return "F";
	if($asc >= -18239 and $asc <= -17923) return "G";
	if($asc >= -17922 and $asc <= -17418) return "I";
	if($asc >= -17417 and $asc <= -16475) return "J";
	if($asc >= -16474 and $asc <= -16213) return "K";
	if($asc >= -16212 and $asc <= -15641) return "L";
	if($asc >= -15640 and $asc <= -15166) return "M";
	if($asc >= -15165 and $asc <= -14923) return "N";
	if($asc >= -14922 and $asc <= -14915) return "O";
	if($asc >= -14914 and $asc <= -14631) return "P";
	if($asc >= -14630 and $asc <= -14150) return "Q";
	if($asc >= -14149 and $asc <= -14091) return "R";
	if($asc >= -14090 and $asc <= -13319) return "S";
	if($asc >= -13318 and $asc <= -12839) return "T";
	if($asc >= -12838 and $asc <= -12557) return "W";
	if($asc >= -12556 and $asc <= -11848) return "X";
	if($asc >= -11847 and $asc <= -11056) return "Y";
	if($asc >= -11055 and $asc <= -10247) return "Z";
	return null;
}

	/**
	 * 搭配勾上状态 isshow_dp
	 * */
 	static function dapeicheck($dpid){
 		$sel = Yii::app()->db->createCommand();
 		$oj = $sel->select('id')->from('isshow_dp')->where('dpid=:dpid',array(':dpid'=>$dpid))->queryAll();
 		if(!empty($oj)){
 			return  1;
 		}else{
 			return 0; 
 		}
 		
 	} 
	
	/**
	* 获取来源IP
	*/
	static function getSourceIp(){
		if (getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else 
			$ip = "";
		return $ip;
	}

	/**
	* 判断IP是否规范
	*/
	static function is_ip($gonten){  
		$ip=explode('.',$gonten);  
		for($i=0;$i<count($ip);$i++){  
			if($ip[$i]>255){  
				return(0);  
			}  
		}  
		return ereg('^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',$gonten);  
    }
	
	/**
	* 字符串加密
	**/
	static public function strencrypt($str_in){
		try{
			if(empty($str_in)){
				throw new Exception('需要加密的字符串不能为空！');
			}
			$str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			
			/*设置需要生成的随机字符串的长度 
			* 算法：
			* 需要加密的字符串长度小于等于5 长度就默认为10
			* 需要加密的字符串长度大于5 长度就是字符串长度的1.5倍
			**/
			$len = strlen($str)-1; //用于加密的字符串的长度
			$str_in_len=strlen($str_in);//需要加密字符串的长度
			$str_in=self::__strreplace($str_in,'in');
			$str_in_start=3;//插入字符串的起始位置
			$add_str_num=3;//一次最多插入几个字符
			if($str_in_len>=200){
				throw new Exception('需要加密的字符串太长！');
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
			
		}catch(Exception $e){}
	}
	
	/**
	* 字符串解密
	**/
	static public function strdecipher($str_in){
		try{
			if(empty($str_in)){
				throw new Exception('需要解密的字符串不能为空！');
			}
			
			$str_in_len=strlen($str_in);//需要解密字符串的长度
			$str_in_arr=str_split($str_in);//将字符串拆分为数组
			if(!isset($str_in_arr[0]) || !isset($str_in_arr[1])){
				throw new Exception('需要解密的字符串格式有误！');
			}
			$len_start=self::__strreplace($str_in_arr[0],'out');//字符串长度放置位置
			$str_in_len_len=self::__strreplace($str_in_arr[1],'out');//字符串长度的字符数量
			$str_in_start=3;//插入字符串的起始位置
			$add_str_num=3;//一次最多插入几个字符
			$str_len='';
			$len_start2=$len_start;
			for($i=0;$i<$str_in_len_len;$i++){
				if(!isset($str_in_arr[$len_start2])){
					throw new Exception('需要解密的字符串被截取！');
				}
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
		}catch(Exception $e){
			$str='';
		}
		return self::__strreplace($str,'out');
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
	
	/**
	* 用数组拼接sql字符串
	**/
	static function join_sql_set($param_arr){
		$int_arr=array('integer','double');//拼接字符串时不需要加引号的字段
		$is_bool=false;//是否加如，号
		$set_str='';
		foreach($param_arr as $key=>$value){
			if($is_bool){
				$set_str.=',';
			}
			if(in_array(gettype($value),$int_arr)){
				$set_str.=$key.'='.$value;
			}else{
				$set_str.=$key.'=\''.$value.'\'';
			}
			$is_bool=true;
		}
		return $set_str;
	}
	/**
	* http请求
	**/
	static public function http_post($url,$postfields){
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
	* 解决 php 5.2.6 以上版本 array_diff() 函数在处理大数组时的效率问题
	* 根据 ChinaUnix 论坛版主 hightman 思路写的方法
	*
	* 整理：http://www.CodeBit.cn
	* 参考：http://bbs.chinaunix.net/viewthread.php?tid=938096&rpid=6817036&ordertype=0&page=1#pid6817036
	*/
	static function array_diff_fast($firstArray, $secondArray) {
		// 转换第二个数组的键值关系
		$secondArray = array_flip($secondArray);
		// 循环第一个数组
		foreach($firstArray as $key => $value) {
			// 如果第二个数组中存在第一个数组的值
			if (isset($secondArray[$value])) {
				// 移除第一个数组中对应的元素
				unset($firstArray[$key]);
			}
		}
		return array_filter($firstArray);
	}

	/**
	*
	* 自定义的array_intersect
	* 如果求的是一维数组的交集这个函数比系统的array_intersect快5倍
	*
	* @param array $arr1
	* @param array $arr2
	* @author LIUBOTAO 2010-12-13上午11:40:20
	*
	*/
	static function array_intersect_fast($arr1,$arr2)
	{
		$arr1=array_unique($arr1);
		$arr2=array_unique($arr2);
		for($i=0;$i<count($arr1);$i++){
			$temp[]=$arr1[$i];
		}
		for($i=0;$i<count($arr2);$i++){
			$temp[]=$arr2[$i];
		}
		sort($temp);
		$get=array();
		for($i=0;$i<count($temp);$i++){
			if($temp[$i]==$temp[$i+1])
				$get[]=$temp[$i];
		}
		return array_filter($get);
	} 
}