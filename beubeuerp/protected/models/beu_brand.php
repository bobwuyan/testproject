<?php
class beu_brand extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'beu_brand';
    }
}