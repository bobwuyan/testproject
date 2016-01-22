<?php
class userlogin extends CFormModel
{
	public $username;
	public $password;
	public $verifyCode;
	public function rules()
	{
		return array(
			// 验证用户名和密码为空
			array('username', 'required','message'=>yii::t('beu_users',"用户名").yii::t('public',"不能为空")),
			array('username', 'length', 'min'=>1, 'max'=>20),
			array('password', 'required','message'=>yii::t('beu_users',"密码").yii::t('public',"不能为空")),
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements(), 'message'=>yii::t('beu_users',"验证码输入不正确，请重新输入")),
		);
	}
	public function attributeLabels()
	{
		return array(
			'verifyCode'=>'Verification Code',
		);
	}
	
}
