<title>百一服装ERP管理系统3.0_做图设置</title>
<style>
.auto_height{
	padding-bottom: 1px;
	margin-bottom: 1px; 
}
.border5 {
    border: #000 1px solid;
    border-top: #fff;
}.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center; padding: 0px;
	}
</style>
<div class="m_body border1">
  <div class="add_fav auto_height">
    <div class=" auto_height" style="overflow:hidden">
    <div class="fr w80 text-left">
        <a href="javascript:void(0)" class="button button-primary button-rounded button-small" onclick="cutdetailsubmit()" style="padding:0 10px; width:80px;">保存</a>
      </div>
      <div class="fr w110 text-left">
        <a href="javascript:void(0)" class="button button-primary button-rounded button-small" onclick="addcutdetailes()" style="padding:0 10px; width:100px;">+添加信息</a>
      </div>
      
       <iframe name="uploadimg" style="display:none"></iframe>
    <form target="uploadimg" enctype="multipart/form-data" method="post" action="/erp/uploadwatermarkimg" name="fileform" id="fileform">
      
      <div class="fl w270 text-left">
        上传水印1：<a href="javascript:void(0)" id="shuiyin1" class="button button-primary button-rounded button-small" style="padding:0 10px; width:80px;">添加</a>
        <a href="javascript:void(0)" id="yulan1" class="button button-primary button-rounded button-small" style="padding:0 10px; width:50px;">预览</a>
<!--      <a href="javascript:void(0)" id="del1" class="button button-primary button-rounded button-small" style="padding:0 10px; width:50px;">删除</a>-->
       <input type="file" name="watermark1" id="watermark1"  style="display:none">
      </div>
      <div class="fl w270 text-left">
       <div style="float: left; vertical-align: middle; line-height: 29px;"> 上传水印2：</div>
        <div  id="watermark_div2">
        <a href="javascript:void(0)" id="shuiyin2" class="button button-primary button-rounded button-small" style="padding:0 10px; width:80px;">添加</a>
         <a href="javascript:void(0)" id="yulan2" class="button button-primary button-rounded button-small" style="padding:0 10px; width:50px;">预览</a>
<!--      <a href="javascript:void(0)" id="del2" class="button button-primary button-rounded button-small" style="padding:0 10px; width:50px;">删除</a>-->
     
        <input type="file" name="watermark2" id="watermark2"  style="display:none"  >
        </div>
      </div>
      </form>
    </div>
    <form enctype="multipart/form-data" method="post" action="/erp/setpatform"  name="cutform" id="cutform">
    <input type="hidden" name="patformcount" id="patformcount" value=""/>
    <div class=" margin_t20 line40" style=" height:40px; background:#000; color:#FFF; overflow:hidden">
   	  <div class="fl w156">平台名</div>
      <div class="fl w256">裁剪尺寸</div>
      <div class="fl w156">选图规则</div>
      <div class="fl w400">水印设置&nbsp;<img src="/images/help.png" onclick="showwatermark()" style="cursor:pointer"></div>
      <div class="fr w170">操作</div>
    </div>
     <?php 
    $num=1;
    foreach($cut_detaile_obj as $cut_detaile_obj_value)
     {
     	
     	?>
    <div class="line40 border5 h116 " id="<?php echo 'div'.$cut_detaile_obj_value['id'];?>" style="background:#fff; color:#000; overflow:hidden; line-height:116px;">
   	  <div class="fl w156 border_rt00 h116">
   	   <input type="hidden" name="<?php echo 'id'.$num;?>" value="<?php echo $cut_detaile_obj_value['id']?>"/>
      <select name="<?php echo 'patform'.$num;?>" onChange="checkselect(this)" size="1" style="width:120px; height:30px; line-height:30px; margin:45px 0; font-size:12px;">
        
        <option value="0">选择推送平台</option>
        <?php 
        foreach($patform_obj as $patform_obj_value)
        {
        	$select="";
        	 if($patform_obj_value['id']==$cut_detaile_obj_value['patformid'])
        	 {
        	 	$select="selected";
        	 }
        	?>
	        <option value="<?php echo $patform_obj_value['id']?>" <?php echo $select;?>><?php echo $patform_obj_value['name']?></option>
	        <?php 
	    }
        ?>
       
      </select></div>
      <div class="fl w256 border_rt00 h116">
      <input type="text" placeholder="填写宽" name="<?php echo 'width'.$num;?>" id="<?php echo 'width'.$num;?>" class="input-xlarge" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $cut_detaile_obj_value['width']?>">
      ×
      <input type="text" placeholder="填写高" name="<?php echo 'height'.$num;?>" id="<?php echo 'height'.$num;?>" class="input-xlarge" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $cut_detaile_obj_value['height']?>">
      </div>
      <div class="fl w156 border_rt00 h116 pad_t30">
      	<div class="line28"><input type="radio" name="<?php echo 'cutradio'.$num;?>" <?php if($cut_detaile_obj_value['barcodetype']==0){echo "checked='checked'";}?> value="0" style="margin:0; width:15px;"/>在色里选</div>
        <div class="line28"><input type="radio" name="<?php echo 'cutradio'.$num;?>" <?php if($cut_detaile_obj_value['barcodetype']==1){echo "checked='checked'";}?> value="1" style="margin:0; width:15px;"/>在款里选</div>
      </div>
      <div class="fl w400 border_rt00 h116">
      	<div class="fl w110 pad_t30">
        	<div class="line28"><input type="radio" name="<?php echo 'waterradio'.$num;?>" <?php if($cut_detaile_obj_value['havewatermark']==0){echo "checked='checked'";}?> value="0" style="margin:0; width:15px;"/>默认有水印</div>
            <div class="line28"><input type="radio" name="<?php echo 'waterradio'.$num;?>" <?php if($cut_detaile_obj_value['havewatermark']==1){echo "checked='checked'";}?> value="1" style="margin:0; width:15px;"/>默认无水印</div>
        </div>
        <div class="fl w235 pad_t10 text-left" style="width:218px">
        	<div class="line50">
            	<input type="text" placeholder="填写宽" name="<?php echo 'watermarkwidth'.$num;?>" id="<?php echo 'watermarkwidth'.$num;?>" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $cut_detaile_obj_value['watermarkwidth']?>">
                  ×
                <input type="text" placeholder="填写高" name="<?php echo 'watermarkheight'.$num;?>" id="<?php echo 'watermarkheight'.$num;?>" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $cut_detaile_obj_value['watermarkheight']?>">
            </div>
            <div class="line50">
            	<input type="text" placeholder="X位置" name="<?php echo 'positionx'.$num;?>" id="<?php echo 'positionx'.$num;?>" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $cut_detaile_obj_value['positionx']?>">
                　
                <input type="text" placeholder="Y位置" name="<?php echo 'positiony'.$num;?>" id="<?php echo 'positiony'.$num;?>" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $cut_detaile_obj_value['positiony']?>">
            </div>
        </div>
        <div class="fl w50"><a href="javascript:void(0)" onclick="preview(<?php echo $num;?>)" class="button button-primary button-rounded button-small" style="padding:0 2px; width:63px; margin-top: 40px;">预览水印1</a></div>
      </div>
      <div class="fr w170 h116"><a href="javascript:void(0)" onclick="delcutdetail(<?php echo $cut_detaile_obj_value['id']?>)" class="button button-primary button-rounded button-small" style="padding:0 2px; width:40px; margin-top: 40px;">删除</a></div>
   	 </div>
   	 <?php 
   		 $num++;
     }?>
   
    </form>
  </div>
</div>

<div id="facebox3" style="width: auto; top: 500px; left: 280.5px; margin-top: 0px; opacity: 1; display: none;    ">   
<div class="popup">
	
    <div class="p_main">
   	  <div class=" pad_t30 line28 text-left w380 margin_x">
      	1、水印支持PNG透明图<br/>
2、默认批量优先显示水印1中的图，若需修改可手动调整。<br/>
3、XY坐标为水印整体左上角点为中心点，可在PS工具中获取<br/>
4、水印可用一张大图，针对不同平台去缩放大小

      </div>
   	</div>
  </div>
</div>
<script>
var jishu=<?php echo count($cut_detaile_obj);?>;
var patform_obj=eval(<?php echo json_encode($patform_obj);?>);
var watermark_url='<?php echo $watermark_url;?>';

var watermark_url2='<?php echo $watermark_url2;?>';
var clickcount=0;
var clothes_width=0;
var clothes_height=0;
var clothes_url="/images/IMG_1863.jpg";
power_tabel_num=2;

function water_div_show()
{
	$("#shuiyin2").unbind();
	$("#yulan2").unbind();
	$("#del2").unbind();
	if(watermark_url=="")//水印1不存在
	{
		$("#yulan1").hide();
//		$("#del1").hide();
		$("#watermark_div2").css("opacity","0.6");
	}else{
		$("#yulan1").show();
//		$("#del1").show();
//		$("#del1").bind("click",function(){
//			delwatermark(1);
//		})
		$("#yulan1").bind("click",function(){
			yulan(1);
		})
		$("#watermark_div2").css("opacity","1");
		$("#shuiyin2").bind("click",function(){
			$("#watermark2").click();
		});
//		$("#del2").bind("click",function(){
//			delwatermark(2);
//		})
		$("#yulan2").bind("click",function(){
			yulan(2);
		})
		
	}
	if(watermark_url2=="")//水印2不存在
	{
		$("#yulan2").hide();
//		$("#del2").hide();
	}else{
		
		$("#watermark_div2").css("opacity","1");
		$("#yulan2").show();
//		$("#del2").show();
		$("#shuiyin2").unbind();
		$("#yulan2").unbind();
//		$("#del2").unbind();
		$("#shuiyin2").bind("click",function(){
			$("#watermark2").click();
		});
//		$("#del2").bind("click",function(){
//			delwatermark(2);
//		})
		$("#yulan2").bind("click",function(){
			yulan(2);
		})
	}
}
function yulan(type)
{
	var url="";
	if(type==1)
	{
		url="/"+watermark_url;
	}else{
		url="/"+watermark_url2;
	}
	var html2='<div   style="width:600px;height:500px;margin-top: 0px; opacity: 1;">'
	+'      <div  class="div_table"><div  class="div_table_cell"><img  src="'+url+'"/></div></div>'
	+'</div>';
	var arr2=new Array();
	//arr2['facebox_title']="提示";
	arr2['close_but']=true;
	arr2['div']=html2;
	arr2['width']='600px';
	arr2['height']='500px';
	arr2['fun']=function(obj){
	    obj.find('.ld_close').show();
	    obj.css('background-color',"#fff");
	}
	facebox(arr2);
}

function delwatermark(type)
{
	
	$.ajax({  
	    url: '/erp/delwatermark',  
	    data: { "type":type },  
	    dataType: "json",  
	    type: "POST",   
	    success: function (a) {  
		    if(a.status==1)
		    {	
		    	if(type==1)
		    	{
		    		watermark_url="";
			    }else{
			    	watermark_url2="";
				}
		    	water_div_show();
		    }
	     }  
	}); 
}

$(document).ready(function(){
	
	$("#shuiyin1").bind("click",function(){
		$("#watermark1").click();
	});
	$("#shuiyin2").bind("click",function(){
		$("#watermark2").click();
	});
	$("#watermark1").bind("change",function(){
		$("#fileform").attr("action","/erp/uploadwatermarkimg/type/1");
		$('#fileform').submit();
	});
	$("#watermark2").bind("change",function(){
		$("#fileform").attr("action","/erp/uploadwatermarkimg/type/2");
		$('#fileform').submit();
	});

	var clothes_image = new Image();
	clothes_image.src = clothes_url;
	clothes_image.onload=function(){
		clothes_width=clothes_image.width;//衣服图片原始宽度
		clothes_height=clothes_image.height;//衣服图片原始宽度
	}
	water_div_show();
});
function setsubclothes(a)
{
	var content="水印添加成功";
	
	if(a.status==0)
	{
		content=a.msg;
	}else{
		if(a.type==1)
		{
			watermark_url=a.url;
		}
		if(a.type==2)
		{
			watermark_url2=a.url;
		}
	}
	Popup(content);
	water_div_show();
	
}
function addcutdetailes()
{ 
	jishu++;
	var myDate = new Date();
	var option='<option value="0">选择推送平台</option>';
	for(var i in patform_obj)
	{
		var patform_id=patform_obj[i]["id"];
		var patform_name=patform_obj[i]["name"];
		option+='<option  value="'+patform_id+'">'+patform_name+'</option>'
	}
	var html=' <div class="line40 border5 h116" style="background:#fff; color:#000; overflow:hidden; line-height:116px;">'
 	 +'<div class="fl w156 border_rt00 h116">' 
 	 +'<input type="hidden" name="id'+jishu+'" value="0"/>'
  	+' <select  name="patform'+jishu+'" onChange="checkselect(this)" size="1" style="width:120px; height:30px; line-height:30px; margin:45px 0; font-size:12px;">'
  	+option
  	+'</select></div>'
  	+'<div class="fl w256 border_rt00 h116">'
  	+'<input type="text" placeholder="填写宽" name="width'+jishu+'" id="width'+jishu+'" class="input-xlarge" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
  	+'×'
  	+'<input type="text" placeholder="填写高" name="height'+jishu+'" id="height'+jishu+'" class="input-xlarge" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
  	+'</div>'
  	+'<div class="fl w156 border_rt00 h116 pad_t30">'
  	+'<div class="line28"><input type="radio" name="cutradio'+jishu+'"  checked="checked" style="margin:0; width:15px;" value="0"/>在色里选</div>'
  	+'<div class="line28"><input type="radio" name="cutradio'+jishu+'"   style="margin:0; width:15px;" value="1"/>在款里选</div>'
  	+'</div>'
  	+'<div class="fl w400 border_rt00 h116">'
  	+'	<div class="fl w110 pad_t30">'
  	+'	<div class="line28"><input type="radio" name="waterradio'+jishu+'" id="radio2"  style="margin:0; width:15px;" value="0"/>默认有水印</div>'
  	+'     <div class="line28"><input type="radio" name="waterradio'+jishu+'" checked="checked" id="radio2"  style="margin:0; width:15px;" value="1"/>默认无水印</div>'
  	+' </div>'
  	+' <div class="fl w235 pad_t10 text-left" style="width:218px">'
  	+' 	<div class="line50">'
  	+'     	<input type="text" placeholder="填写宽" name="watermarkwidth'+jishu+'" id="watermarkwidth'+jishu+'" value="" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
  	+'          ×'
  	+'      <input type="text" placeholder="填写高" name="watermarkheight'+jishu+'" id="watermarkheight'+jishu+'" value="" class="input-xlarge" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
	+'     </div>'
	+'     <div class="line50">'
	+'    	<input type="text" placeholder="X位置" class="input-xlarge" name="positionx'+jishu+'" id="positionx'+jishu+'" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
	+'&nbsp;&nbsp;&nbsp;'
    +'      <input type="text" placeholder="Y位置" class="input-xlarge" name="positiony'+jishu+'" id="positiony'+jishu+'" style="width:80px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">'
	+'    </div>'
	+'  </div>'
	+'  <div class="fl w50"><a href="javascript:void(0)"  onclick="preview('+jishu+')" class="button button-primary button-rounded button-small" style="padding:0 2px; width:63px; margin-top: 40px;">预览水印1</a></div>'
	+' </div>'
	+' <div class="fr w170 h116"><a href="javascript:void(0)" onclick="delcutdetail(0)" class="button button-primary button-rounded button-small" style="padding:0 2px; width:40px; margin-top: 40px;">删除</a></div>'
	+' </div>';

	  
	$("#cutform").append(html); 

} 
//提交分类内容
function cutdetailsubmit(){
	var sub=0;
	var patform=$('#cutform').find("select").each(function(){
		
		if($(this).val()==0)
		{
			sub=1;
			Popup('请选择平台');
		
		}
	});
	
	if(	sub==0)
	{
		var patform=$('#cutform').find("select").size();
		$("#patformcount").val(patform);
		$('#cutform').submit();
	}
}
//显示水印说明文字
function showwatermark()
{
	var html=$("#facebox3").html();
	var arr2=new Array();
	arr2['facebox_title']="水印说明";
	arr2['div']=html;
	arr2['width']='728px';
	facebox(arr2);
}

function Popup(content)
{
	var html2='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
		+' <div class="p_main">'
		+'  <div style="width:430px; margin:50px auto;">'
		+'      <div class="line50 pad_s20" id="content">'+content+'</div>'
		+'   </div>'
		+'   </div>'
		+'</div>';
	var arr2=new Array();
	arr2['facebox_title']="提示";
	arr2['div']=html2;
	arr2['width']='728px';
	facebox(arr2);
}
//平台选择提示
function checkselect(obj)
{
	//alert($(obj).val());
	$('#cutform').find("select").each(function(){
		
		if($(obj).val()==$(this).val() && $(obj).attr("name")!=$(this).attr("name"))
		{
			$(obj).val(0);
			Popup("该平台已设置");
		}
		
	});
}
function delcutdetail(id){
	
	if(id>0)
	{
		var html='<div class="popup">'
	    +'<div class="p_main">'
	   	+'<div class="txt_red pad_t50">请谨慎操作删除功能，若误删请联系百一客服</div>'
	    +'<div style="margin:0 auto" class=" w340 pad_t30">'
	    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_submit">删除</a></div>'
	    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_close">取消</a></div>'
	    +'</div>'
	    +'</div>'
	    +'</div>';
		var p_arr=new Array();
		p_arr['div']=html;
		p_arr['facebox_title']='温 馨 提 示';
		p_arr['fun']=function(facebox_obj){
			facebox_obj.find('.facebox_btn_submit').bind('click',function(){
				/***删除事件***/
				delcutdetail2(id);
			});
			facebox_obj.find('.facebox_btn_close').bind('click',function(){
				facebox_obj.find('.ld_close').click();
			});
		};
		facebox(p_arr);
	}
	else{
		delcutdetail2(id);
	}
}
function delcutdetail2(id)
{
	window.location.href="/erp/delcutdetaile?id="+id;
}

function preview(num)
{
	
	if(watermark_url=="")
	{
		Popup("请上传水印");
		return;
	}
	var div_max_width=$(".container_div").width();//弹框的最大宽度
	var div_max_height=$(window).height()-82;//弹框的最大高度
	//输入的信息数组
	var input_array=new Array();
	input_array['width']=$("#width"+num).val();
	input_array['height']=$("#height"+num).val();
	input_array['watermarkwidth']=$("#watermarkwidth"+num).val();
	input_array['watermarkheight']=$("#watermarkheight"+num).val();
	input_array['positionx']=$("#positionx"+num).val();
	input_array['positiony']=$("#positiony"+num).val();
	if(!isNaN(input_array['width']) && !isNaN(input_array['height']) && !isNaN(input_array['watermarkwidth']) && !isNaN(input_array['watermarkheight']) && !isNaN(input_array['positionx']) && !isNaN(input_array['positiony'])){
	}else{
		Popup("请填写数字");
		return;
	}
	
	if(input_array['width']=="" || input_array['height']=="" || input_array['watermarkwidth']=="" || input_array['watermarkheight']=="" || input_array['positionx']=="" || input_array['positiony']=="")
	{
		Popup("请将信息填写完整");
		return;
	}
	
	/*******************弹框显示的宽高****************start******************/
	var image_ratio_width=div_max_width/input_array['width'];//设定的宽和屏幕的宽的比例
	var image_ratio_height=div_max_height/input_array['height'];//设定的高和屏幕的高的比例
	var zoom_ratio=1;//框缩放比例
	if(input_array['height']>div_max_height || input_array['width']>div_max_width)
	{
		zoom_ratio=image_ratio_width<image_ratio_height?image_ratio_width:image_ratio_height;
	}

	var facebox_div_width=zoom_ratio*input_array['width'];//框的宽
	var facebox_div_height=zoom_ratio*input_array['height'];//框的高
	/*******************弹框显示的宽高****************end******************/
	
	/*******************图片显示的宽高****************start******************/
	var clothes_image_ratio_width=facebox_div_width/clothes_width;//图片的宽与框的宽比例
	var clothes_image_ratio_height=facebox_div_height/clothes_height;

	var zoom_img_ratio=1;//衣服图片缩放比例
	//对于图片的实际宽高是否大于缩放后的弹框宽高
	if(clothes_width>facebox_div_width || clothes_height>facebox_div_height)
	{
		zoom_img_ratio=clothes_image_ratio_width<clothes_image_ratio_height?clothes_image_ratio_width:clothes_image_ratio_height;
	}
	var clohtes_img_width=zoom_img_ratio*clothes_width;//图片最后显示的宽
	var clohtes_img_height=zoom_img_ratio*clothes_height;//图片最后显示的高
	/*******************图片显示的宽高*******************end******************/
	
	/*******************水印图片显示的宽高-xy***************start******************/
	
	var watermark_width=zoom_ratio*input_array['watermarkwidth'];//水印图片最后显示的宽
	var watermark_height=zoom_ratio*input_array['watermarkheight'];//水印图片最后显示的高
	var positionx=zoom_ratio*input_array['positionx'];//水印图片最后显示的x
	var positiony=zoom_ratio*input_array['positiony'];//水印图片最后显示的y

	/*******************图片显示的宽高-xy*******************end******************/

	//alert(zoom_ratio+"---"+clohtes_img_width+"---"+clohtes_img_height);

	var url="/"+watermark_url;
	
	var html2='<div   style="width:'+facebox_div_width+'px;height:'+facebox_div_height+'px;margin-top: 0px; opacity: 1;">'
		+'      <div  class="div_table"><div  class="div_table_cell"><img width="'+clohtes_img_width+'" height="'+clohtes_img_height+'" src="/images/IMG_1863.jpg"/></div></div>'
		+'<div style="position: absolute;top:'+positiony+'px;left:'+positionx+'px;" ><img style="width:'+watermark_width+'px; height:'+watermark_height+'px" src="'+url+'"/></div>'
		+'</div>';
	var arr2=new Array();
	//arr2['facebox_title']="提示";
	arr2['close_but']=true;
	arr2['div']=html2;
	arr2['width']=facebox_div_width+'px';
	arr2['height']=facebox_div_height+'px';
	arr2['fun']=function(obj){
        obj.find('.ld_close').show();
        obj.css('background-color',"#fff");
	}
	facebox(arr2);
}


</script>
