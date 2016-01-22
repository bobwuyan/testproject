<?php
/**
* 接口参数配置文件
* 以品牌id为基础 给品牌的key必须唯一
**/
return array(
	//C&A 接口配置
	array(
		'brandid'=>186,//必须
		'touchid'=>870,//必须
		'APIkey'=>'DJksjus@GAP_sY1Ld',//必须
		//'SIG_Format'=>'_APP__KEY__DATE__STR_',//客户sig生成格式 可选 不设置就使用百一的格式生成 
		'beu_PushWebsite'=>array(//官网推送接口
			//'Interface'=>'http://www.canda.cn/baiyi/match/save',//调用品牌的接口
			//'Model_Img'=>true,
			//'Img_Size'=>'.600x800.png',//搭配图的尺寸 参数不存在推送原尺寸
			'Data_Max'=>10,//接口最大数据
			//'Status'=>1,//自动调用时 是否启用 不存在就表示不启用
		)
	),
);