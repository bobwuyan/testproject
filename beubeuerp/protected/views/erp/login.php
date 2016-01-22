<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>百一服装ERP管理系统3.0_登录页</title>
<link href="/css/beu_e2015.css" rel="stylesheet" type="text/css" />
<link href="/css/buttons.css" rel="stylesheet" type="text/css" />
<link href="/css/bootstrap-combined.min.css" rel="stylesheet" type="text/css" />
<script src="/js/jquery.tools.min.js"></script>
<link href="/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="http://cdn.bootcss.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<style>
	.validation_div{
		height: 29px;
		margin: 0 auto 20px;
		position: relative;
		width:292px;
		overflow: hidden;
	}
	.validation{
		background-color: #fff4f4;
		border: 1px solid #d58a8a;
		border-radius: 3px;
		color: #d58a8a;
		display: inline-block;
		padding: 5px 0;
		line-height: 1.42857;
		width:290px;
		position: absolute;
		top: 29px;
		left:0;
	}
	.validation p{margin:0}
</style>
</head>
<body style="background:#f7f7f7; font-family:'微软雅黑'; font-size:12px">
<div class="container-fluid" style="background:#8f8f8f">
  <div class="login_top"><img src="/images/default_img.jpg" width="1200" height="190" /></div>
</div>
<div class="login_main">
	<div  class="validation_div" >
	<div class="validation">
		<p style="text-align:center;"></p>
	</div>
	</div>
  <div><input id="uname" type="text" placeholder="请输入用户名" class="input-xlarge" style="width:280px; height:50px; font-size:14px; line-height:50px; margin:0; padding:0 6px;"></div>
  <div class="margin_h20"><input id="upwo" type="password" placeholder="请输入密码" class="input-xlarge" style="width:280px; height:50px; font-size:14px; line-height:50px; margin:0; padding:0 6px;"></div>
  <div style="clear:both; height:auto; _height:50px;  min_height:50px;height:50px; width:292px; margin:0 auto;">
    <div class="fl w214 text-left"><input id="code" type="text" placeholder="请输入验证码" class="input-xlarge" style="width:160px; height:50px; font-size:14px; line-height:50px; margin:0; padding:0 6px;"></div>
    <div class="fl w60 text-center num_yzm"><a href="javascript:void(0)">
                                    <img src="/images/getcode/getcode.php" align="absmiddle" onclick="this.src='/images/getcode/getcode.php?'+parseInt(Math.random() * 10000)"
                                    border="0">
                                </a></div>
  </div>
  <div class="margin_h20"><a href="javascript:tijiao()"><img src="/images/login_btn.jpg" width="292" height="49" /></a></div>
</div>
 <script type="text/javascript" language="Javascript">
       var ipp = '<?php echo  $_SERVER["REMOTE_ADDR"];?>';
       
            function tijiao() {
                var unmae = $("#uname").val();
                var upwo = $("#upwo").val();
                var code = $("#code").val();

                if (unmae != "" && upwo != "") {
                    var url = "/erp/getlogin";
                    $.post(url,{unmae:encodeURIComponent(unmae),upwo:upwo,code:code},
                    function(a) {
                        if (a.data == 1) {
                            err_fun("验证码错误");
                        } else if (a.data == 2) {
                            err_fun("用户名或密码错误");
                        } else if (a.data == 3) {
                            err_fun("帐号到期,请联系客服");
                        }else if (a.data == 7) {
                            err_fun("帐号被禁用,请更换帐号");
                        }else if (a.data == 8) {
                            err_fun("帐号不可重复登陆");
                        }else if(a.data == 4){
                        	window.location.href = a.href;  //权限可访问
                        }else if(a.data == 5){
							err_fun('该账号只有在指定IP上才能访问本系统');							
                        }else if(a.data == 6){	
							err_fun('该账号权限不够');
                        }else {
                            err_fun("未知错误");
						}
                    },'json');
                } else {
                   err_fun("用户名或密码不能为空");
                }

            }
			function err_fun(str,is_bool){
				var is_bool=is_bool?is_bool:false;
				if(!is_bool){
					$(".validation p").html(str);
					$('.validation').animate({'top':'0'});
					$('.num_yzm img').click();
				}else{
					$('.validation').animate({'top':'29'});
				}
			}
			
            var SubmitOrHidden = function(evt) {
                evt = window.event || evt;
                if (evt.keyCode == 13) { //如果取到的键值是回车     
                    tijiao();
                }
            }
            window.document.onkeydown = SubmitOrHidden;
			$(document).ready(function(){
				$('input').focus(function(){
					err_fun('',true);
				})
			});
</script>
</body>
</html>
