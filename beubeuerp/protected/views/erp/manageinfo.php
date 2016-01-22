<title>百一服装ERP管理系统3.0_图片管理_审图_<?php echo $data['clothes']['brandnumber']?></title>
<style>
.m_body3{
	margin: 0 auto 10px;
}
.m_body4{
	height: auto;
	min-height: auto;
}
.m_body5{
	height: auto;
	min-height: auto;
}
.cpics_list {
    width: 1200px;
}
.cpic_img{
	height: 268px;
	cursor: pointer;
}
.p_main1{
	height: auto;
	width: auto;
	min-width: 572px;
}
.m_cont_none{
	width: 1200px; line-height: 250px; font-weight: bold; color: #999;
}
.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center; padding: 0px;
	}
.text_overflower{
	overflow: hidden; display: inline-block; width:auto;max-width: 148px; text-overflow: ellipsis;word-break: keep-all;white-space: nowrap;
}
.cpics_list ul li{
	height: 370px;
	position: relative;
}
.p_main{padding:0}
</style>
<div class="m_body border1" style="padding:20px 0;; line-height:24px; min_height:268px;">
<div>
  <div class="fl w1000">
  <?php $id_str=Comm::strencrypt($data['clothes']['id'])?>
  <div>品牌名：<?php echo $data['brandname']?>　　款号：<?php echo $data['clothes']['sku']?>　　单品分类：<?php if(empty($data['clothes']['brandcategoryname'])){echo '<span style="color:red">未归类</span>';}else{echo $data['clothes']['brandcategoryname'];}?></div>
  <div class="w900 margin_x pad_t30 hid">
  	<div class="fl w50 t_30 line150 txt30 text-center txt_gray color_btn_f color_prev" style="cursor: pointer;">〈</div>
      <div class="fl w800 cloths_pic2 color_scrollable" style="overflow:hidden">
      	<ul style="width: 3000em; position: relative; z-index: 9; top: 0px; left: 0px;">
			
			<?php $li_bool=false;$li_i=0;foreach ($data['color'] as $value){
				 if($li_i%6==0){$li_bool=true;
				?>
				<div style="width: 800px;float: left;">
				 <?php } ?>
			<li><div class="border5<?php if($value['id']!=$data['clothes']['id']){echo 'a';}?> div_table" style="height:149px;overflow: hidden;width:100px"><div class="div_table_cell" style="padding:0"><a href="/erp/manageinfo/id/<?php echo Comm::strencrypt($value['id']);?>"><img src="<?php if(empty($value['url'])){echo '/';}else{ echo $domain.$value['url'].'?imageView2/2/w/100/h/149';} ?>" onerror="imgonerror(this,100,149)"/></a></div></div>
			<div style="overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: center;" title="<?php echo $value['brandnumber']?>"><?php echo $value['brandnumber']?></div>
			</li>
			<?php if($li_i%6==5){$li_bool=false;?>
			</div>
			<?php }$li_i++;}
			if($li_bool){
			?>
			</div>
			<?php }?>
        </ul>
      </div>
      <div class="fl w50 t_30 line150 txt30 txt_gray text-center color_btn_f color_next" style="cursor: pointer;">〉</div>
	</div>
	<div class="color_page" style="float: right; width: 45px; margin-top: -106px; font-weight: bold; font-size: 14px;"><span class="color_num">1</span>/<span class="color_count">1</span></div>
	</div>
	<div class="fr w194 pad_t40">
   	  <?php if(Beu_Power::selectAction('/erp/pushlist')){ ?><div class="pad_t20"><a style="padding:0 15px;" class="button button-primary button-rounded button-small" onclick="push_div()">图片推送</a></div><?php }?>
	  <?php $prev_color=''; $prev_url='';if(!empty($data['prev'])){
		  $prev_color='button-primary';
		  $prev_url='href="/erp/manageinfo/id/'.Comm::strencrypt($data['prev']).'"';
	  }?>
      <div class="pad_t20"><a style="padding:0 15px;" class="button <?php echo $prev_color;?> button-rounded button-small" <?php echo $prev_url;?>>看上一款</a></div>
	  <?php $next_color=''; $next_url='';if(!empty($data['next'])){
		  $next_color='button-primary';
		  $next_url='href="/erp/manageinfo/id/'.Comm::strencrypt($data['next']).'"';
	  }?>
	  <div class="pad_t20"><a style="padding:0 15px;" class="button <?php echo $next_color;?> button-rounded button-small" <?php echo $next_url;?>>看下一款</a></div>
      <div class="pad_t20"><input type="checkbox" style="margin:0; width:15px;margin-top: -1px;" onclick="Website_template_fun(this)" class="Website_template">&nbsp;以款查看</div>
    </div>
  </div>
</div>
<div class="m_body border1">
  <div class="w1000 text-center" style="margin:0 auto; clear:both;">
	<div class="fl pad_l20"><a onclick="uploadimg()" class="button button-primary button-rounded button-small but_sort" style="padding:0 10px;">上传图片</a></div>
    <div class="fl pad_l20">排序：<a onclick="deteSort()" sort="<?php echo $sort;?>" class="button button-primary button-rounded button-small but_sort" style="padding:0 10px;">时间排序<span><?php if($sort=='down'){ echo '&darr;';}else{echo '&uarr;';} ?></span></a></div>
		<?php if(Beu_Power::selectAction('/erp/setClothesimgstatus') || Beu_Power::selectAction('/erp/setClothesImgstyle')){ ?>
      <div class="fl pad_l20">批量操作：<?php if(Beu_Power::selectAction('/erp/setClothesimgstatus')){ ?><a onclick="setImgstatepop()" class="button button-primary button-rounded button-small" style="padding:0 10px;">一键审图</a>
	  <?php }?>
	  </div>
	  <?php if(Beu_Power::selectAction('/erp/setClothesImgstyle')){ ?>
      <div class="fl pad_l10"><a onclick="setImgStyle()" class="button button-primary button-rounded button-small" style="padding:0 10px;">修改图片类型</a></div>
	  <?php }?>
	  <?php }?>
      <div class="fl pad_l20" >搜索查询： 
      	<select id="date_sel" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">上传时间</option>
        <?php foreach($date_arr as $value){?>
		 <option value="<?php echo $value;?>"><?php echo $value;?></option>
		<?php }?>
      </select> 
      </div>
      <div class="fl pad_l10" >
      	<select id="style_sel" name="" size="1" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">审核状态</option>
        <option value="7">待审核</option>
		<option value="1">合格</option>
		<option value="2">不合格</option>
      </select> 
      </div>
      <div class="fl pad_l10" ><input type="text" placeholder="文件名模糊搜索" class="input-xlarge search_img_name" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">
      <a onclick="Search_data()" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">查询</a></div>
      <div class="fr pad_l10 txt_red line28 li_show_num" style="display:none">共找到<span>0</span>个</div>
  </div>

</div>   
<div class="m_body3">
  <div class="fl w100 quanxuan" style="line-height: 24px; margin-left: 5px; width: 70px; height: 100%;">
	<div><input onclick="setChecked(this)" type="checkbox" style="margin-top: -1px;">&nbsp;全选/反选</div>
  </div>
  <div class="fl w1150 text-center t_14b" style="width:1045px">产品图</div>
  <div class="fr w50 text-left"><a onclick="openorclr(this)" style="cursor: pointer;"><span class="txt_white">收起&nbsp;&uArr;<!--&dArr;--></span></a></div>
</div>
<div class="m_body4 cpics_list">
	<ul>
		<?php $istop_id=0;$is_num=0;foreach($data['img'] as $value){ if(in_array($value->type,array(0,1,2))){$is_num++;?>
    	<li img_id="<?php echo Comm::strencrypt($value->id)?>" img_id2="<?php echo $value->id?>" class="img_id_<?php echo $value->id?>" c_o_id="<?php echo $id_str?>">
       	  <div class="cpic_t1">
          	<input type="checkbox" name="checkbox" id="checkbox"  style="margin:0; width:15px;"/>
			<?php if(Beu_Power::selectAction('/erp/delClothesimg')){ ?>
          	<a onclick="delClothesimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">删除</a>
			<?php }?>
			<?php if(Beu_Power::selectAction('/erp/setClothesimgstatus')){ ?>
            <?php if($value->status==2 || $value->status==0){?>
			<a onclick="clickStatus(this,1)" class="button button-primary button-rounded button-small img_status_btn_1" style=" width:50px; padding:0 5px;margin-top:15px">合格</a>
			<?php }?>
			<?php if($value->status==1 || $value->status==0){?>
            <a onclick="clickStatus(this,2)" class="button button-primary button-rounded button-small img_status_btn_2" style=" width:50px; padding:0 5px;margin-top:15px">不合格</a>
			<?php }?>
			<?php }?>
            <a onclick="downimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">下载</a>
          </div>
          <div class="cpic_img border1">
            <div class="div_table" onclick="imgbig(this)"><div class="div_table_cell"><img src="<?php if(empty($value->url)){echo '/';}else{ echo $domain.$value->url.'?imageView2/2/w/268/h/268';} ?>" onerror="imgonerror(this,268,268)" img_type="<?php echo $value->type?>"/></div></div>
            
          </div>
          <div class="cpic_admin">
          	<div class="fl w50">
            <a img_status="<?php echo $value->status?>" class="button button-primary button-rounded button-small img_status" style=" width:50px; padding:0 5px;margin-top:15px;<?php if($value->status==1){echo 'background: #1eca11;';}else if($value->status==2){echo 'background: #ff1e1e;';}?>"><?php if($value->status==1){echo '已合格';}else if($value->status==2){echo '不合格';}else { echo '待审核';}?></a>
			</div>
            <div class="fl w214 line20 pad_t10">
              <div><span class="text_overflower" style="width:50px">文件名：</span><span class="img_name text_overflower"><?php echo $value->name ?></span></div>
              <div>上传日期：<span class="img_date"><?php echo $value->addtime ?></span></div>
            </div>
          </div>
        </li>
		<?php }}?>
		<div class="m_cont_none" style="<?php if($is_num>0){ echo 'display:none;';}?>">没有上传图片</div>
      <div class="clr">&nbsp;</div>
    </ul>
</div>
<div class="m_body3">
  <div class="fl w100 quanxuan" style="line-height: 24px; margin-left: 5px; width: 70px; height: 100%;">
	<div><input onclick="setChecked(this)" type="checkbox" style="margin-top: -1px;">&nbsp;全选/反选</div>
  </div>
  <div class="fl w1150 text-center t_14b" style="width:1045px">模特图</div>
  <div class="fr w50 text-left"><a onclick="openorclr(this)" style="cursor: pointer;"><span class="txt_white">展开&nbsp;&dArr;<!--&dArr;--></span></a></div>
</div>
<div class="m_body5 cpics_list" style="display:none">
<ul>
		<?php $is_num=0;foreach($data['img'] as $value){ if($value->type==3){$is_num++;?>
    	<li img_id="<?php echo Comm::strencrypt($value->id)?>" img_id2="<?php echo $value->id?>" class="img_id_<?php echo $value->id?>" c_o_id="<?php echo $id_str?>">
       	  <div class="cpic_t1">
          	<input type="checkbox" name="checkbox" id="checkbox"  style="margin:0; width:15px;"/>
          	<?php if(Beu_Power::selectAction('/erp/delClothesimg')){ ?>
			<a onclick="delClothesimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">删除</a>
			<?php }?>
			<?php if(Beu_Power::selectAction('/erp/setClothesimgstatus')){ ?>
            <?php if($value->status==2 || $value->status==0){?>
			<a onclick="clickStatus(this,1)" class="button button-primary button-rounded button-small img_status_btn_1" style=" width:50px; padding:0 5px;margin-top:15px">合格</a>
			<?php }?>
			<?php if($value->status==1 || $value->status==0){?>
            <a onclick="clickStatus(this,2)" class="button button-primary button-rounded button-small img_status_btn_2" style=" width:50px; padding:0 5px;margin-top:15px">不合格</a>
			<?php }?>
			<?php }?>
            <a onclick="downimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">下载</a>
          </div>
          <div class="cpic_img border1">
            <div class="div_table" onclick="imgbig(this)"><div class="div_table_cell"><img src="<?php if(empty($value->url)){echo '/';}else{ echo $domain.$value->url.'?imageView2/2/w/268/h/268';} ?>" onerror="imgonerror(this,268,268)" img_type="<?php echo $value->type?>"/></div></div>
            
          </div>
          <div class="cpic_admin">
          	<div class="fl w50">
            <a img_status="<?php echo $value->status?>" class="button button-primary button-rounded button-small img_status" style=" width:50px; padding:0 5px;margin-top:15px;<?php if($value->status==1){echo 'background: #1eca11;';}else if($value->status==2){echo 'background: #ff1e1e;';}?>"><?php if($value->status==1){echo '已合格';}else if($value->status==2){echo '不合格';}else { echo '待审核';}?></a>
			</div>
            <div class="fl w214 line20 pad_t10">
              <div><span class="text_overflower" style="width:50px">文件名：</span><span class="img_name text_overflower"><?php echo $value->name ?></span></div>
              <div>上传日期：<span class="img_date"><?php echo $value->addtime ?></span></div>
            </div>
          </div>
        </li>
		<?php }}?>
		<div class="m_cont_none" style="<?php if($is_num>0){ echo 'display:none;';}?>">没有上传图片</div>
		<div class="clr">&nbsp;</div>
    </ul>
</div>
<div class="m_body3">
  <div class="fl w100 quanxuan" style="line-height: 24px; margin-left: 5px; width: 70px; height: 100%;">
	<div><input onclick="setChecked(this)" type="checkbox" style="margin-top: -1px;">&nbsp;全选/反选</div>
  </div>
  <div class="fl w1150 text-center t_14b" style="width:1045px">细节图</div>
  <div class="fr w50 text-left"><a onclick="openorclr(this)" style="cursor: pointer;"><span class="txt_white">展开&nbsp;&dArr;<!--&dArr;--></span></a></div>
</div>
<div class="m_body4 cpics_list" style="display:none">
	<ul>
    	<?php $is_num=0;foreach($data['img'] as $value){ if($value->type==4){$is_num++;?>
    	<li img_id="<?php echo Comm::strencrypt($value->id)?>" img_id2="<?php echo $value->id?>" class="img_id_<?php echo $value->id?>" c_o_id="<?php echo $id_str?>">
       	  <div class="cpic_t1">
          	<input type="checkbox" name="checkbox" id="checkbox"  style="margin:0; width:15px;"/>
          	<?php if(Beu_Power::selectAction('/erp/delClothesimg')){ ?>
			<a onclick="delClothesimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">删除</a>
			<?php }?>
			<?php if(Beu_Power::selectAction('/erp/setClothesimgstatus')){ ?>
			<?php if($value->status==2 || $value->status==0){?>
			<a onclick="clickStatus(this,1)" class="button button-primary button-rounded button-small img_status_btn_1" style=" width:50px; padding:0 5px;margin-top:15px">合格</a>
			<?php }?>
			<?php if($value->status==1 || $value->status==0){?>
            <a onclick="clickStatus(this,2)" class="button button-primary button-rounded button-small img_status_btn_2" style=" width:50px; padding:0 5px;margin-top:15px">不合格</a>
			<?php }?>
			<?php }?>
            <a onclick="downimg(this)" class="button button-primary button-rounded button-small" style=" width:50px; padding:0 5px;margin-top:15px">下载</a>
          </div>
          <div class="cpic_img border1">
            <div class="div_table" onclick="imgbig(this)"><div class="div_table_cell"><img src="<?php if(empty($value->url)){echo '/';}else{ echo $domain.$value->url.'?imageView2/2/w/268/h/268';} ?>" onerror="imgonerror(this,268,268)" img_type="<?php echo $value->type?>"/></div></div>
            
          </div>
          <div class="cpic_admin">
          	<div class="fl w50">
            <a img_status="<?php echo $value->status?>" class="button button-primary button-rounded button-small img_status" style=" width:50px; padding:0 5px;margin-top:15px;<?php if($value->status==1){echo 'background: #1eca11;';}else if($value->status==2){echo 'background: #ff1e1e;';}?>"><?php if($value->status==1){echo '已合格';}else if($value->status==2){echo '不合格';}else { echo '待审核';}?></a>
			</div>
            <div class="fl w214 line20 pad_t10">
              <div><span class="text_overflower" style="width:50px">文件名：</span><span class="img_name text_overflower"><?php echo $value->name ?></span></div>
              <div>上传日期：<span class="img_date"><?php echo $value->addtime ?></span></div>
            </div>
          </div>
        </li>
		<?php }}?>
		<div class="m_cont_none" style="<?php if($is_num>0){ echo 'display:none;';}?>">没有上传图片</div>
      <div class="clr">&nbsp;</div>
    </ul>
</div>
<script src="<?php echo Yii::app()->request->baseUrl; ?>/js/CutOutImg.js"></script>
<script>
var user_type='<?php echo $_SESSION['type']?>';
var brandnumber='<?php echo $data['clothes']['brandnumber']?>';
var id='<?php echo $id_str?>';
var img_sku='<?php echo $data['clothes']['sku']?>';
var istop_id='<?php echo Comm::strencrypt($istop_id)?>';
var domain='<?php echo $domain?>';
var orderid_str='<?php echo Comm::strencrypt($data['clothes']['orderid']);?>';
$(function(){
	$('.color_page .color_count').html($(".color_scrollable ul>div").length)
	try{
		$(".color_scrollable").scrollable({
			size: 1,
			items:'.color_scrollable ul',
			vertical:false,
			prev:'.color_prev',
			next:'.color_next',
			onSeek:currentlistbutton
		});
		if($('.color_scrollable ul>div').length>1){
			$('.color_next').removeClass('txt_gray');
		}
	}catch(e){
		$(".color_scrollable").scrollable().begin();//返回首页
	}
	var cookie_Website_template=getCookie('Website_template');
	if(cookie_Website_template=='SKU'){
		$('.Website_template').attr('checked',true);
	}
	setimg_li_margin();
	//添加作图功能
	CutOut();
})
/**
 * 翻页按钮状态 
 */
function currentlistbutton(e,i){
	var page_sum=Math.ceil(e.target.getSize()/e.target.getConf().size);
	var page_num=Math.ceil(i/e.target.getConf().size)+1;
	$('.color_btn_f').removeClass('txt_gray');
	$('.color_btn_f').addClass('txt_gray');
	if(page_num>1){//当前页大于1 开启上一页翻页按钮
		$('.color_prev').removeClass('txt_gray');
	}
	if(page_sum>1 && page_num<page_sum){//当前页小于总页 并且总页大于1 开启下一页翻页按钮
		$('.color_next').removeClass('txt_gray');
	}
	$('.color_page .color_num').html(page_num)
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
* 全选/反选
**/
function setChecked(obj){
	if($(obj).attr('checked')){
		$(obj).closest('.m_body3').next('.cpics_list').find('input[type=checkbox]').attr('checked',true);
	}else{
		$(obj).closest('.m_body3').next('.cpics_list').find('input[type=checkbox]').attr('checked',false);
	}
}
/**
* 获取勾选单品数量
**/
function getClothesnum(){
	var clothes_arr=new Array();
	//获取勾选单品数量
	$('.cpics_list input[type=checkbox]:checked').map(function(){
		clothes_arr.push($(this).closest('li').attr('img_id'));
	});
	return clothes_arr.join(',');
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
* 一键合格
**/
function setImgstatepop(){
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
			setClothesimgstatus(0,1);
			facebox_obj.find('.ld_close').click();
		});
		facebox_obj.find('.facebox_btn_close').bind('click',function(){
			facebox_obj.find('.ld_close').click();
		});
	};
	facebox(p_arr);
}
/**
* 点击状态
**/
function clickStatus(obj,status){
	setClothesimgstatus($(obj).closest('li').attr('img_id'),status);
}
/**
* 设置图片状态
**/
function setClothesimgstatus(img_id,status){
	$.getJSON('/erp/setClothesimgstatus?clothes_order_id='+id+'&img_id='+img_id+'&status='+status,function(a){
		if(a.status==1){
			var but_text='不合格';
			var but_status=2;
			var status_text='已合格';
			var status_bg_color='#1eca11';
			if(status==2){
				but_text='合格';
				but_status=1;
				status_text='不合格';
				status_bg_color='#ff1e1e';
			}
			var img_id_arr=a.img_id.split(',');
			for(var i in img_id_arr){
				$('.img_id_'+img_id_arr[i]+' .img_status_btn_'+but_status).remove();
				$('.img_id_'+img_id_arr[i]+' .img_status_btn_'+status).html(but_text).removeAttr('onclick').removeClass('img_status_btn_'+status).addClass('img_status_btn_'+but_status).unbind('click').click(function(){
					clickStatus(this,but_status);
				});
				$('.img_id_'+img_id_arr[i]+' .img_status').html(status_text).css('background',status_bg_color).attr('img_status',status);
			}
			Search_data();//重新搜索
		}
	});
}
/**
* 删除图片
**/
function delClothesimg(obj){
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
	p_arr['facebox_title']='删除单品图';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***删除图片事件***/
			$.getJSON('/erp/delClothesimg?clothes_order_id='+id+'&img_id='+$(obj).closest('li').attr('img_id'),function(a){
				if(a.status==1){
					$(obj).closest('li').fadeOut("slow",function(){
						$(this).remove();
						Search_data();//重新搜索
						facebox_obj.find('.ld_close').click();
					});
				}else{
					facebox_obj.find('.ld_close').click();
					PromptPop('删除图片失败',true);
				}
			});
		});
	};
	facebox(p_arr);
}
/**
* 上传图片
**/
function uploadimg(){
	var html='<div class="popup">'
	+'<div class="p_main">'
    +'<div class="fl w170 div_table"><div class="div_table_cell">'
	+'<div><input type="radio" name="checkbox" value="1" style="margin: -2px 0 0; width:15px;" checked/>&nbsp;产品图&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="checkbox" value="3"  style="margin: -2px 0 0; width:15px;"/>&nbsp;模特图&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="checkbox" value="4" style="margin: -2px 0 0; width:15px;"/>&nbsp;细节图</div>'
    +'<div class="">'
    +'<a style="border-radius:4px;padding:0 10px;margin-top: 22px; width:100px; height:34px; line-height:34px; font-size:12px" class="button button-primary facebox_btn_submit">添加图片</a>'
	+'<form style="display:none" action="/erp/uploadimg" method="post" target="facebox_iframe_img" enctype="multipart/form-data">'
	+'<input type="file" name="facebox_file" class="facebox_file"/>'
	+'</form>'
    +'<iframe style="display:none" name="facebox_iframe_img"></iframe>'
	+'</div>'
    +'</div></div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='上传图片';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			facebox_obj.find('.facebox_file').click();
		});
		facebox_obj.find('.facebox_file').change(function(){
			facebox_obj.find('form').attr('action','/erp/uploadimg?id='+id+'&orderid_str='+orderid_str+'&sku='+img_sku+'&skc='+brandnumber+'&img_type='+facebox_obj.find('input[type=radio]:checked').val());
			facebox_obj.find('form').submit();
			facebox_obj.find('.ld_close').click();
			PromptPop('<img src="/images/482.gif" style="width:40px;height:40px"/><br/>图片上传中，请稍等！',true);
		});
	};
	facebox(p_arr);
}
/**
* 图片上传回调
**/
function facebox_upload_img_ret(a){
	facebox_close_all();
	a=eval('('+a+')');
	var txt='图片上传成功,请刷新页面';
	if(a.status==0){
		txt=a.msg;
	}
	PromptPop(txt,true);
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
	var url_arr=img_src_arr[0].split('/');
	var url_type_arr=url_arr[url_arr.length-1].split('.');
	var img_type=$(obj).closest('li').find('.div_table_cell img').attr('img_type');
	if(img_type==0 || img_type==1 || img_type==2){
		 img_type='A';
	}else if(img_type==3){
		 img_type='B';
	}else if(img_type==4){
		 img_type='C';
	}
	var Suffix_namg='';
	if(url_type_arr[0].indexOf('-')!=-1){
		Suffix_namg='-';
		var Suffix_arr=url_type_arr[0].split('-');
		Suffix_arr.splice(0,1);
		Suffix_namg+=Suffix_arr.join('-');
	}
	var file_name=url_arr[url_arr.length-3]+'_'+img_type+'_'+($(obj).closest('.cpics_list').find('li').index($(obj).closest('li'))+1)+($(obj).closest('li').find('.img_tipsa').length>0?'_Z':'')+Suffix_namg+'.'+url_type_arr[url_type_arr.length-1];
	window.location.href="/erp/downfile?url="+img_src_arr[0]+'&file_name='+file_name;
}
/**
* 修改图片类型
**/
function setImgStyle(){  
	if(!PromptPop()){
		return;
	}
	var html='<div class="popup">'
	+'<div class="p_main">'
   	+'<div class=" line100 text-center">请选择需要转移的类型</div>'
   	+'<div style="margin:0 auto" class=" w340">'
    +'<div class="fl w170 text-center">'
    +'<select style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;" size="1" name="">'
    +'<option value="0">选择分类</option>'
	+'<option value="1">产品图</option>'
	+'<option value="3">模特图</option>'
	+'<option value="4">细节图</option>'
    +'</select>'
    +'</div>'
    +'<div class="fl w170 text-center">'
    +'<a style="border-radius:4px;padding:0 10px; width:100px; height:34px; line-height:34px; font-size:12px" class="button button-tiny facebox_btn_submit">确定</a>'
    +'</div>'
    +'</div>'
    +'</div>'
    +'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='修改图片类型';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			/***修改图片类型事件***/
			var select_v=facebox_obj.find('select').val();
			if(select_v!='0'){
				$.getJSON('/erp/setClothesImgstyle?clothes_order_id='+id+'&img_id='+getClothesnum()+'&Style='+select_v,function(a){
					facebox_obj.find('.ld_close').click();
					if(a.status==0){
						PromptPop('修改图片类型失败',true);
					}
					var eq_i=0;
					if(select_v==3){
						eq_i=1;
					}else if(select_v==4){
						eq_i=2;
					}
					for(var i in a.ret_img_url){
						$('.cpics_list').eq(eq_i).find('ul').prepend($('.img_id_'+i));
						$('.img_id_'+i+' .cpic_img img').attr('src',domain+a.ret_img_url[i]+'?imageView2/2/w/268/h/268').attr('img_type',select_v);
					}
					$('.cpics_list').map(function(){
						if($(this).find('ul li').length==0){
							$(this).find('ul .m_cont_none').show();
						}else{
							$(this).find('ul .m_cont_none').hide();
						}
					});
				});
			}
		});
	};
	facebox(p_arr);
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
	+'<div class="p_main1">'
   	+'<div class="div_table" style="margin: 5px auto;min-height:'+img_hei+'px;"><div class="div_table_cell"><img src="/images/482.GIF"></div></div>'
    +'<div style="background:#f9f9f9" class="text-center line28">款色号：'+brandnumber+'</div>'
    +'</div>'
	+'</div>';
	
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['close_but']=true;
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.ld_close').show();
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
* 设置主图片
**/
function setIstop(obj){
	var new_istop_id=$(obj).closest('li').attr('img_id');
	var img_src=$(obj).closest('li').find('.div_table_cell img').attr('src');
	if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图
		return;
	}
	if(new_istop_id!=istop_id){//点击图片不为主图片
		$.getJSON('/erp/setIstop?clothes_order_id='+id+'&new_istop_id='+new_istop_id+'&istop_id='+istop_id,function(a){
			if(a.status==1){
				istop_id=new_istop_id;//设置新的主图
				$(obj).addClass('img_tipsa').removeClass('img_tips');
				$('.img_id_'+a.istop_id+' .img_tipsa').addClass('img_tips').removeClass('img_tipsa');
			}
		});
	}
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
* 搜索数据
**/
function Search_data(){
	var date_sel_val=$('#date_sel').val();
	var style_sel_val=$('#style_sel').val();
	var search_img_name_val=$('.search_img_name').val().toLocaleUpperCase();
	if(style_sel_val==0 && date_sel_val==0 && search_img_name_val==''){
		return;
	}
	$('.cpics_list li .cpic_img').removeClass('border1').addClass('border1a');
	
	if(style_sel_val!=0){
		var style_sel_val_2=style_sel_val==7?0:style_sel_val;
		$('.img_status').map(function(){
			if($(this).attr('img_status')!=style_sel_val_2){
				$(this).closest('li').find('.cpic_img').removeClass('border1a').addClass('border1');
			}
		});
	}
	if(date_sel_val!=0){
		$('.img_date').map(function(){
			if($(this).html().substr(0,10)!=date_sel_val){
				$(this).closest('li').find('.cpic_img').removeClass('border1a').addClass('border1');
			}
		});
	}
	if(search_img_name_val!=''){//搜索图片名
		$('.img_name').map(function(){
			if($(this).html().toLocaleUpperCase().indexOf(search_img_name_val)==-1){
				$(this).closest('li').find('.cpic_img').removeClass('border1a').addClass('border1');
			}
		});
	}
	var li_sum=0;
	$('.cpics_list').map(function(){
		var li_len=$(this).find('.border1a').length;
		li_sum+=li_len;
	});
	
	if(style_sel_val==0 && date_sel_val==0 && search_img_name_val==''){
		$('.li_show_num').hide();
	}else{
		$('.li_show_num').show();
		$('.li_show_num span').html(li_sum);
	}
	
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
/**
* 搜索查询
**/
function Pagerefresh(){
	var url='/erp/manageinfo/id/'+id;
	var sort=$('.but_sort').attr('sort');
	if(sort!=''){
		url+='/sort/'+sort;
	}
	window.location.href=url;
}
/**
* 设置图片的margin
**/
function setimg_li_margin(){
	$('.cpics_list').map(function(){
		var li_num=0;
		$(this).find('li').map(function(){
			if(li_num==0){
				$(this).css('margin-left',0);
			}
			li_num++;
			if(li_num==4){
				$(this).css('margin-right',0);
				li_num=0;
			}
		})
	});
}
/**
* 网站模版切换
**/
function Website_template_fun(obj){
	setTimeout(function(){
		if($(obj).attr('checked')){
			setCookie('Website_template','SKU',0.042);
		}else{
			setCookie('Website_template','SKC',0.042);
		}
		window.location.reload();
	},500);
}
</script>