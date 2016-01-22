var navigatorVersion = false; //浏览器版本是否ie6、7
var img_server_host=getCookie('img_server_host');//图片服务器域名
var web_server_host=getCookie('web_server_host');//程序服务器域名
var Event_name='click';//事件名

/************************************ 弹窗封装 start ***********************************/
/**
* 弹窗
* facebox_over_close 是否点击框外关闭弹窗 默认开启 传参true就表示不开启
* facebox_over_opacity 是否点击框外遮层透明度 是否透明 默认半透明  传参true就表示透明
* div 弹窗显示的内容
* facebox_title 弹窗标题 默认 温馨提示
* head 是否启用头 默认启用 传true关闭
* width 弹窗宽度 
* height 弹窗高度
* close_but 是否启用关闭按钮 默认启用 传true关闭 并且关闭标题
* top left 对弹窗定位
* center 对弹窗定位 优先级低于top、left 其默认为居中 值为center也表示居中
* fun 弹窗创建完成后执行的方法
* fun_show 动画执行前执行的方法
* Single 默认单一弹窗 传参不为false 表示可使用多弹窗
* facebox_name 定义弹窗名字 默认为空
* close_time 自动关闭时间
* animate_type 动画类型 show:直接显示 fade:淡入淡出 moveTop:底部向上移动 moveBottom:顶部向下移动 moveLeft:右侧向左移动
* animate_fun 启用了就不使用animate_type 动画事件 默认 淡入淡出 其它按下面格式 array('show':function(){},'close':function(){})
**/
function facebox(arr){
	var math_num='';
	arr['facebox_name']=arr['facebox_name']?arr['facebox_name']:'';
	arr['Single']=arr['Single']?arr['Single']:false;
	arr['facebox_over_opacity']=arr['facebox_over_opacity']?arr['facebox_over_opacity']:false;
	arr['fun_show']=arr['fun_show']?arr['fun_show']:'';
	arr['facebox_title']=arr['facebox_title']?arr['facebox_title']:'温馨提示';
	arr['width']=arr['width']?'width:'+arr['width']+';':'';
	arr['height']=arr['height']?'height:'+arr['height']+';':'';
	arr['animate_fun']=arr['animate_fun']?arr['animate_fun']:'';
	arr['head']=arr['head']?arr['head']:false;
	arr['close_but']=arr['close_but']?arr['close_but']:false;
	arr['animate_type']=arr['animate_type']?arr['animate_type']:'fade';
	arr['facebox_over_close']=arr['facebox_over_close']?arr['facebox_over_close']:false;
	if(arr['facebox_name']!=''){//如果指定了弹窗名
		math_num=arr['facebox_name'];
	}else if(arr['Single']){//判断是否支持多弹窗
		math_num=parseInt(Math.random()*1000000);
	}else{//单一弹窗时 先关闭所有弹窗
		facebox_close_all();
	}
	
	//设置遮层是否透明
	var facebox_over_opacity='';
	if(arr['facebox_over_opacity']){
		facebox_over_opacity='style="opacity:0.01"';
	}
	var facebox_over_obj=$('#facebox_over'+math_num);
	facebox_over_obj.remove();
	$('body').append('<div id="facebox_over'+math_num+'" class="facebox_over" '+facebox_over_opacity+'></div>');
	facebox_over_obj=$('#facebox_over'+math_num);
	
	var facebox_obj=$('#facebox'+math_num);
	facebox_obj.remove();
	
	//设置关闭按钮是否显示
	var close_but_class='';
	if(arr['close_but']){
		close_but_class=' style="display:none"';
	}
	$('body').append('<div id="facebox'+math_num+'" class="facebox" style="'+arr['width']+arr['height']+'"><div class="ld_tc_tips1"><div class="ld_title"'+close_but_class+'>'+arr['facebox_title']+'</div><div class="ld_close"'+close_but_class+'>X</div></div></div>');
	facebox_obj=$('#facebox'+math_num);
	//关闭按钮添加关闭事件
	facebox_obj.find('.ld_close').bind(Event_name,function(){
		if(strempty(arr['animate_fun']['close']) && typeof(arr['animate_fun']['close'])!='function'){
			if(arr['animate_type']=='show'){
				facebox_obj.remove();
				facebox_over_obj.remove();
			}else if(arr['animate_type']=='moveBottom'){
				//获取弹窗高度
				facebox_obj.stop().animate({height:0},1000,'easeOutQuint',function(){
					$('.Category_fx_img').hide();
					facebox_obj.remove();
					facebox_over_obj.remove();
				});
			}else if(arr['animate_type']=='moveLeft'){
				//获取弹窗宽度
				var facebox_obj_wid=facebox_obj.width();
				var f_left=parseInt(facebox_obj.css('left'));
				facebox_obj.stop().animate({left:f_left+facebox_obj_wid},1000,'easeOutQuint',function(){
					$('body').append(facebox_obj.find('.tc_detail').hide());
					facebox_obj.remove();
					facebox_over_obj.remove();
					$("#current_clotheslist").find("li").attr("class","cls_border2");
				});
			}else{
				facebox_obj.fadeOut('slow',function(){
					facebox_obj.remove();
					facebox_over_obj.remove();
				});
			}
			facebox_over_obj.fadeOut('slow');
		}else{
			setblacktagimg();
			arr['animate_fun']['close'](facebox_obj);
		}
	});
	//启用遮层关闭
	if(!arr['facebox_over_close']){
		facebox_over_obj.bind(Event_name,function(){
			facebox_obj.find('.ld_close').trigger(Event_name);
		});
	}
	//启用自动关闭
	if(!strempty(arr['close_time'])){
		setTimeout(function(){
			facebox_obj.find('.ld_close').trigger(Event_name);
		},arr['close_time']);
	}
	
	//给弹窗添加内容
	if(!strempty(arr['div'])){
		facebox_obj.find('.ld_tc_tips1').append(arr['div']);
		facebox_obj.find('.ld_tc_tips1').append('<div style="clear: both;"></div>');
	}
	//指定定位
	if(!strempty(arr['top']) || !strempty(arr['left'])){
		arr['left']=arr['left']?arr['left']:0;
		arr['top']=arr['top']?arr['top']:0;
		facebox_obj.css({'top':arr['top']+'px','left':arr['left']+'px'});
	}else if(strempty(arr['center']) || arr['center']=='center'){//居中设置
		var win_hei=$(window).height();
		var win_wid=$(window).width();
		var body_wid=$('.bca_main').width();
		var facebox_hei=facebox_obj.height();
		var facebox_wid=facebox_obj.width();
		var top=0;
		var left=0;
		
		if(win_hei>facebox_hei){
			top=(win_hei-facebox_hei)/2;
		}
		if(win_wid<=body_wid){
			left=(body_wid-facebox_wid)/2;
		}else if(win_wid>body_wid){
			left=(win_wid-facebox_wid)/2;
		}
		facebox_obj.css({'top':top+'px','left':left+'px','position':'fixed'});
	}
	
	//执行回调函数
	if(!strempty(arr['fun'])){
		arr['fun'](facebox_obj);
	}
	//执行弹窗启用特效
	if(strempty(arr['animate_fun']['show']) && typeof(arr['animate_fun']['show'])!='function'){
		if(arr['animate_type']=='show'){
			facebox_obj.show('slow');
			facebox_over_obj.show('slow');
		}else if(arr['animate_type']=='moveBottom'){
			//获取弹窗高度
			var facebox_obj_hei=(facebox_obj.find('.ld_tc_tips1 .ld_close').next().height()+facebox_obj.find('.ld_tc_tips1 .ld_close').height());
			facebox_obj.css('height',0);
			facebox_obj.show();
			facebox_over_obj.show();
			facebox_obj.stop().animate({height:facebox_obj_hei},1000,'easeInQuint');
		}else if(arr['animate_type']=='moveLeft'){
			//获取弹窗高度
			var facebox_obj_wid=facebox_obj.width();
			var f_left=parseInt(facebox_obj.css('left'));
			facebox_obj.show();
			facebox_over_obj.show();
			facebox_obj.stop().animate({left:f_left-facebox_obj_wid},1000,'easeInQuint');
		}else{
			facebox_obj.fadeIn('slow');
			facebox_over_obj.fadeIn('slow');
		}
	}else{
		facebox_obj.show();
		facebox_over_obj.show();
		arr['animate_fun']['show'](facebox_obj);
	}
}

/**
* 关闭所有弹窗
**/
function facebox_close_all(){
	$('.facebox').map(function(){
		$(this).find('.ld_close').trigger(Event_name);
	});
}

/************************************ 弹窗封装 end ***********************************/
/**
 * 图片按比例缩放 
 * @param ImgD 要缩放图片对象
 * @param iwidth 缩放后的图片宽度
 * @param iheight 缩放后的图片高度
 */
var flag = false;
function DrawImage(ImgD, iwidth, iheight) {
    //参数(图片,允许的宽度,允许的高度) 
    var image = new Image();
    image.src = ImgD.src;
    if (image.width > 0 && image.height > 0) {
        flag = true;
        if (image.width / image.height >= iwidth / iheight) {
            if (image.width > iwidth) {
                ImgD.width = iwidth;
                ImgD.height = (image.height * iwidth) / image.width;
            } else {
                ImgD.width = image.width;
                ImgD.height = image.height;
            }
            //ImgD.alt = image.width + "×" + image.height;
        }
        else {
            if (image.height > iheight) {
                ImgD.height = iheight;
                ImgD.width = (image.width * iheight) / image.height;
            } else {
                ImgD.width = image.width;
                ImgD.height = image.height;
            }
            //ImgD.alt = image.width + "×" + image.height;
        }
    }
    //div_vertical();
} 

/**
 * 去除字符串前后空格
 * @param str 字符串
 * @return 整理后的字符穿
 */
function strtrim(str)
{
	return str.replace(/(^\s*)|(\s*$)/g, "");
}
/**
 * 判断字符串不为空
 * @param str 字符串
 * @return 为空返回true 否则返回false
 */
function strempty(str){
	try{
		str=str.toLowerCase();
	}catch(e){}
	if(str+''=='null' || str=='' || str+''=='undefined')
	{
		return true;
	}
	return false;
}

/**
 * 判断字符串是否为数字（整数，浮点）
 * @param str
 * @return 是返回true 否则返回false
 */
function strnumber(str){
	var reg = /^\d+(\.\d+)?$/;
	return reg.test(str);
}
/**
 * 判断字符串首字符是否为字母
 * @param str
 * @return 是返回true 否则返回false
 */
function strFirstChar(str){
	var reg = /^[A-Za-z].*?/;
	return reg.test(str);
}

/**
 * 判断字符串是否为纯字母
 * @param str
 * @return 是返回true 否则返回false
 */
function strLetter(str){
	var reg = /^[A-Za-z]+$/;
	return reg.test(str);
}
/**
 * 判断是否为uri完整地址
 * @param str 输入的字符串
 * @return 如果通过验证返回true,否则返回false
 */
function strURL (str) {
	var strRegex = '^((https|http|ftp|rtsp|mms)?://)'
	+ '?(([0-9a-z_!~*\'().&=+$%-]+: )?[0-9a-z_!~*\'().&=+$%-]+@)?' //ftp的user@
	+ '(([0-9]{1,3}.){3}[0-9]{1,3}' // IP形式的URL- 199.194.52.184
	+ '|' // 允许IP和DOMAIN（域名）
	+ '([0-9a-z_!~*\'()-]+.)*' // 域名- www.
	+ '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z].' // 二级域名
	+ '[a-z]{2,6})' // first level domain- .com or .museum
	+ '(:[0-9]{1,4})?' // 端口- :80
	+ '((/?)|' // a slash isn't required if there is no file name
	+ '(/[0-9a-z_!~*\'().;?:@&=+$,%#-]+)+/?)$';
	var re=new RegExp(strRegex);
	//re.test()
	return re.test(str);
}
/**
 * 检查输入对象的值是否符合E-Mail格式
 * @param str 输入的字符串
 * @return 如果通过验证返回true,否则返回false
 */
function strEmail( str ){
	var myReg = /^[\w-_]+(\.[\w-_]+)*@[\w-_]+(\.[\w-_]+)+$/;
	if(myReg.test(str)) return true;
	return false;
}
/**
 * 检查输入的手机号码格式是否正确
 * @param str 字符串
 * @return 成功返回true 否则返回false
 */
function strPhone(str)   
{   
	//	验证电话号码手机号码，包含153，159号段    
	var p1 = /^[1][0-9]{10}$/;   
	return p1.test(str);  
} 
/**
 * 检查输入的手机号码格式是否正确
 * @param str 字符串
 * @return 成功返回true 否则返回false
 */
function strTel(str)
{
    //"兼容格式: 国家代码111cn.net(2到3位)-区号(2到3位)-电话号码(7到8位)-分机号(3位)"
    //return (/^(([0+]d{2,3}-)?(0d{2,3})-)?(d{7,8})(-(d{3,}))?$/.test(this.Trim()));
    return (/^(([0+]d{2,3}-)?(0d{2,3})-)(d{7,8})(-(d{3,}))?$/.test(str));
}
/**
* 判断值是否存在已数组中
* @parm val 需要查找的值
* @parm arr 值存在的数组
* @return 成功返回key 否则返回false
**/
function array_search(val,arr){
	var ret=false;
	for(var i in arr){
		if(arr[i]==val){
			ret=i;
			break;
		}
	}
	return ret;
}
/**
* 获取数组的长度
* @parm o 数组
* @return 成功返回数组的长度 否则返回false
**/
function arr_count(o){
    var t = typeof o;
    if(t == 'string'){
        return o.length;
    }else if(t == 'object'){
        var n = 0;
        for(var i in o){n++;}
        return n;
    }
    return false;
}
/** 
* 将form中的值转换为键值对。
* 形如：{name:'aaa',password:'tttt'}
* ps:注意将同名的放在一个数组里
**/
function getFormJson(frm) {
	var o = {};
	var a = $(frm).serializeArray();
	$.each(a, function () {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
}
/**
 * 关闭facebox弹窗
 * @return
 */
function faceboxclose()
{
	$(document).trigger('close.facebox');	
}

/**
 * 控制文本框只能输入数字
 * @param obj
 * @return
 */
function inputkeyUpint(obj)
{
	$(obj).val($(obj).val().replace(/\D/g,''));
}

/**
 * 判断浏览器是否ie6、7
 */
function getOs()  
{  
    if(navigator.appName == "Microsoft Internet Explorer") 
    { 
    	 var Sys = 0;
         var ua = navigator.userAgent.toLowerCase();
         var s;
         (s = ua.match(/msie ([\d.]+)/)) ? Sys = s[1]:0;
         if(parseInt(Sys)==7 || parseInt(Sys)==6)
         {
//        	 alert(Sys);
        	 navigatorVersion=true;
         }
    }
}

/**
 * 设置div垂直居中
 */
function div_vertical(con_class,middle_class)
{
	$('.'+con_class).map(function(){
		var div_middle=$(this).find('.'+middle_class);
		var hei=$(this).height();
		var div_middle_hei=$(div_middle).height();
		if(hei>div_middle_hei)
		{
			$(div_middle).css({'margin-top':(hei-div_middle_hei)/2+"px"});
		}
	});
}

/**
* 错误提示弹窗
**/
function errpop(txt){
	var options={txt:txt,
				width : 400,
				content:true,
				fun		:function(){
							facebox_center();
						}
			};
	$.fn.templetdiv(options);
}
/**
* 下拉列表
* @param add_id 需要将下拉菜单添加到的位置
* @param select_id 下拉菜单的ID
* @param select_option 下拉菜单默认选择项
* @param select_name 下拉菜单的默认选择值
* @param select_arr 下拉菜单的内容
* @param width 下拉菜单的宽度
* @param callback 下拉菜单的回调函数
* @param templet 下拉菜单的版本
* @return 返回菜单控件对象
**/
function myselect(add_id,select_id,select_option,select_name,select_arr,width,callback,templet){
	if(strempty(add_id) || strempty(select_id) || typeof(select_arr)!='object'  || select_arr.length==0){
		return;
	}
	if(strempty(width)){
		width=102;
	}
	var options={	
			add_place :add_id,
			selectid  :select_id,
			select_arr:select_arr,
			width     :width,
			fun		:function(value){
						if(callback!=''){
							callback(value);
						}
					}
		};
	if(!strempty(select_option)){
		options['select_option']=select_option;
	}else if(!strempty(select_name)){
		options['select_name']=select_name;
	}
	if(!strempty(templet)){
		options['templet']=templet;
	}
	return new $.fn.mySelect(options);
}

/**
* 翻页控件修改
**/
function setyiiPager(id){
	$(id).map(function(){
		var this_obj=this;
		$(this_obj).find('select').change(function(){
			$(this_obj).find('a').unbind('click');
			$(this_obj).find('select').unbind('change');
			
			window.location.href=$(this_obj).find('select').attr('href')+'/page/'+$(this_obj).find('select').val();
		})
		$(this_obj).find('a').map(function(){
			var href=$(this).attr('href');
			$(this).attr('href','javascript:void(0)');
			$(this).click(function(){
				$(this_obj).find('a').unbind('click');
				$(this_obj).find('select').unbind('change');
				window.location.href=href;
			});
		});
	});
}
/**
* flash复制
**/
function flashCopy(obj,coder,text){
	$(obj).zclip({
	    path:'/js/ZeroClipboard.swf',
	    copy:coder,
		text:text
	});
}

/**
*全选反选
**/
function checkqf(obj,div_name)
{
	var checked=$(div_name).find('input[type=checkbox]:checked');
	var len=checked.length;//勾选上的衣服有几件
	if(len>0){
		$(div_name).find('input[type=checkbox]').not("[checked]").attr('checked',true);
		checked.attr('checked',false);
	}else{
		$(div_name).find('input[type=checkbox]').attr('checked',true);
	}
}
/**
* 提示框
* @param obj 需要显示提示框的控件
* @param str 需要显示提示框的控件的唯一标识
* @param txt 需要显示到提示框的内容
**/
function Prompt_tips_pop(obj,str,txt){
	if($('#Promptpop_div').length==0){
		$('body').append('<div id="Promptpop_div" style="" Prompt_type=""><div></div></div>');
	}
	var Prompt_type=$('#Promptpop_div').attr('Prompt_type');
	if(Prompt_type==str || str=='Prompt_Mask'){//点击的同一个提示
		$('#Promptpop_div').attr('Prompt_type','');
		$('#Promptpop_div').hide();
		return;
	}
	
	$('#Promptpop_div').find('div').html(txt);
	$('#Promptpop_div').show();
	var Promptpop_div_hei=$('#Promptpop_div').height();
	oLeft = obj.offsetLeft+60;
	oTop = obj.offsetTop-(Promptpop_div_hei/2) +7.5;
	$('#Promptpop_div').attr('Prompt_type',str).css({left:oLeft+'px',top:oTop+'px'});
	
	$('#Promptpop_div').find('div').css('margin-top',(Promptpop_div_hei-$('#Promptpop_div').find('div').height())/2+'px');
}
/*********************************************拖动判读***********************************************/

var dragerr_time=null;
/**
* 拖动判断
**/
function dragerr(){
	if($('#dragerr_div').length==0){
		$('body').append('<div id="dragerr_div" onmousedown="dragerrhide()" style="width: 1080px; height: 1920px; position: absolute; left:-1080px;top: 0px; display: none;z-index: 5001;"><div style="width:280px;height:90px;margin-top:915px;margin-left:400px"><img src="/images/tips1.png"/></div></div>');
	}
	var start_x=0;
	var start_y=0;
	document.onmousedown=function(e){
		e = e || event;
		if(e.X==undefined){
			start_x=e.clientX;
			start_y=e.clientY;
		}else{
			start_x=e.X;
			start_y=e.Y;
		}
	};
	document.onmouseup=function(e){
		if($('#dragerr_div').css('display')!='none'){
			return;
		}
		e = e || event;
		var end_x=0;
		var end_y=0;
		if(e.X==undefined){
			end_x=e.clientX;
			end_y=e.clientY;
		}else{
			end_x=e.X;
			end_y=e.Y;
		}
		if(Math.abs(start_x-end_x)>10 || Math.abs(start_y-end_y)>10){
			var win_wid=$(window).width();
			if(win_wid<1080){
				win_wid=1080;
			}
			$('#dragerr_div').css('width',win_wid+'px');
			$('#dragerr_div div').css('margin-left',(win_wid-280)/2+'px');
			$('#dragerr_div').css('left','-'+win_wid+'px');
			$('#dragerr_div').show();
			$('#dragerr_div').animate({left:0},700,function(){
				dragerr_time=setTimeout(dragerrhide,3000);
			});
		}
	};
}
/**
* 关闭拖动错误提示
**/
function dragerrhide(){
	if(dragerr_time!=null){
		clearTimeout(dragerr_time);
		dragerr_time=null;
	}
	var win_wid=$(window).width();
	if(win_wid<1080){
		win_wid=1080;
	}
	$('#dragerr_div').animate({left:'-'+win_wid},700,function(){
		$('#dragerr_div').hide();
	});
}

/*********************************************拖动判读***********************************************/

/********************************* Cookie start **********************************************/
//设置cookie
function setCookie(NameOfCookie, value, expiredays)
{
	//@参数:三个变量用来设置新的cookie:
	//cookie的名称,存储的Cookie值,
	// 以及Cookie过期的时间.
	// 这几行是把天数转换为合法的日期
	var ExpireDate = new Date ();
	ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600));
	// 下面这行是用来存储cookie的,只需简单的为"document.cookie"赋值即可.
	// 注意日期通过toGMTstring()函数被转换成了GMT时间。
	document.cookie = NameOfCookie + "=" + escape(value) + ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
}
//获取cookie值
function getCookie(NameOfCookie)
{
	// 首先我们检查下cookie是否存在.
	// 如果不存在则document.cookie的长度为0
	if (document.cookie.length > 0)
	{
		// 接着我们检查下cookie的名字是否存在于document.cookie
		// 因为不止一个cookie值存储,所以即使document.cookie的长度不为0也不能保证我们想要的名字的cookie存在
		//所以我们需要这一步看看是否有我们想要的cookie
		//如果begin的变量值得到的是-1那么说明不存在
		begin = document.cookie.indexOf(NameOfCookie+"=");
		if (begin != -1)   
		{
			// 说明存在我们的cookie.
			begin += NameOfCookie.length+1;//cookie值的初始位置
			end = document.cookie.indexOf(";", begin);//结束位置
			if (end == -1) end = document.cookie.length;//没有;则end为字符串结束位置
				return unescape(document.cookie.substring(begin, end));
		}
	}
	return null;
	// cookie不存在返回null
}
 //删除cookie
function delCookie (NameOfCookie)
{
  // 该函数检查下cookie是否设置，如果设置了则将过期时间调到过去的时间;
  //剩下就交给操作系统适当时间清理cookie啦
	if (getCookie(NameOfCookie))
	{
		document.cookie = NameOfCookie + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
	}
}
/********************************* Cookie end **********************************************/

var browser={
	versions:function(){
		var u = navigator.userAgent, app = navigator.appVersion;
		return { //移动终端浏览器版本信息
			trident: u.indexOf('Trident') > -1, //IE内核
			presto: u.indexOf('Presto') > -1, //opera内核
			webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
			gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
			mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
			ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
			android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
			iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器
			iPad: u.indexOf('iPad') > -1, //是否iPad
			webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
		};
	}(),
	language:(navigator.browserLanguage || navigator.language).toLowerCase()
} 

/***************************字符串加密**********************************/
/**
8进制加密
*/
function EnEight(str){
    var monyer = new Array();var i,s;
    for(i=0;i<str.length;i++)
        monyer+="\\"+str.charCodeAt(i).toString(8); 
	str=monyer;
	return str;
}
/**
8进制解密
*/
function DeEight(str){
    var monyer = new Array();var i;
    var s=str.split("\\");
    for(i=1;i<s.length;i++)
        monyer+=String.fromCharCode(parseInt(s[i],8));
    str=monyer;
	return str;
}