<?php
class erp_brandnumber_order extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'erp_brandnumber_order';
    }
}