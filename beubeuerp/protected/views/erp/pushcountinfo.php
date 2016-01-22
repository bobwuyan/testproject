
<title>百一服装ERP管理系统3.0_推送统计</title>
<link rel="stylesheet" type="text/css" href="/css/pager.css" />
<style>
	.div_table{
		line-height: 25px; display: table; height: 100%; width: 100%; overflow: hidden;word-break: break-all;word-wrap: break-word;
	}
	.div_table_cell{
		display: table-cell; vertical-align: middle; text-align: center; padding: 0px 3px;
	}
</style>


<div class="m_body border1">
	<div class="fl w80 t_16b pad_l20">
		推送统计
    </div>
</div>   
<div class="m_body border1">
    <div class=" w560 text-center" style="margin:0 auto">
      <div class="fl pad_l10" style="height:30px; line-height:30px; margin:0; padding:0 10px;">
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
      <input id="keyword" type="text" value="<?php echo $key?>" placeholder="请填写款号或色号" class="input-xlarge" style="width:100px; height:30px; font-size:12px; line-height:30px; margin:0; padding:0 6px;">
      <select name="" size="1" id="patformid" style="width:100px; height:30px; line-height:30px; margin:0; font-size:12px;">
        <option value="0">推送的平台</option>
      	 <?php 
        foreach($patform_obj as $value)
        {
        	?>
        		 <option value="<?php echo $value['id']?>" <?php if($patfromid==$value['id']){echo selected;}?>><?php echo $value['name']?></option>
        	<?php
        }
        ?>
      </select>     
      <a href="javascript:void(0)" onclick="Pagerefresh(1)" class="button button-primary button-rounded button-small" style=" width:100px; padding:0 15px;">查询</a>
      </div>
    </div>
</div>
<div class="m_body2 ">
  <div class="tab_head text-center border2" >
    <div class="fl w94 border_rt">序号</div>
    <div class="fl w194 border_rt">款号</div>
    <div class="fl w235 border_rt">推送平台</div>
    <div class="fl w400 border_rt">推送数量</div>
    <div class="fl w256 ">推送时间</div>
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
		    <div class="fl w194 border_rt"><?php echo $value['skc']?></div>
		    <div class="fl w235 border_rt">
		    <?php 
		    	
		    	foreach($patform_obj as $patform_value)
		    	{
		    		if($value['patformid']==$patform_value['id'])
		    		{
		    			echo $patform_value['name'];
		    			break;
		    		}
		    	}
		    ?>
		    </div>
		    <div class="fl w400 border_rt">成功<?php echo $value['count']?>张</div>
		    <div class="fl w256 "><?php echo $value['addtime']?></div>
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
	
	var url='/erp/pushcountinfo';
	
	
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
	var patformid=strid('patformid');
	if(patformid!='' && parseInt(patformid)>0)
	{
		url+='/patformid/'+patformid;
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


</script>

