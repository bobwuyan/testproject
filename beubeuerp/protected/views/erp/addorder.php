
<title>百一服装ERP管理系统3.0_订单管理</title>
<div class="m_body border1">
  <div class="add_order">
    <div class="fl w560">
   	  <div class="txt_t1 text-left">新建订单</div>
      <div class="txt12 text-left line23">
      	1、订单号无需填写，系统自动生成<br />
2、导入后将在服务器中自动建立有款号名的文件夹便于上传图片<br />
3、请按照表格样例填写拍摄单
      </div>
      <div id="downfile-tishi" style="display:none">
      <br /><br />
	      <div class="txt_t1 text-left">重复提示</div>
	      <div class="txt12 text-left line23">
	      	本次订单包涵之前订单中的款色号，不能与新订单一起被创建。
为方便您区分重复款色号，<br />可以下载重复款色号，再做上传。
不重复的款色号已新建立订单，请至订单列表中下载新的文件夹。
			</div>
      </div>
    </div>
    <div class="fl w400">
    <iframe name="uploadexcel" style="display:none"></iframe>
    <form target="uploadexcel" enctype="multipart/form-data" method="post" action="/erp/addsuborder" name="fileform" id="fileform">
        <div class="line50">
          <div class="fl w60">订单号：</div>
          <div class="fl w340 text-left"><input type="text" disabled placeholder="自动生成" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">　　备注：<input type="text" name="description" placeholder="请填写备注" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;"></div>
        </div>
        <div class="line50">
          <div class="fl w60">*拍摄单：</div>
          <div class="fl w340 text-left"><input type="text" placeholder="请填写订单号" id="paishe" class="input-xlarge" style="width:150px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">
            <a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px;" id="daoru">导入拍摄单</a>　
            <input type="file" name="inputExcel" id="inputExcel"  style="display:none" onchange="setpaishetext()" >
            <a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px;" onclick="downorderexecl()">下载样例</a>
          </div>
          <div class="clr" style="height:10px;">&nbsp;</div>
        </div>
        
        <div class="line50">
          <div class="fl w60">&nbsp;</div>
          <div class="fl w140 text-left">
            <a href="#" class="button button-tiny" style="padding:0 10px; width:80px; height:34px; line-height:34px; font-size:12px" onclick="orderexecl() ">创建订单</a>
          </div>
        </div>
        
        </form>
        <div class="clr" style="height:1px;">&nbsp;</div>
        <div id="downfile" style="display:none">
	         <div class="line30" style="line-height:30px">
		          <div class="fl w60">重复SKC：</div>
			          <div class="fl w340 text-left" id="brandnumber"> </div>
		          <div class="clr" style="height:10px;">&nbsp;</div>
	        </div>
	        <div class="line30" style="line-height:30px">
	          <div class="fl w60">&nbsp;</div>
	          <div class="fl w140 text-left" id="downfolder">
	               <a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px;" >下载重款文件夹</a>
	          </div>
	        </div>
        </div>
      </div>
  </div>
</div>



<script>
power_tabel_num=2;
$(document).ready(function(){
	$("#daoru").bind("click",function(){
		$("#inputExcel").click();
	});
});
function setpaishetext()
{
	
	var v  = $("#inputExcel").val();
	$("#paishe").val(v );
	
}
//提交上传
function orderexecl(){
	var v  = $("#inputExcel").val();
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
function setsubclothes(a)
{
	var content="";
	
	if(a.status==1 )
	{
		if( a.msg=="")
		{
			content='订单已创建，请至订单列表下查看订单';
		}else{
			content=a.msg;
		}
	}else{
		content=a.msg;
	}
	var html='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
		+' <div class="p_main">'
		+'  <div style="width:430px; margin:50px auto;">'
		+'      <div class="line50 pad_s20" id="content">'+content+'</div>'
		+'    <div class="line50" id="div">'
		+'        <a href="javascript:void(0)" onclick="closediv()" class="button button-primary button-rounded button-small" style="padding:0 10px; width:100px;">确定</a>'
		+'      </div>'
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
	var html='';
	var repeat_data=a.repeat_data;
	
	if(repeat_data.length>0)//重复的数量大于0
	{
		for(var i=0;i<repeat_data.length;i++)
		{
			html+=repeat_data[i]['brandnumber']+"、";
		}
		$("#downfile").css("display","");//隐藏重复款号的div
		$("#downfile-tishi").css("display","");//隐藏重复款号的div
		
		
	}else{
		
		$("#brandnumber").html(html);
		$("#downfile").css("display","none");
		$("#downfile-tishi").css("display","none");
	}
	$("#brandnumber").html(html);
	//点击下载文件夹
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
function closediv()
{
	$("#addsuccess").closest(".facebox").find('.ld_close').click();
}

function downorderexecl()
{
	window.location.href="/erp/downfile?url=http://erp.beubeu.com/order.xls";
	
}
</script>
</html>
