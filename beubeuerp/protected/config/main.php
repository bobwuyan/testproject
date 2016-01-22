<?php

$controller="Match";
$host=$_SERVER['HTTP_HOST'];
if($host=='ld1.beubeu.com' || $host=='ld2.beubeu.com' || $host=='ld3.beubeu.com' || $host=='ld4.beubeu.com'){
	
	$controller="Ld";
}
else if($host=='858.beubeu.com')
{
	$controller="Tc";
}
else if($host=='ipad3.beubeu.com')
{
	$controller="Beuipad";
}
else if($host=='mirror1.beubeu.com')
{
	
	$controller="Beutouch";
}
else if($host=='mix.canda.cn')
{
	$controller="CA";
	if (strpos ( strtolower ( $_SERVER ['HTTP_USER_AGENT'] ), "iphone" ) == true || strpos ( strtolower ( $_SERVER ['HTTP_USER_AGENT'] ), "ndroid" ) == true || strpos ( strtolower ( $_SERVER ['HTTP_USER_AGENT'] ), "pod" ) == true || strpos ( strtolower ( $_SERVER ['HTTP_USER_AGENT'] ), "ipad" ) == true) 
	{
		$controller="CAmobile";
	} 
}



// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',
	'sourceLanguage'=>'cn',     
	'language'=>'zh_cn', 
	'defaultController'=>$controller,
	// preloading 'log' component
	'preload'=>array('log'),
	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
 		'application.extensions.PHPExcel.*',
	),
	'modules'=>array(
		// uncomment the following to enable the Gii tool
		
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'baiyi',
		    //If removed, Gii defaults to localhost only. Edit carefully to taste.
 			'ipFilters'=>array('127.0.0.1','::1'),
		 ),
		
	),
	
	// application components
	'components'=>array(
		'cache'=>array(
            'class'=>'CMemCache',
            'servers'=>array(
                array(
                    'host'=>'127.0.0.1',
                    'port'=>11211,
                    'weight'=>60,
                )
            ),
        ),
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>false,
      	    'class'=>'WebUser',
       		'loginUrl' => array('/admin/userlogin'),
		),
		'coreMessages'=>array(     
        'basePath'=>'protected/messages',     
        ),
		// uncomment the following to enable URLs in path-format
	'urlManager'=>array(
			'urlFormat'=>'path',
		    'showScriptName' => false,//隐藏index.php
			'rules'=>array(
//				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
//				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
//				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				//'http://<_m:(ipad2)+>.beubeu.com<_q:.*>/*'=>'match<_q>',
				'image', 'file', 'allowEmpty'=>true,
				'types'=>'jpg, jpeg, gif, png',
				'maxSize'=>1024 * 1024 * 5, // 1MB
				'tooLarge'=>'上传文件超过 5MB，无法上传。',
			),
		),
		/*'db'=>array(	
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		*/
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=new_beubeu',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '123456',
//			'password' => 'baiyi',
			'charset' => 'utf8',
		),
		'helpdb'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=help_you_pick',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '123456',
		//	'charset' => 'utf8',
		),
		'beudb'=>array(
		//'class'=>'CDbConnection',
			'connectionString' => 'mysql:host=localhost;dbname=beubeu',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '123456',
		//	'charset' => 'utf8',
		),
		
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		
		//所有百一全局参数均存放于此。
		'adminEmail'=>'webmaster@example.com',
		'local'=>false,//是否本地版本
		'domain'=>'http://new.beubeu.com/',//默认域名，注意当Local为true是本地版时，直接截取URL的域名和端口号。
		'cookieDomain'=>'/',//一般为 / ,表示当前域名，如果是网络版，需要修改成根域名，例如beubeu.com
		'uploadPath' =>'/images',
		'cacheshort' => 30, //缓存短时间30秒
		'cachemiddle' => 300,//缓存中等时间，暂定5分钟
		'cachelong' => 3600,//长缓存，暂存1小时
		'power_action'=>array(),//权限数组
		'user_type'=>0,//用户权限
		'new_touch_arr'=>array(),//触摸屏列表
		'main_type'=>5,//主屏权限编号  总分屏使用
		'sub_type'=>7,//子屏权限编号  总分屏使用
		'cocoa_brandid'=>188,//配搭品牌
		'img_server_host'=>'http://pic1.beubeu.com',//图片服务器地址
		'web_server_host'=>'http://new.beubeu.com',//程序服务器地址
		'Backup_local_path'=>'E:\\beubeu.com/pic1.beubeu.com/',//备份本地图片路径
		'local_path'=>'D:\\beubeu.com/pic1.beubeu.com/',//本地图片路径
	),
	
	
);