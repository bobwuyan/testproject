<?php
	/**
	* 缓存名称  
	* 统一管理 防止重复
	**/
 
	class CacheName{
		static $cache_name_arr=array(
			'touch_clothes_adddate_cache'=>'touch_clothes_adddate_cache_',//衣服列表添加时间缓存
			'touch_Category_list'=>'touch_Category_list_',//分类列表缓存
			'touch_Clothes_Category_list'=>'touch_Clothes_Category_list_',//衣服一级分类列表缓存
			'touch_Clothes_Category2_list'=>'touch_Clothes_Category2_list_',//衣服二级分类列表缓存
			'brand_name_list'=>'brand_name_list_',//品牌名列表缓存
			'brand_name_list_all'=>'brand_name_list_all',//所有品牌名列表缓存
			'touch_info'=>'touch_info_',//搭配屏的详细信息
			'account_Select'=>'account_Select_',//账户对应的自定义标签
			'default_dp_list'=>'default_dp_list_',//默认搭配列表
			'user_info'=>'user_info_',//用户信息
			'touch_name_list'=>'touch_name_list_',//搭配屏名称列表
			'user_action_Info'=>'user_action_Info_',//用户的可访问页面列表
			'display_template_list'=>'display_template_list',//陈列手册模板列表
			'display_template_Info'=>'display_template_Info',//陈列手册模板详情
			'display_Relation_list'=>'display_Relation_list',//陈列手册模板关联列表
			'display_Relation_Info'=>'display_Relation_Info',//陈列手册模板关联详情
			'display_handbook_Info'=>'display_handbook_Info_',
			'display_Manual_list'=>'display_Manual_list',//陈列手册列表
		);
		/**
		* 获取缓存名
		* @param $cache_name 缓存名称关键词
		* @return 返回缓存名
		**/
		static function getCacheName($cache_name){
			return isset(self::$cache_name_arr[$cache_name])?self::$cache_name_arr[$cache_name]:'';
		}
	}
?>