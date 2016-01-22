var CutOut_boxs_scroll_time=null;//页面滚动定时器
var CutOut_start_bool=false;//是否开启了作图
var CutOut_data=new Object();//裁剪图片数据
var CutOut_del_data=new Object();//裁剪图片删除数据
var CutOut_Store_val=0;//平台类型
var CutOut_submit_bool=false;//是否需要保存 false不需要
var CutOut_Store_zoom_data=new Object();//平台缩放数据
var CutOut_Store_watermark=new Object();//水印数据
var CutOut_Resize_obj_data=new Object();//图片缩放控件对象数组
var CutOut_cut_id_data=new Object();//有裁剪图的裁剪模版数组
var CutOut_Store_cut_data=new Object();//平台对应的裁剪模版
var CutOut_barcode_template='';//模版类型 
var CutOut_powers=1;//默认裁剪为1 1为完整权限 0为部分权限
function CutOut(){
	CutOut_getStore();
	var html='<div class="img_edit1 dis" onclick="CutOut_start()" style="cursor: pointer;"><!-- dis_n 用于隐藏-->'
	+'<a>做图</a>'
	+'<div class="edit_num dis_n">0</div>'
	+'</div>';
	$('.container_div').prepend(html);
	$('.container_div').append('<script src="/js/Resize.js"></script><script src="/js/Drag.js"></script><link href="/css/Resize.css" rel="stylesheet" type="text/css" /><script src="/js/json2.js"></script>');
	CutOut_getCookie();
	if(user_type==undefined || user_type==53){//权限未设置 或为普通权限
		CutOut_powers=0;
	}
}
/**
* 开启作图功能
**/
function CutOut_start(){
	if(CutOut_start_bool){
		return;
	}
	CutOut_start_bool=true;
	$('.cpic_t1').hide();//隐藏图片功能区
	$('.m_body3 input[type=checkbox]:checked').attr('checked',false);//去除所有勾选按钮
	$('.cpics_list input[type=checkbox]:checked').attr('checked',false);//去除所有勾选按钮
	$('.quanxuan div').hide();//全选框隐藏
	CutOut_img_add_watermark();//图片添加水印功能
	/*if(CutOut_powers==1){//完整权限
		CutOut_img_add_box();//给图片添加裁剪框
	}else{//部分权限时 设置图片初始值
		
	}*/
	
	if($('.cpics_list:visible').length==0){//默认展开一个图片栏目
		$('.m_body3').eq(0).find('a').click();
	}
	CutOut_boxs();//添加裁剪图片栏
	$('.Website_template').closest('div').hide();
	//window.onbeforeunload=onclose;
	
}
function onclose()
{ 
	return "请确认数据已保存！";
}
/**
* 关闭作图功能
**/
function CutOut_end(){
	if(CutOut_start_bool && CutOut_submit_bool){
		var html='<div class="popup">'
		+'<div class="p_main">'
		+'<div class=" pad_t50 line28 text-center" style="padding-top:77px;">内容有所调整，请先点击"保存"按钮后再退出</div>'
		+'<div class=" w256" style="margin:0 auto">'
		+'<a class="button button-primary button-rounded button-small facebox_btn_close" style=" width:120px; padding:0 5px;margin-top:15px">关闭提示</a>'
		+'&nbsp;&nbsp;<a class="button button-primary button-rounded button-small facebox_btn_submit" style=" width:120px; padding:0 5px;margin-top:15px">不保存退出</a>'
		+'</div>'
		+'</div>'
		+'</div>';
		var p_arr=new Array();
		p_arr['div']=html;
		p_arr['facebox_title']='提示';
		p_arr['fun']=function(facebox_obj){
			facebox_obj.find('.facebox_btn_submit').bind('click',function(){
				CutOut_img_del_watermark();//图片删除水印功能
				CutOut_img_del_box();//删除图片裁剪框
				$('.cpic_t1').show();//显示图片功能区
				$('.quanxuan div').show();//全选框显示
				$('.m_body7').remove();//删除裁剪图片栏
				$('.Website_template').closest('div').show();
				$(document).unbind('scroll',CutOut_boxs_scroll);
				CutOut_start_bool=false;
				CutOut_submit_bool=false;
				CutOut_setCookie();
				facebox_obj.find('.ld_close').click();
				//document.removeEventListener("beforeunload", onclose, false);
			});
			facebox_obj.find('.facebox_btn_close').bind('click',function(){
				facebox_obj.find('.ld_close').click();
			});
		};
		facebox(p_arr);
	}else{
		CutOut_img_del_watermark();//图片删除水印功能
		CutOut_img_del_box();//删除图片裁剪框
		$('.cpic_t1').show();//显示图片功能区
		$('.quanxuan div').show();//全选框显示
		$('.m_body7').remove();//删除裁剪图片栏
		$('.Website_template').closest('div').show();
		$(document).unbind('scroll',CutOut_boxs_scroll);
		CutOut_start_bool=false;
		CutOut_setCookie();
		//document.removeEventListener("beforeunload", onclose, false);
	}
}
/**
* 保存数据
**/
function CutOut_submit(){
	var CutOut_zoom_data=new Object();
	for(var i in CutOut_data){
		var Store_num=0;
		for(var i_i in CutOut_data[i]){
			if(CutOut_data[i][i_i]['imageid']!=undefined){
				Store_num++;
				for(var Store_i in CutOut_Store_zoom_data){
					if(CutOut_Store_zoom_data[Store_i]['img_'+CutOut_data[i][i_i]['imageid']]!=undefined){
						if(CutOut_zoom_data[Store_i]==undefined){
							CutOut_zoom_data[Store_i]=new Object();
						}
						CutOut_zoom_data[Store_i][CutOut_data[i][i_i]['imageid']]=CutOut_Store_zoom_data[Store_i]['img_'+CutOut_data[i][i_i]['imageid']];
					}
				}
			}
			if(CutOut_data[i][i_i]['watermarkid']==undefined){
				CutOut_data[i][i_i]['watermarkid']=0;
			}
		}
		//当模版有裁剪图 并且 其模版还未加入的 CutOut_cut_id_data数组时 将其加入
		if(Store_num>0 && array_search(CutOut_Store_cut_data[i],CutOut_cut_id_data)===false){
			CutOut_cut_id_data[CutOut_obj_len(CutOut_cut_id_data)]=CutOut_Store_cut_data[i];
		}
	}
	CutOut_setStoreNum();
	$.post('/erp/setCutOutImg',{'data':JSON.stringify(CutOut_data),'zoom_data':JSON.stringify(CutOut_zoom_data),'del_data':JSON.stringify(CutOut_del_data)},function(){
		CutOut_start_bool=false;
		CutOut_submit_bool=false;
		CutOut_Prompt_pop('保存成功！');
		$.getJSON('/erp/delCutCookie?SKU='+img_sku,function(){});//删除缓存数据
		//CutOut_end();
	});
	//document.removeEventListener("beforeunload", onclose, false);
}
/**
* 获取Object 的长度
**/
function CutOut_obj_len(obj){
	var obj_len=0;
	for(var obj_i in obj){
		obj_len++;
	}
	return obj_len;
}
/**
* 设置还有多少裁剪模版 未推送数据
**/
function CutOut_setStoreNum(){
	var len=CutOut_obj_len(CutOut_cut_id_data);
	if(len>0){
		$('.img_edit1 .edit_num').html(len).removeClass('dis_n');
	}else{
		$('.img_edit1 .edit_num').html(len).addClass('dis_n');
	}
}
/**
* 获取平台 模版 水印 信息
**/
function CutOut_getStore(){
	$.getJSON('/erp/getStore?skc='+brandnumber+'&sku='+img_sku,function(a){
		if(a.status==1){
			CutOut_Store_zoom_data=new Object();//平台缩放数据
			for(var p_i in a.patform){
				CutOut_Store_cut_data[a.patform[p_i]['Englishname']]=a.cut[a.patform[p_i]['id']]['id'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]=new Object();
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['name']=a.patform[p_i]['name'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['w']=a.cut[a.patform[p_i]['id']]['width'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['h']=a.cut[a.patform[p_i]['id']]['height'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['zoom_ratio']=a.cut[a.patform[p_i]['id']]['width']/a.cut[a.patform[p_i]['id']]['height'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['patformid']=a.cut[a.patform[p_i]['id']]['patformid'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['barcodetype']=a.cut[a.patform[p_i]['id']]['barcodetype'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['havewatermark']=a.cut[a.patform[p_i]['id']]['havewatermark'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['watermarkwidth']=strempty(a.cut[a.patform[p_i]['id']]['watermarkwidth'])?0:a.cut[a.patform[p_i]['id']]['watermarkwidth'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['watermarkheight']=strempty(a.cut[a.patform[p_i]['id']]['watermarkheight'])?0:a.cut[a.patform[p_i]['id']]['watermarkheight'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['positionx']=strempty(a.cut[a.patform[p_i]['id']]['positionx'])?0:a.cut[a.patform[p_i]['id']]['positionx'];
				CutOut_Store_zoom_data[a.patform[p_i]['Englishname']]['positiony']=strempty(a.cut[a.patform[p_i]['id']]['positiony'])?0:a.cut[a.patform[p_i]['id']]['positiony'];
			}
			//存储水印数据
			CutOut_Store_watermark[0]=new Object();
			CutOut_Store_watermark[0]['url']='';
			CutOut_Store_watermark[0]['id']=0;
			for(var w_i in a.watermark){
				CutOut_Store_watermark[a.watermark[w_i]['type']]=new Object();
				CutOut_Store_watermark[a.watermark[w_i]['type']]['url']=a.watermark[w_i]['url'];
				CutOut_Store_watermark[a.watermark[w_i]['type']]['id']=a.watermark[w_i]['id'];
			}
			//存储有裁剪图的裁剪模版
			for(var c_i in a.cut_id_arr){
				CutOut_cut_id_data[CutOut_obj_len(CutOut_cut_id_data)]=a.cut_id_arr[c_i];
			}
			CutOut_setStoreNum();
		}
	});
}
/**
* 获取图片裁剪信息
**/
function CutOut_getCutOutImg(){
	$.getJSON('/erp/getCutOutImg?Store_type='+CutOut_Store_val+'&skc='+brandnumber+'&sku='+img_sku,function(a){
		if(a.status==1){
			for(var i in a.data){
				//遍历暂存数据里是否有当前图片裁剪信息 获取裁剪信息数据标记为删除
				var is_bool=false;
				for(var set_i in CutOut_data[CutOut_Store_val]){
					if(CutOut_data[CutOut_Store_val][set_i]['imageid']==a.data[i]['imageid'] || array_search(a.data[i]['id'],CutOut_del_data)!==false){
						is_bool=true;
					}
				}
				if(!is_bool){
					var img_src=$('.cpics_list li.img_id_'+a.data[i]['imageid']+' .cpic_img img').attr('src');
					if(img_src==undefined){
						continue;
					}
					if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图 就不放大
						img_src='';
					}else{
						var img_src_arr=img_src.split('?imageView2');
						img_src=img_src_arr[0];
					}
					
					var data=new Object();
					data['push_id']=a.data[i]['id'];//推送ID 0表示还没有
					data['top']=a.data[i]['top'];//是否主图 默认不是
					data['sort']=a.data[i]['sort'];//图片的位置
					data['cutid']=a.data[i]['cutid'];//裁剪表erp_cut_deaile表的ID
					data['imageid']=a.data[i]['imageid'];//erp_image图片ID
					data['imagesrc']=img_src;//图片的地址
					data['pushstatus']=0;//推送状态
					data['sku']=a.data[i]['sku'];//sku
					data['skc']=a.data[i]['skc'];
					data['img_w']=a.data[i]['img_w'];
					data['img_h']=a.data[i]['img_h'];
					if(CutOut_data[CutOut_Store_val]==undefined){//当前平台还未添加裁剪数据
						CutOut_data[CutOut_Store_val]=new Object();
					}
					CutOut_data[CutOut_Store_val]['li_'+a.data[i]['sort']]=data;
					
				}
				if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]==undefined){//其裁剪数据未设置时 添加
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]=new Object();//设置图片的缩放尺寸
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['top']=a.data[i]['positiony'];
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['left']=a.data[i]['positionx'];
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['width']=a.data[i]['width'];
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['height']=a.data[i]['height'];
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['CutOut_width']=a.data[i]['CutOut_width'];
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['CutOut_height']=a.data[i]['CutOut_height'];
					CutOut_Store_zoom_data[CutOut_Store_val]['img_'+a.data[i]['imageid']]['watermarkid']=a.data[i]['watermarkid'];//水印信息表的ID
				}
			}
		}
		CutOut_show_data();
	})
}

/**
* 图片添加水印功能
**/
function CutOut_img_add_watermark(){
	var watermark_html='';
	for(var w_i in CutOut_Store_watermark){
		if(w_i==0){
			continue;
		}
		watermark_html+='<option value="'+w_i+'">水印'+w_i+'</option>';
	}

	var html='<div class="cpic_t1 watermark_div"><div class="watermark_dis dis_n">'
          	+'<select style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;float: left;margin-top: 13px;margin-left: 45px;" size="1" name="" onchange="CutOut_set_watermark(this)">'
			+'<option value="0">无水印</option>'
            + watermark_html
            +'</select>'
			+'&nbsp;&nbsp;<a style=" width:30px;padding:2px; margin-top:15px; font-size:18px; line-height:24px;background:#FFF; border:#333 1px solid; color:#000;float: left;margin-top: 13px;margin-left: 8px;font-family: monospace;" class="button button-primary button-rounded button-small addimg" onclick="CutOut_add_img(this)" CutOut_img_bool="false" title="加入做图区">＋</a>';
			if(CutOut_powers==1){//完整权限
			html+='&nbsp;&nbsp;<a class="button button-primary button-rounded button-small bigimg" onclick="CutOut_big_img(this)" style="line-height: 20px; border: 1px solid #000; font-size: 12px; margin-top: 0px; padding: 5px; width: 36px;float: left;margin-top: 13px;margin-left: 8px;" title="放大调整裁剪框">放大</a>';
			}
			html+='</div></div>';
	$('.cpics_list li').map(function(){
		if($(this).find('.cpic_admin a').attr('img_status')==1){//只有图片状态设为合格的 才可以裁剪
			$(this).prepend(html);
		}else{
			$(this).prepend('<div class="cpic_t1 watermark_div"></div>');
		}
	});
}
/**
* 图片删除水印按钮
**/
function CutOut_img_del_watermark(){
	$('.cpics_list li .watermark_div').remove();
}
/**
* 设置水印
**/
function CutOut_set_watermark(obj){
	var watermark_num=CutOut_Store_watermark[$(obj).closest('li').find('.watermark_dis select').val()]['id'];
	var imageid=$(obj).closest('li').attr('img_id2');
	if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]==undefined){
		CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]=new Object();
	}
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['watermarkid']=watermark_num;
	for(var i_i in CutOut_data[CutOut_Store_val]){
		if(CutOut_data[CutOut_Store_val][i_i]['imageid']==imageid){
			CutOut_data[CutOut_Store_val][i_i]['watermarkid']=watermark_num;
			break;
		}
	}
	CutOut_submit_bool=true;
}
/**
* 放大图片
**/
function CutOut_big_img(obj){
	var img_src=$(obj).closest('li').find('img').attr('src');
	if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图 就不放大
		return;
	}
	var img_src_arr=img_src.split('?imageView2');
	var imagesrc=img_src_arr[0];
	var imageid=$(obj).closest('li').attr('img_id2');
	
	var div_max_width=$(".container_div").width();//弹框的最大宽度
	var div_max_height=$(window).height()-82;//弹框的最大高度
	//输入的信息数组
	var input_array=new Array();
	input_array['width']=CutOut_Store_zoom_data[CutOut_Store_val]['w'];;
	input_array['height']=CutOut_Store_zoom_data[CutOut_Store_val]['h'];
	input_array['watermarkwidth']=CutOut_Store_zoom_data[CutOut_Store_val]['watermarkwidth'];
	input_array['watermarkheight']=CutOut_Store_zoom_data[CutOut_Store_val]['watermarkheight'];
	input_array['positionx']=CutOut_Store_zoom_data[CutOut_Store_val]['positionx'];
	input_array['positiony']=CutOut_Store_zoom_data[CutOut_Store_val]['positiony'];
	var clothes_width=$('.cpics_list .img_id_'+imageid).attr('img_w');//图片宽度
	var clothes_height=$('.cpics_list .img_id_'+imageid).attr('img_h');//图片高度
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
	var clohtes_img_width=parseInt(zoom_img_ratio*clothes_width);//图片最后显示的宽
	var clohtes_img_height=parseInt(zoom_img_ratio*clothes_height);//图片最后显示的高
	/*******************图片显示的宽高*******************end******************/
	
	$('.cpics_list li.img_id_'+imageid+' .watermark_dis a.bigimg').css({'background':null,'border':'1px solid #1b9af7','color':null});
	
	
	var html2='<div class="clothes_img" style="width:'+facebox_div_width+'px;height:'+facebox_div_height+'px;margin-top: 0px; opacity: 1;border: 1px solid #000;">'
		+'      <div  class="div_table"><div  class="div_table_cell"><img src="/images/482.GIF"/></div></div>'
		+'<div class="CutOut_li_img_box_class" style="cursor: auto;width:'+(facebox_div_width+2)+'px;height:'+(facebox_div_height+2)+'px;left:0;top:0">'
		+'<div class="Auxiliary_line" style="margin:auto"><img src="/images/Auxiliary_line.png"></div>'
		+'<div class="border1 CutOut_li_img_box_border_class" style="cursor: move;">'
		+'<div class="border1 CutOut_rLeftUp"></div>'
		+'<div class="border1 CutOut_rRightUp"></div>'
		+'<div class="border1 CutOut_rleftDown"></div>'
		+'<div class="border1 CutOut_rRightDown"></div>'
		+'</div>'
		+'</div>'
		+'</div>';
	
	var arr2=new Array();
	//arr2['facebox_title']="提示";
	arr2['close_but']=true;
	arr2['div']=html2;
	arr2['width']=(facebox_div_width+2)+'px';
	arr2['height']=(facebox_div_height+2)+'px';
	arr2['fun']=function(obj){
		obj.find('.ld_close').show().css('z-index',99);
        obj.css('background-color',"#fff");
		obj.find('.ld_close').click(function(){
			$('.cpics_list li.img_id_'+imageid+' .watermark_dis a.bigimg').css({'background':'#fff','border':'1px solid #333','color':'#000'});
		})
		var ht='<div class="li_hidden" style="position: absolute; top: 0px; height: 100%; width: 103%; left: -1%; z-index: 5;">'
			+'<div style="top: 0px; height: 100%; width: 100%; background-color: rgb(153, 153, 153); opacity: 0.45; position: absolute;"></div>'
			+'<div class="div_table">'
			+'<div class="div_table_cell" style=""><img src="/images/482.gif" style="width:40px;height:40px"/><br/>图片裁剪设置中，请稍等！</div>'
			+'</div>'
			+'</div>';
		obj.append(ht);
		/**
		* 裁剪框
		**/
		obj.attr('img_id2',imageid);
		obj.find('.CutOut_li_img_box_class').attr('id','facebox_CutOut_li_img_box_'+imageid);
		obj.find('.CutOut_li_img_box_border_class').attr('id','facebox_CutOut_li_img_box_border_'+imageid);
		obj.find('.CutOut_rLeftUp').attr('id','facebox_CutOut_rLeftUp_'+imageid);
		obj.find('.CutOut_rRightUp').attr('id','facebox_CutOut_rRightUp_'+imageid);
		obj.find('.CutOut_rRightDown').attr('id','facebox_CutOut_rRightDown_'+imageid);
		obj.find('.CutOut_rleftDown').attr('id','facebox_CutOut_rleftDown_'+imageid);
		var rs = new Resize('facebox_CutOut_li_img_box_border_'+imageid, { Max: true,Min:true, mxContainer: 'facebox_CutOut_li_img_box_'+imageid ,Scale:true,onResize_end:CutOut_big_img_facebox_onResize_end});//
		rs.Set('facebox_CutOut_rRightDown_'+imageid, "right-down");
		rs.Set('facebox_CutOut_rleftDown_'+imageid, "left-down");
		rs.Set('facebox_CutOut_rLeftUp_'+imageid, "left-up");
		rs.Set('facebox_CutOut_rRightUp_'+imageid, "right-up");
		new Drag('facebox_CutOut_li_img_box_border_'+imageid, { Limit: true, mxContainer: 'facebox_CutOut_li_img_box_'+imageid ,onStop:CutOut_big_img_facebox_onResize_end});//
		
		obj.find('.Auxiliary_line img').css({'width':(clohtes_img_width>clohtes_img_height?clohtes_img_width:clohtes_img_height)+'px','height':(clohtes_img_width>clohtes_img_height?clohtes_img_width:clohtes_img_height)+'px'});
		var current_image = new Image();
		current_image.src = imagesrc+'?imageView2/2/w/'+clohtes_img_width+'/h/'+clohtes_img_height;
		current_image.onload=function(){
			obj.find('.clothes_img .div_table_cell img').attr('src',imagesrc+'?imageView2/2/w/'+clohtes_img_width+'/h/'+clohtes_img_height);
			obj.css({'top':($(window).height()-obj.height())/2,'left':($(window).width()-obj.width())/2})
			CutOut_get_img_src(obj,1);
			obj.find('.CutOut_li_img_box_border_class').css({width:'100%',height:'100%',left:0,top:0})
			CutOut_big_img_facebox_minResize(obj,rs);
		};
	}
	facebox(arr2);
}
/**
* 图片放大窗口设置
**/
function CutOut_big_img_facebox_minResize(obj,rs,num){
	var num=num?num:1;
	
	//图片缩放比例
	var zoom_ratio=obj.attr('zoom_ratio')==undefined?0:Number(obj.attr('zoom_ratio'));
	if(zoom_ratio==0){
		num++;
		if(num>10){
			return;
		}
		setTimeout(function(){
			CutOut_big_img_facebox_minResize(obj,rs,num);
		},1000);
		return;
	}
	rs.Ratio=CutOut_Store_zoom_data[CutOut_Store_val]['zoom_ratio'];
	var minWidth=Math.ceil(CutOut_Store_zoom_data[CutOut_Store_val]['w']*zoom_ratio);
	var minHeight=Math.ceil(CutOut_Store_zoom_data[CutOut_Store_val]['h']*zoom_ratio);
	if(minWidth>$('.facebox').width() || minHeight>$('.facebox').height()){
		minWidth=$('.facebox').width();
		minHeight=$('.facebox').height();
	}
	rs.minWidth=minWidth;//最小宽度
	rs.minHeight=minHeight//最小高度
	
	/**
	* 恢复裁剪大小
	**/
	var imageid=obj.attr('img_id2');
	var parm_arr=CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid];
	if(parm_arr==undefined){
		return;
	}
	var boxs_width=$(obj).width();//外框宽度
	var boxs_height=$(obj).height();//外框高度
	var img_width=obj.attr('current_width');//图片宽度
	var img_height=obj.attr('current_height');//图片高度
	var zoom_ratio=obj.attr('zoom_ratio');//获取缩放控件对应的图片 的缩放比例
	var img_left=(boxs_width-img_width)/2;
	var img_top=(boxs_height-img_height)/2;
	var Resize_top=parm_arr['top']*zoom_ratio+img_top;
	var Resize_left=parm_arr['left']*zoom_ratio+img_left;
	var Resize_width=parm_arr['width']*zoom_ratio+img_left-Resize_left;
	var Resize_height=parm_arr['height']*zoom_ratio+img_top-Resize_top;
	if(parm_arr['top']==0 && parm_arr['left']==0 && parm_arr['width']==0 && parm_arr['height']==0){
		Resize_top=0;
		Resize_left=0;
		Resize_width='100%';
		Resize_height='100%';
	}
	obj.find('.CutOut_li_img_box_border_class').css({'width':Resize_width,'height':Resize_height,'top':Resize_top,'left':Resize_left});
	obj.find('.li_hidden').remove();
}
/**
* 停止裁剪后 回调函数
**/
function CutOut_big_img_facebox_onResize_end(obj_id){
	var img_id=$('#'+obj_id).closest('.facebox').attr('img_id2');//获取缩放控件对应的图片ID
	var boxs_height=$('#'+obj_id).closest('.facebox').height();//外框高度
	var boxs_width=$('#'+obj_id).closest('.facebox').width();//外框宽度
	var img_width=$('#'+obj_id).closest('.facebox').attr('current_width');//图片宽度
	var img_height=$('#'+obj_id).closest('.facebox').attr('current_height');//图片高度
	var zoom_ratio=$('#'+obj_id).closest('.facebox').attr('zoom_ratio');//获取缩放控件对应的图片 的缩放比例
	var Resize_width=$('#'+obj_id).find('.CutOut_li_img_box_border_class').width();//获取缩放控件的宽
	var Resize_height=$('#'+obj_id).find('.CutOut_li_img_box_border_class').height();//获取缩放控件的高
	var Resize_top=parseInt($('#'+obj_id).find('.CutOut_li_img_box_border_class').css('top'));//获取缩放控件的高
	var Resize_left=parseInt($('#'+obj_id).find('.CutOut_li_img_box_border_class').css('left'));//获取缩放控件的高
	var img_left=(boxs_width-img_width)/2;
	var img_top=(boxs_height-img_height)/2;
	
	//设置图片的缩放尺寸
	if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]==undefined){
		CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]=new Object();
	}
	
	var CutOut_img_width=0;//图片需要裁剪的宽度
	var CutOut_img_height=0;//图片需要裁剪的高度
	var Resize_end_x=Resize_width+Resize_left;//缩放x轴结束点
	var current_img_end_x=img_width+img_left;//当前图片x轴结束点
	var Resize_end_y=Resize_height+Resize_top;//缩放y轴结束点
	var current_img_end_y=img_height+img_top;//当前图片y轴结束点
	if(Resize_end_x>current_img_end_x){//缩放x轴结束点在当前图片x轴外 将结束位置改变为图片结束位置
		Resize_end_x=current_img_end_x;
	}
	if(Resize_end_y>current_img_end_y){//缩放y轴结束点在当前图片y轴外 将结束位置改变为图片结束位置
		Resize_end_y=current_img_end_y;
	}
	if(Resize_left<=img_left){//缩放x轴左偏移量在当前图片x轴外 将结束位置改变为图片结束位置
		CutOut_img_width=Resize_end_x-img_left;
	}else{
		CutOut_img_width=Resize_end_x-Resize_left;
	}
	if(Resize_top<=img_top){//缩放x轴左偏移量在当前图片x轴外 将结束位置改变为图片结束位置
		CutOut_img_height=Resize_end_y-img_top;
	}else{
		CutOut_img_height=Resize_end_y-Resize_top;
	}
	if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]==undefined){
		CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]=new Object();
	}
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['CutOut_width']=Math.ceil(CutOut_img_width/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['CutOut_height']=Math.ceil(CutOut_img_height/zoom_ratio);
	
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['top']=Math.ceil((Resize_top-img_top)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['left']=Math.ceil((Resize_left-img_left)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['width']=Math.ceil((Resize_width+Resize_left-img_left)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['height']=Math.ceil((Resize_height+Resize_top-img_top)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['boxs_height']=boxs_height;
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['boxs_width']=boxs_width;
	for(var i in CutOut_data[CutOut_Store_val]){
		if(CutOut_data[CutOut_Store_val][i]['imageid']==img_id){
			CutOut_data[CutOut_Store_val][i]['pushstatus']=0;
		}
	}
	var pram_arr=CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id];
	pram_arr['imgid']=img_id;
	CutOut_img_box_default(pram_arr);
}
/**
* 恢复水印
**/
function CutOut_recovery_watermark(imageid,watermarkid){
	var watermark_type=0;
	for(var w_i in CutOut_Store_watermark){
		if(CutOut_Store_watermark[w_i]['id']==watermarkid){
			watermark_type=w_i;
			break;
		}
	}
	if(CutOut_Store_zoom_data[CutOut_Store_val]['watermarkwidth']!=0 && CutOut_Store_zoom_data[CutOut_Store_val]['watermarkheight']!=0){
		$('.cpics_list .img_id_'+imageid+' .watermark_dis select').val(watermark_type);
	}
}
/**
* 给图片添加裁剪框
**/
function CutOut_img_add_box(img_id){
	var img_obj=$('.cpics_list li.img_id_'+img_id);
	if($('.cpics_list li.img_id_'+img_id+' .CutOut_li_img_box_class').length==0){
		var html='<div class="CutOut_li_img_box_class dis_n">'
		+'<div><img src="/images/Auxiliary_line.png"></div>'
		+'<div class="border1 CutOut_li_img_box_border_class" style="cursor: move;">'
		+'<div class="border1 CutOut_rLeftUp"></div>'
		+'<div class="border1 CutOut_rRightUp"></div>'
		+'<div class="border1 CutOut_rleftDown"></div>'
		+'<div class="border1 CutOut_rRightDown"></div>'
		+'</div>'
		+'</div>';
		$(img_obj).find('.cpic_img').append(html);
	}
	
	$(img_obj).find('.CutOut_li_img_box_class').attr('id','CutOut_li_img_box_'+img_id);
	$(img_obj).find('.CutOut_li_img_box_border_class').attr('id','CutOut_li_img_box_border_'+img_id);
	$(img_obj).find('.CutOut_rLeftUp').attr('id','CutOut_rLeftUp_'+img_id);
	$(img_obj).find('.CutOut_rRightUp').attr('id','CutOut_rRightUp_'+img_id);
	$(img_obj).find('.CutOut_rRightDown').attr('id','CutOut_rRightDown_'+img_id);
	$(img_obj).find('.CutOut_rleftDown').attr('id','CutOut_rleftDown_'+img_id);
	var rs = new Resize('CutOut_li_img_box_border_'+img_id, { Max: true,Min:true, mxContainer: 'CutOut_li_img_box_'+img_id ,Scale:true,onResize_end:CutOut_onResize_end});
	rs.Set('CutOut_rRightDown_'+img_id, "right-down");
	rs.Set('CutOut_rleftDown_'+img_id, "left-down");
	rs.Set('CutOut_rLeftUp_'+img_id, "left-up");
	rs.Set('CutOut_rRightUp_'+img_id, "right-up");
	new Drag('CutOut_li_img_box_border_'+img_id, { Limit: true, mxContainer: 'CutOut_li_img_box_'+img_id ,onStop:CutOut_onResize_end});
	CutOut_Resize_obj_data[img_id]=rs;
	
	//$('.img_id_'+img_id+' .li_hidden').remove();
	$('.img_id_'+img_id+' .watermark_dis').removeClass('dis_n');//显示水印按钮
	$('.img_id_'+img_id+' .CutOut_li_img_box_class').removeClass('dis_n');//显示裁剪框
	
	CutOut_set_min_ratio(img_id);//设置裁剪框缩放比例
	
	if(CutOut_Store_val==0){
		return;
	}
	
	var li_show_data=$('.img_id_'+img_id).attr('show_data');
	if(li_show_data+''!='true'){
		CutOut_img_box_default(undefined,img_id);
		$('.img_id_'+img_id+' .li_hidden').remove();
	}
}
/**
* 设置图片缩放比例 及其最小尺寸
**/
function CutOut_set_min_ratio(img_id,num){
	if(CutOut_Store_val==0){
		return;
	}
	var num=num?num:1;
	var is_bool=true;
	var img_id=img_id?img_id:0;
	var c_data=CutOut_Resize_obj_data;
	if(img_id!=0){
		c_data=new Array();
		c_data[img_id]=CutOut_Resize_obj_data[img_id];
	}
	//图片缩放比例
	for(var imgid in c_data){
		var zoom_ratio=$('.img_id_'+imgid).attr('zoom_ratio')==undefined?0:Number($('.img_id_'+imgid).attr('zoom_ratio'));
		if(zoom_ratio==0){
			break;
		}
		CutOut_Resize_obj_data[imgid].Ratio=CutOut_Store_zoom_data[CutOut_Store_val]['zoom_ratio'];
		var minWidth=Math.ceil(CutOut_Store_zoom_data[CutOut_Store_val]['w']*zoom_ratio);
		var minHeight=Math.ceil(CutOut_Store_zoom_data[CutOut_Store_val]['h']*zoom_ratio);
		if(minWidth>268 || minHeight>268){
			minWidth=268;
			minHeight=268;
		}
		CutOut_Resize_obj_data[imgid].minWidth=minWidth;//最小宽度
		CutOut_Resize_obj_data[imgid].minHeight=minHeight;//最小高度
	}
	
	if(!is_bool){
		num++;
		if(num>20){
			return;
		}
		
		setTimeout(function(){
			CutOut_set_min_ratio(img_id,num);
		},1000);
	}	
}
/**
* 图片刷新
**/
function img_Refresh(obj,imgid){
	CutOut_get_img_src($(obj).closest('li').find('.cpic_img'));
	CutOut_set_min_ratio(imgid)
}
/**
* 提示弹窗
**/
function CutOut_Prompt_pop(txt,fun){
	var html='<div class="popup">'
	+'<div class="p_main">'
	+'<div style="margin:0 auto; line-height:130px;" class=" w340 pad_t50 t_16b">'
	+(txt?txt:'温馨提示！')
	+'</div>'
	+'</div>'
	+'</div>';
	var p_arr=new Array();
	p_arr['facebox_title']="提示";
	p_arr['div']=html;
	p_arr['facebox_over_close']=true;
	p_arr['fun']=fun?fun:function(facebox_obj){};
	facebox(p_arr);
	return;
}
/**
* 停止裁剪后 回调函数
**/
function CutOut_onResize_end(obj_id){
	var img_id=$('#'+obj_id).closest('li').attr('img_id2');//获取缩放控件对应的图片ID
	var boxs_height=$('#'+obj_id).closest('.cpic_img').height();//外框高度
	var boxs_width=$('#'+obj_id).closest('.cpic_img').width();//外框宽度
	var img_width=$('#'+obj_id).closest('li').attr('current_width');//图片宽度
	var img_height=$('#'+obj_id).closest('li').attr('current_height');//图片高度
	var zoom_ratio=$('#'+obj_id).closest('li').attr('zoom_ratio');//获取缩放控件对应的图片 的缩放比例
	var Resize_width=$('#'+obj_id).find('.CutOut_li_img_box_border_class').width();//获取缩放控件的宽
	var Resize_height=$('#'+obj_id).find('.CutOut_li_img_box_border_class').height();//获取缩放控件的高
	var Resize_top=parseInt($('#'+obj_id).find('.CutOut_li_img_box_border_class').css('top'));//获取缩放控件的高
	var Resize_left=parseInt($('#'+obj_id).find('.CutOut_li_img_box_border_class').css('left'));//获取缩放控件的高
	var img_left=(boxs_width-img_width)/2;
	var img_top=(boxs_height-img_height)/2;
	
	//设置图片的缩放尺寸
	if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]==undefined){
		CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]=new Object();
	}
	
	var CutOut_img_width=0;//图片需要裁剪的宽度
	var CutOut_img_height=0;//图片需要裁剪的高度
	var Resize_end_x=Resize_width+Resize_left;//缩放x轴结束点
	var current_img_end_x=img_width+img_left;//当前图片x轴结束点
	var Resize_end_y=Resize_height+Resize_top;//缩放y轴结束点
	var current_img_end_y=img_height+img_top;//当前图片y轴结束点
	if(Resize_end_x>current_img_end_x){//缩放x轴结束点在当前图片x轴外 将结束位置改变为图片结束位置
		Resize_end_x=current_img_end_x;
	}
	if(Resize_end_y>current_img_end_y){//缩放y轴结束点在当前图片y轴外 将结束位置改变为图片结束位置
		Resize_end_y=current_img_end_y;
	}
	if(Resize_left<=img_left){//缩放x轴左偏移量在当前图片x轴外 将结束位置改变为图片结束位置
		CutOut_img_width=Resize_end_x-img_left;
	}else{
		CutOut_img_width=Resize_end_x-Resize_left;
	}
	if(Resize_top<=img_top){//缩放x轴左偏移量在当前图片x轴外 将结束位置改变为图片结束位置
		CutOut_img_height=Resize_end_y-img_top;
	}else{
		CutOut_img_height=Resize_end_y-Resize_top;
	}
	
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['CutOut_width']=Math.ceil(CutOut_img_width/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['CutOut_height']=Math.ceil(CutOut_img_height/zoom_ratio);
	
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['top']=Math.ceil((Resize_top-img_top)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['left']=Math.ceil((Resize_left-img_left)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['width']=Math.ceil((Resize_width+Resize_left-img_left)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['height']=Math.ceil((Resize_height+Resize_top-img_top)/zoom_ratio);
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['boxs_height']=boxs_height;
	CutOut_Store_zoom_data[CutOut_Store_val]['img_'+img_id]['boxs_width']=boxs_width;
	for(var i in CutOut_data[CutOut_Store_val]){
		if(CutOut_data[CutOut_Store_val][i]['imageid']==img_id){
			CutOut_data[CutOut_Store_val][i]['pushstatus']=0;
		}
	}
}
/**
* 图片裁剪框恢复默认
**/
function CutOut_img_box_default(parm_arr,imgid){
	if(parm_arr==undefined){
		if(imgid!=undefined){
			$('#CutOut_li_img_box_border_'+imgid).css({'width':'100%','height':'100%','top':0,'left':0});
			$('.cpics_list li.img_id_'+imgid+' .addimg').attr('cutout_img_bool',false);
		}else{
			$('.CutOut_li_img_box_border_class').css({'width':'100%','height':'100%','top':0,'left':0});
			$('.cpics_list li .addimg').attr('cutout_img_bool',false);
		}
	}else{
		var li_obj=$('.cpics_list li.img_id_'+parm_arr['imgid']);
		var boxs_height=268;//外框高度
		var boxs_width=268;//外框宽度
		var img_width=li_obj.attr('current_width');//图片宽度
		var img_height=li_obj.attr('current_height');//图片高度
		var zoom_ratio=li_obj.attr('zoom_ratio');//获取缩放控件对应的图片 的缩放比例
		var img_left=(boxs_width-img_width)/2;
		var img_top=(boxs_height-img_height)/2;
		
		var Resize_top=parm_arr['top']*zoom_ratio+img_top;
		var Resize_left=parm_arr['left']*zoom_ratio+img_left;
		var Resize_width=parm_arr['width']*zoom_ratio+img_left-Resize_left;
		var Resize_height=parm_arr['height']*zoom_ratio+img_top-Resize_top;
		if(parm_arr['top']==0 && parm_arr['left']==0 && parm_arr['width']==0 && parm_arr['height']==0){
			Resize_top=0;
			Resize_left=0;
			Resize_width='100%';
			Resize_height='100%';
		}
		li_obj.find('.CutOut_li_img_box_border_class').css({'width':Resize_width,'height':Resize_height,'top':Resize_top,'left':Resize_left});
	}
}

/**
* 删除图片裁剪框
**/
function CutOut_img_del_box(){
	$('.CutOut_li_img_box_class').remove();
	$('.cpics_list li .cpic_img').css('border',null);
}
/**
* 添加图片到裁剪栏
**/
function CutOut_add_img(obj){
	if(CutOut_powers==0){//部分权限 禁止添加
		return;
	}
	if($(obj).attr('CutOut_img_bool')+''=='false'){//未添加过的图片才可以添加
		var img_src=$(obj).closest('li').find('.cpic_img img').attr('src');//获取图片地址
		if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图
			return;
		}
		var img_src_arr=img_src.split('?imageView2');
		var img_num=CutOut_addcon(img_src_arr[0],-1,$(obj).closest('li').attr('img_id2'));//添加图片到裁剪栏
		if(img_num==-1){
			return;
		}
		var data=new Object();
		data['push_id']=0;//推送ID 0表示还没有
		data['top']=0;//是否主图 默认不是
		data['sort']=img_num;//图片的位置
		data['cutid']=0;//裁剪表erp_cut_deaile表的ID
		data['imageid']=$(obj).closest('li').attr('img_id2');//erp_image图片ID
		data['imagesrc']=img_src_arr[0];//图片的地址
		data['pushstatus']=0;//推送状态
		data['sku']=img_sku;//sku
		data['coid']=$(obj).closest('li').attr('c_o_id');//skc
		data['img_w']=$(obj).closest('li').attr('img_w');
		data['img_h']=$(obj).closest('li').attr('img_h');
		CutOut_data[CutOut_Store_val]['li_'+img_num]=data;
		
		if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]==undefined){//其裁剪数据未设置时 添加
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]=new Object();//设置图片的缩放尺寸	
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['top']=0;
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['left']=0;
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['width']=0;
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['height']=0;
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['CutOut_width']=0;
			CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['CutOut_height']=0;
		}
		CutOut_Store_zoom_data[CutOut_Store_val]['img_'+data['imageid']]['watermarkid']=CutOut_Store_watermark[$(obj).closest('li').find('.watermark_dis select').val()]['id'];//水印信息表的ID
		
		CutOut_submit_bool=true;
	}
}


/**
* 裁剪图片栏
**/
function CutOut_boxs(){
	var li_html='';
	for(var i=1;i<=8;i++){
		li_html+='<li class="CutOut_i_'+i+'">'
		+'<div class="border1 h129">'
		+'<div class="w22 cloths_id">'+i+'</div>'
		+'<div class="w22 cloths_x dis_n" style="cursor: pointer;" onclick="CutOut_del_img(this)">×</div>'
		+'<div class="line129 CutOut_li_img_btn">'
		+'未添加'
		+'</div>'
		+'</div>'
		+'</li>';
	}
	var Store_html='';
	for (var i in CutOut_Store_zoom_data){
		var str_template='(款里选)';
		if(CutOut_Store_zoom_data[i]['barcodetype']!=1){
			str_template='(色里选)';
		}
		Store_html+='<option value="'+i+'">'+CutOut_Store_zoom_data[i]['name']+str_template+'</option>';
	}
	var html='<div class="m_body7 dis"><!-- dis_n 用于隐藏-->'
	+'<div class="navbar-wrapper img_edit_tc border1"><div class="navwrapper">'
   	+'<div class="fl w1000">'
    +'<div class="cloths_pic3 pad_t10">'
    +'<ul>'
    +li_html
    +'</ul>'
    +'</div>'
    +'</div>'
    +'<div class="fr w184">'
    +'<div>'
    +'<select style="width:120px; height:30px; line-height:30px; margin:30px 0; font-size:12px;" size="1" name="" onchange="CutOut_setStore(this)">'
    +'<option value="0">选择推送平台</option>'
    +Store_html
    +'</select>'
    +'</div>'
    +'<div>'
    +'<a style="padding:0 15px;" class="button button-primary button-rounded button-small" onclick="CutOut_submit()">保存</a>&#12288;<a style="padding:0 15px;" class="button button-primary button-rounded button-small" onclick="CutOut_end()">退出</a>'
    +'</div>'
    +'</div>'
	+'</div>'
	+'</div></div>';
	$('.container_div').prepend(html);
	if(CutOut_powers==0){//部分权限
		$('.container_div .m_body7 .cloths_x').remove();
	}
	CutOut_boxs_scroll();
	$(document).bind('scroll',CutOut_boxs_scroll);
}

/**
* 获取平台并读取其内容
**/
function CutOut_setStore(obj){
	if(CutOut_Store_val==0 || !CutOut_submit_bool){
		CutOut_Store_fun(obj);
		return;
	}
	var html='<div class="popup">'
	+'<div class="p_main">'
	+'<div class=" pad_t50 line28 text-center" style="padding-top:77px;">内容有所调整，请先点击"保存"按钮后再切换平台</div>'
	+'<div class=" w256" style="margin:0 auto">'
	+'<a class="button button-primary button-rounded button-small facebox_btn_close" style=" width:120px; padding:0 5px;margin-top:15px">关闭提示</a>'
	+'&nbsp;&nbsp;<a class="button button-primary button-rounded button-small facebox_btn_submit" style=" width:120px; padding:0 5px;margin-top:15px">不保存切换</a>'
	+'</div>'
	+'</div>'
	+'</div>';
	var p_arr=new Array();
	p_arr['div']=html;
	p_arr['facebox_title']='提示';
	p_arr['fun']=function(facebox_obj){
		facebox_obj.find('.facebox_btn_submit').bind('click',function(){
			CutOut_Store_fun(obj);
			CutOut_submit_bool=false;
			facebox_obj.find('.ld_close').click();
			//document.removeEventListener("beforeunload", onclose, false);
		});
		facebox_obj.find('.facebox_btn_close').bind('click',function(){
			$('.m_body7 select').val(CutOut_Store_val);
			facebox_obj.find('.ld_close').click();
		});
	};
	facebox(p_arr);
	
}
/**
* 平台切换
**/
function CutOut_Store_fun(obj){
	CutOut_Store_val=$(obj).val();
	cookie_Website_template=getCookie('Website_template');
	CutOut_setCookie();//将数据存入cookie
	if(CutOut_Store_val!=0){//当其有选择平台时 判断平台类型
		if(CutOut_Store_zoom_data[CutOut_Store_val]['barcodetype']==1){
			CutOut_barcode_template='SKU';
		}else{
			CutOut_barcode_template='SKC';
		}
		if(cookie_Website_template!=CutOut_barcode_template){//需要切换页面
			$('.Website_template').click();
			return;
		}
	}
	//切换平台 先将数据还原
	for(var i=1;i<=8;i++){
		CutOut_move_default(i);
	}
	//添加按钮恢复默认
	$('.watermark_dis a').css({'background':'#FFF','border':'#333 1px solid','color':'#000'});
	//水印列表恢复默认
	if(CutOut_Store_zoom_data[CutOut_Store_val]!=undefined && CutOut_Store_zoom_data[CutOut_Store_val]['havewatermark']==0){
		$('.watermark_dis select').val(1);
	}else{
		$('.watermark_dis select').val(0);
	}
	$('.cpics_list li .watermark_dis select option').show();
	//图片裁剪框恢复默认
	CutOut_img_box_default();
	$('.cpics_list li').attr('show_data',false);
		if(CutOut_Store_val==0){
			// 隐藏水印功能
			$('.watermark_dis').addClass('dis_n');
			// 隐藏裁剪框
			$('.CutOut_li_img_box_class').addClass('dis_n');
		}else{//切换不同的平台
			$('.cpics_list li').map(function(){
				if($(this).find('.cpic_admin a').attr('img_status')==1){//只有图片状态设为合格的 才可以裁剪
					CutOut_get_img_src(this);
				}
			});
			//当水印未设置尺寸时 禁用
			if(CutOut_Store_zoom_data[CutOut_Store_val]['watermarkwidth']==0 || CutOut_Store_zoom_data[CutOut_Store_val]['watermarkheight']==0){
				$('.cpics_list li .watermark_dis select').map(function(){
					$(this).find('option').hide();
					$(this).find('option').eq(0).show();
				});
			}
			CutOut_getCutOutImg();//查询已存在的数据
			CutOut_set_min_ratio();//设置图片缩放比例 及其最小尺寸
			if(CutOut_powers==0){//部分权限
				$('.cpics_list li .watermark_dis').removeClass('dis_n');//显示水印按钮
				$('.cpics_list li .watermark_dis .addimg').hide();//显示水印按钮
			}
		}
	
}
/**
* 显示平台裁剪数据
**/
function CutOut_show_data(){
	if(CutOut_data[CutOut_Store_val]==undefined){//当前平台还未添加裁剪数据
		CutOut_data[CutOut_Store_val]=new Object();
	}else{
		for(var i in CutOut_data[CutOut_Store_val]){
			CutOut_addcon(CutOut_data[CutOut_Store_val][i]['imagesrc'],CutOut_data[CutOut_Store_val][i]['sort'],CutOut_data[CutOut_Store_val][i]['imageid']);
			if(CutOut_data[CutOut_Store_val][i]['top']==1){//勾选主图
				CutOut_istop($('.m_body7 .cloths_pic3 li.CutOut_i_'+CutOut_data[CutOut_Store_val][i]['sort']+' .CutOut_li_img_btn .img_tipsd'),1);
			}
			
		}
		//恢复裁剪框
		for(var i in CutOut_Store_zoom_data[CutOut_Store_val]){
			if(typeof(CutOut_Store_zoom_data[CutOut_Store_val][i])!='object'){
				continue;
			}
			CutOut_img_add_Mask(i.replace(/img_/, ""));
			CutOut_show_data_fun(i);
		}
	}
}

function CutOut_show_data_fun(i,num){
	var num=num?num:0;
	if($('.img_id_'+i.replace(/img_/, "")+' .CutOut_li_img_box_class').length==0){
		num++;
		if(num>50){
			return;
		}
		setTimeout(function(){
			CutOut_show_data_fun(i,num);
		},1000);
		return ;
	}
	$('.img_id_'+i.replace(/img_/, "")).attr('show_data',true);//表示图片裁剪由数据恢复
	var pram_arr=CutOut_Store_zoom_data[CutOut_Store_val][i];
	pram_arr['imgid']=i.replace(/img_/, "");
	CutOut_img_box_default(pram_arr);
	CutOut_recovery_watermark(pram_arr['imgid'],pram_arr['watermarkid']);//恢复水印
	$('.img_id_'+i.replace(/img_/, "")+' .li_hidden').remove();
	
}
/**
* 添加裁剪内容
* img_url 图片地址 
* img_num 图片的位置 -1 为自动定位
* return 返回图片添加的位置
**/
function CutOut_addcon(img_url,img_num,imageid){
	if(img_num==-1){//定位需要添加的位置
		$('.m_body7 .cloths_pic3 li').map(function(){
			if(img_num==-1 && $(this).find('.CutOut_li_img').length==0){
				img_num=$(this).find('.cloths_id').html();
			}
		});
	}
	if(img_num==-1){//已添加满
		Popup('选择图片已达上限！');
		return img_num;
	}
	if($('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .border1 .CutOut_li_img').length>0){
		CutOut_move_default(img_num);
	}
	if(img_url==undefined && imageid==undefined){//当图片与其ID都不存在的情况下就不添加数据
		return;
	}
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .border1').prepend('<div class="CutOut_li_img div_table" style="height:107px"><div class="div_table_cell"><img  onerror="imgonerror(this,108,107)" src="'+img_url+'?imageView2/2/w/108/h/107"></div></div>');
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .CutOut_li_img_btn').html('<a style="padding:0; margin:0 1px; width:33px;height:22px;line-height:22px" class="button button-primary button-rounded button-small" onclick="CutOut_move_left(this)">左移</a><a style="padding:0; margin:0 1px; width:33px;height:22px;line-height:22px" class="button button-primary button-rounded button-small preview_a" onclick="CutOut_preview(this)">预览</a><a style="padding:0; margin:0 1px; width:33px;height:22px;line-height:22px" class="button button-primary button-rounded button-small"  onclick="CutOut_move_right(this)">右移</a>');
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .CutOut_li_img_btn').append('<div class="img_tipsd" style="cursor: pointer;" onclick="CutOut_istop(this)">主</div>');
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .border1 .cloths_x').removeClass('dis_n');
	
	//水印按钮调整
	$('.cpics_list li.img_id_'+imageid+' .watermark_dis a.addimg').attr('CutOut_img_bool','true').css({'background':null,'border':'1px solid #1b9af7','color':null}).show();
	$('.cpics_list li.img_id_'+imageid).attr('show_data',true);
	//按钮调整
	var li_end_num=$('.m_body7 .cloths_pic3 li').length;
	var li_start_html=$('.m_body7 .cloths_pic3 li.CutOut_i_1 .CutOut_li_img_btn').html();
	var li_end_html=$('.m_body7 .cloths_pic3 li.CutOut_i_'+li_end_num+' .CutOut_li_img_btn').html();
	if(li_start_html!='未添加'){
		$('.m_body7 .cloths_pic3 li.CutOut_i_1 .CutOut_li_img_btn a').eq(0).css({'color':'#cfcfcf','background-color': '#eee','cursor':'default'});
	}
	if(li_end_html!='未添加'){
		$('.m_body7 .cloths_pic3 li.CutOut_i_'+li_end_num+' .CutOut_li_img_btn a').eq(2).css({'color':'#cfcfcf','background-color': '#eee','cursor':'default'});
	}
	return img_num;
}
/**
* 裁剪框恢复默认
**/
function CutOut_move_default(img_num){
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .border1 .CutOut_li_img').remove();//删除图片
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .border1 .cloths_x').addClass('dis_n');//隐藏删除按钮
	$('.m_body7 .cloths_pic3 li.CutOut_i_'+img_num+' .CutOut_li_img_btn').html('未添加');//填写默认内容
}
/**
* 图片左移动
**/
function CutOut_move_left(obj){
	var li_num=parseInt($(obj).closest('.CutOut_li_img_btn').prevAll('.cloths_id').html());
	if(li_num==1){
		return;
	}
	var current_data=CutOut_data[CutOut_Store_val]['li_'+li_num];//获取当前位置图片的裁剪数据
	if(CutOut_data[CutOut_Store_val]['li_'+(li_num-1)]==undefined){
		CutOut_data[CutOut_Store_val]['li_'+(li_num-1)]=new Object();
	}
	var left_data=CutOut_data[CutOut_Store_val]['li_'+(li_num-1)];//获取当前位置左侧图片的裁剪数据
	left_data['sort']=li_num;
	current_data['sort']=li_num-1;
	CutOut_data[CutOut_Store_val]['li_'+li_num]=left_data;
	CutOut_data[CutOut_Store_val]['li_'+(li_num-1)]=current_data;
	CutOut_addcon(left_data['imagesrc'],left_data['sort'],left_data['imageid']);
	CutOut_addcon(current_data['imagesrc'],current_data['sort'],current_data['imageid']);
	if(left_data['top']==1){//勾选主图
		CutOut_istop($('.m_body7 .cloths_pic3 li.CutOut_i_'+left_data['sort']+' .CutOut_li_img_btn .img_tipsd'),1);
	}
	if(current_data['top']==1){//勾选主图
		CutOut_istop($('.m_body7 .cloths_pic3 li.CutOut_i_'+current_data['sort']+' .CutOut_li_img_btn .img_tipsd'),1);
	}
	CutOut_submit_bool=true;
}
/**
* 图片右移动
**/
function CutOut_move_right(obj){
	var li_num=parseInt($(obj).closest('.CutOut_li_img_btn').prevAll('.cloths_id').html());
	if(li_num==8){
		return;
	}
	var current_data=CutOut_data[CutOut_Store_val]['li_'+li_num];//获取当前位置图片的裁剪数据
	if(CutOut_data[CutOut_Store_val]['li_'+(li_num+1)]==undefined){
		CutOut_data[CutOut_Store_val]['li_'+(li_num+1)]=new Object();
	}
	var right_data=CutOut_data[CutOut_Store_val]['li_'+(li_num+1)];//获取当前位置右侧图片的裁剪数据
	right_data['sort']=li_num;
	current_data['sort']=li_num+1;
	CutOut_data[CutOut_Store_val]['li_'+li_num]=right_data;
	CutOut_data[CutOut_Store_val]['li_'+(li_num+1)]=current_data;
	CutOut_addcon(right_data['imagesrc'],right_data['sort'],right_data['imageid']);
	CutOut_addcon(current_data['imagesrc'],current_data['sort'],current_data['imageid']);
	if(right_data['top']==1){//勾选主图
		CutOut_istop($('.m_body7 .cloths_pic3 li.CutOut_i_'+right_data['sort']+' .CutOut_li_img_btn .img_tipsd'),1);
	}
	if(current_data['top']==1){//勾选主图
		CutOut_istop($('.m_body7 .cloths_pic3 li.CutOut_i_'+current_data['sort']+' .CutOut_li_img_btn .img_tipsd'),1);
	}
	CutOut_submit_bool=true;
}
/**
* 图片预览
**/
function CutOut_preview(obj,is_bool){
	var is_bool=is_bool?is_bool:false;
	if($('.preview_facebox').length>0){
		is_bool=true;
	}
	var li_num=parseInt($(obj).closest('.CutOut_li_img_btn').prevAll('.cloths_id').html());
	
	
	var div_max_width=$(".container_div").width();//弹框的最大宽度
	var div_max_height=$(window).height()-6;//弹框的最大高度
	//输入的信息数组
	var input_array=new Array();
	input_array['width']=CutOut_Store_zoom_data[CutOut_Store_val]['w'];;
	input_array['height']=CutOut_Store_zoom_data[CutOut_Store_val]['h'];
	var imageid=CutOut_data[CutOut_Store_val]['li_'+li_num]['imageid'];
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
	
	
	if(!is_bool){
		var CutOut_li_span_html='';
		var facebox_width=0;
		$('.m_body7 .cloths_pic3 li').map(function(){
			var CutOut_li_img_src=$(this).find('img').attr('src');
			if(CutOut_li_img_src!=undefined){
				var li_span_num=$(this).find('.cloths_id').html();
				var color_str='border1';
				if(li_span_num==li_num){
					color_str='border5';
				}
				CutOut_li_span_html+='<span class="'+color_str+'" style="display: inline-block; margin: 0px 4px; width: 108px; height: 107px;"><div  class="div_table"><div  class="div_table_cell"><img src="'+CutOut_li_img_src+'" onerror="imgonerror(this,108,107)" li_num="'+li_span_num+'"></div></div></span>';
				facebox_width+=118;
			}
		});
		facebox_width+=20;
		
		//设置图片的缩放
		var img_CutOut_h=200;
		var img_CutOut_w=0;
		var kk_h=facebox_div_height;
		if((facebox_div_height-160)>200){
			img_CutOut_h=facebox_div_height-160;
		}else{
			facebox_div_height+=200-(facebox_div_height-160);
		}
		img_CutOut_w=img_CutOut_h/kk_h*facebox_div_width;
		var page_h=(img_CutOut_h-60)/2+20;
		var html2='<div class="preview_facebox"><div class="big_img" style="width:'+img_CutOut_w+'px;height:'+img_CutOut_h+'px;margin-top: 0px; opacity: 1;opacity: 1; margin: 20px auto 0px; border: 1px solid #000;">'
			+'<div  class="div_table"><div  class="div_table_cell"><img src="/images/482.gif" style="max-width:'+img_CutOut_w+'px;max-height:'+img_CutOut_h+'px" err_num="0" li_num="'+li_num+'"/></div></div>'
			+'</div>'
			+'<div class="CutOut_li_page li_page_prev" style="height: 60px; width: 50px; position: absolute; top: '+page_h+'px; left: 10px;"><span style="cursor: pointer;font-size: 30px; display: inline-block; line-height: 60px; height: 60px; width: 50px;">〈</span></div>'
			+'<div class="CutOut_li_page li_page_next" style="height: 60px; width: 50px; position: absolute; top: '+page_h+'px; right: 10px;"><span style="cursor: pointer;font-size: 30px; display: inline-block; line-height: 60px; height: 60px; width: 50px;">〉</span></div>'
			+'<div class="img_li" style="margin: 10px;">'+CutOut_li_span_html+'</div>'
			+'</div>';
		var arr2=new Array();
		//arr2['facebox_title']="提示";
		arr2['close_but']=true;
		arr2['div']=html2;
		arr2['width']=(facebox_width>facebox_div_width?facebox_width:facebox_div_width)+'px';
		arr2['height']=facebox_div_height+'px';
		arr2['fun']=function(obj){
			obj.page_fun=function(){
				var span_len=obj.find('.img_li span').length;
				var span_index=obj.find('.img_li span').index(obj.find('.img_li span[class=border5]'));
				obj.find('.CutOut_li_page').css('color',null);
				if(span_index==0){
					obj.find('.CutOut_li_page.li_page_prev').css('color','#d9d9d9');
				}
				if(span_index==span_len-1){
					obj.find('.CutOut_li_page.li_page_next').css('color','#d9d9d9');
				}
			};
			obj.find('.ld_close').show();
			obj.css('background-color',"#fff");
			obj.find('.big_img img').error(function(){
				obj.find('.big_img img').remove();
				obj.find('.big_img .div_table_cell').html('合成图片失败');
			});
			obj.find('.img_li span').bind('click',function(){
				var li_span_num=$(this).find('img').attr('li_num');
				obj.find('.big_img .div_table_cell').html('<img src="/images/482.gif" style="max-width:'+img_CutOut_w+'px;max-height:'+img_CutOut_h+'px" err_num="0" li_num="'+li_span_num+'"/>');
				obj.find('.big_img img').error(function(){
					obj.find('.big_img img').remove();
					obj.find('.big_img .div_table_cell').html('合成图片失败');
				});
				$('.m_body7 .cloths_pic3 li').eq(li_span_num-1).find('.CutOut_li_img_btn .preview_a').click();
				obj.find('.img_li span').attr('class','border1');
				$(this).attr('class','border5');
				obj.page_fun();
			});
			obj.find('.CutOut_li_page').bind('click',function(){
				var span_index=obj.find('.img_li span').index(obj.find('.img_li span[class=border5]'));
				if($(this).hasClass('li_page_prev')){//向上翻页
					if(span_index>0){
						obj.find('.img_li span').eq(span_index-1).click();
					}
				}else{//向下翻页
					var span_len=obj.find('.img_li span').length;
					if(span_index<span_len-1){
						obj.find('.img_li span').eq(span_index+1).click();
					}
				}
				//obj.page_fun();
			});
			obj.page_fun();
		}
		facebox(arr2);
	}
	
	var clothes_width=$('.cpics_list .img_id_'+imageid).attr('img_w');//图片宽度
	if(clothes_width==undefined){//获取图片原始尺寸失败 重新加载原始图
		var img_src=$('.cpics_list .img_id_'+imageid+' img').attr('src');
		if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图 就不放大
			return false;
		}
		var img_src_arr=img_src.split('?imageView2');
		var image = new Image();
		image.src = img_src_arr[0];//原始路径
		image.onload=function(){
			$('.cpics_list .img_id_'+imageid).attr({'img_w':image.width,'img_h':image.height});
			CutOut_preview(obj,true);
		}
		return;
	}
	var clothes_height=$('.cpics_list .img_id_'+imageid).attr('img_h');//图片高度
	/*******************图片显示的宽高****************start******************/
	
	//获取水印地址
	var watermark_url='';
	for(var w_i in CutOut_Store_watermark){
		if(CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]!=undefined && CutOut_Store_watermark[w_i]['id']==CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['watermarkid']){
			watermark_url=CutOut_Store_watermark[w_i]['url'];
			break;
		}
	}
	$.post('/erp/CutOutpreview',{
		'watermarkid':CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]==undefined?0:CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['watermarkid'],
		'watermark_width':CutOut_Store_zoom_data[CutOut_Store_val]['watermarkwidth'],
		'watermark_height':CutOut_Store_zoom_data[CutOut_Store_val]['watermarkheight'],
		'watermark_url':watermark_url,
		'watermark_positionx':CutOut_Store_zoom_data[CutOut_Store_val]['positionx'],
		'watermark_positiony':CutOut_Store_zoom_data[CutOut_Store_val]['positiony'],
		'patform_width':CutOut_Store_zoom_data[CutOut_Store_val]['w'],
		'patform_height':CutOut_Store_zoom_data[CutOut_Store_val]['h'],
		'image_url':CutOut_data[CutOut_Store_val]['li_'+li_num]['imagesrc'].replace(domain,""),
		'cut_positionx':CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]==undefined?0:CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['left'],
		'cut_positiony':CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]==undefined?0:CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['top'],
		'cut_width':CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]==undefined?0:CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['CutOut_width'],
		'cut_height':CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]==undefined?0:CutOut_Store_zoom_data[CutOut_Store_val]['img_'+imageid]['CutOut_height'],
		'img_w':clothes_width,
		'img_h':clothes_height,
	},function(a){
		if(a.status==1){
			$('.facebox .big_img img').attr('src',a.data);
		}
	},'json');
	
	
}
/**
* 删除裁剪图
**/
function CutOut_del_img(obj){
	var li_num=parseInt($(obj).prevAll('.cloths_id').html());
	if(CutOut_data[CutOut_Store_val]['li_'+li_num]['push_id']!=0){//已保存的数据删除
		CutOut_del_data[CutOut_obj_len(CutOut_del_data)]=CutOut_data[CutOut_Store_val]['li_'+li_num]['push_id'];
	}
	var imageid=CutOut_data[CutOut_Store_val]['li_'+li_num]['imageid'];
	$('.cpics_list li.img_id_'+imageid+' .watermark_dis a.addimg').attr('CutOut_img_bool','false').css({'background':'#FFF','border':'1px solid #333','color':'#000'});
	CutOut_data[CutOut_Store_val]['li_'+li_num]=new Object();
	CutOut_move_default(li_num);
	CutOut_submit_bool=true;
}
/**
* 设置主图
**/
function CutOut_istop(obj,top){
	var li_num=parseInt($(obj).closest('.CutOut_li_img_btn').prevAll('.cloths_id').html());
	if(top==undefined && CutOut_data[CutOut_Store_val]['li_'+li_num]['top']==1){
		$(obj).removeClass('img_tipsc').addClass('img_tipsd');
		CutOut_data[CutOut_Store_val]['li_'+li_num]['top']=0;
	}else{
		$(obj).removeClass('img_tipsd').addClass('img_tipsc');
		CutOut_data[CutOut_Store_val]['li_'+li_num]['top']=1;
	}
	
	if(top==undefined){//当其没有值时，表示为主动点击设置
		CutOut_submit_bool=true;
	}
}

/**
* 裁剪弹窗滚动条定位
**/
function CutOut_boxs_scroll(){
	var scroll = parseInt($(document).scrollTop());
	var top_h=$(".m_body7").offset().top;
	if(scroll-70<=0){
		$('.m_body7').css('position','inherit');
		clearInterval(CutOut_boxs_scroll_time);
		CutOut_boxs_scroll_time=null;
	}else{
		$('.m_body7').css({'position':'fixed','top':0});
		if(CutOut_boxs_scroll==null){
			CutOut_boxs_scroll_time=setInterval(CutOut_boxs_scroll,1000);
		}
	}
}
function CutOut_img_add_Mask(imgid){
	if(CutOut_powers==0){//部分权限
		return;
	}
	$('.img_id_'+imgid).find('.li_hidden').remove();
	if(CutOut_Store_val!=0){
		var ht='<div class="li_hidden" style="position: absolute; top: 0px; height: 100%; width: 103%; left: -1%; z-index: 5;">'
		+'<div style="top: 0px; height: 100%; width: 100%; background-color: rgb(153, 153, 153); opacity: 0.45; position: absolute;"></div>'
		+'<div class="div_table" style="position: relative;">'
		+'<div class="div_table_cell" style=""><img src="/images/482.gif" style="width:40px;height:40px"/><br/>图片裁剪设置中，请稍等！</div>'
		+'</div>'
		+'</div>';
		$('.img_id_'+imgid).append(ht);
	}
}
/**
* 获取图片相关数据
* obj html 对象
**/
function CutOut_get_img_src(obj,num){
	if(CutOut_powers==0){//完整权限
		return;
	}
	var num=num?num:1;
	var imgid=$(obj).attr('img_id2');
	var img_src=$(obj).find('.clothes_img img').attr('src');
	if($(obj).attr('class')!='facebox'){
		CutOut_img_add_Mask(imgid);
		img_src=$(obj).find('img').attr('src');
	}
	var img_data=new Array();
	
	if(img_src==undefined){
		//alert(imgid)
		//setTimeout(function(){CutOut_get_img_src(obj,num);},500);
		return;
	}
	if(img_src=='' || img_src=='/images/c_none.jpg'){//图片为空 或为默认图 就不放大
		//alert(imgid+'-----')
		return false;
	}
	
	var zoom_ratio=parseFloat($(obj).attr('zoom_ratio'));
	
	if(zoom_ratio>0){
		if(CutOut_powers==1){//完整权限
			CutOut_img_add_box(imgid);//添加裁剪框
		}
		return;
	}
	var img_src_arr=img_src.split('?imageView2');
	img_data['original_src']=img_src_arr[0];//原始路径
	var image = new Image();
    image.src = img_data['original_src'];
	var current_image = new Image();
    current_image.src = img_src;
	image.onload=function(){
		img_data['original_width']=image.width;//原始宽度
		img_data['original_height']=image.height;//原始高度
		
		img_data['current_src']=img_src;//当前路径
		img_data['current_width']=current_image.width;//当前宽度
		img_data['current_height']=current_image.height;//当前高度
		img_data['zoom_ratio']=1;
		var width_zoom_ratio=img_data['current_width']/img_data['original_width'];//宽度缩放比例
		var height_zoom_ratio=img_data['current_height']/img_data['original_height'];//高度缩放比例
		if(img_data['original_width']<=img_data['current_width'] && img_data['original_height']<=img_data['current_height']){
			img_data['zoom_ratio']=1;
		}else{
			img_data['zoom_ratio']=width_zoom_ratio>height_zoom_ratio?height_zoom_ratio:width_zoom_ratio;
		}
		$(obj).attr({'zoom_ratio':img_data['zoom_ratio'],'img_w':img_data['original_width'],'img_h':img_data['original_height'],'width_zoom_ratio':width_zoom_ratio,'height_zoom_ratio':height_zoom_ratio,'current_width':img_data['current_width'],'current_height':img_data['current_height']});
		if(img_data['zoom_ratio']==0){
			num++;
			if(num>50){
				return;
			}
			setTimeout(function(){
				CutOut_get_img_src(obj,num);
			},200);
			return;
		}
		if(CutOut_powers==1){//完整权限
			CutOut_img_add_box(imgid);//添加裁剪框
		}
	}
	
}
/**
* 将数据加入到Cookie
**/
function CutOut_setCookie(){
	//return;
	var CutOut=new Object();
	//CutOut['CutOut_data']=CutOut_data;
	//CutOut['CutOut_del_data']=CutOut_del_data;
	//CutOut['CutOut_Store_zoom_data']=CutOut_Store_zoom_data;
	CutOut['CutOut_start_bool']=CutOut_start_bool;
	CutOut['CutOut_Store_val']=CutOut_Store_val;
	//CutOut['CutOut_submit_bool']=CutOut_submit_bool;
	$.post('/erp/setCutCookie',{'data':JSON.stringify(CutOut),'SKU':brandnumber},function(a){});
}

/**
* 将数据获取到Cookie
**/
function CutOut_getCookie(){
	//return;
	$.getJSON('/erp/getCutCookie?SKU='+brandnumber,function(a){
		if(a.status==1){
			/*CutOut_Prompt_pop('数据恢复中<span style="font-size: 30px; display: inline-block; width: 38px;text-align: left;">...</span>',function(facebox_obj){
				facebox_obj.find('.ld_title').hide();
				facebox_obj.find('.ld_close').hide();
				var inter_time=setInterval(function(){
					var span_html=facebox_obj.find('.p_main span').html();
					if(span_html=='.'){
						span_html='..';
					}else if(span_html=='..'){
						span_html='...';
					}else{
						span_html='.';
					}
					facebox_obj.find('.p_main span').html(span_html);
					},500);
				setTimeout(function(){clearInterval(inter_time);facebox_obj.find('.ld_close').click();},3000);
			});*/
			
			var CutOut=JSON.parse(a.data);
			//CutOut_data=CutOut['CutOut_data'];
			//CutOut_del_data=CutOut['CutOut_del_data'];
			//CutOut_Store_zoom_data=CutOut['CutOut_Store_zoom_data'];
			//CutOut_Store_val=CutOut['CutOut_Store_val'];
			//CutOut_submit_bool=CutOut['CutOut_submit_bool'];
			if(CutOut['CutOut_start_bool']){//自动开启裁剪数据
				CutOut_start();
			}
			setTimeout(function(){
				$('.m_body7 select').val(CutOut['CutOut_Store_val']);
				$('.m_body7 select').change();
			},1000);
			
		}
	});
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
	var html2='<div id="addsuccess"  style="width: auto;  margin-top: 0px; opacity: 1;">' 
		+' <div class="p_main">'
		+'  <div  class="div_table"><div  class="div_table_cell">'
		+'      <a target="_blank" href="/erp/pushlist" onmousemove="push_image(this,1)" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">推送到官网</a></br>'
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
	 $(obj).attr('href',"/erp/pushlist?clothes_string="+id+"&patformid="+patformid);
}