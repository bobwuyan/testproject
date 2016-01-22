
<title>百一服装ERP管理系统3.0_图片推送</title>
<link rel="stylesheet" type="text/css" href="/css/pager.css" />
<style>
.progress {
    background-color: #ccc;
    background-image: -webkit-linear-gradient(top, #ccc, #ccc); */

}
.progress-bar {
   
    color: #fff;
    background-color: #428bca;
    
}
	.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center; padding: 0px 3px;
	}
</style>

<div class="m_body border1">
	<div class="fl w80 t_16b pad_l20">
		图片推送
    </div>
</div>   
<div class="m_body2 ">
  <div class="tab_head text-center border2" style="background:#ebebeb">
    <div class="fl w380 border_rt">推送进度</div>
    <div class="fl w310 border_rt">推送时间</div>
    <div class="fl w500">推送结果</div>
  </div>
  <div class="tab_head text-center border3b" >
    <div class="fl w380 border_rt line23 pad_t20 h116" id="progress_id">
    
		<div class="progress" style=" background-color: #ccc;width:280px;margin:12px auto;">
		   <div class="progress-bar" role="progressbar" aria-valuenow="0" 
		      aria-valuemin="0" aria-valuemax="0" style="width: 0%;" >
		      <span class="sr-only">0% 完成</span>
		   </div>
		</div>
图片推送中请不要关闭页面</div>
    <div class="fl w310 border_rt"><?php echo date("Y-m-d H:i:s");?></div>
    <div class="fl w500" style="display:none" id="result">
</div>
  </div> 
</div>
 



<script>
var data=eval(<?php echo json_encode($data);?>);
var cut_detaile=eval(<?php echo json_encode($cut_obj);?>);
var count=0;//总数
var ok_count=0;//完成数量
var success_count=0;//成功数量
var fail_count=0;//失败数量
$(document).ready(function(){
	for(var i in data)
	{
		var push_detaile=eval(data[i]['push_detaile']);
		data[i]['push_detaile']=push_detaile;
		count+=push_detaile.length;
	}
	if(count==0)
	{
		$("#progress_id").css("padding-top","42px");
		$("#progress_id").html("暂无可推送内容");
	}
	pushimage();
});

function pushimage()
{
	var find=0;//寻找未推送的图片，0为未找到
	var type=0;
	for(var i in data)
	{
		var push_detaile=data[i]['push_detaile'];
		if(typeof(push_detaile)=="object")
		{
			for(var j in push_detaile)
			{
				if(push_detaile[j]['pushstatus']==0 && type==0)//未推送的图片
				{
					type=1;
					$.ajax({  
					    url: '/erp/pushimage',  
					    data: { "push_detaile": push_detaile[j],"cut_detaile": cut_detaile,"imageid":push_detaile[j]['imageid'],"skc":push_detaile[j]['skc']},  
					    dataType: "json",  
					    type: "POST",   
					    success: function (b) {  
					    	ok_count++;
							var percent=(ok_count/count).toFixed(2)*100;
					    	var status=0;//0成功，1失败
					    	data[i]['push_detaile'][j]['pushstatus']=1;
					    	if(b.status==1)//b.status=1为成功
						    {
					    		success_count++;
						    	$(".progress-bar").attr("aria-valuenow",percent);
								$(".progress-bar").attr("style","width:"+percent+"%");
								
								$(".progress-bar span").html(percent+"% &nbsp;完成");
								
								type=0;
								pushimage();
								status=0;//成功
							}else{
								fail_count++;
								status=1;//失败
							}
						    data[i]['push_detaile'][j]['image_url']=b.image_url;
						    data[i]['push_detaile'][j]['result_status']=status;
						    if(ok_count==count)
							{
								var html="成功："+success_count+"张 &nbsp;失败："+fail_count+"张";
								html+='<a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0; margin:0 20px; width:60px;" onclick="show_detaile()">看详情</a>';
								$("#result").html(html);
								$("#result").css("display","");
							}
						 }  
					}); 
					find=1;
					return;
				}
			}
		}
		if(find!=0)
		{
			break;
		}
	}
}
function show_detaile()
{
	var log="";
	for(var i in data)
	{
		var push_detaile=data[i]['push_detaile'];
		if(push_detaile.length>0)
		{
			log+="款色号："+data[i]['brandnumber']+"<br>";
		}
		if(typeof(push_detaile)=="object")
		{
			for(var j in push_detaile)
			{
				var status="成功";
				if(push_detaile[j]['pushstatus']==0)//0为未推送，1为推送成功
				{
					status="失败";
				}
				log+=status+"&nbsp;&nbsp;&nbsp;&nbsp;"+push_detaile[j]['image_url']+"<br>"
			}
		}
	}
	var html='<div id="facebox2" style="width: auto; top: 500px; left: 280.5px; margin-top: 0px; opacity: 1; ">'
		+'<div class="p_main" style="height:410px">'
		+' 	  <div class="fl" style="width:130px">&nbsp;</div>'
		+' 	  <div class="fl w380">'
		+'    	<div class="border1 text-left pad_5" id="content" style="height:368px; overflow-y:scroll; width:460px;">'+log+' </div>'
		+' 	  </div>'
		+'    <div class="fl w170">'
		+'    </div>'
		+'  </div>'
		+'</div>';
	var arr=new Array();
	arr['facebox_title']="推送详情";
	arr['div']=html;
	arr['width']='728px';
	facebox(arr);
}
</script>

