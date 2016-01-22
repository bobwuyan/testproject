<title>百一服装ERP管理系统3.0_图片管理_海报图管理</title>
<style>
ul.yiiPager li{float:none;margin:0}
.cpics_list {
    width: 1200px;
}
.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center; padding: 0px;
	}
.m_body4{height:auto;min-height:auto}

.m_body3 {
    margin: 0 auto 10px;
}
.cpic_t1, cpic_admin {
    height: 40px;
    line-height: 40px;
}
.m_cont_none{
	width: 1200px; line-height: 100px; font-weight: bold; color: #999;
}
.cpic_img {
    height: 268px;
	overflow: hidden;
}
.error{
	background-color: #fff4f4;
	border: 1px solid #d58a8a;
	border-radius: 3px;
	color: #d58a8a;
	display: inline-block;
	padding: 5px;
	line-height: 1.42857;
	width:auto;
	min-width:100px;
	position: absolute;
	margin-top: 10px;
	left:445px;
}
.text_overflower{
	overflow: hidden; display: inline-block; width:auto;max-width: 148px; text-overflow: ellipsis;word-break: keep-all;white-space: nowrap;
}
.p_main{padding:0}
</style>
<div class="m_body border1">
  <div class="w950 text-center" style="margin:0 auto; clear:both;">
    <div class="fl pad_l20">排序：<a onclick="dirnameSort()" sort="<?php echo $sort;?>" class="button button-primary button-rounded button-small but_sort" style="padding:0 10px;">文件夹排序<span><?php if($sort=='bottom'){ echo '&darr;';}else{echo '&uarr;';} ?></span></a></div>
      <div class="fl pad_l20">批量操作：<a onclick="delPosterdir()" class="button button-primary button-rounded button-small" style="padding:0 10px;">删除文件夹</a></div>
      <div class="fl pad_l10"><a onclick="movePosterimg()" class="button button-primary button-rounded button-small" style="padding:0 10px;">移动图片</a></div>
      <div class="fl pad_l20" >搜索查询： 
      	<select id="start_date" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">开始时间</option>
		<?php foreach($date_arr as $value){?>
		<option value="<?php echo $value;?>" <?php if($start_date==$value){ echo 'selected';}?>><?php echo $value;?></option>
		<?php }?>
      </select> 
      </div>
      <div class="fl pad_l10" >
      	<select id="end_date" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">结束时间</option>
        <?php foreach($date_arr as $value){?>
		<option value="<?php echo $value;?>" <?php if($end_date==$value){ echo 'selected';}?>><?php echo $value;?></option>
		<?php }?>
      </select> 
      </div>
      <div class="fl pad_l10" >
      	<select id="dirname" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">文件夹名</option>
        <?php foreach($dirname_arr as $value){?>
		<option value="<?php echo $value;?>" <?php if($dirname==$value){ echo 'selected';}?>><?php echo $value;?></option>
		<?php }?>
      </select> 
      </div>
      <div class="fl pad_l10" >
      <a onclick="Pagerefresh()" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">查询</a></div>
  </div>

</div>   
 <?php foreach($dirname_arr as $value){
	 ?>
<div class="m_body3">
  <div class="fl w1150 text-center t_14b"><?php echo $value;?></div>
  <div class="fr w50 text-left"><a onclick="openorclr(this)" style="cursor: pointer;"><span class="txt_white">收起&nbsp;&uArr;<!--&dArr;--></span></a></div>
</div>
<div class="m_body4 cpics_list">
	<ul class="cp_ul">
		<?php if(isset($data[$value])){foreach($data[$value]['data'] as $data_value){
			?>
		
			<li poster_id="<?php echo $data_value['id'];?>">
       	  <div class="cpic_t1">
          	<input type="checkbox" name="checkbox" id="checkbox"  style="margin:0; width:15px;"/>
          	<a onclick="delPosterimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:5px">删除</a>
            <a onclick="downimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:5px">下载</a>
          </div>
          <div class="cpic_img border1">
            <div class="div_table" onclick="imgbig(this)" style="cursor: pointer;"><div class="div_table_cell"><img src="<?php echo $domain.$data_value['url']?>?imageView2/2/w/268/h/268" onerror="imgonerror(this,268,268)"/></div></div>
             
          </div>
          <div class="cpic_admin">
          	<div class="fl w22">
            &nbsp;</div>
            <div class="fl w214 line20 pad_t10">
              <div><span class="text_overflower" style="width:50px">文件名：</span><span class="text_overflower"><?php echo $data_value['name'];?></span></div>
              <div>上传日期：<?php echo $data_value['addtime'];?></div>
            </div>
          </div>
        </li>
		<?php }}else{?>
		<div class="m_cont_none" style="">没有上传图片</div>
		<?php }?>
	 <div class="clr">&nbsp;</div>
		</ul>
		<?php if(isset($data[$value])){?>
	   <div class="page_1" dirname="<?php echo $value;?>">
	   <div style="width:700px; float:right;font-size: 12px;color:#000;text-align: right;padding:0px 24px">
	   <?php if(isset($data[$value]['pages'])){
		$this->widget ( 'CLinkPager', array (
		'header' => $data[$value]['pages']->getItemCount()."条&nbsp;&nbsp;共".$data[$value]['pages']->getPageCount()."页&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
		'firstPageLabel' => '首页',    
		'lastPageLabel' => '末页',    
		'prevPageLabel' => '上一页',    
		'nextPageLabel' => '下一页', 
		'pagelist' => true,
		'maxButtonCount' => 5, 'pages' => $data[$value]['pages'], "cssFile" => "/css/pager.css" ));
		}
		?>
		</div>
		</div>
		<?php }?>
</div>
<?php }
if(count($dirname_arr)==0) {
	echo '<div style="border: 1px solid #d9d9d9; line-height: 100px;">查询无果</div>';
}?>
<script>
var domain='<?php echo $domain?>';
$(function() {
	page_url();
	$('.m_cont_none:visible').map(function(){
		$(this).closest('.cpics_list').prev('.m_body3').remove();
		$(this).closest('.cpics_list').remove();
	})
});
/**
* 重置翻页地址
**/
function page_url(obj){
	var obj=obj?obj:$('.yiiPager');
	$(obj).find('li').map(function(){
		var dirname=$(this).closest('.page_1').attr('dirname');
		$(this).find('a').attr('href_url',$(this).find('a').attr('href'));
		$(this).find('a').attr('href','javascript:void(0)');
		if(!$(this).hasClass('hidden') && !$(this).hasClass('selected')){
			$(this).find('a').click(function(){
				getImgByPage(this,$(this).attr('href_url')+'/dirname/'+dirname);
			});
		}
	});
	$(obj).find('select').map(function(){
		var dirname=$(this).closest('.page_1').attr('dirname');
		$(this).attr('onchange','');
		$(this).change(function(){
			var select_v=$(this).val();
			getImgByPage(this,'/erp/poster/page/'+select_v+'/dirname/'+dirname);
		});
	});
	$('.cpics_list').map(function(){
		var li_num=0;
		$(this).find('ul.cp_ul li').map(function(){
			if(li_num%4==0){
				$(this).css('margin-left',0);
			}else if(li_num%3==0){
				$(this).css('margin-right',0);
			}
			li_num++;
		});
	})
}
/**
* 翻页
**/
function getImgByPage(obj,url){
	url=url.replace("/poster/","/getposterImgByPage/");
	url=url.replace("/getposterPages/","/getposterImgByPage/");
	$.getJSON(url,function(a){
		var page_1=$(obj).closest('.page_1');
		var cpics_list=$(obj).closest('.cpics_list');
		$.post('/erp/getposterPages/page/'+a.page,{'page_sum':a.page_sum},function(pages_a){
			$(obj).closest('.page_1').find('div').html(pages_a);
			page_url(page_1.find('.yiiPager'));
		});
		cpics_list.find('.cp_ul').html('');
		for(var i in a.data){
			var html='<li poster_id="'+a.data[i]['id']+'">'
				+'<div class="cpic_t1">'
				+'<input type="checkbox" style="margin:0; width:15px;" id="checkbox" name="checkbox">&nbsp;'
				+'<a style=" width:50px; padding:0 5px;margin-top:5px" class="button button-primary button-rounded button-small" onclick="delPosterimg(this)">删除</a>&nbsp;'
				+'<a style=" width:50px; padding:0 5px;margin-top:5px" class="button button-primary button-rounded button-small" onclick="downimg(this)">下载</a>'
				+'</div>'
				+'<div class="cpic_img border1">'
				+'<div class="div_table" onclick="imgbig(this)" style="cursor: pointer;"><div class="div_table_cell"><img src="'+domain+a.data[i]['url']+'?imageView2/2/w/268/h/268" onerror="imgonerror(this,268,268)"></div></div>'
				+'</div>'
				+'<div class="cpic_admin">'
				+'<div class="fl w22">'
				+'&nbsp;</div>'
				+'<div class="fl w214 line20 pad_t10">'
				+'<div><span class="text_overflower" style="width:50px">文件名：</span><span class="text_overflower">'+a.data[i]['name']+'</span></div>'
				+'<div>上传日期：'+a.data[i]['addtime']+'</div>'
				+'</div>'
				+'</div>'
				+'</li>';
				cpics_list.find('.cp_ul').append(html);
		}
		cpics_list.find('.cp_ul').append('<div class="clr"> </div>');
	});
}

/**
* 用户信息栏展开、收起
**/
function openorclr(obj){
	if($(obj).closest('.m_body3').next('.cpics_list').is(':hidden')){//隐藏
		$(obj).closest('.m_body3').next('.cpics_list').show();
		$(obj).find('span').html('收起&nbsp;&uArr;');
	}else{//展开
		$(obj).closest('.m_body3').next('.cpics_list').hide();
		$(obj).find('span').html('展开&nbsp;&dArr;');
	}
}
/**
* 删除图片
**/
function delPosterimg(obj){
	var html='<div class="popup">'
	+'<div class="p_main">'
   	+'<div class=" line100 text-center">是否要删除此图片</div>'
   	+'<div style="margin:0 auto" class=" w235">'
	+'<a style=" width:100px; padding:0 15px;margin-top:15px" class="button button-primary button-rounded button-small facebox_btn_submit">确定</a>'
	+'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='删除海报图';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***删除图片事件***/
			$.getJSON('/erp/delPosterimg?poster_id='+$(obj).closest('li').attr('poster_id'),function(a){
				window.location.reload();
			});
		});
	};
	facebox(p_arr);
}

/**
* 删除文件夹
**/
function delPosterdir(){
	var dirname_arr='<?php echo json_encode($dirname_arr)?>';
	dirname_arr=eval('('+dirname_arr+')');
	var sel_html='';
	for(var i in dirname_arr){
		sel_html+='<option value="'+dirname_arr[i]+'">'+dirname_arr[i]+'</option>';
	}
	var html='<div class="popup">'
	+'<div class="p_main">'
   	+'<div class=" line100 text-center">请选择需要删除的文件夹，并再填写一次文件夹名</div>'
   	+'<div style="margin:0 auto" class=" w380">'
    +'<div class="fl w110 text-center">'
    +'<select style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;" size="1" name="">'
    +'<option value="0">文件夹名</option>'
    +sel_html
    +'</select>'
    +'</div>'
    +'<div class="fl w110 text-center">'
    +'<input type="text" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;" class="input-xlarge file_name" placeholder="文件名">'
    +'</div>'
    +'<div class="fl w110 text-center pad_l10">'
    +'<a style=" width:100px; padding:0 15px; height:34px; line-height:34px; font-size:12px" class="button button-tiny button-rounded button-small facebox_btn_submit">删除</a>'
    +'</div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='删除文件夹';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			var sel_dir=facebox_obj.find('select').val();
			var new_dir=strtrim(facebox_obj.find('input').val());
			if(sel_dir==0 || strempty(new_dir) || sel_dir==new_dir){
				return;
			}
			/***删除文件夹事件***/
			$.getJSON('/erp/delPosterdir?del_dir='+sel_dir+'&new_dir='+new_dir,function(a){
				window.location.reload();
			});
		});
		facebox_obj.find('.file_name').change(function(){
			facebox_obj.find('.error').hide();
			var file_name=strtrim($(this).val());
			if(strempty(file_name)){
				facebox_obj.find('.facebox_btn_submit').removeClass('button-primary');
				facebox_obj.find('.facebox_btn_submit').addClass('button-tiny');
			}else{
				facebox_obj.find('.facebox_btn_submit').removeClass('button-tiny');
				facebox_obj.find('.facebox_btn_submit').addClass('button-primary');
			}
		});
		facebox_obj.find('.file_name').keyup(function(){
			var file_name=strtrim($(this).val());
			if(strempty(file_name)){
				facebox_obj.find('.facebox_btn_submit').removeClass('button-primary');
				facebox_obj.find('.facebox_btn_submit').addClass('button-tiny');
			}else{
				facebox_obj.find('.facebox_btn_submit').removeClass('button-tiny');
				facebox_obj.find('.facebox_btn_submit').addClass('button-primary');
			}
		});
	};
	facebox(p_arr);
}
/**
* 移动图片
**/
function movePosterimg(){
	if(!PromptPop()){
		return;
	}
	var html='<div class="popup">'
	+'<div class="p_main">'
   	+'<div style="width:430px; margin:0 auto;">'
    +'<div style="overflow:hidden" class=" line50 text-left">'
    +'<div class="fl w110">&nbsp;</div>'
    +'<div class="fl w280 text-left">图片将复制移入到该款色下</div>'
    +'</div>'
    +'<div style="overflow:hidden" class="line50">'
    +'<div class="fl w110">输入款色号：</div>'
    +'<div class="fl w280 text-left"><input type="text" style="width:160px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px; margin-top:10px;" class="input-xlarge brandnumber" placeholder="请填写款色号"></div><div><label class="error" style="display:none"></label></div>'
    +'</div>'
    +'<div style="overflow:hidden" class="line50">'
    +'<div class="fl w110">转移到类目：</div>'
    +'<div class="fl w280 text-left"><input value="model_img" type="checkbox" style="margin:0; width:15px;" id="checkbox" name="checkbox" checked>模特图'
    +'</div>'
    +'</div>'
    +'<div style="overflow:hidden" class="line50">'
    +'<div class="fl w110">&nbsp;</div>'
    +'<div class="fl w280 text-left">'
    +'<a style="border-radius:4px;padding:0 10px; width:100px; height:34px; line-height:34px; font-size:12px" class="button button-tiny facebox_btn_submit">移动</a>'
    +'</div>'
    +'</div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='移动图片';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			var brandnumber=facebox_obj.find('.brandnumber').val();
			if(strempty(brandnumber)){
				return;
			}
			var img_type_arr=new Array();
			facebox_obj.find('input[type=checkbox]:checked').map(function(){
				img_type_arr.push($(this).val());
			});
			/***删除文件夹事件***/
			$.getJSON('/erp/CopyPosterImg?brandnumber='+brandnumber+'&img_type='+img_type_arr.join(',')+'&id='+getClothesnum(),function(a){
				if(a.status==0){
					facebox_obj.find('.error').html(a.msg);
					facebox_obj.find('.error').show();
				}else{
					facebox_obj.find('.error').hide();
					facebox_obj.find('.ld_close').click();
					PromptPop('海报复制成功',true);
				}
			});
		});
		facebox_obj.find('.brandnumber').change(function(){
			facebox_obj.find('.error').hide();
			var brandnumber=strtrim($(this).val());
			if(strempty(brandnumber)){
				facebox_obj.find('.facebox_btn_submit').removeClass('button-primary');
				facebox_obj.find('.facebox_btn_submit').addClass('button-tiny');
			}else{
				facebox_obj.find('.facebox_btn_submit').removeClass('button-tiny');
				facebox_obj.find('.facebox_btn_submit').addClass('button-primary');
			}
		});
		facebox_obj.find('.brandnumber').keyup(function(){
			var brandnumber=strtrim($(this).val());
			if(strempty(brandnumber)){
				facebox_obj.find('.facebox_btn_submit').removeClass('button-primary');
				facebox_obj.find('.facebox_btn_submit').addClass('button-tiny');
			}else{
				facebox_obj.find('.facebox_btn_submit').removeClass('button-tiny');
				facebox_obj.find('.facebox_btn_submit').addClass('button-primary');
			}
		});
		facebox_obj.find('input[type=checkbox]').click(function(){
			if(facebox_obj.find('input[type=checkbox]:checked').length==0){
				facebox_obj.find('input[type=checkbox]').eq(0).attr('checked',true);
			}
		})
		
	};
	facebox(p_arr);
}
/**
* 获取勾选图片数量
**/
function getClothesnum(){
	var poster_id_arr=new Array();
	//获取勾选单品数量
	$('.cpics_list input[type=checkbox]:checked').map(function(){
		poster_id_arr.push($(this).closest('li').attr('poster_id'));
	});
	return poster_id_arr.join(',');
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
    +(txt?txt:'请选择图片！')
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
* 下载图片
**/
function downimg(obj){
	var img_src=$(obj).closest('li').find('.div_table_cell img').attr('src');
	if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图
		return;
	}
	var img_src_arr=img_src.split('?imageView2');
	window.location.href="/erp/downfile?url="+img_src_arr[0];
}
/**
* 文件夹排序
**/
function dirnameSort(){
	var sort=$('.but_sort').attr('sort');
	if(sort=='bottom'){
		$('.but_sort').attr('sort','up').find('span').html('&uarr;');
	}else{
		$('.but_sort').attr('sort','bottom').find('span').html('&darr;');
	}
	Pagerefresh();
}
  
/**
* 搜索查询
**/
function Pagerefresh(){
	var url='/erp/poster';
	var sort=$('.but_sort').attr('sort');
	if(sort!=''){
		url+='/sort/'+sort;
	}
	var start_date=$('#start_date').val();
	if(!strempty(start_date) && start_date!=0){
		url+='/start_date/'+start_date;
	}
	var end_date=$('#end_date').val();
	if(!strempty(end_date) && end_date!=0){
		url+='/end_date/'+end_date;
	}
	var dirname=$('#dirname').val();
	if(!strempty(dirname) && dirname!=0){
		url+='/dirname/'+dirname;
	}
	window.location.href=url;
}
/**
* 图片放大
**/
function imgbig(obj){     
	var img_src=$(obj).find('img').attr('src');
	if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图 就不放大
		return;
	}
	var img_hei=$(window).height()-40;
	var img_wid=$(window).width();
	var img_src_arr=img_src.split('?imageView2');
	var html='<div class="popup">'
	+'<div class="p_main1" style="height:auto">'
   	+'<div class="div_table" style="margin: 5px auto;min-height:'+img_hei+'px;"><div class="div_table_cell"><img src="/images/482.GIF"></div></div>'
    +'</div>'
	+'</div>';
	
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['close_but']=true;
	p_arr['fun']=function(facebox_obj){
		var current_image = new Image();
		current_image.src = img_src_arr[0]+'?imageView2/2/w/'+img_wid+'/h/'+img_hei;
		current_image.onload=function(){
			facebox_obj.find('img').attr('src',img_src_arr[0]+'?imageView2/2/w/'+img_wid+'/h/'+img_hei);
			facebox_obj.css({'top':($(window).height()-facebox_obj.height())/2,'left':($(window).width()-facebox_obj.width())/2})
		};
	}
	facebox(p_arr);
}
/**
* 图片错误方法
**/
function imgonerror(obj,width,height,is_bool){
	var is_bool=is_bool?is_bool:false;
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
</script>