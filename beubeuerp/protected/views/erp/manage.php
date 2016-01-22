<title>百一服装ERP管理系统3.0_图片管理</title>
<div class="m_body"  style="padding: 0px; margin: 0px;">
	<div class="fr w50 text-left">
		<a style="cursor: pointer;" onclick="openorclr(this)">收起&nbsp;&uArr;</a>
		<!--&dArr;-->
  </div>
</div>
<div class="m_body border1 user_info_div" style="padding:0; line-height:48px; margin-top: 0px;">
	<div class="fl w80" style="width: 60px;">
		&nbsp;
    </div>
    <div class="fl w214 text-left pad_s20" style="width: 200px;">
   	  <div>账号权限：<?php if(isset($user['type_name'])){echo $user['type_name'];}?></div>
      
    </div>
  <div class="fl w500 text-left pad_s20" style="width: 600px;">
  	<div>
      <div class="fl w170" style="width: 135px;">品牌名：<?php if(isset($user['brandname'])){echo $user['brandname'];}?></div>
	  <div class="fl">有效期：<?php if(isset($user['createtime'])){echo substr($user['createtime'],0,10);}?>  至  <?php if(isset($user['endtime'])){echo substr($user['endtime'],0,10);}?></div>
      <div class="fl w170" style="width: 130px; margin-left: 55px;"><a onclick="getImgsum()" style="cursor: pointer;">查看图片总计</a></div>
      <div class="fl"><a onclick="PromptPop('页面建设中。。。',true)" style="cursor: pointer;">帮助中心</a></div>
  	</div>
    
  </div>
  
  <div class="fl w340 pad_s20" style="width: 310px;line-height:24px;">
  	<div class="text-center">客服电话：021-6563 7300</div>
    <div class="text-center">周一至周五 10:00-17：00  遇节假日，另行通告。</div>
  </div>
</div>


<div class="m_body border1" style="margin-top: 0px;">
    <div class=" w1050 text-center" style="margin:0 auto;width: 1185px;">
     <?php if(Beu_Power::selectAction('/erp/pushlist')){ ?><div style="display: inline-table;"><a onclick="push_div()" class="button button-primary button-rounded button-small" style="padding:0 15px;">图片推送</a></div><?php }?>
      <?php if(Beu_Power::selectAction('/erp/pushcountinfo')){ ?><div class="pad_l10" style="display: inline-table;"><a target="_blank" href="/erp/pushcountinfo" class="button button-primary button-rounded button-small" style="padding:0 15px;">推送统计</a></div><?php }?>
      <?php if(Beu_Power::selectAction('/erp/poster')){ ?><div class="pad_l10" style="display: inline-table;"><a target="_blank" href="/erp/poster" class="button button-primary button-rounded button-small" style="padding:0 15px;">海报图管理</a></div><?php }?>
      <div class="pad_l10" style="display: inline-table;height:30px; line-height:30px; margin:0; padding: 0px 0px 0px 10px;">搜索查询： 
      <select id="order_date_sel" onchange="sel_change_fun(this)" name="" size="1" style="width:120px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">订单创建时间</option>
		<?php foreach($order_date_list as $order_date_value){?>
        <option value="<?php echo $order_date_value?>" <?php if($order_date_value==$order_date){echo 'selected';}?>><?php echo $order_date_value?></option>
		<?php }?>
      </select>&nbsp;
      <select id="Auditing_sel" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;" onchange="sel_change_fun(this)">
        <option value="0">审图状态</option>
        <option value="1" <?php if($Auditing==1){echo 'selected';}?>>已审核且合格</option>
        <option value="2" <?php if($Auditing==2){echo 'selected';}?>>已审核不合格</option>
		<option value="3" <?php if($Auditing==3){echo 'selected';}?>>未审核</option>
      </select>&nbsp;
      <select id="category_sel" onchange="sel_change_fun(this)" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">切换分类</option>
		<?php foreach($category_data as $category_key=>$category_value){?>
		<option value="<?php echo $category_key?>" <?php if($category_key==$category){echo 'selected';}?>><?php echo $category_value?></option>
		<?php }?>
      </select>&nbsp;
	  <select id="patform_sel" onchange="sel_change_fun(this)" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">选图筛选</option>
		<?php foreach($patform as $patform_key=>$patform_value){?>
		<option value="<?php echo $patform_value?>_0" <?php if($patform_value.'_0'==$patform_val){echo 'selected';}?>><?php echo $patform_key?>未加</option>
		<option value="<?php echo $patform_value?>_1" <?php if($patform_value.'_1'==$patform_val){echo 'selected';}?>><?php echo $patform_key?>已加</option>
		<?php }?>
      </select>
      <input id="search_val" value="<?php echo $search_val?>" type="text" placeholder="请填写订单号\款号" class="input-xlarge" style="width:100px; margin: 0 0 0 17px;height:30px; font-size:12px; line-height:30px; padding:0 6px;">&nbsp;
      <select id="search_sel" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">查询类型</option>
        <option value="brandnumber" <?php if($search_type=='brandnumber'){echo 'selected';}?>>款号或款色号</option>
		<option value="order" <?php if($search_type=='order'){echo 'selected';}?>>订单号</option>
      </select>&nbsp;  
      <a onclick="Pagerefresh()" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">查询</a>
      </div>
    </div>
    <div class=" w1150 text-center" style="margin:0 auto; padding-top:20px; clear:both;">
      <!--<div class="fl">下载：<a onclick="downclothesimg()" class="button button-primary button-rounded button-small" style="padding:0 10px;">正面产品图</a></div>-->
      <div style="display: inline-table;">排序：<a onclick="deteSort()" sort="<?php echo $sort;?>" class="button button-primary button-rounded button-small but_sort" style="padding:0 10px;">订单时间<span><?php if($sort=='down'){ echo '&darr;';}else{echo '&uarr;';} ?></span></a></div>
	  <?php if(Beu_Power::selectAction('/erp/delClothesimgBystyle') || Beu_Power::selectAction('/erp/setClothesimgstatus')){ ?>
      <div class="pad_l20" style="display: inline-table;">批量操作：<?php if(Beu_Power::selectAction('/erp/delClothesimgBystyle')){ ?><a onclick="delClothesImgByImgstyle()" class="button button-primary button-rounded button-small" style="padding:0 10px;">删除图片类型</a><?php }?></div>
      <?php if(Beu_Power::selectAction('/erp/setClothesimgstatus')){ ?>
	  <div class="pad_l10" style="display: inline-table;"><a onclick="setImgstatepop()" class="button button-primary button-rounded button-small" style="padding:0 10px;">一键审图</a></div>
	  <?php }?>
	  <?php }?>
	  <?php if(Beu_Power::selectAction('/erp/setcustomcategory') || Beu_Power::selectAction('/erp/setClothesStyle') || Beu_Power::selectAction('/erp/addsuborder')){ ?>
      <div class="pad_l20" style="display: inline-table;">分类操作： 
      <a target="_blank" href="/erp/setcustomcategory" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 10px;">分类名管理</a></div>
	  <?php if(Beu_Power::selectAction('/erp/setClothesStyle')){ ?>
      <div class="pad_l10" style="display: inline-table;"><a onclick="setClothesStyle()" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 10px;">编辑分类</a></div>
	  <?php }?>
	  <?php if(Beu_Power::selectAction('/erp/addsuborder')){ ?>
      <div class="pad_l10" style="display: inline-table;"><a onclick="categorypop()" class="button button-primary button-rounded button-small" style=" width:120px; padding:0 10px;">导表管理分类</a></div>
	  <?php }?>
	  <?php }?>
	  <?php if(Beu_Power::selectAction('/erp/setpatform')){ ?><div class="pad_l10" style="display: inline-table;">
		设置：
		<a target="_blank" class="button button-primary button-rounded button-small" style=" width:120px; padding:0 10px;" href="/erp/setpatform">做图设置</a>
	  </div><?php }?>
    </div>

</div> 
<div style="width:100%;text-align: right;">
<label style="display: inline-block;margin-top: -13px; margin-bottom: 8px; font-size: 12px;"><input type="checkbox" class="Website_template" onclick="Website_template_fun(this)" style="margin-top: -1px;">&nbsp;以款查看</label>
</div>
<div class="m_body2 ">
  <div class="tab_head text-center border2" >
    <div class="fl w94 border_rt"><input onclick="setChecked()" type="checkbox" name="checkbox" class="checkbox_all"  style="margin:0; width:15px;"/>全选/反选</div>
    <div class="fl w140 border_rt">产品缩略图</div>
    <div class="fl w85 border_rt">产品图/张</div>
    <div class="fl w85 border_rt">模特图/张</div>
    <div class="fl w85 border_rt">细节图/张</div>
    <div class="fl w170 border_rt">添加备注</div>
    <div class="fl w90 border_rt"><?php if($Website_template=='SKU'){echo '款号';}else{echo '款色号';}?></div>
    <div class="fl w90 border_rt">分类</div>
    <div class="fl w90 border_rt">订单号</div>
    <div class="fl w140 border_rt">订单创建时间</div>
    <div class="fl w115">操作</div>
  </div>
  <?php if($status==1){?>
  <?php foreach($data as $key=>$value){?>
  <div class="tab_head text-center <?php echo $key%2==0?'border3a':'border2a'?>" >
  	<div class="fl w94 border_rt"><input type="checkbox" name="checkbox" class="checkbox_clothes" clothes_order_id="<?php echo Comm::strencrypt($value['id']);?>" style="margin:0; width:15px;"/>&nbsp;</div>
    <div class="fl w140 border_rt h106"><div class="div_table"><div class="div_table_cell"><img src="<?php if(isset($img_data[$value['id']]) && !empty($img_data[$value['id']]['url'])){echo $domain.$img_data[$value['id']]['url'].'?imageView2/2/w/99/h/99';}else{echo '/images/c_none.jpg';}?>" onerror="imgonerror(this,99,99)"/></div></div></div>
    <div class="fl w85 border_rt"><div class="fl w22 <?php echo isset($img_data[$value['id']]) && $img_data[$value['id']]['pcount']>0?'c_red':'c_green'?> pad_l20"><?php if(!empty($value['pcount'])){ echo '&bull;'; }else{ echo '&nbsp;';}?></div><div class="fl c_num"><?php echo $value['pcount'];?></div></div>
    <div class="fl w85 border_rt"><div class="fl w22 <?php echo isset($img_data[$value['id']]) && $img_data[$value['id']]['mcount']>0?'c_red':'c_green'?> pad_l20"><?php if(!empty($value['mcount'])){ echo '&bull;'; }else{ echo '&nbsp;';}?></div><div class="fl c_num"><?php echo $value['mcount'];?></div></div>
    <div class="fl w85 border_rt"><div class="fl w22 <?php echo isset($img_data[$value['id']]) && $img_data[$value['id']]['dcount']>0?'c_red':'c_green'?> pad_l20"><?php if(!empty($value['dcount'])){ echo '&bull;'; }else{ echo '&nbsp;';}?></div><div class="fl c_num"><?php echo $value['dcount'];?></div></div>
    <div class="fl w170 border_rt h106">
		<div style="cursor: pointer;" class="div_table"<?php if(Beu_Power::selectAction('/erp/wantRemarks')){ ?> onclick="upRemarks(this)"<?php }?>>
			<div class="div_table_cell"><?php if(empty($value['description'])){echo '点击添加备注';}else{echo $value['description'];}?></div>
		</div>
    </div>
    <div class="fl w90 border_rt" style="height: 100%;"><div class="div_table"><div class="div_table_cell"><?php if($Website_template=='SKU'){echo $value['sku'];}else{echo $value['brandnumber'];}?></div></div></div>
    <div class="fl w90 border_rt" style="height: 100%;"><?php if(isset($category_data[$value['brandcategoryid']])){ echo $category_data[$value['brandcategoryid']];} else{echo '<span class="txt_red">未归类</span>';}?></div>
    <div class="fl w90 border_rt" style="height: 100%;"><div class="div_table"><div class="div_table_cell"><?php if(is_array($value['orderid'])){
		$o_name_arr=array();
		foreach($value['orderid'] as $o_value){
			$o_name_arr[]='<span onmousemove="getSkcAll(this,'.$o_value.',\''.$value['sku'].'\')" onmouseout="getSkcAll(null,0,0)">'.$order_name[$o_value].'</span>';
		} 
		echo implode('<br/>',$o_name_arr);
	}else{
		echo '<span onmousemove="getSkcAll(this,'.$value['orderid'].',\''.$value['sku'].'\')" onmouseout="getSkcAll(null,0,0)">'.$order_name[$value['orderid']].'</span>';
	}?></div></div></div>
    <div class="fl w140 border_rt" style="height: 100%;"><div class="div_table"><div class="div_table_cell"><?php if(is_array($value['o_addtime'])){$o_name_arr=array();foreach($value['o_addtime'] as $o_value){$o_name_arr[]=substr($o_value,0,10);} echo implode('<br/>',$o_name_arr);}else{echo substr($value['o_addtime'],0,10);}?></div></div></div>
    <div class="fl w115" style="height: 100%;">
    <div class="div_table"><div class="div_table_cell"><a target="_blank" href="/erp/manageinfo/id/<?php echo Comm::strencrypt($value['id'])?>" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;">审图</a>
	
	<?php if(Beu_Power::selectAction('/erp/delClothesByclothesid')){ ?>
		<br/>
	<a onclick="delClothesByclothesidpop(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">删除</a>
	<?php }?>
	</div></div>
    </div>
  </div>
  <?php }?>
  <?php }else {echo '<div class="tab_head text-center border3a" >查询无果</div>';}?>
  <div class="tab_head text-center border4 clr" >
   <div class="page_1">
   <div style="width:700px; float:right;font-size: 12px;color:#000;text-align: right;padding:0px 24px">
   <?php if(isset($pages)){
	$this->widget ( 'CLinkPager', array (
	'header' => $pages->getItemCount()."条&nbsp;&nbsp;共".$pages->getPageCount()."页&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
	'firstPageLabel' => '首页',    
    'lastPageLabel' => '末页',    
    'prevPageLabel' => '上一页',    
    'nextPageLabel' => '下一页', 
	'pagelist' => true,
	'maxButtonCount' => 5, 'pages' => $pages, "cssFile" => "/css/pager.css" ));
	}
	?>
	</div>
    </div>
  </div>
</div>

<style>
	.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center; padding: 0px 3px;
	}
	.p_main{padding:0}
	/******************标题框 start ************************/
#Promptpop_div{display:none;
position: absolute; 
background-color: rgb(255, 255, 255); 
z-index: 2; 
top: 0px; 
left: 562px; 
font-size: 12px; 
color: rgb(55, 55, 55); 
padding: 5px; 
text-align: left; 
min-width: 100px; 
max-width: 300px; 
width:auto;
overflow:hidden;
border: 1px solid rgb(153, 153, 153); 
height: auto;
}
#Promptpop_div div{
	width:100%;
	height:auto;
}
/******************标题框 end ************************/
</style>
<script>
var domain='<?php echo $domain?>';
var category_data='<?php echo json_encode($category_data);?>';
var cookie_Website_template='SKC';
$(function() {
	var tab_head_len=$('.m_body2 .tab_head').length;
	if($('.m_body2 .tab_head').eq(tab_head_len-2).hasClass('border3a')){
		$('.m_body2 .tab_head').eq(tab_head_len-2).css('border-bottom','1px solid #d9d9d9');
	}
	$('.m_body2 img').bind('load',function(){
		imgonerror(this,99,99,true);
	});
	cookie_Website_template=getCookie('Website_template');
	if(cookie_Website_template=='SKU'){
		$('.Website_template').attr('checked',true);
	}
});
/**
* 用户信息栏展开、收起
**/
function openorclr(obj){
	if($('.user_info_div').is(':hidden')){//隐藏
		$('.user_info_div').show();
		$(obj).html('收起&nbsp;&uArr;');
	}else{//展开
		$('.user_info_div').hide();
		$(obj).html('展开&nbsp;&dArr;');
	}
}
/**
* 导表分类管理
**/
function categorypop(){
	var html='<div class="p_main">'
   	+'<div class="fl w60">&nbsp;</div>'
   	+'<div class="fl w340 text-left">'
    +'<div class="line23 pad_t40">说明：</div>'
	+'<div class="line23">1、请先进入"分类名管理"中添加分类名<br>'
    +'2、请先下载样例表格，按照样例填写内容再导入<br>'
    +'3、若表格中的分类名中没有，导入后不会新建该分类</div>'
    +'</div>'
	+'<div class="fl w280 text-left">'
	+'<form action="/erp/addsuborder?type=style" name="upload_from" id="upload_from" method="post" target="upload_frame" enctype="multipart/form-data">'
    +'<div class="line23 pad_t30">&nbsp;</div>'
    +'<div class="line50">'
    +'<div class="fl"><a onclick="downorderexecl()" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 5px; margin-top:15px">下载样表</a></div>'
    +'<div class="fl pad_l10"><a class="button button-primary button-rounded button-small uplode_but" style=" width:100px; padding:0 5px; margin-top:15px">添加文件</a><input type="file" name="inputExcel" value="" style="display:none"/></div>'
    +'</div>'
	+'<div>'
    +'<div class="fl w110">&nbsp;</div>'
	+'<div class="fl file_value line23" style="width: 170px; height: 23px; overflow: hidden; word-break: break-all; text-overflow: ellipsis; white-space: normal;">未选择文件</div>'
	+'</div>'
    +'<div>'
    +'<div class="fl w110">&nbsp;</div>'
    +'<div class="fl"><a onclick="upload_submit()" class="button button-tiny button-rounded upload_submit" style="padding:0 10px; width:100px; height:34px; line-height:34px; font-size:12px">确定修改</a></div>'
    +'</div>'
	+'</form><iframe name="upload_frame" style="display:none"></iframe>'
	+'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='导表分类管理';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.uplode_but').bind('click',function(){
			facebox_obj.find('input[type=file]').click();
		});
		facebox_obj.find('input[type=file]').bind('change',function(){
			facebox_obj.find('.file_value').html($(this).val());
			facebox_obj.find('.upload_submit').removeClass('button-tiny');
			facebox_obj.find('.upload_submit').removeClass('button-primary');
			facebox_obj.find('.upload_submit').addClass('button-primary');
		});
		/**
		* 表格上传
		**/
		facebox_obj.find('.upload_submit').bind('click',function(){
			var v  = facebox_obj.find("input[name=inputExcel]").val();
			var tt = v.split('.');
			var len = tt.length-1;
			if(tt[len] != 'xls'){
				PromptPop('请检查文件格式，应为.xls格式',true);
			}else{
				facebox_obj.find('#upload_from').submit();
			}
		});
	};
	facebox(p_arr);
}
/**
* 下载Excel样例表
**/
function downorderexecl(){
	window.location.href="/erp/downfile?url=http://erp.beubeu.com/order_category.xls";
}

/**
* 表格上传返回内容
**/
function setsubclothes(a){
	facebox_close_all();
	var div_html='';
	var facebox_title='';
	if(a['status']==0){
		div_html+='<span style="font-size: 16px;font-weight: bold;">'+a['msg']+'</span>';
	}else{
		if(a.no_category.length>0 || a.no_brandnumber.length>0){
			facebox_title='：表格已导入，发现不匹配数据';
			div_html+='<div class="" style="width:45%;display: inline-table;margin: 0 1%;"><span style="line-height: 30px;">不存在的分类</span><div style="height: 170px; overflow-x: hidden; overflow-y: auto; border: 1px solid #d9d9d9;">';
			for(var i in a.no_category){
				div_html+='<span>'+a.no_category[i]+'</span><br/>';
			}
			div_html+='</div></div>'
			
			div_html+='<div class="" style="width:45%;display: inline-table;margin: 0 1%;"><span style="line-height: 30px;">不存在的款号</span><div style="height: 170px; overflow-x: hidden; overflow-y: auto; border: 1px solid #d9d9d9;">';
			for(var i in a.no_brandnumber){
				div_html+='<span>'+a.no_brandnumber[i]+'</span><br/>';
			}
			div_html+='</div></div>'
    
		}else{
			div_html+='<span style="font-size: 16px;font-weight: bold;">导入数据成功</span>';
		}
	}
	
	var html='<div class="popup">'
    +'<div class="p_main"><div class="div_table"><div class="div_table_cell">'
   	+div_html
    +'</div></div></div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='提示'+facebox_title;
	p_arr['fun']=function(facebox_obj){
	};
	facebox(p_arr);
}
/**
* 一键合格
**/
function setImgstatepop(){
	if(!PromptPop()){
		return;
	}
	var html='<div class="popup">'
    +'<div class="p_main">'
   	+'<div class="txt_red pad_t50">"一键审图"可以将全部待审核状态设为合格，不合格状态不会合格。</div>'
    +'<div style="margin:0 auto" class=" w340 pad_t30">'
    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_submit">确定</a></div>'
    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_close">取消</a></div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='一键审图';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***合格事件***/
			$.getJSON('/erp/setClothesimgstatus?clothes_order_id='+getClothesnum(),function(a){
				window.location.reload();
			});
		});
		facebox_obj.find('.facebox_btn_close').bind('click',function(){
			facebox_obj.find('.ld_close').click();
		});
	};
	facebox(p_arr);
}

/**
* 设置单品勾选项
**/
function setChecked(obj){
	var obj=obj?obj:false;
	if(obj){//设置单个单品勾选
		$('.m_body2 .checkbox_clothes').attr('checked',false);
		$('.checkbox_all').attr('checked',false);
		$(obj).closest('.tab_head').find('.checkbox_clothes').attr('checked',true);
		return;
	}
	if($('.checkbox_all').attr('checked')){
		$('.m_body2 .checkbox_clothes').attr('checked',true);
	}else{
		$('.m_body2 .checkbox_clothes').attr('checked',false);
	}
}

/**
* 获取勾选单品数量
**/
function getClothesnum(){
	var clothes_order_id_arr=new Array();
	//获取勾选单品数量
	$('.m_body2 .checkbox_clothes:checked').map(function(){
		clothes_order_id_arr.push($(this).attr('clothes_order_id'));
	});
	
	return clothes_order_id_arr.join(',');
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

/**
* 修改衣服分类名
**/
function setClothesStyle(){  
	if(!PromptPop()){
		return;
	}
	var category=eval('('+category_data+')');
	var category_html='';
	for(var i in category){
		category_html+='<option value="'+i+'">'+category[i]+'</option>';
	}
	var html='<div class="popup">'
    +'<div class="p_main">'
    +'<div style="margin:0 auto;padding-top: 103px;" class=" w340 pad_t50">'
    +'<div class="fl w110">'
    +'<select style="width:100px; height:30px; margin:0; font-size:12px;" size="1" name="">'
    +'<option value="0">选择分类</option>'
    +category_html
    +'</select>'
    +'</div>'
    +'<div class="fl w110">'
    +'<a style=" width:100px; padding:0 15px;" class="button button-primary button-rounded button-small facebox_btn_submit">确定保存</a>'
    +'</div>'
    +'<div class="fl w115 line28">'
    +'<a href="/erp/setcustomcategory">分类名管理</a></div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='修改分类';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***修改分类事件***/
			var select_v=facebox_obj.find('select').val();
			if(select_v!='0'){
				$.getJSON('/erp/setClothesStyle?clothes_order_id='+getClothesnum()+'&Style='+select_v,function(a){
					window.location.reload();
				});
			}
		});
	};
	facebox(p_arr);
}

/**
* 图片统计
**/
function getImgsum(){  
	$.getJSON('/erp/getImgsum',function(a){
		var pcount=0;
		var mcount=0;
		var dcount=0;
		if(a.status==1){
			pcount=a.data['pcount'];
			mcount=a.data['mcount'];
			dcount=a.data['dcount'];
		}
		var html='<div class="popup">'
		+'<div class="p_main">'
		+'<div style="margin:0 auto; line-height:180px;" class=" w340 pad_t30">'
		+'图片总计：产品图 '+pcount+' 张，模特图 '+mcount+' 张，细节图 '+dcount+' 张'
		+'</div>'
		+'</div>'
		+'</div>';
		var p_arr=new Array();
		p_arr['div']=html;
		p_arr['facebox_title']='图片总计';
		facebox(p_arr);
	});
	
}

/**
* 按图片类型删除所有内容
**/
function delClothesImgByImgstyle(){  
	if(!PromptPop()){
		return;
	}
	var html='<div class="popup">'
    +'<div class="p_main">'
   	+'<div class="w400 pad_t50 margin_x">'
    +'<div class="fl">选择要删除的类型：</div>'
    +'<div class="fl pad_l40">'
    +'<input type="checkbox" style="margin:0; width:10px;" id="checkbox" name="checkbox" value="pcount">&nbsp;产品图</div>'
    +'<div class="fl pad_l40">'
    +'<input type="checkbox" style="margin:0; width:10px; padding-left:30px;" id="checkbox" name="checkbox" value="mcount">&nbsp;模特图</div>'
    +'<div class="fl pad_l40">'
    +'<input type="checkbox" style="margin:0; width:10px; padding-left:30px;" id="checkbox" name="checkbox" value="dcount">&nbsp;细节图</div>'
    +'</div>'
    +'<div style="margin:0 auto" class=" w340 pad_t30">'
    +'<div style="margin:0 auto" class="txt_red w340 pad_t30">请谨慎操作删除功能</div>'
    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_submit">删除</a></div>'
    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_close">取消</a></div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='按图片类型删除所有内容';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***删除事件***/
			var check_arr=new Array();
			facebox_obj.find('input[type=checkbox]:checked').map(function(){
				check_arr.push($(this).val());
			});
			var str=check_arr.join(',');
			if(str==''){
				return;
			}
			$.getJSON('/erp/delClothesimgBystyle?clothes_order_id='+getClothesnum()+'&style='+str,function(a){
				window.location.reload();
			});
		});
		facebox_obj.find('.facebox_btn_close').bind('click',function(){
			facebox_obj.find('.ld_close').click();
		});
	};
	facebox(p_arr);
}

/**
* 按单品删除所有内容
**/
function delClothesByclothesidpop(obj){
	setChecked(obj);
	if(!PromptPop()){
		return;
	}
	var html='<div class="popup">'
    +'<div class="p_main">'
   	+'<div class="txt_red pad_t50">清除此单品下的所有内容，请谨慎操作删除功能，若误删请联系百一客服</div>'
    +'<div style="margin:0 auto" class=" w340 pad_t30">'
    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_submit">删除</a></div>'
    +'<div class="fl w170 text-center"><a style=" width:100px; padding:0 5px; margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_close">取消</a></div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='按单品删除所有内容';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***删除事件***/
			ajax_delClothesByclothesid();
		});
		facebox_obj.find('.facebox_btn_close').bind('click',function(){
			facebox_obj.find('.ld_close').click();
		});
	};
	facebox(p_arr);
}
/**
* 按单品删除所有内容
**/
function ajax_delClothesByclothesid(){
	var clothes_str=getClothesnum();
	$.getJSON('/erp/delClothesByclothesid?clothes_order_id='+clothes_str,function(a){
		if(a.status==1){
			window.location.reload();
		}else{
			PromptPop('删除失败！',true);
		}
	});
}
/**
* 保存备注
**/
function wantRemarks(obj){
	var con_str=$(obj).closest('.border_rt').find('textarea').val();
	var con_str2=$(obj).closest('.border_rt').find('label').html();
	if(con_str!=con_str2){
		ajax_wantRemarks(obj);
	}
	if(con_str==''){
		con_str='点击添加备注';
	}
	$(obj).closest('.border_rt').html('<div style="cursor: pointer;" class="div_table" onclick="upRemarks(this)"><div class="div_table_cell">'+con_str+'</div></div>');
}
/**
* 保存备注
**/
function ajax_wantRemarks(obj){
	var con_str=encodeURIComponent($(obj).closest('.border_rt').find('textarea').val());
	var clothes_order_id=$(obj).closest('.tab_head').find('input[type=checkbox]').attr('clothes_order_id');
	$.getJSON('/erp/wantRemarks?clothes_order_id='+clothes_order_id+'&description='+con_str,function(a){
		if(a.status==1){
		}else{
			PromptPop('修改失败！',true);
		}
	});
}
/**
* 修改备注
**/
function upRemarks(obj){
	var con_str=$(obj).find('.div_table_cell').html();
	if(con_str=='点击添加备注'){
		con_str='';
	}
	var html='<div>'
    +'<textarea placeholder="备注最多放20个汉字" onkeyup="this.value=this.value.substring(0, 20)" onkeydown="this.value=this.value.substring(0, 20)" onchange="this.value=this.value.substring(0, 20)" maxlength="20" style="height:40px; width:130px; padding:5px;margin:5px;font-size:12px; resize:none;" rows="2" cols="20">'+con_str+'</textarea>'
	+'<label style="display: none;">'+con_str+'</label>'
    +'</div>'
    +'<div class="w156 text-right"><a class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;" onclick="wantRemarks(this)">保存</a></div>';
	$(obj).closest('.border_rt').html(html);
}

/**
* 下载图片
**/
function downclothesimg(){
	if(!PromptPop()){
		return;
	}
	$.getJSON('/erp/downclothesimg?clothes_order_id='+getClothesnum()+'&domain='+domain,function(a){
		if(a.status==1){
			window.open(a.out_url);
		}else{
			PromptPop('下载图片失败！',true);
		}
	});
}
/**
* 时间排序
**/
function deteSort(){
	var sort=$('.but_sort').attr('sort');
	if(sort=='down'){
		$('.but_sort').attr('sort','up').find('span').html('&uarr;');
	}else{
		$('.but_sort').attr('sort','down').find('span').html('&darr;');
	}
	Pagerefresh();
}
/**
* 搜索查询
**/
function Pagerefresh(){
	var url='/erp/manage';
	var sort=$('.but_sort').attr('sort');
	if(sort!=''){
		url+='/sort/'+sort;
	}
	var Auditing=$('#Auditing_sel').val();
	if(Auditing!=0){
		url+='/Auditing/'+Auditing;
	}
	var order_date=$('#order_date_sel').val();
	if(order_date!=0){
		url+='/order_date/'+order_date;
	}
	var category=$('#category_sel').val();
	if(category!=0){
		url+='/category/'+category;
	}
	var search_type=$('#search_sel').val();
	if(search_type!=0){
		url+='/search_type/'+search_type;
	}
	var search_val=$('#search_val').val();
	if(search_val!=0){
		url+='/search_val/'+search_val;
	}
	var patform_sel=$('#patform_sel').val();
	if(patform_sel!=0){
		url+='/patform_val/'+patform_sel;
	}
	window.location.href=url;
}
/**
* 查询条件还原
**/
function sel_change_fun(obj){
	var obj_id=$(obj).attr('id');
	var obj_val=$(obj).val();
	$('#Auditing_sel').find("option").eq(0).attr("selected",true);
	$('#order_date_sel').find("option").eq(0).attr("selected",true);
	$('#category_sel').find("option").eq(0).attr("selected",true);
	$('#patform_sel').find("option").eq(0).attr("selected",true);
	$(obj).find("option[value="+obj_val+"]").attr("selected",true);
	Pagerefresh();
}


/**
* 图片错误方法
**/
function imgonerror(obj,width,height,is_bool){
	var is_bool=is_bool?is_bool:false;
	$(obj).unbind('load');
	if(is_bool && $(obj).attr('src')!='/images/c_none.jpg'){
		return;
	}
	if (151 / 268 >= width / height) {
		if (151 > width) {
			height = (268 * width) / 151;
		}else{
			width = 151;
			height = 268;
		}
	}else {
		if (268 > height) {
			width = (151 * height) / 268;
		} else {
			width = 151;
			height = 268;
		}
    }
	$(obj).removeAttr('onerror').css({'width':width+'px','height':height+'px'}).attr('src','/images/c_none.jpg');
}
/**
* 网站模版切换
**/
function Website_template_fun(obj){
	if($(obj).attr('checked')){
		setCookie('Website_template','SKU',0.042);
	}else{
		setCookie('Website_template','SKC',0.042);
	}
	window.location.reload();
}
function Popup(content)
{
	var html2='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
		+' <div class="p_main">'
		+'  <div  class="div_table"><div  class="div_table_cell">'
		+'      <div class="line50 pad_s20" id="content">'+content+'</div>'
		+'   </div></div>'
		+'   </div>'
		+'</div>';
	var arr2=new Array();
	arr2['facebox_title']="提示";
	arr2['div']=html2;
	arr2['width']='728px';
	facebox(arr2);
}
//图片推送
function push_div()
{
	var clothesid_string=getClothesnum();
	
	if(clothesid_string=="")
	{
		Popup("请选择要推送的衣服");
		return;
	}
	
	var html2='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
		+' <div class="p_main">'
		+'  <div  class="div_table"><div  class="div_table_cell">'
		+'      <a href="/erp/pushlist" target="_blank" onclick="push_image(this,1)" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">推送到官网</a></br>'
		+'   </div></div>'
		+'   </div>'
		+'</div>';
	var arr2=new Array();
	arr2['facebox_title']="图片推送";
	arr2['div']=html2;
	arr2['width']='728px';
	facebox(arr2);
}

function push_image(obj,patformid)
{
	var clothesid_string=getClothesnum();
	var url="/erp/pushlist?clothes_string="+clothesid_string+"&patformid="+patformid;
	$(obj).attr("href",url);
	 
}
function getSkcAll(obj,orderid,sku_v){
	if(cookie_Website_template!='SKU'){
		return;
	}
	if(obj==null){
		Prompt_tips_pop(null,'Prompt_Mask','');
		return;
	}
	if($('#Promptpop_div').length>0 && $('#Promptpop_div').attr('Prompt_type')==sku_v){
		return;
	}
	Prompt_tips_pop(obj,sku_v,'<img src="/images/482.GIF" width="30" height="30"/>');
	getSkcAll_fun(obj,orderid,sku_v)
}

/**
* 提示框 文字设置
**/
function getSkcAll_fun(obj,orderid,sku_v){
	$.getJSON('/erp/getSkcAll?sku='+sku_v+'&orderid='+orderid,function(a){
		if(a.status==1){
			var Promptpop_hei=$('#Promptpop_div').height();
			$('#Promptpop_div>div').html('款色号<br/>'+a.data.join('<br/>'));
			var Promptpop_end=$('#Promptpop_div').height();
			if(Promptpop_end>Promptpop_hei){
				var top=parseInt($('#Promptpop_div').css('top'));
				$('#Promptpop_div').css('top',top-(Promptpop_end-Promptpop_hei)/2);
			}
		}
	});
}
</script>