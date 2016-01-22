<?php

class beu_users extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model ( $className );
	}
	
	public function tableName() {
		return 'beu_users';
	}

}
