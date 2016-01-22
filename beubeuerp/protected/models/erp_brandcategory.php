<?php
class erp_brandcategory extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'erp_brandcategory';
    }
}