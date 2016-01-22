<style>
.con{color: #959595;
    font-family: 宋体;
    font-size: 18px;
    font-weight: bold;
    letter-spacing: 2px;
    text-align: left;
	display: inline-block;
	margin:55px 5%;
	width:90%;
	}
.con a{color:#000}
</style>

<script type="text/javascript">
	ERR_Disable_bool=true;
	power_tabel_num=0;
	$(document).ready(function(){
		timeauto(5);
	});
	function timeauto(num){
		$('#time_span').html(num);
		if(num>0){
			num--;
			setTimeout(function(){
				timeauto(num);
			},1000);
		}else{
			<?php if($status!=0){ ?>
			returl();
			<?php }else{ ?>
			window.location.href='/erp/login';
			<?php } ?>
		}
	}
	function returl(num){
		var num=num?num:1;
		if(history.length>1 && num<10){
			var referrer=document.referrer;
			if(referrer!='' && (referrer.indexOf('/erp/manage')==-1 || referrer.indexOf('/erp/login')==-1)){
				history.go(-1);
				return;
			}
			setTimeout(function(){
				num++;
				returl(num);
			},20);
		}else{
			window.location.href='/erp/manage';
		}
	}
</script>



<div class="clr m_bm10 height1">&nbsp;</div>


<div class="main_m">
	<div class="con">
	<span><?php echo $msg; ?></span><br/><br/><br/>
	<?php if($status==1){ ?>
	<span>请<a href="/erp/login">【退出】</a>后重新用其他具备此权限的账号登录，或者<span id="time_span">5</span>秒后自动 <a href="javascript:returl()">【返回上一页】</a> </span>
	<?php }else if($status==0) {?>
	<span>请<a href="/erp/login">【登录】</a>系统后再执行此操作，或者<span id="time_span">5</span>秒后自动进入登录页面 </span>
	<?php }else {?>
	<span>错误提示：<span id="time_span">5</span>秒后自动<a href="javascript:returl()">【返回上一页】</a>  </span>
	<?php } ?>
	</div>
	<div class="clr"></div>
</div>

<div style="text-align: right; width: 850px; margin: 0 auto;">  

</div>