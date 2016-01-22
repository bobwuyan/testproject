
<title>百一服装ERP管理系统3.0_分类名管理</title>


<script>
power_tabel_num=2;
var jishu=<?php echo count($brandcategory_obj);?>;
var submit=<?php echo $submit_status;?>;
var mes='<?php echo $mes;?>';
$(document).ready(function(){
	var content=mes;
	if(content!="")
	{
		var html='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
			+' <div class="p_main">'
			+'  <div style="width:430px; margin:50px auto;">'
			+'      <div class="line50 pad_s20" id="content">'+content+'</div>'
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
	}
});

</script>
<div class="m_body border1">
  <div class="add_fav auto_height">
    <div class=" auto_height" style="overflow:hidden">
      <div class="fl w110 text-left">
        <a href="javascript:void(0)" class="button button-primary button-rounded button-small" onclick="addcustomcategory()" style="padding:0 10px; width:100px;">+添加分类</a>
      </div>
      <div class="fl w140 text-left">
       <?php if(Beu_Power::selectAction('/erp/setcustomcategory')){ ?>
        <a href="javascript:void(0)" class="button button-primary button-rounded button-small" style="padding:0 10px; width:80px;" onclick="categorysubmit()">保存</a>
       <?php }?>
      </div>
    </div>
    <div class=" margin_t20 line40" style=" height:40px; background:#000; color:#FFF; overflow:hidden">
   	  <div class="fl w310">分类名</div>
      <div class="fl w560">创建时间</div>
      <div class="fr w280">操作</div>
    </div>
    <div id="customdiv">
    	<form enctype="multipart/form-data" method="post" action="/erp/setcustomcategory"  name="fileform" id="fileform">
    	<?php 
    	if(count($brandcategory_obj)>0)
    	{
    		$num=1;
    		foreach($brandcategory_obj as $value)
    		{
    			?>
    			
    			 <div class="line40 border6 margin_t20"  style=" height:40px; background:#fff; color:#000; overflow:hidden">
			   	  <div class="fl w310">
			   	  	  <input type="hidden" name="<?php echo 'category_id_'.$num;?>" value="<?php echo $value['id']?>"/>
			   		  <input type="text" class="input-xlarge" name="<?php echo 'category_name_'.$num;?>" onblur="keypd(this)" style="width:150px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="<?php echo $value['name']?>" placeholder="请填写分类名">
			   	  </div>
			      <div class="fl w560"><?php echo $value['addtime'];?></div>
			      <div class="fr w280">
			        <?php if(Beu_Power::selectAction('/erp/deletecategory')){ ?>
			      <a href="#/" class="button button-primary button-rounded button-small" onclick="deletecategory(<?php echo $value['id']?>,this)" style="padding:0 10px;">删除</a>
			     <?php }?>
			      </div>
			    </div>
    			<?php
    			$num++;
    		}
    	}
    	?>
    	</form>
	  </div>
  </div>
</div>




<script>
//添加分类div
function addcustomcategory()
{ 
	jishu++;
	var myDate = new Date();
	var html='<div class="line40 border6 margin_t20"   style=" height:40px; background:#fff; color:#000; overflow:hidden">'
	  +'<div class="fl w310">'
	  +'<input type="hidden" name="category_id_'+jishu+'" value="0"/>'
	  +' <input type="text" name="category_name_'+jishu+'" onblur="keypd(this)" class="input-xlarge" style="width:150px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" value="" placeholder="请填写分类名"></div>'
	  +'<div class="fl w560"></div>'
	  +'<div class="fr w280" ><a href="#/" class="button button-primary button-rounded button-small" style="padding:0 10px;" onclick="deletecategory(0,this)">删除</a></div>'
	  +'</div>';
	$("#fileform").append(html); 

} 
//提交分类内容
function categorysubmit(){
	$('#fileform').submit();
}

function keypd(obj)
{
	
    var value=$(obj).val();
    if($(obj).val().length>10)
	{
		alert('自定义分类名不得超过10个汉字');
	}
	
}
var brandcategory=eval(<?php echo json_encode($brandcategory_obj);?>);
//删除分类
function deletecategory(id,obj)
{
	var option='<option value="-1">选择分类</option>';
	option+='<option value="0">未分类</option>';
	for(var i in brandcategory)
	{
		var category_id=brandcategory[i]["id"];
		var category_name=brandcategory[i]["name"];
		if(id!=category_id){
			option+='<option  value="'+category_id+'">'+category_name+'</option>'
		}
	}
	

	if(id>0)
	{
		var countclothes=0;
		$.getJSON('/erp/selectclothescount?categoryid='+id,function(b){
			
			if(b.status==1){
				countclothes=b.count;
				var html= '<div class="popup">'
				+'<div class="p_main">'
				+' <div style="width:430px; margin:35px auto;">'
				+'  <div class="line28 ">分类下共有'+countclothes+'款</div>'
				+'  <div class="line28 ">*删除本分类请先将原分类下内容归入其他分类下</div>'
				+'  <div class="line28 ">*选择“未归类”图片会变成没有分类的图片</div>'
				+'  <div class="line50">'
				+'    <select  name="" id="categoryid" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">'
				+option
				+'    </select>'
			    +'		<a href="javascript:void(0)" id="savecategory" class="button button-tiny" style="border-radius:4px; padding:0 10px; width:80px; height:34px; line-height:34px; font-size:12px">确定</a>'
		        +'  	</div>'
		        +'			</div>'
		        +'		</div>'
		        +'</div>';
				var arr=new Array();
				arr['facebox_title']="提示";
				arr['div']=html;
				arr['width']='728px';
				arr['fun']=function(){
					$("#savecategory").bind("click", function() {
						//getorderlog(orderid,dateobj,this.options[this.options.selectedIndex].value);
						var categoryid=$("#categoryid").val();
						if(categoryid==-1)
						{
							
							Popup('请选择分类');
							return;
						}
						$.getJSON('/erp/deletecategory?id='+id+'&categoryid='+categoryid,function(c){
								var content='删除成功';
								if(c.status==1){
									$(obj).closest('div[class="line40 border6 margin_t20"]').remove();
									for(var i in brandcategory)
									{
										if(brandcategory[i]["id"]==id)
										{
											brandcategory.splice(i,1);
											break;
										}
									}
									
								}else{
									content='删除失败';
								}
								
								Popup(content);
							});


						
						});
					}
				facebox(arr);
			}
		});
		
	}else{
		$(obj).closest('div[class="line40 border6 margin_t20"]').remove();
	}
	//
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
</script>

