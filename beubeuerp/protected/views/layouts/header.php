<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<link href="<?php echo Yii::app()->request->baseUrl; ?>/css/beu_e2015.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo Yii::app()->request->baseUrl; ?>/css/facebox_touch.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo Yii::app()->request->baseUrl; ?>/css/buttons.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap-combined.min.css" rel="stylesheet" type="text/css" />
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.tools.min.js"></script>
	<link href="<?php echo Yii::app()->request->baseUrl; ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<link href="http://cdn.bootcss.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/public.js"></script>
</head>
<style>
.nav_m ul li{
	cursor: pointer;
}
.container_fluid_bottom{
	position:relative;
}
</style>
<body style="background:#f7f7f7; font-family:'微软雅黑'; font-size:12px">
<script>
var power_tabel_num=1;//第几个选项卡选中 默认第一个
var power_action=eval('<?php echo json_encode(Yii::app()->params['power_action'])?>');//权限列表
var touchid='<?php echo $_SESSION['touchid_new']?>';//当前触摸屏id
var touchid_str=eval('<?php echo json_encode(Yii::app()->params['new_touch_arr']);?>');//触摸屏列表
var ERR_Disable_bool=false;//是否禁用错误弹窗
$(function() {
	//setyiiPager('.yiiPager');
	setTabel();
	if(power_tabel_num>0){
		setTabelUrl();
	}
	getusertype();
	getusername();
	bottom_div();
	$(window).resize(bottom_div);
});
/**
* 获取用户的登录状态
**/
function getusertype(){
	if(ERR_Disable_bool){
		return;
	}
	$.getJSON('/erp/GetUserType',function(a){
		if(a.status==1){
			setTimeout(getusertype,10000);
		}else{
			var html='<div class="p_main" style="font-size: 14px;padding-bottom: 26px;text-align: center; height: 110px; line-height: 110px;">帐号已在其他地方登录，请&nbsp;<a href="/erp/login"><font style="color:red;font-weight: bold;">重新登录</font></a></div>';
			var p_arr=new Array();
			p_arr['div']=html;
			p_arr['facebox_over_close']=true;
			p_arr['facebox_title']='温 馨 提 示';
			p_arr['fun']=function(facebox_obj){
				facebox_obj.find('.ld_close').remove();
			};
			facebox(p_arr);
		}
	});
}
/**
* 设置管理页面选项卡
**/
function setTabel(){
	$('.head .top ul li').attr('class','');
	var li_len=$('.head .top ul li').length;
	power_tabel_num=parseInt(power_tabel_num);
	if(power_tabel_num==0){//error页面
		$('.head .top ul').remove();
		return;
	}
	if(power_tabel_num>li_len || power_tabel_num<1){
		power_tabel_num=1;
	}
	$('.head .top ul li').eq(power_tabel_num-1).attr('class','sel');
}
/**
* 设置管理页面选项卡 链接
**/
function setTabelUrl(){
	$('.head .top ul li').unbind('click');
	var url_array=new Array('/erp/manage','/erp/orderlist');//标签地址
	$('.head .top ul li').click(function(){
		window.location.href=url_array[$(this).index()];
	});
}
function getusername(){
	$.getJSON('/erp/getusername',function(a){
		$('.head .top .login_rt span').html(a.uname);
	});
}
function bottom_div(){
	var body_hei=$('body').height();
	var win_hei=$(window).height();
	var head_hei=$('.container-fluid.head').height();
	var bottom_hei=$('.container-fluid.bottom').height();
	var div_hei=$('.container_div').css('min-height');
	if((win_hei-head_hei-bottom_hei-40)+'px'!=div_hei){
		$('.container_div').css('min-height',(win_hei-head_hei-bottom_hei-40));
	}
}

</script>
<div class="container-fluid head">
    <div class="top">
   	  <div class="logo w244 fl"><img src="/images/logo_1.jpg" alt="140x140"></div>
      <div class="nav_m w620 fl">
      	<ul>
        	<li class="sel" <?php if(!Beu_Power::selectAction('/erp/manage')){ echo 'style="display:none"'; }?>>图片管理</li>
            <li <?php if(!Beu_Power::selectAction('/erp/orderlist')){ echo 'style="display:none"'; }?>>订单管理</li>
            <li <?php if(!Beu_Power::selectAction('/erp/dplist')){ echo 'style="display:none"'; }?>>搭配管理</li>
        </ul>
      </div>
      <div class="login_rt w244 fr text-right">
   	  登录名：<span></span>&#12288;<a href="/erp/loginout">退出</a></div>
    </div>
</div>
