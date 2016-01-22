
<title>百一服装ERP管理系统3.0_订单管理</title>
<link rel="stylesheet" type="text/css" href="/css/pager.css" />
<style>
	.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center;line-height: 20px; padding: 0px 3px;
	}
	
</style>
<div class="m_body border1">
	<div class="fl w235">
		&nbsp;
    </div>
    <div class="fl w800 text-left">
      <div class="fl">排序：<a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 15px;" id="dataorder" sal="<?php echo $dateorder;?>" onclick="Pagerefresh(0)" >时间排序<?php if($dateorder=="desc"){echo "↓";}else{echo "↑";}?></a></div>
      <div class="fl pad_l10" style="height:30px; line-height:30px; margin:0; padding:0 10px;">搜索查询： 
      <select name="" size="1" id="startdate" style="width:105px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option>开始时间</option>
        <?php 
        foreach($datearray as $value)
        {
        	?>
        		 <option value="<?php echo $value?>" <?php if($strd_data==$value){echo selected;}?>><?php echo $value?></option>
        	<?php
        }
        ?>
      </select>
      <select name="" size="1" id="end_data" style="width:105px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option>结束时间</option>
        <?php 
        foreach($datearray as $value)
        {
        	?>
        		 <option value="<?php echo $value?>" <?php if($end_data==$value){echo selected;}?>><?php echo $value?></option>
        	<?php
        }
        ?>
      </select>
      <input id="keyword" type="text" value="<?php echo $key?>" placeholder="" class="input-xlarge" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">
      <select id="type" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
         <option value="1" <?php if($type==1){echo selected;}?>>订单号</option>
        <option value="2" <?php if($type==2){echo selected;}?>>备注</option>
      </select>     
      <a href="javascript:void(0)" onclick="Pagerefresh(1)" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">查询</a>
      </div>

      
    </div>
<div class="fr w140 text-left">
	<?php if(Beu_Power::selectAction('/erp/addorder')){ ?>
		<a href="/erp/addorder" class="button button-primary button-rounded button-small" style=" background:#F00; color:#FFF">新建订单+</a>
     <?php }?>
      </div>
</div>   
<div class="m_body2 ">
  <div class="tab_head text-center border2" >
    <div class="fl w94 border_rt">序号</div>
    <div class="fl w194 border_rt">订单号</div>
    <div class="fl w214 border_rt">发货数量</div>
    <div class="fl w194 border_rt">创建时间</div>
    <div class="fl w194 border_rt">备注内容</div>
    <div class="fl w256">操作</div>
  </div>
  <?php 
  if(isset($data['data']))
  {
  		$num=1;
  		$count=count($data['data']);
  		foreach($data['data'] as $value)
  		{
  			if($count<=1){
  				$border=2;
  			}else{
	  			$border=3;
	  			if($num%2==0)
	  			{
	  				$border=2;
	  			}
	  			$num++;
  			}
  			?>
  				 <div class="tab_head text-center border<?php echo $border;?>" >
				    <div class="fl w94 border_rt"><?php echo $value['id']?></div>
				    <div class="fl w194 border_rt"><?php echo $value['ordername']?></div>
				    <div class="fl w214 border_rt"><?php echo $value['barcodecount']?></div>
				    <div class="fl w194 border_rt" ><?php echo $value['addtime']?></div>
				    <div class="fl w194 border_rt line23 " style="height:47px"><div class="div_table" onclick="upRemarks(this,<?php echo $value['id']?>)"><div class="div_table_cell"><?php if(!empty($value['description'])){echo mb_substr($value['description'],0,20,'utf-8');}else{ echo '点击添加备注';}?></div></div></div>
				   <div class="fl w256" style="height: 100%;width:300px">
					   <div class="div_table"><div class="div_table_cell">
					    <?php if(Beu_Power::selectAction('/erp/downfolder')){ ?>
					    <a href="/erp/downfolder/orderid/<?php echo $value['ordername'];?>" class="button button-primary button-rounded button-small" style="padding:0 10px;">下载文件夹</a>
					   <?php }?>
					    <?php if(Beu_Power::selectAction('/erp/addsuborder'))
					    {
					    	if($value['haveimage']==0)
					    	{   ?>
					    			<a href="javascript:void(0)" onclick="updateorder(<?php echo $value['id']?>,'<?php echo $value['ordername']?>','<?php echo $value['description'];?>')" class="button button-primary button-rounded button-small" style="padding:0 10px;">修改订单</a>
					      	    <?php 
					    	}
					    }?>
					       <?php if(Beu_Power::selectAction('/erp/getorderlogdate'))
					       {  
								if($value['haveimage']==1)
						    	{
							    	?>
						  		 		 <a href="javascript:void(0)" onclick="getorderlogdate(<?php echo $value['id']?>)" class="button button-primary button-rounded button-small" style="padding:0 10px;">查看日志</a>
						  		 		 <?php 
						    	}
					       }?>
					        <a href="/erp/downfile?url=<?php echo 'http://erp.beubeu.com/uploads/saveexcel/'.$brandid.'_'.$value['ordername'].'.xls'?>" class="button button-primary button-rounded button-small" style="padding:0 10px;">下载EXCEL表格</a>
						  		  
					  </div> </div>
					 
				  </div>
				  </div>
  			<?php
  		}
		if($count==0){
			echo '<div class="tab_head text-center border3" style="border-bottom:1px solid #d9d9d9">查询无果</div>';
		}
  }
  
  ?>
 
 <div class="tab_head text-center border4 clr" >
  
    <div class="page_1">
     <div style="width:700px; float:right;font-size: 12px;color:#000;text-align: right;padding:0px 24px">
   <?php if(isset($data['page'])){
	$this->widget ( 'CLinkPager', array (
	'header' => $data['page']->getItemCount()."条&nbsp;&nbsp;共".$data['page']->getPageCount()."页&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
	'firstPageLabel' => '首页',    
    'lastPageLabel' => '末页',    
    'prevPageLabel' => '上一页',    
    'nextPageLabel' => '下一页', 
	'pagelist' => true,
	'maxButtonCount' => 5, 'pages' => $data['page'], "cssFile" => "/css/pager.css" ));
	}
	?>
  </div>
    </div>
  </div>
</div>
 



<script>
$(function() {
	var tab_head_len=$('.m_body2 .tab_head').length;
	if($('.m_body2 .tab_head').eq(tab_head_len-2).hasClass('border3')){
		$('.m_body2 .tab_head').eq(tab_head_len-2).css('border-bottom','1px solid #d9d9d9');
	}
});
power_tabel_num=2;
function Pagerefresh(num)
{
	
	var url='/erp/orderlist';
	
	var dateorder=$("#dataorder").attr("sal");
	if(num==0)
	{
		if(dateorder=="desc")
		{
			dateorder="asc";
		}else{
			dateorder="desc";
		}
	}
	
	url+='/dateorder/'+dateorder;
	
	var startdate=strid('startdate');
	
	if(startdate!='' && parseInt(startdate)>0)
	{
		url+='/startdate/'+startdate;
	}
	var end_data=strid('end_data');
	if(end_data!='' && parseInt(end_data)>0)
	{
		url+='/enddate/'+end_data;
	}
	var type=strid('type');
	if(type!='' && parseInt(type)>0)
	{
		url+='/type/'+type;
	}
	var keyword=$('#keyword').val();
	if(keyword!='')
	{
		url+='/keyword/'+keyword;
	}
	window.location.href=url;
	//alert(type+"---"+startdate+"---"+end_data+"---"+dateorder+"---"+keyword);
}
function strid(id)
{
	var value=$('#'+id).val();
	if(!strempty(value))
	{
		value=value.split('///')[0];
	}
	else
	{
		value='';
	}
	return value;
}
//查看日志时间
function getorderlogdate(orderid){
	
	$.getJSON('/erp/getorderlogdate?orderid='+orderid,function(a){
		
		if(a.status==1)
		{
			getorderlog(orderid,a.data);
		}else{
			var html='<div   style="width: auto;  margin-top: 0px; opacity: 1;">'   
				+' <div class="p_main">'
				+'  <div style="width:430px; margin:50px auto;">'
				+'      <div class="line50 pad_s20" id="content">没有日志</div>'
				+'   </div>'
				+'   </div>'
				+'</div>';
			var arr=new Array();
			arr['facebox_title']="操作日志";
			arr['div']=html;
			arr['width']='728px';
			facebox(arr);
			
		}
	});
}
//查看日志
function getorderlog(orderid,dateobj,addtime)
{
	
	addtime=addtime?addtime:'';
	$.getJSON('/erp/getorderlog?orderid='+orderid+'&addtime='+addtime,function(a){
		var option='<option value="">上传时间</option>';
		if(a.status==1)
		{
			var log='';
			for(var i in dateobj)
			{
				var logtime=dateobj[i];
				option+='<option value="'+logtime.substr(0,10)+'">'+logtime.substr(0,10)+'</option>'
			}
			var logdata=a.data;
			for(var i in logdata)
			{
				log+=logdata[i]['orderlog'];
				
			}
			var html='<div id="facebox2" style="width: auto; top: 500px; left: 280.5px; margin-top: 0px; opacity: 1; ">'
				+'<div class="p_main" style="height:410px">'
				+' 	  <div class="fl w170">&nbsp;</div>'
				+' 	  <div class="fl w380">'
				+'    	<div class="border1 text-left pad_5" id="content" style="height:368px; overflow-y:scroll; width:380px;">'+log+' </div>'
				+' 	  </div>'
				+'    <div class="fl w170">'
				+'    	<select id="selectdate" name="" size="1" style="width:103px; height:30px; line-height:30px; margin:0; font-size:12px;" >'
				+option
				+'    </select>'
				+'    </div>'
				+'  </div>'
				+'</div>';
				var arr=new Array();
				arr['facebox_title']="操作日志";
				arr['div']=html;
				arr['width']='728px';
				arr['fun']=function(){
					$("#selectdate").bind("change", function() {
						//getorderlog(orderid,dateobj,this.options[this.options.selectedIndex].value);
						addtime=this.options[this.options.selectedIndex].value;
						$.getJSON('/erp/getorderlog?orderid='+orderid+'&addtime='+addtime,function(c){
								if(c.status==1){
									var log2='';
									var logdata2=c.data;
									for(var i in logdata2)
									{
										log2+=logdata2[i]['orderlog'];
									}
									$("#facebox").find("#content").html(log2);
								}else{
									alert("没有数据");
								}
							});
						});
					
					
					}
				facebox(arr);
		}else{
			alert("没有日志");
		}
		
	});

	
}
function setpaishetext()
{
	var v  = $("#inputExcel").val();
	$("#facebox").find("#paishe").val(v );
}
function updateorder(orderid,ordername,content)
{
	
	var order_name=ordername;

	var html='<div id="facebox2" style="width: auto; top: 500px; left: 280.5px; margin-top: 0px; opacity: 1; ">'
	       	+'<div class="popup">'
			+'  <div class="p_main">'
			+'    <div style="width:430px; margin:0 auto;">'
			+'		<iframe name="uploadexcel" style="display:none"></iframe>'
			+'		<form target="uploadexcel" enctype="multipart/form-data" method="post" action="/erp/addsuborder" name="fileform" id="fileform">'
			+'      <div class="line50">'
			+'        <div class="fl w60">订单号：</div>'
			+'        <div class="fl w340 text-left"><input type="hidden" name="orderid" value="'+orderid+'"/><input type="hidden" name="ordername" value="'+order_name+'"/><input type="text"  disabled value="'+order_name+'"   placeholder="'+order_name+'" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">　　备注：<input type="text" value="'+content+'" onkeyup="this.value=this.value.substring(0, 20)" onkeydown="this.value=this.value.substring(0, 20)" id="description" name="description" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;"></div>'
			+'      </div>'
			+'      <div class="line50">'
			+'        <div class="fl w60">*拍摄单：</div>'
			+'		<div class="fl w340 text-left"><input type="text" placeholder="请填写订单号" id="paishe" class="input-xlarge" style="width:150px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
			+'		<a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px;" id="daoru">导入拍摄单</a>　'
			+'   	 <input type="file" name="inputExcel" id="inputExcel"  style="display:none" onchange="setpaishetext()" >'
			+'      <a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px;" onclick="downorderexecl()">下载样例</a>'
			+'		<div class="clr" style="height:10px;">&nbsp;</div>'
			+'      </div>'
			+'      <div class="line50">'
			+'        <div class="fl w60">&nbsp;</div>'
			+'        <div class="fl w140 text-left">'
			+'          <a href="#" class="button button-tiny" style="padding:0 10px; width:80px; height:34px; line-height:34px; font-size:12px" onclick="orderexecl() ">确定修改</a>'
			+'        </div>'
			+'      </div>'
			+'		</form>'
			+'    </div>'
			+'  </div>'
			+'</div>'
			+'</div>';
			
	var arr=new Array();
	arr['facebox_title']="修改订单";
	arr['div']=html;
	arr['fun']=function(){
		$(document).ready(function(){
			$("#facebox").find("#daoru").bind("click",function(){
				$("#inputExcel").click();
			});
		});
		}
	arr['width']='728px';
	facebox(arr);
}
function setsubclothes(a)
{
	var content="";
	var show="block";
	if(a.status==1 )
	{
		if( a.msg=="")
		{
			content='订单已创建，请至订单列表下查看订单';
			show="none";
		}else{
			content=a.msg;
		}
	}else{
		content=a.msg;
	}
	var repeat_data=a.repeat_data;
	var html='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
		+' <div class="p_main">'
		+'  <div style="width:430px; margin:0px auto; margin-top:50px" >'
		+'      <div class="line50" id="content">'+content+'</div>'
		+'   </div>'
		+'  <div style="width:430px; margin:0px auto;display:'+show+'" id="downfolder">'
		+'       <a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px;" >下载重款文件夹</a>'
		+'   </div>'
		+'   </div>'
		+'</div>';
	
	
	$("#addsuccess").css("display","");
	var arr=new Array();
	arr['div']=html;
	arr['top']=150;
	arr['left']=($(window).width()>712)?(712/2):0;;
	arr['facebox_title']="提示";
	arr['width']='728px';
	facebox(arr);
	$("#downfolder").bind("click",function(){
		$.ajax({  
		    url: '/erp/downrepeatfolder',  
		    data: { "orderinfo": repeat_data },  
		    dataType: "json",  
		    type: "POST",   
		    success: function (a) {  
			    if(a.status==1)
			    {	
			    	 window.location.href="/erp/downfile?url="+a.url;
			    }
		     }  
		}); 
		
	
	});
}
//提交上传
function orderexecl(){
	var v  = $("#inputExcel").val();
	var description  = $("#description").val();

	
	if(v=="" && description!="")
	{
		$('#fileform').submit();
	}
	else {
		
		var tt = v.split('.');
		var len = tt.length-1;
		
		if(tt[len] != 'xls'){
			//suborr(1);
			var html='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">'   
				+' <div class="p_main">'
				+'  <div style="width:430px; margin:50px auto;">'
				+'      <div class="line50 pad_s20" id="content">请检查文件格式，应为.xls格式</div>'
				+'   </div>'
				+'   </div>'
				+'</div>';
			var arr=new Array();
			arr['div']=html;
			arr['top']=150;
			arr['left']=($(window).width()>712)?(712/2):0;;
			arr['facebox_title']="提示";
			arr['width']='728px';
			facebox(arr);
		}else{
			$('#fileform').submit();
		}
	}
}
function downorderexecl()
{
	window.location.href="/erp/downfile?url=http://erp.beubeu.com/order.xls";
	
}
/**
* 修改备注
**/
function upRemarks(obj,id){
	var con_str=$(obj).find('.div_table_cell').html();
	if(con_str=='点击添加备注'){
		con_str='';
	}
	var html='<div>'
    +'<textarea placeholder="备注最多放20个汉字" onkeyup="this.value=this.value.substring(0, 20)" onkeydown="this.value=this.value.substring(0, 20)" onchange="this.value=this.value.substring(0, 20)" maxlength="20" style="height:23px; width:120px;float:left; padding:5px;margin:5px;font-size:12px; resize:none;" rows="2" cols="20">'+con_str+'</textarea>'
	+'<label style="display: none;">'+con_str+'</label>'
    +'</div>'
    +'<div class="w50 text-right" style="float:left;margin: 8px 0"><a class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;" onclick="wantRemarks(this,'+id+')">保存</a></div>';
	$(obj).closest('.border_rt').html(html);
}
/**
* 保存备注
**/
function wantRemarks(obj,id){
	var con_str=$(obj).closest('.border_rt').find('textarea').val();
	var con_str2=$(obj).closest('.border_rt').find('label').html();
	if(con_str!=con_str2){
		ajax_wantRemarks(obj,id);
	}
	if(con_str==''){
		con_str='点击添加备注';
	}
	$(obj).closest('.border_rt').html('<div style="cursor: pointer;" class="div_table" onclick="upRemarks(this,'+id+')"><div class="div_table_cell">'+con_str+'</div></div>');
}
/**
* 保存备注
**/
function ajax_wantRemarks(obj,id){
	var con_str=encodeURIComponent($(obj).closest('.border_rt').find('textarea').val());
	$.getJSON('/erp/updatedescription?id='+id+'&description='+con_str,function(a){
		if(a.status==1){
			PromptPop('修改成功！',true);
		}else{
			PromptPop('修改失败！',true);
		}
	});
}
/**
* 提示弹窗
**/
function PromptPop(txt,is_bool){
	var is_bool=is_bool?is_bool:false;
	if(!is_bool){
		var clothes_str=getClothesnum();
		if(clothes_str!=''){
			return true;
		}
	}
	var html='<div class="popup">'
    +'<div class="p_main">'
    +'<div style="margin:0 auto; line-height:130px;" class=" w340 pad_t50 t_16b">'
    +(txt?txt:'请选择衣服！')
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['Single']=true;
	facebox(p_arr);
	
	return false;
}
</script>
</html>
