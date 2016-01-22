<?php
class erp_order extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'erp_order';
    }
}