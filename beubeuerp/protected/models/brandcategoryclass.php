<?php
/**
 * 品牌自定义分类
 */
class brandcategoryclass {
	/**
	 * 添加自定义分类
	 * @param  $name 自定义分类名
	 * @param  $brandid 品牌ID号
	 */
	public function add_category($name,$brandid) 
	{
		return true;//添加成功返回
	}
	/**
	 * 通过自定义分类ID修改自定义分类信息
	 * @param  $name 自定义分类名
	 * @param  $id 自定义分类的ID编号
	 */
	public function update_category($id,$name) 
	{
		return true;//添加成功返回
	}
	/**
	 * 通过自定义分类ID修改自定义分类信息
	 * @param  $brandid 品牌ID号
	 */
	public function select_category($brandid) 
	{
		$criteria = new CDbCriteria;
		$criteria->select = '*';
		$criteria->order = 'id DESC';
		$criteria->addCondition('brandid=:brandid');
		$criteria->addCondition('status=0');
		$criteria->params[':brandid']=$brandid;
		
		$users = erp_brandcategory::model()->findAll( $criteria );
		return $users;//查询成功返回
	}
}
