<?php
class beu_clothesdifferent extends CActiveRecord
{
	public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
 
    public function tableName()
    {
        return 'beu_clothesdifferent';
    }
}